<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftSetting extends Model
{
    protected $table    = 'hr_shift_setting';
    protected $fillable = [
        'toleransi_mismatch_menit',
        'max_tukar_shift_per_bulan',
        'wajib_approval_double_shift',
        'notif_mismatch_ke_atasan',
        'updated_by',
    ];
    protected $casts = [
        'wajib_approval_double_shift' => 'boolean',
        'notif_mismatch_ke_atasan'    => 'boolean',
    ];

    /** Singleton — ambil atau buat satu baris setting */
    public static function get(): static
    {
        return static::firstOrCreate([], [
            'toleransi_mismatch_menit'    => 30,
            'max_tukar_shift_per_bulan'   => 3,
            'wajib_approval_double_shift' => true,
            'notif_mismatch_ke_atasan'    => true,
        ]);
    }
}
