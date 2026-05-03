<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanCuti extends Model
{
    protected $table      = 'hr_pengajuan_cuti';
    protected $primaryKey = 'id';
    public $incrementing  = true;

    protected $fillable = [
        'no_pengajuan',
        'tanggal',
        'tanggal_awal',
        'tanggal_akhir',
        'nik',
        'urgensi',
        'alamat',
        'jumlah',
        'kepentingan',
        'nik_pj',
        'catatan_atasan',
        'approved_atasan_at',
        'catatan_hrd',
        'approved_hrd_at',
        'status',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'tanggal_awal'       => 'date',
        'tanggal_akhir'      => 'date',
        'jumlah'             => 'integer',
        'approved_atasan_at' => 'datetime',
        'approved_hrd_at'    => 'datetime',
    ];

    const JENIS_CUTI = [
        'Tahunan',
        'Besar',
        'Sakit',
        'Bersalin',
        'Alasan Penting',
        'Keterangan Lainnya',
    ];

    const STATUS = [
        'Menunggu Atasan',
        'Menunggu HRD',
        'Disetujui',
        'Ditolak Atasan',
        'Ditolak HRD',
    ];

    const HAK_CUTI_TAHUNAN = 12;

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeMenungguApproval($query)
    {
        return $query->whereIn('status', ['Menunggu Atasan', 'Menunggu HRD']);
    }

    public function scopeMenungguAtasan($query)
    {
        return $query->where('status', 'Menunggu Atasan');
    }

    public function scopeMenungguHrd($query)
    {
        return $query->where('status', 'Menunggu HRD');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'Disetujui');
    }

    public function scopeTahunIni($query)
    {
        return $query->whereYear('tanggal', now()->year);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function bisaApproveAtasan(): bool
    {
        return $this->status === 'Menunggu Atasan';
    }

    public function bisaApproveHrd(): bool
    {
        return $this->status === 'Menunggu HRD';
    }

    public function ditolak(): bool
    {
        return str_starts_with($this->status, 'Ditolak');
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

    public function getDurasiAttribute(): string
    {
        return $this->jumlah . ' hari kerja';
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik_pj', 'nik');
    }
}
