<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class IHTPeserta extends Model
{
    protected $table = 'hr_iht_peserta';

    protected $fillable = [
        'iht_id', 'pegawai_id', 'status', 'nilai',
        'nomor_sertifikat', 'sertifikat_path', 'sertifikat_at',
    ];

    protected $casts = [
        'sertifikat_at' => 'datetime',
        'nilai'         => 'float',
    ];

    const STATUS = [
        'terdaftar'   => 'Terdaftar',
        'hadir'       => 'Hadir',
        'tidak_hadir' => 'Tidak Hadir',
        'selesai'     => 'Selesai',
    ];

    public function sudahSertifikat(): bool
    {
        return !is_null($this->nomor_sertifikat);
    }

    public function getSertifikatUrlAttribute(): ?string
    {
        return $this->sertifikat_path
            ? Storage::url($this->sertifikat_path)
            : null;
    }

    // ── Relasi ─────────────────────────────────────────────────────────────────
    public function iht(): BelongsTo
    {
        return $this->belongsTo(IHT::class, 'iht_id');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
