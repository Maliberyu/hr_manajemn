<?php

namespace App\Http\Controllers\Register;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // tampilkan halaman register 
    public function showRegister()
    {
        return view('auth.register');
    }

    // proses register
    public function register(Request $request)
    {
        try {

            $request->validate([
                'nama' => 'required|string|max:150',
                'email' => 'required|email|unique:users_hr,email',
                'password' => 'required|min:6|confirmed',
                'jabatan' => 'required|string|max:100'
            ]);

            \App\Models\User::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'email_verified' => 0,
                'auth_provider' => 'local',
                'status' => 'active',
                'jabatan' => $request->jabatan,
            ]);

            return redirect()->route('login')->with('success', 'Registrasi berhasil');
        } catch (\Exception $e) {

            return back()->withInput()->with('error', 'Register gagal: ' . $e->getMessage());
        }
    }
}
