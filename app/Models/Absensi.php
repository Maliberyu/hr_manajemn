<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Absensi extends Model
{
    // Tabel BARU — perlu migration
    protected $table = 'absensi';

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',           // hadir | izin | sakit | alfa | cuti | libur
        'terlambat_menit',  // dihitung otomatis saat check-in
        'metode',           // manual | fingerprint | rfid | mobile
        'lat_masuk',        // koordinat GPS saat check-in
        'lng_masuk',
        'lat_keluar',
        'lng_keluar',
        'keterangan',
        'diinput_oleh',     // user_id yang menginput (jika manual oleh HR)
        'foto_masuk',       // path file foto selfie saat check-in
        'foto_keluar',      // path file foto selfie saat check-out
        'lokasi_valid',     // apakah check-in dalam radius lokasi kantor
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'jam_masuk'      => 'datetime',
        'jam_keluar'     => 'datetime',
        'terlambat_menit'=> 'integer',
        'lat_masuk'      => 'float',
        'lng_masuk'      => 'float',
        'lat_keluar'     => 'float',
        'lng_keluar'     => 'float',
    ];

    const STATUS = ['hadir', 'izin', 'sakit', 'alfa', 'cuti', 'libur'];
    const METODE = ['manual', 'fingerprint', 'rfid', 'mobile'];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeBulan($query, int $tahun, int $bulan)
    {
        return $query->whereYear('tanggal', $tahun)
                     ->whereMonth('tanggal', $bulan);
    }

    public function scopeTerlambat($query)
    {
        return $query->where('terlambat_menit', '>', 0);
    }

    public function scopeHadir($query)
    {
        return $query->where('status', 'hadir');
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'hadir'  => 'success',
            'izin'   => 'info',
            'sakit'  => 'warning',
            'alfa'   => 'danger',
            'cuti'   => 'primary',
            'libur'  => 'secondary',
            default  => 'secondary',
        };
    }

    public function getDurasiKerjaAttribute(): ?string
    {
        if (!$this->jam_masuk || !$this->jam_keluar) return null;

        $menit = Carbon::parse($this->jam_masuk)
                       ->diffInMinutes(Carbon::parse($this->jam_keluar));
        $jam   = intdiv($menit, 60);
        $sisa  = $menit % 60;

        return "{$jam}j {$sisa}m";
    }

    public function getTerlambatLabelAttribute(): string
    {
        if (!$this->terlambat_menit) return '-';
        $jam  = intdiv($this->terlambat_menit, 60);
        $mnt  = $this->terlambat_menit % 60;
        return $jam > 0 ? "{$jam}j {$mnt}m" : "{$mnt}m";
    }

    public function getFotoMasukUrlAttribute(): ?string
    {
        return $this->foto_masuk ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_masuk) : null;
    }

    public function getFotoKeluarUrlAttribute(): ?string
    {
        return $this->foto_keluar ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_keluar) : null;
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    public function diinputOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diinput_oleh', 'id');
    }
}