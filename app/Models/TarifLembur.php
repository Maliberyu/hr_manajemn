<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarifLembur extends Model
{
    protected $table = 'hr_tarif_lembur';

    protected $fillable = ['dep_id', 'tarif_hb', 'tarif_hr'];

    protected $casts = [
        'tarif_hb' => 'double',
        'tarif_hr' => 'double',
    ];

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'dep_id', 'dep_id');
    }

    public static function getForDep(?string $depId): ?self
    {
        if (!$depId) return null;
        return static::where('dep_id', $depId)->first();
    }
}
