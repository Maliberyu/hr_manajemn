<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterGaji extends Model
{
    protected $table    = 'hr_master_gaji';
    protected $fillable = ['golongan', 'pendidikan', 'umk_tahun', 'gaji_pokok', 'tunjangan_jabatan', 'keterangan'];
    protected $casts    = ['gaji_pokok' => 'double', 'tunjangan_jabatan' => 'double'];

    /** Cari master gaji yang sesuai: pendidikan spesifik dulu, fallback ke null */
    public static function cariUntuk(string $golongan, int $umkTahun, ?string $pendidikan = null): ?self
    {
        return static::where('golongan', $golongan)
            ->where('umk_tahun', $umkTahun)
            ->where(fn($q) => $q->where('pendidikan', $pendidikan)->orWhereNull('pendidikan'))
            ->orderByRaw("CASE WHEN pendidikan IS NOT NULL THEN 0 ELSE 1 END")
            ->first();
    }
}
