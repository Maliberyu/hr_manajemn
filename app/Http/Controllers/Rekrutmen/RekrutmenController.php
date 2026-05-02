<?php

namespace App\Http\Controllers\Rekrutmen;

use App\Http\Controllers\Controller;
use App\Models\Rekrutmen;
use App\Models\Pelamar;
use App\Models\Departemen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RekrutmenController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {
        $lowongan = Rekrutmen::with(['departemen', 'dibuatOleh'])
            ->withCount('pelamar')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $s) => $q->where('posisi', 'like', "%{$s}%"))
            ->orderByDesc('tanggal_buka')
            ->paginate(20)->withQueryString();

        return view('rekrutmen.index', compact('lowongan'));
    }

    public function create()
    {
        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');
        return view('rekrutmen.create', compact('departemen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'posisi'            => 'required|max:100',
            'departemen_id'     => 'required|exists:departemen,dep_id',
            'jumlah_dibutuhkan' => 'required|integer|min:1',
            'tanggal_buka'      => 'required|date',
            'tanggal_tutup'     => 'required|date|after:tanggal_buka',
            'deskripsi'         => 'nullable|max:2000',
            'syarat'            => 'nullable|max:2000',
        ]);

        Rekrutmen::create([...$validated, 'status' => 'buka', 'dibuat_oleh' => auth()->id()]);

        return redirect()->route('rekrutmen.index')
            ->with('success', "Lowongan {$validated['posisi']} berhasil dibuka.");
    }

    public function show(Request $request, Rekrutmen $rekrutmen)
    {
        $rekrutmen->load('departemen');

        $pelamar = Pelamar::where('rekrutmen_id', $rekrutmen->id)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('tanggal_apply')
            ->paginate(20)->withQueryString();

        $rekapStatus = Pelamar::where('rekrutmen_id', $rekrutmen->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('rekrutmen.show', compact('rekrutmen', 'pelamar', 'rekapStatus'));
    }

    public function edit(Rekrutmen $rekrutmen)
    {
        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');
        return view('rekrutmen.edit', compact('rekrutmen', 'departemen'));
    }

    public function update(Request $request, Rekrutmen $rekrutmen)
    {
        $validated = $request->validate([
            'posisi'            => 'required|max:100',
            'departemen_id'     => 'required|exists:departemen,dep_id',
            'jumlah_dibutuhkan' => 'required|integer|min:1',
            'tanggal_buka'      => 'required|date',
            'tanggal_tutup'     => 'required|date|after:tanggal_buka',
            'status'            => 'required|in:' . implode(',', Rekrutmen::STATUS),
            'deskripsi'         => 'nullable|max:2000',
            'syarat'            => 'nullable|max:2000',
        ]);

        $rekrutmen->update($validated);
        return redirect()->route('rekrutmen.show', $rekrutmen)->with('success', 'Data lowongan diperbarui.');
    }

    public function storePelamar(Request $request, Rekrutmen $rekrutmen)
    {
        $validated = $request->validate([
            'nama'                => 'required|max:100',
            'email'               => 'nullable|email|max:100',
            'telepon'             => 'required|max:20',
            'pendidikan_terakhir' => 'required|max:50',
            'pengalaman_tahun'    => 'nullable|integer|min:0',
            'cv_file'             => 'required|file|mimes:pdf|max:5120',
        ]);

        $cvPath = $request->file('cv_file')->store("rekrutmen/{$rekrutmen->id}", 'public');

        Pelamar::create([
            ...$validated,
            'rekrutmen_id'  => $rekrutmen->id,
            'cv_file'       => $cvPath,
            'status'        => 'applied',
            'tanggal_apply' => today(),
        ]);

        return back()->with('success', "Lamaran {$validated['nama']} berhasil ditambahkan.");
    }

    public function updateStatusPelamar(Request $request, Rekrutmen $rekrutmen, Pelamar $pelamar)
    {
        $request->validate([
            'status'            => 'required|in:' . implode(',', Pelamar::STATUS),
            'catatan'           => 'nullable|max:500',
            'tanggal_interview' => 'nullable|date',
            'nilai_test'        => 'nullable|numeric|min:0|max:100',
        ]);

        $pelamar->update($request->only('status', 'catatan', 'tanggal_interview', 'nilai_test'));
        return back()->with('success', "Status {$pelamar->nama} diubah ke: {$request->status}");
    }

    public function downloadCv(Rekrutmen $rekrutmen, Pelamar $pelamar)
    {
        abort_unless(Storage::disk('public')->exists($pelamar->cv_file), 404);
        return Storage::disk('public')->download($pelamar->cv_file, "CV_{$pelamar->nama}.pdf");
    }

    public function destroyPelamar(Rekrutmen $rekrutmen, Pelamar $pelamar)
    {
        if ($pelamar->cv_file) Storage::disk('public')->delete($pelamar->cv_file);
        $pelamar->delete();
        return back()->with('success', 'Data pelamar dihapus.');
    }

    public function destroy(Rekrutmen $rekrutmen)
    {
        $rekrutmen->update(['status' => 'dibatalkan']);
        return redirect()->route('rekrutmen.index')->with('success', 'Lowongan ditutup.');
    }
}
