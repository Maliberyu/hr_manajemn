<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai360 extends Model
{
    protected $table    = 'hr_penilaian_360_nilai';
    protected $fillable = ['rater_id', 'aspek_id', 'nilai'];

    public function rater(): BelongsTo
    {
        return $this->belongsTo(Rater360::class, 'rater_id');
    }

    public function aspek(): BelongsTo
    {
        return $this->belongsTo(Aspek360::class, 'aspek_id');
    }
}
