<?php

namespace App\Http\Controllers\IjinKhusus;

use App\Http\Controllers\Controller;
use App\Models\AtasanPegawai;
use App\Models\HrNotification;
use App\Models\IjinKhusus;
use App\Models\JenisIjinKhusus;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IjinKhususController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = IjinKhusus::with(['jenis', 'pegawai'])
            ->when($request->jenis_id, fn($q, $j) => $q->where('jenis_ijin_id', $j))
            ->when($request->status,   fn($q, $s) => $q->where('status', $s))
            ->when($request->bulan,    fn($q, $b) => $q->whereMonth('tanggal_mulai', $b))
            ->when($request->tahun,    fn($q, $t) => $q->whereYear('tanggal_mulai', $t))
            ->orderByDesc('tanggal_mulai')->orderByDesc('id');

        if ($user->hasRole('karyawan')) {
            $query->where('nik', $user->pegawai?->nik ?? '');
        } elseif ($user->hasRole('atasan')) {
            $nikBawahan = AtasanPegawai::nikBawahan($user->id);
            $nikSendiri = $user->pegawai?->nik ?? '';
            $query->whereIn('nik', array_filter(array_merge([$nikSendiri], $nikBawahan)));
        }

        $list      = $query->paginate(20)->withQueryString();
        $jenisList = JenisIjinKhusus::aktif()->get();
        $isHrd     = $user->hasRole(['hrd', 'admin']);

        return view('ijin-khusus.index', compact('list', 'jenisList', 'isHrd'));
    }

    public function create()
    {
        $user      = auth()->user();
        $jenisList = JenisIjinKhusus::aktif()->get();
        $pegawai   = $user->hasRole(['karyawan', 'atasan'])
            ? collect([$user->pegawai])->filter()
            : Pegawai::aktif()->orderBy('nama')->get(['id','nama','nik','jbtn']);

        return view('ijin-khusus.create', compact('jenisList', 'pegawai'));
    }

    public function store(Request $request)
    {
        $jenis = JenisIjinKhusus::findOrFail($request->jenis_ijin_id);

        $rules = [
            'jenis_ijin_id'  => 'required|exists:hr_jenis_ijin_khusus,id',
            'nik'            => 'required|exists:pegawai,nik',
            'tanggal_mulai'  => 'required|date',
            'alasan'         => 'required|string|max:500',
        ];

        if ($jenis->max_hari) {
            $rules['tanggal_akhir'] = "nullable|date|after_or_equal:tanggal_mulai";
        }
        if ($jenis->butuh_waktu) {
            $rules['jam_mulai']   = 'required|date_format:H:i';
            $rules['jam_selesai'] = 'required|date_format:H:i|after:jam_mulai';
        }
        if ($jenis->wajib_lampiran) {
            $rules['file_lampiran'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:3072';
        }

        $validated = $request->validate($rules);

        // Hitung durasi
        $durasi_hari  = null;
        $durasi_menit = null;

        if ($jenis->butuh_waktu && $request->jam_mulai && $request->jam_selesai) {
            $durasi_menit = abs(\Carbon\Carbon::parse($request->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($request->jam_selesai)));
        } elseif ($request->tanggal_akhir) {
            $durasi_hari = \Carbon\Carbon::parse($request->tanggal_mulai)->diffInWeekdays(\Carbon\Carbon::parse($request->tanggal_akhir)) + 1;
        } else {
            $durasi_hari = 1;
        }

        // Max hari check
        if ($jenis->max_hari && $durasi_hari > $jenis->max_hari) {
            return back()->withErrors(['tanggal_akhir' => "Maksimal ijin {$jenis->nama} adalah {$jenis->max_hari} hari."])->withInput();
        }

        // Upload lampiran
        $filePath = null;
        if ($request->hasFile('file_lampiran')) {
            $filePath = $request->file('file_lampiran')->store('ijin-khusus/' . now()->format('Ym'), 'public');
        }

        $adaAtasan = AtasanPegawai::where('nik', $validated['nik'])->exists();

        $ijin = IjinKhusus::create([
            'no_pengajuan'  => IjinKhusus::generateNomor(),
            'nik'           => $validated['nik'],
            'pegawai_id'    => Pegawai::where('nik', $validated['nik'])->value('id'),
            'jenis_ijin_id' => $jenis->id,
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_akhir' => $request->tanggal_akhir ?? $validated['tanggal_mulai'],
            'jam_mulai'     => $request->jam_mulai,
            'jam_selesai'   => $request->jam_selesai,
            'durasi_hari'   => $durasi_hari,
            'durasi_menit'  => $durasi_menit,
            'alasan'        => $validated['alasan'],
            'file_lampiran' => $filePath,
            'status'        => $adaAtasan ? 'Menunggu Atasan' : 'Menunggu HRD',
            'dibuat_oleh'   => auth()->id(),
        ]);

        $link = route('ijin-khusus.show', $ijin);
        if ($adaAtasan) {
            HrNotification::kirimKeAtasan($validated['nik'], 'ijin_khusus_submitted',
                "Ijin Khusus: {$jenis->nama}", "Ada pengajuan ijin dari " . ($ijin->pegawai?->nama ?? '-'), $link);
        } else {
            HrNotification::kirimKeHrd('ijin_khusus_submitted', "Ijin Khusus: {$jenis->nama}",
                "Pengajuan ijin khusus menunggu persetujuan HRD.", $link);
        }

        return redirect()->route('ijin-khusus.index')
            ->with('success', "Pengajuan {$ijin->no_pengajuan} berhasil diajukan.");
    }

    public function show(IjinKhusus $ijinKhusus)
    {
        $user = auth()->user();
        // Karyawan hanya lihat milik sendiri
        if ($user->hasRole('karyawan') && $ijinKhusus->nik !== $user->pegawai?->nik) {
            abort(403);
        }
        $ijinKhusus->load(['jenis', 'pegawai', 'approvedAtasanBy', 'approvedHrdBy']);
        $isHrd = auth()->user()->hasRole(['hrd', 'admin']);
        return view('ijin-khusus.show', compact('ijinKhusus', 'isHrd'));
    }

    public function approveAtasan(Request $request, IjinKhusus $ijinKhusus)
    {
        abort_unless($ijinKhusus->bisaApproveAtasan(), 403);
        $ijinKhusus->update([
            'status'             => 'Menunggu HRD',
            'catatan_atasan'     => $request->catatan_atasan,
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
        ]);
        HrNotification::kirimKeHrd('ijin_khusus_submitted', 'Ijin Khusus Perlu Disetujui HRD',
            "Ijin {$ijinKhusus->jenis?->nama} dari {$ijinKhusus->pegawai?->nama} menunggu HRD.",
            route('ijin-khusus.show', $ijinKhusus));
        return back()->with('success', 'Ijin disetujui, menunggu HRD.');
    }

    public function tolakAtasan(Request $request, IjinKhusus $ijinKhusus)
    {
        abort_unless($ijinKhusus->bisaApproveAtasan(), 403);
        $request->validate(['catatan_atasan' => 'required|max:300']);
        $ijinKhusus->update([
            'status'             => 'Ditolak Atasan',
            'catatan_atasan'     => $request->catatan_atasan,
            'approved_atasan_by' => auth()->id(),
            'approved_atasan_at' => now(),
        ]);
        return back()->with('success', 'Ijin ditolak.');
    }

    public function approveHrd(Request $request, IjinKhusus $ijinKhusus)
    {
        abort_unless($ijinKhusus->bisaApproveHrd(), 403);
        $ijinKhusus->update([
            'status'          => 'Disetujui',
            'catatan_hrd'     => $request->catatan_hrd,
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
        ]);
        return back()->with('success', 'Ijin khusus disetujui.');
    }

    public function tolakHrd(Request $request, IjinKhusus $ijinKhusus)
    {
        abort_unless($ijinKhusus->bisaApproveHrd(), 403);
        $request->validate(['catatan_hrd' => 'required|max:300']);
        $ijinKhusus->update([
            'status'          => 'Ditolak HRD',
            'catatan_hrd'     => $request->catatan_hrd,
            'approved_hrd_by' => auth()->id(),
            'approved_hrd_at' => now(),
        ]);
        return back()->with('success', 'Ijin ditolak.');
    }

    public function downloadLampiran(IjinKhusus $ijinKhusus)
    {
        abort_unless($ijinKhusus->file_lampiran, 404);
        return Storage::disk('public')->download($ijinKhusus->file_lampiran);
    }
}
