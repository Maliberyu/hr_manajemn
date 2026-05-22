<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TukarShift extends Model
{
    protected $table    = 'hr_tukar_shift';
    protected $fillable = [
        'no_pengajuan', 'pemohon_id', 'rekan_id',
        'tgl_shift_pemohon', 'tgl_shift_rekan',
        'shift_pemohon_kode', 'shift_rekan_kode',
        'alasan', 'status',
        'catatan_rekan', 'approved_rekan_by', 'approved_rekan_at',
        'catatan_atasan', 'approved_atasan_by', 'approved_atasan_at',
        'dibuat_oleh',
    ];
    protected $casts = [
        'tgl_shift_pemohon'  => 'date',
        'tgl_shift_rekan'    => 'date',
        'approved_rekan_at'  => 'datetime',
        'approved_atasan_at' => 'datetime',
    ];

    const STATUS = [
        'menunggu_rekan'   => 'Menunggu Rekan',
        'menunggu_atasan'  => 'Menunggu Atasan',
        'disetujui'        => 'Disetujui',
        'ditolak_rekan'    => 'Ditolak Rekan',
        'ditolak_atasan'   => 'Ditolak Atasan',
    ];

    // ── Generate nomor pengajuan ───────────────────────────────────────────────
    public static function generateNomor(): string
    {
        $prefix = 'TS/' . now()->format('Ym') . '/';
        $last   = static::where('no_pengajuan', 'like', $prefix . '%')
                        ->orderByDesc('no_pengajuan')->value('no_pengajuan');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    // ── Relasi ────────────────────────────────────────────────────────────────
    public function pemohon(): BelongsTo        { return $this->belongsTo(User::class, 'pemohon_id'); }
    public function rekan(): BelongsTo          { return $this->belongsTo(User::class, 'rekan_id'); }
    public function approvedRekanBy(): BelongsTo   { return $this->belongsTo(User::class, 'approved_rekan_by'); }
    public function approvedAtasanBy(): BelongsTo  { return $this->belongsTo(User::class, 'approved_atasan_by'); }
    public function shiftPemohon(): BelongsTo   { return $this->belongsTo(ShiftMaster::class, 'shift_pemohon_kode', 'kode'); }
    public function shiftRekan(): BelongsTo     { return $this->belongsTo(ShiftMaster::class, 'shift_rekan_kode', 'kode'); }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function bisaApproveRekan(): bool
    {
        return $this->status === 'menunggu_rekan'
            && auth()->id() === $this->rekan_id;
    }

    public function bisaApproveAtasan(): bool
    {
        if ($this->status !== 'menunggu_atasan') return false;
        $user = auth()->user();
        if ($user->hasRole(['hrd', 'admin'])) return true;
        return $user->hasRole('atasan')
            && AtasanPegawai::isAtasanDari($user->id, $this->pemohon?->pegawai?->nik ?? '');
    }

    public function ditolak(): bool
    {
        return in_array($this->status, ['ditolak_rekan', 'ditolak_atasan']);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeMenunggu($q)    { return $q->whereIn('status', ['menunggu_rekan','menunggu_atasan']); }
    public function scopeDisetujui($q)   { return $q->where('status', 'disetujui'); }

    // ── Accessors ─────────────────────────────────────────────────────────────
    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'disetujui'                        => 'bg-green-100 text-green-700',
            'ditolak_rekan', 'ditolak_atasan'  => 'bg-red-100 text-red-700',
            default                            => 'bg-yellow-100 text-yellow-700',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
