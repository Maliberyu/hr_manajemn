@extends('layouts.app')
@section('title', 'Akun Belum Terhubung')
@section('page-title', 'Portal Karyawan (ESS)')
@section('page-subtitle', 'Self Service')

@section('content')
<div class="max-w-lg mx-auto mt-10">
    <div class="bg-white rounded-2xl border border-yellow-200 shadow-sm p-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Akun Belum Terhubung ke Data Karyawan</h2>
        <p class="text-sm text-gray-500 mb-6">
            Akun login Anda belum dihubungkan ke data pegawai di sistem.<br>
            Hubungi Admin atau HRD untuk menautkan NIK karyawan ke akun Anda.
        </p>
        <div class="bg-gray-50 rounded-xl p-4 text-left text-xs text-gray-500 mb-6 space-y-1">
            <p><span class="font-medium text-gray-700">Akun:</span> {{ auth()->user()->nama }}</p>
            <p><span class="font-medium text-gray-700">Email:</span> {{ auth()->user()->email }}</p>
            <p><span class="font-medium text-gray-700">Role:</span> {{ auth()->user()->role_label }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="px-6 py-2.5 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-semibold transition">
                Keluar
            </button>
        </form>
    </div>
</div>
@endsection
