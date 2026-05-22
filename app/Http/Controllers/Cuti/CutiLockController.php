<?php

namespace App\Http\Controllers\Cuti;

use App\Http\Controllers\Controller;
use App\Models\CutiLock;
use App\Models\CutiSetting;
use App\Models\CutiUnlockRequest;
use Illuminate\Http\Request;

class CutiLockController extends Controller
{
    // ── Halaman setting lock + H-N ────────────────────────────────────────────

    public function index()
    {
        $lock       = CutiLock::status();
        $setting    = CutiSetting::get();
        $requests   = CutiUnlockRequest::with('user')->latest()->paginate(15);
        $menunggu   = CutiUnlockRequest::menunggu()->count();
        return view('cuti.lock.index', compact('lock', 'setting', 'requests', 'menunggu'));
    }

    // ── Kunci cuti ────────────────────────────────────────────────────────────

    public function kunci(Request $request)
    {
        $request->validate(['alasan_kunci' => 'required|string|max:500']);
        CutiLock::status()->kunci($request->alasan_kunci);
        return back()->with('success', 'Fitur cuti tahunan berhasil dikunci.');
    }

    // ── Buka kunci cuti ───────────────────────────────────────────────────────

    public function buka()
    {
        CutiLock::status()->buka();
        return back()->with('success', 'Fitur cuti tahunan dibuka kembali untuk semua karyawan.');
    }

    // ── Update setting H-N ────────────────────────────────────────────────────

    public function updateSetting(Request $request)
    {
        $request->validate([
            'min_hari_pengajuan' => 'required|integer|min:0|max:30',
        ]);
        CutiSetting::get()->update([
            'min_hari_pengajuan' => $request->min_hari_pengajuan,
            'updated_by'         => auth()->id(),
        ]);
        return back()->with('success', "Setting H-{$request->min_hari_pengajuan} berhasil disimpan.");
    }

    // ── Review unlock request dari karyawan ───────────────────────────────────

    public function setujuiRequest(Request $request, CutiUnlockRequest $cutiUnlockRequest)
    {
        abort_unless($cutiUnlockRequest->status === 'menunggu', 422);
        $cutiUnlockRequest->update([
            'status'      => 'disetujui',
            'catatan_hrd' => $request->catatan_hrd,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        return back()->with('success', 'Permintaan disetujui. Karyawan kini bisa mengajukan cuti.');
    }

    public function tolakRequest(Request $request, CutiUnlockRequest $cutiUnlockRequest)
    {
        $request->validate(['catatan_hrd' => 'required|string|max:300']);
        abort_unless($cutiUnlockRequest->status === 'menunggu', 422);
        $cutiUnlockRequest->update([
            'status'      => 'ditolak',
            'catatan_hrd' => $request->catatan_hrd,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        return back()->with('success', 'Permintaan ditolak.');
    }
}
