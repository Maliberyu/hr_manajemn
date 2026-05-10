<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanIjin extends Model
{
    protected $table = 'hr_pengajuan_ijin';

    protected $fillable = [
        'no_pengajuan', 'nik', 'pegawai_id',
        'tanggal', 'jenis',
        'jam_mulai', 'jam_selesai', 'durasi_menit',
        'alasan', 'file_surat', 'status',
        'catatan_atasan', 'approved_atasan_by', 'approved_atasan_at',
        'catatan_hrd',    'approved_hrd_by',    'approved_hrd_at',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'approved_atasan_at' => 'datetime',
        'approved_hrd_at'    => 'datetime',
        'durasi_menit'       => 'integer',
    ];

    const JENIS = [
        'sakit'        => 'Ijin Sakit',
        'terlambat'    => 'Ijin Terlambat',
        'pulang_duluan'=> 'Ijin Pulang Duluan',
    ];

    const STATUS = [
        'Menunggu Atasan',
        'Menunggu HRD',
        'Disetujui',
        'Ditolak Atasan',
        'Ditolak HRD',
    ];

    const JENIS_ICON = [
        'sakit'         => '🤒',
        'terlambat'     => '⏰',
        'pulang_duluan' => '🏃',
    ];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeMenungguApproval($query)
    {
        return $query->whereIn('status', ['Menunggu Atasan', 'Menunggu HRD']);
    }

    public function scopeJenis($query, string $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'Disetujui');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function bisaApproveAtasan(): bool
    {
        if ($this->status !== 'Menunggu Atasan') return false;
        $user = auth()->user();
        if ($user->hasRole(['hrd', 'admin'])) return true;
        if (!$user->hasRole('atasan')) return false;
        return $this->nik && AtasanPegawai::isAtasanDari($user->id, $this->nik);
    }

    public function bisaApproveHrd(): bool
    {
        return $this->status === 'Menunggu HRD'
            && auth()->user()->hasRole(['hrd', 'admin']);
    }

    public function ditolak(): bool
    {
        return str_starts_with($this->status, 'Ditolak');
    }

    public function getLabelJenisAttribute(): string
    {
        return self::JENIS[$this->jenis] ?? $this->jenis;
    }

    public function getDurasiLabelAttribute(): string
    {
        if (!$this->durasi_menit) return '-';
        $jam  = intdiv($this->durasi_menit, 60);
        $mnt  = $this->durasi_menit % 60;
        return $jam > 0 ? "{$jam} jam {$mnt} menit" : "{$mnt} menit";
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'Menunggu Atasan' => 'yellow',
            'Menunggu HRD'    => 'blue',
            'Disetujui'       => 'green',
            default           => 'red',
        };
    }

    public function getFileSuratUrlAttribute(): ?string
    {
        return $this->file_surat
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->file_surat)
            : null;
    }

    // ─── Generator nomor ──────────────────────────────────────────────────────

    public static function generateNomor(string $jenis): string
    {
        $kode   = match($jenis) {
            'sakit'         => 'IS',
            'terlambat'     => 'IT',
            'pulang_duluan' => 'IP',
            default         => 'IJ',
        };
        $prefix = "{$kode}/" . now()->format('Ym') . '/';
        $last   = static::where('no_pengajuan', 'like', $prefix . '%')
                        ->orderByDesc('no_pengajuan')->value('no_pengajuan');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function approvedAtasanBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_atasan_by', 'id');
    }

    public function approvedHrdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_hrd_by', 'id');
    }
}
