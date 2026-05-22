<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AtasanPegawai;

class Lembur extends Model
{
    protected $table = 'lembur';

    protected $fillable = [
        'pegawai_id', 'tanggal', 'jam_mulai', 'jam_selesai',
        'durasi_jam', 'durasi_aktual',
        'jenis', 'keterangan', 'status', 'nominal',
        'sumber_draft',
        // Kalkulasi baru
        'metode', 'shift_kode', 'jam_selesai_shift',
        'multiplier', 'upah_per_jam', 'catatan_sistem',
        // Approval level 1 — Atasan
        'catatan_atasan', 'approved_atasan_by', 'approved_atasan_at',
        // Approval level 2 — HRD
        'catatan_hrd', 'approved_hrd_by', 'approved_hrd_at',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'durasi_jam'         => 'float',
        'durasi_aktual'      => 'float',
        'multiplier'         => 'float',
        'upah_per_jam'       => 'double',
        'nominal'            => 'double',
        'approved_atasan_at' => 'datetime',
        'approved_hrd_at'    => 'datetime',
    ];

    const STATUS = [
        'Draft',
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

    public function scopeDraft($query)        { return $query->where('status', 'Draft'); }
    public function scopeAktif($query)        { return $query->whereNotIn('status', ['Ditolak Atasan','Ditolak HRD']); }

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
        return self::formatDurasi($this->durasi_jam);
    }

    public function getMetodeLabelAttribute(): string
    {
        return match($this->metode) {
            'shift'      => 'Shift (' . ($this->shift_kode ?? '-') . ')',
            'jam_aktual' => 'Jam Aktual',
            default      => '—',
        };
    }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'Draft'           => 'bg-gray-100 text-gray-600 border-gray-200',
            'Menunggu Atasan' => 'bg-amber-50 text-amber-700 border-amber-200',
            'Menunggu HRD'    => 'bg-blue-50 text-blue-700 border-blue-200',
            'Disetujui'       => 'bg-green-50 text-green-700 border-green-200',
            default           => 'bg-red-50 text-red-700 border-red-200',
        };
    }

    public static function formatDurasi(?float $jam): string
    {
        if (!$jam) return '0j';
        $j = (int) $jam;
        $m = (int)(($jam - $j) * 60);
        return "{$j}j" . ($m > 0 ? " {$m}m" : '');
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
