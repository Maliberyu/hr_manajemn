<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'user_hr';

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'auth_provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
