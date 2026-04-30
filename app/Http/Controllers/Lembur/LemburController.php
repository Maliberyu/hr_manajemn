<?php

namespace App\Http\Controllers\Lembur;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use App\Models\Pegawai;
use App\Models\SetLemburHB;
use App\Models\SetLemburHR;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LemburController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:lembur.view')->only(['index', 'show']);
        $this->middleware('permission:lembur.create')->only(['create', 'store']);
        $this->middleware('permission:lembur.approve')->only(['approve', 'tolak']);
        $this->middleware('permission:lembur.setting')->only(['setting', 'updateSetting']);
    }

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Lembur::with(['pegawai.departemenRef', 'approver'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->bulan, fn($q, $b) => $q->whereMonth('tanggal', $b))
            ->when($request->tahun, fn($q, $t) => $q->whereYear('tanggal', $t))
            ->when($request->q, fn($q, $s) =>
                $q->whereHas('pegawai', fn($p) => $p->cari($s)));

        // Karyawan hanya lihat lembur sendiri
        if (auth()->user()->hasRole('karyawan')) {
            $query->where('pegawai_id', auth()->user()->pegawai?->id);
        }

        $lembur      = $query->orderByDesc('tanggal')->paginate(25)->withQueryString();
        $totalMenunggu = Lembur::menungguApproval()->count();

        return view('lembur.index', compact('lembur', 'totalMenunggu'));
    }

    // ─── Form pengajuan lembur ────────────────────────────────────────────────

    public function create()
    {
        $pegawai   = auth()->user()->hasRole('karyawan')
            ? collect([auth()->user()->pegawai])
            : Pegawai::aktif()->orderBy('nama')->get(['id', 'nama', 'nik', 'jbtn']);

        $tarifHB = SetLemburHB::tarifAktif();
        $tarifHR = SetLemburHR::tarifAktif();

        return view('lembur.create', compact('pegawai', 'tarifHB', 'tarifHR'));
    }

    // ─── Simpan pengajuan ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id'  => 'required|exists:pegawai,id',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis'       => 'required|in:HB,HR',
            'keterangan'  => 'required|max:255',
        ]);

        $mulai   = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_mulai']);
        $selesai = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_selesai']);
        $durasi  = $mulai->diffInMinutes($selesai) / 60;

        // Nominal otomatis
        $tarif   = $validated['jenis'] === 'HR'
            ? SetLemburHR::tarifAktif()
            : SetLemburHB::tarifAktif();
        $nominal = $durasi * $tarif;

        Lembur::create([
            ...$validated,
            'jam_mulai'   => $mulai,
            'jam_selesai' => $selesai,
            'durasi_jam'  => round($durasi, 2),
            'nominal'     => $nominal,
            'status'      => 'diajukan',
        ]);

        return redirect()->route('lembur.index')
            ->with('success', "Pengajuan lembur berhasil. Nominal estimasi: Rp " . number_format($nominal, 0, ',', '.'));
    }

    // ─── Detail ───────────────────────────────────────────────────────────────

    public function show(Lembur $lembur)
    {
        $lembur->load(['pegawai.departemenRef', 'approver']);
        return view('lembur.show', compact('lembur'));
    }

    // ─── Approve ──────────────────────────────────────────────────────────────

    public function approve(Request $request, Lembur $lembur)
    {
        if ($lembur->status !== 'diajukan') {
            return back()->withErrors(['status' => 'Pengajuan sudah diproses sebelumnya.']);
        }

        $lembur->update([
            'status'           => 'disetujui',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'catatan_approval' => $request->catatan,
        ]);

        return back()->with('success', "Lembur {$lembur->pegawai->nama} disetujui.");
    }

    // ─── Tolak ────────────────────────────────────────────────────────────────

    public function tolak(Request $request, Lembur $lembur)
    {
        $request->validate(['catatan_approval' => 'required|max:255']);

        $lembur->update([
            'status'           => 'ditolak',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'catatan_approval' => $request->catatan_approval,
        ]);

        return back()->with('success', "Pengajuan lembur ditolak.");
    }

    // ─── Rekap lembur bulanan ─────────────────────────────────────────────────

    public function rekap(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $rekap = Pegawai::aktif()
            ->withSum([
                'lembur as total_jam_lembur' => fn($q) =>
                    $q->where('status', 'disetujui')->bulan($tahun, $bulan)
            ], 'durasi_jam')
            ->withSum([
                'lembur as total_nominal_lembur' => fn($q) =>
                    $q->where('status', 'disetujui')->bulan($tahun, $bulan)
            ], 'nominal')
            ->having('total_jam_lembur', '>', 0)
            ->orderByDesc('total_jam_lembur')
            ->paginate(30);

        return view('lembur.rekap', compact('rekap', 'bulan', 'tahun'));
    }

    // ─── Setting tarif lembur ─────────────────────────────────────────────────

    public function setting()
    {
        $tarifHB = SetLemburHB::first();
        $tarifHR = SetLemburHR::first();
        return view('lembur.setting', compact('tarifHB', 'tarifHR'));
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'tarif_hb' => 'required|numeric|min:0',
            'tarif_hr' => 'required|numeric|min:0',
        ]);

        SetLemburHB::truncate();
        SetLemburHB::create(['tnj' => $request->tarif_hb]);

        SetLemburHR::truncate();
        SetLemburHR::create(['tnj' => $request->tarif_hr]);

        return back()->with('success', 'Tarif lembur berhasil diperbarui.');
    }
}
