<?php

namespace App\Http\Controllers\Rekrutmen;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Lowongan;
use App\Models\RekrutmenRequest;
use Illuminate\Http\Request;

class LowonganController extends Controller
{
    public function index(Request $request)
    {
        $list = Lowongan::with('departemenRef', 'dibuatOleh')
            ->withCount('pelamar')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $s) => $q->where('posisi', 'like', "%{$s}%"))
            ->latest()->paginate(20)->withQueryString();

        $statusList = Lowongan::STATUS;
        return view('rekrutmen.lowongan.index', compact('list', 'statusList'));
    }

    public function create(Request $request)
    {
        $departemen = Departemen::orderBy('nama')->get(['dep_id', 'nama']);
        $requestRef = $request->request_id
            ? RekrutmenRequest::find($request->request_id)
            : null;
        return view('rekrutmen.lowongan.create', compact('departemen', 'requestRef'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id'  => 'nullable|exists:hr_rekrutmen_request,id',
            'posisi'      => 'required|string|max:150',
            'departemen'  => 'nullable|exists:departemen,dep_id',
            'kuota'       => 'required|integer|min:1',
            'tgl_buka'    => 'required|date',
            'tgl_tutup'   => 'required|date|after_or_equal:tgl_buka',
            'deskripsi'   => 'nullable|string|max:3000',
            'syarat'      => 'nullable|string|max:3000',
        ]);

        Lowongan::create([
            ...$validated,
            'no_lowongan' => Lowongan::generateNomor(),
            'status'      => 'buka',
            'dibuat_oleh' => auth()->id(),
        ]);

        return redirect()->route('rekrutmen.lowongan.index')
            ->with('success', 'Lowongan berhasil dibuka.');
    }

    public function show(Lowongan $lowongan)
    {
        $lowongan->load('departemenRef','request','pelamar');
        return view('rekrutmen.lowongan.show', compact('lowongan'));
    }

    public function edit(Lowongan $lowongan)
    {
        $departemen = Departemen::orderBy('nama')->get(['dep_id','nama']);
        return view('rekrutmen.lowongan.edit', compact('lowongan', 'departemen'));
    }

    public function update(Request $request, Lowongan $lowongan)
    {
        $validated = $request->validate([
            'posisi'     => 'required|string|max:150',
            'departemen' => 'nullable|exists:departemen,dep_id',
            'kuota'      => 'required|integer|min:1',
            'tgl_buka'   => 'required|date',
            'tgl_tutup'  => 'required|date|after_or_equal:tgl_buka',
            'status'     => 'required|in:' . implode(',', Lowongan::STATUS),
            'deskripsi'  => 'nullable|string|max:3000',
            'syarat'     => 'nullable|string|max:3000',
        ]);

        $lowongan->update($validated);
        return back()->with('success', 'Lowongan berhasil diperbarui.');
    }

    public function destroy(Lowongan $lowongan)
    {
        abort_if($lowongan->pelamar()->exists(), 422, 'Lowongan sudah memiliki pelamar.');
        $lowongan->update(['status' => 'dibatalkan']);
        return back()->with('success', 'Lowongan dibatalkan.');
    }
}
