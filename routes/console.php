<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Generate rekap absensi bulanan ──────────────────────────────────────────
// Jalankan tiap tanggal 1 jam 00:05 → generate rekap bulan lalu
Schedule::command('rekap:absensi')
    ->monthlyOn(1, '00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/rekap-absensi.log'));