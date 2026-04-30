<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

// ═══════════════════════════════════════════════════════════════════════════════
// Training — program pelatihan / diklat
// ═══════════════════════════════════════════════════════════════════════════════
class Training extends Model
{
    // Tabel BARU — perlu migration
    protected $table = 'training';

    protected $fillable = [
        'nama_training',
        'penyelenggara',
        'tanggal_mulai',
        'tanggal_selesai',
        'lokasi',
        'jenis',            // internal | eksternal | online
        'biaya',
        'deskripsi',
        'kuota',
        'status',           // rencana | berjalan | selesai | dibatalkan
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'biaya'           => 'double',
        'kuota'           => 'integer',
    ];

    const JENIS  = ['internal', 'eksternal', 'online'];
    const STATUS = ['rencana', 'berjalan', 'selesai', 'dibatalkan'];

    public function getDurasiHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function peserta(): HasMany
    {
        return $this->hasMany(TrainingPeserta::class, 'training_id', 'id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id');
    }
}