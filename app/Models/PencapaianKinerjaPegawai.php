<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PencapaianKinerja extends Model
{
    public $timestamps = false;

    protected $table = 'pencapaian_kinerja';
    protected $primaryKey = 'kode_pencapaian';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_pencapaian',
        'nama_pencapaian',
        'indek',
    ];

    protected $casts = [
        'indek' => 'integer',
    ];

    public function hasilPegawai(): HasMany
    {
        return $this->hasMany(PencapaianKinerjaPegawai::class, 'kode_pencapaian', 'kode_pencapaian');
    }
}