@extends('layouts.app')
@section('title', 'Penilaian Kinerja')
@section('page-title', 'Penilaian Kinerja')
@section('page-subtitle', 'Penilaian Prestasi Kerja & 360 Derajat')

@section('content')
<div class="space-y-6">

    {{-- Filter periode --}}
    <form method="GET" action="{{ route('kinerja.index') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Semester</label>
            <select name="semester" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester 1 (Jan–Jun)</option>
                <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester 2 (Jul–Des)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tahun</label>
            <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                @foreach(range(now()->year - 1, now()->year + 1) as $t)
                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            Tampilkan
        </button>
        <a href="{{ route('kinerja.master') }}"
           class="ml-auto px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition">
            ⚙ Master Kriteria & Dimensi
        </a>
    </form>

    {{-- 2 Sub-modul card --}}
    <div class="grid md:grid-cols-2 gap-6">

        {{-- Penilaian Prestasi --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-5 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-base">Penilaian Prestasi Kerja</h2>
                        <p class="text-xs text-blue-200">Evaluasi oleh Atasan Langsung</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="bg-white/15 rounded-xl p-2">
                        <div class="text-xl font-bold">{{ $totalPrestasi }}</div>
                        <div class="text-xs text-blue-100">Total Penilaian</div>
                    </div>
                    <div class="bg-white/15 rounded-xl p-2">
                        <div class="text-xl font-bold">{{ $totalFinalPre }}</div>
                        <div class="text-xs text-blue-100">Sudah Final</div>
                    </div>
                </div>
            </div>
            <div class="p-4 space-y-2">
                <p class="text-xs text-gray-500">
                    Semester {{ $semester }} / {{ $tahun }} &middot; Skala Kecewa–Istimewa (1–5) &middot;
                    7 Kriteria berbobot
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('kinerja.prestasi.index', ['semester' => $semester, 'tahun' => $tahun]) }}"
                       class="flex-1 text-center py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition">
                        Lihat Daftar
                    </a>
                    <a href="{{ route('kinerja.prestasi.create', ['semester' => $semester, 'tahun' => $tahun]) }}"
                       class="px-4 py-2 text-sm border border-blue-200 text-blue-600 hover:bg-blue-50 rounded-xl transition">
                        + Buat
                    </a>
                </div>
            </div>
        </div>

        {{-- Penilaian 360 --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-purple-700 p-5 text-white">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-base">Penilaian 360 Derajat</h2>
                        <p class="text-xs text-purple-200">Multi-rater: Atasan, Rekan, Bawahan, Self</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="bg-white/15 rounded-xl p-2">
                        <div class="text-xl font-bold">{{ $total360 }}</div>
                        <div class="text-xs text-purple-100">Total Sesi</div>
                    </div>
                    <div class="bg-white/15 rounded-xl p-2">
                        <div class="text-xl font-bold">{{ $total360Aktif }}</div>
                        <div class="text-xs text-purple-100">Sedang Berjalan</div>
                    </div>
                </div>
            </div>
            <div class="p-4 space-y-2">
                <p class="text-xs text-gray-500">
                    Semester {{ $semester }} / {{ $tahun }} &middot; 4 Dimensi berbobot &middot;
                    Anonim untuk rekan & bawahan
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('kinerja.360.index', ['semester' => $semester, 'tahun' => $tahun]) }}"
                       class="flex-1 text-center py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-medium transition">
                        Lihat Daftar
                    </a>
                    <a href="{{ route('kinerja.360.create') }}"
                       class="px-4 py-2 text-sm border border-purple-200 text-purple-600 hover:bg-purple-50 rounded-xl transition">
                        + Buat Sesi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
