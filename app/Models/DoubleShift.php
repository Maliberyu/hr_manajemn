<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoubleShift extends Model
{
    protected $table    = 'hr_double_shift';
    protected $fillable = [
        'no_pengajuan', 'pegawai_id', 'tanggal',
        'shift_pertama_kode', 'shift_kedua_kode',
        'alasan', 'status',
        'catatan_atasan', 'approved_by', 'approved_at',
        'lembur_id', 'dibuat_oleh',
    ];
    protected $casts = [
        'tanggal'     => 'date',
        'approved_at' => 'datetime',
    ];

    const STATUS = [
        'menunggu_atasan' => 'Menunggu Atasan',
        'disetujui'       => 'Disetujui',
        'ditolak'         => 'Ditolak',
    ];

    // ── Generate nomor ────────────────────────────────────────────────────────
    public static function generateNomor(): string
    {
        $prefix = 'DS/' . now()->format('Ym') . '/';
        $last   = static::where('no_pengajuan', 'like', $prefix . '%')
                        ->orderByDesc('no_pengajuan')->value('no_pengajuan');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    // ── Relasi ────────────────────────────────────────────────────────────────
    public function pegawai(): BelongsTo       { return $this->belongsTo(Pegawai::class, 'pegawai_id'); }
    public function approvedBy(): BelongsTo    { return $this->belongsTo(User::class, 'approved_by'); }
    public function lembur(): BelongsTo        { return $this->belongsTo(Lembur::class, 'lembur_id'); }
    public function shiftPertama(): BelongsTo  { return $this->belongsTo(ShiftMaster::class, 'shift_pertama_kode', 'kode'); }
    public function shiftKedua(): BelongsTo    { return $this->belongsTo(ShiftMaster::class, 'shift_kedua_kode', 'kode'); }

    // ── Helpers ───────────────────────────────────────────────────────────────
    public function bisaApprove(): bool
    {
        if ($this->status !== 'menunggu_atasan') return false;
        $user = auth()->user();
        if ($user->hasRole(['hrd', 'admin'])) return true;
        return $user->hasRole('atasan')
            && AtasanPegawai::isAtasanDari($user->id, $this->pegawai?->nik ?? '');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeMenunggu($q)   { return $q->where('status', 'menunggu_atasan'); }
    public function scopeDisetujui($q)  { return $q->where('status', 'disetujui'); }

    // ── Accessors ─────────────────────────────────────────────────────────────
    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'disetujui' => 'bg-green-100 text-green-700',
            'ditolak'   => 'bg-red-100 text-red-700',
            default     => 'bg-yellow-100 text-yellow-700',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }
}
