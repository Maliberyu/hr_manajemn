<?php

namespace App\Http\Controllers\Ess;

use App\Http\Controllers\Controller;
use App\Models\AtasanPegawai;
use App\Models\Beasiswa;
use App\Models\HrNotification;
use App\Models\RiwayatPendidikan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EssPendidikanController extends Controller
{
    // ─── Halaman utama ESS Pendidikan ─────────────────────────────────────────

    public function index()
    {
        $pegawai = auth()->user()->pegawai;
        abort_if(!$pegawai, 403, 'Akun belum terhubung ke data pegawai.');

        $riwayats  = RiwayatPendidikan::where('nik', $pegawai->nik)
            ->orderByRaw("FIELD(jenjang, 'S3','S2','S1','D3','D2','D1','SMA/SMK','SMP','SD','Non-Formal')")
            ->get();

        $beasiswas = Beasiswa::where('nik', $pegawai->nik)
            ->orderByDesc('created_at')
            ->get();

        $jenjangList = RiwayatPendidikan::jenjangList();
        $jenisBeasiswaList = Beasiswa::jenisLabel();

        return view('ess.pendidikan', compact(
            'pegawai', 'riwayats', 'beasiswas',
            'jenjangList', 'jenisBeasiswaList'
        ));
    }

    // ─── Riwayat Pendidikan (self) ────────────────────────────────────────────

    public function riwayatStore(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        abort_if(!$pegawai, 403);

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
            RiwayatPendidikan::where('nik', $pegawai->nik)->update(['is_terakhir' => false]);
        }

        if ($request->hasFile('file_ijazah')) {
            $data['file_ijazah'] = $request->file('file_ijazah')
                ->store("hr_pendidikan/ijazah/{$pegawai->nik}", 'public');
        }

        RiwayatPendidikan::create($data + [
            'nik'        => $pegawai->nik,
            'dibuat_oleh'=> auth()->id(),
        ]);

        return back()->with('success', 'Riwayat pendidikan berhasil ditambahkan.');
    }

    public function riwayatDestroy(RiwayatPendidikan $riwayat)
    {
        $pegawai = auth()->user()->pegawai;
        abort_if(!$pegawai || $riwayat->nik !== $pegawai->nik, 403);

        if ($riwayat->file_ijazah) {
            Storage::disk('public')->delete($riwayat->file_ijazah);
        }
        $riwayat->delete();

        return back()->with('success', 'Data dihapus.');
    }

    // ─── Beasiswa / Bantuan Pendidikan ────────────────────────────────────────

    public function beasiswaStore(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        abort_if(!$pegawai, 403, 'Akun belum terhubung ke data pegawai.');

        $data = $request->validate([
            'jenis'          => 'required|in:tugas_belajar,ijin_belajar,kursus,sertifikasi,lainnya',
            'nama_program'   => 'required|string|max:200',
            'institusi'      => 'required|string|max:150',
            'kota'           => 'nullable|string|max:100',
            'biaya_diajukan' => 'required|numeric|min:0',
            'tgl_mulai'      => 'required|date',
            'tgl_selesai'    => 'nullable|date|after:tgl_mulai',
            'catatan_pengaju'=> 'nullable|string|max:1000',
            'file_proposal'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('file_proposal')) {
            $data['file_proposal'] = $request->file('file_proposal')
                ->store("hr_pendidikan/proposal/{$pegawai->nik}", 'public');
        }

        $beasiswa = Beasiswa::create($data + [
            'nik'          => $pegawai->nik,
            'status'       => 'menunggu_atasan',
            'diajukan_oleh'=> auth()->id(),
        ]);

        // Notifikasi ke atasan
        $atasan = AtasanPegawai::with('atasan')->where('nik', $pegawai->nik)->first();
        if ($atasan?->atasan) {
            HrNotification::kirim(
                $atasan->atasan->id,
                'beasiswa_menunggu_atasan',
                'Pengajuan Bantuan Pendidikan Menunggu Persetujuan',
                "{$pegawai->nama} mengajukan bantuan pendidikan: {$beasiswa->nama_program}.",
                route('pendidikan.beasiswa.show', $beasiswa)
            );
        }

        return back()->with('success', 'Pengajuan bantuan pendidikan berhasil dikirim dan menunggu persetujuan atasan.');
    }

    public function beasiswaApproveAtasan(Request $request, Beasiswa $beasiswa)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['atasan','hrd','admin']), 403);

        // Validasi: pengaju bawahan atasan ini
        $isAtasan = AtasanPegawai::where('nik', $beasiswa->nik)
            ->where('user_id', $user->id)->exists();
        abort_unless($isAtasan || in_array($user->role, ['hrd','admin']), 403);
        abort_unless($beasiswa->status === 'menunggu_atasan', 422, 'Status tidak valid.');

        $data = $request->validate([
            'keputusan'     => 'required|in:lanjut,ditolak',
            'catatan_atasan'=> 'nullable|string|max:400',
        ]);

        if ($data['keputusan'] === 'lanjut') {
            $beasiswa->update([
                'status'             => 'menunggu_hrd',
                'catatan_atasan'     => $data['catatan_atasan'],
                'approve_atasan_oleh'=> $user->id,
            ]);

            // Notifikasi ke HRD
            HrNotification::kirimKeHrd(
                'beasiswa_menunggu_hrd',
                'Pengajuan Bantuan Pendidikan dari ' . $beasiswa->pegawai->nama,
                "Disetujui atasan, menunggu keputusan HRD: {$beasiswa->nama_program}.",
                route('pendidikan.beasiswa.show', $beasiswa)
            );
        } else {
            $beasiswa->update([
                'status'             => 'ditolak',
                'catatan_atasan'     => $data['catatan_atasan'],
                'approve_atasan_oleh'=> $user->id,
            ]);

            HrNotification::kirimKePegawai(
                $beasiswa->nik,
                'beasiswa_ditolak',
                'Pengajuan Bantuan Pendidikan Ditolak',
                "Pengajuan \"{$beasiswa->nama_program}\" ditolak oleh atasan.",
                route('ess.pendidikan.index')
            );
        }

        return back()->with('success', $data['keputusan'] === 'lanjut'
            ? 'Pengajuan diteruskan ke HRD.'
            : 'Pengajuan ditolak.');
    }
}
