<?php

namespace App\Http\Controllers\Ess;

use App\Http\Controllers\Controller;
use App\Models\KontrakKerja;

class EssKontrakController extends Controller
{
    public function index()
    {
        $pegawai = auth()->user()->pegawai;
        abort_if(!$pegawai, 403, 'Akun belum terhubung ke data pegawai.');

        $kontrakAktif = KontrakKerja::with('jenis')
            ->where('nik', $pegawai->nik)
            ->where('status', 'aktif')
            ->latest('tgl_mulai')
            ->first();

        $riwayat = KontrakKerja::with('jenis')
            ->where('nik', $pegawai->nik)
            ->orderByDesc('tgl_mulai')
            ->get();

        return view('ess.kontrak', compact('pegawai', 'kontrakAktif', 'riwayat'));
    }
}
