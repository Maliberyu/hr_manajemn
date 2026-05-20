<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RekrutmenRequest extends Model
{
    protected $table    = 'hr_rekrutmen_request';
    protected $fillable = [
        'no_request', 'user_id', 'posisi', 'departemen', 'jumlah',
        'alasan', 'tanggal_dibutuhkan', 'status',
        'catatan_hrd', 'reviewed_by', 'reviewed_at',
    ];
    protected $casts = [
        'tanggal_dibutuhkan' => 'date',
        'reviewed_at'        => 'datetime',
        'jumlah'             => 'integer',
    ];

    const STATUS = [
        'menunggu_hrd'       => 'Menunggu HRD',
        'menunggu_direktur'  => 'Menunggu Direktur',
        'disetujui'          => 'Disetujui',
        'ditolak'            => 'Ditolak',
    ];

    public static function generateNomor(): string
    {
        $prefix = 'REQ/' . now()->format('Ym') . '/';
        $last   = static::where('no_request', 'like', $prefix . '%')
                        ->orderByDesc('no_request')->value('no_request');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function departemenRef(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'departemen', 'dep_id');
    }

    public function lowongan(): HasMany
    {
        return $this->hasMany(Lowongan::class, 'request_id');
    }

    public function scopeMenunggu($q)      { return $q->whereIn('status', ['menunggu_hrd','menunggu_direktur']); }
    public function scopeMenungguHrd($q)   { return $q->where('status', 'menunggu_hrd'); }
    public function scopeDisetujui($q)     { return $q->where('status', 'disetujui'); }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'menunggu_hrd', 'menunggu_direktur' => 'bg-yellow-100 text-yellow-700',
            'disetujui' => 'bg-green-100 text-green-700',
            'ditolak'   => 'bg-red-100 text-red-700',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
