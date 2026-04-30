<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tarif lembur HARI BIASA
 * Tabel hanya punya satu kolom: tnj (nominal tunjangan per jam)
 */
class SetLemburHB extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $table = 'set_lemburhb';
    protected $primaryKey = 'tnj';
    protected $keyType = 'float';

    protected $fillable = ['tnj'];

    protected $casts = ['tnj' => 'double'];

    /** Ambil tarif aktif (baris pertama / tunggal) */
    public static function tarifAktif(): float
    {
        return static::first()?->tnj ?? 0;
    }
}