<?php

namespace App\Http\Controllers\Lembur;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\Lembur;
use App\Models\LemburSetting;
use App\Models\Pegawai;
use App\Models\AtasanPegawai;
use App\Models\User;
use App\Models\TarifLembur;
use App\Models\TarifLemburPendidikan;
use App\Models\HrNotification;
use App\Models\SlipGaji;
use App\Models\SlipKomponen;
use App\Services\LemburKalkulasiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LemburController extends Controller
{
    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = Lembur::with(['pegawai'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->bulan,  fn($q, $b) => $q->whereMonth('tanggal', $b))
            ->when($request->tahun,  fn($q, $t) => $q->whereYear('tanggal', $t))
            ->when($request->q,      fn($q, $s) =>
                $q->whereHas('pegawai', fn($p) => $p->where('nama', 'like', "%$s%")));

        if ($user->hasRole('karyawan')) {
            $query->where('pegawai_id', $user->pegawai?->id ?? 0);
        } elseif ($user->hasRole('atasan')) {
            $nikBawahan = AtasanPegawai::nikBawahan($user->id);
            $nikSendiri = $user->pegawai?->nik ?? '';
            $semuaNik   = array_filter(array_merge([$nikSendiri], $nikBawahan));
            $ids        = Pegawai::whereIn('nik', $semuaNik)->pluck('id');
            $query->whereIn('pegawai_id', $ids);
        }

        $lembur      = $query->orderByDesc('tanggal')->paginate(25)->withQueryString();
        $totalAtasan = Lembur::menungguAtasan()->count();
        $totalHrd    = Lembur::menungguHrd()->count();

        // Draft milik user ini yang belum dikonfirmasi
        $draftSaya = $user->hasRole(['karyawan','atasan'])
            ? Lembur::where('pegawai_id', $user->pegawai?->id ?? 0)
                    ->where('status', 'Draft')->count()
            : 0;

        return view('lembur.index', compact('lembur', 'totalAtasan', 'totalHrd', 'draftSaya'));
    }

    // ─── Form pengajuan ───────────────────────────────────────────────────────

    public function create()
    {
        $user    = auth()->user();
        $setting = LemburSetting::get();

        if ($user->hasRole(['karyawan', 'atasan'])) {
            $pegawai = collect([$user->pegawai])->filter();
        } else {
            $pegawai = Pegawai::aktif()->orderBy('nama')->get(['id','nama','nik','jbtn','departemen','gapok']);
        }

        $tarifMap = TarifLembur::all()->keyBy('dep_id');

        return view('lembur.create', compact('pegawai', 'tarifMap', 'setting'));
    }

    // ─── Preview kalkulasi (AJAX) ─────────────────────────────────────────────

    public function hitungPreview(Request $request)
    {
        $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'tanggal'    => 'required|date',
            'jam_mulai'  => 'required|date_format:H:i',
            'jam_selesai'=> 'required|date_format:H:i',
            'jenis'      => 'required|in:HB,HR',
        ]);

        $peg      = Pegawai::findOrFail($request->pegawai_id);
        $tanggal  = Carbon::parse($request->tanggal);
        $jamMulai = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
        $jamKeluar= Carbon::parse($request->tanggal . ' ' . $request->jam_selesai);

        if ($jamKeluar->lt($jamMulai)) $jamKeluar->addDay();

        $svc    = new LemburKalkulasiService();
        $result = $svc->hitung($peg, $tanggal, $jamMulai, $jamKeluar, $request->jenis);

        if (!$result['dihitung']) {
            return response()->json(['dihitung' => false, 'alasan' => $result['alasan']]);
        }

        return response()->json([
            'dihitung'          => true,
            'metode'            => $result['metode'],
            'metode_label'      => $result['metode'] === 'shift' ? 'Berdasarkan Shift' : 'Berdasarkan Jam Aktual',
            'shift_kode'        => $result['shift_kode'],
            'jam_selesai_shift' => $result['jam_selesai_shift'],
            'multiplier'        => $result['multiplier'],
            'upah_per_jam'      => $result['upah_per_jam'],
            'upah_per_jam_fmt'  => 'Rp ' . number_format($result['upah_per_jam'], 0, ',', '.'),
            'durasi_aktual'     => $result['durasi_aktual'],
            'durasi_jam'        => $result['durasi_jam'],
            'durasi_label'      => Lembur::formatDurasi($result['durasi_jam']),
            'nominal'           => $result['nominal'],
            'nominal_fmt'       => 'Rp ' . number_format($result['nominal'], 0, ',', '.'),
            'catatan_sistem'    => $result['catatan_sistem'],
        ]);
    }

    // ─── Simpan pengajuan ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id'  => 'required|exists:pegawai,id',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'jenis'       => 'required|in:HB,HR',
            'keterangan'  => 'required|max:255',
        ]);

        $peg      = Pegawai::findOrFail($validated['pegawai_id']);
        $tanggal  = Carbon::parse($validated['tanggal']);
        $jamMulai = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_mulai']);
        $jamKeluar= Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_selesai']);

        if ($jamKeluar->lt($jamMulai)) $jamKeluar->addDay();

        $svc    = new LemburKalkulasiService();
        $result = $svc->hitung($peg, $tanggal, $jamMulai, $jamKeluar, $validated['jenis']);

        if (!$result['dihitung']) {
            return back()->withInput()
                ->withErrors(['jam_selesai' => $result['alasan']]);
        }

        $adaAtasan  = AtasanPegawai::where('nik', $peg->nik)->exists();
        $statusAwal = $adaAtasan ? 'Menunggu Atasan' : 'Menunggu HRD';

        $lembur = Lembur::create([
            'pegawai_id'        => $validated['pegawai_id'],
            'tanggal'           => $validated['tanggal'],
            'jam_mulai'         => $validated['jam_mulai'],
            'jam_selesai'       => $validated['jam_selesai'],
            'jenis'             => $validated['jenis'],
            'keterangan'        => $validated['keterangan'],
            'status'            => $statusAwal,
            // Hasil kalkulasi
            'metode'            => $result['metode'],
            'shift_kode'        => $result['shift_kode'],
            'jam_selesai_shift' => $result['jam_selesai_shift'],
            'multiplier'        => $result['multiplier'],
            'upah_per_jam'      => $result['upah_per_jam'],
            'durasi_aktual'     => $result['durasi_aktual'],
            'durasi_jam'        => $result['durasi_jam'],
            'nominal'           => $result['nominal'],
            'catatan_sistem'    => $result['catatan_sistem'],
        ]);

        $link = route('lembur.show', $lembur);
        if ($adaAtasan) {
            HrNotification::kirimKeAtasan($peg->nik, 'lembur_submitted',
                'Pengajuan Lembur Baru',
                "Ada pengajuan lembur dari {$peg->nama} menunggu persetujuan Anda.", $link);
        } else {
            HrNotification::kirimKeHrd('lembur_submitted',
                'Pengajuan Lembur Baru',
                "Pengajuan lembur dari {$peg->nama} menunggu persetujuan HRD.", $link);
        }

        $nominal = 'Rp ' . number_format($result['nominal'], 0, ',', '.');
        return redirect()->route('lembur.index')
            ->with('success', "Lembur berhasil diajukan. Estimasi nominal: {$nominal}.");
    }

    // ─── Detail ───────────────────────────────────────────────────────────────

    public function show(Lembur $lembur)
    {
        $lembur->load(['pegawai.departemenRef', 'approverAtasan', 'approverHrd']);
        return view('lembur.show', compact('lembur'));
    }

    // ─── Konfirmasi Draft (dari auto-create saat checkout) ────────────────────

    public function konfirmasiDraft(Request $request, Lembur $lembur)
    {
        abort_unless($lembur->status === 'Draft', 422, 'Hanya draft yang bisa dikonfirmasi.');
        abort_unless(
            auth()->user()->hasRole(['hrd','admin'])
            || $lembur->pegawai_id === auth()->user()->pegawai?->id,
            403
        );

        // Hitung ulang untuk memastikan angka masih valid
        $peg      = $lembur->pegawai;
        $tanggal  = Carbon::parse($lembur->tanggal);
        $jamMulai = Carbon::parse($lembur->tanggal->format('Y-m-d') . ' ' . $lembur->jam_mulai);
        $jamKeluar= Carbon::parse($lembur->tanggal->format('Y-m-d') . ' ' . $lembur->jam_selesai);
        if ($jamKeluar->lt($jamMulai)) $jamKeluar->addDay();

        $svc    = new LemburKalkulasiService();
        $result = $svc->hitung($peg, $tanggal, $jamMulai, $jamKeluar, $lembur->jenis ?? 'HB');

        if (!$result['dihitung']) {
            $lembur->delete();
            return redirect()->route('lembur.index')
                ->with('info', 'Draft lembur dibatalkan: ' . $result['alasan']);
        }

        $adaAtasan  = AtasanPegawai::where('nik', $peg->nik)->exists();
        $statusBaru = $adaAtasan ? 'Menunggu Atasan' : 'Menunggu HRD';

        $lembur->update([
            'status'         => $statusBaru,
            // Perbarui kalkulasi
            'metode'         => $result['metode'],
            'shift_kode'     => $result['shift_kode'],
            'jam_selesai_shift' => $result['jam_selesai_shift'],
            'multiplier'     => $result['multiplier'],
            'upah_per_jam'   => $result['upah_per_jam'],
            'durasi_aktual'  => $result['durasi_aktual'],
            'durasi_jam'     => $result['durasi_jam'],
            'nominal'        => $result['nominal'],
            'catatan_sistem' => $result['catatan_sistem'],
        ]);

        $link = route('lembur.show', $lembur);
        if ($adaAtasan) {
            HrNotification::kirimKeAtasan($peg->nik, 'lembur_submitted',
                'Pengajuan Lembur Baru',
                "Ada pengajuan lembur dari {$peg->nama} menunggu persetujuan Anda.", $link);
        } else {
            HrNotification::kirimKeHrd('lembur_submitted',
                'Pengajuan Lembur Baru',
                "Pengajuan lembur dari {$peg->nama} menunggu persetujuan HRD.", $link);
        }

        $nominal = 'Rp ' . number_format($result['nominal'], 0, ',', '.');
        return redirect()->route('lembur.index')
            ->with('success', "Draft lembur dikonfirmasi. Nominal: {$nominal}.");
    }

    // ─── Batalkan Draft ───────────────────────────────────────────────────────

    public function batalkanDraft(Lembur $lembur)
    {
        abort_unless($lembur->status === 'Draft', 422);
        abort_unless(
            auth()->user()->hasRole(['hrd','admin'])
            || $lembur->pegawai_id === auth()->user()->pegawai?->id,
            403
        );
        $lembur->delete();
        return redirect()->route('lembur.index')
            ->with('success', 'Draft lembur dibatalkan.');
    }

    // ─── Approve Atasan ───────────────────────────────────────────────────────

    public function approveAtasan(Request $request, Lembur $lembur)
    {
        abort_unless($lembur->bisaApproveAtasan(), 403);

        $lembur->update([
            'status'             => 'Menunggu HRD',
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
            'catatan_atasan'     => $request->catatan_atasan,
        ]);

        $link = route('lembur.show', $lembur);
        HrNotification::kirimKePegawai($lembur->pegawai?->nik ?? '', 'lembur_approved_atasan',
            'Lembur Disetujui Atasan', "Pengajuan lembur Anda disetujui atasan, menunggu HRD.", $link);
        HrNotification::kirimKeHrd('lembur_submitted',
            'Lembur Perlu Disetujui HRD', "Lembur {$lembur->pegawai?->nama} menunggu persetujuan HRD.", $link);

        return back()->with('success', "Lembur disetujui. Menunggu persetujuan HRD.");
    }

    // ─── Tolak Atasan ─────────────────────────────────────────────────────────

    public function tolakAtasan(Request $request, Lembur $lembur)
    {
        abort_unless($lembur->status === 'Menunggu Atasan', 422);
        $request->validate(['catatan_atasan' => 'required|max:255']);

        $lembur->update([
            'status'             => 'Ditolak Atasan',
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
            'catatan_atasan'     => $request->catatan_atasan,
        ]);

        return back()->with('success', "Pengajuan lembur ditolak.");
    }

    // ─── Approve HRD ──────────────────────────────────────────────────────────

    public function approveHrd(Request $request, Lembur $lembur)
    {
        abort_unless($lembur->bisaApproveHrd(), 403);

        $lembur->update([
            'status'          => 'Disetujui',
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
            'catatan_hrd'     => $request->catatan_hrd,
        ]);

        // Masukkan otomatis ke slip gaji bulan yang bersangkutan
        $masukPayroll = $this->masukkanKePayroll($lembur);

        HrNotification::kirimKePegawai($lembur->pegawai?->nik ?? '', 'lembur_approved',
            'Lembur Disetujui', "Lembur Anda disetujui HRD. Nominal: Rp " . number_format($lembur->nominal, 0, ',', '.'),
            route('lembur.show', $lembur));

        $pesan = $masukPayroll
            ? "Lembur disetujui HRD. Nominal Rp " . number_format($lembur->nominal, 0, ',', '.') . " sudah masuk ke slip gaji."
            : "Lembur disetujui HRD. Nominal tidak otomatis masuk ke slip (nominal 0 atau slip tidak ditemukan).";

        return back()->with('success', $pesan);
    }

    // ─── Tolak HRD ────────────────────────────────────────────────────────────

    public function tolakHrd(Request $request, Lembur $lembur)
    {
        abort_unless($lembur->status === 'Menunggu HRD', 422);
        $request->validate(['catatan_hrd' => 'required|max:255']);

        $lembur->update([
            'status'          => 'Ditolak HRD',
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
            'catatan_hrd'     => $request->catatan_hrd,
        ]);

        return back()->with('success', "Pengajuan lembur ditolak.");
    }

    // ─── Sync manual ke payroll (untuk lembur lama yang belum masuk) ─────────

    public function syncPayroll(Lembur $lembur)
    {
        abort_unless($lembur->status === 'Disetujui', 422, 'Hanya lembur yang sudah disetujui.');
        abort_unless(auth()->user()->hasRole(['hrd','admin']), 403);

        $ok = $this->masukkanKePayroll($lembur);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok
                ? "Lembur Rp " . number_format($lembur->nominal, 0, ',', '.') . " berhasil dimasukkan ke slip gaji."
                : "Gagal memasukkan ke slip gaji. Cek log untuk detail."
        );
    }

    // ─── Inject lembur ke slip gaji ──────────────────────────────────────────

    private function masukkanKePayroll(Lembur $lembur): bool
    {
        try {
            if (!$lembur->nominal || $lembur->nominal <= 0) return false;
            if (!$lembur->pegawai_id) return false;

            $bulan  = (int) Carbon::parse($lembur->tanggal)->format('m');
            $tahun  = (int) Carbon::parse($lembur->tanggal)->format('Y');
            $sumber = 'lb:' . $lembur->id;   // max 20 char sesuai kolom varchar(20)

            $peg = $lembur->pegawai ?? \App\Models\Pegawai::find($lembur->pegawai_id);
            if (!$peg) return false;

            // Cari slip bulan tersebut, atau buat baru
            $slip = SlipGaji::where('pegawai_id', $lembur->pegawai_id)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->first();

            if (!$slip) {
                $gapok = (float) ($peg->gapok ?? 0);
                $slip  = SlipGaji::create([
                    'nik'             => $peg->nik,
                    'pegawai_id'      => $lembur->pegawai_id,
                    'bulan'           => $bulan,
                    'tahun'           => $tahun,
                    'status'          => 'draft',
                    'gaji_pokok'      => $gapok,
                    'total_tunjangan' => 0,
                    'total_potongan'  => 0,
                    'gaji_bersih'     => $gapok,
                    'generated_by'    => auth()->id(),
                    'generated_at'    => now(),
                ]);
            }

            // Nama komponen: "Lembur 4j ×1.5 @ Rp 25.000/jam (15 Jan)"
            $durasiLabel  = Lembur::formatDurasi($lembur->durasi_jam);
            $upahPerJam   = $lembur->upah_per_jam ?? 0;
            $multiplier   = $lembur->multiplier   ?? 1.0;
            $tglFmt       = Carbon::parse($lembur->tanggal)->translatedFormat('d M');
            $tarifFmt     = 'Rp ' . number_format($upahPerJam, 0, ',', '.');

            $namaKomponen = "Lembur {$durasiLabel}"
                . ($multiplier != 1.0 ? " ×{$multiplier}" : '')
                . " @ {$tarifFmt}/jam ({$tglFmt})";

            // Upsert — satu komponen per lembur ID, tidak duplikat
            SlipKomponen::updateOrCreate(
                ['slip_id' => $slip->id, 'sumber' => $sumber],
                [
                    'nama'   => $namaKomponen,
                    'jenis'  => 'tambah',
                    'nilai'  => $lembur->nominal,
                    'urutan' => 90,
                ]
            );

            // Recalculate total slip
            $slip->load('komponenSlip');
            $slip->recalculate();

            return true;

        } catch (\Throwable $e) {
            \Log::warning("masukkanKePayroll gagal untuk lembur #{$lembur->id}: " . $e->getMessage());
            return false;
        }
    }

    // ─── Rekap ────────────────────────────────────────────────────────────────

    public function rekap(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $nikBawahanAtasan = $request->atasan_id
            ? AtasanPegawai::nikBawahan((int) $request->atasan_id)
            : null;

        $rekap = Pegawai::aktif()
            ->when($request->departemen, fn($q, $d) => $q->where('departemen', $d))
            ->when($request->bidang,     fn($q, $b) => $q->where('bidang', $b))
            ->when($nikBawahanAtasan,    fn($q)     => $q->whereIn('nik', $nikBawahanAtasan))
            ->with('departemenRef')
            ->withSum(['lembur as total_jam' => fn($q) =>
                $q->where('status','Disetujui')->bulan($tahun,$bulan)], 'durasi_jam')
            ->withSum(['lembur as total_nominal' => fn($q) =>
                $q->where('status','Disetujui')->bulan($tahun,$bulan)], 'nominal')
            ->withCount(['lembur as total_pengajuan' => fn($q) => $q->bulan($tahun,$bulan)])
            ->having('total_jam', '>', 0)
            ->orderByDesc('total_jam')
            ->paginate(30)->withQueryString();

        $departemenList = Departemen::orderBy('nama')->get(['dep_id','nama']);
        $bidangList     = Pegawai::aktif()->whereNotNull('bidang')->distinct()->orderBy('bidang')->pluck('bidang');
        $atasanList     = User::whereIn('role', ['atasan','hrd','admin'])
            ->where('status','aktif')->orderBy('nama')->get(['id','nama','jabatan']);

        return view('lembur.rekap', compact('rekap','bulan','tahun','departemenList','bidangList','atasanList'));
    }

    // ─── Setting: master setting + tarif per dept ─────────────────────────────

    public function setting()
    {
        $setting          = LemburSetting::get();
        $departemen       = Departemen::orderBy('nama')->get();
        $tarifMap         = TarifLembur::all()->keyBy('dep_id');
        $tarifPendidikan  = TarifLemburPendidikan::orderBy('pendidikan')->get();

        return view('lembur.setting', compact('setting', 'departemen', 'tarifMap', 'tarifPendidikan'));
    }

    public function updateSetting(Request $request)
    {
        // ── Update master setting ──────────────────────────────────────────────
        $request->validate([
            'metode'           => 'required|in:shift,jam_aktual,keduanya',
            'min_jam_lembur'   => 'required|numeric|min:0|max:12',
            'min_jam_shift'    => 'required|numeric|min:0|max:4',
            'max_jam_harian'   => 'required|numeric|min:1|max:24',
            'max_jam_mingguan' => 'required|numeric|min:1|max:72',
            'formula_upah_jam' => 'required|in:gapok_173,tarif_dept',
            'wajib_approval'   => 'boolean',
        ]);

        LemburSetting::get()->update([
            'metode'           => $request->metode,
            'min_jam_lembur'   => $request->min_jam_lembur,
            'min_jam_shift'    => $request->min_jam_shift,
            'max_jam_harian'   => $request->max_jam_harian,
            'max_jam_mingguan' => $request->max_jam_mingguan,
            'formula_upah_jam' => $request->formula_upah_jam,
            'wajib_approval'   => $request->boolean('wajib_approval'),
            'updated_by'       => auth()->id(),
        ]);

        // ── Update tarif per dept ──────────────────────────────────────────────
        if ($request->has('tarif')) {
            $request->validate([
                'tarif.*.dep_id' => 'required|exists:departemen,dep_id',
                'tarif.*.hb'     => 'required|numeric|min:0',
                'tarif.*.hr'     => 'required|numeric|min:0',
            ]);
            foreach ($request->tarif as $row) {
                TarifLembur::updateOrCreate(
                    ['dep_id' => $row['dep_id']],
                    ['tarif_hb' => $row['hb'], 'tarif_hr' => $row['hr']]
                );
            }
        }

        // ── Update tarif per pendidikan ────────────────────────────────────────
        if ($request->has('tarif_pend')) {
            $request->validate([
                'tarif_pend.*.pendidikan' => 'required|string|max:20',
                'tarif_pend.*.hb'         => 'required|numeric|min:0',
                'tarif_pend.*.hr'         => 'required|numeric|min:0',
            ]);
            foreach ($request->tarif_pend as $row) {
                TarifLemburPendidikan::updateOrCreate(
                    ['pendidikan' => $row['pendidikan']],
                    ['tarif_hb' => $row['hb'], 'tarif_hr' => $row['hr']]
                );
            }
        }

        return back()->with('success', 'Setting lembur berhasil disimpan.');
    }
}
