<?php

namespace App\Http\Controllers\Pendidikan;

use App\Http\Controllers\Controller;
use App\Models\Beasiswa;
use App\Models\HrNotification;
use App\Models\Pegawai;
use App\Models\RiwayatPendidikan;
use App\Models\AtasanPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PendidikanController extends Controller
{
    // ─── Riwayat Pendidikan ───────────────────────────────────────────────────

    public function riwayatIndex(Request $request)
    {
        $query = RiwayatPendidikan::with('pegawai')
            ->orderByDesc('tahun_lulus');

        if ($request->nik) {
            $query->where('nik', $request->nik);
        }
        if ($request->jenjang) {
            $query->where('jenjang', $request->jenjang);
        }

        $riwayats  = $query->paginate(25)->withQueryString();
        $jenjangList = RiwayatPendidikan::jenjangList();

        return view('pendidikan.riwayat.index', compact('riwayats', 'jenjangList'));
    }

    public function riwayatStore(Request $request)
    {
        $data = $request->validate([
            'nik'            => 'required|exists:pegawai,nik',
            'jenjang'        => 'required|in:' . implode(',', RiwayatPendidikan::jenjangList()),
            'nama_institusi' => 'required|string|max:200',
            'jurusan'        => 'nullable|string|max:100',
            'tahun_masuk'    => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'tahun_lulus'    => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'ipk'            => 'nullable|numeric|min:0|max:4',
            'file_ijazah'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_terakhir'    => 'boolean',
            'keterangan'     => 'nullable|string|max:300',
        ]);

        if ($request->boolean('is_terakhir')) {
            RiwayatPendidikan::where('nik', $data['nik'])->update(['is_terakhir' => false]);
        }

        if ($request->hasFile('file_ijazah')) {
            $data['file_ijazah'] = $request->file('file_ijazah')
                ->store("hr_pendidikan/ijazah/{$data['nik']}", 'public');
        }

        RiwayatPendidikan::create($data + ['dibuat_oleh' => auth()->id()]);

        return back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
    }

    public function riwayatUpdate(Request $request, RiwayatPendidikan $riwayat)
    {
        $data = $request->validate([
            'jenjang'        => 'required|in:' . implode(',', RiwayatPendidikan::jenjangList()),
            'nama_institusi' => 'required|string|max:200',
            'jurusan'        => 'nullable|string|max:100',
            'tahun_masuk'    => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'tahun_lulus'    => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'ipk'            => 'nullable|numeric|min:0|max:4',
            'file_ijazah'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_terakhir'    => 'boolean',
            'keterangan'     => 'nullable|string|max:300',
        ]);

        if ($request->boolean('is_terakhir')) {
            RiwayatPendidikan::where('nik', $riwayat->nik)
                ->where('id', '!=', $riwayat->id)
                ->update(['is_terakhir' => false]);
        }

        if ($request->hasFile('file_ijazah')) {
            if ($riwayat->file_ijazah) {
                Storage::disk('public')->delete($riwayat->file_ijazah);
            }
            $data['file_ijazah'] = $request->file('file_ijazah')
                ->store("hr_pendidikan/ijazah/{$riwayat->nik}", 'public');
        }

        $riwayat->update($data);

        return back()->with('success', 'Riwayat pendidikan berhasil diperbarui.');
    }

    public function riwayatDestroy(RiwayatPendidikan $riwayat)
    {
        if ($riwayat->file_ijazah) {
            Storage::disk('public')->delete($riwayat->file_ijazah);
        }
        $riwayat->delete();
        return back()->with('success', 'Riwayat pendidikan dihapus.');
    }

    // ─── Beasiswa / Bantuan Pendidikan ────────────────────────────────────────

    public function beasiswaIndex(Request $request)
    {
        $query = Beasiswa::with('pegawai')->orderByDesc('created_at');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->jenis) {
            $query->where('jenis', $request->jenis);
        }
        if ($request->nik) {
            $query->where('nik', $request->nik);
        }

        $beasiswas  = $query->paginate(20)->withQueryString();
        $menunggu   = Beasiswa::where('status', 'menunggu_hrd')->count();

        return view('pendidikan.beasiswa.index', compact('beasiswas', 'menunggu'));
    }

    public function beasiswaShow(Beasiswa $beasiswa)
    {
        $beasiswa->load(['pegawai','pengajuUser','approveAtasanUser','approveHrdUser']);
        return view('pendidikan.beasiswa.show', compact('beasiswa'));
    }

    public function beasiswaApproveHrd(Request $request, Beasiswa $beasiswa)
    {
        abort_unless($beasiswa->status === 'menunggu_hrd', 422, 'Status tidak valid.');

        $data = $request->validate([
            'keputusan'      => 'required|in:disetujui,ditolak',
            'catatan_hrd'    => 'nullable|string|max:400',
            'biaya_disetujui'=> 'nullable|numeric|min:0',
        ]);

        $beasiswa->update([
            'status'           => $data['keputusan'],
            'catatan_hrd'      => $data['catatan_hrd'],
            'biaya_disetujui'  => $data['keputusan'] === 'disetujui' ? ($data['biaya_disetujui'] ?? $beasiswa->biaya_diajukan) : null,
            'approve_hrd_oleh' => auth()->id(),
        ]);

        $label = $data['keputusan'] === 'disetujui' ? 'disetujui' : 'ditolak';
        HrNotification::kirimKePegawai(
            $beasiswa->nik,
            'beasiswa_' . $data['keputusan'],
            'Pengajuan Bantuan Pendidikan ' . ucfirst($label),
            "Pengajuan \"{$beasiswa->nama_program}\" Anda telah {$label} oleh HRD.",
            route('pendidikan.beasiswa.show', $beasiswa)
        );

        return redirect()->route('pendidikan.beasiswa.show', $beasiswa)
            ->with('success', "Pengajuan berhasil {$label}.");
    }

    public function beasiswaSelesai(Beasiswa $beasiswa)
    {
        abort_unless($beasiswa->status === 'disetujui', 422, 'Hanya pengajuan berstatus disetujui yang bisa diselesaikan.');
        $beasiswa->update(['status' => 'selesai']);
        return back()->with('success', 'Status pengajuan diubah ke Selesai.');
    }

    public function beasiswaUploadHasil(Request $request, Beasiswa $beasiswa)
    {
        $request->validate([
            'file_hasil' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($beasiswa->file_hasil) {
            Storage::disk('public')->delete($beasiswa->file_hasil);
        }

        $beasiswa->update([
            'file_hasil' => $request->file('file_hasil')
                ->store("hr_pendidikan/hasil/{$beasiswa->nik}", 'public'),
        ]);

        return back()->with('success', 'File hasil berhasil diunggah.');
    }
}
