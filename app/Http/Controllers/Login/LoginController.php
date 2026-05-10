<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'User tidak ditemukan.'])->withInput();
        }

        if ($user->status !== 'aktif') {
            return back()->withErrors(['email' => 'Akun tidak aktif. Hubungi administrator.'])->withInput();
        }

        $loginOk = false;

        try {
            $loginOk = Auth::attempt($request->only('email', 'password'));
        } catch (\RuntimeException $e) {
            // Password lama (MD5 dari SIK) — coba verifikasi manual
            if (str_contains($e->getMessage(), 'Bcrypt algorithm')) {
                $legacyMatch = hash_equals($user->password, md5($request->password))
                    || hash_equals($user->password, sha1($request->password))
                    || $user->password === $request->password; // plain text fallback

                if ($legacyMatch) {
                    // Migrate password ke bcrypt sekarang
                    $user->update(['password' => bcrypt($request->password)]);
                    Auth::login($user);
                    $loginOk = true;
                }
            }
        }

        if ($loginOk) {
            $request->session()->regenerate();

            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            $redirect = match($user->role ?? 'karyawan') {
                'karyawan' => route('ess.dashboard'),
                default    => route('dashboard'),
            };

            return redirect()->intended($redirect);
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
