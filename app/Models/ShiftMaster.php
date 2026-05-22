<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftMaster extends Model
{
    protected $table    = 'hr_shift_master';
    protected $fillable = [
        'kode', 'nama', 'jam_mulai', 'jam_selesai',
        'melewati_tengah_malam', 'multiplier_lembur', 'aktif', 'urutan',
    ];
    protected $casts = [
        'melewati_tengah_malam' => 'boolean',
        'multiplier_lembur'     => 'float',
        'aktif'                 => 'boolean',
    ];

    public function scopeAktif($q)    { return $q->where('aktif', true)->orderBy('urutan'); }
    public function scopeUrut($q)     { return $q->orderBy('urutan'); }

    /** Ambil shift berdasarkan kode, atau null */
    public static function cariKode(?string $kode): ?static
    {
        if (!$kode) return null;
        return static::where('kode', $kode)->first();
    }

    /**
     * Map nama shift dari jadwal_pegawai (h1-h31 text) ke ShiftMaster.
     * "Pagi" → 'pagi', "Siang" → 'sore', "Malam" → 'malam', dll.
     */
    public static function dariNamaJadwal(?string $namaShift): ?static
    {
        if (!$namaShift || trim($namaShift) === '') return null;

        $nama = trim(strtolower($namaShift));

        // Coba exact match by kode dulu
        $exact = static::where('kode', $nama)->first();
        if ($exact) return $exact;

        // Mapping nama umum jadwal_pegawai → kode shift master
        $map = [
            'pagi'        => 'pagi',
            'siang'       => 'sore',
            'sore'        => 'sore',
            'malam'       => 'malam',
            'libur'       => 'libur',
            'off'         => 'libur',
        ];

        // Cek exact map
        if (isset($map[$nama])) {
            return static::where('kode', $map[$nama])->first();
        }

        // Cek partial: "midle pagi1" → cari kode yang mengandung "midle" + "pagi"
        $kata = explode(' ', $nama);
        if (count($kata) >= 2) {
            $cari = static::whereRaw('LOWER(kode) LIKE ?', ['%' . $kata[0] . '%'])
                          ->whereRaw('LOWER(nama) LIKE ?', ['%' . $kata[1] . '%'])
                          ->first();
            if ($cari) return $cari;
        }

        // Fallback: cari by nama LIKE
        return static::whereRaw('LOWER(nama) LIKE ?', ['%' . $nama . '%'])->first();
    }

    /** Durasi shift dalam jam (hitung melewati tengah malam) */
    public function getDurasiJamAttribute(): float
    {
        [$h1, $m1] = explode(':', $this->jam_mulai);
        [$h2, $m2] = explode(':', $this->jam_selesai);
        $menit = ($h2 * 60 + $m2) - ($h1 * 60 + $m1);
        if ($menit <= 0) $menit += 1440; // melewati tengah malam
        return round($menit / 60, 2);
    }

    /** Label jam mulai–selesai */
    public function getJamLabelAttribute(): string
    {
        return substr($this->jam_mulai, 0, 5) . ' – ' . substr($this->jam_selesai, 0, 5);
    }

    /** Multiplier label */
    public function getMultiplierLabelAttribute(): string
    {
        return $this->multiplier_lembur . '×';
    }
}
