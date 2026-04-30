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
    public function __construct()
    {
        $this->middleware('permission:karyawan.berkas');
    }

    // ─── List berkas milik satu pegawai ───────────────────────────────────────

    public function index(Pegawai $karyawan)
    {
        $berkas       = $karyawan->berkas()->with('masterBerkas')->get()
                            ->groupBy('masterBerkas.kategori');
        $masterBerkas = MasterBerkasPegawai::orderBy('no_urut')->get()
                            ->groupBy('kategori');

        return view('karyawan.berkas.index', compact('karyawan', 'berkas', 'masterBerkas'));
    }

    // ─── Upload berkas baru ────────────────────────────────────────────────────

    public function store(Request $request, Pegawai $karyawan)
    {
        $request->validate([
            'kode_berkas' => 'required|exists:master_berkas_pegawai,kode',
            'file'        => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // maks 5 MB
        ]);

        $master    = MasterBerkasPegawai::findOrFail($request->kode_berkas);
        $ext       = $request->file('file')->getClientOriginalExtension();
        $filename  = "pegawai/berkas/{$karyawan->nik}/{$request->kode_berkas}_{$karyawan->nik}.{$ext}";

        // Hapus file lama jika ada (replace)
        $existing = BerkasPegawai::where('nik', $karyawan->nik)
                                  ->where('kode_berkas', $request->kode_berkas)
                                  ->first();
        if ($existing && Storage::disk('public')->exists($existing->berkas)) {
            Storage::disk('public')->delete($existing->berkas);
            $existing->delete();
        }

        Storage::disk('public')->putFileAs(
            "pegawai/berkas/{$karyawan->nik}",
            $request->file('file'),
            "{$request->kode_berkas}_{$karyawan->nik}.{$ext}"
        );

        BerkasPegawai::create([
            'nik'         => $karyawan->nik,
            'kode_berkas' => $request->kode_berkas,
            'tgl_uploud'  => today(),
            'berkas'      => $filename,
        ]);

        return back()->with('success', "Berkas {$master->nama_berkas} berhasil diupload.");
    }

    // ─── Download / preview berkas ────────────────────────────────────────────

    public function download(Pegawai $karyawan, BerkasPegawai $berkas)
    {
        abort_if($berkas->nik !== $karyawan->nik, 403);
        abort_unless(Storage::disk('public')->exists($berkas->berkas), 404, 'File tidak ditemukan.');

        return Storage::disk('public')->download(
            $berkas->berkas,
            $berkas->masterBerkas->nama_berkas . '.' . $berkas->ekstensi
        );
    }

    // ─── Hapus berkas ─────────────────────────────────────────────────────────

    public function destroy(Pegawai $karyawan, BerkasPegawai $berkas)
    {
        abort_if($berkas->nik !== $karyawan->nik, 403);

        if (Storage::disk('public')->exists($berkas->berkas)) {
            Storage::disk('public')->delete($berkas->berkas);
        }

        $berkas->delete();

        return back()->with('success', 'Berkas berhasil dihapus.');
    }
}
