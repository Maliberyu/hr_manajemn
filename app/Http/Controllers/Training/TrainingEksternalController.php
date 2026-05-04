<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\TrainingEksternal;
use App\Models\BerkasPegawai;
use App\Models\MasterBerkasPegawai;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingEksternalController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $list = TrainingEksternal::with('pegawai', 'submittedBy')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $s) =>
                $q->where('nama_training', 'like', "%{$s}%")
                  ->orWhere('lembaga', 'like', "%{$s}%"))
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        // Badge: pending approval yang perlu tindakan user saat ini
        $pendingAtasan = TrainingEksternal::where('status', 'menunggu_atasan')->count();
        $pendingHrd    = TrainingEksternal::where('status', 'menunggu_hrd')->count();
        $pendingValidasi = TrainingEksternal::where('status', 'menunggu_validasi')->count();

        return view('training.eksternal.index', compact(
            'list', 'pendingAtasan', 'pendingHrd', 'pendingValidasi'
        ));
    }

    // ── Create / Store ─────────────────────────────────────────────────────────

    public function create()
    {
        $pegawai   = Pegawai::aktif()->orderBy('nama')->get(['id', 'nama', 'nik', 'jbtn']);
        $atasanList = User::whereIn('role', ['atasan', 'hrd', 'admin'])
                          ->orderBy('nama')
                          ->get(['id', 'nama', 'role']);

        return view('training.eksternal.create', compact('pegawai', 'atasanList'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'pegawai_id'      => 'required|integer',
            'nama_training'   => 'required|max:200',
            'lembaga'         => 'required|max:150',
            'lokasi'          => 'nullable|max:150',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'biaya'           => 'nullable|numeric|min:0',
            'deskripsi'       => 'nullable|max:2000',
            'mode'            => 'required|in:pengajuan,rekam_langsung',
            'atasan_id'       => 'nullable|integer',
            // rekam_langsung
            'nomor_sertifikat'=> 'nullable|max:100',
            'masa_berlaku'    => 'nullable|date|after_or_equal:tanggal_selesai',
            'file_sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $mode   = $v['mode'];
        $status = $mode === 'rekam_langsung' ? 'menunggu_validasi' : 'menunggu_atasan';

        $data = [
            'pegawai_id'      => $v['pegawai_id'],
            'submitted_by'    => auth()->id(),
            'nama_training'   => $v['nama_training'],
            'lembaga'         => $v['lembaga'],
            'lokasi'          => $v['lokasi'] ?? null,
            'tanggal_mulai'   => $v['tanggal_mulai'],
            'tanggal_selesai' => $v['tanggal_selesai'],
            'biaya'           => $v['biaya'] ?? 0,
            'deskripsi'       => $v['deskripsi'] ?? null,
            'mode'            => $mode,
            'status'          => $status,
            'atasan_id'       => $mode === 'pengajuan' ? ($v['atasan_id'] ?? null) : null,
        ];

        if ($mode === 'rekam_langsung') {
            $data['nomor_sertifikat'] = $v['nomor_sertifikat'] ?? null;
            $data['masa_berlaku']     = $v['masa_berlaku'] ?? null;

            if ($request->hasFile('file_sertifikat')) {
                $data['file_sertifikat'] = $request->file('file_sertifikat')
                    ->store('training/eksternal', 'public');
                $data['uploaded_at'] = now();
            }
        }

        TrainingEksternal::create($data);

        return redirect()->route('training.eksternal.index')
            ->with('success', "Training \"{$v['nama_training']}\" berhasil diajukan.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(TrainingEksternal $eksternal)
    {
        $eksternal->load('pegawai', 'submittedBy', 'atasan', 'approvedAtasanBy', 'approvedHrdBy', 'validatedBy');
        return view('training.eksternal.show', compact('eksternal'));
    }

    // ── Approve / Tolak Atasan ─────────────────────────────────────────────────

    public function approveAtasan(TrainingEksternal $eksternal)
    {
        abort_unless($eksternal->bisaApproveAtasan(), 403);

        $eksternal->update([
            'status'             => 'menunggu_hrd',
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
            'catatan_atasan'     => null,
        ]);

        return back()->with('success', 'Disetujui oleh atasan. Menunggu persetujuan HRD.');
    }

    public function tolakAtasan(Request $request, TrainingEksternal $eksternal)
    {
        abort_unless($eksternal->bisaApproveAtasan(), 403);
        $request->validate(['catatan_atasan' => 'required|max:500']);

        $eksternal->update([
            'status'             => 'ditolak_atasan',
            'catatan_atasan'     => $request->catatan_atasan,
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }

    // ── Approve / Tolak HRD ───────────────────────────────────────────────────

    public function approveHrd(TrainingEksternal $eksternal)
    {
        abort_unless($eksternal->bisaApproveHrd(), 403);

        $eksternal->update([
            'status'         => 'disetujui',
            'approved_hrd_by'=> auth()->id(),
            'approved_hrd_at'=> now(),
            'catatan_hrd'    => null,
        ]);

        return back()->with('success', 'Disetujui HRD. Training dapat dilaksanakan.');
    }

    public function tolakHrd(Request $request, TrainingEksternal $eksternal)
    {
        abort_unless($eksternal->bisaApproveHrd(), 403);
        $request->validate(['catatan_hrd' => 'required|max:500']);

        $eksternal->update([
            'status'          => 'ditolak_hrd',
            'catatan_hrd'     => $request->catatan_hrd,
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }

    // ── Upload Sertifikat (oleh karyawan setelah selesai) ─────────────────────

    public function uploadSertifikat(Request $request, TrainingEksternal $eksternal)
    {
        abort_unless($eksternal->bisaUploadSertifikat(), 403);

        $request->validate([
            'nomor_sertifikat' => 'required|max:100',
            'masa_berlaku'     => 'nullable|date',
            'file_sertifikat'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($eksternal->file_sertifikat) {
            Storage::disk('public')->delete($eksternal->file_sertifikat);
        }

        $path = $request->file('file_sertifikat')
            ->store('training/eksternal', 'public');

        $eksternal->update([
            'nomor_sertifikat' => $request->nomor_sertifikat,
            'masa_berlaku'     => $request->masa_berlaku,
            'file_sertifikat'  => $path,
            'uploaded_at'      => now(),
            'status'           => 'menunggu_validasi',
        ]);

        return back()->with('success', 'Sertifikat berhasil diupload. Menunggu validasi HR.');
    }

    // ── Validasi HR ───────────────────────────────────────────────────────────

    public function validasi(TrainingEksternal $eksternal)
    {
        abort_unless($eksternal->bisaValidasiHr(), 403);

        $eksternal->update([
            'status'       => 'tervalidasi',
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        // Push ke hr_berkas pegawai
        if ($eksternal->file_sertifikat && $eksternal->pegawai?->nik) {
            $this->pushKeBerkas($eksternal);
        }

        return back()->with('success', 'Sertifikat tervalidasi dan masuk ke dokumen karyawan.');
    }

    // ── Helper ─────────────────────────────────────────────────────────────────

    private function pushKeBerkas(TrainingEksternal $eksternal): void
    {
        $jenis = MasterBerkasPegawai::firstOrCreate(
            ['nama' => 'Sertifikat Training Eksternal'],
            ['kategori' => 'Pelatihan', 'urutan' => (MasterBerkasPegawai::max('urutan') ?? 0) + 1]
        );

        BerkasPegawai::updateOrCreate(
            [
                'nik'       => $eksternal->pegawai->nik,
                'jenis_id'  => $jenis->id,
                'keterangan'=> $eksternal->nomor_sertifikat,
            ],
            [
                'nama_file'  => "Sertifikat_{$eksternal->nama_training}_{$eksternal->pegawai->nama}.pdf",
                'path'       => $eksternal->file_sertifikat,
                'tgl_upload' => today(),
            ]
        );
    }
}
