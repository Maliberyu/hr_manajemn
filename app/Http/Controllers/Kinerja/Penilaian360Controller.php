<?php

namespace App\Http\Controllers\Kinerja;

use App\Http\Controllers\Controller;
use App\Models\{Pegawai, Penilaian360, Rater360, Nilai360, Komentar360, Dimensi360, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Penilaian360Controller extends Controller
{
    public function index(Request $request)
    {
        $semester = (int)($request->semester ?? (now()->month <= 6 ? 1 : 2));
        $tahun    = (int)($request->tahun ?? now()->year);

        $sesiList = Penilaian360::with(['pegawai.departemenRef', 'raters'])
            ->periode($tahun, $semester)
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        return view('kinerja.360.index', compact('sesiList', 'semester', 'tahun'));
    }

    public function create()
    {
        $pegawai  = Pegawai::aktif()->orderBy('nama')->get(['id', 'nik', 'nama', 'jbtn', 'departemen']);
        $userList = User::where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'jabatan', 'role']);
        $semester = now()->month <= 6 ? 1 : 2;
        $tahun    = now()->year;
        return view('kinerja.360.create', compact('pegawai', 'userList', 'semester', 'tahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'semester'   => 'required|in:1,2',
            'tahun'      => 'required|integer|min:2020',
            'deadline'   => 'nullable|date|after:today',
            'raters'     => 'required|array|min:1',
            'raters.*.user_id'   => 'required|exists:users_hr,id',
            'raters.*.hubungan'  => 'required|in:atasan,rekan,bawahan,self',
        ]);

        $peg = Pegawai::find($request->pegawai_id);

        $sesi = Penilaian360::create([
            'nik'        => $peg->nik,
            'pegawai_id' => $request->pegawai_id,
            'semester'   => $request->semester,
            'tahun'      => $request->tahun,
            'status'     => 'aktif',
            'deadline'   => $request->deadline,
            'dibuat_oleh'=> auth()->id(),
        ]);

        foreach ($request->raters as $r) {
            $user = User::find($r['user_id']);
            $sesi->raters()->create([
                'user_id'    => $r['user_id'],
                'hubungan'   => $r['hubungan'],
                'nama_rater' => $user?->nama,
                'is_anonim'  => $r['hubungan'] !== 'self',
            ]);
        }

        return redirect()->route('kinerja.360.show', $sesi)
            ->with('success', 'Sesi 360° berhasil dibuat. Rater dapat mulai mengisi.');
    }

    public function show(Penilaian360 $sesi)
    {
        $sesi->load(['pegawai.departemenRef', 'raters.user', 'raters.nilai', 'komentar']);
        return view('kinerja.360.show', compact('sesi'));
    }

    /** Form rater mengisi penilaian */
    public function form(Request $request, Penilaian360 $sesi)
    {
        $raterId = $request->rater;
        $rater   = Rater360::where('id', $raterId)->where('penilaian_id', $sesi->id)->firstOrFail();

        if ($rater->sudahSubmit()) {
            return back()->with('info', 'Anda sudah mengisi penilaian ini.');
        }

        $dimensi = Dimensi360::where('aktif', true)
            ->with(['aspek' => fn($q) => $q->where('aktif', true)->orderBy('urutan')])
            ->orderBy('urutan')->get()
            ->filter(fn($d) => in_array($rater->hubungan, $d->untuk_rater ?? []));

        $nilaiExisting = $rater->nilai->keyBy('aspek_id');

        return view('kinerja.360.form', compact('sesi', 'rater', 'dimensi', 'nilaiExisting'));
    }

    /** Submit form rater */
    public function submitForm(Request $request, Penilaian360 $sesi)
    {
        $rater = Rater360::where('id', $request->rater_id)->where('penilaian_id', $sesi->id)->firstOrFail();

        if ($rater->sudahSubmit()) {
            return back()->withErrors(['status' => 'Sudah disubmit.']);
        }

        $request->validate([
            'nilai'   => 'required|array',
            'nilai.*' => 'required|integer|between:1,5',
        ]);

        foreach ($request->nilai as $aspekId => $val) {
            Nilai360::updateOrCreate(
                ['rater_id' => $rater->id, 'aspek_id' => $aspekId],
                ['nilai' => $val]
            );
        }

        Komentar360::updateOrCreate(
            ['penilaian_id' => $sesi->id, 'rater_id' => $rater->id],
            [
                'kekuatan'          => $request->kekuatan,
                'area_pengembangan' => $request->area_pengembangan,
                'saran'             => $request->saran,
            ]
        );

        $rater->update(['submitted_at' => now()]);

        // Jika semua rater sudah submit, hitung nilai akhir
        if ($sesi->raters()->whereNull('submitted_at')->doesntExist()) {
            $nilaiAkhir = $sesi->hitungNilaiAkhir();
            $sesi->update(['status' => 'selesai', 'nilai_akhir' => $nilaiAkhir]);
        }

        return redirect()->route('kinerja.360.show', $sesi)
            ->with('success', 'Penilaian berhasil disubmit. Terima kasih.');
    }

    /** Rekap hasil 360° */
    public function rekap(Penilaian360 $sesi)
    {
        $sesi->load(['pegawai.departemenRef', 'raters.nilai.aspek.dimensi', 'komentar.rater']);
        $dimensi    = Dimensi360::where('aktif', true)->with('aspek')->orderBy('urutan')->get();
        $bobotRater = DB::table('hr_kinerja_360_bobot_rater')->pluck('bobot', 'hubungan');

        // Hitung rata-rata per aspek per hubungan
        $rekapNilai = [];
        foreach ($sesi->raters as $rater) {
            if (!$rater->sudahSubmit()) continue;
            foreach ($rater->nilai as $n) {
                $rekapNilai[$rater->hubungan][$n->aspek_id][] = $n->nilai;
            }
        }

        return view('kinerja.360.rekap', compact('sesi', 'dimensi', 'bobotRater', 'rekapNilai'));
    }

    public function tutup(Penilaian360 $sesi)
    {
        $nilaiAkhir = $sesi->hitungNilaiAkhir();
        $sesi->update(['status' => 'selesai', 'nilai_akhir' => $nilaiAkhir]);
        return back()->with('success', 'Sesi 360° ditutup dan nilai akhir dihitung.');
    }
}
