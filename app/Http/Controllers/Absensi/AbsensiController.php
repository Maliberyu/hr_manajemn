<?php

namespace App\Http\Controllers\Absensi;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\LokasiAbsensi;
use App\Models\Pegawai;
use App\Models\RekapAbsensi;
use App\Models\JadwalPegawai;
use App\Models\Departemen;
use App\Models\AtasanPegawai;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function __construct()
    {
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

        $sudah = Absensi::where('pegawai_id', $pegawai->id)
                        ->whereDate('tanggal', today())
                        ->exists();

        if ($sudah) {
            return response()->json(['message' => 'Anda sudah check-in hari ini.'], 422);
        }

        $request->validate([
            'lat'  => 'required|numeric',
            'lng'  => 'required|numeric',
            'foto' => 'required|string', // base64 image
        ]);

        // Validasi radius — cek apakah dalam jangkauan salah satu lokasi aktif
        $lokasiList    = LokasiAbsensi::aktif()->get();
        $lokasiValid   = false;
        $jarakTerdekat = PHP_FLOAT_MAX;
        $namaLokasi    = null;

        foreach ($lokasiList as $lok) {
            $jarak = LokasiAbsensi::hitungJarak($lok->lat, $lok->lng, $request->lat, $request->lng);
            if ($jarak < $jarakTerdekat) {
                $jarakTerdekat = $jarak;
                $namaLokasi    = $lok->nama;
            }
            if ($lok->dalamRadius($request->lat, $request->lng)) {
                $lokasiValid = true;
                $namaLokasi  = $lok->nama;
                break;
            }
        }

        if ($lokasiList->isNotEmpty() && !$lokasiValid) {
            return response()->json([
                'message' => "Anda berada di luar radius absensi ({$namaLokasi}). Jarak Anda: " . round($jarakTerdekat) . ' m.',
                'jarak'   => round($jarakTerdekat),
            ], 422);
        }

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

        // Simpan foto selfie check-in
        $fotoPath = $this->simpanFotoBase64(
            $request->foto,
            "absensi/foto/" . now()->format('Ym') . "/{$pegawai->id}_masuk_" . now()->format('His') . ".jpg"
        );

        Absensi::create([
            'pegawai_id'      => $pegawai->id,
            'tanggal'         => today(),
            'jam_masuk'       => $jamMasuk,
            'status'          => 'hadir',
            'terlambat_menit' => $terlambat,
            'metode'          => 'mobile',
            'lat_masuk'       => $request->lat,
            'lng_masuk'       => $request->lng,
            'foto_masuk'      => $fotoPath,
            'lokasi_valid'    => $lokasiValid,
            'diinput_oleh'    => auth()->id(),
        ]);

        return response()->json([
            'message'         => 'Check-in berhasil! ' . ($terlambat > 0 ? "Terlambat {$terlambat} menit." : 'Tepat waktu.'),
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
            'lat'  => 'required|numeric',
            'lng'  => 'required|numeric',
            'foto' => 'required|string',
        ]);

        // Validasi radius check-out
        $lokasiList  = LokasiAbsensi::aktif()->get();
        $lokasiValid = false;

        foreach ($lokasiList as $lok) {
            if ($lok->dalamRadius($request->lat, $request->lng)) {
                $lokasiValid = true;
                break;
            }
        }

        if ($lokasiList->isNotEmpty() && !$lokasiValid) {
            $jarak = $lokasiList->map(fn($l) =>
                LokasiAbsensi::hitungJarak($l->lat, $l->lng, $request->lat, $request->lng)
            )->min();

            return response()->json([
                'message' => 'Anda berada di luar radius absensi. Jarak Anda: ' . round($jarak) . ' m.',
                'jarak'   => round($jarak),
            ], 422);
        }

        $fotoPath = $this->simpanFotoBase64(
            $request->foto,
            "absensi/foto/" . now()->format('Ym') . "/{$pegawai->id}_keluar_" . now()->format('His') . ".jpg"
        );

        $absensi->update([
            'jam_keluar'  => now(),
            'lat_keluar'  => $request->lat,
            'lng_keluar'  => $request->lng,
            'foto_keluar' => $fotoPath,
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
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $nikBawahanAtasan = $request->atasan_id
            ? AtasanPegawai::nikBawahan((int) $request->atasan_id)
            : null;

        $rekap = RekapAbsensi::with('pegawai.departemenRef')
            ->periode($tahun, $bulan)
            ->when($request->departemen, fn($q, $d) =>
                $q->whereHas('pegawai', fn($p) => $p->where('departemen', $d)))
            ->when($request->bidang, fn($q, $b) =>
                $q->whereHas('pegawai', fn($p) => $p->where('bidang', $b)))
            ->when($nikBawahanAtasan, fn($q) =>
                $q->whereHas('pegawai', fn($p) => $p->whereIn('nik', $nikBawahanAtasan)))
            ->orderByDesc('total_terlambat')
            ->paginate(30)->withQueryString();

        $departemen = Departemen::orderBy('nama')->get(['dep_id', 'nama']);
        $bidangList  = Pegawai::aktif()->whereNotNull('bidang')->distinct()->orderBy('bidang')->pluck('bidang');
        $atasanList  = User::whereIn('role', ['atasan', 'hrd', 'admin'])
            ->where('status', 'aktif')->orderBy('nama')->get(['id', 'nama', 'jabatan']);

        return view('absensi.rekap', compact('rekap', 'bulan', 'tahun', 'departemen', 'bidangList', 'atasanList'));
    }

    // ─── Generate rekap (jalankan tiap akhir bulan / via scheduler) ──────────

    public function generateRekap(Request $request)
    {

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

        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        // Export class akan dibuat di app/Exports/AbsensiExport.php
        return Excel::download(
            new \App\Exports\AbsensiExport($bulan, $tahun),
            "rekap-absensi-{$bulan}-{$tahun}.xlsx"
        );
    }

    // ─── Private: hitung keterlambatan ───────────────────────────────────────

    private function simpanFotoBase64(string $base64, string $path): ?string
    {
        try {
            // Lepas prefix data:image/...;base64,
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);
            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

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
        ];

        $batas = $batasShift[$namaShift] ?? $batasShift['Pagi'];

        $masuk     = Carbon::parse($tanggal . ' ' . $jamMasuk);
        $batasWaktu= Carbon::parse($tanggal . ' ' . $batas);

        // Toleransi 10 menit
        $selisih = $masuk->diffInMinutes($batasWaktu, false) * -1;

        return max(0, $selisih - 10);
    }
}
