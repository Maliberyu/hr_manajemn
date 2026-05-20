<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrPenilaianInterview extends Model
{
    protected $table    = 'hr_penilaian_interview';
    protected $fillable = ['interview_id', 'penilai_id', 'nilai', 'rekomendasi', 'catatan'];
    protected $casts    = ['nilai' => 'float'];

    const REKOMENDASI = [
        'lanjutkan'      => 'Lanjutkan',
        'pertimbangkan'  => 'Pertimbangkan',
        'tolak'          => 'Tolak',
    ];

    public function interview(): BelongsTo { return $this->belongsTo(HrInterview::class, 'interview_id'); }
    public function penilai(): BelongsTo   { return $this->belongsTo(User::class, 'penilai_id'); }

    public function getBadgeRekomendasiAttribute(): string
    {
        return match($this->rekomendasi) {
            'lanjutkan'     => 'bg-green-100 text-green-700',
            'pertimbangkan' => 'bg-yellow-100 text-yellow-700',
            'tolak'         => 'bg-red-100 text-red-700',
            default         => 'bg-gray-100 text-gray-600',
        };
    }
}
