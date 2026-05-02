<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users_hr';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'email_verified',
        'google_id',
        'auth_provider',
        'jabatan',
        'status',
        'last_login_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'last_login_at' => 'datetime',
    ];
}
