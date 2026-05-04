<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenGaji extends Model
{
    protected $table    = 'hr_komponen_gaji';
    protected $fillable = ['nama', 'jenis', 'tipe', 'nilai', 'urutan', 'aktif', 'keterangan'];
    protected $casts    = ['nilai' => 'double', 'aktif' => 'boolean'];

    public function hitungNilai(float $gajiPokok, float $umkNominal = 0): float
    {
        return match($this->tipe) {
            'persen_gapok' => round($gajiPokok * $this->nilai / 100),
            'persen_umk'   => round($umkNominal * $this->nilai / 100),
            default        => $this->nilai,
        };
    }
}
