<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TrainingEksternal extends Model
{
    protected $table = 'hr_training_eksternal';

    protected $fillable = [
        'pegawai_id', 'submitted_by',
        'nama_training', 'lembaga', 'lokasi',
        'tanggal_mulai', 'tanggal_selesai', 'biaya', 'deskripsi',
        'mode', 'status',
        'atasan_id', 'catatan_atasan', 'approved_atasan_by', 'approved_atasan_at',
        'catatan_hrd', 'approved_hrd_by', 'approved_hrd_at',
        'nomor_sertifikat', 'masa_berlaku', 'file_sertifikat', 'uploaded_at',
        'validated_by', 'validated_at',
    ];

    protected $casts = [
        'tanggal_mulai'      => 'date',
        'tanggal_selesai'    => 'date',
        'masa_berlaku'       => 'date',
        'approved_atasan_at' => 'datetime',
        'approved_hrd_at'    => 'datetime',
        'uploaded_at'        => 'datetime',
        'validated_at'       => 'datetime',
        'biaya'              => 'float',
    ];

    const STATUS_LABEL = [
        'menunggu_atasan'  => 'Menunggu Atasan',
        'menunggu_hrd'     => 'Menunggu HRD',
        'disetujui'        => 'Disetujui',
        'ditolak_atasan'   => 'Ditolak Atasan',
        'ditolak_hrd'      => 'Ditolak HRD',
        'menunggu_validasi'=> 'Menunggu Validasi',
        'tervalidasi'      => 'Tervalidasi',
    ];

    const STATUS_COLOR = [
        'menunggu_atasan'  => 'bg-yellow-100 text-yellow-700',
        'menunggu_hrd'     => 'bg-orange-100 text-orange-700',
        'disetujui'        => 'bg-blue-100 text-blue-700',
        'ditolak_atasan'   => 'bg-red-100 text-red-600',
        'ditolak_hrd'      => 'bg-red-100 text-red-600',
        'menunggu_validasi'=> 'bg-purple-100 text-purple-700',
        'tervalidasi'      => 'bg-green-100 text-green-700',
    ];

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function bisaApproveAtasan(): bool
    {
        return $this->status === 'menunggu_atasan'
            && auth()->user()->hasRole(['atasan', 'hrd', 'admin']);
    }

    public function bisaApproveHrd(): bool
    {
        return $this->status === 'menunggu_hrd'
            && auth()->user()->hasRole(['hrd', 'admin']);
    }

    public function bisaUploadSertifikat(): bool
    {
        return $this->status === 'disetujui'
            && auth()->id() === $this->submitted_by;
    }

    public function bisaValidasiHr(): bool
    {
        return $this->status === 'menunggu_validasi'
            && auth()->user()->hasRole(['hrd', 'admin']);
    }

    public function getFileSertifikatUrlAttribute(): ?string
    {
        return $this->file_sertifikat
            ? Storage::url($this->file_sertifikat)
            : null;
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->masa_berlaku
            && $this->masa_berlaku->isFuture()
            && $this->masa_berlaku->diffInDays(now()) <= $days;
    }

    public function isExpired(): bool
    {
        return $this->masa_berlaku && $this->masa_berlaku->isPast();
    }

    // ── Relasi ─────────────────────────────────────────────────────────────────
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function approvedAtasanBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_atasan_by');
    }

    public function approvedHrdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_hrd_by');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
