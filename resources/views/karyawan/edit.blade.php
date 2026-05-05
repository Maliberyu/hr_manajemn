@extends('layouts.app')
@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Data Karyawan')
@section('page-subtitle', $karyawan->nama)

@section('content')

<div class="mb-5 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('karyawan.index') }}" class="hover:text-blue-600 transition">Master Karyawan</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('karyawan.show', $karyawan) }}" class="hover:text-blue-600 transition">{{ $karyawan->nama }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium">Edit</span>
</div>

{{-- Form nonaktifkan berdiri sendiri di luar form utama --}}
<form id="form-nonaktif"
      method="POST" action="{{ route('karyawan.destroy', $karyawan) }}"
      onsubmit="return confirm('Nonaktifkan {{ $karyawan->nama }}? Data tidak akan dihapus.')">
    @csrf
    @method('DELETE')
</form>

<form method="POST" action="{{ route('karyawan.update', $karyawan) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @include('karyawan._form', ['karyawan' => $karyawan])

    <div class="mt-6 flex items-center justify-between">
        <button type="submit" form="form-nonaktif"
                class="px-4 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition">
            Nonaktifkan
        </button>
        <div class="flex items-center gap-3">
            <a href="{{ route('karyawan.show', $karyawan) }}"
               class="px-5 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                Simpan Perubahan
            </button>
        </div>
    </div>
</form>

@endsection
