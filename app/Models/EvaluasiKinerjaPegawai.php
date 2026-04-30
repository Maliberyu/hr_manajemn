<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluasiKinerjaPegawai extends Model
{
    public $timestamps = false;

    protected $table = 'evaluasi_kinerja_pegawai';

    // Composite PK: id + kode_evaluasi + tahun + bulan
    public $incrementing = false;

    protected $fillable = [
        'id',               // FK ke pegawai.id
        'kode_evaluasi',    // FK ke evaluasi_kinerja.kode_evaluasi
        'tahun',
        'bulan',
        'keterangan',       // catatan hasil evaluasi
    ];

    protected $casts = [
        'tahun' => 'integer',
        'bulan' => 'integer',
    ];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeBulanIni($query)
    {
        return $query->where('tahun', now()->year)
                     ->where('bulan', now()->month);
    }

    public function scopePeriode($query, int $tahun, int $bulan)
    {
        return $query->where('tahun', $tahun)->where('bulan', $bulan);
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id', 'id');
    }

    public function indikator(): BelongsTo
    {
        return $this->belongsTo(EvaluasiKinerja::class, 'kode_evaluasi', 'kode_evaluasi');
    }
}