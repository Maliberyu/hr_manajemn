<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CutiSetting extends Model
{
    protected $table    = 'hr_cuti_setting';
    protected $fillable = ['min_hari_pengajuan', 'updated_by'];
    protected $casts    = ['min_hari_pengajuan' => 'integer'];

    public static function get(): static
    {
        return static::firstOrCreate([], ['min_hari_pengajuan' => 3]);
    }

    /** Tanggal minimal pengajuan = hari ini + min_hari_pengajuan */
    public static function tanggalMinimal(): string
    {
        return now()->addDays(static::get()->min_hari_pengajuan)->format('Y-m-d');
    }
}
