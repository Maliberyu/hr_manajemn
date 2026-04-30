<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\RekapAbsensi;
use App\Models\JadwalPegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:absensi.view')->only(['index', 'show', 'rekap']);
        $this->middleware('permission:absensi.input')->only(['create', 'store', 'checkIn', 'checkOut']);
        $this->middleware('permission:absensi.edit')->only(['edit', 'update']);
    }

    // ─── Dashboard absensi hari ini ───────────────────────────────────────────

    public function index(Request $request)
    {
        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : today();

        $absensi = Absensi::with('pegawai.departemenRef')
            ->whereDate('tanggal', $tanggal)
            ->when($request->departemen, fn($q, $d) =>
                $q->whereHas('pegawai', fn($p) => $p->where('departemen', $d)))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('jam_masuk')
            ->paginate(25)->withQueryString();

        // Ringkasan harian
        $ringkasan = Absensi::whereDate('tanggal', $tanggal)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalPegawaiAktif = Pegawai::aktif()->count();

        return view('absensi.index', compact('absensi', 'tanggal', 'ringkasan', 'totalPegawaiAktif'));
    }

    // ─── Form input manual (oleh HR) ─────────────────────────────────────────

    public function create()
    {
        $pegawai = Pegawai::aktif()->orderBy('nama')->get(['id', 'nama', 'nik', 'jbtn']);
        return view('absensi.create', compact('pegawai'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:pegawai,id',
            'tanggal'    => 'required|date|before_or_equal:today',
            'jam_masuk'  => 'required|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i|after:jam_masuk',
            'status'     => 'required|in:' . implode(',', Absensi::STATUS),
            'keterangan' => 'nullable|max:255',
        ]);

        // Cek duplikat
        $sudahAda = Absensi::where('pegawai_id', $validated['pegawai_id'])
                           ->whereDate('tanggal', $validated['tanggal'])
                           ->exists();

        if ($sudahAda) {
            return back()->withErrors(['tanggal' => 'Absensi pegawai ini sudah ada untuk tanggal tersebut.']);
        }

        $pegawai = Pegawai::find($validated['pegawai_id']);
        $jadwal  = JadwalPegawai::where('id', $validated['pegawai_id'])
                                ->where('tahun', Carbon::parse($validated['tanggal'])->year)
                                ->where('bulan', Carbon::parse($validated['tanggal'])->month)
                                ->first();

        $terlambat = $this->hitungKeterlambatan(
            $validated['jam_masuk'],
            $validated['tanggal'],
            $jadwal
        );

        Absensi::create([
            ...$validated,
            'jam_masuk'       => $validated['tanggal'] . ' ' . $validated['jam_masuk'],
            'jam_keluar'      => $validated['jam_keluar']
                                    ? $validated['tanggal'] . ' ' . $validated['jam_keluar']
                                    : null,
            'terlambat_menit' => $terlambat,
            'metode'          => 'manual',
            'diinput_oleh'    => auth()->id(),
        ]);

        return redirect()->route('absensi.index')
            ->with('success', "Absensi {$pegawai->nama} berhasil dicatat.");
    }

    // ─── Check-in ESS (karyawan mandiri) ─────────────────────────────────────

    public function checkIn(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        abort_unless($pegawai, 403, 'Akun tidak terhubung ke data pegawai.');

        // Cek sudah check-in hari ini
        $sudah = Absensi::where('pegawai_id', $pegawai->id)
                        ->whereDate('tanggal', today())
                        ->exists();

        if ($sudah) {
            return response()->json(['message' => 'Anda sudah check-in hari ini.'], 422);
        }

        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $jamMasuk = now();
        $jadwal   = JadwalPegawai::where('id', $pegawai->id)
                                 ->where('tahun', now()->year)
                                 ->where('bulan', now()->month)
                                 ->first();

        $terlambat = $this->hitungKeterlambatan(
            $jamMasuk->format('H:i'),
            today()->toDateString(),
            $jadwal
        );

        $absensi = Absensi::create([
            'pegawai_id'      => $pegawai->id,
            'tanggal'         => today(),
            'jam_masuk'       => $jamMasuk,
            'status'          => 'hadir',
            'terlambat_menit' => $terlambat,
            'metode'          => 'mobile',
            'lat_masuk'       => $request->lat,
            'lng_masuk'       => $request->lng,
            'diinput_oleh'    => auth()->id(),
        ]);

        return response()->json([
            'message'         => 'Check-in berhasil.',
            'jam_masuk'       => $jamMasuk->format('H:i'),
            'terlambat_menit' => $terlambat,
        ]);
    }

    // ─── Check-out ESS ───────────────────────────────────────────────────────

    public function checkOut(Request $request)
    {
        $pegawai = auth()->user()->pegawai;
        abort_unless($pegawai, 403);

        $absensi = Absensi::where('pegawai_id', $pegawai->id)
                          ->whereDate('tanggal', today())
                          ->whereNull('jam_keluar')
                          ->firstOrFail();

        $request->validate([
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $absensi->update([
            'jam_keluar' => now(),
            'lat_keluar' => $request->lat,
            'lng_keluar' => $request->lng,
        ]);

        return response()->json([
            'message'    => 'Check-out berhasil.',
            'jam_keluar' => now()->format('H:i'),
            'durasi'     => $absensi->fresh()->durasi_kerja,
        ]);
    }

    // ─── Detail absensi satu pegawai ─────────────────────────────────────────

    public function show(Request $request, Pegawai $karyawan)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $absensi = Absensi::where('pegawai_id', $karyawan->id)
            ->bulan($tahun, $bulan)
            ->orderBy('tanggal')
            ->get();

        $rekap = RekapAbsensi::where('pegawai_id', $karyawan->id)
            ->periode($tahun, $bulan)
            ->first();

        return view('absensi.show', compact('karyawan', 'absensi', 'rekap', 'bulan', 'tahun'));
    }

    // ─── Edit & Update (koreksi manual) ──────────────────────────────────────

    public function edit(Absensi $absensi)
    {
        $absensi->load('pegawai');
        return view('absensi.edit', compact('absensi'));
    }

    public function update(Request $request, Absensi $absensi)
    {
        $validated = $request->validate([
            'jam_masuk'  => 'required|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i|after:jam_masuk',
            'status'     => 'required|in:' . implode(',', Absensi::STATUS),
            'keterangan' => 'nullable|max:255',
        ]);

        $tanggal = $absensi->tanggal->format('Y-m-d');

        $absensi->update([
            'jam_masuk'  => $tanggal . ' ' . $validated['jam_masuk'],
            'jam_keluar' => $validated['jam_keluar'] ? $tanggal . ' ' . $validated['jam_keluar'] : null,
            'status'     => $validated['status'],
            'keterangan' => $validated['keterangan'],
        ]);

        return redirect()->route('absensi.index')
            ->with('success', 'Data absensi berhasil dikoreksi.');
    }

    // ─── Rekap bulanan semua pegawai ─────────────────────────────────────────

    public function rekap(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $rekap = RekapAbsensi::with('pegawai.departemenRef')
            ->periode($tahun, $bulan)
            ->when($request->departemen, fn($q, $d) =>
                $q->whereHas('pegawai', fn($p) => $p->where('departemen', $d)))
            ->orderByDesc('total_terlambat')
            ->paginate(30)->withQueryString();

        return view('absensi.rekap', compact('rekap', 'bulan', 'tahun'));
    }

    // ─── Generate rekap (jalankan tiap akhir bulan / via scheduler) ──────────

    public function generateRekap(Request $request)
    {
        $this->middleware('permission:absensi.rekap');

        $bulan = $request->bulan ?? now()->subMonth()->month;
        $tahun = $request->tahun ?? now()->subMonth()->year;

        $pegawaiList = Pegawai::aktif()->get();

        foreach ($pegawaiList as $pegawai) {
            $rows = Absensi::where('pegawai_id', $pegawai->id)
                           ->bulan($tahun, $bulan)
                           ->get();

            RekapAbsensi::updateOrCreate(
                ['pegawai_id' => $pegawai->id, 'tahun' => $tahun, 'bulan' => $bulan],
                [
                    'total_hadir'           => $rows->where('status', 'hadir')->count(),
                    'total_izin'            => $rows->where('status', 'izin')->count(),
                    'total_sakit'           => $rows->where('status', 'sakit')->count(),
                    'total_alfa'            => $rows->where('status', 'alfa')->count(),
                    'total_cuti'            => $rows->where('status', 'cuti')->count(),
                    'total_terlambat'       => $rows->where('terlambat_menit', '>', 0)->count(),
                    'total_menit_terlambat' => $rows->sum('terlambat_menit'),
                    'wajib_masuk'           => $pegawai->wajibmasuk ?? 25,
                ]
            );
        }

        return back()->with('success', "Rekap bulan {$bulan}/{$tahun} berhasil digenerate.");
    }

    // ─── Export Excel ─────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $this->middleware('permission:absensi.export');

        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        // Export class akan dibuat di app/Exports/AbsensiExport.php
        return Excel::download(
            new \App\Exports\AbsensiExport($bulan, $tahun),
            "rekap-absensi-{$bulan}-{$tahun}.xlsx"
        );
    }

    // ─── Private: hitung keterlambatan ───────────────────────────────────────

    private function hitungKeterlambatan(string $jamMasuk, string $tanggal, ?JadwalPegawai $jadwal): int
    {
        // Ambil shift hari ini
        $hariKe   = (int) Carbon::parse($tanggal)->format('j');
        $namaShift = $jadwal?->getHari($hariKe) ?? '';

        // Jam mulai per shift (bisa dikonfigurasi)
        $batasShift = [
            'Pagi'   => '07:00',
            'Siang'  => '14:00',
            'Malam'  => '21:00',
            default  => '07:00',
        ];

        $batas = $batasShift[$namaShift] ?? $batasShift['Pagi'];

        $masuk     = Carbon::parse($tanggal . ' ' . $jamMasuk);
        $batasWaktu= Carbon::parse($tanggal . ' ' . $batas);

        // Toleransi 10 menit
        $selisih = $masuk->diffInMinutes($batasWaktu, false) * -1;

        return max(0, $selisih - 10);
    }
}
