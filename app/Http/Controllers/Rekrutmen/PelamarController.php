<?php

namespace App\Http\Controllers\Rekrutmen;

use App\Http\Controllers\Controller;
use App\Models\HrPelamar;
use App\Models\Lowongan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PelamarController extends Controller
{
    public function index(Request $request)
    {
        $list = HrPelamar::with('lowongan')
            ->when($request->lowongan_id, fn($q, $id) => $q->where('lowongan_id', $id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $s) => $q->where('nama', 'like', "%{$s}%"))
            ->latest()->paginate(25)->withQueryString();

        $lowonganList = Lowongan::orderByDesc('id')->get(['id','no_lowongan','posisi']);
        $statusList   = HrPelamar::STATUS;
        return view('rekrutmen.pelamar.index', compact('list', 'lowonganList', 'statusList'));
    }

    public function create(Request $request)
    {
        $lowonganList = Lowongan::aktif()->orderBy('posisi')->get(['id','no_lowongan','posisi','departemen']);
        $selected     = $request->lowongan_id;
        return view('rekrutmen.pelamar.create', compact('lowonganList', 'selected'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lowongan_id'          => 'required|exists:hr_lowongan,id',
            'nama'                 => 'required|string|max:150',
            'email'                => 'nullable|email|max:150',
            'no_hp'                => 'nullable|string|max:25',
            'tanggal_lahir'        => 'nullable|date',
            'pendidikan_terakhir'  => 'nullable|string|max:50',
            'pengalaman_tahun'     => 'nullable|integer|min:0|max:50',
            'sumber'               => 'required|in:' . implode(',', array_keys(HrPelamar::SUMBER)),
            'cv'                   => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store(
                'rekrutmen/cv/' . $validated['lowongan_id'], 'public'
            );
        }

        HrPelamar::create([
            ...$validated,
            'cv_path'       => $cvPath,
            'status'        => 'baru',
            'tanggal_apply' => today(),
            'dibuat_oleh'   => auth()->id(),
        ]);

        return redirect()->route('rekrutmen.pelamar.index', ['lowongan_id' => $validated['lowongan_id']])
            ->with('success', 'Pelamar berhasil ditambahkan.');
    }

    public function show(HrPelamar $pelamar)
    {
        $pelamar->load(['lowongan', 'interviews.penilaian.penilai', 'interviews.pewawancara', 'offering']);
        return view('rekrutmen.pelamar.show', compact('pelamar'));
    }

    public function updateStatus(Request $request, HrPelamar $pelamar)
    {
        $request->validate([
            'status'  => 'required|in:' . implode(',', array_keys(HrPelamar::STATUS)),
            'catatan' => 'nullable|string|max:500',
        ]);

        $pelamar->update(['status' => $request->status, 'catatan' => $request->catatan]);

        return back()->with('success', 'Status pelamar diperbarui.');
    }

    public function downloadCv(HrPelamar $pelamar)
    {
        abort_unless($pelamar->cv_path, 404);
        return Storage::disk('public')->download(
            $pelamar->cv_path, "CV_{$pelamar->nama}.pdf"
        );
    }

    public function destroy(HrPelamar $pelamar)
    {
        if ($pelamar->cv_path) Storage::disk('public')->delete($pelamar->cv_path);
        $pelamar->delete();
        return back()->with('success', 'Data pelamar dihapus.');
    }
}
