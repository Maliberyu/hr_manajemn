<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Umk extends Model
{
    protected $table    = 'hr_umk';
    protected $fillable = ['tahun', 'nominal', 'keterangan'];
    protected $casts    = ['nominal' => 'double'];

    public static function getNominal(int $tahun): float
    {
        return (float) static::where('tahun', $tahun)->value('nominal') ?? 0;
    }
}
