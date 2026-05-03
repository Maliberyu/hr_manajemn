<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('pegawai')
            ->when($request->q, fn($q, $s) =>
                $q->where('nama', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('nik', 'like', "%$s%"))
            ->when($request->role, fn($q, $r) => $q->where('role', $r))
            ->orderBy('nama')
            ->paginate(20)->withQueryString();

        return view('pengaturan.users.index', compact('users'));
    }

    public function create()
    {
        $pegawai = Pegawai::aktif()->orderBy('nama')
            ->get(['nik', 'nama', 'jbtn']);

        return view('pengaturan.users.create', compact('pegawai'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'      => 'nullable|exists:pegawai,nik',
            'nama'     => 'required|max:150',
            'email'    => 'required|email|unique:users_hr,email',
            'password' => 'required|min:6|confirmed',
            'jabatan'  => 'nullable|max:100',
            'role'     => 'required|in:karyawan,atasan,hrd,admin',
            'status'   => 'required|in:aktif,nonaktif',
        ]);

        User::create([
            ...$validated,
            'password'      => Hash::make($validated['password']),
            'auth_provider' => 'local',
        ]);

        return redirect()->route('pengaturan.users.index')
            ->with('success', "User {$validated['nama']} berhasil dibuat.");
    }

    public function edit(User $user)
    {
        $user->load('pegawai');
        $pegawai = Pegawai::aktif()->orderBy('nama')
            ->get(['nik', 'nama', 'jbtn']);

        return view('pengaturan.users.edit', compact('user', 'pegawai'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nik'     => 'nullable|exists:pegawai,nik',
            'nama'    => 'required|max:150',
            'email'   => "required|email|unique:users_hr,email,{$user->id}",
            'jabatan' => 'nullable|max:100',
            'role'    => 'required|in:karyawan,atasan,hrd,admin',
            'status'  => 'required|in:aktif,nonaktif',
        ]);

        $user->update($validated);

        return redirect()->route('pengaturan.users.index')
            ->with('success', "User {$user->nama} berhasil diperbarui.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['password' => 'required|min:6|confirmed']);

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', "Password {$user->nama} berhasil direset.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Tidak bisa menghapus akun sendiri.']);
        }

        $nama = $user->nama;
        $user->delete();

        return redirect()->route('pengaturan.users.index')
            ->with('success', "User {$nama} berhasil dihapus.");
    }
}
