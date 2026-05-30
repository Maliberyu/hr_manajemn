<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class KontrakKerja extends Model
{
    protected $table    = 'hr_kontrak_kerja';
    protected $fillable = [
        'nik', 'jenis_kontrak_id', 'no_kontrak',
        'tgl_mulai', 'tgl_selesai', 'tgl_tanda_tangan',
        'file_kontrak', 'status', 'catatan',
        'dibuat_oleh', 'diperbarui_oleh',
    ];
    protected $casts = [
        'tgl_mulai'         => 'date',
        'tgl_selesai'       => 'date',
        'tgl_tanda_tangan'  => 'date',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────────────

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function jenis()
    {
        return $this->belongsTo(JenisKontrak::class, 'jenis_kontrak_id');
    }

    public function pembuatUser()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeAkanBerakhir($query, int $hari = 30)
    {
        return $query->where('status', 'aktif')
                     ->whereNotNull('tgl_selesai')
                     ->whereBetween('tgl_selesai', [today(), today()->addDays($hari)]);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_kontrak ? Storage::disk('public')->url($this->file_kontrak) : null;
    }

    public function getSisaHariAttribute(): ?int
    {
        if (!$this->tgl_selesai) return null;
        return max(0, today()->diffInDays($this->tgl_selesai, false));
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'aktif'      => 'green',
            'berakhir'   => 'red',
            'diperbarui' => 'blue',
            'dibatalkan' => 'gray',
            default      => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'aktif'      => 'Aktif',
            'berakhir'   => 'Berakhir',
            'diperbarui' => 'Diperbarui',
            'dibatalkan' => 'Dibatalkan',
            default      => $this->status,
        ];
    }
}
