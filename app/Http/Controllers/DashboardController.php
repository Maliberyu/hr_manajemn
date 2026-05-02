<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\PengajuanCuti;
use App\Models\Lembur;
use App\Models\Rekrutmen;
use App\Models\Training;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_pegawai'     => $this->safe(fn() => Pegawai::aktif()->count()),
            'hadir_hari_ini'    => $this->safe(fn() => Absensi::hariIni()->where('status', 'hadir')->count()),
            'terlambat_hari'    => $this->safe(fn() => Absensi::hariIni()->terlambat()->count()),
            'cuti_menunggu'     => $this->safe(fn() => PengajuanCuti::menungguApproval()->count()),
            'lembur_menunggu'   => $this->safe(fn() => Lembur::menungguApproval()->count()),
            'lowongan_buka'     => $this->safe(fn() => Rekrutmen::buka()->count()),
            'training_berjalan' => $this->safe(fn() => Training::where('status', 'berjalan')->count()),
        ];

        $absensiHariIni = $this->safe(function () {
            return Absensi::hariIni()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
        }, collect());

        $cutiTerbaru = $this->safe(function () {
            return PengajuanCuti::with('pegawai')
                ->menungguApproval()
                ->latest('tanggal')
                ->limit(5)->get();
        }, collect());

        $lemburMenunggu = $this->safe(function () {
            return Lembur::with('pegawai')
                ->menungguApproval()
                ->latest('tanggal')
                ->limit(5)->get();
        }, collect());

        $ultah = $this->safe(function () {
            return Pegawai::aktif()
                ->whereMonth('tgl_lahir', now()->month)
                ->orderByRaw('DAY(tgl_lahir)')
                ->limit(10)->get(['nama', 'jbtn', 'tgl_lahir', 'photo']);
        }, collect());

        $grafikAbsensi = $this->safe(function () {
            return collect(range(6, 0))->map(function ($daysAgo) {
                $tgl = now()->subDays($daysAgo);
                return [
                    'label'     => $tgl->locale('id')->isoFormat('ddd, D MMM'),
                    'hadir'     => Absensi::whereDate('tanggal', $tgl)->where('status', 'hadir')->count(),
                    'terlambat' => Absensi::whereDate('tanggal', $tgl)->terlambat()->count(),
                    'alfa'      => Absensi::whereDate('tanggal', $tgl)->where('status', 'alfa')->count(),
                ];
            });
        }, collect(range(6, 0))->map(fn($i) => [
            'label'     => now()->subDays($i)->locale('id')->isoFormat('ddd, D MMM'),
            'hadir'     => 0,
            'terlambat' => 0,
            'alfa'      => 0,
        ]));

        return view('dashboard', compact(
            'stats', 'absensiHariIni', 'cutiTerbaru',
            'lemburMenunggu', 'ultah', 'grafikAbsensi'
        ));
    }

    public function ess()
    {
        $pegawai = auth()->user()->pegawai;
        abort_unless($pegawai, 403);

        $absensiHariIni = $this->safe(
            fn() => Absensi::where('pegawai_id', $pegawai->id)->whereDate('tanggal', today())->first()
        );

        $cutiSaya = $this->safe(
            fn() => PengajuanCuti::where('nik', $pegawai->nik)->orderByDesc('tanggal')->limit(5)->get(),
            collect()
        );

        $lemburSaya = $this->safe(
            fn() => Lembur::where('pegawai_id', $pegawai->id)->orderByDesc('tanggal')->limit(5)->get(),
            collect()
        );

        $sisaCuti = max(0, 12 - ($pegawai->cuti_diambil ?? 0));

        return view('ess.dashboard', compact(
            'pegawai', 'absensiHariIni', 'cutiSaya', 'lemburSaya', 'sisaCuti'
        ));
    }

    /** Jalankan query, kembalikan $default jika tabel belum siap. */
    private function safe(callable $fn, $default = 0)
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
