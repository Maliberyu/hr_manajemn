<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\IHT;
use App\Models\IHTPeserta;
use App\Models\TrainingEksternal;
use App\Models\BerkasPegawai;
use App\Models\MasterBerkasPegawai;
use App\Models\Pegawai;
use App\Models\Departemen;
use App\Models\AtasanPegawai;
use App\Models\User;
use App\Models\TrainingSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TrainingController extends Controller
{
    // ── Rekap jam pelatihan per karyawan ──────────────────────────────────────

    public function rekap(Request $request)
    {
        $tahun = (int) ($request->tahun ?? now()->year);

        $nikBawahanAtasan = $request->atasan_id
            ? AtasanPegawai::nikBawahan((int) $request->atasan_id)
            : null;

        // IHT: jam = (TIME_TO_SEC(TIMEDIFF(jam_selesai, jam_mulai)) / 3600) * durasi_hari
        $ihtPerPegawai = DB::table('hr_iht_peserta as ip')
            ->join('hr_iht as i', 'ip.iht_id', '=', 'i.id')
            ->join('pegawai as p', 'ip.pegawai_id', '=', 'p.id')
            ->where('ip.status', 'hadir')
            ->whereYear('i.tanggal_mulai', $tahun)
            ->selectRaw("p.id as pegawai_id,
                SUM((TIME_TO_SEC(TIMEDIFF(i.jam_selesai, i.jam_mulai)) / 3600) * (DATEDIFF(i.tanggal_selesai, i.tanggal_mulai) + 1)) as jam_iht,
                COUNT(DISTINCT i.id) as kali_iht")
            ->groupBy('p.id')
            ->get()->keyBy('pegawai_id');

        // Eksternal: 8 jam/hari
        $eksternalPerPegawai = DB::table('hr_training_eksternal as te')
            ->join('pegawai as p', 'te.pegawai_id', '=', 'p.id')
            ->whereIn('te.status', ['tervalidasi', 'disetujui'])
            ->whereYear('te.tanggal_mulai', $tahun)
            ->selectRaw("p.id as pegawai_id,
                SUM((DATEDIFF(te.tanggal_selesai, te.tanggal_mulai) + 1) * 8) as jam_eksternal,
                COUNT(*) as kali_eksternal")
            ->groupBy('p.id')
            ->get()->keyBy('pegawai_id');

        $rekap = Pegawai::aktif()
            ->when($request->departemen, fn($q, $d) => $q->where('departemen', $d))
            ->when($request->bidang,     fn($q, $b) => $q->where('bidang', $b))
            ->when($nikBawahanAtasan,    fn($q)     => $q->whereIn('nik', $nikBawahanAtasan))
            ->with('departemenRef')
            ->orderBy('nama')
            ->get()
            ->map(function ($p) use ($ihtPerPegawai, $eksternalPerPegawai) {
                $iht   = $ihtPerPegawai[$p->id]   ?? null;
                $ekst  = $eksternalPerPegawai[$p->id] ?? null;
                $jamIHT   = round((float) ($iht?->jam_iht ?? 0), 1);
                $jamEkst  = round((float) ($ekst?->jam_eksternal ?? 0), 1);
                return [
                    'pegawai'       => $p,
                    'jam_iht'       => $jamIHT,
                    'kali_iht'      => (int) ($iht?->kali_iht ?? 0),
                    'jam_eksternal' => $jamEkst,
                    'kali_eksternal'=> (int) ($ekst?->kali_eksternal ?? 0),
                    'jam_total'     => round($jamIHT + $jamEkst, 1),
                ];
            })
            ->filter(fn($r) => $r['jam_total'] > 0 || request()->has('tampil_semua'))
            ->sortByDesc('jam_total')
            ->values();

        $departemen = Departemen::orderBy('nama')->get(['dep_id', 'nama']);
        $bidangList  = Pegawai::aktif()->whereNotNull('bidang')->distinct()->orderBy('bidang')->pluck('bidang');
        $atasanList  = User::whereIn('role', ['atasan', 'hrd', 'admin'])
            ->where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'jabatan']);

        return view('pelatihan.rekap', compact('rekap', 'tahun', 'departemen', 'bidangList', 'atasanList'));
    }

    // ── Index ──────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $ihtList = IHT::withCount('peserta')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $s) => $q->where('nama_training', 'like', "%{$s}%"))
            ->orderByDesc('tanggal_mulai')
            ->paginate(20)->withQueryString();

        $pendingCount = IHT::where('status', 'draft')->count();

        return view('training.iht.index', compact('ihtList', 'pendingCount'));
    }

    // ── Create / Store ─────────────────────────────────────────────────────────

    public function create()
    {
        return view('training.iht.create');
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'nama_training'       => 'required|max:200',
            'penyelenggara'       => 'required|max:100',
            'pemateri'            => 'nullable|max:150',
            'lokasi'              => 'required|max:150',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'jam_mulai'           => 'nullable|date_format:H:i',
            'jam_selesai'         => 'nullable|date_format:H:i',
            'deskripsi'           => 'nullable|max:3000',
            'kuota'               => 'nullable|integer|min:1',
            'penandatangan_nama'  => 'nullable|max:100',
            'penandatangan_jabatan' => 'nullable|max:100',
            'status'              => 'required|in:draft,aktif',
        ]);

        IHT::create([...$v, 'dibuat_oleh' => auth()->id()]);

        return redirect()->route('training.iht.index')
            ->with('success', "IHT \"{$v['nama_training']}\" berhasil dibuat.");
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(IHT $iht)
    {
        $iht->load('dibuatOleh');

        $peserta = IHTPeserta::with('pegawai.departemenRef')
            ->where('iht_id', $iht->id)
            ->orderBy('created_at')
            ->get();

        $terdaftarIds = $peserta->pluck('pegawai_id');
        $pegawaiBelum = Pegawai::aktif()
            ->whereNotIn('id', $terdaftarIds)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nik', 'jbtn']);

        return view('training.iht.show', compact('iht', 'peserta', 'pegawaiBelum'));
    }

    // ── Edit / Update ─────────────────────────────────────────────────────────

    public function edit(IHT $iht)
    {
        abort_if($iht->status === 'selesai', 403, 'Training sudah selesai.');
        return view('training.iht.edit', compact('iht'));
    }

    public function update(Request $request, IHT $iht)
    {
        $v = $request->validate([
            'nama_training'         => 'required|max:200',
            'penyelenggara'         => 'required|max:100',
            'pemateri'              => 'nullable|max:150',
            'lokasi'                => 'required|max:150',
            'tanggal_mulai'         => 'required|date',
            'tanggal_selesai'       => 'required|date|after_or_equal:tanggal_mulai',
            'jam_mulai'             => 'nullable|date_format:H:i',
            'jam_selesai'           => 'nullable|date_format:H:i',
            'deskripsi'             => 'nullable|max:3000',
            'kuota'                 => 'nullable|integer|min:1',
            'penandatangan_nama'    => 'nullable|max:100',
            'penandatangan_jabatan' => 'nullable|max:100',
            'status'                => 'required|in:draft,aktif,selesai,dibatalkan',
        ]);

        $iht->update($v);
        return redirect()->route('training.iht.show', $iht)
            ->with('success', 'Data IHT diperbarui.');
    }

    // ── Tambah Peserta ─────────────────────────────────────────────────────────

    public function storePeserta(Request $request, IHT $iht)
    {
        $request->validate([
            'pegawai_ids'   => 'required|array|min:1',
            'pegawai_ids.*' => 'integer',
        ]);

        if ($iht->kuota) {
            $terdaftar = $iht->peserta()->count();
            $sisa = $iht->kuota - $terdaftar;
            if (count($request->pegawai_ids) > $sisa) {
                return back()->withErrors(['kuota' => "Kuota tersisa $sisa slot."]);
            }
        }

        foreach ($request->pegawai_ids as $id) {
            IHTPeserta::firstOrCreate(
                ['iht_id' => $iht->id, 'pegawai_id' => $id],
                ['status' => 'terdaftar']
            );
        }

        return back()->with('success', count($request->pegawai_ids) . ' peserta ditambahkan.');
    }

    // ── Update Status Peserta ──────────────────────────────────────────────────

    public function updateStatusPeserta(Request $request, IHT $iht, IHTPeserta $peserta)
    {
        $request->validate([
            'status' => 'required|in:terdaftar,hadir,tidak_hadir,selesai',
            'nilai'  => 'nullable|numeric|min:0|max:100',
        ]);

        $peserta->update($request->only('status', 'nilai'));
        return back()->with('success', "Status {$peserta->pegawai->nama} diperbarui.");
    }

    // ── Hapus Peserta ─────────────────────────────────────────────────────────

    public function destroyPeserta(IHT $iht, IHTPeserta $peserta)
    {
        abort_if($peserta->sudahSertifikat(), 403, 'Peserta sudah memiliki sertifikat.');
        $peserta->delete();
        return back()->with('success', 'Peserta dihapus.');
    }

    // ── Generate Sertifikat ───────────────────────────────────────────────────

    public function generateSertifikat(IHT $iht, IHTPeserta $peserta)
    {
        abort_unless(in_array($peserta->status, ['hadir', 'selesai']), 422, 'Peserta belum hadir.');

        $nomor = IHT::generateNomorSertifikat();
        $logo  = TrainingSetting::logoUrl();

        $pdf = Pdf::loadView('training.iht.sertifikat-pdf', compact('iht', 'peserta', 'nomor', 'logo'))
                  ->setPaper('a4', 'landscape');

        $filename = "sertifikat_{$iht->id}_{$peserta->pegawai_id}.pdf";
        $path     = "training/sertifikat/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        $peserta->update([
            'nomor_sertifikat' => $nomor,
            'sertifikat_path'  => $path,
            'sertifikat_at'    => now(),
            'status'           => 'selesai',
        ]);

        // Push ke hr_berkas pegawai
        $this->pushKeBerkas($peserta, $iht->nama_training, $path, $nomor);

        return back()->with('success', "Sertifikat {$nomor} berhasil dibuat untuk {$peserta->pegawai->nama}.");
    }

    // ── Download Sertifikat ────────────────────────────────────────────────────

    public function downloadSertifikat(IHT $iht, IHTPeserta $peserta)
    {
        abort_unless($peserta->sertifikat_path, 404);
        return Storage::disk('public')->download(
            $peserta->sertifikat_path,
            "Sertifikat_{$peserta->pegawai->nama}_{$iht->nama_training}.pdf"
        );
    }

    // ── Tutup / Batalkan ──────────────────────────────────────────────────────

    public function tutup(IHT $iht)
    {
        $iht->update(['status' => 'selesai']);
        return back()->with('success', 'IHT ditutup.');
    }

    public function destroy(IHT $iht)
    {
        $iht->update(['status' => 'dibatalkan']);
        return redirect()->route('training.iht.index')->with('success', 'IHT dibatalkan.');
    }

    // ── Setting Training ──────────────────────────────────────────────────────

    public function setting()
    {
        $logoUrl = TrainingSetting::logoUrl();
        return view('training.setting', compact('logoUrl'));
    }

    public function settingUpdate(Request $request)
    {
        $request->validate([
            'logo_rs' => 'nullable|file|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo_rs')) {
            $old = TrainingSetting::get('logo_rs');
            if ($old) Storage::disk('public')->delete($old);

            $path = $request->file('logo_rs')->store('training/setting', 'public');
            TrainingSetting::set('logo_rs', $path);
        }

        return back()->with('success', 'Setting training diperbarui.');
    }

    // ── Helper ─────────────────────────────────────────────────────────────────

    private function pushKeBerkas(IHTPeserta $peserta, string $namaTraining, string $path, string $nomor): void
    {
        if (!$peserta->pegawai->nik) return;

        $jenis = MasterBerkasPegawai::firstOrCreate(
            ['nama' => 'Sertifikat Training IHT'],
            ['kategori' => 'Pelatihan', 'urutan' => (MasterBerkasPegawai::max('urutan') ?? 0) + 1]
        );

        BerkasPegawai::updateOrCreate(
            ['nik' => $peserta->pegawai->nik, 'jenis_id' => $jenis->id, 'keterangan' => $nomor],
            [
                'nama_file'  => "Sertifikat_{$namaTraining}_{$peserta->pegawai->nama}.pdf",
                'path'       => $path,
                'tgl_upload' => today(),
            ]
        );
    }
}
