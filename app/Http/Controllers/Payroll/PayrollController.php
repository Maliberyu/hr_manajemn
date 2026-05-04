<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\{Pegawai, Departemen, SlipGaji, SlipKomponen, MasterGaji, KomponenGaji,
                PayrollConfig, PegawaiPayroll, Umk, Lembur, RekapAbsensi};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // ─── Index: daftar pegawai + status slip ─────────────────────────────────

    public function index(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        $depId = $request->departemen;

        $pegawai = Pegawai::aktif()
            ->with([
                'departemenRef', 'pendidikanRef', 'payrollSetting',
                'slipGaji' => fn($q) => $q->periode($tahun, $bulan),
            ])
            ->when($depId, fn($q, $d) => $q->departemen($d))
            ->orderBy('nama')
            ->paginate(30)->withQueryString();

        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');
        $totalDraft = SlipGaji::periode($tahun, $bulan)->draft()->count();
        $totalFinal = SlipGaji::periode($tahun, $bulan)->final()->count();

        return view('payroll.index', compact(
            'pegawai', 'departemen', 'bulan', 'tahun', 'depId', 'totalDraft', 'totalFinal'
        ));
    }

    // ─── Master management (UMK, Gaji, Komponen, Config, Pegawai) ────────────

    public function master()
    {
        $umkList     = Umk::orderByDesc('tahun')->get();
        $masterGaji  = MasterGaji::orderBy('umk_tahun')->orderBy('golongan')->get();
        $tambahan    = KomponenGaji::where('jenis', 'tambah')->orderBy('urutan')->get();
        $potongan    = KomponenGaji::where('jenis', 'kurang')->orderBy('urutan')->get();
        $config      = PayrollConfig::allConfig();
        $pegawaiList = Pegawai::aktif()
                              ->with(['payrollSetting', 'pendidikanRef', 'departemenRef'])
                              ->orderBy('nama')->get();
        $pendidikan  = DB::table('pendidikan')->orderBy('tingkat')->get();
        $umkTahunOpts= Umk::orderByDesc('tahun')->pluck('tahun');

        return view('payroll.master', compact(
            'umkList', 'masterGaji', 'tambahan', 'potongan',
            'config', 'pegawaiList', 'pendidikan', 'umkTahunOpts'
        ));
    }

    // ─── CRUD UMK ─────────────────────────────────────────────────────────────

    public function storeUmk(Request $request)
    {
        $request->validate(['tahun' => 'required|integer|min:2000', 'nominal' => 'required|numeric|min:0']);
        Umk::updateOrCreate(['tahun' => $request->tahun], [
            'nominal'     => $request->nominal,
            'keterangan'  => $request->keterangan,
        ]);
        return back()->with('success_umk', "UMK {$request->tahun} disimpan.");
    }

    public function destroyUmk(Umk $umk)
    {
        $umk->delete();
        return back()->with('success_umk', 'UMK dihapus.');
    }

    // ─── CRUD Master Gaji ─────────────────────────────────────────────────────

    public function storeMasterGaji(Request $request)
    {
        $request->validate([
            'golongan'   => 'required|max:100',
            'umk_tahun'  => 'required|integer',
            'gaji_pokok' => 'required|numeric|min:0',
        ]);
        MasterGaji::updateOrCreate(
            ['golongan' => $request->golongan, 'pendidikan' => $request->pendidikan ?: null, 'umk_tahun' => $request->umk_tahun],
            ['gaji_pokok' => $request->gaji_pokok, 'tunjangan_jabatan' => $request->tunjangan_jabatan ?? 0, 'keterangan' => $request->keterangan]
        );
        return back()->with('success_gaji', 'Master gaji disimpan.');
    }

    public function destroyMasterGaji(MasterGaji $masterGaji)
    {
        $masterGaji->delete();
        return back()->with('success_gaji', 'Master gaji dihapus.');
    }

    // ─── CRUD Komponen (tambah/kurang) ────────────────────────────────────────

    public function storeKomponen(Request $request)
    {
        $request->validate([
            'nama'  => 'required|max:100',
            'jenis' => 'required|in:tambah,kurang',
            'tipe'  => 'required|in:tetap,persen_gapok,persen_umk',
            'nilai' => 'required|numeric|min:0',
        ]);
        KomponenGaji::create([
            'nama'        => $request->nama,
            'jenis'       => $request->jenis,
            'tipe'        => $request->tipe,
            'nilai'       => $request->nilai,
            'urutan'      => $request->urutan ?? 50,
            'aktif'       => true,
            'keterangan'  => $request->keterangan,
        ]);
        $key = $request->jenis === 'tambah' ? 'success_tambah' : 'success_kurang';
        return back()->with($key, "Komponen '{$request->nama}' ditambahkan.");
    }

    public function toggleKomponen(KomponenGaji $komponen)
    {
        $komponen->update(['aktif' => !$komponen->aktif]);
        return back();
    }

    public function destroyKomponen(KomponenGaji $komponen)
    {
        $komponen->delete();
        return back()->with('success_kurang', 'Komponen dihapus.');
    }

    // ─── Update Config ────────────────────────────────────────────────────────

    public function updateConfig(Request $request)
    {
        foreach ($request->config ?? [] as $key => $value) {
            PayrollConfig::set($key, $value ?? '0');
        }
        return back()->with('success_config', 'Konfigurasi disimpan.');
    }

    // ─── Setting per Pegawai ──────────────────────────────────────────────────

    public function savePegawaiPayroll(Request $request)
    {
        $request->validate(['nik' => 'required|exists:pegawai,nik']);
        PegawaiPayroll::updateOrCreate(
            ['nik' => $request->nik],
            ['golongan' => $request->golongan, 'umk_tahun' => $request->umk_tahun ?: null, 'catatan' => $request->catatan]
        );
        return back()->with('success_pegawai', 'Setting pegawai disimpan.');
    }

    // ─── Generate slip draft ──────────────────────────────────────────────────

    public function generateSlips(Request $request)
    {
        $request->validate(['bulan' => 'required|integer|between:1,12', 'tahun' => 'required|integer|min:2020']);

        $bulan    = (int) $request->bulan;
        $tahun    = (int) $request->tahun;
        $config   = PayrollConfig::allConfig();
        $komponen = KomponenGaji::where('aktif', true)->orderBy('urutan')->get();
        $pegawai  = Pegawai::aktif()->with('payrollSetting')->get();

        $generated = 0; $skipped = 0;

        foreach ($pegawai as $p) {
            $existing = SlipGaji::where('pegawai_id', $p->id)->periode($tahun, $bulan)->first();
            if ($existing?->status === 'final') { $skipped++; continue; }

            $komponenList = $this->buildKomponen($p, $bulan, $tahun, $config, $komponen);
            $tambah = collect($komponenList)->where('jenis', 'tambah')->sum('nilai');
            $kurang = collect($komponenList)->where('jenis', 'kurang')->sum('nilai');
            $gapok  = collect($komponenList)->firstWhere('nama', 'Gaji Pokok')['nilai'] ?? 0;

            $slip = SlipGaji::updateOrCreate(
                ['pegawai_id' => $p->id, 'bulan' => $bulan, 'tahun' => $tahun],
                [
                    'nik'             => $p->nik,
                    'status'          => 'draft',
                    'gaji_pokok'      => $gapok,
                    'total_tunjangan' => $tambah - $gapok,
                    'total_potongan'  => $kurang,
                    'gaji_bersih'     => $tambah - $kurang,
                    'generated_by'    => auth()->id(),
                    'generated_at'    => now(),
                ]
            );

            $slip->komponenSlip()->delete();
            foreach ($komponenList as $idx => $k) {
                $slip->komponenSlip()->create(array_merge($k, ['urutan' => ($idx + 1) * 10]));
            }
            $generated++;
        }

        return redirect()->route('payroll.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', "{$generated} slip draft digenerate. {$skipped} dilewati (sudah final).");
    }

    // ─── Tampilkan slip ───────────────────────────────────────────────────────

    public function showSlip(SlipGaji $slip)
    {
        $slip->load(['pegawai.departemenRef', 'pegawai.pendidikanRef', 'pegawai.payrollSetting', 'komponenSlip']);
        return view('payroll.slip', compact('slip'));
    }

    // ─── Update slip (edit nilai, tambah/hapus manual) ────────────────────────

    public function updateSlip(Request $request, SlipGaji $slip)
    {
        if ($slip->status === 'final') {
            return back()->withErrors(['status' => 'Slip sudah final.']);
        }

        // Update nilai komponen yang ada
        foreach ($request->komponen ?? [] as $id => $nilai) {
            SlipKomponen::where('id', $id)->where('slip_id', $slip->id)->update(['nilai' => (float)$nilai]);
        }

        // Hapus komponen manual
        $hapus = array_filter((array)($request->hapus ?? []));
        if ($hapus) {
            SlipKomponen::whereIn('id', $hapus)->where('slip_id', $slip->id)->where('sumber', 'manual')->delete();
        }

        // Tambah komponen manual baru
        $lastUrutan = $slip->komponenSlip()->max('urutan') ?? 0;
        foreach ($request->komponen_baru ?? [] as $row) {
            if (empty($row['nama']) || !isset($row['nilai']) || $row['nilai'] === '') continue;
            $slip->komponenSlip()->create([
                'nama'   => $row['nama'],
                'jenis'  => $row['jenis'] ?? 'kurang',
                'nilai'  => (float)$row['nilai'],
                'urutan' => $lastUrutan += 10,
                'sumber' => 'manual',
            ]);
        }

        $slip->load('komponenSlip');
        $slip->recalculate();

        return back()->with('success', 'Slip berhasil diperbarui.');
    }

    // ─── Finalize / Un-finalize ───────────────────────────────────────────────

    public function finalizeSlip(SlipGaji $slip)
    {
        $slip->update(['status' => 'final', 'finalized_at' => now()]);
        return back()->with('success', "Slip {$slip->pegawai?->nama} ({$slip->periode_label}) telah difinalisasi.");
    }

    public function unFinalizeSlip(SlipGaji $slip)
    {
        $slip->update(['status' => 'draft', 'finalized_at' => null]);
        return back()->with('success', 'Slip dikembalikan ke draft.');
    }

    // ─── PDF Slip ─────────────────────────────────────────────────────────────

    public function slipPdf(SlipGaji $slip)
    {
        $slip->load(['pegawai.departemenRef', 'komponenSlip']);
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.pdf.slip', compact('slip'))
                      ->setPaper('a5', 'landscape');
            $nama = str_replace(' ', '_', $slip->pegawai?->nama ?? 'slip');
            return $pdf->download("Slip_{$nama}_{$slip->bulan}_{$slip->tahun}.pdf");
        }
        return view('payroll.pdf.slip', compact('slip'));
    }

    // ─── Export Excel ─────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $slips = SlipGaji::with(['pegawai', 'komponenSlip'])
            ->periode($tahun, $bulan)->final()->get();

        // Simple CSV fallback
        $filename = "Payroll_{$bulan}_{$tahun}.csv";
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename={$filename}"];
        $callback = function () use ($slips) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['NIK','Nama','Departemen','Gaji Pokok','Total Tunjangan','Total Potongan','Gaji Bersih']);
            foreach ($slips as $s) {
                fputcsv($f, [
                    $s->nik, $s->pegawai?->nama, $s->pegawai?->departemenRef?->nama,
                    $s->gaji_pokok, $s->total_tunjangan, $s->total_potongan, $s->gaji_bersih,
                ]);
            }
            fclose($f);
        };
        return response()->stream($callback, 200, $headers);
    }

    // ═════════════════════════════════════════════════════════════════════════
    // PRIVATE: Build komponen gaji untuk satu pegawai
    // ═════════════════════════════════════════════════════════════════════════

    private function buildKomponen(Pegawai $p, int $bulan, int $tahun, array $config, $masterKomponen): array
    {
        $list = [];

        // ── Gaji Pokok dari master_gaji ──────────────────────────────────────
        $setting    = $p->payrollSetting;
        $masterGaji = null;
        $umkNominal = 0;

        if ($setting?->golongan && $setting?->umk_tahun) {
            $masterGaji = MasterGaji::cariUntuk($setting->golongan, $setting->umk_tahun, $p->pendidikan);
            $umkNominal = Umk::getNominal($setting->umk_tahun);
        }

        $gajiPokok   = (float) ($masterGaji?->gaji_pokok ?? $p->gapok ?? 0);
        $tunjJabatan = (float) ($masterGaji?->tunjangan_jabatan ?? 0);

        $list[] = ['nama' => 'Gaji Pokok', 'jenis' => 'tambah', 'nilai' => $gajiPokok, 'sumber' => 'master'];
        if ($tunjJabatan > 0) {
            $list[] = ['nama' => 'Tunjangan Jabatan', 'jenis' => 'tambah', 'nilai' => $tunjJabatan, 'sumber' => 'master'];
        }

        // ── Komponen yang dikonfigurasi HRD (tunjangan & potongan) ───────────
        foreach ($masterKomponen as $k) {
            $nilai = $k->hitungNilai($gajiPokok, $umkNominal);
            if ($nilai != 0) {
                $list[] = ['nama' => $k->nama, 'jenis' => $k->jenis, 'nilai' => $nilai, 'sumber' => 'komponen'];
            }
        }

        // ── Lembur disetujui bulan ini ────────────────────────────────────────
        $lembur = Lembur::where('pegawai_id', $p->id)->where('status', 'Disetujui')
                        ->bulan($tahun, $bulan)->sum('nominal');
        if ($lembur > 0) {
            $list[] = ['nama' => 'Lembur', 'jenis' => 'tambah', 'nilai' => round((float)$lembur), 'sumber' => 'auto'];
        }

        // ── Potongan Absensi (jika aktif) ─────────────────────────────────────
        if (($config['potongan_absensi_aktif'] ?? '0') === '1') {
            $rekap     = RekapAbsensi::where('pegawai_id', $p->id)->periode($tahun, $bulan)->first();
            $alfa      = (int) ($rekap?->total_alfa ?? 0);
            $tarifHari = (float) ($config['tarif_potongan_absensi'] ?? 0);
            if ($alfa > 0 && $tarifHari > 0) {
                $list[] = ['nama' => "Potongan Absensi ({$alfa} hari)", 'jenis' => 'kurang',
                           'nilai' => $alfa * $tarifHari, 'sumber' => 'auto'];
            }
        }

        // ── SIK: Angsuran Koperasi ────────────────────────────────────────────
        $koperasi = DB::table('angsuran_koperasi')
            ->where('id', $p->id)
            ->whereYear('tanggal_angsur', $tahun)->whereMonth('tanggal_angsur', $bulan)
            ->selectRaw('SUM(pokok + jasa) as total')->value('total') ?? 0;
        if ($koperasi > 0) {
            $list[] = ['nama' => 'Angsuran Koperasi', 'jenis' => 'kurang', 'nilai' => (float)$koperasi, 'sumber' => 'sik'];
        }

        // ── SIK: Dana Kesehatan ────────────────────────────────────────────────
        $dankes = DB::table('ambil_dankes')
            ->where('id', $p->id)
            ->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan)
            ->sum('dankes');
        if ($dankes > 0) {
            $list[] = ['nama' => 'Dana Kesehatan', 'jenis' => 'kurang', 'nilai' => (float)$dankes, 'sumber' => 'sik'];
        }

        // ── PPh21 otomatis (jika aktif) ───────────────────────────────────────
        if (($config['pph21_aktif'] ?? '1') === '1') {
            $bruto = collect($list)->where('jenis', 'tambah')->sum('nilai');
            $pph21 = $this->hitungPPh21($bruto, $p->stts_wp ?? 'TK/0');
            if ($pph21 > 0) {
                $list[] = ['nama' => 'PPh21', 'jenis' => 'kurang', 'nilai' => $pph21, 'sumber' => 'auto'];
            }
        }

        return $list;
    }

    private function hitungPPh21(float $brutoPerBulan, string $statusWP): float
    {
        $brutoTahunan = $brutoPerBulan * 12;
        $ptkp = match(true) {
            str_starts_with($statusWP, 'K/3') => 63_000_000,
            str_starts_with($statusWP, 'K/2') => 61_500_000,
            str_starts_with($statusWP, 'K/1') => 60_000_000,
            str_starts_with($statusWP, 'K')   => 58_500_000,
            default                            => 54_000_000,
        };
        $pkp   = max(0, $brutoTahunan - $ptkp);
        $pajak = 0;
        if      ($pkp <= 60_000_000)  $pajak = $pkp * 0.05;
        elseif  ($pkp <= 250_000_000) $pajak = 3_000_000 + ($pkp - 60_000_000) * 0.15;
        elseif  ($pkp <= 500_000_000) $pajak = 31_500_000 + ($pkp - 250_000_000) * 0.25;
        else                          $pajak = 93_500_000 + ($pkp - 500_000_000) * 0.30;
        return round($pajak / 12);
    }
}
