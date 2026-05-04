<?php

namespace App\Http\Controllers\Kinerja;

use App\Http\Controllers\Controller;
use App\Models\{Pegawai, PenilaianPrestasi, PenilaianPrestasiNilai, KriteriaKinerja};
use Illuminate\Http\Request;

class PenilaianPrestasiController extends Controller
{
    public function index(Request $request)
    {
        $semester = (int)($request->semester ?? (now()->month <= 6 ? 1 : 2));
        $tahun    = (int)($request->tahun ?? now()->year);

        $penilaian = PenilaianPrestasi::with(['pegawai.departemenRef', 'penilai'])
            ->periode($tahun, $semester)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('updated_at')
            ->paginate(25)->withQueryString();

        return view('kinerja.prestasi.index', compact('penilaian', 'semester', 'tahun'));
    }

    public function create(Request $request)
    {
        $semester = (int)($request->semester ?? (now()->month <= 6 ? 1 : 2));
        $tahun    = (int)($request->tahun ?? now()->year);
        $kriteria = KriteriaKinerja::where('aktif', true)->with('subIndikator')->orderBy('urutan')->get();
        $pegawai  = Pegawai::aktif()->orderBy('nama')->get(['id', 'nik', 'nama', 'jbtn', 'departemen']);

        return view('kinerja.prestasi.create', compact('kriteria', 'pegawai', 'semester', 'tahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'semester'   => 'required|in:1,2',
            'tahun'      => 'required|integer|min:2020',
            'nilai'      => 'required|array',
            'nilai.*'    => 'required|integer|between:1,5',
        ]);

        $peg = Pegawai::find($request->pegawai_id);

        $penilaian = PenilaianPrestasi::updateOrCreate(
            ['pegawai_id' => $request->pegawai_id, 'semester' => $request->semester, 'tahun' => $request->tahun],
            [
                'nik'         => $peg->nik,
                'penilai_id'  => auth()->id(),
                'status'      => 'draft',
                'kelebihan'   => $request->kelebihan,
                'kekurangan'  => $request->kekurangan,
                'saran'       => $request->saran,
                'rekomendasi' => $request->rekomendasi,
            ]
        );

        $penilaian->nilaiList()->delete();
        foreach ($request->nilai as $kriteriaId => $val) {
            $penilaian->nilaiList()->create([
                'kriteria_id' => $kriteriaId,
                'nilai'       => $val,
                'catatan'     => $request->catatan[$kriteriaId] ?? null,
            ]);
        }

        $penilaian->load('nilaiList');
        $nilaiAkhir = $penilaian->hitungNilaiAkhir();
        $penilaian->update([
            'nilai_akhir' => $nilaiAkhir,
            'predikat'    => PenilaianPrestasi::predikatDari($nilaiAkhir),
        ]);

        return redirect()->route('kinerja.prestasi.show', $penilaian)
            ->with('success', 'Penilaian berhasil disimpan.');
    }

    public function show(PenilaianPrestasi $penilaian)
    {
        $penilaian->load(['pegawai.departemenRef', 'penilai', 'nilaiList.kriteria.subIndikator']);
        $kriteria = KriteriaKinerja::where('aktif', true)->orderBy('urutan')->get();
        return view('kinerja.prestasi.show', compact('penilaian', 'kriteria'));
    }

    public function edit(PenilaianPrestasi $penilaian)
    {
        if ($penilaian->status === 'final') {
            return back()->withErrors(['status' => 'Penilaian sudah final, tidak bisa diedit.']);
        }
        $penilaian->load(['pegawai', 'nilaiList']);
        $kriteria = KriteriaKinerja::where('aktif', true)->with('subIndikator')->orderBy('urutan')->get();
        return view('kinerja.prestasi.edit', compact('penilaian', 'kriteria'));
    }

    public function update(Request $request, PenilaianPrestasi $penilaian)
    {
        if ($penilaian->status === 'final') {
            return back()->withErrors(['status' => 'Penilaian sudah final.']);
        }
        $request->validate(['nilai' => 'required|array', 'nilai.*' => 'required|integer|between:1,5']);

        $penilaian->update([
            'kelebihan'   => $request->kelebihan,
            'kekurangan'  => $request->kekurangan,
            'saran'       => $request->saran,
            'rekomendasi' => $request->rekomendasi,
        ]);

        foreach ($request->nilai as $kriteriaId => $val) {
            $penilaian->nilaiList()->updateOrCreate(
                ['kriteria_id' => $kriteriaId],
                ['nilai' => $val, 'catatan' => $request->catatan[$kriteriaId] ?? null]
            );
        }

        $penilaian->load('nilaiList');
        $nilaiAkhir = $penilaian->hitungNilaiAkhir();
        $penilaian->update([
            'nilai_akhir' => $nilaiAkhir,
            'predikat'    => PenilaianPrestasi::predikatDari($nilaiAkhir),
        ]);

        return redirect()->route('kinerja.prestasi.show', $penilaian)
            ->with('success', 'Penilaian diperbarui.');
    }

    public function finalize(PenilaianPrestasi $penilaian)
    {
        $penilaian->update(['status' => 'final', 'submitted_at' => now()]);
        return back()->with('success', 'Penilaian difinalisasi.');
    }

    public function pdf(PenilaianPrestasi $penilaian)
    {
        $penilaian->load(['pegawai.departemenRef', 'penilai', 'nilaiList.kriteria']);
        $kriteria = KriteriaKinerja::where('aktif', true)->orderBy('urutan')->get();

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kinerja.prestasi.pdf', compact('penilaian', 'kriteria'))
                ->setPaper('a4', 'portrait');
            $nama = str_replace(' ', '_', $penilaian->pegawai?->nama ?? 'penilaian');
            return $pdf->download("Penilaian_{$nama}_S{$penilaian->semester}_{$penilaian->tahun}.pdf");
        }
        return view('kinerja.prestasi.pdf', compact('penilaian', 'kriteria'));
    }
}
