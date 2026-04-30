<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbilDankes extends Model
{
    public $timestamps = false;

    protected $table = 'ambil_dankes';
    public $incrementing = false;

    protected $fillable = [
        'id',           // FK ke pegawai.id
        'tanggal',
        'ktg',          // keterangan kategori penggunaan dana kesehatan
        'dankes',       // nominal yang diambil
    ];

    protected $casts = [
        'tanggal' => 'date',
        'dankes'  => 'double',
    ];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeTahunIni($query)
    {
        return $query->whereYear('tanggal', now()->year);
    }

    public function scopeBulan($query, int $tahun, int $bulan)
    {
        return $query->whereYear('tanggal', $tahun)
                     ->whereMonth('tanggal', $bulan);
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id', 'id');
    }
}