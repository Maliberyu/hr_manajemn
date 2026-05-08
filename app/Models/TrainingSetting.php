<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TrainingSetting extends Model
{
    protected $table = 'hr_training_setting';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function logoUrl(): ?string
    {
        $path = static::get('logo_rs');
        return $path ? Storage::disk('public')->url($path) : null;
    }
}
