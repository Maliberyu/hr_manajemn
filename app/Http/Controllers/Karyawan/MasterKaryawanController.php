<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Departemen;
use App\Models\Pendidikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterKaryawanController extends Controller
{
    public function __construct()
    {
    }

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Pegawai::with('departemenRef')
            ->when($request->q, fn($q, $s) => $q->cari($s))
            ->when($request->departemen, fn($q, $d) => $q->departemen($d))
            ->when($request->status, fn($q, $s) => $q->where('stts_aktif', $s))
            ->when($request->jk, fn($q, $j) => $q->where('jk', $j))
            ->orderBy('nama');

        $pegawai    = $query->paginate(20)->withQueryString();
        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');

        return view('karyawan.index', compact('pegawai', 'departemen'));
    }

    // ─── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');
        $pendidikan = Pendidikan::orderBy('indek')->pluck('tingkat', 'tingkat');

        return view('karyawan.create', compact('departemen', 'pendidikan'));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'           => 'required|unique:pegawai,nik|max:20',
            'nama'          => 'required|max:100',
            'jk'            => 'required|in:Pria,Wanita',
            'jbtn'          => 'required|max:50',
            'departemen'    => 'required|exists:departemen,dep_id',
            'pendidikan'    => 'required|exists:pendidikan,tingkat',
            'tgl_lahir'     => 'required|date|before:today',
            'mulai_kerja'   => 'required|date',
            'no_ktp'        => 'nullable|digits:16',
            'npwp'          => 'nullable|max:30',
            'gapok'         => 'required|numeric|min:0',
            'status_kerja'  => 'required|max:50',
            'stts_aktif'    => 'required|in:AKTIF,CUTI,KELUAR,TENAGA LUAR',
            'wajibmasuk'    => 'required|integer|min:0|max:31',
            'photo'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Upload & resize foto profil
        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->simpanFoto($request->file('photo'), $validated['nik']);
        }

        Pegawai::create($validated);

        return redirect()->route('karyawan.index')
            ->with('success', "Pegawai {$validated['nama']} berhasil ditambahkan.");
    }

    // ─── Show ──────────────────────────────────────────────────────────────────

    public function show(Pegawai $karyawan)
    {
        $karyawan->load([
            'departemenRef',
            'pendidikanRef',
            'berkas.jenis',
            'jadwalBulanan' => fn($q) => $q->where('tahun', now()->year)
                                           ->where('bulan', now()->month),
            'pengajuanCuti' => fn($q) => $q->orderByDesc('tanggal')->limit(5),
            'rekapAbsensi'  => fn($q) => $q->orderByDesc('tahun')->orderByDesc('bulan')->limit(3),
        ]);

        return view('karyawan.show', compact('karyawan'));
    }

    // ─── Edit ──────────────────────────────────────────────────────────────────

    public function edit(Pegawai $karyawan)
    {
        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');
        $pendidikan = Pendidikan::orderBy('indek')->pluck('tingkat', 'tingkat');

        return view('karyawan.edit', compact('karyawan', 'departemen', 'pendidikan'));
    }

    // ─── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, Pegawai $karyawan)
    {
        $validated = $request->validate([
            'nik'         => "required|unique:pegawai,nik,{$karyawan->id}|max:20",
            'nama'        => 'required|max:100',
            'jk'          => 'required|in:Pria,Wanita',
            'jbtn'        => 'required|max:50',
            'departemen'  => 'required|exists:departemen,dep_id',
            'pendidikan'  => 'required|exists:pendidikan,tingkat',
            'tgl_lahir'   => 'required|date|before:today',
            'mulai_kerja' => 'required|date',
            'no_ktp'      => 'nullable|digits:16',
            'npwp'        => 'nullable|max:30',
            'gapok'       => 'required|numeric|min:0',
            'status_kerja' => 'required|max:50',
            'stts_aktif'  => 'required|in:AKTIF,CUTI,KELUAR,TENAGA LUAR',
            'wajibmasuk'  => 'required|integer|min:0|max:31',
            'photo'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Hapus foto lama
            if ($karyawan->photo) {
                Storage::disk('public')->delete($karyawan->photo);
            }
            $validated['photo'] = $this->simpanFoto($request->file('photo'), $validated['nik']);
        }

        $karyawan->update($validated);

        return redirect()->route('karyawan.show', $karyawan)
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    // ─── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(Pegawai $karyawan)
    {
        // Soft-delete: ubah status menjadi NON AKTIF, jangan hapus data
        $karyawan->update(['stts_aktif' => 'KELUAR']);

        return redirect()->route('karyawan.index')
            ->with('success', "{$karyawan->nama} dinonaktifkan.");
    }

    // ─── Private Helper ────────────────────────────────────────────────────────

    private function simpanFoto($file, string $nik): string
    {
        $filename = 'pegawai/foto/' . $nik . '_' . time() . '.jpg';

        // Jika GD tidak tersedia, simpan file langsung tanpa resize
        if (!extension_loaded('gd')) {
            Storage::disk('public')->put($filename, file_get_contents($file->getRealPath()));
            return $filename;
        }

        $mime = $file->getMimeType();
        $src  = match ($mime) {
            'image/png'  => \imagecreatefrompng($file->getRealPath()),
            'image/webp' => \imagecreatefromwebp($file->getRealPath()),
            default      => \imagecreatefromjpeg($file->getRealPath()),
        };

        $srcW = \imagesx($src);
        $srcH = \imagesy($src);
        $size = 400;

        // Crop persegi dari tengah lalu resize ke 400×400
        if ($srcW > $srcH) {
            $cropX = (int)(($srcW - $srcH) / 2);
            $cropY = 0;
            $cropS = $srcH;
        } else {
            $cropX = 0;
            $cropY = (int)(($srcH - $srcW) / 2);
            $cropS = $srcW;
        }

        $dst = \imagecreatetruecolor($size, $size);
        \imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $size, $size, $cropS, $cropS);

        ob_start();
        \imagejpeg($dst, null, 85);
        $data = ob_get_clean();

        \imagedestroy($src);
        \imagedestroy($dst);

        Storage::disk('public')->put($filename, $data);

        return $filename;
    }
}
