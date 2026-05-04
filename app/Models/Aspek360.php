<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aspek360 extends Model
{
    protected $table    = 'hr_kinerja_360_aspek';
    protected $fillable = ['dimensi_id', 'nama', 'urutan', 'aktif'];
    protected $casts    = ['aktif' => 'boolean'];

    public function dimensi(): BelongsTo
    {
        return $this->belongsTo(Dimensi360::class, 'dimensi_id');
    }
}
