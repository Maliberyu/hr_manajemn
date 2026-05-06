<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\AtasanPegawai;
use App\Models\Departemen;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;

class AtasanPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $depId = $request->departemen;

        $pegawai = Pegawai::aktif()
            ->with(['departemenRef', 'atasanRecord.atasan'])
            ->when($depId, fn($q, $d) => $q->departemen($d))
            ->when($request->q, fn($q, $s) => $q->cari($s))
            ->when($request->belum_diset, fn($q) =>
                $q->whereDoesntHave('atasanRecord')
            )
            ->orderBy('nama')
            ->paginate(30)->withQueryString();

        $departemen = Departemen::orderBy('nama')->pluck('nama', 'dep_id');
        $atasanList = User::whereIn('role', ['atasan', 'hrd', 'admin'])
                          ->orderBy('nama')
                          ->get(['id', 'nama', 'role', 'jabatan']);

        $totalBelumSet = Pegawai::aktif()
            ->whereDoesntHave('atasanRecord')
            ->count();

        return view('pengaturan.atasan.index', compact(
            'pegawai', 'departemen', 'atasanList', 'totalBelumSet'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik'         => 'required|exists:pegawai,nik',
            'user_id'     => 'required|exists:users_hr,id',
            'keterangan'  => 'nullable|max:100',
        ]);

        AtasanPegawai::updateOrCreate(
            ['nik' => $request->nik],
            ['user_id' => $request->user_id, 'keterangan' => $request->keterangan]
        );

        return back()->with('success', 'Atasan langsung berhasil diset.');
    }

    // Simpan banyak sekaligus (dari bulk form)
    public function storeBulk(Request $request)
    {
        $request->validate([
            'atasan'         => 'required|array',
            'atasan.*.nik'   => 'required|exists:pegawai,nik',
            'atasan.*.user_id' => 'nullable|exists:users_hr,id',
        ]);

        $updated = 0;
        foreach ($request->atasan as $item) {
            if (empty($item['user_id'])) {
                AtasanPegawai::where('nik', $item['nik'])->delete();
            } else {
                AtasanPegawai::updateOrCreate(
                    ['nik' => $item['nik']],
                    ['user_id' => $item['user_id'], 'keterangan' => $item['keterangan'] ?? null]
                );
                $updated++;
            }
        }

        return back()->with('success', "{$updated} karyawan berhasil diupdate.");
    }

    public function destroy(Request $request)
    {
        $request->validate(['nik' => 'required']);
        AtasanPegawai::where('nik', $request->nik)->delete();
        return back()->with('success', 'Atasan langsung dihapus.');
    }
}
