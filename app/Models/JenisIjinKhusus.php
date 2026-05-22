<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisIjinKhusus extends Model
{
    protected $table    = 'hr_jenis_ijin_khusus';
    protected $fillable = [
        'kode', 'nama', 'max_hari', 'wajib_lampiran',
        'butuh_waktu', 'keterangan', 'aktif', 'urutan', 'dibuat_oleh',
    ];
    protected $casts = [
        'wajib_lampiran' => 'boolean',
        'butuh_waktu'    => 'boolean',
        'aktif'          => 'boolean',
        'max_hari'       => 'integer',
    ];

    public function pengajuan(): HasMany
    {
        return $this->hasMany(IjinKhusus::class, 'jenis_ijin_id');
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', true)->orderBy('urutan');
    }

    public function getLabelMaxHariAttribute(): string
    {
        return $this->max_hari ? "Maks {$this->max_hari} hari" : 'Tidak dibatasi';
    }
}
