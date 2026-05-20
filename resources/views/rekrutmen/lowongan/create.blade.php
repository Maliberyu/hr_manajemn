@extends('layouts.app')
@section('title', 'Buka Lowongan')
@section('page-title', 'Buka Lowongan')
@section('page-subtitle', 'Formulir pembukaan lowongan rekrutmen')

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- Banner dari Request --}}
    @if($requestRef)
    <div class="mb-4 px-4 py-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl text-sm flex items-start gap-2">
        <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p class="font-semibold">Membuka lowongan dari Permintaan SDM</p>
            <p class="text-xs text-blue-600 mt-0.5">
                {{ $requestRef->no_request }} &mdash; {{ $requestRef->posisi }}
                (Diajukan oleh {{ $requestRef->pengaju?->nama }})
            </p>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-800">Buka Lowongan Baru</h2>
                <p class="text-xs text-gray-500">Lengkapi detail lowongan rekrutmen</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <p class="font-semibold mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('rekrutmen.lowongan.store') }}" class="space-y-4">
            @csrf

            @if($requestRef)
            <input type="hidden" name="request_id" value="{{ $requestRef->id }}">
            @endif

            {{-- Posisi --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Posisi <span class="text-red-500">*</span>
                </label>
                <input type="text" name="posisi" required maxlength="100"
                       value="{{ old('posisi', $requestRef?->posisi) }}"
                       placeholder="Nama posisi yang dibuka"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Departemen --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Departemen <span class="text-red-500">*</span>
                </label>
                <select name="departemen_id" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departemen as $dep)
                    <option value="{{ $dep->dep_id }}"
                        {{ old('departemen_id', $requestRef?->departemen_id) == $dep->dep_id ? 'selected' : '' }}>
                        {{ $dep->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Kuota --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Kuota <span class="text-red-500">*</span>
                </label>
                <input type="number" name="kuota" required min="1" max="999"
                       value="{{ old('kuota', $requestRef?->jumlah ?? 1) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Tanggal --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Tanggal Buka <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tgl_buka" required
                           value="{{ old('tgl_buka', today()->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Tanggal Tutup <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tgl_tutup" required
                           value="{{ old('tgl_tutup') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi Pekerjaan</label>
                <textarea name="deskripsi" rows="4" maxlength="2000"
                          placeholder="Deskripsi tugas, tanggung jawab, dan lingkup pekerjaan..."
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('deskripsi') }}</textarea>
            </div>

            {{-- Syarat --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Persyaratan</label>
                <textarea name="syarat" rows="4" maxlength="2000"
                          placeholder="Kualifikasi, pengalaman, pendidikan, dan persyaratan lainnya..."
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('syarat') }}</textarea>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Buka Lowongan
                </button>
                <a href="{{ route('rekrutmen.lowongan.index') }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
