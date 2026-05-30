<?php

namespace App\Http\Controllers\Kontrak;

use App\Http\Controllers\Controller;
use App\Models\HrNotification;
use App\Models\JenisKontrak;
use App\Models\KontrakKerja;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KontrakKerjaController extends Controller
{
    public function index(Request $request)
    {
        $query = KontrakKerja::with(['pegawai', 'jenis'])
            ->orderByDesc('tgl_mulai');

        if ($request->nik) {
            $query->where('nik', $request->nik);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->jenis_id) {
            $query->where('jenis_kontrak_id', $request->jenis_id);
        }

        $kontraks      = $query->paginate(20)->withQueryString();
        $jenisList     = JenisKontrak::orderBy('nama')->get();
        $akanBerakhir  = KontrakKerja::with(['pegawai', 'jenis'])->akanBerakhir(30)
                            ->orderBy('tgl_selesai')->get();

        return view('kontrak.index', compact('kontraks', 'jenisList', 'akanBerakhir'));
    }

    public function create()
    {
        $jenisList  = JenisKontrak::orderBy('nama')->get();
        $pegawaiList = Pegawai::aktif()->orderBy('nama')->get(['nik', 'nama', 'jbtn', 'departemen']);
        return view('kontrak.create', compact('jenisList', 'pegawaiList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nik'               => 'required|exists:pegawai,nik',
            'jenis_kontrak_id'  => 'required|exists:hr_jenis_kontrak,id',
            'no_kontrak'        => 'nullable|string|max:50|unique:hr_kontrak_kerja,no_kontrak',
            'tgl_mulai'         => 'required|date',
            'tgl_selesai'       => 'nullable|date|after:tgl_mulai',
            'tgl_tanda_tangan'  => 'nullable|date',
            'file_kontrak'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'catatan'           => 'nullable|string|max:500',
        ]);

        // Tandai kontrak aktif sebelumnya sebagai diperbarui
        KontrakKerja::where('nik', $data['nik'])->where('status', 'aktif')
            ->update(['status' => 'diperbarui', 'diperbarui_oleh' => auth()->id()]);

        if ($request->hasFile('file_kontrak')) {
            $data['file_kontrak'] = $request->file('file_kontrak')
                ->store("hr_kontrak/{$data['nik']}", 'public');
        }

        $jenis = JenisKontrak::find($data['jenis_kontrak_id']);
        // PKWTT tidak punya tgl_selesai
        if ($jenis->is_tetap) {
            $data['tgl_selesai'] = null;
        }

        $kontrak = KontrakKerja::create($data + ['dibuat_oleh' => auth()->id(), 'status' => 'aktif']);

        // Update mulai_kontrak di tabel pegawai
        Pegawai::where('nik', $data['nik'])->update(['mulai_kontrak' => $data['tgl_mulai']]);

        // Notifikasi ke karyawan
        HrNotification::kirimKePegawai(
            $data['nik'], 'kontrak_baru',
            'Kontrak Kerja Baru',
            "Kontrak {$jenis->nama} Anda telah dibuat, berlaku mulai " . \Carbon\Carbon::parse($data['tgl_mulai'])->isoFormat('D MMMM Y') . '.',
            route('kontrak.show', $kontrak)
        );

        return redirect()->route('kontrak.show', $kontrak)
            ->with('success', "Kontrak {$jenis->nama} untuk {$kontrak->pegawai->nama} berhasil dibuat.");
    }

    public function show(KontrakKerja $kontrak)
    {
        $kontrak->load(['pegawai', 'jenis', 'pembuatUser']);
        $riwayat = KontrakKerja::with('jenis')
            ->where('nik', $kontrak->nik)
            ->orderByDesc('tgl_mulai')
            ->get();
        return view('kontrak.show', compact('kontrak', 'riwayat'));
    }

    public function edit(KontrakKerja $kontrak)
    {
        $jenisList = JenisKontrak::orderBy('nama')->get();
        return view('kontrak.edit', compact('kontrak', 'jenisList'));
    }

    public function update(Request $request, KontrakKerja $kontrak)
    {
        $data = $request->validate([
            'jenis_kontrak_id'  => 'required|exists:hr_jenis_kontrak,id',
            'no_kontrak'        => 'nullable|string|max:50|unique:hr_kontrak_kerja,no_kontrak,' . $kontrak->id,
            'tgl_mulai'         => 'required|date',
            'tgl_selesai'       => 'nullable|date|after:tgl_mulai',
            'tgl_tanda_tangan'  => 'nullable|date',
            'file_kontrak'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'catatan'           => 'nullable|string|max:500',
            'status'            => 'required|in:aktif,berakhir,diperbarui,dibatalkan',
        ]);

        if ($request->hasFile('file_kontrak')) {
            if ($kontrak->file_kontrak) {
                Storage::disk('public')->delete($kontrak->file_kontrak);
            }
            $data['file_kontrak'] = $request->file('file_kontrak')
                ->store("hr_kontrak/{$kontrak->nik}", 'public');
        }

        $jenis = JenisKontrak::find($data['jenis_kontrak_id']);
        if ($jenis->is_tetap) $data['tgl_selesai'] = null;

        $kontrak->update($data + ['diperbarui_oleh' => auth()->id()]);

        return redirect()->route('kontrak.show', $kontrak)
            ->with('success', 'Kontrak berhasil diperbarui.');
    }

    public function destroy(KontrakKerja $kontrak)
    {
        if ($kontrak->file_kontrak) {
            Storage::disk('public')->delete($kontrak->file_kontrak);
        }
        $kontrak->delete();
        return redirect()->route('kontrak.index')
            ->with('success', 'Kontrak berhasil dihapus.');
    }

    // ─── Master Jenis Kontrak ─────────────────────────────────────────────────

    public function masterJenis()
    {
        $jenisList = JenisKontrak::withCount('kontraks')->orderBy('nama')->get();
        return view('kontrak.master-jenis', compact('jenisList'));
    }

    public function storeJenis(Request $request)
    {
        $data = $request->validate([
            'nama'                  => 'required|string|max:50|unique:hr_jenis_kontrak,nama',
            'durasi_default_bulan'  => 'nullable|integer|min:1|max:120',
            'is_tetap'              => 'boolean',
            'keterangan'            => 'nullable|string|max:200',
        ]);
        JenisKontrak::create($data);
        return back()->with('success', 'Jenis kontrak berhasil ditambahkan.');
    }

    public function updateJenis(Request $request, JenisKontrak $jenis)
    {
        $data = $request->validate([
            'nama'                  => 'required|string|max:50|unique:hr_jenis_kontrak,nama,' . $jenis->id,
            'durasi_default_bulan'  => 'nullable|integer|min:1|max:120',
            'is_tetap'              => 'boolean',
            'keterangan'            => 'nullable|string|max:200',
        ]);
        $jenis->update($data);
        return back()->with('success', 'Jenis kontrak berhasil diperbarui.');
    }

    public function destroyJenis(JenisKontrak $jenis)
    {
        abort_if($jenis->kontraks()->exists(), 422, 'Jenis kontrak sudah digunakan, tidak bisa dihapus.');
        $jenis->delete();
        return back()->with('success', 'Jenis kontrak berhasil dihapus.');
    }
}
