<?php

namespace App\Console\Commands;

use App\Http\Controllers\Absensi\AbsensiController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRekapAbsensi extends Command
{
    protected $signature = 'rekap:absensi
                            {--bulan= : Bulan (1-12), default bulan lalu}
                            {--tahun= : Tahun, default tahun sekarang}';

    protected $description = 'Generate atau update rekap absensi bulanan ke tabel rekap_absensi';

    public function handle(): int
    {
        $bulan = (int) ($this->option('bulan') ?? now()->subMonth()->month);
        $tahun = (int) ($this->option('tahun') ?? now()->subMonth()->year);

        $label = Carbon::create($tahun, $bulan)->translatedFormat('F Y');
        $this->info("Generating rekap absensi: {$label} ...");

        $jumlah = AbsensiController::prosesGenerateRekap($bulan, $tahun);

        $this->info("✓ Selesai. {$jumlah} karyawan diproses untuk periode {$label}.");

        return Command::SUCCESS;
    }
}
