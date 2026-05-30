<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Beasiswa extends Model
{
    protected $table = 'hr_beasiswa';

    protected $fillable = [
        'nik', 'jenis', 'nama_program', 'institusi', 'kota',
        'biaya_diajukan', 'biaya_disetujui',
        'tgl_mulai', 'tgl_selesai', 'status',
        'catatan_pengaju', 'catatan_atasan', 'catatan_hrd',
        'file_proposal', 'file_hasil',
        'diajukan_oleh', 'approve_atasan_oleh', 'approve_hrd_oleh',
    ];

    protected $casts = [
        'tgl_mulai'        => 'date',
        'tgl_selesai'      => 'date',
        'biaya_diajukan'   => 'float',
        'biaya_disetujui'  => 'float',
    ];

    public static function jenisLabel(): array
    {
        return [
            'tugas_belajar' => 'Tugas Belajar',
            'ijin_belajar'  => 'Izin Belajar',
            'kursus'        => 'Kursus/Pelatihan',
            'sertifikasi'   => 'Sertifikasi',
            'lainnya'       => 'Lainnya',
        ];
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getJenisLabelAttribute(): string
    {
        return self::jenisLabel()[$this->jenis] ?? $this->jenis;
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'menunggu_atasan' => 'Menunggu Atasan',
            'menunggu_hrd'    => 'Menunggu HRD',
            'disetujui'       => 'Disetujui',
            'ditolak'         => 'Ditolak',
            'selesai'         => 'Selesai',
            default           => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'menunggu_atasan' => 'yellow',
            'menunggu_hrd'    => 'blue',
            'disetujui'       => 'green',
            'ditolak'         => 'red',
            'selesai'         => 'gray',
            default           => 'gray',
        };
    }

    public function getFileProposalUrlAttribute(): ?string
    {
        if (!$this->file_proposal) return null;
        return Storage::disk('public')->url($this->file_proposal);
    }

    public function getFileHasilUrlAttribute(): ?string
    {
        if (!$this->file_hasil) return null;
        return Storage::disk('public')->url($this->file_hasil);
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeMenungguAtasan($query)
    {
        return $query->where('status', 'menunggu_atasan');
    }

    public function scopeMenungguHrd($query)
    {
        return $query->where('status', 'menunggu_hrd');
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['menunggu_atasan','menunggu_hrd','disetujui']);
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function pengajuUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function approveAtasanUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approve_atasan_oleh');
    }

    public function approveHrdUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approve_hrd_oleh');
    }
}
