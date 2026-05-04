<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rater360 extends Model
{
    protected $table    = 'hr_penilaian_360_rater';
    protected $fillable = ['penilaian_id', 'user_id', 'hubungan', 'nama_rater', 'is_anonim', 'submitted_at'];
    protected $casts    = ['is_anonim' => 'boolean', 'submitted_at' => 'datetime'];

    const HUBUNGAN = [
        'atasan'  => 'Atasan Langsung',
        'rekan'   => 'Rekan Sejawat',
        'bawahan' => 'Bawahan',
        'self'    => 'Diri Sendiri',
    ];

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(Penilaian360::class, 'penilaian_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai360::class, 'rater_id');
    }

    public function komentar(): HasOne
    {
        return $this->hasOne(Komentar360::class, 'rater_id');
    }

    public function sudahSubmit(): bool
    {
        return !is_null($this->submitted_at);
    }

    public function getNamaDisplayAttribute(): string
    {
        if ($this->is_anonim && $this->hubungan !== 'self') {
            return self::HUBUNGAN[$this->hubungan] . ' (Anonim)';
        }
        return $this->nama_rater ?? $this->user?->nama ?? 'Unknown';
    }
}
