<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penilaian360 extends Model
{
    protected $table    = 'hr_penilaian_360';
    protected $fillable = ['nik', 'pegawai_id', 'semester', 'tahun', 'status', 'nilai_akhir', 'deadline', 'dibuat_oleh'];
    protected $casts    = ['nilai_akhir' => 'double', 'deadline' => 'date'];

    public function scopePeriode($query, int $tahun, int $semester)
    {
        return $query->where('tahun', $tahun)->where('semester', $semester);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id');
    }

    public function raters(): HasMany
    {
        return $this->hasMany(Rater360::class, 'penilaian_id');
    }

    public function raterSubmitted(): HasMany
    {
        return $this->hasMany(Rater360::class, 'penilaian_id')->whereNotNull('submitted_at');
    }

    public function komentar(): HasMany
    {
        return $this->hasMany(Komentar360::class, 'penilaian_id');
    }

    /** Hitung nilai akhir berbobot dari semua rater yang sudah submit */
    public function hitungNilaiAkhir(): float
    {
        $bobotRater  = \DB::table('hr_kinerja_360_bobot_rater')->pluck('bobot', 'hubungan');
        $dimensiList = Dimensi360::where('aktif', true)->with('aspek')->get();
        $raters      = $this->raterSubmitted()->with('nilai')->get();

        if ($raters->isEmpty()) return 0;

        $totalBobot = 0;
        $totalNilai = 0;

        foreach ($raters->groupBy('hubungan') as $hubungan => $group) {
            $bobotHubungan = (float)($bobotRater[$hubungan] ?? 0) / 100;
            if ($bobotHubungan === 0.0) continue;

            // Rata-rata nilai semua rater dalam hubungan ini
            $allNilai = $group->flatMap->nilai->pluck('nilai');
            if ($allNilai->isEmpty()) continue;

            $avgNilai    = $allNilai->avg();
            $totalNilai += $avgNilai * $bobotHubungan;
            $totalBobot += $bobotHubungan;
        }

        return $totalBobot > 0 ? round(($totalNilai / $totalBobot) * 20, 2) : 0; // scale ke 100
    }
}
