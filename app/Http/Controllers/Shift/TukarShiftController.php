<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\AtasanPegawai;
use App\Models\HrNotification;
use App\Models\JadwalRealisasi;
use App\Models\JadwalPegawai;
use App\Models\Pegawai;
use App\Models\ShiftMaster;
use App\Models\ShiftSetting;
use App\Models\TukarShift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TukarShiftController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = TukarShift::with(['pemohon', 'rekan', 'shiftPemohon', 'shiftRekan'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->bulan,  fn($q, $b) => $q->whereMonth('tgl_shift_pemohon', $b))
            ->when($request->tahun,  fn($q, $t) => $q->whereYear('tgl_shift_pemohon', $t))
            ->orderByDesc('created_at');

        if ($user->hasRole('karyawan')) {
            $query->where(fn($q) => $q->where('pemohon_id', $user->id)->orWhere('rekan_id', $user->id));
        } elseif ($user->hasRole('atasan')) {
            $nikBawahan = AtasanPegawai::nikBawahan($user->id);
            // User.nik = nik, jadi bisa filter langsung
            $idBawahan  = User::whereIn('nik', $nikBawahan)->pluck('id');
            $query->where(fn($q) => $q
                ->where('pemohon_id', $user->id)
                ->orWhere('rekan_id', $user->id)
                ->orWhereIn('pemohon_id', $idBawahan)
            );
        }

        $list      = $query->paginate(20)->withQueryString();
        $shiftList = ShiftMaster::aktif()->get();
        $menunggu  = TukarShift::where('rekan_id', $user->id)->where('status','menunggu_rekan')->count()
                   + ($user->hasRole(['hrd','admin'])
                       ? TukarShift::where('status','menunggu_atasan')->count()
                       : 0);

        return view('shift.tukar.index', compact('list', 'shiftList', 'menunggu'));
    }

    public function create()
    {
        $user      = auth()->user();
        $shiftList = ShiftMaster::aktif()->get();

        $pegawaiList = Pegawai::aktif()
            ->where('id', '!=', $user->pegawai?->id ?? 0)
            ->orderBy('nama')
            ->get(['id','nama','nik','jbtn']);

        return view('shift.tukar.create', compact('shiftList', 'pegawaiList'));
    }

    public function store(Request $request)
    {
        $user    = auth()->user();
        $setting = ShiftSetting::get();

        $validated = $request->validate([
            'rekan_id'           => 'required|exists:users_hr,id',
            'tgl_shift_pemohon'  => 'required|date|after_or_equal:today',
            'tgl_shift_rekan'    => 'required|date',
            'shift_pemohon_kode' => 'required|exists:hr_shift_master,kode',
            'shift_rekan_kode'   => 'required|exists:hr_shift_master,kode',
            'alasan'             => 'required|string|max:500',
        ]);

        // Cek batas per bulan
        $bulan  = Carbon::parse($validated['tgl_shift_pemohon'])->month;
        $tahun  = Carbon::parse($validated['tgl_shift_pemohon'])->year;
        $count  = TukarShift::where('pemohon_id', $user->id)
            ->whereYear('tgl_shift_pemohon', $tahun)
            ->whereMonth('tgl_shift_pemohon', $bulan)
            ->whereNotIn('status', ['ditolak_rekan','ditolak_atasan'])
            ->count();

        if ($count >= $setting->max_tukar_shift_per_bulan) {
            return back()->withErrors(['rekan_id' =>
                "Maksimal {$setting->max_tukar_shift_per_bulan}x tukar shift per bulan. Bulan ini sudah {$count}x."])
                ->withInput();
        }

        $ts = TukarShift::create([
            ...$validated,
            'no_pengajuan' => TukarShift::generateNomor(),
            'pemohon_id'   => $user->id,
            'status'       => 'menunggu_rekan',
            'dibuat_oleh'  => $user->id,
        ]);

        // Notif ke rekan
        $rekan = User::find($validated['rekan_id']);
        HrNotification::create([
            'user_id' => $rekan->id,
            'type'    => 'tukar_shift',
            'title'   => 'Permintaan Tukar Shift',
            'message' => "{$user->nama} meminta tukar shift pada " . Carbon::parse($validated['tgl_shift_pemohon'])->translatedFormat('d F Y') . ".",
            'link'    => route('tukar-shift.show', $ts),
        ]);

        return redirect()->route('tukar-shift.index')
            ->with('success', "Pengajuan {$ts->no_pengajuan} berhasil dikirim ke rekan.");
    }

    public function show(TukarShift $tukarShift)
    {
        $user = auth()->user();
        // Karyawan hanya lihat yang terkait dirinya
        if ($user->hasRole('karyawan')) {
            abort_unless(
                $tukarShift->pemohon_id === $user->id || $tukarShift->rekan_id === $user->id,
                403
            );
        }
        $tukarShift->load(['pemohon','rekan','shiftPemohon','shiftRekan','approvedRekanBy','approvedAtasanBy']);
        return view('shift.tukar.show', compact('tukarShift'));
    }

    // ── Rekan menyetujui ──────────────────────────────────────────────────────
    public function approveRekan(Request $request, TukarShift $tukarShift)
    {
        abort_unless($tukarShift->bisaApproveRekan(), 403);

        $tukarShift->update([
            'status'            => 'menunggu_atasan',
            'catatan_rekan'     => $request->catatan_rekan,
            'approved_rekan_by' => auth()->id(),
            'approved_rekan_at' => now(),
        ]);

        // Notif ke atasan pemohon
        $pemohon   = $tukarShift->pemohon;
        $nikPemohon = $pemohon?->pegawai?->nik;
        if ($nikPemohon) {
            HrNotification::kirimKeAtasan($nikPemohon, 'tukar_shift',
                'Tukar Shift Menunggu Persetujuan',
                "{$tukarShift->rekan?->nama} menyetujui tukar shift dengan {$pemohon->nama}. Perlu persetujuan Anda.",
                route('tukar-shift.show', $tukarShift)
            );
        }

        return back()->with('success', 'Anda menyetujui tukar shift. Menunggu persetujuan atasan.');
    }

    // ── Rekan menolak ─────────────────────────────────────────────────────────
    public function tolakRekan(Request $request, TukarShift $tukarShift)
    {
        abort_unless($tukarShift->bisaApproveRekan(), 403);
        $request->validate(['catatan_rekan' => 'required|max:300']);

        $tukarShift->update([
            'status'            => 'ditolak_rekan',
            'catatan_rekan'     => $request->catatan_rekan,
            'approved_rekan_by' => auth()->id(),
            'approved_rekan_at' => now(),
        ]);

        return back()->with('success', 'Tukar shift ditolak.');
    }

    // ── Atasan menyetujui → update jadwal realisasi ───────────────────────────
    public function approveAtasan(Request $request, TukarShift $tukarShift)
    {
        abort_unless($tukarShift->bisaApproveAtasan(), 403);

        $tukarShift->update([
            'status'             => 'disetujui',
            'catatan_atasan'     => $request->catatan_atasan,
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
        ]);

        // Update jadwal realisasi untuk kedua pihak
        $pemohonPegawai = $tukarShift->pemohon?->pegawai;
        $rekanPegawai   = $tukarShift->rekan?->pegawai;

        if ($pemohonPegawai) {
            JadwalRealisasi::catat(
                $pemohonPegawai->id,
                $tukarShift->tgl_shift_pemohon->format('Y-m-d'),
                $tukarShift->shift_rekan_kode,
                'tukar_shift',
                ['tukar_shift_id' => $tukarShift->id]
            );
        }
        if ($rekanPegawai) {
            JadwalRealisasi::catat(
                $rekanPegawai->id,
                $tukarShift->tgl_shift_rekan->format('Y-m-d'),
                $tukarShift->shift_pemohon_kode,
                'tukar_shift',
                ['tukar_shift_id' => $tukarShift->id]
            );
        }

        return back()->with('success', 'Tukar shift disetujui. Jadwal realisasi diperbarui.');
    }

    // ── Atasan menolak ────────────────────────────────────────────────────────
    public function tolakAtasan(Request $request, TukarShift $tukarShift)
    {
        abort_unless($tukarShift->bisaApproveAtasan(), 403);
        $request->validate(['catatan_atasan' => 'required|max:300']);

        $tukarShift->update([
            'status'             => 'ditolak_atasan',
            'catatan_atasan'     => $request->catatan_atasan,
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
        ]);

        return back()->with('success', 'Tukar shift ditolak.');
    }
}
