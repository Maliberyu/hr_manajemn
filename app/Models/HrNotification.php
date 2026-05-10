<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HrNotification extends Model
{
    protected $table    = 'hr_notifications';
    protected $fillable = ['user_id', 'type', 'title', 'message', 'link', 'read_at'];
    protected $casts    = ['read_at' => 'datetime'];

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }

    // ─── Static helper: buat notifikasi ──────────────────────────────────────

    public static function kirim(int $userId, string $type, string $title, string $message, string $link = ''): void
    {
        try {
            static::create([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'link'    => $link,
            ]);
        } catch (\Throwable) {
            // Tidak crash app jika tabel belum ada
        }
    }

    public static function kirimKeAtasan(string $nik, string $type, string $title, string $message, string $link = ''): void
    {
        $atasanUserId = AtasanPegawai::where('nik', $nik)->value('user_id');
        if ($atasanUserId) {
            static::kirim($atasanUserId, $type, $title, $message, $link);
        }
    }

    public static function kirimKeHrd(string $type, string $title, string $message, string $link = ''): void
    {
        User::where('role', 'hrd')->orWhere('role', 'admin')
            ->pluck('id')
            ->each(fn($id) => static::kirim($id, $type, $title, $message, $link));
    }

    public static function kirimKePegawai(string $nik, string $type, string $title, string $message, string $link = ''): void
    {
        $userId = User::where('nik', $nik)->orWhereHas('pegawai', fn($q) => $q->where('nik', $nik))->value('id');
        if ($userId) {
            static::kirim($userId, $type, $title, $message, $link);
        }
    }
}
