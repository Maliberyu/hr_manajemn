<?php

namespace App\Http\Controllers\Shift;

use App\Http\Controllers\Controller;
use App\Models\ShiftMaster;
use App\Models\ShiftSetting;
use Illuminate\Http\Request;

class ShiftMasterController extends Controller
{
    public function index()
    {
        $list    = ShiftMaster::orderBy('urutan')->get();
        $setting = ShiftSetting::get();
        return view('shift.master.index', compact('list', 'setting'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode'                  => 'required|string|max:30|unique:hr_shift_master,kode',
            'nama'                  => 'required|string|max:100',
            'jam_mulai'             => 'required|date_format:H:i',
            'jam_selesai'           => 'required|date_format:H:i',
            'melewati_tengah_malam' => 'boolean',
            'multiplier_lembur'     => 'required|numeric|min:0.5|max:5',
            'urutan'                => 'nullable|integer|min:0',
        ]);

        $kode = strtolower(preg_replace('/\s+/', '_', trim($validated['kode'])));
        ShiftMaster::create([
            ...$validated,
            'kode'   => $kode,
            'aktif'  => true,
        ]);

        return back()->with('success', "Shift \"{$validated['nama']}\" berhasil ditambahkan.");
    }

    public function update(Request $request, ShiftMaster $shiftMaster)
    {
        $validated = $request->validate([
            'nama'                  => 'required|string|max:100',
            'jam_mulai'             => 'required|date_format:H:i',
            'jam_selesai'           => 'required|date_format:H:i',
            'melewati_tengah_malam' => 'boolean',
            'multiplier_lembur'     => 'required|numeric|min:0.5|max:5',
            'urutan'                => 'nullable|integer|min:0',
        ]);

        $shiftMaster->update($validated);
        return back()->with('success', "Shift \"{$shiftMaster->nama}\" berhasil diperbarui.");
    }

    public function toggle(ShiftMaster $shiftMaster)
    {
        $shiftMaster->update(['aktif' => !$shiftMaster->aktif]);
        $status = $shiftMaster->aktif ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Shift \"{$shiftMaster->nama}\" berhasil {$status}.");
    }

    public function updateSetting(Request $request)
    {
        $request->validate([
            'toleransi_mismatch_menit'    => 'required|integer|min:0|max:120',
            'max_tukar_shift_per_bulan'   => 'required|integer|min:1|max:10',
            'wajib_approval_double_shift' => 'boolean',
            'notif_mismatch_ke_atasan'    => 'boolean',
        ]);

        ShiftSetting::get()->update([
            'toleransi_mismatch_menit'    => $request->toleransi_mismatch_menit,
            'max_tukar_shift_per_bulan'   => $request->max_tukar_shift_per_bulan,
            'wajib_approval_double_shift' => $request->boolean('wajib_approval_double_shift'),
            'notif_mismatch_ke_atasan'    => $request->boolean('notif_mismatch_ke_atasan'),
            'updated_by'                  => auth()->id(),
        ]);

        return back()->with('success', 'Setting shift berhasil disimpan.');
    }
}
