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
        'check_in_at', 'check_out_at',
    ];

    protected $casts = [
        'sertifikat_at' => 'datetime',
        'check_in_at'   => 'datetime',
        'check_out_at'  => 'datetime',
        'nilai'         => 'float',
    ];

    public function getDurasiHadirAttribute(): ?string
    {
        if (!$this->check_in_at || !$this->check_out_at) return null;
        $menit = $this->check_in_at->diffInMinutes($this->check_out_at);
        $j = intdiv($menit, 60);
        $m = $menit % 60;
        return $j > 0 ? "{$j}j " . ($m > 0 ? "{$m}m" : '') : "{$m}m";
    }

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
