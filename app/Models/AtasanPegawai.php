<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtasanPegawai extends Model
{
    protected $table = 'hr_atasan_pegawai';

    protected $fillable = ['nik', 'user_id', 'keterangan'];

    // ── Cari atasan dari NIK karyawan ──────────────────────────────────────────
    public static function cariAtasan(string $nik): ?User
    {
        $record = static::where('nik', $nik)->first();
        return $record?->atasan;
    }

    // ── Ambil user_id atasan dari NIK, null jika belum diset ──────────────────
    public static function userId(string $nik): ?int
    {
        return static::where('nik', $nik)->value('user_id');
    }

    // ── Cek apakah user tertentu adalah atasan dari NIK ───────────────────────
    public static function isAtasanDari(int $userId, string $nik): bool
    {
        return static::where('nik', $nik)->where('user_id', $userId)->exists();
    }

    // ── NIK-NIK yang menjadi bawahan dari user_id tertentu ───────────────────
    public static function nikBawahan(int $userId): array
    {
        return static::where('user_id', $userId)->pluck('nik')->toArray();
    }

    // ── Relasi ─────────────────────────────────────────────────────────────────
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
