<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalRealisasi extends Model
{
    protected $table    = 'hr_jadwal_realisasi';
    protected $fillable = [
        'pegawai_id', 'tanggal', 'shift_kode', 'sumber',
        'tukar_shift_id', 'double_shift_id', 'catatan',
    ];
    protected $casts = [
        'tanggal' => 'date',
    ];

    const SUMBER = [
        'absensi_auto' => 'Auto Absensi',
        'tukar_shift'  => 'Tukar Shift',
        'double_shift' => 'Double Shift',
        'manual'       => 'Manual',
    ];

    // ── Relasi ────────────────────────────────────────────────────────────────
    public function pegawai(): BelongsTo     { return $this->belongsTo(Pegawai::class, 'pegawai_id'); }
    public function shift(): BelongsTo       { return $this->belongsTo(ShiftMaster::class, 'shift_kode', 'kode'); }
    public function tukarShift(): BelongsTo  { return $this->belongsTo(TukarShift::class, 'tukar_shift_id'); }
    public function doubleShift(): BelongsTo { return $this->belongsTo(DoubleShift::class, 'double_shift_id'); }

    // ── Scopes ────────────────────────────────────────────────────────────────
    public function scopeBulan($q, int $tahun, int $bulan)
    {
        return $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }

    // ── Catat / update realisasi (upsert) ─────────────────────────────────────
    public static function catat(int $pegawaiId, string $tanggal, string $shiftKode, string $sumber, array $extra = []): static
    {
        return static::updateOrCreate(
            ['pegawai_id' => $pegawaiId, 'tanggal' => $tanggal],
            array_merge(['shift_kode' => $shiftKode, 'sumber' => $sumber], $extra)
        );
    }

    // ── Accessors ─────────────────────────────────────────────────────────────
    public function getIsTukarShiftAttribute(): bool { return $this->sumber === 'tukar_shift'; }
    public function getIsDoubleShiftAttribute(): bool { return $this->sumber === 'double_shift'; }

    public function getSumberLabelAttribute(): string
    {
        return self::SUMBER[$this->sumber] ?? $this->sumber;
    }
}
