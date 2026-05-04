<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlipGaji extends Model
{
    protected $table    = 'hr_slip_gaji';
    protected $fillable = [
        'nik', 'pegawai_id', 'bulan', 'tahun', 'status',
        'gaji_pokok', 'total_tunjangan', 'total_potongan', 'gaji_bersih',
        'catatan', 'generated_by', 'generated_at', 'finalized_at',
    ];
    protected $casts = [
        'gaji_pokok'      => 'double',
        'total_tunjangan' => 'double',
        'total_potongan'  => 'double',
        'gaji_bersih'     => 'double',
        'generated_at'    => 'datetime',
        'finalized_at'    => 'datetime',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePeriode($query, int $tahun, int $bulan)
    {
        return $query->where('tahun', $tahun)->where('bulan', $bulan);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeFinal($query)
    {
        return $query->where('status', 'final');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getPeriodeLabelAttribute(): string
    {
        return \Carbon\Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y');
    }

    // ─── Recalculate totals dari komponen ─────────────────────────────────────

    public function recalculate(): void
    {
        $komponen = $this->komponenSlip;
        $gapok    = $komponen->where('nama', 'Gaji Pokok')->first()?->nilai ?? 0;
        $tambah   = $komponen->where('jenis', 'tambah')->sum('nilai');
        $kurang   = $komponen->where('jenis', 'kurang')->sum('nilai');

        $this->update([
            'gaji_pokok'      => $gapok,
            'total_tunjangan' => $tambah - $gapok,
            'total_potongan'  => $kurang,
            'gaji_bersih'     => $tambah - $kurang,
        ]);
    }

    // ─── Relasi ───────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by', 'id');
    }

    public function komponenSlip(): HasMany
    {
        return $this->hasMany(SlipKomponen::class, 'slip_id')->orderBy('urutan');
    }

    public function komponen(string $jenis): HasMany
    {
        return $this->hasMany(SlipKomponen::class, 'slip_id')
                    ->where('jenis', $jenis)->orderBy('urutan');
    }
}
