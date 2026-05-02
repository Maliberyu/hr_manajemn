@extends('layouts.app')
@section('title', 'Tambah Karyawan')
@section('page-title', 'Tambah Karyawan')
@section('page-subtitle', 'Isi data lengkap karyawan baru')

@section('content')

<div class="mb-5 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('karyawan.index') }}" class="hover:text-blue-600 transition">Master Karyawan</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium">Tambah Karyawan</span>
</div>

<form method="POST" action="{{ route('karyawan.store') }}" enctype="multipart/form-data">
    @csrf

    @include('karyawan._form')

    <div class="mt-6 flex items-center justify-end gap-3">
        <a href="{{ route('karyawan.index') }}"
           class="px-5 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
            Batal
        </a>
        <button type="submit"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
            Simpan Karyawan
        </button>
    </div>
</form>

@endsection
