<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users_hr';

    protected $fillable = [
        'nama',
        'nik',
        'email',
        'password',
        'email_verified',
        'google_id',
        'auth_provider',
        'jabatan',
        'role',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified' => 'datetime',
        'last_login_at'  => 'datetime',
    ];

    const ROLES = [
        'karyawan' => 'Karyawan',
        'atasan'   => 'Atasan Langsung',
        'hrd'      => 'HRD',
        'admin'    => 'Admin',
    ];

    // ─── Relasi ───────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nik', 'nik');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role ?? 'karyawan', ['admin', 'hrd']);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role ?? 'karyawan'] ?? ucfirst($this->role ?? '-');
    }

    // ─── Role checks ──────────────────────────────────────────────────────────

    public function hasRole($roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array($this->role ?? 'karyawan', $roles);
    }

    public function hasAnyRole($roles): bool
    {
        return $this->hasRole(is_array($roles) ? $roles : [$roles]);
    }
}
