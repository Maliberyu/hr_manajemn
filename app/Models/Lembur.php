<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Lembur extends Model
{
    // Tabel BARU — perlu migration
    protected $table = 'lembur';

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'durasi_jam',           // dihitung otomatis
        'jenis',                // HB (hari biasa) | HR (hari raya/libur)
        'keterangan',
        'status',               // draft | diajukan | disetujui | ditolak
        'approved_by',          // user_id yang approve
        'approved_at',
        'catatan_approval',
        'nominal',              // dihitung dari durasi × tarif lembur
    ];

    protected $casts = [
        'tanggal'     => 'date',
        'jam_mulai'   => 'datetime',
        'jam_selesai' => 'datetime',
        'durasi_jam'  => 'float',
        'approved_at' => 'datetime',
        'nominal'     => 'double',
    ];

    const STATUS = ['draft', 'diajukan', 'disetujui', 'ditolak'];
    const JENIS  = ['HB', 'HR'];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeMenungguApproval($query)
    {
        return $query->where('status', 'diajukan');
    }

    public function scopeBulan($query, int $tahun, int $bulan)
    {
        return $query->whereYear('tanggal', $tahun)
                     ->whereMonth('tanggal', $bulan);
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'disetujui' => 'success',
            'ditolak'   => 'danger',
            'diajukan'  => 'warning',
            'draft'     => 'secondary',
            default     => 'secondary',
        };
    }

    public function getDurasiLabelAttribute(): string
    {
        $jam  = intdiv((int)$this->durasi_jam, 1);
        $mnt  = (int)(($this->durasi_jam - $jam) * 60);
        return "{$jam}j " . ($mnt > 0 ? "{$mnt}m" : "");
    }

    // ─── Helper kalkulasi ──────────────────────────────────────────────────────

    /**
     * Hitung nominal lembur berdasarkan jenis dan durasi.
     * Tarif diambil dari tabel set_lemburhb / set_lemburhr.
     */
    public function hitungNominal(): float
    {
        $tarif = $this->jenis === 'HR'
            ? SetLemburHR::tarifAktif()
            : SetLemburHB::tarifAktif();

        return $this->durasi_jam * $tarif;
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}