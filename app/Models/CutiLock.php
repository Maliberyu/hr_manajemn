<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiLock extends Model
{
    protected $table    = 'hr_cuti_lock';
    protected $fillable = [
        'is_locked', 'alasan_kunci',
        'dikunci_oleh', 'dikunci_at',
        'dibuka_oleh',  'dibuka_at',
    ];
    protected $casts = [
        'is_locked'  => 'boolean',
        'dikunci_at' => 'datetime',
        'dibuka_at'  => 'datetime',
    ];

    public function dikunciOleh(): BelongsTo { return $this->belongsTo(User::class, 'dikunci_oleh'); }
    public function dibukaOleh(): BelongsTo  { return $this->belongsTo(User::class, 'dibuka_oleh'); }

    /** Ambil/buat satu baris setting lock global */
    public static function status(): static
    {
        return static::firstOrCreate([], ['is_locked' => false]);
    }

    public static function isLocked(): bool
    {
        return static::status()->is_locked;
    }

    public function kunci(string $alasan): void
    {
        $this->update([
            'is_locked'    => true,
            'alasan_kunci' => $alasan,
            'dikunci_oleh' => auth()->id(),
            'dikunci_at'   => now(),
            'dibuka_oleh'  => null,
            'dibuka_at'    => null,
        ]);
    }

    public function buka(): void
    {
        $this->update([
            'is_locked'   => false,
            'dibuka_oleh' => auth()->id(),
            'dibuka_at'   => now(),
        ]);
    }
}
