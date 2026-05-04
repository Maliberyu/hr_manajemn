<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Komentar360 extends Model
{
    protected $table    = 'hr_penilaian_360_komentar';
    protected $fillable = ['penilaian_id', 'rater_id', 'kekuatan', 'area_pengembangan', 'saran'];

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian360::class, 'penilaian_id');
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(Rater360::class, 'rater_id');
    }
}
