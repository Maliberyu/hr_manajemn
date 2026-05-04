<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubIndikatorKinerja extends Model
{
    protected $table    = 'hr_kinerja_sub_indikator';
    protected $fillable = ['kriteria_id', 'nama', 'urutan', 'aktif'];
    protected $casts    = ['aktif' => 'boolean'];

    public function kriteria(): BelongsTo
    {
        return $this->belongsTo(KriteriaKinerja::class, 'kriteria_id');
    }
}
