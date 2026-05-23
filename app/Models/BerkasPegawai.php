<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BerkasPegawai extends Model
{
    protected $table = 'hr_berkas';

    protected $fillable = [
        'jenis_id',
        'nik',
        'nama_file',
        'path',
        'tgl_upload',
        'keterangan',
        'tgl_kadaluarsa',
        'notif_aktif',
    ];

    protected $casts = [
        'tgl_upload'     => 'date',
        'tgl_kadaluarsa' => 'date',
        'notif_aktif'    => 'boolean',
    ];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAkanKadaluarsa($query, int $hari = 30)
    {
        return $query->where('notif_aktif', true)
                     ->whereNotNull('tgl_kadaluarsa')
                     ->whereDate('tgl_kadaluarsa', '<=', today()->addDays($hari));
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getEkstensiAttribute(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->ekstensi === 'pdf';
    }

    public function getHariSisaAttribute(): ?int
    {
        if (!$this->tgl_kadaluarsa) return null;
        return today()->diffInDays($this->tgl_kadaluarsa, false);
    }

    // 'kadaluarsa' | 'urgent' | 'warning' | 'aktif' | null
    public function getStatusKadaluarsaAttribute(): ?string
    {
        if (!$this->tgl_kadaluarsa || !$this->notif_aktif) return null;
        $sisa = $this->hari_sisa;
        if ($sisa < 0) return 'kadaluarsa';
        if ($sisa <= 7) return 'urgent';
        if ($sisa <= 30) return 'warning';
        return 'aktif';
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(MasterBerkasPegawai::class, 'jenis_id');
    }
}
