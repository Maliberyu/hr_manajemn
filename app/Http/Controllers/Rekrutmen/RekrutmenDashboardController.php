<?php

namespace App\Http\Controllers\Rekrutmen;

use App\Http\Controllers\Controller;
use App\Models\RekrutmenRequest;
use App\Models\Lowongan;
use App\Models\HrPelamar;
use App\Models\HrInterview;
use Illuminate\Support\Facades\DB;

class RekrutmenDashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $isHrd    = $user->hasRole(['hrd', 'admin']);

        // Stats
        $stats = [
            'request_menunggu' => RekrutmenRequest::menunggu()
                ->when(!$isHrd, fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'lowongan_aktif'   => Lowongan::aktif()->count(),
            'total_pelamar'    => HrPelamar::count(),
            'interview_hari_ini' => HrInterview::whereDate('jadwal', today())
                ->where('status', 'dijadwalkan')->count(),
        ];

        // Pipeline funnel (HRD only)
        $pipeline = [];
        if ($isHrd) {
            $pipeline = HrPelamar::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');
        }

        // 5 request terbaru
        $requestTerbaru = RekrutmenRequest::with('pengaju','departemenRef')
            ->when(!$isHrd, fn($q) => $q->where('user_id', $user->id))
            ->latest()->limit(5)->get();

        // 5 interview terdekat
        $interviewTerdekat = HrInterview::with(['pelamar.lowongan', 'pewawancara'])
            ->where('status', 'dijadwalkan')
            ->where('jadwal', '>=', now())
            ->orderBy('jadwal')->limit(5)->get();

        // Pelamar per bulan (6 bulan terakhir) — HRD only
        $grafikPelamar = collect();
        if ($isHrd) {
            $grafikPelamar = DB::table('hr_pelamar')
                ->selectRaw('YEAR(tanggal_apply) as tahun, MONTH(tanggal_apply) as bulan, COUNT(*) as total')
                ->where('tanggal_apply', '>=', now()->subMonths(5)->startOfMonth())
                ->groupBy('tahun', 'bulan')->orderBy('tahun')->orderBy('bulan')
                ->get()->map(fn($r) => [
                    'label' => \Carbon\Carbon::create($r->tahun, $r->bulan)->translatedFormat('M Y'),
                    'total' => $r->total,
                ]);
        }

        return view('rekrutmen.dashboard', compact(
            'stats', 'pipeline', 'requestTerbaru',
            'interviewTerdekat', 'grafikPelamar', 'isHrd'
        ));
    }
}
