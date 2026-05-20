<?php

namespace App\Http\Controllers\Rekrutmen;

use App\Http\Controllers\Controller;
use App\Models\HrInterview;
use App\Models\HrPelamar;
use App\Models\HrPenilaianInterview;
use App\Models\User;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function index(Request $request)
    {
        $list = HrInterview::with(['pelamar.lowongan', 'pewawancara'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->tanggal, fn($q, $t) => $q->whereDate('jadwal', $t))
            ->orderBy('jadwal')->paginate(20)->withQueryString();

        return view('rekrutmen.interview.index', compact('list'));
    }

    public function create(Request $request)
    {
        $pelamar      = HrPelamar::with('lowongan')
            ->whereIn('status', ['seleksi_cv','interview'])
            ->orderBy('nama')->get();
        $pewawancaraList = User::whereIn('role', ['hrd','admin','atasan'])
            ->where('status','aktif')->orderBy('nama')->get(['id','nama','jabatan']);
        $selectedPelamar = $request->pelamar_id;
        return view('rekrutmen.interview.create', compact('pelamar', 'pewawancaraList', 'selectedPelamar'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelamar_id'      => 'required|exists:hr_pelamar,id',
            'tahap'           => 'required|integer|min:1|max:10',
            'label_tahap'     => 'required|string|max:100',
            'jadwal'          => 'required|date',
            'metode'          => 'required|in:online,offline',
            'lokasi_atau_link'=> 'nullable|string|max:255',
            'pewawancara_id'  => 'nullable|exists:users_hr,id',
        ]);

        HrInterview::create($validated);

        // Update status pelamar ke interview
        HrPelamar::find($validated['pelamar_id'])->update(['status' => 'interview']);

        return redirect()->route('rekrutmen.interview.index')
            ->with('success', 'Jadwal interview berhasil dibuat.');
    }

    public function show(HrInterview $interview)
    {
        $interview->load(['pelamar.lowongan', 'pewawancara', 'penilaian.penilai']);
        return view('rekrutmen.interview.show', compact('interview'));
    }

    public function selesai(Request $request, HrInterview $interview)
    {
        $interview->update(['status' => 'selesai', 'catatan' => $request->catatan]);
        return back()->with('success', 'Interview ditandai selesai.');
    }

    public function batal(HrInterview $interview)
    {
        $interview->update(['status' => 'batal']);
        return back()->with('success', 'Jadwal interview dibatalkan.');
    }

    public function storePenilaian(Request $request, HrInterview $interview)
    {
        $request->validate([
            'nilai'        => 'required|numeric|min:0|max:100',
            'rekomendasi'  => 'required|in:' . implode(',', array_keys(HrPenilaianInterview::REKOMENDASI)),
            'catatan'      => 'nullable|string|max:500',
        ]);

        HrPenilaianInterview::updateOrCreate(
            ['interview_id' => $interview->id, 'penilai_id' => auth()->id()],
            ['nilai' => $request->nilai, 'rekomendasi' => $request->rekomendasi, 'catatan' => $request->catatan]
        );

        return back()->with('success', 'Penilaian interview disimpan.');
    }
}
