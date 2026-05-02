<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BerkasPegawai extends Model
{
    protected $table = 'hr_berkas';

    protected $fillable = [
        'jenis_id',
        'nik',
        'nama_file',
        'path',
        'tgl_upload',
        'keterangan',
    ];

    protected $casts = [
        'tgl_upload' => 'date',
    ];

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    public function getEkstensiAttribute(): string
    {
        return strtolower(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    public function getIsPdfAttribute(): bool
    {
        return $this->ekstensi === 'pdf';
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(MasterBerkasPegawai::class, 'jenis_id');
    }
}
