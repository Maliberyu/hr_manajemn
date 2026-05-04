<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianPrestasiNilai extends Model
{
    protected $table    = 'hr_penilaian_prestasi_nilai';
    protected $fillable = ['penilaian_id', 'kriteria_id', 'nilai', 'catatan'];

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(PenilaianPrestasi::class, 'penilaian_id');
    }

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(KriteriaKinerja::class, 'kriteria_id');
    }

    public function getLabelAttribute(): string
    {
        return PenilaianPrestasi::SKALA[$this->nilai] ?? '-';
    }
}
