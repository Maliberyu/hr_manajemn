<?php

namespace App\Http\Controllers\Kinerja;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiKinerja;
use App\Models\EvaluasiKinerjaPegawai;
use App\Models\PencapaianKinerja;
use App\Models\PencapaianKinerjaPegawai;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class KinerjaController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kinerja.view')->only(['index', 'show', 'grafik']);
        $this->middleware('permission:kinerja.input')->only(['create', 'storeEvaluasi', 'storePencapaian']);
        $this->middleware('permission:kinerja.master')->only(['masterEvaluasi', 'masterPencapaian']);
    }

    // ─── Dashboard kinerja ────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        // Pegawai yang sudah dievaluasi bulan ini
        $sudahDievaluasi = EvaluasiKinerjaPegawai::periode($tahun, $bulan)
                                                  ->distinct('id')
                                                  ->pluck('id');

        $pegawai = Pegawai::aktif()
            ->with('departemenRef')
            ->when($request->departemen, fn($q, $d) => $q->departemen($d))
            ->withCount([
                'evaluasiKinerja as jumlah_evaluasi' => fn($q) =>
                    $q->where('tahun', $tahun)->where('bulan', $bulan),
            ])
            ->orderBy('nama')
            ->paginate(25)->withQueryString();

        $totalIndikator = EvaluasiKinerja::count();

        return view('kinerja.index', compact(
            'pegawai', 'bulan', 'tahun', 'sudahDievaluasi', 'totalIndikator'
        ));
    }

    // ─── Form input evaluasi kinerja ─────────────────────────────────────────

    public function create(Request $request)
    {
        $karyawan   = Pegawai::aktif()->orderBy('nama')->get(['id', 'nama', 'nik', 'jbtn']);
        $indikator  = EvaluasiKinerja::orderBy('kode_evaluasi')->get();
        $pencapaian = PencapaianKinerja::orderBy('kode_pencapaian')->get();

        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        return view('kinerja.create', compact('karyawan', 'indikator', 'pencapaian', 'bulan', 'tahun'));
    }

    // ─── Simpan evaluasi kinerja ──────────────────────────────────────────────

    public function storeEvaluasi(Request $request)
    {
        $request->validate([
            'pegawai_id'   => 'required|exists:pegawai,id',
            'bulan'        => 'required|integer|between:1,12',
            'tahun'        => 'required|integer|min:2020',
            'evaluasi'     => 'required|array',
            'evaluasi.*'   => 'nullable|max:150',
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);

        foreach ($request->evaluasi as $kodeEvaluasi => $keterangan) {
            EvaluasiKinerjaPegawai::updateOrCreate(
                [
                    'id'            => $pegawai->id,
                    'kode_evaluasi' => $kodeEvaluasi,
                    'tahun'         => $request->tahun,
                    'bulan'         => $request->bulan,
                ],
                ['keterangan' => $keterangan]
            );
        }

        return redirect()->route('kinerja.show', [$pegawai, 'bulan' => $request->bulan, 'tahun' => $request->tahun])
            ->with('success', "Evaluasi kinerja {$pegawai->nama} berhasil disimpan.");
    }

    // ─── Simpan pencapaian kinerja ────────────────────────────────────────────

    public function storePencapaian(Request $request)
    {
        $request->validate([
            'pegawai_id'    => 'required|exists:pegawai,id',
            'bulan'         => 'required|integer|between:1,12',
            'tahun'         => 'required|integer|min:2020',
            'pencapaian'    => 'required|array',
            'pencapaian.*'  => 'nullable|max:150',
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);

        foreach ($request->pencapaian as $kode => $keterangan) {
            PencapaianKinerjaPegawai::updateOrCreate(
                [
                    'id'               => $pegawai->id,
                    'kode_pencapaian'  => $kode,
                    'tahun'            => $request->tahun,
                    'bulan'            => $request->bulan,
                ],
                ['keterangan' => $keterangan]
            );
        }

        return back()->with('success', "Pencapaian kinerja {$pegawai->nama} disimpan.");
    }

    // ─── Detail kinerja satu pegawai ─────────────────────────────────────────

    public function show(Request $request, Pegawai $karyawan)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $evaluasi = EvaluasiKinerjaPegawai::with('indikator')
            ->where('id', $karyawan->id)
            ->periode($tahun, $bulan)
            ->get()
            ->keyBy('kode_evaluasi');

        $pencapaian = PencapaianKinerjaPegawai::with('indikator')
            ->where('id', $karyawan->id)
            ->periode($tahun, $bulan)
            ->get()
            ->keyBy('kode_pencapaian');

        $semuaIndikatorEvaluasi  = EvaluasiKinerja::orderBy('kode_evaluasi')->get();
        $semuaIndikatorPencapaian= PencapaianKinerja::orderBy('kode_pencapaian')->get();

        return view('kinerja.show', compact(
            'karyawan', 'evaluasi', 'pencapaian',
            'semuaIndikatorEvaluasi', 'semuaIndikatorPencapaian',
            'bulan', 'tahun'
        ));
    }

    // ─── Grafik performa (data JSON untuk Chart.js) ───────────────────────────

    public function grafik(Pegawai $karyawan)
    {
        // Ambil 12 bulan terakhir
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $tgl   = now()->subMonths($i);
            $bulan = $tgl->month;
            $tahun = $tgl->year;

            $jumlahEval = EvaluasiKinerjaPegawai::where('id', $karyawan->id)
                ->periode($tahun, $bulan)->count();

            $data[] = [
                'label'   => $tgl->translatedFormat('M Y'),
                'evaluasi'=> $jumlahEval,
            ];
        }

        return response()->json($data);
    }

    // ─── Laporan kinerja semua pegawai ────────────────────────────────────────

    public function laporan(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $rekap = Pegawai::aktif()
            ->with([
                'evaluasiKinerja' => fn($q) => $q->periode($tahun, $bulan)->with('indikator'),
                'pencapaianKinerja' => fn($q) => $q->periode($tahun, $bulan)->with('indikator'),
                'departemenRef',
            ])
            ->when($request->departemen, fn($q, $d) => $q->departemen($d))
            ->orderBy('nama')
            ->get();

        if ($request->format === 'pdf') {
            $pdf = Pdf::loadView('kinerja.pdf.laporan', compact('rekap', 'bulan', 'tahun'))
                      ->setPaper('a4', 'landscape');
            return $pdf->download("Laporan_Kinerja_{$bulan}_{$tahun}.pdf");
        }

        return view('kinerja.laporan', compact('rekap', 'bulan', 'tahun'));
    }

    // ─── CRUD Master Indikator Evaluasi ──────────────────────────────────────

    public function masterEvaluasi()
    {
        $indikator = EvaluasiKinerja::orderBy('kode_evaluasi')->get();
        return view('kinerja.master.evaluasi', compact('indikator'));
    }

    public function storeMasterEvaluasi(Request $request)
    {
        $request->validate([
            'kode_evaluasi' => 'required|unique:evaluasi_kinerja,kode_evaluasi|max:3',
            'nama_evaluasi' => 'required|max:200',
            'indek'         => 'required|integer|min:1',
        ]);

        EvaluasiKinerja::create($request->only('kode_evaluasi', 'nama_evaluasi', 'indek'));

        return back()->with('success', 'Indikator evaluasi ditambahkan.');
    }

    public function destroyMasterEvaluasi(EvaluasiKinerja $evaluasi)
    {
        // Cek apakah sudah digunakan
        if ($evaluasi->hasilPegawai()->exists()) {
            return back()->withErrors(['hapus' => 'Indikator ini sudah digunakan dalam data evaluasi.']);
        }

        $evaluasi->delete();
        return back()->with('success', 'Indikator evaluasi dihapus.');
    }
}
