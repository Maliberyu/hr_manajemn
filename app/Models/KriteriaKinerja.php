<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KriteriaKinerja extends Model
{
    protected $table    = 'hr_kinerja_kriteria';
    protected $fillable = ['nama', 'bobot', 'urutan', 'aktif'];
    protected $casts    = ['bobot' => 'double', 'aktif' => 'boolean'];

    public function subIndikator(): HasMany
    {
        return $this->hasMany(SubIndikatorKinerja::class, 'kriteria_id')->orderBy('urutan');
    }

    public function penilaianNilai(): HasMany
    {
        return $this->hasMany(PenilaianPrestasiNilai::class, 'kriteria_id');
    }
}
