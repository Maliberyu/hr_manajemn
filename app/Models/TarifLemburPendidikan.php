<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifLemburPendidikan extends Model
{
    protected $table = 'hr_tarif_lembur_pendidikan';

    protected $fillable = ['pendidikan', 'label', 'tarif_hb', 'tarif_hr'];

    protected $casts = [
        'tarif_hb' => 'double',
        'tarif_hr' => 'double',
    ];

    public static function getForPendidikan(?string $pendidikan): ?self
    {
        if (!$pendidikan) return null;
        return static::where('pendidikan', $pendidikan)->first();
    }
}
