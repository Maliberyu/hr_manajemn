<?php

namespace App\Http\Controllers\Kinerja;

use App\Http\Controllers\Controller;
use App\Models\{KriteriaKinerja, SubIndikatorKinerja, Dimensi360, Aspek360, PenilaianPrestasi, Penilaian360};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KinerjaController extends Controller
{
    public function index(Request $request)
    {
        $semester = (int) ($request->semester ?? (now()->month <= 6 ? 1 : 2));
        $tahun    = (int) ($request->tahun ?? now()->year);

        $totalPrestasi = PenilaianPrestasi::periode($tahun, $semester)->count();
        $totalFinalPre = PenilaianPrestasi::periode($tahun, $semester)->where('status', 'final')->count();
        $total360      = Penilaian360::periode($tahun, $semester)->count();
        $total360Aktif = Penilaian360::periode($tahun, $semester)->where('status', 'aktif')->count();

        return view('kinerja.index', compact(
            'semester', 'tahun', 'totalPrestasi', 'totalFinalPre', 'total360', 'total360Aktif'
        ));
    }

    public function master()
    {
        $kriteria   = KriteriaKinerja::with('subIndikator')->orderBy('urutan')->get();
        $dimensi    = Dimensi360::with('aspek')->orderBy('urutan')->get();
        $bobotRater = DB::table('hr_kinerja_360_bobot_rater')->get()->keyBy('hubungan');
        return view('kinerja.master', compact('kriteria', 'dimensi', 'bobotRater'));
    }

    public function storeKriteria(Request $request)
    {
        $request->validate(['nama' => 'required|max:100', 'bobot' => 'required|numeric|min:0|max:100']);
        KriteriaKinerja::create(['nama' => $request->nama, 'bobot' => $request->bobot, 'urutan' => $request->urutan ?? 50, 'aktif' => true]);
        return back()->with('success_kriteria', 'Kriteria ditambahkan.');
    }

    public function updateBobot(Request $request)
    {
        foreach ($request->bobot ?? [] as $id => $val) {
            KriteriaKinerja::where('id', $id)->update(['bobot' => (float)$val]);
        }
        return back()->with('success_kriteria', 'Bobot disimpan.');
    }

    public function toggleKriteria(KriteriaKinerja $kriteria)
    {
        $kriteria->update(['aktif' => !$kriteria->aktif]);
        return back();
    }

    public function destroyKriteria(KriteriaKinerja $kriteria)
    {
        $kriteria->delete();
        return back()->with('success_kriteria', 'Kriteria dihapus.');
    }

    public function storeSubIndikator(Request $request)
    {
        $request->validate(['kriteria_id' => 'required|exists:hr_kinerja_kriteria,id', 'nama' => 'required|max:255']);
        SubIndikatorKinerja::create(['kriteria_id' => $request->kriteria_id, 'nama' => $request->nama, 'urutan' => $request->urutan ?? 10, 'aktif' => true]);
        return back()->with('success_kriteria', 'Sub-indikator ditambahkan.');
    }

    public function destroySubIndikator(SubIndikatorKinerja $sub)
    {
        $sub->delete();
        return back()->with('success_kriteria', 'Sub-indikator dihapus.');
    }

    public function storeAspek(Request $request)
    {
        $request->validate(['dimensi_id' => 'required|exists:hr_kinerja_360_dimensi,id', 'nama' => 'required|max:255']);
        Aspek360::create(['dimensi_id' => $request->dimensi_id, 'nama' => $request->nama, 'urutan' => $request->urutan ?? 10, 'aktif' => true]);
        return back()->with('success_360', 'Aspek ditambahkan.');
    }

    public function destroyAspek(Aspek360 $aspek)
    {
        $aspek->delete();
        return back()->with('success_360', 'Aspek dihapus.');
    }

    public function updateBobotRater(Request $request)
    {
        foreach ($request->bobot ?? [] as $hubungan => $val) {
            DB::table('hr_kinerja_360_bobot_rater')->where('hubungan', $hubungan)->update(['bobot' => (float)$val]);
        }
        return back()->with('success_360', 'Bobot rater disimpan.');
    }
}
