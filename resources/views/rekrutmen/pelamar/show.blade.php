@extends('layouts.app')
@section('title', 'Detail Pelamar')
@section('page-title', 'Detail Pelamar')
@section('page-subtitle', $pelamar->nama)

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

@php
    $statusColor = match($pelamar->status) {
        'baru'      => 'blue',
        'screening' => 'yellow',
        'interview' => 'purple',
        'offering'  => 'orange',
        'diterima'  => 'green',
        'ditolak'   => 'red',
        default     => 'gray',
    };
@endphp

{{-- Flash ─────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Kolom Kiri ───────────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Info Pelamar --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-800">{{ $pelamar->nama }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $pelamar->lowongan?->posisi }}</p>
                </div>
                <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $statusColor }}-700 bg-{{ $statusColor }}-50 whitespace-nowrap">
                    {{ ucfirst($pelamar->status) }}
                </span>
            </div>

            <div class="space-y-2.5 text-sm">
                @if($pelamar->email)
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $pelamar->email }}</span>
                </div>
                @endif
                @if($pelamar->no_hp)
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>{{ $pelamar->no_hp }}</span>
                </div>
                @endif
                @if($pelamar->pendidikan_terakhir)
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </svg>
                    <span>{{ $pelamar->pendidikan_terakhir }}</span>
                </div>
                @endif
                @if(!is_null($pelamar->pengalaman_tahun))
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $pelamar->pengalaman_tahun }} tahun pengalaman</span>
                </div>
                @endif
                @if($pelamar->sumber)
                <div class="flex items-center gap-2 text-gray-600">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span>{{ $pelamar->sumber }}</span>
                </div>
                @endif
            </div>

            @if($pelamar->cv_path)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ Storage::url($pelamar->cv_path) }}" target="_blank"
                   class="flex items-center justify-center gap-2 w-full py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download CV
                </a>
            </div>
            @endif
        </div>

        {{-- Update Status --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Update Status</h3>
            <form method="POST" action="{{ route('rekrutmen.pelamar.updateStatus', $pelamar) }}" class="space-y-3">
                @csrf
                <select name="status" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    @foreach(['baru','screening','interview','offering','diterima','ditolak'] as $s)
                    <option value="{{ $s }}" {{ $pelamar->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <textarea name="catatan" rows="2" maxlength="500" placeholder="Catatan (opsional)..."
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
                <button type="submit"
                        class="w-full py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Simpan Status
                </button>
            </form>
        </div>

        {{-- Offering --}}
        @if($pelamar->offering)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Offering</h3>
            @php
                $ofColor = match($pelamar->offering->status) {
                    'diterima' => 'green',
                    'ditolak'  => 'red',
                    default    => 'yellow',
                };
            @endphp
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-xs text-gray-400">Status</span>
                    <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $ofColor }}-700 bg-{{ $ofColor }}-50">
                        {{ ucfirst($pelamar->offering->status) }}
                    </span>
                </div>
                @if($pelamar->offering->gaji_ditawarkan)
                <div class="flex justify-between">
                    <span class="text-xs text-gray-400">Gaji Ditawarkan</span>
                    <span class="text-sm font-semibold text-gray-700">Rp {{ number_format($pelamar->offering->gaji_ditawarkan, 0, ',', '.') }}</span>
                </div>
                @endif
                @if($pelamar->offering->tanggal_offering)
                <div class="flex justify-between">
                    <span class="text-xs text-gray-400">Tanggal Offering</span>
                    <span class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($pelamar->offering->tanggal_offering)->translatedFormat('d M Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        @elseif($pelamar->status === 'interview')
        <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-5">
            <p class="text-sm text-gray-600 mb-3">Pelamar siap menerima penawaran kerja.</p>
            <a href="{{ route('rekrutmen.offering.create', ['pelamar_id' => $pelamar->id]) }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Buat Offering
            </a>
        </div>
        @endif
    </div>

    {{-- ── Kolom Kanan: Interview ───────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Header Actions --}}
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-800">Riwayat Interview</h3>
            <a href="{{ route('rekrutmen.interview.create', ['pelamar_id' => $pelamar->id]) }}"
               class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Jadwalkan Interview
            </a>
        </div>

        @forelse($pelamar->interviews as $iv)
        @php
            $ivColor = match($iv->status) {
                'dijadwalkan' => 'blue',
                'selesai'     => 'green',
                'batal'       => 'red',
                default       => 'gray',
            };
            $avgNilai = $iv->penilaian->avg('nilai');
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Header --}}
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $iv->label_tahap ?? 'Interview Tahap ' . $iv->tahap }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $iv->jadwal ? \Carbon\Carbon::parse($iv->jadwal)->translatedFormat('d F Y, H:i') : '-' }}
                        &bull; {{ $iv->metode ?? '-' }}
                        @if($iv->pewawancara)
                        &bull; {{ $iv->pewawancara->nama }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($avgNilai && $iv->status === 'selesai')
                    <span class="px-2 py-0.5 text-xs rounded-full font-semibold bg-purple-50 text-purple-700">
                        Nilai: {{ number_format($avgNilai, 1) }}
                    </span>
                    @endif
                    <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $ivColor }}-700 bg-{{ $ivColor }}-50">
                        {{ ucfirst($iv->status) }}
                    </span>
                    <a href="{{ route('rekrutmen.interview.show', $iv) }}"
                       class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                        Detail
                    </a>
                </div>
            </div>

            {{-- Penilaian per interviewer --}}
            @if($iv->penilaian->count())
            <div class="px-5 py-3">
                <p class="text-xs text-gray-400 font-medium mb-2">Penilaian</p>
                <div class="space-y-2">
                    @foreach($iv->penilaian as $pn)
                    @php
                        $rekColor = match($pn->rekomendasi) {
                            'diterima'   => 'green',
                            'pertimbangkan' => 'yellow',
                            'ditolak'    => 'red',
                            default      => 'gray',
                        };
                    @endphp
                    <div class="flex items-center justify-between text-xs text-gray-600 py-1.5 border-b border-gray-50 last:border-0">
                        <span class="font-medium">{{ $pn->penilai?->nama ?? 'Penilai' }}</span>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-purple-600">{{ $pn->nilai }}</span>
                            <span class="px-1.5 py-0.5 rounded-full font-medium text-{{ $rekColor }}-700 bg-{{ $rekColor }}-50">
                                {{ ucfirst($pn->rekomendasi) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col items-center gap-2 py-10 text-gray-400">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <p class="text-sm">Belum ada jadwal interview</p>
        </div>
        @endforelse

        <a href="{{ route('rekrutmen.pelamar.index') }}"
           class="flex items-center justify-center w-full py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
            ← Kembali ke Daftar
        </a>
    </div>
</div>

@endsection
