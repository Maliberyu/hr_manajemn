<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrInterview extends Model
{
    protected $table    = 'hr_interview';
    protected $fillable = [
        'pelamar_id', 'tahap', 'label_tahap', 'jadwal',
        'metode', 'lokasi_atau_link', 'pewawancara_id', 'status', 'catatan',
    ];
    protected $casts = [
        'jadwal' => 'datetime',
        'tahap'  => 'integer',
    ];

    const STATUS  = ['dijadwalkan','selesai','batal'];
    const METODE  = ['offline' => 'Tatap Muka', 'online' => 'Online/Video Call'];

    public function pelamar(): BelongsTo      { return $this->belongsTo(HrPelamar::class, 'pelamar_id'); }
    public function pewawancara(): BelongsTo  { return $this->belongsTo(User::class, 'pewawancara_id'); }
    public function penilaian(): HasMany      { return $this->hasMany(HrPenilaianInterview::class, 'interview_id'); }

    public function getNilaiRataAttribute(): ?float
    {
        $avg = $this->penilaian()->avg('nilai');
        return $avg ? round($avg, 1) : null;
    }

    public function getRekomendasiMayoritasAttribute(): ?string
    {
        return $this->penilaian()
            ->selectRaw('rekomendasi, COUNT(*) as n')
            ->groupBy('rekomendasi')
            ->orderByDesc('n')
            ->value('rekomendasi');
    }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'dijadwalkan' => 'bg-blue-100 text-blue-700',
            'selesai'     => 'bg-green-100 text-green-700',
            'batal'       => 'bg-red-100 text-red-600',
            default       => 'bg-gray-100 text-gray-600',
        };
    }
}
