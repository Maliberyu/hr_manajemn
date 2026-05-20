<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lowongan extends Model
{
    protected $table    = 'hr_lowongan';
    protected $fillable = [
        'no_lowongan', 'request_id', 'posisi', 'departemen', 'kuota',
        'tgl_buka', 'tgl_tutup', 'status', 'deskripsi', 'syarat', 'dibuat_oleh',
    ];
    protected $casts = [
        'tgl_buka'  => 'date',
        'tgl_tutup' => 'date',
        'kuota'     => 'integer',
    ];

    const STATUS = ['buka','proses_seleksi','tutup','dibatalkan'];

    public static function generateNomor(): string
    {
        $prefix = 'LOW/' . now()->format('Ym') . '/';
        $last   = static::where('no_lowongan', 'like', $prefix . '%')
                        ->orderByDesc('no_lowongan')->value('no_lowongan');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }

    public function request(): BelongsTo     { return $this->belongsTo(RekrutmenRequest::class, 'request_id'); }
    public function departemenRef(): BelongsTo { return $this->belongsTo(Departemen::class, 'departemen', 'dep_id'); }
    public function dibuatOleh(): BelongsTo  { return $this->belongsTo(User::class, 'dibuat_oleh'); }
    public function pelamar(): HasMany       { return $this->hasMany(HrPelamar::class, 'lowongan_id'); }

    public function scopeBuka($q)  { return $q->where('status', 'buka'); }
    public function scopeAktif($q) { return $q->whereIn('status', ['buka','proses_seleksi']); }

    public function getJumlahPelamarAttribute(): int  { return $this->pelamar()->count(); }
    public function getJumlahDiterimaAttribute(): int { return $this->pelamar()->where('status','diterima')->count(); }
    public function getSisaKuotaAttribute(): int      { return max(0, $this->kuota - $this->jumlah_diterima); }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'buka'            => 'bg-green-100 text-green-700',
            'proses_seleksi'  => 'bg-blue-100 text-blue-700',
            'tutup'           => 'bg-gray-100 text-gray-600',
            'dibatalkan'      => 'bg-red-100 text-red-700',
            default           => 'bg-gray-100 text-gray-600',
        };
    }
}
