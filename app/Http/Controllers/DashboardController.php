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
use App\Models\PengajuanIjin;
use App\Models\TarifLembur;
use App\Models\HrNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
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

            'ijin_menunggu'     => $this->safe(fn() => $isAtasan
                ? PengajuanIjin::menungguApproval()->whereIn('nik', $nikBawahan)->count()
                : PengajuanIjin::menungguApproval()->count()),

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

        // ─── Rekap SDM chart data (real-time) ────────────────────────────────────
        $rekapDep   = $request->get('rekap_dep');
        $bulanRekap = (int) ($request->get('rekap_bulan', now()->month));
        $tahunRekap = (int) ($request->get('rekap_tahun', now()->year));

        $departemen = $this->safe(
            fn() => \App\Models\Departemen::orderBy('nama')->get(['dep_id', 'nama']),
            collect()
        );

        $grafikRekapAbsensi = $this->safe(function () use ($bulanRekap, $tahunRekap, $rekapDep, $isAtasan, $nikBawahan) {
            return DB::table('absensi as a')
                ->join('pegawai as p', 'a.pegawai_id', '=', 'p.id')
                ->join('departemen as d', 'p.departemen', '=', 'd.dep_id')
                ->whereMonth('a.tanggal', $bulanRekap)
                ->whereYear('a.tanggal', $tahunRekap)
                ->when($rekapDep, fn($q) => $q->where('p.departemen', $rekapDep))
                ->when($isAtasan && $nikBawahan, fn($q) => $q->whereIn('p.nik', $nikBawahan))
                ->selectRaw("d.dep_id, d.nama as dep_nama,
                    SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN a.status = 'alfa' THEN 1 ELSE 0 END) as alfa,
                    SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN a.terlambat_menit > 0 AND a.status = 'hadir' THEN 1 ELSE 0 END) as terlambat")
                ->groupBy('d.dep_id', 'd.nama')
                ->orderBy('d.nama')
                ->get()
                ->map(fn($r) => [
                    'dep'       => $r->dep_nama,
                    'hadir'     => (int) $r->hadir,
                    'sakit'     => (int) $r->sakit,
                    'alfa'      => (int) $r->alfa,
                    'izin'      => (int) $r->izin,
                    'terlambat' => (int) $r->terlambat,
                ]);
        }, collect());

        $grafikRekapCuti = $this->safe(function () use ($tahunRekap, $rekapDep, $isAtasan, $nikBawahan) {
            return PengajuanCuti::where('status', 'Disetujui')
                ->whereYear('tanggal', $tahunRekap)
                ->when($isAtasan && $nikBawahan, fn($q) => $q->whereIn('nik', $nikBawahan))
                ->when($rekapDep, fn($q) => $q->whereHas('pegawai', fn($p) => $p->where('departemen', $rekapDep)))
                ->selectRaw('urgensi, COUNT(*) as jumlah, SUM(jumlah) as total_hari')
                ->groupBy('urgensi')
                ->orderBy('urgensi')
                ->get()
                ->map(fn($r) => [
                    'jenis'      => $r->urgensi,
                    'jumlah'     => (int) $r->jumlah,
                    'total_hari' => (int) $r->total_hari,
                ]);
        }, collect());

        $grafikRekapIjin = $this->safe(function () use ($bulanRekap, $tahunRekap, $rekapDep, $isAtasan, $nikBawahan) {
            return PengajuanIjin::where('status', 'Disetujui')
                ->whereMonth('tanggal', $bulanRekap)
                ->whereYear('tanggal', $tahunRekap)
                ->when($isAtasan && $nikBawahan, fn($q) => $q->whereIn('nik', $nikBawahan))
                ->when($rekapDep, fn($q) => $q->whereHas('pegawai', fn($p) => $p->where('departemen', $rekapDep)))
                ->selectRaw('jenis, COUNT(*) as jumlah')
                ->groupBy('jenis')
                ->get()
                ->map(fn($r) => [
                    'jenis'  => PengajuanIjin::JENIS[$r->jenis] ?? $r->jenis,
                    'key'    => $r->jenis,
                    'jumlah' => (int) $r->jumlah,
                ]);
        }, collect());

        $grafikRekapPelatihan = $this->safe(function () use ($tahunRekap, $rekapDep, $isAtasan, $nikBawahan) {
            $ihtData = DB::table('hr_iht_peserta as ip')
                ->join('hr_iht as i', 'ip.iht_id', '=', 'i.id')
                ->join('pegawai as p', 'ip.pegawai_id', '=', 'p.id')
                ->join('departemen as d', 'p.departemen', '=', 'd.dep_id')
                ->where('ip.status', 'hadir')
                ->whereYear('i.tanggal_mulai', $tahunRekap)
                ->when($rekapDep, fn($q) => $q->where('p.departemen', $rekapDep))
                ->when($isAtasan && $nikBawahan, fn($q) => $q->whereIn('p.nik', $nikBawahan))
                ->selectRaw("d.dep_id, d.nama as dep_nama,
                    SUM((TIME_TO_SEC(TIMEDIFF(i.jam_selesai, i.jam_mulai)) / 3600) * (DATEDIFF(i.tanggal_selesai, i.tanggal_mulai) + 1)) as jam_iht")
                ->groupBy('d.dep_id', 'd.nama')
                ->get()->keyBy('dep_id');

            $eksternalData = DB::table('hr_training_eksternal as te')
                ->join('pegawai as p', 'te.pegawai_id', '=', 'p.id')
                ->join('departemen as d', 'p.departemen', '=', 'd.dep_id')
                ->whereIn('te.status', ['tervalidasi', 'disetujui'])
                ->whereYear('te.tanggal_mulai', $tahunRekap)
                ->when($rekapDep, fn($q) => $q->where('p.departemen', $rekapDep))
                ->when($isAtasan && $nikBawahan, fn($q) => $q->whereIn('p.nik', $nikBawahan))
                ->selectRaw("d.dep_id, d.nama as dep_nama,
                    SUM((DATEDIFF(te.tanggal_selesai, te.tanggal_mulai) + 1) * 8) as jam_eksternal")
                ->groupBy('d.dep_id', 'd.nama')
                ->get()->keyBy('dep_id');

            $allDepIds = collect(array_keys($ihtData->toArray()))
                ->merge(array_keys($eksternalData->toArray()))->unique();

            return $allDepIds->map(fn($depId) => [
                'dep'           => $ihtData[$depId]?->dep_nama ?? $eksternalData[$depId]?->dep_nama ?? $depId,
                'jam_iht'       => round((float) ($ihtData[$depId]?->jam_iht ?? 0), 1),
                'jam_eksternal' => round((float) ($eksternalData[$depId]?->jam_eksternal ?? 0), 1),
            ])->sortBy('dep')->values();
        }, collect());

        return view('dashboard', compact(
            'stats', 'absensiHariIni', 'cutiTerbaru',
            'lemburMenunggu', 'ultah', 'grafikAbsensi',
            'pegawaiBelumAdaAtasan', 'isAtasan', 'nikBawahan',
            'grafikRekapAbsensi', 'grafikRekapCuti', 'grafikRekapIjin', 'grafikRekapPelatihan',
            'departemen', 'rekapDep', 'bulanRekap', 'tahunRekap'
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

        // Riwayat Ijin (3 terbaru per jenis)
        $ijinSaya = $this->safe(
            fn() => PengajuanIjin::where('nik', $pegawai->nik)
                ->orderByDesc('tanggal')->limit(15)->get(),
            collect()
        );

        // Riwayat Lembur
        $lemburSaya = $this->safe(
            fn() => Lembur::where('pegawai_id', $pegawai->id)
                ->orderByDesc('tanggal')->limit(10)->get(),
            collect()
        );

        $tarifLembur = $this->safe(
            fn() => TarifLembur::getForDep($pegawai->departemen),
            null
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
            'trainingIHT', 'trainingEksternal', 'expiringSoon', 'slipGaji',
            'ijinSaya', 'lemburSaya', 'tarifLembur'
        ));
    }

    public function essStoreLembur(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        abort_unless($pegawai, 403);

        $validated = $request->validate([
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'jenis'       => 'required|in:HB,HR',
            'keterangan'  => 'required|max:255',
        ]);

        $durasi  = \Carbon\Carbon::parse($validated['jam_mulai'])
                    ->diffInMinutes(\Carbon\Carbon::parse($validated['jam_selesai'])) / 60;
        $tarif   = TarifLembur::getForDep($pegawai->departemen);
        $nominal = $durasi * ($validated['jenis'] === 'HR' ? ($tarif?->tarif_hr ?? 0) : ($tarif?->tarif_hb ?? 0));

        $adaAtasan  = AtasanPegawai::where('nik', $pegawai->nik)->exists();
        $lembur = Lembur::create([
            'pegawai_id'  => $pegawai->id,
            'tanggal'     => $validated['tanggal'],
            'jam_mulai'   => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'durasi_jam'  => round($durasi, 2),
            'jenis'       => $validated['jenis'],
            'keterangan'  => $validated['keterangan'],
            'nominal'     => $nominal,
            'status'      => $adaAtasan ? 'Menunggu Atasan' : 'Menunggu HRD',
        ]);

        $link = route('lembur.show', $lembur);
        if ($adaAtasan) {
            HrNotification::kirimKeAtasan($pegawai->nik, 'lembur_submitted',
                'Pengajuan Lembur Baru', "Ada pengajuan lembur dari {$pegawai->nama} menunggu persetujuan Anda.", $link);
        } else {
            HrNotification::kirimKeHrd('lembur_submitted', 'Pengajuan Lembur Baru',
                "Pengajuan lembur dari {$pegawai->nama} menunggu persetujuan HRD.", $link);
        }

        return redirect()->route('ess.dashboard', ['tab' => 'lembur'])
            ->with('lembur_success', 'Pengajuan lembur berhasil diajukan.');
    }

    public function essStoreIjin(Request $request, string $jenis)
    {
        $pegawai = auth()->user()->pegawai;
        abort_unless($pegawai, 403);
        abort_unless(array_key_exists($jenis, PengajuanIjin::JENIS), 404);

        $rules = ['tanggal' => 'required|date', 'alasan' => 'required|max:500'];
        if ($jenis === 'sakit') {
            $rules['file_surat'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
        }
        if (in_array($jenis, ['terlambat', 'pulang_duluan'])) {
            $rules['jam_mulai']   = 'required|date_format:H:i';
            $rules['jam_selesai'] = 'required|date_format:H:i';
        }
        $validated = $request->validate($rules);

        $durasi = null;
        if (!empty($validated['jam_mulai']) && !empty($validated['jam_selesai'])) {
            $durasi = abs(\Carbon\Carbon::parse($validated['jam_mulai'])->diffInMinutes(\Carbon\Carbon::parse($validated['jam_selesai'])));
        }

        $filePath = null;
        if ($jenis === 'sakit' && $request->hasFile('file_surat')) {
            $filePath = $request->file('file_surat')->store('ijin/surat/' . now()->format('Ym'), 'public');
        }

        $ijin = PengajuanIjin::create([
            'no_pengajuan' => PengajuanIjin::generateNomor($jenis),
            'nik'          => $pegawai->nik,
            'pegawai_id'   => $pegawai->id,
            'tanggal'      => $validated['tanggal'],
            'jenis'        => $jenis,
            'jam_mulai'    => $validated['jam_mulai'] ?? null,
            'jam_selesai'  => $validated['jam_selesai'] ?? null,
            'durasi_menit' => $durasi,
            'alasan'       => $validated['alasan'],
            'file_surat'   => $filePath,
            'status'       => 'Menunggu Atasan',
        ]);

        HrNotification::kirimKeAtasan($pegawai->nik, 'ijin_submitted',
            PengajuanIjin::JENIS[$jenis] . ' Baru',
            "Ada pengajuan " . strtolower(PengajuanIjin::JENIS[$jenis]) . " dari {$pegawai->nama}.",
            route('ijin.show', [$jenis, $ijin]));

        return redirect()->route('ess.dashboard', ['tab' => 'ijin'])
            ->with('ijin_success', 'Pengajuan ' . PengajuanIjin::JENIS[$jenis] . ' berhasil diajukan.');
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
