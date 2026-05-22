<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiUnlockRequest extends Model
{
    protected $table    = 'hr_cuti_unlock_request';
    protected $fillable = [
        'no_request', 'user_id',
        'tgl_rencana_mulai', 'tgl_rencana_akhir',
        'alasan', 'status', 'catatan_hrd',
        'reviewed_by', 'reviewed_at',
    ];
    protected $casts = [
        'tgl_rencana_mulai' => 'date',
        'tgl_rencana_akhir' => 'date',
        'reviewed_at'       => 'datetime',
    ];

    const STATUS = [
        'menunggu'  => 'Menunggu Review',
        'disetujui' => 'Disetujui',
        'ditolak'   => 'Ditolak',
    ];

    public static function generateNomor(): string
    {
        $prefix = 'UCR/' . now()->format('Ym') . '/';
        $last   = static::where('no_request', 'like', $prefix . '%')
                        ->orderByDesc('no_request')->value('no_request');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo      { return $this->belongsTo(User::class, 'user_id'); }
    public function reviewer(): BelongsTo  { return $this->belongsTo(User::class, 'reviewed_by'); }

    /** Cek apakah user_id punya request disetujui untuk periode tertentu */
    public static function isDisetujui(int $userId): bool
    {
        return static::where('user_id', $userId)
            ->where('status', 'disetujui')
            ->where('tgl_rencana_akhir', '>=', today())
            ->exists();
    }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'disetujui' => 'bg-green-100 text-green-700',
            'ditolak'   => 'bg-red-100 text-red-700',
            default     => 'bg-yellow-100 text-yellow-700',
        };
    }

    public function scopeMenunggu($q)  { return $q->where('status', 'menunggu'); }
    public function scopeDisetujui($q) { return $q->where('status', 'disetujui'); }
}
