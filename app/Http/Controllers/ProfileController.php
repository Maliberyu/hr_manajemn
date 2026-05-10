<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user    = auth()->user();
        $pegawai = $user->pegawai;

        $atasan = null;
        if ($user->nik) {
            $atasan = \App\Models\AtasanPegawai::where('nik', $user->nik)
                ->with('atasan')
                ->first()
                ?->atasan;
        }

        return view('profil.index', compact('user', 'pegawai', 'atasan'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama'     => 'required',
            'password_baru'     => 'required|min:8|confirmed',
        ], [
            'password_baru.min'       => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return back()->withErrors(['password_lama' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => bcrypt($request->password_baru)]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
