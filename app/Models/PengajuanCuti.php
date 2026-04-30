<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanCuti extends Model
{
    public $timestamps = false;

    protected $table = 'pengajuan_cuti';
    protected $primaryKey = 'no_pengajuan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'no_pengajuan',     // format: CT/YYYYMM/XXX
        'tanggal',          // tanggal pengajuan dibuat
        'tanggal_awal',     // mulai cuti
        'tanggal_akhir',    // selesai cuti
        'nik',              // pegawai yang mengajukan
        'urgensi',          // jenis cuti
        'alamat',           // alamat selama cuti
        'jumlah',           // jumlah hari cuti
        'kepentingan',      // alasan/keterangan
        'nik_pj',           // nik penanggung jawab selama cuti
        'status',           // Proses Pengajuan | Disetujui | Ditolak
    ];

    protected $casts = [
        'tanggal'       => 'date',
        'tanggal_awal'  => 'date',
        'tanggal_akhir' => 'date',
        'jumlah'        => 'integer',
    ];

    // Jenis cuti sesuai ENUM di DB
    const JENIS_CUTI = [
        'Tahunan',
        'Besar',
        'Sakit',
        'Bersalin',
        'Alasan Penting',
        'Keterangan Lainnya',
    ];

    const STATUS = [
        'Proses Pengajuan',
        'Disetujui',
        'Ditolak',
    ];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeMenungguApproval($query)
    {
        return $query->where('status', 'Proses Pengajuan');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'Disetujui');
    }

    public function scopeTahunIni($query)
    {
        return $query->whereYear('tanggal', now()->year);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'Disetujui'        => 'success',
            'Ditolak'          => 'danger',
            'Proses Pengajuan' => 'warning',
            default            => 'secondary',
        };
    }

    public function getDurasiAttribute(): string
    {
        return $this->jumlah . ' hari';
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