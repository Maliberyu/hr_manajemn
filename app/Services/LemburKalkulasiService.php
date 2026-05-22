<?php

namespace App\Services;

use App\Models\JadwalPegawai;
use App\Models\Lembur;
use App\Models\LemburSetting;
use App\Models\Pegawai;
use App\Models\ShiftMaster;
use App\Models\TarifLembur;
use Carbon\Carbon;

class LemburKalkulasiService
{
    private LemburSetting $setting;

    public function __construct(?LemburSetting $setting = null)
    {
        $this->setting = $setting ?? LemburSetting::get();
    }

    /**
     * Hitung semua parameter lembur secara otomatis.
     *
     * @return array{
     *   dihitung: bool, alasan?: string,
     *   metode: string, shift_kode: ?string,
     *   jam_selesai_shift: ?string, multiplier: float,
     *   upah_per_jam: float, durasi_aktual: float,
     *   durasi_jam: float, nominal: float,
     *   catatan_sistem: ?string, jenis: string
     * }
     */
    public function hitung(
        Pegawai $pegawai,
        Carbon  $tanggal,
        Carbon  $jamMulai,
        Carbon  $jamSelesai,
        string  $jenis = 'HB'
    ): array {
        // ── 1. Cari jadwal shift hari ini ─────────────────────────────────────
        $hariKe      = (int) $tanggal->format('j');
        $jadwal      = JadwalPegawai::where('id', $pegawai->id)
                          ->where('tahun', $tanggal->year)
                          ->where('bulan', $tanggal->month)
                          ->first();
        $namaShift   = $jadwal ? ($jadwal->{"h{$hariKe}"} ?? '') : '';
        $shiftMaster = ShiftMaster::dariNamaJadwal($namaShift);
        $hasShift    = $shiftMaster !== null;

        // ── 2. Tentukan metode secara otomatis ────────────────────────────────
        $metode = match($this->setting->metode) {
            'shift'      => 'shift',
            'jam_aktual' => 'jam_aktual',
            default      => $hasShift ? 'shift' : 'jam_aktual',  // keduanya
        };

        // ── 3. Hitung durasi & multiplier per metode ──────────────────────────
        $multiplier        = $hasShift ? (float) $shiftMaster->multiplier_lembur : 1.0;
        $jamSelesaiShiftStr = null;
        $durasiAktual      = 0.0;

        if ($jenis === 'HR') {
            // Hari Raya/Libur selalu multiplier 2.0
            $multiplier = 2.0;
        }

        if ($metode === 'shift' && $hasShift) {
            // Durasi = jam_keluar - jam_selesai_shift
            $tglStr       = $tanggal->format('Y-m-d');
            $selesaiShift = Carbon::parse($tglStr . ' ' . substr($shiftMaster->jam_selesai, 0, 5));

            // Shift malam melewati tengah malam: jam_selesai bisa esok hari
            if ($shiftMaster->melewati_tengah_malam && $selesaiShift->lt($jamMulai)) {
                $selesaiShift->addDay();
            }

            $durasiMenit  = max(0, (int) $jamSelesai->diffInMinutes($selesaiShift, false) * -1);
            $durasiAktual = round($durasiMenit / 60, 2);
            $jamSelesaiShiftStr = substr($shiftMaster->jam_selesai, 0, 5);

            // Cek minimum shift
            if ($durasiAktual < $this->setting->min_jam_shift) {
                return [
                    'dihitung' => false,
                    'alasan'   => "Overtime hanya {$durasiAktual}j, minimum shift {$this->setting->min_jam_shift}j.",
                    'metode'   => $metode,
                ];
            }

        } else {
            // Jam aktual: floor per jam penuh
            $durasiAktual = (float) floor($jamMulai->diffInHours($jamSelesai, false));

            // Cek minimum jam aktual
            if ($durasiAktual < $this->setting->min_jam_lembur) {
                return [
                    'dihitung' => false,
                    'alasan'   => "Durasi {$durasiAktual}j, minimum {$this->setting->min_jam_lembur}j.",
                    'metode'   => $metode,
                ];
            }
        }

        // ── 4. Capping harian ─────────────────────────────────────────────────
        $durasiPakai    = $durasiAktual;
        $catatanSistem  = null;

        if ($durasiPakai > $this->setting->max_jam_harian) {
            $durasiPakai   = $this->setting->max_jam_harian;
            $catatanSistem = "Dipotong {$durasiAktual}j → {$durasiPakai}j (maks harian {$this->setting->max_jam_harian}j)";
        }

        // ── 5. Capping mingguan ───────────────────────────────────────────────
        $startWeek       = $tanggal->copy()->startOfWeek(Carbon::MONDAY);
        $endWeek         = $tanggal->copy()->endOfWeek(Carbon::SUNDAY);
        $totalMingguIni  = Lembur::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [$startWeek->format('Y-m-d'), $endWeek->format('Y-m-d')])
            ->whereDate('tanggal', '!=', $tanggal->format('Y-m-d'))
            ->whereNotIn('status', ['Ditolak Atasan', 'Ditolak HRD'])
            ->sum('durasi_jam');

        $sisaMinggu = max(0, $this->setting->max_jam_mingguan - $totalMingguIni);

        if ($durasiPakai > $sisaMinggu) {
            $durasiPakai    = $sisaMinggu;
            $catatanSistem  = ($catatanSistem ? $catatanSistem . '; ' : '')
                . "Dipotong karena batas mingguan, sisa kuota {$sisaMinggu}j (terpakai {$totalMingguIni}j).";
        }

        // ── 6. Hitung upah per jam ────────────────────────────────────────────
        $upahPerJam = $this->hitungUpahPerJam($pegawai, $jenis);

        // ── 7. Nominal ────────────────────────────────────────────────────────
        $nominal = round($durasiPakai * $multiplier * $upahPerJam, 0);

        return [
            'dihitung'          => true,
            'metode'            => $metode,
            'shift_kode'        => $shiftMaster?->kode,
            'jam_selesai_shift' => $jamSelesaiShiftStr,
            'multiplier'        => $multiplier,
            'upah_per_jam'      => $upahPerJam,
            'durasi_aktual'     => $durasiAktual,
            'durasi_jam'        => $durasiPakai,
            'nominal'           => $nominal,
            'catatan_sistem'    => $catatanSistem,
            'jenis'             => $jenis,
        ];
    }

    /** Hitung upah per jam: cek tarif dept dulu, fallback ke gapok/173 */
    public function hitungUpahPerJam(Pegawai $pegawai, string $jenis = 'HB'): float
    {
        if ($this->setting->formula_upah_jam === 'tarif_dept') {
            $tarif = TarifLembur::getForDep($pegawai->departemen);
            if ($tarif) {
                return (float) ($jenis === 'HR' ? $tarif->tarif_hr : $tarif->tarif_hb);
            }
        }

        // Fallback: cek tarif dept apapun formula-nya (override)
        $tarif = TarifLembur::getForDep($pegawai->departemen);
        if ($tarif && ($jenis === 'HR' ? $tarif->tarif_hr : $tarif->tarif_hb) > 0) {
            return (float) ($jenis === 'HR' ? $tarif->tarif_hr : $tarif->tarif_hb);
        }

        // gapok / 173
        $gapok = (float) ($pegawai->gapok ?? 0);
        return $gapok > 0 ? round($gapok / 173, 0) : 0;
    }

    public function getSetting(): LemburSetting
    {
        return $this->setting;
    }
}
