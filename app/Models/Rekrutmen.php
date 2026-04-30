<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rekrutmen extends Model
{
    // Tabel BARU — perlu migration
    protected $table = 'rekrutmen';

    protected $fillable = [
        'posisi',
        'departemen_id',
        'jumlah_dibutuhkan',
        'tanggal_buka',
        'tanggal_tutup',
        'status',               // buka | proses_seleksi | tutup | dibatalkan
        'deskripsi',
        'syarat',
        'dibuat_oleh',          // user_id
    ];

    protected $casts = [
        'tanggal_buka'       => 'date',
        'tanggal_tutup'      => 'date',
        'jumlah_dibutuhkan'  => 'integer',
    ];

    const STATUS = ['buka', 'proses_seleksi', 'tutup', 'dibatalkan'];

    public function scopeBuka($query)
    {
        return $query->where('status', 'buka')
                     ->where('tanggal_tutup', '>=', today());
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class, 'departemen_id', 'dep_id');
    }

    public function pelamar(): HasMany
    {
        return $this->hasMany(Pelamar::class, 'rekrutmen_id', 'id');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id');
    }

    // Hitung jumlah pelamar per status
    public function getJumlahDiterimaAttribute(): int
    {
        return $this->pelamar()->where('status', 'diterima')->count();
    }
}