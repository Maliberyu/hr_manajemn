<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\BerkasPegawai;
use App\Models\BerkasSetting;
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

        $jenisList = MasterBerkasPegawai::orderBy('nama')->pluck('nama');
        $setting   = BerkasSetting::get();

        return view('karyawan.berkas.index', compact('karyawan', 'berkas', 'jenisList', 'setting'));
    }

    // ─── Upload berkas baru ────────────────────────────────────────────────────

    public function store(Request $request, Pegawai $karyawan)
    {
        $request->validate([
            'nama_dokumen'   => 'required|string|max:100',
            'file'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'keterangan'     => 'nullable|string|max:255',
            'tgl_kadaluarsa' => 'nullable|date|after_or_equal:today',
            'notif_aktif'    => 'nullable|boolean',
        ]);

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
            'jenis_id'       => $jenis->id,
            'nik'            => $karyawan->nik,
            'nama_file'      => $namaFile,
            'path'           => $path,
            'tgl_upload'     => today(),
            'keterangan'     => $request->keterangan,
            'tgl_kadaluarsa' => $request->tgl_kadaluarsa ?: null,
            'notif_aktif'    => $request->tgl_kadaluarsa ? (bool) $request->notif_aktif : false,
        ]);

        return back()->with('success', "Berkas \"{$jenis->nama}\" berhasil diupload.");
    }

    // ─── Update tanggal kadaluarsa berkas ─────────────────────────────────────

    public function updateKadaluarsa(Request $request, Pegawai $karyawan, BerkasPegawai $berkas)
    {
        abort_if($berkas->nik !== $karyawan->nik, 403);

        $request->validate([
            'tgl_kadaluarsa' => 'nullable|date',
            'notif_aktif'    => 'nullable|boolean',
        ]);

        $tgl = $request->tgl_kadaluarsa ?: null;

        $berkas->update([
            'tgl_kadaluarsa' => $tgl,
            'notif_aktif'    => $tgl ? (bool) $request->notif_aktif : false,
        ]);

        return back()->with('success', 'Pengaturan kadaluarsa berhasil disimpan.');
    }

    // ─── Simpan setting threshold notifikasi ──────────────────────────────────

    public function updateSetting(Request $request)
    {
        $request->validate([
            'hari_notif_1' => 'required|integer|min:1|max:365',
            'hari_notif_2' => 'required|integer|min:1|max:30',
        ]);

        $setting = BerkasSetting::first();
        if ($setting) {
            $setting->update($request->only('hari_notif_1', 'hari_notif_2'));
        } else {
            BerkasSetting::create($request->only('hari_notif_1', 'hari_notif_2'));
        }

        return back()->with('success', 'Setting notifikasi kadaluarsa berhasil disimpan.');
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
