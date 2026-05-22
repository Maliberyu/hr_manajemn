<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\AtasanPegawai;
use App\Models\Departemen;
use App\Models\JadwalPegawai;
use App\Models\Lembur;
use App\Models\Pegawai;
use App\Models\ShiftMaster;
use App\Models\TukarShift;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalRealisasiController extends Controller
{
    public function index(Request $request)
    {
        $bulan  = (int) ($request->bulan ?? now()->month);
        $tahun  = (int) ($request->tahun ?? now()->year);
        $depId  = $request->departemen;
        $nikPeg = $request->pegawai;   // NIK, untuk mode per orang
        $mode   = $request->mode ?? 'departemen'; // 'departemen' | 'orang'
        $isPrint= $request->boolean('print');

        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');
        $jumlahHari = Carbon::create($tahun, $bulan)->daysInMonth;

        // ── Daftar pegawai sesuai filter ───────────────────────────────────────
        $user = auth()->user();
        $queryPeg = Pegawai::aktif()->when($depId, fn($q) => $q->departemen($depId));

        if ($user->hasRole('karyawan')) {
            $queryPeg->where('id', $user->pegawai?->id ?? 0);
            $mode = 'orang';
            $nikPeg = $user->pegawai?->nik;
        } elseif ($user->hasRole('atasan')) {
            $nikBawahan = AtasanPegawai::nikBawahan($user->id);
            $nikBawahan[] = $user->pegawai?->nik ?? '';
            $queryPeg->whereIn('nik', array_filter($nikBawahan));
        }

        $pegawaiList = $queryPeg->orderBy('nama')->get();

        // ── Pilih satu pegawai untuk mode per-orang ────────────────────────────
        $pegawaiDipilih = null;
        if ($mode === 'orang' && $nikPeg) {
            $pegawaiDipilih = $pegawaiList->firstWhere('nik', $nikPeg)
                             ?? $pegawaiList->first();
        } elseif ($mode === 'orang') {
            $pegawaiDipilih = $pegawaiList->first();
        }

        // ── Bangun data realisasi ──────────────────────────────────────────────
        $realisasi = $this->buildRealisasi(
            $mode === 'orang' && $pegawaiDipilih
                ? collect([$pegawaiDipilih])
                : $pegawaiList,
            $tahun, $bulan, $jumlahHari
        );

        if ($isPrint) {
            return view('shift.realisasi.print', compact(
                'realisasi', 'bulan', 'tahun', 'jumlahHari',
                'departemen', 'depId', 'mode', 'pegawaiDipilih'
            ));
        }

        return view('shift.realisasi.index', compact(
            'realisasi', 'bulan', 'tahun', 'jumlahHari',
            'departemen', 'depId', 'mode', 'pegawaiDipilih',
            'pegawaiList', 'nikPeg'
        ));
    }

    // ── Build realisasi per pegawai per hari ──────────────────────────────────
    private function buildRealisasi($pegawaiList, int $tahun, int $bulan, int $jumlahHari): array
    {
        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();

        // Preload semua absensi untuk pegawai di bulan ini
        $pegawaiIds = $pegawaiList->pluck('id')->toArray();

        $absensiMap = Absensi::whereIn('pegawai_id', $pegawaiIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get()
            ->groupBy(fn($a) => $a->pegawai_id . '_' . Carbon::parse($a->tanggal)->day);

        // Preload jadwal rencana
        $jadwalMap = JadwalPegawai::whereIn('id', $pegawaiIds)
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->get()
            ->keyBy('id');

        // Preload tukar shift yang disetujui di bulan ini
        $tukarMap = TukarShift::where('status', 'disetujui')
            ->where(fn($q) => $q
                ->whereYear('tgl_shift_pemohon', $tahun)->whereMonth('tgl_shift_pemohon', $bulan)
                ->orWhere(fn($q2) => $q2->whereYear('tgl_shift_rekan', $tahun)->whereMonth('tgl_shift_rekan', $bulan))
            )
            ->get();

        // Preload lembur di bulan ini
        $lemburMap = Lembur::whereIn('pegawai_id', $pegawaiIds)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get()
            ->groupBy(fn($l) => $l->pegawai_id . '_' . Carbon::parse($l->tanggal)->day);

        // Preload shift master
        $shiftMasterAll = ShiftMaster::all()->keyBy('kode');

        $result = [];

        foreach ($pegawaiList as $peg) {
            $jadwal = $jadwalMap[$peg->id] ?? null;
            $hariData = [];

            for ($hari = 1; $hari <= $jumlahHari; $hari++) {
                $tgl     = Carbon::create($tahun, $bulan, $hari);
                $key     = $peg->id . '_' . $hari;
                $absensi = $absensiMap[$key] ?? collect();
                $absensi = $absensi->first();

                // Shift rencana
                $namaShiftRencana = $jadwal ? ($jadwal->{"h{$hari}"} ?? '') : '';
                $shiftRencana     = ShiftMaster::dariNamaJadwal($namaShiftRencana);

                // Cek tukar shift untuk pegawai ini pada tanggal ini
                $isTukar = false;
                $shiftRealisasi = $shiftRencana;

                foreach ($tukarMap as $ts) {
                    if ($ts->pemohon_id === $peg->user_id
                        && $ts->tgl_shift_pemohon->day === $hari
                        && $ts->tgl_shift_pemohon->month === $bulan) {
                        $isTukar = true;
                        $shiftRealisasi = $shiftMasterAll[$ts->shift_rekan_kode] ?? $shiftRencana;
                        break;
                    }
                    if ($ts->rekan_id === $peg->user_id
                        && $ts->tgl_shift_rekan->day === $hari
                        && $ts->tgl_shift_rekan->month === $bulan) {
                        $isTukar = true;
                        $shiftRealisasi = $shiftMasterAll[$ts->shift_pemohon_kode] ?? $shiftRencana;
                        break;
                    }
                }

                // Lembur hari ini
                $lemburHari = $lemburMap[$key] ?? collect();

                // Hitung overtime dari absensi vs shift selesai
                $ovtMenit   = 0;
                $jamKeluar  = null;
                $jamMasuk   = null;

                if ($absensi) {
                    $jamMasuk  = $absensi->jam_masuk  ? Carbon::parse($absensi->jam_masuk)->format('H:i')  : null;
                    $jamKeluar = $absensi->jam_keluar ? Carbon::parse($absensi->jam_keluar)->format('H:i') : null;

                    if ($jamKeluar && $shiftRealisasi && $shiftRealisasi->jam_selesai) {
                        $selesaiShift = Carbon::createFromTimeString(substr($shiftRealisasi->jam_selesai, 0, 5));
                        $keluarAktual = Carbon::parse($absensi->jam_keluar);
                        $ovtMenit     = max(0, $keluarAktual->diffInMinutes($selesaiShift, false) * -1);
                    }
                }

                $hariData[$hari] = [
                    'tanggal'       => $tgl,
                    'nama_rencana'  => $namaShiftRencana,
                    'shift_rencana' => $shiftRencana,
                    'shift_real'    => $shiftRealisasi,
                    'is_tukar'      => $isTukar,
                    'absensi'       => $absensi,
                    'jam_masuk'     => $jamMasuk,
                    'jam_keluar'    => $jamKeluar,
                    'ovt_menit'     => $ovtMenit,
                    'lembur'        => $lemburHari,
                    'is_libur'      => $tgl->isWeekend() || strtolower($namaShiftRencana) === 'libur' || $namaShiftRencana === '',
                ];
            }

            $result[] = [
                'pegawai'   => $peg,
                'hari_data' => $hariData,
            ];
        }

        return $result;
    }
}
