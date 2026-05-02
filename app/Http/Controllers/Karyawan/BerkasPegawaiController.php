<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\BerkasPegawai;
use App\Models\MasterBerkasPegawai;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BerkasPegawaiController extends Controller
{
    // ─── List berkas milik satu pegawai ───────────────────────────────────────

    public function index(Pegawai $karyawan)
    {
        $berkas  = BerkasPegawai::where('nik', $karyawan->nik)
                      ->with('jenis')
                      ->orderByDesc('tgl_upload')
                      ->get();

        // Nama-nama jenis yang sudah ada untuk autocomplete
        $jenisList = MasterBerkasPegawai::orderBy('nama')->pluck('nama');

        return view('karyawan.berkas.index', compact('karyawan', 'berkas', 'jenisList'));
    }

    // ─── Upload berkas baru ────────────────────────────────────────────────────

    public function store(Request $request, Pegawai $karyawan)
    {
        $request->validate([
            'nama_dokumen' => 'required|string|max:100',
            'file'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'keterangan'   => 'nullable|string|max:255',
        ]);

        // Auto-create atau reuse jenis berkas berdasarkan nama
        $jenis = MasterBerkasPegawai::firstOrCreate(
            ['nama' => trim($request->nama_dokumen)],
            [
                'kategori' => 'Umum',
                'urutan'   => (MasterBerkasPegawai::max('urutan') ?? 0) + 1,
            ]
        );

        $file     = $request->file('file');
        $ext      = strtolower($file->getClientOriginalExtension());
        $namaFile = $file->getClientOriginalName();
        $slug     = preg_replace('/[^A-Za-z0-9]+/', '_', $request->nama_dokumen);
        $path     = "hr_berkas/{$karyawan->nik}/{$slug}_{$karyawan->nik}_{$jenis->id}.{$ext}";

        // Hapus file lama jika dokumen yang sama sudah ada
        $existing = BerkasPegawai::where('nik', $karyawan->nik)
                                  ->where('jenis_id', $jenis->id)
                                  ->first();
        if ($existing) {
            Storage::disk('public')->delete($existing->path);
            $existing->delete();
        }

        Storage::disk('public')->putFileAs(
            "hr_berkas/{$karyawan->nik}",
            $file,
            basename($path)
        );

        BerkasPegawai::create([
            'jenis_id'   => $jenis->id,
            'nik'        => $karyawan->nik,
            'nama_file'  => $namaFile,
            'path'       => $path,
            'tgl_upload' => today(),
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', "Berkas \"{$jenis->nama}\" berhasil diupload.");
    }

    // ─── Download / preview berkas ────────────────────────────────────────────

    public function download(Pegawai $karyawan, BerkasPegawai $berkas)
    {
        abort_if($berkas->nik !== $karyawan->nik, 403);
        abort_unless(Storage::disk('public')->exists($berkas->path), 404, 'File tidak ditemukan.');

        return Storage::disk('public')->download($berkas->path, $berkas->nama_file);
    }

    // ─── Hapus berkas ─────────────────────────────────────────────────────────

    public function destroy(Pegawai $karyawan, BerkasPegawai $berkas)
    {
        abort_if($berkas->nik !== $karyawan->nik, 403);
        Storage::disk('public')->delete($berkas->path);
        $berkas->delete();

        return back()->with('success', 'Berkas berhasil dihapus.');
    }
}
