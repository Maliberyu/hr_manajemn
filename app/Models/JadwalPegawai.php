<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPegawai extends Model
{
    public $timestamps = false;

    protected $table = 'jadwal_pegawai';

    // Composite PK: id + tahun + bulan
    public $incrementing = false;

    protected $fillable = [
        'id',       // FK ke pegawai.id
        'tahun',
        'bulan',
        // h1 s.d. h31 — shift tiap hari dalam sebulan
        'h1','h2','h3','h4','h5','h6','h7','h8','h9','h10',
        'h11','h12','h13','h14','h15','h16','h17','h18','h19','h20',
        'h21','h22','h23','h24','h25','h26','h27','h28','h29','h30','h31',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    // Daftar nilai shift yang valid (sesuai ENUM di DB)
    const SHIFT_OPTIONS = [
        'Pagi','Siang','Malam',
        'Midle Pagi1','Midle Siang1','Midle Malam1',
        '',     // Libur / tidak masuk
    ];

    // ─── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Kembalikan shift hari ke-N (1–31).
     * Contoh: $jadwal->getHari(5) → 'Pagi'
     */
    public function getHari(int $hari): string
    {
        $col = 'h' . $hari;
        return $this->{$col} ?? '';
    }

    /**
     * Array shift seluruh bulan: [1 => 'Pagi', 2 => 'Siang', ...]
     */
    public function getShiftBulanAttribute(): array
    {
        $result = [];
        for ($i = 1; $i <= 31; $i++) {
            $result[$i] = $this->{'h' . $i} ?? '';
        }
        return $result;
    }

    /** Hitung total hari masuk dalam bulan ini */
    public function getTotalMasukAttribute(): int
    {
        $count = 0;
        for ($i = 1; $i <= 31; $i++) {
            if (!empty($this->{'h' . $i})) {
                $count++;
            }
        }
        return $count;
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'id', 'id');
    }
}