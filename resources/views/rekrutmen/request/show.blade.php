@extends('layouts.app')
@section('title', 'Detail Permintaan SDM')
@section('page-title', 'Detail Permintaan SDM')
@section('page-subtitle', $rekrutmenRequest->no_request)

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

@php
    $req = $rekrutmenRequest;
    $isHrd = auth()->user()->hasRole(['hrd','admin']);
    $statusColor = match($req->status) {
        'menunggu_hrd'       => 'yellow',
        'menunggu_direktur'  => 'purple',
        'disetujui'          => 'green',
        'ditolak'            => 'red',
        default              => 'gray',
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

    {{-- ── Kolom Kiri: Info Request ────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Header Detail --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="font-mono text-sm text-gray-400">{{ $req->no_request }}</p>
                    <h2 class="text-lg font-bold text-gray-800 mt-0.5">{{ $req->posisi }}</h2>
                    <p class="text-xs text-gray-400 mt-1">
                        Diajukan {{ $req->created_at->translatedFormat('d F Y, H:i') }}
                        oleh <span class="font-medium text-gray-600">{{ $req->pengaju?->nama }}</span>
                    </p>
                </div>
                <span class="px-3 py-1.5 text-sm font-semibold rounded-full text-{{ $statusColor }}-700 bg-{{ $statusColor }}-50 whitespace-nowrap">
                    {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Departemen</p>
                    <p class="font-semibold text-gray-700">{{ $req->departemenRef?->nama ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Jumlah Kebutuhan</p>
                    <p class="font-bold text-blue-600 text-base">{{ $req->jumlah }} orang</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Tanggal Dibutuhkan</p>
                    <p class="font-semibold text-gray-700">
                        {{ $req->tanggal_dibutuhkan ? \Carbon\Carbon::parse($req->tanggal_dibutuhkan)->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-xs text-gray-400 mb-0.5">Alasan / Justifikasi</p>
                    <p class="text-gray-700 leading-relaxed">{{ $req->alasan }}</p>
                </div>
            </div>
        </div>

        {{-- Reviewer Info --}}
        @if($req->reviewer)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Informasi Reviewer</h3>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">{{ $req->reviewer->nama }}</p>
                    <p class="text-xs text-gray-500">Reviewer</p>
                </div>
                @if($req->catatan_hrd)
                <div class="ml-auto max-w-xs">
                    <p class="text-xs text-gray-400 mb-0.5">Catatan</p>
                    <p class="text-xs text-gray-600 bg-gray-50 px-3 py-2 rounded-lg italic">"{{ $req->catatan_hrd }}"</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Related Lowongan --}}
        @if($req->lowongan && $req->lowongan->count())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Lowongan Terkait</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($req->lowongan as $lw)
                @php
                    $lwColor = match($lw->status) {
                        'aktif'   => 'green',
                        'ditutup' => 'gray',
                        'draft'   => 'yellow',
                        default   => 'blue',
                    };
                @endphp
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition">
                    <div>
                        <p class="font-mono text-xs text-gray-400">{{ $lw->no_lowongan }}</p>
                        <p class="text-sm font-medium text-gray-800">{{ $lw->posisi }}</p>
                        <p class="text-xs text-gray-500">Kuota: {{ $lw->kuota }} | Pelamar: {{ $lw->pelamar_count ?? $lw->pelamar->count() }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $lwColor }}-700 bg-{{ $lwColor }}-50">
                            {{ ucfirst($lw->status) }}
                        </span>
                        <a href="{{ route('rekrutmen.lowongan.show', $lw) }}"
                           class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                            Lihat
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── Kolom Kanan: Aksi ───────────────────────────────────────────────── --}}
    <div class="space-y-4"
         x-data="{ showEskalasi: false, showTolak: false }">

        {{-- Aksi HRD: status menunggu_hrd --}}
        @if($req->status === 'menunggu_hrd' && $isHrd)
        <div class="bg-white rounded-2xl border border-yellow-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Tindakan HRD</p>
            <p class="text-xs text-gray-500">Tinjau permintaan SDM ini dan tentukan tindakan lanjut.</p>

            {{-- Setujui Langsung --}}
            <div x-show="!showEskalasi && !showTolak">
                <form method="POST" action="{{ route('rekrutmen.request.setujui', $req) }}" class="space-y-2">
                    @csrf
                    <textarea name="catatan_hrd" rows="2" maxlength="500" placeholder="Catatan HRD (opsional)..."
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"></textarea>
                    <button type="submit"
                            class="w-full py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                        Setujui Langsung
                    </button>
                </form>
                <div class="flex gap-2 mt-2">
                    <button type="button" @click="showEskalasi = true"
                            class="flex-1 py-2 text-sm bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-xl transition font-medium">
                        Eskalasi ke Direktur
                    </button>
                    <button type="button" @click="showTolak = true"
                            class="flex-1 py-2 text-sm bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl transition font-medium">
                        Tolak
                    </button>
                </div>
            </div>

            {{-- Form Eskalasi --}}
            <div x-show="showEskalasi" x-cloak>
                <form method="POST" action="{{ route('rekrutmen.request.eskalasi', $req) }}" class="space-y-2">
                    @csrf
                    <p class="text-xs text-purple-700 font-medium">Eskalasi ke Direktur untuk persetujuan final:</p>
                    <textarea name="catatan_hrd" rows="3" maxlength="500" placeholder="Alasan eskalasi / catatan untuk direktur..."
                              class="w-full px-3 py-2 text-sm border border-purple-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400 resize-none"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold transition">
                            Eskalasi ke Direktur
                        </button>
                        <button type="button" @click="showEskalasi = false"
                                class="px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form Tolak --}}
            <div x-show="showTolak" x-cloak>
                <form method="POST" action="{{ route('rekrutmen.request.tolak', $req) }}" class="space-y-2">
                    @csrf
                    <p class="text-xs text-red-600 font-medium">Masukkan alasan penolakan:</p>
                    <textarea name="catatan_hrd" rows="3" required maxlength="500" placeholder="Alasan penolakan..."
                              class="w-full px-3 py-2 text-sm border border-red-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition">
                            Konfirmasi Tolak
                        </button>
                        <button type="button" @click="showTolak = false"
                                class="px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Aksi HRD: status menunggu_direktur --}}
        @if($req->status === 'menunggu_direktur' && $isHrd)
        <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Tindakan Final (Direktur)</p>

            <div x-show="!showTolak">
                <form method="POST" action="{{ route('rekrutmen.request.approve', $req) }}" class="space-y-2">
                    @csrf
                    <textarea name="catatan_hrd" rows="2" maxlength="500" placeholder="Catatan persetujuan final (opsional)..."
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
                    <button type="submit"
                            class="w-full py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                        Final Approve
                    </button>
                </form>
                <button type="button" @click="showTolak = true"
                        class="w-full mt-2 py-2 text-sm bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-xl transition font-medium">
                    Tolak
                </button>
            </div>

            <div x-show="showTolak" x-cloak>
                <form method="POST" action="{{ route('rekrutmen.request.tolak', $req) }}" class="space-y-2">
                    @csrf
                    <p class="text-xs text-red-600 font-medium">Alasan penolakan:</p>
                    <textarea name="catatan_hrd" rows="3" required maxlength="500" placeholder="Alasan penolakan..."
                              class="w-full px-3 py-2 text-sm border border-red-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition">
                            Konfirmasi Tolak
                        </button>
                        <button type="button" @click="showTolak = false"
                                class="px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Buka Lowongan jika disetujui --}}
        @if($req->status === 'disetujui')
        <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-700 mb-1">Permintaan Disetujui</p>
            <p class="text-xs text-gray-500 mb-3">Anda dapat membuka lowongan rekrutmen berdasarkan permintaan ini.</p>
            <a href="{{ route('rekrutmen.lowongan.create', ['request_id' => $req->id]) }}"
               class="flex items-center justify-center gap-2 w-full py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buka Lowongan dari Request Ini
            </a>
        </div>
        @endif

        {{-- Kembali --}}
        <a href="{{ route('rekrutmen.request.index') }}"
           class="flex items-center justify-center gap-2 w-full py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
            ← Kembali ke Daftar
        </a>
    </div>
</div>

@endsection
