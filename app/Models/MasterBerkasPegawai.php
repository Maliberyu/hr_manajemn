<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterBerkasPegawai extends Model
{
    public $timestamps = false;

    protected $table = 'master_berkas_pegawai';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode',
        'kategori',     // enum: Tenaga klinis Dokter Umum, Perawat dan Bidan, Tenaga Non Klinis, dll
        'nama_berkas',  // Nama dokumen, mis: "Ijazah Terakhir", "STR/SIP", "KTP"
        'no_urut',
    ];

    protected $casts = [
        'no_urut' => 'integer',
    ];

    // Daftar kategori yang ada di DB
    const KATEGORI = [
        'Tenaga klinis Dokter Umum',
        'Tenaga klinis Dokter Spesialis',
        'Tenaga klinis Perawat dan Bidan',
        'Tenaga klinis Profesi Lain',
        'Tenaga Non Klinis',
    ];

    // ─── Scope ─────────────────────────────────────────────────────────────────

    public function scopeKategori($query, string $kategori)
    {
        return $query->where('kategori', $kategori)->orderBy('no_urut');
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function berkasPegawai(): HasMany
    {
        return $this->hasMany(BerkasPegawai::class, 'kode_berkas', 'kode');
    }
}