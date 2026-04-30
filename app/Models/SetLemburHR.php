<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tarif lembur HARI RAYA / LIBUR NASIONAL
 */
class SetLemburHR extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $table = 'set_lemburhr';
    protected $primaryKey = 'tnj';
    protected $keyType = 'float';

    protected $fillable = ['tnj'];

    protected $casts = ['tnj' => 'double'];

    public static function tarifAktif(): float
    {
        return static::first()?->tnj ?? 0;
    }
}