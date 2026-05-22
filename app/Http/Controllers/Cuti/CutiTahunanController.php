<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\AtasanPegawai;
use App\Models\CutiLock;
use App\Models\CutiSetting;
use App\Models\CutiUnlockRequest;
use App\Models\Departemen;
use App\Models\HrNotification;
use App\Models\Pegawai;
use App\Models\PengajuanCuti;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CutiTahunanController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = PengajuanCuti::with(['pegawai'])
            ->where('urgensi', 'Tahunan')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->tahun,  fn($q, $t) => $q->whereYear('tanggal', $t))
            ->orderByDesc('tanggal')->orderByDesc('id');

        if ($user->hasRole('karyawan')) {
            $query->where('nik', $user->pegawai?->nik ?? '');
        } elseif ($user->hasRole('atasan')) {
            $nikBawahan = AtasanPegawai::nikBawahan($user->id);
            $nikSendiri = $user->pegawai?->nik ?? '';
            $query->whereIn('nik', array_filter(array_merge([$nikSendiri], $nikBawahan)));
        }

        $list       = $query->paginate(20)->withQueryString();
        $lock       = CutiLock::status();
        $setting    = CutiSetting::get();
        $minTanggal = CutiSetting::tanggalMinimal();
        $isHrd      = $user->hasRole(['hrd', 'admin']);

        $unlockReqSaya = !$isHrd
            ? CutiUnlockRequest::where('user_id', $user->id)->latest()->first()
            : null;

        return view('cuti.tahunan.index', compact('list', 'lock', 'setting', 'minTanggal', 'isHrd', 'unlockReqSaya'));
    }

    // ── Form pengajuan ────────────────────────────────────────────────────────

    public function create()
    {
        $user  = auth()->user();
        $lock  = CutiLock::status();
        $isHrd = $user->hasRole(['hrd', 'admin']);

        if ($lock->is_locked && !$isHrd) {
            if (!CutiUnlockRequest::isDisetujui($user->id)) {
                return redirect()->route('cuti.tahunan.index')
                    ->with('error', 'Fitur cuti tahunan sedang ditutup oleh HRD.');
            }
        }

        $setting      = CutiSetting::get();
        $hasHnBypass  = $isHrd || CutiUnlockRequest::isDisetujui($user->id);

        $pegawai = $user->hasRole(['karyawan', 'atasan'])
            ? collect([$user->pegawai])->filter()
            : Pegawai::aktif()->orderBy('nama')->get(['id','nama','nik','jbtn']);

        $pj = Pegawai::aktif()->orderBy('nama')->get(['id','nama','nik']);

        return view('cuti.tahunan.create', compact('hasHnBypass', 'pegawai', 'pj', 'setting'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $user    = auth()->user();
        $lock    = CutiLock::status();
        $setting = CutiSetting::get();

        $isHrd = $user->hasRole(['hrd', 'admin']);

        // Global lock check
        if ($lock->is_locked && !$isHrd) {
            if (!CutiUnlockRequest::isDisetujui($user->id)) {
                return back()->withErrors(['lock' => 'Fitur cuti tahunan sedang ditutup oleh HRD.'])->withInput();
            }
        }

        // Validasi dasar dulu — tanggal_awal boleh hari ini
        $validated = $request->validate([
            'nik'           => 'required|exists:pegawai,nik',
            'tanggal_awal'  => 'required|date|after_or_equal:today',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
            'alamat'        => 'required|max:255',
            'kepentingan'   => 'required|max:500',
            'nik_pj'        => 'nullable|exists:pegawai,nik',
        ], [
            'tanggal_awal.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
        ]);

        // H-N check — setelah validasi dasar lulus
        $hasHnBypass = $isHrd || CutiUnlockRequest::isDisetujui($user->id);
        $minTanggal  = CutiSetting::tanggalMinimal();

        if (!$hasHnBypass && Carbon::parse($validated['tanggal_awal'])->lt(Carbon::parse($minTanggal))) {
            return back()
                ->withInput()
                ->with('hn_blocked', true)
                ->with('hn_tanggal_awal', $validated['tanggal_awal'])
                ->with('hn_tanggal_akhir', $validated['tanggal_akhir'])
                ->withErrors(['tanggal_awal' =>
                    "Cuti harus diajukan minimal H-{$setting->min_hari_pengajuan} sebelum tanggal mulai " .
                    "(paling lambat " . Carbon::parse($validated['tanggal_awal'])->subDays($setting->min_hari_pengajuan)->translatedFormat('d F Y') . ")."
                ]);
        }

        $awal   = Carbon::parse($validated['tanggal_awal']);
        $akhir  = Carbon::parse($validated['tanggal_akhir']);
        $jumlah = $awal->diffInWeekdays($akhir) + 1;

        // Cek tumpang tindih
        $tumpang = PengajuanCuti::where('nik', $validated['nik'])
            ->where('urgensi', 'Tahunan')
            ->whereNotIn('status', ['Ditolak Atasan', 'Ditolak HRD'])
            ->where(fn($q) => $q
                ->whereBetween('tanggal_awal', [$validated['tanggal_awal'], $validated['tanggal_akhir']])
                ->orWhereBetween('tanggal_akhir', [$validated['tanggal_awal'], $validated['tanggal_akhir']])
            )->exists();

        if ($tumpang) {
            return back()->withErrors(['tanggal_awal' => 'Tanggal tumpang tindih dengan pengajuan yang sudah ada.'])->withInput();
        }

        $prefix = 'CT/' . now()->format('Ym') . '/';
        $last   = PengajuanCuti::where('no_pengajuan', 'like', $prefix . '%')->orderByDesc('no_pengajuan')->value('no_pengajuan');
        $urut   = $last ? ((int) substr($last, -3)) + 1 : 1;
        $no     = $prefix . str_pad($urut, 3, '0', STR_PAD_LEFT);

        $adaAtasan = AtasanPegawai::where('nik', $validated['nik'])->exists();

        $cuti = PengajuanCuti::create([
            ...$validated,
            'no_pengajuan' => $no,
            'tanggal'      => today(),
            'jumlah'       => $jumlah,
            'urgensi'      => 'Tahunan',
            'status'       => $adaAtasan ? 'Menunggu Atasan' : 'Menunggu HRD',
        ]);

        $link = route('cuti.show', $cuti);
        if ($adaAtasan) {
            HrNotification::kirimKeAtasan($validated['nik'], 'cuti_submitted',
                'Pengajuan Cuti Tahunan', "Ada pengajuan cuti tahunan dari " . ($cuti->pegawai?->nama ?? '-'), $link);
        } else {
            HrNotification::kirimKeHrd('cuti_submitted', 'Pengajuan Cuti Tahunan',
                "Pengajuan cuti tahunan menunggu persetujuan HRD.", $link);
        }

        return redirect()->route('cuti.tahunan.index')
            ->with('success', "Pengajuan {$no} berhasil diajukan ({$jumlah} hari kerja).");
    }

    // ── Request buka cuti (saat lock) ─────────────────────────────────────────

    public function requestBuka(Request $request)
    {
        $request->validate([
            'tgl_rencana_mulai' => 'required|date',
            'tgl_rencana_akhir' => 'required|date|after_or_equal:tgl_rencana_mulai',
            'alasan'            => 'required|string|max:500',
        ]);

        $no = CutiUnlockRequest::generateNomor();
        CutiUnlockRequest::create([
            'no_request'         => $no,
            'user_id'            => auth()->id(),
            'tgl_rencana_mulai'  => $request->tgl_rencana_mulai,
            'tgl_rencana_akhir'  => $request->tgl_rencana_akhir,
            'alasan'             => $request->alasan,
            'status'             => 'menunggu',
        ]);

        $nama = auth()->user()->nama ?? auth()->user()->name;
        HrNotification::kirimKeHrd('cuti_unlock_request', 'Permintaan Cuti Mendadak',
            "{$nama} mengajukan permintaan cuti mendadak ({$request->tgl_rencana_mulai} s/d {$request->tgl_rencana_akhir}).",
            route('cuti.lock.index'));

        return back()->with('success', 'Permintaan berhasil dikirim ke HRD. Tunggu konfirmasi.');
    }
}
