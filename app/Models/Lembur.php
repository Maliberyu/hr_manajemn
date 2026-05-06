<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AtasanPegawai;

class Lembur extends Model
{
    protected $table = 'lembur';

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'durasi_jam',
        'jenis',                  // HB (hari biasa) | HR (hari raya/libur)
        'keterangan',
        'status',
        'nominal',
        // Approval level 1 — Atasan
        'catatan_atasan',
        'approved_atasan_by',
        'approved_atasan_at',
        // Approval level 2 — HRD
        'catatan_hrd',
        'approved_hrd_by',
        'approved_hrd_at',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'durasi_jam'         => 'float',
        'nominal'            => 'double',
        'approved_atasan_at' => 'datetime',
        'approved_hrd_at'    => 'datetime',
    ];

    const STATUS = [
        'Menunggu Atasan',
        'Menunggu HRD',
        'Disetujui',
        'Ditolak Atasan',
        'Ditolak HRD',
    ];

    const JENIS = ['HB' => 'Hari Biasa', 'HR' => 'Hari Raya/Libur'];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeMenungguAtasan($query)
    {
        return $query->where('status', 'Menunggu Atasan');
    }

    public function scopeMenungguHrd($query)
    {
        return $query->where('status', 'Menunggu HRD');
    }

    public function scopeMenungguApproval($query)
    {
        return $query->whereIn('status', ['Menunggu Atasan', 'Menunggu HRD']);
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'Disetujui');
    }

    public function scopeBulan($query, int $tahun, int $bulan)
    {
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }

    // ─── Permission helpers ────────────────────────────────────────────────────

    public function bisaApproveAtasan(): bool
    {
        if ($this->status !== 'Menunggu Atasan') return false;
        $user = auth()->user();
        if ($user->hasRole(['hrd', 'admin'])) return true;
        if (!$user->hasRole('atasan')) return false;
        $nik = $this->pegawai?->nik;
        return $nik && AtasanPegawai::isAtasanDari($user->id, $nik);
    }

    public function bisaApproveHrd(): bool
    {
        return $this->status === 'Menunggu HRD'
            && auth()->user()->hasRole(['hrd', 'admin']);
    }

    public function ditolak(): bool
    {
        return in_array($this->status, ['Ditolak Atasan', 'Ditolak HRD']);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Menunggu Atasan' => 'yellow',
            'Menunggu HRD'    => 'blue',
            'Disetujui'       => 'green',
            'Ditolak Atasan',
            'Ditolak HRD'     => 'red',
            default           => 'gray',
        };
    }

    public function getDurasiLabelAttribute(): string
    {
        $total = (float) $this->durasi_jam;
        $jam   = (int) $total;
        $mnt   = (int)(($total - $jam) * 60);
        return "{$jam}j" . ($mnt > 0 ? " {$mnt}m" : "");
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    public function approverAtasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_atasan_by', 'id');
    }

    public function approverHrd(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_hrd_by', 'id');
    }
}
