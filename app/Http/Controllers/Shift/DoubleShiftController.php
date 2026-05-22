<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\AtasanPegawai;
use App\Models\DoubleShift;
use App\Models\HrNotification;
use App\Models\JadwalRealisasi;
use App\Models\Lembur;
use App\Models\Pegawai;
use App\Models\ShiftMaster;
use App\Models\ShiftSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoubleShiftController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = DoubleShift::with(['pegawai','shiftPertama','shiftKedua'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->bulan,  fn($q, $b) => $q->whereMonth('tanggal', $b))
            ->when($request->tahun,  fn($q, $t) => $q->whereYear('tanggal', $t))
            ->orderByDesc('tanggal')->orderByDesc('id');

        if ($user->hasRole('karyawan')) {
            $query->where('pegawai_id', $user->pegawai?->id ?? 0);
        } elseif ($user->hasRole('atasan')) {
            $nikBawahan = AtasanPegawai::nikBawahan($user->id);
            $idBawahan  = Pegawai::whereIn('nik', $nikBawahan)->pluck('id');
            $idSendiri  = $user->pegawai?->id ?? 0;
            $query->whereIn('pegawai_id', $idBawahan->push($idSendiri)->unique());
        }

        $list     = $query->paginate(20)->withQueryString();
        $menunggu = DoubleShift::where('status', 'menunggu_atasan')->count();

        return view('shift.double.index', compact('list', 'menunggu'));
    }

    public function create()
    {
        $user      = auth()->user();
        $shiftList = ShiftMaster::aktif()->get();
        $setting   = ShiftSetting::get();

        $pegawai = $user->hasRole(['karyawan','atasan'])
            ? collect([$user->pegawai])->filter()
            : Pegawai::aktif()->orderBy('nama')->get(['id','nama','nik','jbtn']);

        return view('shift.double.create', compact('shiftList', 'pegawai', 'setting'));
    }

    public function store(Request $request)
    {
        $user    = auth()->user();
        $setting = ShiftSetting::get();

        $validated = $request->validate([
            'pegawai_id'        => 'required|exists:pegawai,id',
            'tanggal'           => 'required|date|after_or_equal:today',
            'shift_pertama_kode'=> 'required|exists:hr_shift_master,kode',
            'shift_kedua_kode'  => 'required|exists:hr_shift_master,kode|different:shift_pertama_kode',
            'alasan'            => 'required|string|max:500',
        ]);

        $ds = DoubleShift::create([
            ...$validated,
            'no_pengajuan' => DoubleShift::generateNomor(),
            'status'       => $setting->wajib_approval_double_shift ? 'menunggu_atasan' : 'disetujui',
            'dibuat_oleh'  => $user->id,
        ]);

        $pegawai = Pegawai::find($validated['pegawai_id']);

        if ($setting->wajib_approval_double_shift) {
            HrNotification::kirimKeAtasan($pegawai->nik, 'double_shift',
                'Pengajuan Double Shift',
                "{$pegawai->nama} mengajukan double shift pada " . Carbon::parse($validated['tanggal'])->translatedFormat('d F Y') . ".",
                route('double-shift.show', $ds)
            );
        } else {
            // Auto-approve: langsung buat lembur
            $this->buatLembur($ds);
        }

        return redirect()->route('double-shift.index')
            ->with('success', "Pengajuan {$ds->no_pengajuan} berhasil diajukan.");
    }

    public function show(DoubleShift $doubleShift)
    {
        $user = auth()->user();
        if ($user->hasRole('karyawan')) {
            abort_unless($doubleShift->pegawai_id === $user->pegawai?->id, 403);
        }
        $doubleShift->load(['pegawai','shiftPertama','shiftKedua','approvedBy','lembur']);
        return view('shift.double.show', compact('doubleShift'));
    }

    public function approveAtasan(Request $request, DoubleShift $doubleShift)
    {
        abort_unless($doubleShift->bisaApprove(), 403);

        $doubleShift->update([
            'status'          => 'disetujui',
            'catatan_atasan'  => $request->catatan_atasan,
            'approved_by'     => auth()->id(),
            'approved_at'     => now(),
        ]);

        $this->buatLembur($doubleShift);

        return back()->with('success', 'Double shift disetujui. Lembur otomatis dibuat.');
    }

    public function tolakAtasan(Request $request, DoubleShift $doubleShift)
    {
        abort_unless($doubleShift->bisaApprove(), 403);
        $request->validate(['catatan_atasan' => 'required|max:300']);

        $doubleShift->update([
            'status'         => 'ditolak',
            'catatan_atasan' => $request->catatan_atasan,
            'approved_by'    => auth()->id(),
            'approved_at'    => now(),
        ]);

        return back()->with('success', 'Double shift ditolak.');
    }

    // ── Buat lembur otomatis dari double shift ────────────────────────────────
    private function buatLembur(DoubleShift $ds): void
    {
        $shiftKedua = ShiftMaster::cariKode($ds->shift_kedua_kode);
        if (!$shiftKedua) return;

        $adaAtasan = AtasanPegawai::where('nik', $ds->pegawai?->nik)->exists();

        $lembur = Lembur::create([
            'pegawai_id'  => $ds->pegawai_id,
            'tanggal'     => $ds->tanggal,
            'jam_mulai'   => $shiftKedua->jam_mulai,
            'jam_selesai' => $shiftKedua->jam_selesai,
            'durasi_jam'  => $shiftKedua->durasi_jam,
            'jenis'       => 'HB',
            'keterangan'  => "Double shift ({$shiftKedua->nama}) - {$ds->no_pengajuan}",
            'nominal'     => null,
            'status'      => $adaAtasan ? 'Menunggu Atasan' : 'Menunggu HRD',
        ]);

        $ds->update(['lembur_id' => $lembur->id]);

        // Update jadwal realisasi tambah shift kedua
        JadwalRealisasi::catat(
            $ds->pegawai_id,
            $ds->tanggal->format('Y-m-d'),
            $ds->shift_kedua_kode,
            'double_shift',
            ['double_shift_id' => $ds->id, 'catatan' => 'Double shift']
        );
    }
}
