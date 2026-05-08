<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\PengajuanCuti;
use App\Models\Lembur;
use App\Models\Rekrutmen;
use App\Models\IHTPeserta;
use App\Models\TrainingEksternal;
use App\Models\SlipGaji;
use App\Models\AtasanPegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Karyawan tidak punya akses dashboard HR — arahkan ke ESS
        if (auth()->user()->hasRole('karyawan')) {
            return redirect()->route('ess.dashboard');
        }

        $isAtasan    = auth()->user()->hasRole('atasan');
        $nikBawahan  = [];
        $pegawaiIds  = [];

        if ($isAtasan) {
            $nikBawahan = AtasanPegawai::nikBawahan(auth()->id());
            $pegawaiIds = $this->safe(
                fn() => Pegawai::whereIn('nik', $nikBawahan)->pluck('id')->toArray(),
                []
            );
        }

        $stats = [
            'total_pegawai'     => $this->safe(fn() => $isAtasan
                ? Pegawai::aktif()->whereIn('nik', $nikBawahan)->count()
                : Pegawai::aktif()->count()),

            'hadir_hari_ini'    => $this->safe(fn() => $isAtasan
                ? Absensi::hariIni()->where('status', 'hadir')->whereIn('pegawai_id', $pegawaiIds)->count()
                : Absensi::hariIni()->where('status', 'hadir')->count()),

            'terlambat_hari'    => $this->safe(fn() => $isAtasan
                ? Absensi::hariIni()->terlambat()->whereIn('pegawai_id', $pegawaiIds)->count()
                : Absensi::hariIni()->terlambat()->count()),

            'cuti_menunggu'     => $this->safe(fn() => $isAtasan
                ? PengajuanCuti::menungguApproval()->whereIn('nik', $nikBawahan)->count()
                : PengajuanCuti::menungguApproval()->count()),

            'lembur_menunggu'   => $this->safe(fn() => $isAtasan
                ? Lembur::menungguApproval()->whereIn('pegawai_id', $pegawaiIds)->count()
                : Lembur::menungguApproval()->count()),

            'lowongan_buka'     => $this->safe(fn() => Rekrutmen::buka()->count()),
            'training_berjalan' => $this->safe(fn() => \App\Models\IHT::where('status', 'aktif')->count()),
        ];

        $absensiHariIni = $this->safe(function () use ($isAtasan, $pegawaiIds) {
            $q = Absensi::hariIni();
            if ($isAtasan) $q->whereIn('pegawai_id', $pegawaiIds);
            return $q->selectRaw('status, count(*) as total')
                     ->groupBy('status')
                     ->pluck('total', 'status');
        }, collect());

        $cutiTerbaru = $this->safe(function () use ($isAtasan, $nikBawahan) {
            $q = PengajuanCuti::with('pegawai')->menungguApproval()->latest('tanggal');
            if ($isAtasan) $q->whereIn('nik', $nikBawahan);
            return $q->limit(5)->get();
        }, collect());

        $lemburMenunggu = $this->safe(function () use ($isAtasan, $pegawaiIds) {
            $q = Lembur::with('pegawai')->menungguApproval()->latest('tanggal');
            if ($isAtasan) $q->whereIn('pegawai_id', $pegawaiIds);
            return $q->limit(5)->get();
        }, collect());

        $ultah = $this->safe(function () use ($isAtasan, $nikBawahan) {
            $q = Pegawai::aktif()->whereMonth('tgl_lahir', now()->month)->orderByRaw('DAY(tgl_lahir)');
            if ($isAtasan) $q->whereIn('nik', $nikBawahan);
            return $q->limit(10)->get(['nama', 'jbtn', 'tgl_lahir', 'photo']);
        }, collect());

        $grafikAbsensi = $this->safe(function () use ($isAtasan, $pegawaiIds) {
            return collect(range(6, 0))->map(function ($daysAgo) use ($isAtasan, $pegawaiIds) {
                $tgl = now()->subDays($daysAgo);
                $base = fn() => $isAtasan
                    ? Absensi::whereDate('tanggal', $tgl)->whereIn('pegawai_id', $pegawaiIds)
                    : Absensi::whereDate('tanggal', $tgl);
                return [
                    'label'     => $tgl->locale('id')->isoFormat('ddd, D MMM'),
                    'hadir'     => (clone $base())->where('status', 'hadir')->count(),
                    'terlambat' => (clone $base())->terlambat()->count(),
                    'alfa'      => (clone $base())->where('status', 'alfa')->count(),
                ];
            });
        }, collect(range(6, 0))->map(fn($i) => [
            'label'     => now()->subDays($i)->locale('id')->isoFormat('ddd, D MMM'),
            'hadir'     => 0, 'terlambat' => 0, 'alfa' => 0,
        ]));

        $pegawaiBelumAdaAtasan = $this->safe(
            fn() => $isAtasan ? 0 : Pegawai::aktif()->whereDoesntHave('atasanRecord')->count(),
            0
        );

        return view('dashboard', compact(
            'stats', 'absensiHariIni', 'cutiTerbaru',
            'lemburMenunggu', 'ultah', 'grafikAbsensi',
            'pegawaiBelumAdaAtasan', 'isAtasan', 'nikBawahan'
        ));
    }

    public function ess()
    {
        $pegawai = auth()->user()->pegawai;

        if (!$pegawai) {
            return view('ess.not_linked');
        }

        $absensiHariIni = $this->safe(
            fn() => Absensi::where('pegawai_id', $pegawai->id)->whereDate('tanggal', today())->first()
        );

        $cutiSaya = $this->safe(
            fn() => PengajuanCuti::where('nik', $pegawai->nik)
                ->orderByDesc('tanggal')->orderByDesc('id')->limit(10)->get(),
            collect()
        );

        // Hitung sisa cuti dari tabel hr_pengajuan_cuti tahun ini
        $diambilTahunIni = $this->safe(
            fn() => PengajuanCuti::where('nik', $pegawai->nik)
                ->where('status', 'Disetujui')
                ->whereYear('tanggal', now()->year)
                ->sum('jumlah'),
            0
        );
        $sisaCuti = max(0, PengajuanCuti::HAK_CUTI_TAHUNAN - $diambilTahunIni);

        // Daftar pegawai lain untuk dropdown penanggung jawab
        $pegawaiPj = $this->safe(
            fn() => Pegawai::aktif()->where('nik', '!=', $pegawai->nik)
                ->orderBy('nama')->get(['nik', 'nama', 'jbtn']),
            collect()
        );

        // Riwayat training karyawan
        $trainingIHT = $this->safe(
            fn() => IHTPeserta::with('iht')
                ->where('pegawai_id', $pegawai->id)
                ->orderByDesc('created_at')->limit(20)->get(),
            collect()
        );

        $trainingEksternal = $this->safe(
            fn() => TrainingEksternal::where('pegawai_id', $pegawai->id)
                ->orderByDesc('created_at')->limit(20)->get(),
            collect()
        );

        // Badge: sertifikat akan expired dalam 30 hari
        $expiringSoon = $this->safe(
            fn() => TrainingEksternal::where('pegawai_id', $pegawai->id)
                ->where('status', 'tervalidasi')
                ->whereNotNull('masa_berlaku')
                ->whereDate('masa_berlaku', '>=', today())
                ->whereDate('masa_berlaku', '<=', today()->addDays(30))
                ->count(),
            0
        );

        $slipGaji = $this->safe(
            fn() => SlipGaji::where('pegawai_id', $pegawai->id)
                ->final()
                ->with('komponenSlip')
                ->orderByDesc('tahun')
                ->orderByDesc('bulan')
                ->get(),
            collect()
        );

        return view('ess.dashboard', compact(
            'pegawai', 'absensiHariIni', 'cutiSaya', 'sisaCuti', 'pegawaiPj',
            'trainingIHT', 'trainingEksternal', 'expiringSoon', 'slipGaji'
        ));
    }

    public function essSlipPdf(SlipGaji $slip)
    {
        $pegawai = auth()->user()->pegawai;
        abort_if(!$pegawai || $slip->pegawai_id !== $pegawai->id, 403, 'Slip ini bukan milik Anda.');

        $slip->load(['pegawai.departemenRef', 'pegawai.payrollSetting', 'komponenSlip']);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.pdf.slip', compact('slip'))
                      ->setPaper('a5', 'landscape');
            $nama = str_replace(' ', '_', $slip->pegawai?->nama ?? 'slip');
            return $pdf->download("SlipGaji_{$nama}_{$slip->bulan}_{$slip->tahun}.pdf");
        }

        return view('payroll.pdf.slip', compact('slip'));
    }

    public function essStoreCuti(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        abort_unless($pegawai, 403);

        $validated = $request->validate([
            'tanggal_awal'  => 'required|date|after_or_equal:today',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'urgensi'       => 'required|in:' . implode(',', PengajuanCuti::JENIS_CUTI),
            'alamat'        => 'required|max:255',
            'kepentingan'   => 'required|max:500',
            'nik_pj'        => 'nullable|exists:pegawai,nik',
        ]);

        $awal   = Carbon::parse($validated['tanggal_awal']);
        $akhir  = Carbon::parse($validated['tanggal_akhir']);
        $jumlah = $awal->diffInWeekdays($akhir) + 1;

        $tumpang = PengajuanCuti::where('nik', $pegawai->nik)
            ->whereNotIn('status', ['Ditolak Atasan', 'Ditolak HRD'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('tanggal_awal', [$validated['tanggal_awal'], $validated['tanggal_akhir']])
                  ->orWhereBetween('tanggal_akhir', [$validated['tanggal_awal'], $validated['tanggal_akhir']]);
            })->exists();

        if ($tumpang) {
            return back()->withErrors(['tanggal_awal' => 'Tanggal tumpang tindih dengan pengajuan yang sedang diproses.'])->withInput();
        }

        // Generate nomor
        $prefix = 'CT/' . now()->format('Ym') . '/';
        $last   = PengajuanCuti::where('no_pengajuan', 'like', $prefix . '%')
                               ->orderByDesc('no_pengajuan')->value('no_pengajuan');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        $no     = $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);

        PengajuanCuti::create([
            ...$validated,
            'nik'          => $pegawai->nik,
            'no_pengajuan' => $no,
            'tanggal'      => today(),
            'jumlah'       => $jumlah,
            'status'       => 'Menunggu Atasan',
        ]);

        return redirect()->route('ess.dashboard', ['tab' => 'cuti'])
            ->with('cuti_success', "Pengajuan {$no} berhasil ({$jumlah} hari kerja). Menunggu persetujuan atasan.");
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
