<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\AmbilDankes;
use App\Models\AngsuranKoperasi;
use App\Models\RekapAbsensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PayrollController extends Controller
{
    public function __construct()
    {
    }

    // Tarif BPJS (hardcode, bisa dipindah ke config/settings)
    const BPJS_KES_PEKERJA   = 0.01;   // 1%
    const BPJS_KES_PERUSAHAAN= 0.04;   // 4%
    const BPJS_TK_JHT_PEKERJA = 0.02;  // 2%
    const BPJS_TK_JHT_PERUSAHAAN = 0.037;
    const BPJS_TK_JP_PEKERJA  = 0.01;  // 1%
    const TUNJANGAN_TRANSPORT  = 150000;
    const TUNJANGAN_MAKAN      = 100000;
    const PTKP_TK0             = 54000000; // PTKP tahunan TK/0

    // ─── Index: pilih bulan payroll ───────────────────────────────────────────

    public function index(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $pegawai = Pegawai::aktif()
            ->with(['departemenRef', 'pendidikanRef'])
            ->when($request->departemen, fn($q, $d) => $q->departemen($d))
            ->orderBy('nama')
            ->paginate(30)->withQueryString();

        return view('payroll.index', compact('pegawai', 'bulan', 'tahun'));
    }

    // ─── Hitung & tampilkan slip satu pegawai ─────────────────────────────────

    public function show(Request $request, Pegawai $karyawan)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $slip = $this->hitungGaji($karyawan, $bulan, $tahun);

        return view('payroll.show', compact('karyawan', 'slip', 'bulan', 'tahun'));
    }

    // ─── Proses payroll massal ────────────────────────────────────────────────

    public function proses(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;

        $pegawai = Pegawai::aktif()->get();
        $hasil   = [];

        foreach ($pegawai as $p) {
            $hasil[] = $this->hitungGaji($p, $bulan, $tahun);
        }

        return view('payroll.proses', compact('hasil', 'bulan', 'tahun'));
    }

    // ─── Slip gaji PDF (satu pegawai) ─────────────────────────────────────────

    public function slipPdf(Request $request, Pegawai $karyawan)
    {

        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $slip = $this->hitungGaji($karyawan, $bulan, $tahun);

        $pdf = Pdf::loadView('payroll.pdf.slip', compact('karyawan', 'slip', 'bulan', 'tahun'))
                  ->setPaper([0, 0, 595, 400], 'portrait'); // A5 landscape-ish

        return $pdf->download("Slip_Gaji_{$karyawan->nik}_{$bulan}_{$tahun}.pdf");
    }

    // ─── Export rekap payroll Excel ───────────────────────────────────────────

    public function export(Request $request)
    {

        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        return Excel::download(
            new \App\Exports\PayrollExport($bulan, $tahun),
            "Payroll_{$bulan}_{$tahun}.xlsx"
        );
    }

    // ─── Core: kalkulasi gaji ─────────────────────────────────────────────────

    public function hitungGaji(Pegawai $pegawai, int $bulan, int $tahun): array
    {
        $periode = Carbon::create($tahun, $bulan, 1);

        // ── Rekap absensi bulan ini ──────────────────────────────────────────
        $rekap        = RekapAbsensi::where('pegawai_id', $pegawai->id)
                                    ->periode($tahun, $bulan)->first();
        $hariHadir    = $rekap?->total_hadir ?? $pegawai->wajibmasuk;
        $hariWajib    = $pegawai->wajibmasuk ?: 25;
        $totalLembur  = $rekap?->total_lembur_jam ?? 0;

        // ── Pendapatan ───────────────────────────────────────────────────────
        $gapok = (float) $pegawai->gapok;

        // Prorata: gaji dibayar proporsional jika ada alfa
        $totalAlfa  = $rekap?->total_alfa ?? 0;
        $potonganAlfa = $totalAlfa > 0 ? ($gapok / $hariWajib) * $totalAlfa : 0;
        $gapokBersih = $gapok - $potonganAlfa;

        $tunjTransport = self::TUNJANGAN_TRANSPORT;
        $tunjMakan     = self::TUNJANGAN_MAKAN;

        // Tunjangan dari gapok (indek × gapok — sesuai logika DB Respati)
        $tunjKhusus = (float) $pegawai->indek * $gapok;

        // Lembur
        $jamLemburBiasa = $totalLembur * 0.7; // asumsi 70% lembur hari biasa
        $jamLemburHR    = $totalLembur * 0.3;
        $nominalLembur  = ($jamLemburBiasa * \App\Models\SetLemburHB::tarifAktif())
                        + ($jamLemburHR    * \App\Models\SetLemburHR::tarifAktif());

        $totalPendapatan = $gapokBersih + $tunjTransport + $tunjMakan + $tunjKhusus + $nominalLembur;

        // ── Potongan ─────────────────────────────────────────────────────────
        $bpjsKes    = $gapok * self::BPJS_KES_PEKERJA;
        $bpjsJHT    = $gapok * self::BPJS_TK_JHT_PEKERJA;
        $bpjsJP     = $gapok * self::BPJS_TK_JP_PEKERJA;

        // Angsuran koperasi bulan ini
        $koperasi = AngsuranKoperasi::where('id', $pegawai->id)
                                    ->bulanAngsur($tahun, $bulan)
                                    ->sum('pokok') + AngsuranKoperasi::where('id', $pegawai->id)
                                    ->bulanAngsur($tahun, $bulan)
                                    ->sum('jasa');

        // Dana kesehatan terpakai
        $dankes = AmbilDankes::where('id', $pegawai->id)
                             ->bulan($tahun, $bulan)->sum('dankes');

        // PPh21 sederhana (bisa diperluas dengan tabel pajak)
        $pph21 = $this->hitungPPh21($gapokBersih + $tunjKhusus, $pegawai->stts_wp ?? 'TK/0');

        $totalPotongan = $bpjsKes + $bpjsJHT + $bpjsJP + $koperasi + $dankes + $pph21;

        $gajiDiterima = $totalPendapatan - $totalPotongan;

        return [
            'pegawai'          => $pegawai,
            'periode'          => $periode->translatedFormat('F Y'),
            'bulan'            => $bulan,
            'tahun'            => $tahun,
            'hari_hadir'       => $hariHadir,
            'hari_wajib'       => $hariWajib,
            'total_alfa'       => $totalAlfa,

            // Pendapatan
            'gapok'            => $gapok,
            'potongan_alfa'    => $potonganAlfa,
            'gapok_bersih'     => $gapokBersih,
            'tunj_transport'   => $tunjTransport,
            'tunj_makan'       => $tunjMakan,
            'tunj_khusus'      => $tunjKhusus,
            'lembur_jam'       => $totalLembur,
            'lembur_nominal'   => $nominalLembur,
            'total_pendapatan' => $totalPendapatan,

            // Potongan
            'bpjs_kes'         => $bpjsKes,
            'bpjs_jht'         => $bpjsJHT,
            'bpjs_jp'          => $bpjsJP,
            'koperasi'         => $koperasi,
            'dankes'           => $dankes,
            'pph21'            => $pph21,
            'total_potongan'   => $totalPotongan,

            'gaji_diterima'    => $gajiDiterima,
        ];
    }

    // ─── Kalkulasi PPh21 sederhana ────────────────────────────────────────────

    private function hitungPPh21(float $penghasilanBrutoPerBulan, string $statusWP): float
    {
        $brutoTahunan = $penghasilanBrutoPerBulan * 12;

        $ptkp = match(true) {
            str_starts_with($statusWP, 'K/3') => 63000000,
            str_starts_with($statusWP, 'K/2') => 61500000,
            str_starts_with($statusWP, 'K/1') => 60000000,
            str_starts_with($statusWP, 'K')   => 58500000,
            default                            => 54000000, // TK/0
        };

        $pkp = max(0, $brutoTahunan - $ptkp);

        // Tarif progresif
        $pajak = 0;
        if ($pkp <= 60_000_000) {
            $pajak = $pkp * 0.05;
        } elseif ($pkp <= 250_000_000) {
            $pajak = 3_000_000 + ($pkp - 60_000_000) * 0.15;
        } elseif ($pkp <= 500_000_000) {
            $pajak = 31_500_000 + ($pkp - 250_000_000) * 0.25;
        } else {
            $pajak = 93_500_000 + ($pkp - 500_000_000) * 0.30;
        }

        return round($pajak / 12); // per bulan
    }
}
