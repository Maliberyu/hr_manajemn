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

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();


        if (!$user) {
            return back()->withErrors([
                'email' => 'User tidak ditemukan'
            ]);
        }


        if ($user->status !== 'aktif') {
            return back()->withErrors([
                'email' => 'Akun tidak aktif. Hubungi administrator.'
            ]);
        }


        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();


            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip()
            ]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah'
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
