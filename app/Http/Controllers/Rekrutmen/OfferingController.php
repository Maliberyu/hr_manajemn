<?php

namespace App\Http\Controllers\Rekrutmen;

use App\Http\Controllers\Controller;
use App\Models\HrOffering;
use App\Models\HrPelamar;
use Illuminate\Http\Request;

class OfferingController extends Controller
{
    public function index(Request $request)
    {
        $list = HrOffering::with(['pelamar.lowongan', 'updatedBy'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20)->withQueryString();

        $statusList = HrOffering::STATUS;
        return view('rekrutmen.offering.index', compact('list', 'statusList'));
    }

    public function create(Request $request)
    {
        $pelamar = HrPelamar::with('lowongan')
            ->whereIn('status', ['interview', 'offering'])
            ->whereDoesntHave('offering')
            ->orderBy('nama')->get();
        $selected = $request->pelamar_id;
        return view('rekrutmen.offering.create', compact('pelamar', 'selected'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pelamar_id'       => 'required|exists:hr_pelamar,id',
            'gaji_ditawarkan'  => 'nullable|numeric|min:0',
            'catatan'          => 'nullable|string|max:500',
            'tanggal_offering' => 'required|date',
        ]);

        HrOffering::create([...$validated, 'status' => 'draft', 'updated_by' => auth()->id()]);
        HrPelamar::find($validated['pelamar_id'])->update(['status' => 'offering']);

        return redirect()->route('rekrutmen.offering.index')
            ->with('success', 'Offering berhasil dibuat.');
    }

    public function updateStatus(Request $request, HrOffering $offering)
    {
        $request->validate([
            'status'  => 'required|in:' . implode(',', array_keys(HrOffering::STATUS)),
            'catatan' => 'nullable|string|max:500',
        ]);

        $offering->update([
            'status'     => $request->status,
            'catatan'    => $request->catatan,
            'updated_by' => auth()->id(),
        ]);

        // Sinkron status pelamar
        if ($request->status === 'diterima') {
            $offering->pelamar->update(['status' => 'diterima']);
        } elseif ($request->status === 'ditolak') {
            $offering->pelamar->update(['status' => 'ditolak']);
        }

        return back()->with('success', 'Status offering diperbarui.');
    }
}
