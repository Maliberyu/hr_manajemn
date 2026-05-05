<?php

namespace App\Http\Controllers\Lembur;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Lembur;
use App\Models\Pegawai;
use App\Models\TarifLembur;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LemburController extends Controller
{
    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Lembur::with(['pegawai', 'approverAtasan', 'approverHrd'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->bulan,  fn($q, $b) => $q->whereMonth('tanggal', $b))
            ->when($request->tahun,  fn($q, $t) => $q->whereYear('tanggal', $t))
            ->when($request->q,      fn($q, $s) =>
                $q->whereHas('pegawai', fn($p) => $p->where('nama', 'like', "%$s%")));

        // Karyawan & atasan hanya lihat milik sendiri
        if (auth()->user()->hasRole(['karyawan', 'atasan'])) {
            $query->where('pegawai_id', auth()->user()->pegawai?->id);
        }

        $lembur        = $query->orderByDesc('tanggal')->paginate(25)->withQueryString();
        $totalAtasan   = Lembur::menungguAtasan()->count();
        $totalHrd      = Lembur::menungguHrd()->count();

        return view('lembur.index', compact('lembur', 'totalAtasan', 'totalHrd'));
    }

    // ─── Form pengajuan ───────────────────────────────────────────────────────

    public function create()
    {
        if (auth()->user()->hasRole(['karyawan', 'atasan'])) {
            $pegawai = collect([auth()->user()->pegawai])->filter();
        } else {
            $pegawai = Pegawai::aktif()->orderBy('nama')->get(['id', 'nama', 'nik', 'jbtn', 'departemen']);
        }

        // Kirim map dep_id → tarif agar form bisa estimasi nominal
        $tarifMap = TarifLembur::all()->keyBy('dep_id');

        return view('lembur.create', compact('pegawai', 'tarifMap'));
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

        $durasi  = Carbon::parse($validated['jam_mulai'])
                         ->diffInMinutes(Carbon::parse($validated['jam_selesai'])) / 60;

        $peg     = Pegawai::find($validated['pegawai_id']);
        $tarif   = TarifLembur::getForDep($peg?->departemen);
        $nominal = $durasi * ($validated['jenis'] === 'HR'
            ? ($tarif?->tarif_hr ?? 0)
            : ($tarif?->tarif_hb ?? 0));

        Lembur::create([
            ...$validated,
            'durasi_jam' => round($durasi, 2),
            'nominal'    => $nominal,
            'status'     => 'Menunggu Atasan',
        ]);

        return redirect()->route('lembur.index')
            ->with('success', "Pengajuan lembur berhasil disimpan. Estimasi nominal: Rp " . number_format($nominal, 0, ',', '.'));
    }

    // ─── Detail ───────────────────────────────────────────────────────────────

    public function show(Lembur $lembur)
    {
        $lembur->load(['pegawai.departemenRef', 'approverAtasan', 'approverHrd']);
        return view('lembur.show', compact('lembur'));
    }

    // ─── Approve Atasan ───────────────────────────────────────────────────────

    public function approveAtasan(Request $request, Lembur $lembur)
    {
        if (!$lembur->bisaApproveAtasan()) {
            return back()->withErrors(['status' => 'Anda tidak berhak atau status tidak sesuai.']);
        }

        $lembur->update([
            'status'             => 'Menunggu HRD',
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
            'catatan_atasan'     => $request->catatan_atasan,
        ]);

        return back()->with('success', "Lembur {$lembur->pegawai->nama} disetujui atasan. Menunggu persetujuan HRD.");
    }

    // ─── Tolak Atasan ─────────────────────────────────────────────────────────

    public function tolakAtasan(Request $request, Lembur $lembur)
    {
        if ($lembur->status !== 'Menunggu Atasan') {
            return back()->withErrors(['status' => 'Status tidak sesuai.']);
        }

        $request->validate(['catatan_atasan' => 'required|max:255']);

        $lembur->update([
            'status'             => 'Ditolak Atasan',
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
            'catatan_atasan'     => $request->catatan_atasan,
        ]);

        return back()->with('success', "Pengajuan lembur ditolak oleh atasan.");
    }

    // ─── Approve HRD ──────────────────────────────────────────────────────────

    public function approveHrd(Request $request, Lembur $lembur)
    {
        if (!$lembur->bisaApproveHrd()) {
            return back()->withErrors(['status' => 'Anda tidak berhak atau status tidak sesuai.']);
        }

        $lembur->update([
            'status'          => 'Disetujui',
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
            'catatan_hrd'     => $request->catatan_hrd,
        ]);

        return back()->with('success', "Lembur {$lembur->pegawai->nama} disetujui HRD.");
    }

    // ─── Tolak HRD ────────────────────────────────────────────────────────────

    public function tolakHrd(Request $request, Lembur $lembur)
    {
        if ($lembur->status !== 'Menunggu HRD') {
            return back()->withErrors(['status' => 'Status tidak sesuai.']);
        }

        $request->validate(['catatan_hrd' => 'required|max:255']);

        $lembur->update([
            'status'          => 'Ditolak HRD',
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
            'catatan_hrd'     => $request->catatan_hrd,
        ]);

        return back()->with('success', "Pengajuan lembur ditolak oleh HRD.");
    }

    // ─── Rekap bulanan ────────────────────────────────────────────────────────

    public function rekap(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $rekap = Pegawai::aktif()
            ->withSum([
                'lembur as total_jam' => fn($q) =>
                    $q->where('status', 'Disetujui')->bulan($tahun, $bulan)
            ], 'durasi_jam')
            ->withSum([
                'lembur as total_nominal' => fn($q) =>
                    $q->where('status', 'Disetujui')->bulan($tahun, $bulan)
            ], 'nominal')
            ->withCount([
                'lembur as total_pengajuan' => fn($q) =>
                    $q->bulan($tahun, $bulan)
            ])
            ->having('total_jam', '>', 0)
            ->orderByDesc('total_jam')
            ->paginate(30)->withQueryString();

        return view('lembur.rekap', compact('rekap', 'bulan', 'tahun'));
    }

    // ─── Setting tarif per departemen ─────────────────────────────────────────

    public function setting()
    {
        $departemen = Departemen::orderBy('nama')->get();
        $tarifMap   = TarifLembur::all()->keyBy('dep_id');

        return view('lembur.setting', compact('departemen', 'tarifMap'));
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'tarif'           => 'required|array',
            'tarif.*.dep_id'  => 'required|exists:departemen,dep_id',
            'tarif.*.hb'      => 'required|numeric|min:0',
            'tarif.*.hr'      => 'required|numeric|min:0',
        ]);

        foreach ($request->tarif as $row) {
            TarifLembur::updateOrCreate(
                ['dep_id' => $row['dep_id']],
                ['tarif_hb' => $row['hb'], 'tarif_hr' => $row['hr']]
            );
        }

        return back()->with('success', 'Tarif lembur per departemen berhasil diperbarui.');
    }
}
