<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenilaianPrestasi extends Model
{
    protected $table    = 'hr_penilaian_prestasi';
    protected $fillable = [
        'nik', 'pegawai_id', 'semester', 'tahun', 'penilai_id',
        'status', 'nilai_akhir', 'predikat',
        'kelebihan', 'kekurangan', 'saran', 'rekomendasi', 'submitted_at',
    ];
    protected $casts = [
        'nilai_akhir'  => 'double',
        'submitted_at' => 'datetime',
    ];

    const SKALA = [
        1 => 'Kecewa',
        2 => 'Kurang',
        3 => 'Biasa',
        4 => 'Puas',
        5 => 'Istimewa',
    ];

    public function hitungNilaiAkhir(): float
    {
        $kriteria = KriteriaKinerja::where('aktif', true)->get();
        $total    = 0;
        foreach ($kriteria as $k) {
            $nilai = $this->nilaiList->firstWhere('kriteria_id', $k->id)?->nilai ?? 0;
            $total += ($nilai / 5) * $k->bobot;
        }
        return round($total, 2);
    }

    public static function predikatDari(float $nilai): string
    {
        return match(true) {
            $nilai >= 90 => 'Istimewa',
            $nilai >= 75 => 'Puas',
            $nilai >= 60 => 'Biasa',
            $nilai >= 45 => 'Kurang',
            default      => 'Kecewa',
        };
    }

    public function scopePeriode($query, int $tahun, int $semester)
    {
        return $query->where('tahun', $tahun)->where('semester', $semester);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penilai_id', 'id');
    }

    public function nilaiList(): HasMany
    {
        return $this->hasMany(PenilaianPrestasiNilai::class, 'penilaian_id');
    }
}
