@extends('layouts.app')
@section('title', 'Jadwalkan Interview')
@section('page-title', 'Jadwalkan Interview')
@section('page-subtitle', 'Formulir penjadwalan sesi interview')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-800">Jadwalkan Sesi Interview</h2>
                <p class="text-xs text-gray-500">Atur jadwal interview untuk pelamar</p>
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

        <form method="POST" action="{{ route('rekrutmen.interview.store') }}" class="space-y-4">
            @csrf

            {{-- Pelamar --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Pelamar <span class="text-red-500">*</span>
                </label>
                <select name="pelamar_id" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Pelamar --</option>
                    @foreach($pelamar as $p)
                    <option value="{{ $p->id }}"
                        {{ old('pelamar_id', $selectedPelamar) == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} &mdash; {{ $p->lowongan?->posisi ?? 'N/A' }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Tahap & Label --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Tahap ke- <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="tahap" required min="1" max="10"
                           value="{{ old('tahap', 1) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Label Tahap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="label_tahap" required maxlength="100"
                           value="{{ old('label_tahap', 'HR Interview') }}"
                           placeholder="Contoh: HR Interview, Technical Test..."
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            {{-- Jadwal --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Jadwal Interview <span class="text-red-500">*</span>
                </label>
                <input type="datetime-local" name="jadwal" required
                       value="{{ old('jadwal') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Metode --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Metode <span class="text-red-500">*</span>
                </label>
                <select name="metode" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Metode --</option>
                    <option value="online" {{ old('metode') === 'online' ? 'selected' : '' }}>Online (Video Call)</option>
                    <option value="offline" {{ old('metode') === 'offline' ? 'selected' : '' }}>Offline (Tatap Muka)</option>
                </select>
            </div>

            {{-- Lokasi / Link --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Lokasi / Link Meeting <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <input type="text" name="lokasi_atau_link" maxlength="255"
                       value="{{ old('lokasi_atau_link') }}"
                       placeholder="Alamat kantor atau link Zoom/Google Meet..."
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Pewawancara --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Pewawancara <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <select name="pewawancara_id"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Pewawancara --</option>
                    @foreach($pewawancaraList as $pw)
                    <option value="{{ $pw->id }}" {{ old('pewawancara_id') == $pw->id ? 'selected' : '' }}>
                        {{ $pw->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Jadwalkan Interview
                </button>
                <a href="{{ route('rekrutmen.interview.index') }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
