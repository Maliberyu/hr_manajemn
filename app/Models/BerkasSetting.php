<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerkasSetting extends Model
{
    protected $table    = 'hr_berkas_setting';
    protected $fillable = ['hari_notif_1', 'hari_notif_2'];

    protected $casts = [
        'hari_notif_1' => 'integer',
        'hari_notif_2' => 'integer',
    ];

    public static function get(): static
    {
        return static::first() ?? new static(['hari_notif_1' => 30, 'hari_notif_2' => 7]);
    }
}
