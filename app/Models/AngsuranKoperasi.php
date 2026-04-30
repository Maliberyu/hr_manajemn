<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AngsuranKoperasi extends Model
{
    public $timestamps = false;

    protected $table = 'angsuran_koperasi';
    public $incrementing = false;

    protected $fillable = [
        'id',               // FK ke pegawai.id
        'tanggal_pinjam',
        'tanggal_angsur',
        'pokok',            // pokok angsuran
        'jasa',             // bunga / jasa
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_angsur' => 'date',
        'pokok'          => 'double',
        'jasa'           => 'double',
    ];

    // ─── Accessor ──────────────────────────────────────────────────────────────

    /** Total pokok + jasa */
    public function getTotalAttribute(): float
    {
        return $this->pokok + $this->jasa;
    }

    // ─── Scope ─────────────────────────────────────────────────────────────────

    public function scopeBulanAngsur($query, int $tahun, int $bulan)
    {
        return $query->whereYear('tanggal_angsur', $tahun)
                     ->whereMonth('tanggal_angsur', $bulan);
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id', 'id');
    }
}