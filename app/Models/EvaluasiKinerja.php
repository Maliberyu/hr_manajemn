<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluasiKinerja extends Model
{
    public $timestamps = false;

    protected $table = 'evaluasi_kinerja';
    protected $primaryKey = 'kode_evaluasi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_evaluasi',    // contoh: 'E01', 'E02'
        'nama_evaluasi',    // mis: "Kedisiplinan", "Kualitas Kerja"
        'indek',            // bobot indikator
    ];

    protected $casts = [
        'indek' => 'integer',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function hasilPegawai(): HasMany
    {
        return $this->hasMany(EvaluasiKinerjaPegawai::class, 'kode_evaluasi', 'kode_evaluasi');
    }
}