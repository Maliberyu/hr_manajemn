<?php

namespace App\Http\Controllers\Ijin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIjin;
use App\Models\Pegawai;
use App\Models\AtasanPegawai;
use App\Models\Departemen;
use App\Models\User;
use App\Models\HrNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IjinController extends Controller
{
    private function validasiJenis(string $jenis): void
    {
        abort_unless(array_key_exists($jenis, PengajuanIjin::JENIS), 404);
    }

    // ─── Rekap semua jenis ijin ────────────────────────────────────────────────

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
            ->orderBy('nama')
            ->get()
            ->map(function ($p) use ($bulan, $tahun) {
                $rows = PengajuanIjin::where('nik', $p->nik)
                    ->where('status', 'Disetujui')
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->selectRaw('jenis, COUNT(*) as kali, SUM(durasi_menit) as total_menit')
                    ->groupBy('jenis')
                    ->get()
                    ->keyBy('jenis');

                $total = $rows->sum('kali');
                return ['pegawai' => $p, 'rows' => $rows, 'total' => $total];
            })
            ->filter(fn($r) => $r['total'] > 0 || request()->has('tampil_semua'));

        $departemen = Departemen::orderBy('nama')->get(['dep_id', 'nama']);
        $bidangList  = Pegawai::aktif()->whereNotNull('bidang')->distinct()->orderBy('bidang')->pluck('bidang');
        $atasanList  = User::whereIn('role', ['atasan', 'hrd', 'admin'])
            ->where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'jabatan']);
        $jenisList   = PengajuanIjin::JENIS;

        return view('ijin.rekap', compact('rekap', 'bulan', 'tahun', 'departemen', 'bidangList', 'atasanList', 'jenisList'));
    }

    // ─── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request, string $jenis)
    {
        $this->validasiJenis($jenis);

        $query = PengajuanIjin::with('pegawai')
            ->jenis($jenis)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->bulan,  fn($q, $b) => $q->whereMonth('tanggal', $b))
            ->when($request->tahun,  fn($q, $t) => $q->whereYear('tanggal', $t))
            ->when($request->q, fn($q, $s) =>
                $q->whereHas('pegawai', fn($p) => $p->cari($s)))
            ->orderByDesc('tanggal')->orderByDesc('id');

        $user = auth()->user();
        if ($user->hasRole('karyawan')) {
            $query->where('nik', $user->pegawai?->nik ?? '');
        } elseif ($user->hasRole('atasan')) {
            $nikBawahan = AtasanPegawai::nikBawahan($user->id);
            $nikSendiri = $user->pegawai?->nik ?? '';
            $query->whereIn('nik', array_filter(array_merge([$nikSendiri], $nikBawahan)));
        }

        $daftar   = $query->paginate(20)->withQueryString();
        $labelJenis = PengajuanIjin::JENIS[$jenis];

        return view('ijin.index', compact('daftar', 'jenis', 'labelJenis'));
    }

    // ─── Form buat ─────────────────────────────────────────────────────────────

    public function create(string $jenis)
    {
        $this->validasiJenis($jenis);

        $pegawai = auth()->user()->hasRole(['karyawan', 'atasan'])
            ? collect([auth()->user()->pegawai])->filter()
            : Pegawai::aktif()->orderBy('nama')->get(['id', 'nama', 'nik', 'jbtn']);

        $labelJenis = PengajuanIjin::JENIS[$jenis];

        return view('ijin.create', compact('jenis', 'labelJenis', 'pegawai'));
    }

    // ─── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request, string $jenis)
    {
        $this->validasiJenis($jenis);

        $rules = [
            'nik'       => 'required|exists:pegawai,nik',
            'tanggal'   => 'required|date',
            'alasan'    => 'required|max:500',
        ];

        if ($jenis === 'sakit') {
            $rules['file_surat'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:2048';
        }

        if (in_array($jenis, ['terlambat', 'pulang_duluan'])) {
            $rules['jam_mulai']   = 'required|date_format:H:i';
            $rules['jam_selesai'] = 'required|date_format:H:i';
        }

        $validated = $request->validate($rules);

        // Cek duplikat per nik per tanggal per jenis
        $duplikat = PengajuanIjin::where('nik', $validated['nik'])
            ->where('tanggal', $validated['tanggal'])
            ->where('jenis', $jenis)
            ->whereNotIn('status', ['Ditolak Atasan', 'Ditolak HRD'])
            ->exists();

        if ($duplikat) {
            return back()->withErrors(['tanggal' => 'Sudah ada pengajuan ijin ' . PengajuanIjin::JENIS[$jenis] . ' untuk tanggal tersebut.'])->withInput();
        }

        $pegawai = Pegawai::where('nik', $validated['nik'])->first();

        // Hitung durasi
        $durasi = null;
        if (!empty($validated['jam_mulai']) && !empty($validated['jam_selesai'])) {
            $mulai  = \Carbon\Carbon::parse($validated['jam_mulai']);
            $selesai= \Carbon\Carbon::parse($validated['jam_selesai']);
            $durasi = abs($selesai->diffInMinutes($mulai));
        }

        // Upload surat sakit
        $filePath = null;
        if ($jenis === 'sakit' && $request->hasFile('file_surat')) {
            $filePath = $request->file('file_surat')->store(
                'ijin/surat/' . now()->format('Ym'), 'public'
            );
        }

        PengajuanIjin::create([
            'no_pengajuan'  => PengajuanIjin::generateNomor($jenis),
            'nik'           => $validated['nik'],
            'pegawai_id'    => $pegawai?->id,
            'tanggal'       => $validated['tanggal'],
            'jenis'         => $jenis,
            'jam_mulai'     => $validated['jam_mulai'] ?? null,
            'jam_selesai'   => $validated['jam_selesai'] ?? null,
            'durasi_menit'  => $durasi,
            'alasan'        => $validated['alasan'],
            'file_surat'    => $filePath,
            'status'        => 'Menunggu Atasan',
        ]);

        $saved = PengajuanIjin::where('no_pengajuan', PengajuanIjin::generateNomor($jenis))->latest()->first()
                 ?? PengajuanIjin::where('nik', $validated['nik'])->where('jenis', $jenis)->latest()->first();
        $link  = $saved ? route('ijin.show', [$jenis, $saved]) : '';
        HrNotification::kirimKeAtasan($validated['nik'], 'ijin_submitted',
            PengajuanIjin::JENIS[$jenis] . ' Baru',
            "Ada pengajuan " . strtolower(PengajuanIjin::JENIS[$jenis]) . " menunggu persetujuan Anda.", $link);

        return redirect()->route('ijin.index', $jenis)
            ->with('success', 'Pengajuan ' . PengajuanIjin::JENIS[$jenis] . ' berhasil diajukan.');
    }

    // ─── Show detail ───────────────────────────────────────────────────────────

    public function show(string $jenis, PengajuanIjin $ijin)
    {
        $this->validasiJenis($jenis);
        abort_if($ijin->jenis !== $jenis, 404);
        $ijin->load(['pegawai', 'approvedAtasanBy', 'approvedHrdBy']);
        $labelJenis = PengajuanIjin::JENIS[$jenis];
        return view('ijin.show', compact('ijin', 'jenis', 'labelJenis'));
    }

    // ─── Approve Atasan ────────────────────────────────────────────────────────

    public function approveAtasan(Request $request, PengajuanIjin $ijin)
    {
        abort_unless($ijin->bisaApproveAtasan(), 403, 'Tidak berhak atau status tidak sesuai.');

        $ijin->update([
            'status'              => 'Menunggu HRD',
            'catatan_atasan'      => $request->catatan_atasan,
            'approved_atasan_by'  => auth()->id(),
            'approved_atasan_at'  => now(),
        ]);

        $link = route('ijin.show', [$ijin->jenis, $ijin]);
        HrNotification::kirimKePegawai($ijin->nik, 'ijin_approved_atasan',
            $ijin->label_jenis . ' Disetujui Atasan', "Pengajuan {$ijin->no_pengajuan} disetujui, menunggu HRD.", $link);
        HrNotification::kirimKeHrd('ijin_submitted',
            $ijin->label_jenis . ' Perlu Disetujui', "Pengajuan {$ijin->no_pengajuan} menunggu persetujuan HRD.", $link);

        return back()->with('success', 'Ijin disetujui atasan, menunggu HRD.');
    }

    // ─── Tolak Atasan ──────────────────────────────────────────────────────────

    public function tolakAtasan(Request $request, PengajuanIjin $ijin)
    {
        abort_unless($ijin->bisaApproveAtasan(), 403);

        $ijin->update([
            'status'             => 'Ditolak Atasan',
            'catatan_atasan'     => $request->catatan_atasan,
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan ijin ditolak.');
    }

    // ─── Approve HRD ───────────────────────────────────────────────────────────

    public function approveHrd(Request $request, PengajuanIjin $ijin)
    {
        abort_unless($ijin->bisaApproveHrd(), 403);

        $ijin->update([
            'status'          => 'Disetujui',
            'catatan_hrd'     => $request->catatan_hrd,
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
        ]);

        HrNotification::kirimKePegawai($ijin->nik, 'ijin_approved',
            $ijin->label_jenis . ' Disetujui', "Pengajuan {$ijin->no_pengajuan} telah disetujui HRD.",
            route('ijin.show', [$ijin->jenis, $ijin]));

        return back()->with('success', 'Ijin ' . $ijin->label_jenis . ' telah disetujui.');
    }

    // ─── Tolak HRD ─────────────────────────────────────────────────────────────

    public function tolakHrd(Request $request, PengajuanIjin $ijin)
    {
        abort_unless($ijin->bisaApproveHrd(), 403);

        $ijin->update([
            'status'          => 'Ditolak HRD',
            'catatan_hrd'     => $request->catatan_hrd,
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan ijin ditolak.');
    }
}
