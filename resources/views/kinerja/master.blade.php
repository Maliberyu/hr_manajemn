@extends('layouts.app')
@section('title', 'Master Kinerja')
@section('page-title', 'Master Penilaian Kinerja')
@section('page-subtitle', 'Kelola kriteria, sub-indikator, dimensi & aspek 360°')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')
@php
    $defaultTab = 'prestasi';
    if(session('success_360')) $defaultTab = '360';
@endphp
<div x-data="{ tab: '{{ $defaultTab }}' }" class="space-y-4">

    {{-- Tab bar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5 flex gap-1">
        <button @click="tab = 'prestasi'"
                :class="tab==='prestasi' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'"
                class="px-5 py-2 text-xs font-semibold rounded-xl transition">
            Kriteria Prestasi Kerja
        </button>
        <button @click="tab = '360'"
                :class="tab==='360' ? 'bg-purple-600 text-white' : 'text-gray-500 hover:bg-gray-100'"
                class="px-5 py-2 text-xs font-semibold rounded-xl transition">
            Dimensi 360 Derajat
        </button>
    </div>

    {{-- Flash --}}
    @foreach(['success_kriteria','success_360'] as $k)
    @if(session($k))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session($k) }}</div>
    @endif
    @endforeach

    {{-- ══ TAB: PRESTASI ══ --}}
    <div x-show="tab === 'prestasi'" x-cloak class="space-y-4">

        {{-- Bobot Kriteria --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-700">Kriteria & Bobot Penilaian</p>
                @php $totalBobot = $kriteria->sum('bobot'); @endphp
                <span class="text-xs px-2 py-1 rounded-lg {{ abs($totalBobot - 100) < 0.1 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    Total Bobot: {{ $totalBobot }}%
                    {{ abs($totalBobot - 100) < 0.1 ? '✓' : '⚠ Harus = 100%' }}
                </span>
            </div>

            <form method="POST" action="{{ route('kinerja.master.kriteria.bobot') }}">
                @csrf
                <div class="space-y-2 mb-4">
                    @foreach($kriteria as $k)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl {{ !$k->aktif ? 'opacity-50' : '' }}">
                        <span class="text-xs text-gray-400 w-5">{{ $k->urutan }}</span>
                        <p class="flex-1 text-sm font-medium text-gray-800">{{ $k->nama }}</p>
                        <div class="flex items-center gap-2">
                            <input type="number" name="bobot[{{ $k->id }}]"
                                   value="{{ $k->bobot }}" min="0" max="100" step="0.5"
                                   class="w-20 px-2 py-1 text-sm text-right border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            <span class="text-xs text-gray-400">%</span>
                        </div>
                        <form method="POST" action="{{ route('kinerja.master.kriteria.toggle', $k) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs px-2 py-1 rounded-lg {{ $k->aktif ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                                {{ $k->aktif ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition">
                    Simpan Bobot
                </button>
            </form>
        </div>

        {{-- Tambah Kriteria --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-700 mb-3">Tambah Kriteria Baru</p>
            <form method="POST" action="{{ route('kinerja.master.kriteria.store') }}" class="flex gap-3 items-end flex-wrap">
                @csrf
                <div class="flex-1 min-w-40">
                    <label class="block text-xs text-gray-500 mb-1">Nama Kriteria</label>
                    <input type="text" name="nama" required maxlength="100"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div class="w-28">
                    <label class="block text-xs text-gray-500 mb-1">Bobot (%)</label>
                    <input type="number" name="bobot" required min="0" max="100" step="0.5" value="0"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div class="w-24">
                    <label class="block text-xs text-gray-500 mb-1">Urutan</label>
                    <input type="number" name="urutan" value="{{ ($kriteria->max('urutan') ?? 0) + 10 }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition">
                    Tambah
                </button>
            </form>
        </div>

        {{-- Sub-Indikator per Kriteria --}}
        @foreach($kriteria as $k)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5" x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between text-sm font-semibold text-gray-700">
                <span>{{ $k->nama }} — Sub-Indikator ({{ $k->subIndikator->count() }})</span>
                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-4 space-y-2">
                @forelse($k->subIndikator as $sub)
                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-xl text-sm">
                    <span class="flex-1 text-gray-700">{{ $sub->nama }}</span>
                    <form method="POST" action="{{ route('kinerja.master.sub.destroy', $sub) }}"
                          onsubmit="return confirm('Hapus sub-indikator ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                    </form>
                </div>
                @empty
                <p class="text-xs text-gray-400">Belum ada sub-indikator. Tambahkan panduan penilaian di bawah.</p>
                @endforelse
                <form method="POST" action="{{ route('kinerja.master.sub.store') }}" class="flex gap-2 mt-2">
                    @csrf
                    <input type="hidden" name="kriteria_id" value="{{ $k->id }}">
                    <input type="text" name="nama" required maxlength="255" placeholder="Tambah sub-indikator..."
                           class="flex-1 px-3 py-1.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <button type="submit" class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">+ Tambah</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══ TAB: 360° ══ --}}
    <div x-show="tab === '360'" x-cloak class="space-y-4">

        {{-- Bobot Rater --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-700 mb-4">Bobot Suara Per Hubungan Rater</p>
            <form method="POST" action="{{ route('kinerja.master.bobot.rater') }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                @foreach(['atasan' => 'Atasan Langsung', 'rekan' => 'Rekan Sejawat', 'bawahan' => 'Bawahan', 'self' => 'Diri Sendiri'] as $key => $label)
                <div>
                    <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                    <div class="relative">
                        <input type="number" name="bobot[{{ $key }}]" min="0" max="100" step="5"
                               value="{{ $bobotRater[$key]->bobot ?? 0 }}"
                               class="w-24 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none text-right">
                        <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400">%</span>
                    </div>
                </div>
                @endforeach
                <div class="self-end">
                    <p class="text-xs text-gray-400 mb-1">Total: {{ collect($bobotRater)->sum('bobot') }}%</p>
                    <button type="submit" class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-medium transition">
                        Simpan Bobot
                    </button>
                </div>
            </form>
        </div>

        {{-- Aspek per Dimensi --}}
        @foreach($dimensi as $dim)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5" x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between">
                <div class="text-left">
                    <p class="text-sm font-semibold text-gray-700">{{ $dim->nama }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Bobot {{ $dim->bobot }}% &middot;
                        Rater: {{ implode(', ', $dim->untuk_rater ?? []) }} &middot;
                        {{ $dim->aspek->count() }} aspek
                    </p>
                </div>
                <svg class="w-4 h-4 transition-transform flex-shrink-0" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-collapse class="mt-4 space-y-2">
                @foreach($dim->aspek as $aspek)
                <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-xl text-sm">
                    <span class="flex-1 text-gray-700">{{ $aspek->nama }}</span>
                    <form method="POST" action="{{ route('kinerja.master.aspek.destroy', $aspek) }}"
                          onsubmit="return confirm('Hapus aspek ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                    </form>
                </div>
                @endforeach
                <form method="POST" action="{{ route('kinerja.master.aspek.store') }}" class="flex gap-2 mt-2">
                    @csrf
                    <input type="hidden" name="dimensi_id" value="{{ $dim->id }}">
                    <input type="text" name="nama" required maxlength="255" placeholder="Tambah aspek..."
                           class="flex-1 px-3 py-1.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none">
                    <button type="submit" class="px-3 py-1.5 text-xs bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition">+ Tambah</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
