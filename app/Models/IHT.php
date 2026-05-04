<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IHT extends Model
{
    protected $table = 'hr_iht';

    protected $fillable = [
        'nama_training', 'penyelenggara', 'pemateri', 'lokasi',
        'tanggal_mulai', 'tanggal_selesai', 'jam_mulai', 'jam_selesai',
        'deskripsi', 'kuota', 'status',
        'penandatangan_nama', 'penandatangan_jabatan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    const STATUS = [
        'draft'      => 'Draft',
        'aktif'      => 'Aktif',
        'selesai'    => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    const STATUS_COLOR = [
        'draft'      => 'bg-gray-100 text-gray-600',
        'aktif'      => 'bg-blue-100 text-blue-700',
        'selesai'    => 'bg-green-100 text-green-700',
        'dibatalkan' => 'bg-red-100 text-red-600',
    ];

    public function getDurasiHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function getSisaKuotaAttribute(): ?int
    {
        if (!$this->kuota) return null;
        return max(0, $this->kuota - $this->peserta()->count());
    }

    // ── Nomor sertifikat berikutnya ────────────────────────────────────────────
    public static function generateNomorSertifikat(): string
    {
        $tahun  = now()->year;
        $urutan = IHTPeserta::whereYear('sertifikat_at', $tahun)->count() + 1;
        return sprintf('SERT/IHT/%d/%03d', $tahun, $urutan);
    }

    // ── Relasi ─────────────────────────────────────────────────────────────────
    public function peserta(): HasMany
    {
        return $this->hasMany(IHTPeserta::class, 'iht_id');
    }

    public function pesertaHadir(): HasMany
    {
        return $this->hasMany(IHTPeserta::class, 'iht_id')
                    ->whereIn('status', ['hadir', 'selesai']);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id');
    }
}
