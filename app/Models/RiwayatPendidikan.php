<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RiwayatPendidikan extends Model
{
    protected $table = 'hr_riwayat_pendidikan';

    protected $fillable = [
        'nik', 'jenjang', 'nama_institusi', 'jurusan',
        'tahun_masuk', 'tahun_lulus', 'ipk',
        'file_ijazah', 'is_terakhir', 'keterangan', 'dibuat_oleh',
    ];

    protected $casts = [
        'is_terakhir' => 'boolean',
        'ipk'         => 'float',
    ];

    // Urutan jenjang untuk sorting
    public const URUTAN_JENJANG = [
        'SD' => 1, 'SMP' => 2, 'SMA/SMK' => 3,
        'D1' => 4, 'D2' => 5, 'D3' => 6,
        'S1' => 7, 'S2' => 8, 'S3' => 9,
        'Non-Formal' => 10,
    ];

    public static function jenjangList(): array
    {
        return ['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3','Non-Formal'];
    }

    // ─── Accessors ─────────────────────────────────────────────────────────────

    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_ijazah) return null;
        return Storage::disk('public')->url($this->file_ijazah);
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    public function pembuatUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
}
