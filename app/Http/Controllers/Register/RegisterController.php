<?php

namespace App\Http\Controllers\Register;

use App\Http\Controllers\Controller;
use App\Models\AtasanPegawai;
use App\Models\HrNotification;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function searchPegawai(\Illuminate\Http\Request $request)
    {
        $q = $request->q;
        $pegawai = Pegawai::aktif()
            ->where(fn($query) =>
                $query->where('nama', 'like', "%{$q}%")
                      ->orWhere('nik',  'like', "%{$q}%")
            )
            ->limit(10)
            ->get(['id', 'nik', 'nama', 'jbtn', 'photo']);

        return response()->json($pegawai->map(fn($p) => [
            'nik'  => $p->nik,
            'nama' => $p->nama,
            'jbtn' => $p->jbtn,
            'foto' => $p->foto_url,
        ]));
    }

    public function showRegister()
    {
        $calonAtasan = User::whereIn('role', ['atasan', 'hrd', 'admin'])
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'jabatan', 'role']);

        return view('auth.register', compact('calonAtasan'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'nama'           => 'required|string|max:150',
            'email'          => 'required|email|unique:users_hr,email',
            'jabatan'        => 'required|string|max:100',
            'password'       => 'required|min:8|confirmed',
            'nik'            => 'nullable|exists:pegawai,nik',
            'atasan_user_id' => 'nullable|exists:users_hr,id',
        ], [
            'email.unique'     => 'Email ini sudah terdaftar.',
            'password.min'     => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nik.exists'       => 'NIK pegawai tidak ditemukan di sistem.',
        ]);

        // Cek NIK tidak dipakai akun lain
        if ($request->nik) {
            $nikDipakai = User::where('nik', $request->nik)->exists();
            if ($nikDipakai) {
                return back()->withErrors(['nik' => 'NIK ini sudah terhubung ke akun lain.'])->withInput();
            }
        }

        $user = User::create([
            'nama'          => $request->nama,
            'email'         => $request->email,
            'jabatan'       => $request->jabatan,
            'nik'           => $request->nik ?: null,
            'password'      => Hash::make($request->password),
            'auth_provider' => 'local',
            'status'        => 'aktif',
            'role'          => 'karyawan',  // default; HRD/admin ubah sesuai kebutuhan
        ]);

        // Set atasan langsung jika pegawai dipilih
        if ($request->nik && $request->atasan_user_id) {
            AtasanPegawai::updateOrCreate(
                ['nik' => $request->nik],
                ['user_id' => $request->atasan_user_id, 'keterangan' => 'Diset saat registrasi']
            );
        }

        // Notifikasi ke semua HRD & Admin
        $pegawaiInfo = $request->nik
            ? ' (NIK: ' . $request->nik . ' — ' . (Pegawai::where('nik', $request->nik)->value('nama') ?? '-') . ')'
            : '';

        HrNotification::kirimKeHrd(
            'user_registered',
            'Pendaftaran Akun Baru',
            "User baru \"{$user->nama}\" ({$user->email}) telah mendaftar{$pegawaiInfo}. Tentukan hak aksesnya.",
            route('pengaturan.users.index')
        );

        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Silakan masuk. Hak akses akan ditentukan oleh HRD.');
    }
}
