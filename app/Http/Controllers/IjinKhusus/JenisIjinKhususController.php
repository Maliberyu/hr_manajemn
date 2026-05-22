<?php

namespace App\Http\Controllers\IjinKhusus;

use App\Http\Controllers\Controller;
use App\Models\JenisIjinKhusus;
use Illuminate\Http\Request;

class JenisIjinKhususController extends Controller
{
    public function index()
    {
        $list = JenisIjinKhusus::orderBy('urutan')->get();
        return view('ijin-khusus.master.index', compact('list'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'           => 'required|string|max:30|unique:hr_jenis_ijin_khusus,kode',
            'nama'           => 'required|string|max:100',
            'max_hari'       => 'nullable|integer|min:1|max:365',
            'wajib_lampiran' => 'boolean',
            'butuh_waktu'    => 'boolean',
            'keterangan'     => 'nullable|string|max:300',
            'urutan'         => 'nullable|integer|min:0',
        ]);

        JenisIjinKhusus::create([
            ...$validated,
            'aktif'      => true,
            'dibuat_oleh'=> auth()->id(),
        ]);

        return back()->with('success', "Jenis ijin \"{$validated['nama']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, JenisIjinKhusus $jenisIjinKhusus)
    {
        $validated = $request->validate([
            'nama'           => 'required|string|max:100',
            'max_hari'       => 'nullable|integer|min:1|max:365',
            'wajib_lampiran' => 'boolean',
            'butuh_waktu'    => 'boolean',
            'keterangan'     => 'nullable|string|max:300',
            'urutan'         => 'nullable|integer|min:0',
        ]);

        $jenisIjinKhusus->update($validated);
        return back()->with('success', 'Jenis ijin berhasil diperbarui.');
    }

    public function toggle(JenisIjinKhusus $jenisIjinKhusus)
    {
        $jenisIjinKhusus->update(['aktif' => !$jenisIjinKhusus->aktif]);
        $status = $jenisIjinKhusus->aktif ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Jenis ijin \"{$jenisIjinKhusus->nama}\" berhasil {$status}.");
    }
}
