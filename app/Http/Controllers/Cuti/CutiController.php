<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCuti;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CutiController extends Controller
{
    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = PengajuanCuti::with(['pegawai', 'penanggungJawab'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->tahun,  fn($q, $t) => $q->whereYear('tanggal', $t))
            ->when($request->bulan,  fn($q, $b) => $q->whereMonth('tanggal', $b))
            ->when($request->q, fn($q, $s) =>
                $q->whereHas('pegawai', fn($p) => $p->cari($s)))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        // Karyawan & atasan hanya lihat milik sendiri
        if (auth()->user()->hasRole(['karyawan', 'atasan'])) {
            $query->where('nik', auth()->user()->pegawai->nik ?? '');
        }

        $pengajuan   = $query->paginate(20)->withQueryString();
        $totalAtasan = PengajuanCuti::menungguAtasan()->count();
        $totalHrd    = PengajuanCuti::menungguHrd()->count();

        return view('cuti.index', compact('pengajuan', 'totalAtasan', 'totalHrd'));
    }

    // ─── Form pengajuan baru ──────────────────────────────────────────────────

    public function create()
    {
        $pegawai = auth()->user()->hasRole(['karyawan', 'atasan'])
            ? collect([auth()->user()->pegawai])->filter()
            : Pegawai::aktif()->orderBy('nama')->get(['id', 'nama', 'nik', 'jbtn', 'cuti_diambil']);

        $pj = Pegawai::aktif()->orderBy('nama')->get(['id', 'nama', 'nik']);

        return view('cuti.create', compact('pegawai', 'pj'));
    }

    // ─── Store pengajuan ──────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'           => 'required|exists:pegawai,nik',
            'tanggal_awal'  => 'required|date|after_or_equal:today',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'urgensi'       => 'required|in:' . implode(',', PengajuanCuti::JENIS_CUTI),
            'alamat'        => 'required|max:255',
            'kepentingan'   => 'required|max:500',
            'nik_pj'        => 'nullable|exists:pegawai,nik|different:nik',
        ]);

        $awal   = Carbon::parse($validated['tanggal_awal']);
        $akhir  = Carbon::parse($validated['tanggal_akhir']);
        $jumlah = $awal->diffInWeekdays($akhir) + 1;

        $tumpang = PengajuanCuti::where('nik', $validated['nik'])
            ->whereNotIn('status', ['Ditolak Atasan', 'Ditolak HRD'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('tanggal_awal', [$validated['tanggal_awal'], $validated['tanggal_akhir']])
                  ->orWhereBetween('tanggal_akhir', [$validated['tanggal_awal'], $validated['tanggal_akhir']]);
            })->exists();

        if ($tumpang) {
            return back()->withErrors(['tanggal_awal' => 'Tanggal cuti tumpang tindih dengan pengajuan yang sedang diproses.'])->withInput();
        }

        $noPengajuan = $this->generateNomor();

        PengajuanCuti::create([
            ...$validated,
            'no_pengajuan' => $noPengajuan,
            'tanggal'      => today(),
            'jumlah'       => $jumlah,
            'status'       => 'Menunggu Atasan',
        ]);

        return redirect()->route('cuti.index')
            ->with('success', "Pengajuan {$noPengajuan} berhasil diajukan ({$jumlah} hari kerja). Menunggu persetujuan atasan langsung.");
    }

    // ─── Detail ───────────────────────────────────────────────────────────────

    public function show(PengajuanCuti $cuti)
    {
        $cuti->load(['pegawai.departemenRef', 'penanggungJawab']);
        return view('cuti.show', compact('cuti'));
    }

    // ─── Approve Atasan ───────────────────────────────────────────────────────

    public function approveAtasan(Request $request, PengajuanCuti $cuti)
    {
        if (! $cuti->bisaApproveAtasan()) {
            return back()->withErrors(['status' => 'Status pengajuan tidak sesuai untuk aksi ini.']);
        }

        $request->validate(['catatan_atasan' => 'nullable|max:500']);

        $cuti->update([
            'status'             => 'Menunggu HRD',
            'catatan_atasan'     => $request->catatan_atasan,
            'approved_atasan_at' => now(),
        ]);

        return back()->with('success', "Disetujui atasan. Pengajuan {$cuti->no_pengajuan} diteruskan ke HRD.");
    }

    // ─── Tolak Atasan ─────────────────────────────────────────────────────────

    public function tolakAtasan(Request $request, PengajuanCuti $cuti)
    {
        if (! $cuti->bisaApproveAtasan()) {
            return back()->withErrors(['status' => 'Status pengajuan tidak sesuai untuk aksi ini.']);
        }

        $request->validate(['catatan_atasan' => 'required|max:500']);

        $cuti->update([
            'status'         => 'Ditolak Atasan',
            'catatan_atasan' => $request->catatan_atasan,
        ]);

        return back()->with('success', "Pengajuan {$cuti->no_pengajuan} ditolak oleh atasan langsung.");
    }

    // ─── Approve HRD ──────────────────────────────────────────────────────────

    public function approveHrd(Request $request, PengajuanCuti $cuti)
    {
        if (! $cuti->bisaApproveHrd()) {
            return back()->withErrors(['status' => 'Status pengajuan tidak sesuai untuk aksi ini.']);
        }

        $request->validate(['catatan_hrd' => 'nullable|max:500']);

        $cuti->update([
            'status'          => 'Disetujui',
            'catatan_hrd'     => $request->catatan_hrd,
            'approved_hrd_at' => now(),
        ]);

        Pegawai::where('nik', $cuti->nik)->increment('cuti_diambil', $cuti->jumlah);

        return back()->with('success', "Pengajuan {$cuti->no_pengajuan} DISETUJUI. Saldo cuti dikurangi {$cuti->jumlah} hari.");
    }

    // ─── Tolak HRD ────────────────────────────────────────────────────────────

    public function tolakHrd(Request $request, PengajuanCuti $cuti)
    {
        if (! $cuti->bisaApproveHrd()) {
            return back()->withErrors(['status' => 'Status pengajuan tidak sesuai untuk aksi ini.']);
        }

        $request->validate(['catatan_hrd' => 'required|max:500']);

        $cuti->update([
            'status'      => 'Ditolak HRD',
            'catatan_hrd' => $request->catatan_hrd,
        ]);

        return back()->with('success', "Pengajuan {$cuti->no_pengajuan} ditolak oleh HRD.");
    }

    // ─── Cetak surat cuti PDF ────────────────────────────────────────────────

    public function cetak(PengajuanCuti $cuti)
    {
        if ($cuti->status !== 'Disetujui') {
            return back()->withErrors(['status' => 'Surat hanya bisa dicetak setelah cuti disetujui.']);
        }

        $cuti->load(['pegawai.departemenRef', 'penanggungJawab']);

        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->withErrors(['status' => 'PDF library belum diinstall. Jalankan: composer require barryvdh/laravel-dompdf']);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('cuti.pdf.surat', compact('cuti'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download("Surat_Cuti_{$cuti->no_pengajuan}.pdf");
    }

    // ─── Saldo cuti per pegawai ───────────────────────────────────────────────

    public function saldo(Request $request)
    {
        $pegawai = Pegawai::aktif()
            ->with('departemenRef')
            ->when($request->departemen, fn($q, $d) => $q->departemen($d))
            ->withSum(
                ['pengajuanCuti as cuti_tahun_ini' => fn($q) =>
                    $q->where('status', 'Disetujui')->whereYear('tanggal', now()->year)
                ],
                'jumlah'
            )
            ->orderBy('nama')
            ->paginate(30)->withQueryString();

        return view('cuti.saldo', compact('pegawai'));
    }

    // ─── Private: generate nomor ─────────────────────────────────────────────

    private function generateNomor(): string
    {
        $prefix = 'CT/' . now()->format('Ym') . '/';
        $last   = PengajuanCuti::where('no_pengajuan', 'like', $prefix . '%')
                               ->orderByDesc('no_pengajuan')
                               ->value('no_pengajuan');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }
}
