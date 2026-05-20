<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class HrPelamar extends Model
{
    protected $table    = 'hr_pelamar';
    protected $fillable = [
        'lowongan_id', 'nama', 'email', 'no_hp', 'tanggal_lahir',
        'pendidikan_terakhir', 'pengalaman_tahun', 'sumber',
        'cv_path', 'status', 'catatan', 'tanggal_apply', 'dibuat_oleh',
    ];
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_apply' => 'date',
    ];

    const STATUS = [
        'baru'        => 'Baru',
        'seleksi_cv'  => 'Seleksi CV',
        'interview'   => 'Interview',
        'offering'    => 'Offering',
        'diterima'    => 'Diterima',
        'ditolak'     => 'Ditolak',
    ];

    const SUMBER = [
        'walk_in'      => 'Walk In',
        'referral'     => 'Referral',
        'job_portal'   => 'Job Portal',
        'media_sosial' => 'Media Sosial',
        'lainnya'      => 'Lainnya',
    ];

    public function lowongan(): BelongsTo  { return $this->belongsTo(Lowongan::class, 'lowongan_id'); }
    public function dibuatOleh(): BelongsTo { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function interviews(): HasMany  { return $this->hasMany(HrInterview::class, 'pelamar_id')->orderBy('tahap'); }
    public function offering(): HasOne     { return $this->hasOne(HrOffering::class, 'pelamar_id'); }

    public function getCvUrlAttribute(): ?string
    {
        return $this->cv_path ? Storage::disk('public')->url($this->cv_path) : null;
    }

    public function getLabelStatusAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'baru'       => 'bg-gray-100 text-gray-600',
            'seleksi_cv' => 'bg-blue-100 text-blue-700',
            'interview'  => 'bg-yellow-100 text-yellow-700',
            'offering'   => 'bg-purple-100 text-purple-700',
            'diterima'   => 'bg-green-100 text-green-700',
            'ditolak'    => 'bg-red-100 text-red-700',
            default      => 'bg-gray-100 text-gray-600',
        };
    }

    public function getNilaiInterviewRataAttribute(): ?float
    {
        $nilai = $this->interviews()
            ->where('status', 'selesai')
            ->with('penilaian')
            ->get()
            ->flatMap(fn($i) => $i->penilaian)
            ->pluck('nilai');
        return $nilai->count() ? round($nilai->avg(), 1) : null;
    }
}
