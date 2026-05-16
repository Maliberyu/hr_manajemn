<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    | Set ke false untuk menonaktifkan fitur. User yang klik menu akan
    | mendapat notifikasi "Fitur belum tersedia" tanpa halaman error.
    |
    | Bisa di-override lewat .env:
    |   FEATURE_CUTI=false
    */

    'cuti'        => env('FEATURE_CUTI', true),
    'lembur'      => env('FEATURE_LEMBUR', true),
    'shift'       => env('FEATURE_SHIFT', true),
    'payroll'     => env('FEATURE_PAYROLL', true),
    'kinerja'     => env('FEATURE_KINERJA', true),
    'kpi'         => env('FEATURE_KPI', true),
    'rekrutmen'   => env('FEATURE_REKRUTMEN', true),
    'training'    => env('FEATURE_TRAINING', true),
    'absensi_gps' => env('FEATURE_ABSENSI_GPS', true),

];
