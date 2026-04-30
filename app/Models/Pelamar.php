<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Pelamar extends Model
{
    // Tabel BARU — perlu migration
    protected $table = 'pelamar';

    protected $fillable = [
        'rekrutmen_id',
        'nama',
        'email',
        'telepon',
        'tanggal_lahir',
        'pendidikan_terakhir',
        'pengalaman_tahun',
        'cv_file',          // path file CV (PDF)
        'status',           // applied | interview | test | offering | diterima | ditolak
        'catatan',
        'tanggal_apply',
        'tanggal_interview',
        'nilai_test',
    ];

    protected $casts = [
        'tanggal_lahir'     => 'date',
        'tanggal_apply'     => 'date',
        'tanggal_interview' => 'datetime',
        'pengalaman_tahun'  => 'integer',
        'nilai_test'        => 'float',
    ];

    const STATUS = ['applied', 'interview', 'test', 'offering', 'diterima', 'ditolak'];

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'diterima' => 'success',
            'ditolak'  => 'danger',
            'interview'=> 'info',
            'offering' => 'primary',
            'test'     => 'warning',
            default    => 'secondary',
        };
    }

    public function getCvUrlAttribute(): ?string
    {
        return $this->cv_file ? Storage::url($this->cv_file) : null;
    }

    public function rekrutmen(): BelongsTo
    {
        return $this->belongsTo(Rekrutmen::class, 'rekrutmen_id', 'id');
    }
}