<?php

namespace App\Http\Controllers\Ijin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIjin;
use App\Models\Pegawai;
use App\Models\AtasanPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IjinController extends Controller
{
    private function validasiJenis(string $jenis): void
    {
        abort_unless(array_key_exists($jenis, PengajuanIjin::JENIS), 404);
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
