<?php

namespace App\Http\Controllers\Rekrutmen;

use App\Http\Controllers\Controller;
use App\Models\Departemen;
use App\Models\RekrutmenRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RekrutmenRequestController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $isHrd = $user->hasRole(['hrd', 'admin']);

        $query = RekrutmenRequest::with('pengaju', 'departemenRef', 'reviewer')
            ->when(!$isHrd, fn($q) => $q->where('user_id', $user->id))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->q, fn($q, $s) => $q->where('posisi', 'like', "%{$s}%"))
            ->latest();

        $list       = $query->paginate(20)->withQueryString();
        $statusList = RekrutmenRequest::STATUS;

        return view('rekrutmen.request.index', compact('list', 'statusList', 'isHrd'));
    }

    public function create()
    {
        $departemen = Departemen::orderBy('nama')->get(['dep_id', 'nama']);
        return view('rekrutmen.request.create', compact('departemen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'posisi'              => 'required|string|max:150',
            'departemen'          => 'nullable|exists:departemen,dep_id',
            'jumlah'              => 'required|integer|min:1|max:99',
            'alasan'              => 'required|string|max:1000',
            'tanggal_dibutuhkan'  => 'nullable|date|after_or_equal:today',
        ]);

        RekrutmenRequest::create([
            ...$validated,
            'no_request' => RekrutmenRequest::generateNomor(),
            'user_id'    => auth()->id(),
            'status'     => 'menunggu_hrd',
        ]);

        return redirect()->route('rekrutmen.request.index')
            ->with('success', 'Permintaan SDM berhasil diajukan ke HRD.');
    }

    public function show(RekrutmenRequest $rekrutmenRequest)
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole(['hrd','admin']) || $rekrutmenRequest->user_id === $user->id,
            403
        );
        $rekrutmenRequest->load('pengaju','departemenRef','reviewer','lowongan');
        return view('rekrutmen.request.show', compact('rekrutmenRequest'));
    }

    // ── HRD: Setujui langsung ─────────────────────────────────────────────────
    public function setujui(Request $request, RekrutmenRequest $rekrutmenRequest)
    {
        abort_unless(auth()->user()->hasRole(['hrd','admin']), 403);
        abort_unless($rekrutmenRequest->status === 'menunggu_hrd', 422);

        $rekrutmenRequest->update([
            'status'      => 'disetujui',
            'catatan_hrd' => $request->catatan_hrd,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan SDM disetujui. Silakan buka lowongan.');
    }

    // ── HRD: Eskalasi ke Direktur ─────────────────────────────────────────────
    public function eskalasi(Request $request, RekrutmenRequest $rekrutmenRequest)
    {
        abort_unless(auth()->user()->hasRole(['hrd','admin']), 403);
        abort_unless($rekrutmenRequest->status === 'menunggu_hrd', 422);

        $rekrutmenRequest->update([
            'status'      => 'menunggu_direktur',
            'catatan_hrd' => $request->catatan_hrd,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan diteruskan ke Direktur untuk persetujuan.');
    }

    // ── Direktur / HRD Senior: Final approve ─────────────────────────────────
    public function approve(Request $request, RekrutmenRequest $rekrutmenRequest)
    {
        abort_unless(auth()->user()->hasRole(['hrd','admin']), 403);
        abort_unless($rekrutmenRequest->status === 'menunggu_direktur', 422);

        $rekrutmenRequest->update([
            'status'      => 'disetujui',
            'catatan_hrd' => ($rekrutmenRequest->catatan_hrd ?? '') . "\n[Direktur]: " . $request->catatan,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan SDM disetujui oleh Direktur.');
    }

    // ── Tolak ─────────────────────────────────────────────────────────────────
    public function tolak(Request $request, RekrutmenRequest $rekrutmenRequest)
    {
        abort_unless(auth()->user()->hasRole(['hrd','admin']), 403);
        $request->validate(['catatan_hrd' => 'required|string|max:500']);

        $rekrutmenRequest->update([
            'status'      => 'ditolak',
            'catatan_hrd' => $request->catatan_hrd,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Permintaan SDM ditolak.');
    }
}
