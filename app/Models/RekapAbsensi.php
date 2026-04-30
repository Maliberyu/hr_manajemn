<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapAbsensi extends Model
{
    // Tabel BARU — perlu migration
    protected $table = 'rekap_absensi';

    protected $fillable = [
        'pegawai_id',
        'bulan',
        'tahun',
        'total_hadir',
        'total_izin',
        'total_sakit',
        'total_alfa',
        'total_cuti',
        'total_terlambat',      // jumlah kejadian terlambat
        'total_menit_terlambat',// akumulasi menit terlambat
        'total_lembur_jam',     // total jam lembur bulan ini
        'wajib_masuk',          // hari wajib masuk bulan ini (dari pegawai.wajibmasuk)
    ];

    protected $casts = [
        'bulan'                 => 'integer',
        'tahun'                 => 'integer',
        'total_hadir'           => 'integer',
        'total_izin'            => 'integer',
        'total_sakit'           => 'integer',
        'total_alfa'            => 'integer',
        'total_cuti'            => 'integer',
        'total_terlambat'       => 'integer',
        'total_menit_terlambat' => 'integer',
        'total_lembur_jam'      => 'float',
        'wajib_masuk'           => 'integer',
    ];

    // ─── Accessor ──────────────────────────────────────────────────────────────

    /** Persentase kehadiran */
    public function getPersentaseHadirAttribute(): float
    {
        if ($this->wajib_masuk === 0) return 0;
        return round(($this->total_hadir / $this->wajib_masuk) * 100, 1);
    }

    public function getNamaBulanAttribute(): string
    {
        return \Carbon\Carbon::create($this->tahun, $this->bulan, 1)
                             ->translatedFormat('F Y');
    }

    // ─── Scope ─────────────────────────────────────────────────────────────────

    public function scopePeriode($query, int $tahun, int $bulan)
    {
        return $query->where('tahun', $tahun)->where('bulan', $bulan);
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }
}