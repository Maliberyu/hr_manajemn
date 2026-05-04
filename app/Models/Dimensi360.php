<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dimensi360 extends Model
{
    protected $table    = 'hr_kinerja_360_dimensi';
    protected $fillable = ['nama', 'bobot', 'untuk_rater', 'urutan', 'aktif'];
    protected $casts    = ['bobot' => 'double', 'aktif' => 'boolean', 'untuk_rater' => 'array'];

    public function aspek(): HasMany
    {
        return $this->hasMany(Aspek360::class, 'dimensi_id')->where('aktif', true)->orderBy('urutan');
    }
}
