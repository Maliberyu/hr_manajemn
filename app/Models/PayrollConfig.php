<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollConfig extends Model
{
    protected $table   = 'hr_payroll_config';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'key';
    protected $fillable   = ['key', 'value', 'label', 'group'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
    }

    /** Ambil semua config sebagai array key → value */
    public static function allConfig(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}
