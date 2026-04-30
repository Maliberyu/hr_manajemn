<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BerkasPegawai extends Model
{
    public $timestamps = false;

    protected $table = 'berkas_pegawai';

    // Tabel ini composite PK (tidak ada kolom id tunggal)
    // Eloquent tidak pakai $primaryKey untuk composite — biarkan default
    public $incrementing = false;

    protected $fillable = [
        'nik',
        'tgl_uploud',
        'kode_berkas',
        'berkas',           // path relatif di storage, mis: berkas_pegawai/KTP_12345.pdf
    ];

    protected $casts = [
        'tgl_uploud' => 'date',
    ];

    // ─── Accessors ─────────────────────────────────────────────────────────────

    /** URL download berkas */
    public function getUrlBerkasAttribute(): string
    {
        return Storage::url($this->berkas);
    }

    /** Ekstensi file (pdf, jpg, dll) */
    public function getEkstensiAttribute(): string
    {
        return strtolower(pathinfo($this->berkas, PATHINFO_EXTENSION));
    }

    /** Apakah file berupa PDF */
    public function getIsPdfAttribute(): bool
    {
        return $this->ekstensi === 'pdf';
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function masterBerkas(): BelongsTo
    {
        return $this->belongsTo(MasterBerkasPegawai::class, 'kode_berkas', 'kode');
    }
}