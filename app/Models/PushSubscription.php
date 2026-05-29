<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $table = 'push_subscriptions';

    protected $fillable = [
        'user_id', 'endpoint', 'public_key', 'auth_token', 'content_encoding',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public static function saveForUser(int $userId, array $sub): void
    {
        static::updateOrCreate(
            ['user_id' => $userId, 'endpoint' => $sub['endpoint']],
            [
                'public_key'       => $sub['keys']['p256dh'] ?? null,
                'auth_token'       => $sub['keys']['auth']   ?? null,
                'content_encoding' => $sub['contentEncoding'] ?? 'aesgcm',
            ]
        );
    }

    public static function removeForUser(int $userId, string $endpoint): void
    {
        static::where('user_id', $userId)->where('endpoint', $endpoint)->delete();
    }
}
