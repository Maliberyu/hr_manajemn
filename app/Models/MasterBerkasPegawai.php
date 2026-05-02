<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterBerkasPegawai extends Model
{
    protected $table = 'hr_jenis_berkas';

    protected $fillable = [
        'nama',
        'kategori',
        'urutan',
    ];

    protected $casts = [
        'urutan' => 'integer',
    ];

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function berkas(): HasMany
    {
        return $this->hasMany(BerkasPegawai::class, 'jenis_id');
    }
}
