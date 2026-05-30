<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Reminder kontrak akan berakhir ──────────────────────────────────────────
// Jalankan tiap hari jam 08:00 → cek H-30 dan H-7
Schedule::command('kontrak:reminder')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/kontrak-reminder.log'));

// ─── Generate rekap absensi bulanan ──────────────────────────────────────────
// Jalankan tiap tanggal 1 jam 00:05 → generate rekap bulan lalu
Schedule::command('rekap:absensi')
    ->monthlyOn(1, '00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/rekap-absensi.log'));