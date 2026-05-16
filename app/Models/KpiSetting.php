<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiSetting extends Model
{
    protected $table    = 'hr_kpi_setting';
    protected $fillable = [
        'bobot_kehadiran', 'bobot_disiplin', 'bobot_penilaian',
        'bobot_p360', 'bobot_pelatihan',
        'target_hadir_pct', 'target_jam_pelatihan',
        'penalti_alfa', 'penalti_terlambat',
    ];

    protected $casts = [
        'bobot_kehadiran'      => 'integer',
        'bobot_disiplin'       => 'integer',
        'bobot_penilaian'      => 'integer',
        'bobot_p360'           => 'integer',
        'bobot_pelatihan'      => 'integer',
        'target_hadir_pct'     => 'integer',
        'target_jam_pelatihan' => 'integer',
        'penalti_alfa'         => 'integer',
        'penalti_terlambat'    => 'integer',
    ];

    /** Ambil setting aktif (selalu baris pertama). */
    public static function aktif(): static
    {
        return static::firstOrCreate([], [
            'bobot_kehadiran'      => 25,
            'bobot_disiplin'       => 15,
            'bobot_penilaian'      => 30,
            'bobot_p360'           => 20,
            'bobot_pelatihan'      => 10,
            'target_hadir_pct'     => 95,
            'target_jam_pelatihan' => 40,
            'penalti_alfa'         => 5,
            'penalti_terlambat'    => 2,
        ]);
    }

    public function totalBobot(): int
    {
        return $this->bobot_kehadiran + $this->bobot_disiplin
             + $this->bobot_penilaian + $this->bobot_p360
             + $this->bobot_pelatihan;
    }
}
