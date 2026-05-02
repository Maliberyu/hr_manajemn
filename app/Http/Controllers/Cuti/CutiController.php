<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\PengajuanCuti;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CutiController extends Controller
{
    public function __construct()
    {
    }

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = PengajuanCuti::with(['pegawai', 'penanggungJawab'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->tahun, fn($q, $t) => $q->whereYear('tanggal', $t))
            ->when($request->bulan, fn($q, $b) => $q->whereMonth('tanggal', $b))
            ->when($request->q, fn($q, $s) =>
                $q->whereHas('pegawai', fn($p) => $p->cari($s)))
            ->orderByDesc('tanggal');

        // Karyawan biasa hanya lihat pengajuan sendiri
        if (auth()->user()->hasRole('karyawan')) {
            $query->where('nik', auth()->user()->pegawai->nik);
        }

        $pengajuan = $query->paginate(20)->withQueryString();

        $totalMenunggu = PengajuanCuti::menungguApproval()->count();

        return view('cuti.index', compact('pengajuan', 'totalMenunggu'));
    }

    // ─── Form pengajuan baru ──────────────────────────────────────────────────

    public function create()
    {
        $pegawai = auth()->user()->hasRole('karyawan')
            ? collect([auth()->user()->pegawai])
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
            'nik_pj'        => 'required|exists:pegawai,nik|different:nik',
        ]);

        $awal   = Carbon::parse($validated['tanggal_awal']);
        $akhir  = Carbon::parse($validated['tanggal_akhir']);
        $jumlah = $awal->diffInWeekdays($akhir) + 1;

        // Cek tumpang tindih pengajuan
        $tumpang = PengajuanCuti::where('nik', $validated['nik'])
            ->where('status', '!=', 'Ditolak')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('tanggal_awal', [$validated['tanggal_awal'], $validated['tanggal_akhir']])
                  ->orWhereBetween('tanggal_akhir', [$validated['tanggal_awal'], $validated['tanggal_akhir']]);
            })->exists();

        if ($tumpang) {
            return back()->withErrors(['tanggal_awal' => 'Tanggal cuti tumpang tindih dengan pengajuan lain.']);
        }

        $noPengajuan = $this->generateNomor();

        PengajuanCuti::create([
            ...$validated,
            'no_pengajuan' => $noPengajuan,
            'tanggal'      => today(),
            'jumlah'       => $jumlah,
            'status'       => 'Proses Pengajuan',
        ]);

        return redirect()->route('cuti.index')
            ->with('success', "Pengajuan cuti {$noPengajuan} berhasil diajukan ({$jumlah} hari kerja).");
    }

    // ─── Detail pengajuan ─────────────────────────────────────────────────────

    public function show(PengajuanCuti $cuti)
    {
        $cuti->load(['pegawai.departemenRef', 'penanggungJawab']);
        return view('cuti.show', compact('cuti'));
    }

    // ─── Approve ──────────────────────────────────────────────────────────────

    public function approve(PengajuanCuti $cuti)
    {
        if ($cuti->status !== 'Proses Pengajuan') {
            return back()->withErrors(['status' => 'Pengajuan ini sudah diproses.']);
        }

        $cuti->update(['status' => 'Disetujui']);

        // Tambah cuti_diambil di tabel pegawai
        Pegawai::where('nik', $cuti->nik)
               ->increment('cuti_diambil', $cuti->jumlah);

        return back()->with('success', "Pengajuan {$cuti->no_pengajuan} disetujui.");
    }

    // ─── Tolak ────────────────────────────────────────────────────────────────

    public function tolak(Request $request, PengajuanCuti $cuti)
    {
        $request->validate(['alasan_tolak' => 'required|max:255']);

        if ($cuti->status !== 'Proses Pengajuan') {
            return back()->withErrors(['status' => 'Pengajuan ini sudah diproses.']);
        }

        $cuti->update([
            'status'      => 'Ditolak',
            'kepentingan' => $cuti->kepentingan . ' [DITOLAK: ' . $request->alasan_tolak . ']',
        ]);

        return back()->with('success', "Pengajuan {$cuti->no_pengajuan} ditolak.");
    }

    // ─── Cetak surat cuti PDF ────────────────────────────────────────────────

    public function cetak(PengajuanCuti $cuti)
    {
        $cuti->load(['pegawai.departemenRef', 'penanggungJawab']);

        $pdf = Pdf::loadView('cuti.pdf.surat', compact('cuti'))
                  ->setPaper('a4', 'portrait');

        return $pdf->download("Surat_Cuti_{$cuti->no_pengajuan}.pdf");
    }

    // ─── Saldo cuti per pegawai ───────────────────────────────────────────────

    public function saldo(Request $request)
    {
        $pegawai = Pegawai::aktif()
            ->with('departemenRef')
            ->when($request->departemen, fn($q, $d) => $q->departemen($d))
            ->withCount([
                'pengajuanCuti as total_cuti_disetujui' => fn($q) =>
                    $q->where('status', 'Disetujui')->whereYear('tanggal', now()->year),
            ])
            ->orderBy('nama')
            ->paginate(30);

        return view('cuti.saldo', compact('pegawai'));
    }

    // ─── Private: generate nomor pengajuan ───────────────────────────────────

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
