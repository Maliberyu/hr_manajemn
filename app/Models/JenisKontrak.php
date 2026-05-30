<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisKontrak extends Model
{
    protected $table    = 'hr_jenis_kontrak';
    protected $fillable = ['nama', 'durasi_default_bulan', 'is_tetap', 'keterangan'];
    protected $casts    = ['is_tetap' => 'boolean'];

    public function kontraks()
    {
        return $this->hasMany(KontrakKerja::class, 'jenis_kontrak_id');
    }
}
