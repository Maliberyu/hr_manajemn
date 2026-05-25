<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LemburSetting extends Model
{
    protected $table    = 'hr_lembur_setting';
    protected $fillable = [
        'metode', 'min_jam_lembur', 'min_jam_shift',
        'max_jam_harian', 'max_jam_mingguan',
        'formula_upah_jam', 'wajib_approval', 'updated_by',
    ];
    protected $casts = [
        'min_jam_lembur'   => 'float',
        'min_jam_shift'    => 'float',
        'max_jam_harian'   => 'float',
        'max_jam_mingguan' => 'float',
        'wajib_approval'   => 'boolean',
    ];

    const METODE = [
        'keduanya'   => 'Otomatis (shift jika ada, jam aktual jika tidak)',
        'shift'      => 'Selalu berdasarkan shift',
        'jam_aktual' => 'Selalu berdasarkan jam aktual',
    ];

    const FORMULA = [
        'gapok_173'        => 'Gaji Pokok ÷ 173',
        'tarif_dept'       => 'Tarif per Departemen',
        'tarif_pendidikan' => 'Tarif per Pendidikan',
    ];

    public static function get(): static
    {
        return static::firstOrCreate([], [
            'metode'           => 'keduanya',
            'min_jam_lembur'   => 3.00,
            'min_jam_shift'    => 0.50,
            'max_jam_harian'   => 4.00,
            'max_jam_mingguan' => 18.00,
            'formula_upah_jam' => 'gapok_173',
            'wajib_approval'   => true,
        ]);
    }
}
