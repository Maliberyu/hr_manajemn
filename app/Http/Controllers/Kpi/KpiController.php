<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\KpiSetting;
use App\Models\Pegawai;
use App\Models\RekapAbsensi;
use App\Models\PenilaianPrestasi;
use App\Models\Penilaian360;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    // Fallback konstanta (dipakai jika setting DB belum ada)
    const TARGET_JAM_PELATIHAN = 40;

    // ── Dashboard KPI ─────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tahun    = (int) ($request->tahun ?? now()->year);
        $semester = (int) ($request->semester ?? (now()->month <= 6 ? 1 : 2));
        $depId    = $request->departemen;
        $setting  = KpiSetting::aktif();

        [$bulanMulai, $bulanAkhir] = $semester === 1 ? [1, 6] : [7, 12];

        // ── Komponen 1 & 2: Kehadiran + Disiplin (dari rekap_absensi) ──────
        $rekapData = RekapAbsensi::whereBetween('bulan', [$bulanMulai, $bulanAkhir])
            ->where('tahun', $tahun)
            ->selectRaw('pegawai_id,
                SUM(total_hadir) as hadir,
                SUM(wajib_masuk) as wajib,
                SUM(total_alfa) as alfa,
                SUM(total_terlambat) as terlambat,
                SUM(total_menit_terlambat) as menit_terlambat')
            ->groupBy('pegawai_id')
            ->get()->keyBy('pegawai_id');

        // ── Komponen 3: Penilaian Prestasi ───────────────────────────────────
        $penilaianData = PenilaianPrestasi::where('tahun', $tahun)
            ->where('semester', $semester)
            ->where('status', 'final')
            ->get()->keyBy('pegawai_id');

        // ── Komponen 4: Penilaian 360° ───────────────────────────────────────
        $p360Data = Penilaian360::where('tahun', $tahun)
            ->where('semester', $semester)
            ->where('status', 'selesai')
            ->get()->keyBy('pegawai_id');

        // ── Komponen 5: Jam Pelatihan (IHT + Eksternal) ──────────────────────
        $ihtJam = DB::table('hr_iht_peserta as ip')
            ->join('hr_iht as i', 'ip.iht_id', '=', 'i.id')
            ->where('ip.status', 'hadir')
            ->whereYear('i.tanggal_mulai', $tahun)
            ->whereBetween(DB::raw('MONTH(i.tanggal_mulai)'), [$bulanMulai, $bulanAkhir])
            ->selectRaw('ip.pegawai_id,
                SUM((TIME_TO_SEC(TIMEDIFF(i.jam_selesai, i.jam_mulai)) / 3600)
                    * (DATEDIFF(i.tanggal_selesai, i.tanggal_mulai) + 1)) as jam')
            ->groupBy('ip.pegawai_id')
            ->get()->keyBy('pegawai_id');

        $eksternalJam = DB::table('hr_training_eksternal as te')
            ->whereIn('te.status', ['tervalidasi', 'disetujui'])
            ->whereYear('te.tanggal_mulai', $tahun)
            ->whereBetween(DB::raw('MONTH(te.tanggal_mulai)'), [$bulanMulai, $bulanAkhir])
            ->selectRaw('te.pegawai_id,
                SUM((DATEDIFF(te.tanggal_selesai, te.tanggal_mulai) + 1) * 8) as jam')
            ->groupBy('te.pegawai_id')
            ->get()->keyBy('pegawai_id');

        // ── Hitung KPI per pegawai ────────────────────────────────────────────
        $kpiList = Pegawai::aktif()
            ->with('departemenRef')
            ->when($depId, fn($q) => $q->where('departemen', $depId))
            ->orderBy('nama')
            ->get()
            ->map(function ($p) use ($rekapData, $penilaianData, $p360Data, $ihtJam, $eksternalJam, $setting) {
                $rekap     = $rekapData[$p->id]     ?? null;
                $penilaian = $penilaianData[$p->id] ?? null;
                $p360      = $p360Data[$p->id]       ?? null;
                $jamIHT    = (float) ($ihtJam[$p->id]?->jam ?? 0);
                $jamEkst   = (float) ($eksternalJam[$p->id]?->jam ?? 0);
                $jamTotal  = round($jamIHT + $jamEkst, 1);

                // Kehadiran — % hadir dari hari wajib
                $hadirPct      = $rekap && $rekap->wajib > 0
                    ? min(100, round(($rekap->hadir / $rekap->wajib) * 100, 1))
                    : null;
                $skorKehadiran = $hadirPct;

                // Disiplin — penalti dari terlambat & alfa (pakai setting)
                $skorDisiplin = $rekap
                    ? max(0, 100 - ($rekap->alfa * $setting->penalti_alfa) - ($rekap->terlambat * $setting->penalti_terlambat))
                    : null;

                // Penilaian Prestasi
                $skorPenilaian = $penilaian?->nilai_akhir;

                // 360°
                $skorP360 = $p360?->nilai_akhir;

                // Pelatihan — jam total vs target dari setting
                $skorPelatihan = ($rekap !== null || $jamTotal > 0)
                    ? min(100, round(($jamTotal / max(1, $setting->target_jam_pelatihan)) * 100, 1))
                    : null;

                // Skor composite — hanya komponen yang ada datanya, bobot dari setting
                $bobot = [
                    'kehadiran' => $setting->bobot_kehadiran,
                    'disiplin'  => $setting->bobot_disiplin,
                    'penilaian' => $setting->bobot_penilaian,
                    'p360'      => $setting->bobot_p360,
                    'pelatihan' => $setting->bobot_pelatihan,
                ];

                $totalBobot  = 0;
                $totalNilai  = 0;
                $komponenAda = 0;

                foreach ([
                    'kehadiran' => $skorKehadiran,
                    'disiplin'  => $skorDisiplin,
                    'penilaian' => $skorPenilaian,
                    'p360'      => $skorP360,
                    'pelatihan' => $skorPelatihan,
                ] as $key => $nilai) {
                    if ($nilai !== null) {
                        $totalBobot += $bobot[$key];
                        $totalNilai += $nilai * $bobot[$key];
                        $komponenAda++;
                    }
                }

                $skorKPI = $totalBobot > 0
                    ? round($totalNilai / $totalBobot, 1)
                    : null;

                $predikat = match(true) {
                    $skorKPI === null => null,
                    $skorKPI >= 90   => 'Istimewa',
                    $skorKPI >= 75   => 'Puas',
                    $skorKPI >= 60   => 'Biasa',
                    $skorKPI >= 45   => 'Kurang',
                    default          => 'Kecewa',
                };

                return [
                    'pegawai'        => $p,
                    'hadir_pct'      => $hadirPct,
                    'skor_kehadiran' => $skorKehadiran,
                    'skor_disiplin'  => $skorDisiplin,
                    'skor_penilaian' => $skorPenilaian,
                    'skor_p360'      => $skorP360,
                    'jam_pelatihan'  => $jamTotal,
                    'skor_pelatihan' => $skorPelatihan,
                    'skor_kpi'       => $skorKPI,
                    'predikat'       => $predikat,
                    'komponen_ada'   => $komponenAda,
                    'terlambat'      => $rekap?->terlambat ?? 0,
                    'alfa'           => $rekap?->alfa ?? 0,
                ];
            });

        // Ringkasan predikat
        $ringkasan = $kpiList
            ->whereNotNull('predikat')
            ->groupBy('predikat')
            ->map->count();

        $departemen = Departemen::orderBy('nama')->get(['dep_id', 'nama']);

        return view('kpi.index', compact(
            'kpiList', 'ringkasan', 'departemen', 'setting',
            'tahun', 'semester', 'depId'
        ));
    }

    // ── Target KPI ────────────────────────────────────────────────────────────

    public function target(Request $request)
    {
        $setting = KpiSetting::aktif();
        return view('kpi.target', compact('setting'));
    }

    public function saveTarget(Request $request)
    {
        $data = $request->validate([
            'bobot_kehadiran'      => 'required|integer|min:0|max:100',
            'bobot_disiplin'       => 'required|integer|min:0|max:100',
            'bobot_penilaian'      => 'required|integer|min:0|max:100',
            'bobot_p360'           => 'required|integer|min:0|max:100',
            'bobot_pelatihan'      => 'required|integer|min:0|max:100',
            'target_hadir_pct'     => 'required|integer|min:1|max:100',
            'target_jam_pelatihan' => 'required|integer|min:1|max:999',
            'penalti_alfa'         => 'required|integer|min:0|max:50',
            'penalti_terlambat'    => 'required|integer|min:0|max:50',
        ]);

        $totalBobot = $data['bobot_kehadiran'] + $data['bobot_disiplin']
                    + $data['bobot_penilaian'] + $data['bobot_p360']
                    + $data['bobot_pelatihan'];

        if ($totalBobot !== 100) {
            return back()->withInput()
                ->withErrors(['bobot' => "Total bobot harus 100%. Sekarang: {$totalBobot}%."]);
        }

        KpiSetting::aktif()->update($data);
        return back()->with('success', 'Setting KPI berhasil disimpan.');
    }

    // ── Rekap KPI ─────────────────────────────────────────────────────────────

    public function rekap(Request $request)
    {
        $tahun    = (int) ($request->tahun ?? now()->year);
        $semester = (int) ($request->semester ?? (now()->month <= 6 ? 1 : 2));
        $depId    = $request->departemen;
        $setting  = KpiSetting::aktif();

        [$bulanMulai, $bulanAkhir] = $semester === 1 ? [1, 6] : [7, 12];

        // Gunakan helper yang sama dengan index()
        $rekapData = RekapAbsensi::whereBetween('bulan', [$bulanMulai, $bulanAkhir])
            ->where('tahun', $tahun)
            ->selectRaw('pegawai_id, SUM(total_hadir) as hadir, SUM(wajib_masuk) as wajib,
                SUM(total_alfa) as alfa, SUM(total_terlambat) as terlambat,
                SUM(total_menit_terlambat) as menit_terlambat, SUM(total_lembur_jam) as lembur_jam')
            ->groupBy('pegawai_id')->get()->keyBy('pegawai_id');

        $penilaianData = PenilaianPrestasi::where('tahun', $tahun)
            ->where('semester', $semester)->where('status', 'final')
            ->get()->keyBy('pegawai_id');

        $p360Data = Penilaian360::where('tahun', $tahun)
            ->where('semester', $semester)->where('status', 'selesai')
            ->get()->keyBy('pegawai_id');

        $ihtJam = DB::table('hr_iht_peserta as ip')
            ->join('hr_iht as i', 'ip.iht_id', '=', 'i.id')
            ->where('ip.status', 'hadir')->whereYear('i.tanggal_mulai', $tahun)
            ->whereBetween(DB::raw('MONTH(i.tanggal_mulai)'), [$bulanMulai, $bulanAkhir])
            ->selectRaw('ip.pegawai_id, SUM((TIME_TO_SEC(TIMEDIFF(i.jam_selesai,i.jam_mulai))/3600)*(DATEDIFF(i.tanggal_selesai,i.tanggal_mulai)+1)) as jam')
            ->groupBy('ip.pegawai_id')->get()->keyBy('pegawai_id');

        $eksternalJam = DB::table('hr_training_eksternal as te')
            ->whereIn('te.status', ['tervalidasi','disetujui'])->whereYear('te.tanggal_mulai', $tahun)
            ->whereBetween(DB::raw('MONTH(te.tanggal_mulai)'), [$bulanMulai, $bulanAkhir])
            ->selectRaw('te.pegawai_id, SUM((DATEDIFF(te.tanggal_selesai,te.tanggal_mulai)+1)*8) as jam')
            ->groupBy('te.pegawai_id')->get()->keyBy('pegawai_id');

        $bobot = [
            'kehadiran' => $setting->bobot_kehadiran,
            'disiplin'  => $setting->bobot_disiplin,
            'penilaian' => $setting->bobot_penilaian,
            'p360'      => $setting->bobot_p360,
            'pelatihan' => $setting->bobot_pelatihan,
        ];

        $kpiList = Pegawai::aktif()->with('departemenRef')
            ->when($depId, fn($q) => $q->where('departemen', $depId))
            ->orderBy('nama')->get()
            ->map(function ($p) use ($rekapData, $penilaianData, $p360Data, $ihtJam, $eksternalJam, $setting, $bobot) {
                $rekap     = $rekapData[$p->id]     ?? null;
                $penilaian = $penilaianData[$p->id] ?? null;
                $p360      = $p360Data[$p->id]       ?? null;
                $jamTotal  = round((float)($ihtJam[$p->id]?->jam ?? 0) + (float)($eksternalJam[$p->id]?->jam ?? 0), 1);

                $hadirPct      = $rekap && $rekap->wajib > 0 ? min(100, round(($rekap->hadir / $rekap->wajib)*100,1)) : null;
                $skorKehadiran = $hadirPct;
                $skorDisiplin  = $rekap ? max(0, 100-($rekap->alfa*$setting->penalti_alfa)-($rekap->terlambat*$setting->penalti_terlambat)) : null;
                $skorPenilaian = $penilaian?->nilai_akhir;
                $skorP360      = $p360?->nilai_akhir;
                $skorPelatihan = ($rekap||$jamTotal>0) ? min(100, round(($jamTotal/max(1,$setting->target_jam_pelatihan))*100,1)) : null;

                $totalBobot = $totalNilai = $komponenAda = 0;
                foreach (['kehadiran'=>$skorKehadiran,'disiplin'=>$skorDisiplin,'penilaian'=>$skorPenilaian,'p360'=>$skorP360,'pelatihan'=>$skorPelatihan] as $key=>$nilai) {
                    if ($nilai !== null) { $totalBobot += $bobot[$key]; $totalNilai += $nilai*$bobot[$key]; $komponenAda++; }
                }
                $skorKPI  = $totalBobot > 0 ? round($totalNilai/$totalBobot,1) : null;
                $predikat = match(true) { $skorKPI===null=>null, $skorKPI>=90=>'Istimewa', $skorKPI>=75=>'Puas', $skorKPI>=60=>'Biasa', $skorKPI>=45=>'Kurang', default=>'Kecewa' };

                return compact('p','hadirPct','skorKehadiran','skorDisiplin','skorPenilaian','skorP360','jamTotal','skorPelatihan','skorKPI','predikat','komponenAda') +
                    ['pegawai'=>$p,'terlambat'=>$rekap?->terlambat??0,'alfa'=>$rekap?->alfa??0,'hadir'=>$rekap?->hadir??0,'lembur_jam'=>round((float)($rekap?->lembur_jam??0),1)];
            })->sortByDesc('skorKPI')->values();

        // Statistik agregat
        $punya = $kpiList->whereNotNull('skorKPI');
        $stats = [
            'total'       => $kpiList->count(),
            'dinilai'     => $punya->count(),
            'rata_rata'   => $punya->count() ? round($punya->avg('skorKPI'),1) : null,
            'tertinggi'   => $punya->max('skorKPI'),
            'terendah'    => $punya->min('skorKPI'),
        ];

        $distribusi = $kpiList->whereNotNull('predikat')->groupBy('predikat')->map->count();

        // Per departemen
        $perDep = $kpiList->whereNotNull('skorKPI')
            ->groupBy(fn($r) => $r['pegawai']->departemenRef?->nama ?? $r['pegawai']->departemen ?? 'Lainnya')
            ->map(fn($g) => ['count'=>$g->count(),'avg'=>round($g->avg('skorKPI'),1)])
            ->sortByDesc('avg')->values();

        $grafikDep  = $perDep->pluck('avg','0')->toArray(); // not quite right, fix:
        $grafikDep  = $perDep->map(fn($d,$k)=>$d)->values();
        $depLabels  = $perDep->keys()->values();

        $departemen = Departemen::orderBy('nama')->get(['dep_id','nama']);

        return view('kpi.rekap', compact(
            'kpiList','stats','distribusi','perDep','depLabels',
            'setting','bobot','departemen','tahun','semester','depId'
        ));
    }
}
