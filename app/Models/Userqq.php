<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    // Tabel BARU — dibuat via migration, bukan dari SIK
    protected $table = 'users';

    protected $fillable = [
        'pegawai_id',       // FK ke pegawai.id (nullable untuk admin murni)
        'name',
        'email',
        'username',         // alternatif login dengan username/NIK
        'password',
        'is_active',
        'last_login_at',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Relasi ────────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id', 'id');
    }

    // ─── Helper ────────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isHR(): bool
    {
        return $this->hasAnyRole(['hr_manager', 'super_admin']);
    }

    public function getNamaLengkapAttribute(): string
    {
        return $this->pegawai?->nama ?? $this->name;
    }
}