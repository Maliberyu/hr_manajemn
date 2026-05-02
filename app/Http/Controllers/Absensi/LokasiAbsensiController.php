<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Models\LokasiAbsensi;
use Illuminate\Http\Request;

class LokasiAbsensiController extends Controller
{
    public function index()
    {
        $lokasi = LokasiAbsensi::orderBy('nama')->get();
        return view('absensi.lokasi.index', compact('lokasi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'         => 'required|string|max:100',
            'alamat'       => 'nullable|string|max:255',
            'lat'          => 'required|numeric|between:-90,90',
            'lng'          => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:5000',
        ]);

        LokasiAbsensi::create($request->only('nama', 'alamat', 'lat', 'lng', 'radius_meter') + ['aktif' => true]);

        return back()->with('success', "Lokasi \"{$request->nama}\" berhasil ditambahkan.");
    }

    public function update(Request $request, LokasiAbsensi $lokasi)
    {
        $request->validate([
            'nama'         => 'required|string|max:100',
            'alamat'       => 'nullable|string|max:255',
            'lat'          => 'required|numeric|between:-90,90',
            'lng'          => 'required|numeric|between:-180,180',
            'radius_meter' => 'required|integer|min:10|max:5000',
            'aktif'        => 'boolean',
        ]);

        $lokasi->update($request->only('nama', 'alamat', 'lat', 'lng', 'radius_meter', 'aktif'));

        return back()->with('success', "Lokasi \"{$lokasi->nama}\" berhasil diperbarui.");
    }

    public function destroy(LokasiAbsensi $lokasi)
    {
        $lokasi->delete();
        return back()->with('success', 'Lokasi berhasil dihapus.');
    }

    /** Toggle aktif/nonaktif via PATCH */
    public function toggle(LokasiAbsensi $lokasi)
    {
        $lokasi->update(['aktif' => !$lokasi->aktif]);
        return back()->with('success', $lokasi->aktif ? "Lokasi diaktifkan." : "Lokasi dinonaktifkan.");
    }
}
