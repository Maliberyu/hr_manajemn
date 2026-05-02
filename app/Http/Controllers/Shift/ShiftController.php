<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\JadwalPegawai;
use App\Models\Pegawai;
use App\Models\Departemen;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function __construct()
    {
    }

    // ─── Index: jadwal bulan ini per departemen ───────────────────────────────

    public function index(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        $depId = $request->departemen;

        $pegawai = Pegawai::aktif()
            ->when($depId, fn($q, $d) => $q->departemen($d))
            ->with(['jadwalBulanan' => fn($q) => $q->where('tahun', $tahun)->where('bulan', $bulan)])
            ->orderBy('nama')
            ->get();

        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');

        // Jumlah hari dalam bulan
        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;
        $hariPertama= Carbon::create($tahun, $bulan, 1)->dayOfWeek; // 0=Minggu

        return view('shift.index', compact(
            'pegawai', 'departemen', 'bulan', 'tahun', 'jumlahHari', 'hariPertama', 'depId'
        ));
    }

    // ─── Edit jadwal satu pegawai ─────────────────────────────────────────────

    public function edit(Request $request, Pegawai $karyawan)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $jadwal = JadwalPegawai::where('id', $karyawan->id)
                               ->where('tahun', $tahun)
                               ->where('bulan', $bulan)
                               ->first();

        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;

        return view('shift.edit', compact('karyawan', 'jadwal', 'bulan', 'tahun', 'jumlahHari'));
    }

    // ─── Simpan / update jadwal ───────────────────────────────────────────────

    public function update(Request $request, Pegawai $karyawan)
    {
        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;

        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;

        // Bangun array kolom h1..h31
        $shiftData = ['id' => $karyawan->id, 'tahun' => $tahun, 'bulan' => $bulan];
        for ($i = 1; $i <= 31; $i++) {
            $shiftData["h{$i}"] = $i <= $jumlahHari
                ? ($request->input("h{$i}", '') ?: '')
                : '';
        }

        JadwalPegawai::updateOrCreate(
            ['id' => $karyawan->id, 'tahun' => $tahun, 'bulan' => $bulan],
            $shiftData
        );

        return redirect()->route('shift.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', "Jadwal {$karyawan->nama} bulan {$bulan}/{$tahun} disimpan.");
    }

    // ─── Input massal: semua pegawai satu departemen ─────────────────────────

    public function inputMassal(Request $request)
    {

        $bulan  = (int) $request->bulan;
        $tahun  = (int) $request->tahun;
        $shifts = $request->input('shifts', []); // ['pegawai_id' => ['h1'=>'Pagi', ...]]

        foreach ($shifts as $pegawaiId => $hariShift) {
            $data = ['id' => $pegawaiId, 'tahun' => $tahun, 'bulan' => $bulan];
            for ($i = 1; $i <= 31; $i++) {
                $data["h{$i}"] = $hariShift["h{$i}"] ?? '';
            }

            JadwalPegawai::updateOrCreate(
                ['id' => $pegawaiId, 'tahun' => $tahun, 'bulan' => $bulan],
                $data
            );
        }

        return back()->with('success', 'Jadwal massal berhasil disimpan.');
    }

    // ─── Copy jadwal dari bulan lalu ─────────────────────────────────────────

    public function copyBulanLalu(Request $request)
    {
        $request->validate([
            'bulan_tujuan' => 'required|integer|between:1,12',
            'tahun_tujuan' => 'required|integer|min:2020',
            'departemen'   => 'nullable|exists:departemen,dep_id',
        ]);

        $bulanAsal  = $request->bulan_tujuan == 1 ? 12 : $request->bulan_tujuan - 1;
        $tahunAsal  = $request->bulan_tujuan == 1 ? $request->tahun_tujuan - 1 : $request->tahun_tujuan;

        $query = JadwalPegawai::where('tahun', $tahunAsal)->where('bulan', $bulanAsal);

        if ($request->departemen) {
            $pegawaiIds = Pegawai::departemen($request->departemen)->pluck('id');
            $query->whereIn('id', $pegawaiIds);
        }

        $jadwalLalu = $query->get();

        foreach ($jadwalLalu as $j) {
            JadwalPegawai::updateOrCreate(
                ['id' => $j->id, 'tahun' => $request->tahun_tujuan, 'bulan' => $request->bulan_tujuan],
                collect($j->toArray())->except(['tahun', 'bulan'])->merge([
                    'tahun' => $request->tahun_tujuan,
                    'bulan' => $request->bulan_tujuan,
                ])->toArray()
            );
        }

        return back()->with('success', "Jadwal berhasil dicopy dari {$bulanAsal}/{$tahunAsal}. ({$jadwalLalu->count()} pegawai)");
    }

    // ─── Rekap shift satu pegawai ─────────────────────────────────────────────

    public function show(Request $request, Pegawai $karyawan)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $jadwal = JadwalPegawai::where('id', $karyawan->id)
                               ->where('tahun', $tahun)
                               ->where('bulan', $bulan)
                               ->first();

        $jumlahHari = Carbon::create($tahun, $bulan, 1)->daysInMonth;

        return view('shift.show', compact('karyawan', 'jadwal', 'bulan', 'tahun', 'jumlahHari'));
    }
}
