<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\PengajuanCuti;
use App\Models\Lembur;
use App\Models\Rekrutmen;
use App\Models\Training;
use App\Models\EvaluasiKinerjaPegawai;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Statistik utama ───────────────────────────────────────────────────
        $stats = [
            'total_pegawai'    => Pegawai::aktif()->count(),
            'hadir_hari_ini'   => Absensi::hariIni()->where('status', 'hadir')->count(),
            'terlambat_hari'   => Absensi::hariIni()->terlambat()->count(),
            'cuti_menunggu'    => PengajuanCuti::menungguApproval()->count(),
            'lembur_menunggu'  => Lembur::menungguApproval()->count(),
            'lowongan_buka'    => Rekrutmen::buka()->count(),
            'training_berjalan'=> Training::where('status', 'berjalan')->count(),
        ];

        // ── Absensi hari ini per status ───────────────────────────────────────
        $absensiHariIni = Absensi::hariIni()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // ── Pengajuan cuti terbaru (5 item) ───────────────────────────────────
        $cutiTerbaru = PengajuanCuti::with('pegawai')
            ->menungguApproval()
            ->latest('tanggal')
            ->limit(5)->get();

        // ── Lembur menunggu approval ───────────────────────────────────────────
        $lemburMenunggu = Lembur::with('pegawai')
            ->menungguApproval()
            ->latest('tanggal')
            ->limit(5)->get();

        // ── Karyawan berulang tahun bulan ini ─────────────────────────────────
        $ultah = Pegawai::aktif()
            ->whereMonth('tgl_lahir', now()->month)
            ->orderByRaw('DAY(tgl_lahir)')
            ->limit(10)->get(['nama', 'jbtn', 'tgl_lahir', 'photo']);

        // ── Grafik absensi 7 hari terakhir (JSON untuk Chart.js) ─────────────
        $grafikAbsensi = collect(range(6, 0))->map(function ($daysAgo) {
            $tgl = now()->subDays($daysAgo);
            return [
                'label'    => $tgl->translatedFormat('D, d M'),
                'hadir'    => Absensi::whereDate('tanggal', $tgl)->where('status', 'hadir')->count(),
                'terlambat'=> Absensi::whereDate('tanggal', $tgl)->terlambat()->count(),
                'alfa'     => Absensi::whereDate('tanggal', $tgl)->where('status', 'alfa')->count(),
            ];
        });

        return view('dashboard', compact(
            'stats', 'absensiHariIni', 'cutiTerbaru',
            'lemburMenunggu', 'ultah', 'grafikAbsensi'
        ));
    }

    // ─── Dashboard ESS (karyawan biasa) ──────────────────────────────────────

    public function ess()
    {
        $pegawai = auth()->user()->pegawai;
        abort_unless($pegawai, 403);

        $absensiHariIni = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', today())->first();

        $cutiSaya = PengajuanCuti::where('nik', $pegawai->nik)
            ->orderByDesc('tanggal')->limit(5)->get();

        $lemburSaya = Lembur::where('pegawai_id', $pegawai->id)
            ->orderByDesc('tanggal')->limit(5)->get();

        $sisaCuti = max(0, 12 - ($pegawai->cuti_diambil ?? 0));

        return view('ess.dashboard', compact(
            'pegawai', 'absensiHariIni', 'cutiSaya', 'lemburSaya', 'sisaCuti'
        ));
    }
}
