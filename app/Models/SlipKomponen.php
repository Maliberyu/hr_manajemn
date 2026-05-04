<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlipKomponen extends Model
{
    protected $table    = 'hr_slip_komponen';
    protected $fillable = ['slip_id', 'nama', 'jenis', 'nilai', 'urutan', 'sumber'];
    protected $casts    = ['nilai' => 'double'];

    public function slip(): BelongsTo
    {
        return $this->belongsTo(SlipGaji::class, 'slip_id');
    }
}
