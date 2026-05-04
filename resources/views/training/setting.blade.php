@extends('layouts.app')
@section('title', 'Setting Training')
@section('page-title', 'Setting Training')
@section('page-subtitle', 'Konfigurasi logo untuk sertifikat IHT')

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Logo Rumah Sakit (untuk sertifikat IHT)</p>

        @if($logoUrl)
        <div class="mb-4 flex items-center gap-4 p-3 bg-gray-50 rounded-xl">
            <img src="{{ $logoUrl }}" alt="Logo RS" class="h-16 object-contain">
            <div>
                <p class="text-xs font-medium text-gray-700">Logo saat ini</p>
                <p class="text-xs text-gray-400 mt-0.5">Akan muncul di pojok kiri atas sertifikat</p>
            </div>
        </div>
        @else
        <div class="mb-4 p-3 bg-gray-50 rounded-xl text-center">
            <p class="text-xs text-gray-400">Belum ada logo. Sertifikat akan menampilkan teks "RSIA Respati".</p>
        </div>
        @endif

        <form method="POST" action="{{ route('training.setting.update') }}" enctype="multipart/form-data">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-1">Upload Logo Baru (PNG / JPG / SVG, maks 2MB)</label>
                <input type="file" name="logo_rs" accept=".png,.jpg,.jpeg,.svg"
                       class="w-full text-sm text-gray-600 border border-gray-200 rounded-xl px-3 py-2
                              file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                              file:text-xs file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                @error('logo_rs')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit"
                    class="mt-3 w-full py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                Simpan Logo
            </button>
        </form>
    </div>

    <div class="flex gap-2">
        <a href="{{ route('training.iht.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 transition">← Kembali ke IHT</a>
        <span class="text-gray-300">·</span>
        <a href="{{ route('training.eksternal.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 transition">Training Eksternal</a>
    </div>
</div>
@endsection
