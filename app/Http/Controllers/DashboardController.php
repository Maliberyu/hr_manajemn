<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Absensi;
use App\Models\PengajuanCuti;
use App\Models\Lembur;
use App\Models\Rekrutmen;
use App\Models\IHTPeserta;
use App\Models\TrainingEksternal;
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

        $stats = [
            'total_pegawai'     => $this->safe(fn() => Pegawai::aktif()->count()),
            'hadir_hari_ini'    => $this->safe(fn() => Absensi::hariIni()->where('status', 'hadir')->count()),
            'terlambat_hari'    => $this->safe(fn() => Absensi::hariIni()->terlambat()->count()),
            'cuti_menunggu'     => $this->safe(fn() => PengajuanCuti::menungguApproval()->count()),
            'lembur_menunggu'   => $this->safe(fn() => Lembur::menungguApproval()->count()),
            'lowongan_buka'     => $this->safe(fn() => Rekrutmen::buka()->count()),
            'training_berjalan' => $this->safe(fn() => \App\Models\IHT::where('status', 'aktif')->count()),
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

        return view('ess.dashboard', compact(
            'pegawai', 'absensiHariIni', 'cutiSaya', 'sisaCuti', 'pegawaiPj',
            'trainingIHT', 'trainingEksternal', 'expiringSoon'
        ));
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
