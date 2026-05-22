<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class IjinKhusus extends Model
{
    protected $table    = 'hr_pengajuan_ijin_khusus';
    protected $fillable = [
        'no_pengajuan', 'nik', 'pegawai_id', 'jenis_ijin_id',
        'tanggal_mulai', 'tanggal_akhir', 'jam_mulai', 'jam_selesai',
        'durasi_hari', 'durasi_menit', 'alasan', 'file_lampiran', 'status',
        'catatan_atasan', 'catatan_hrd',
        'approved_atasan_by', 'approved_atasan_at',
        'approved_hrd_by',    'approved_hrd_at',
        'dibuat_oleh',
    ];
    protected $casts = [
        'tanggal_mulai'      => 'date',
        'tanggal_akhir'      => 'date',
        'approved_atasan_at' => 'datetime',
        'approved_hrd_at'    => 'datetime',
    ];

    const STATUS = [
        'Menunggu Atasan', 'Menunggu HRD',
        'Disetujui', 'Ditolak Atasan', 'Ditolak HRD',
    ];

    public static function generateNomor(): string
    {
        $prefix = 'IK/' . now()->format('Ym') . '/';
        $last   = static::where('no_pengajuan', 'like', $prefix . '%')
                        ->orderByDesc('no_pengajuan')->value('no_pengajuan');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    public function jenis(): BelongsTo        { return $this->belongsTo(JenisIjinKhusus::class, 'jenis_ijin_id'); }
    public function pegawai(): BelongsTo       { return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id'); }
    public function approvedAtasanBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_atasan_by'); }
    public function approvedHrdBy(): BelongsTo    { return $this->belongsTo(User::class, 'approved_hrd_by'); }

    public function getLampiranUrlAttribute(): ?string
    {
        return $this->file_lampiran ? Storage::disk('public')->url($this->file_lampiran) : null;
    }

    public function getDurasiLabelAttribute(): string
    {
        if ($this->durasi_menit) {
            $j = intdiv($this->durasi_menit, 60);
            $m = $this->durasi_menit % 60;
            return ($j > 0 ? "{$j} jam " : '') . ($m > 0 ? "{$m} menit" : '');
        }
        return $this->durasi_hari ? "{$this->durasi_hari} hari" : '—';
    }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'Disetujui'      => 'bg-green-100 text-green-700',
            'Ditolak Atasan',
            'Ditolak HRD'    => 'bg-red-100 text-red-700',
            default          => 'bg-yellow-100 text-yellow-700',
        };
    }

    public function bisaApproveAtasan(): bool
    {
        return $this->status === 'Menunggu Atasan'
            && AtasanPegawai::isAtasanDari(auth()->id(), $this->nik);
    }

    public function bisaApproveHrd(): bool
    {
        return $this->status === 'Menunggu HRD'
            && auth()->user()->hasRole(['hrd', 'admin']);
    }

    public function scopeMenungguApproval($q)  { return $q->whereIn('status', ['Menunggu Atasan','Menunggu HRD']); }
    public function scopeMenungguAtasan($q)    { return $q->where('status', 'Menunggu Atasan'); }
    public function scopeMenungguHrd($q)       { return $q->where('status', 'Menunggu HRD'); }
    public function scopeDisetujui($q)         { return $q->where('status', 'Disetujui'); }
}
