@extends('layouts.app')
@section('title', 'Detail Interview')
@section('page-title', 'Detail Interview')
@section('page-subtitle', $interview->label_tahap ?? 'Interview Tahap ' . $interview->tahap)

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

@php
    $iv = $interview;
    $ivColor = match($iv->status) {
        'dijadwalkan' => 'blue',
        'selesai'     => 'green',
        'batal'       => 'red',
        default       => 'gray',
    };
    $avgNilai = $iv->penilaian->avg('nilai');
    $sudahMenilai = $iv->penilaian->contains('penilai_id', auth()->id());
@endphp

{{-- Flash ─────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Kolom Kanan: Detail & Aksi ──────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-800">{{ $iv->label_tahap ?? 'Interview Tahap ' . $iv->tahap }}</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Tahap ke-{{ $iv->tahap }}</p>
                </div>
                <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $ivColor }}-700 bg-{{ $ivColor }}-50 whitespace-nowrap">
                    {{ ucfirst($iv->status) }}
                </span>
            </div>

            <div class="space-y-2.5 text-sm">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Jadwal</p>
                    <p class="font-semibold text-gray-700">
                        {{ $iv->jadwal ? \Carbon\Carbon::parse($iv->jadwal)->translatedFormat('d F Y, H:i') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Metode</p>
                    <p class="font-semibold text-gray-700">{{ ucfirst($iv->metode ?? '-') }}</p>
                </div>
                @if($iv->lokasi_atau_link)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Lokasi / Link</p>
                    <p class="text-gray-700 text-xs">{{ $iv->lokasi_atau_link }}</p>
                </div>
                @endif
                @if($iv->pewawancara)
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Pewawancara</p>
                    <p class="font-semibold text-gray-700">{{ $iv->pewawancara->nama }}</p>
                </div>
                @endif
                @if($avgNilai && $iv->status === 'selesai')
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Nilai Rata-rata</p>
                    <p class="font-bold text-purple-600 text-lg">{{ number_format($avgNilai, 1) }}<span class="text-xs font-normal text-gray-400">/100</span></p>
                </div>
                @endif
            </div>
        </div>

        {{-- Info Pelamar --}}
        @if($iv->pelamar)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Data Pelamar</h3>
            <p class="font-semibold text-gray-800">{{ $iv->pelamar->nama }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $iv->pelamar->lowongan?->posisi ?? '-' }}</p>
            @if($iv->pelamar->email)
            <p class="text-xs text-gray-400 mt-1">{{ $iv->pelamar->email }}</p>
            @endif
            @if($iv->pelamar->no_hp)
            <p class="text-xs text-gray-400">{{ $iv->pelamar->no_hp }}</p>
            @endif
            <a href="{{ route('rekrutmen.pelamar.show', $iv->pelamar) }}"
               class="mt-3 text-xs text-blue-600 hover:underline flex items-center gap-1">
                Lihat profil pelamar →
            </a>
        </div>
        @endif

        {{-- Tombol Aksi (jika dijadwalkan) --}}
        @if($iv->status === 'dijadwalkan')
        <div class="bg-white rounded-2xl border border-yellow-100 shadow-sm p-5 space-y-3"
             x-data="{ showSelesai: false, showBatal: false }">
            <p class="text-sm font-semibold text-gray-700">Tindakan</p>

            {{-- Default --}}
            <div x-show="!showSelesai && !showBatal">
                <button type="button" @click="showSelesai = true"
                        class="w-full py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition mb-2">
                    Tandai Selesai
                </button>
                <button type="button" @click="showBatal = true"
                        class="w-full py-2 text-sm bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl transition font-medium">
                    Batalkan Interview
                </button>
            </div>

            {{-- Form Selesai --}}
            <div x-show="showSelesai" x-cloak>
                <form method="POST" action="{{ route('rekrutmen.interview.selesai', $iv) }}" class="space-y-2">
                    @csrf
                    <p class="text-xs text-green-700 font-medium">Catatan penyelesaian interview:</p>
                    <textarea name="catatan" rows="3" maxlength="500" placeholder="Ringkasan hasil interview (opsional)..."
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                            Konfirmasi Selesai
                        </button>
                        <button type="button" @click="showSelesai = false"
                                class="px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form Batal --}}
            <div x-show="showBatal" x-cloak>
                <form method="POST" action="{{ route('rekrutmen.interview.batal', $iv) }}" class="space-y-2">
                    @csrf
                    <p class="text-xs text-red-600 font-medium">Alasan pembatalan:</p>
                    <textarea name="catatan" rows="3" maxlength="500" placeholder="Alasan pembatalan interview..."
                              class="w-full px-3 py-2 text-sm border border-red-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition">
                            Konfirmasi Batal
                        </button>
                        <button type="button" @click="showBatal = false"
                                class="px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                            Tutup
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Kembali --}}
        <a href="{{ route('rekrutmen.interview.index') }}"
           class="flex items-center justify-center w-full py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
            ← Kembali ke Daftar
        </a>
    </div>

    {{-- ── Kolom Kanan: Penilaian ───────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Daftar Penilaian --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Penilaian Interview</h3>
                @if($iv->penilaian->count() && $avgNilai)
                <span class="text-xs text-gray-400">Rata-rata: <span class="font-semibold text-purple-600">{{ number_format($avgNilai, 1) }}</span></span>
                @endif
            </div>

            @if($iv->penilaian->isEmpty())
            <div class="flex flex-col items-center gap-2 py-8 text-gray-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm">Belum ada penilaian</p>
            </div>
            @else
            <div class="divide-y divide-gray-50">
                @foreach($iv->penilaian as $pn)
                @php
                    $rekColor = match($pn->rekomendasi) {
                        'diterima'       => 'green',
                        'pertimbangkan'  => 'yellow',
                        'ditolak'        => 'red',
                        default          => 'gray',
                    };
                @endphp
                <div class="px-5 py-4">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $pn->penilai?->nama ?? 'Penilai' }}</p>
                            <p class="text-xs text-gray-400">{{ $pn->created_at?->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold text-purple-600">{{ $pn->nilai }}</span>
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $rekColor }}-700 bg-{{ $rekColor }}-50">
                                {{ ucfirst($pn->rekomendasi ?? '-') }}
                            </span>
                        </div>
                    </div>
                    @if($pn->catatan)
                    <p class="text-xs text-gray-600 bg-gray-50 px-3 py-2 rounded-lg italic">"{{ $pn->catatan }}"</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Form Tambah Penilaian --}}
        @if($iv->status === 'selesai' && !$sudahMenilai)
        <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Berikan Penilaian Anda</h3>
            <form method="POST" action="{{ route('rekrutmen.interview.penilaian.store', $iv) }}" class="space-y-4">
                @csrf

                {{-- Nilai --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Nilai <span class="text-red-500">*</span>
                        <span class="text-gray-400 font-normal">(0 - 100)</span>
                    </label>
                    <input type="number" name="nilai" required min="0" max="100"
                           value="{{ old('nilai') }}"
                           placeholder="Nilai 0-100"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400">
                </div>

                {{-- Rekomendasi --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Rekomendasi <span class="text-red-500">*</span>
                    </label>
                    <select name="rekomendasi" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                        <option value="">-- Pilih Rekomendasi --</option>
                        <option value="diterima" {{ old('rekomendasi') === 'diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="pertimbangkan" {{ old('rekomendasi') === 'pertimbangkan' ? 'selected' : '' }}>Pertimbangkan</option>
                        <option value="ditolak" {{ old('rekomendasi') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan Penilaian</label>
                    <textarea name="catatan" rows="3" maxlength="1000" placeholder="Komentar, catatan, dan masukan terkait pelamar..."
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400 resize-none">{{ old('catatan') }}</textarea>
                </div>

                <button type="submit"
                        class="w-full py-2.5 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold transition">
                    Kirim Penilaian
                </button>
            </form>
        </div>
        @elseif($iv->status === 'selesai' && $sudahMenilai)
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            Anda sudah memberikan penilaian untuk sesi interview ini.
        </div>
        @endif
    </div>
</div>

@endsection
