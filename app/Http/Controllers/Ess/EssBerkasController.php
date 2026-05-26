<?php

namespace App\Http\Controllers\Ess;

use App\Http\Controllers\Controller;
use App\Models\BerkasPegawai;
use App\Models\MasterBerkasPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EssBerkasController extends Controller
{
    private function pegawai()
    {
        $pegawai = auth()->user()->pegawai;
        abort_if(!$pegawai, 403, 'Akun belum terhubung ke data pegawai.');
        return $pegawai;
    }

    // ─── List berkas milik sendiri ────────────────────────────────────────────

    public function index()
    {
        $pegawai   = $this->pegawai();
        $berkas    = BerkasPegawai::where('nik', $pegawai->nik)
                        ->with('jenis')
                        ->orderByDesc('tgl_upload')
                        ->get();
        $jenisList = MasterBerkasPegawai::orderBy('nama')->pluck('nama');

        return view('ess.berkas', compact('pegawai', 'berkas', 'jenisList'));
    }

    // ─── Upload berkas ────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $pegawai = $this->pegawai();

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
        $path     = "hr_berkas/{$pegawai->nik}/{$slug}_{$pegawai->nik}_{$jenis->id}.{$ext}";

        // Replace jika sudah ada berkas dengan jenis yang sama
        $existing = BerkasPegawai::where('nik', $pegawai->nik)
                                  ->where('jenis_id', $jenis->id)
                                  ->first();
        if ($existing) {
            Storage::disk('public')->delete($existing->path);
            $existing->delete();
        }

        Storage::disk('public')->putFileAs(
            "hr_berkas/{$pegawai->nik}",
            $file,
            basename($path)
        );

        BerkasPegawai::create([
            'jenis_id'       => $jenis->id,
            'nik'            => $pegawai->nik,
            'nama_file'      => $namaFile,
            'path'           => $path,
            'tgl_upload'     => today(),
            'keterangan'     => $request->keterangan,
            'tgl_kadaluarsa' => $request->tgl_kadaluarsa ?: null,
            'notif_aktif'    => $request->tgl_kadaluarsa ? (bool) $request->notif_aktif : false,
        ]);

        return back()->with('success', "Berkas \"{$jenis->nama}\" berhasil diupload.");
    }

    // ─── Update tanggal kadaluarsa ────────────────────────────────────────────

    public function updateKadaluarsa(Request $request, BerkasPegawai $berkas)
    {
        $pegawai = $this->pegawai();
        abort_if($berkas->nik !== $pegawai->nik, 403);

        $request->validate([
            'tgl_kadaluarsa' => 'nullable|date',
            'notif_aktif'    => 'nullable|boolean',
        ]);

        $tgl = $request->tgl_kadaluarsa ?: null;
        $berkas->update([
            'tgl_kadaluarsa' => $tgl,
            'notif_aktif'    => $tgl ? (bool) $request->notif_aktif : false,
        ]);

        return back()->with('success', 'Tanggal kadaluarsa berhasil disimpan.');
    }

    // ─── Download / preview ───────────────────────────────────────────────────

    public function download(BerkasPegawai $berkas)
    {
        $pegawai = $this->pegawai();
        abort_if($berkas->nik !== $pegawai->nik, 403);
        abort_unless(Storage::disk('public')->exists($berkas->path), 404, 'File tidak ditemukan.');

        return Storage::disk('public')->download($berkas->path, $berkas->nama_file);
    }

    // ─── Hapus berkas ─────────────────────────────────────────────────────────

    public function destroy(BerkasPegawai $berkas)
    {
        $pegawai = $this->pegawai();
        abort_if($berkas->nik !== $pegawai->nik, 403);

        Storage::disk('public')->delete($berkas->path);
        $berkas->delete();

        return back()->with('success', 'Berkas berhasil dihapus.');
    }
}
