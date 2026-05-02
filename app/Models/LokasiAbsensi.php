<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiAbsensi extends Model
{
    protected $table = 'hr_lokasi_absensi';

    protected $fillable = ['nama', 'alamat', 'lat', 'lng', 'radius_meter', 'aktif'];

    protected $casts = [
        'lat'          => 'float',
        'lng'          => 'float',
        'radius_meter' => 'integer',
        'aktif'        => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /** Haversine — kembalikan jarak dalam meter */
    public static function hitungJarak(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R  = 6371000;
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lng2 - $lng1);
        $a  = sin($Δφ / 2) ** 2 + cos($φ1) * cos($φ2) * sin($Δλ / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function dalamRadius(float $lat, float $lng): bool
    {
        return static::hitungJarak($this->lat, $this->lng, $lat, $lng) <= $this->radius_meter;
    }
}
