@extends('layouts.app')
@section('title', 'Ijin Khusus — ' . $ijinKhusus->no_pengajuan)
@section('page-title', 'Detail Ijin Khusus')
@section('page-subtitle', $ijinKhusus->no_pengajuan)

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

{{-- Flash ───────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

@php
    $ij = $ijinKhusus;
    [$statusBg, $statusText, $statusIcon] = match($ij->status) {
        'Menunggu Atasan' => ['bg-amber-50  border-amber-200',  'text-amber-700',  'clock'],
        'Menunggu HRD'    => ['bg-blue-50   border-blue-200',   'text-blue-700',   'clock'],
        'Disetujui'       => ['bg-green-50  border-green-200',  'text-green-700',  'check'],
        default           => ['bg-red-50    border-red-200',    'text-red-700',    'x'],
    };
@endphp

{{-- Status Banner ───────────────────────────────────────────────────────────── --}}
<div class="mb-5 px-5 py-4 rounded-2xl border {{ $statusBg }} flex items-center gap-4">
    @if($statusIcon === 'check')
    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    @elseif($statusIcon === 'x')
    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
    </div>
    @else
    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    @endif
    <div class="flex-1">
        <p class="text-sm font-bold {{ $statusText }}">{{ $ij->status }}</p>
        <p class="text-xs {{ $statusText }} opacity-80 mt-0.5">
            @if($ij->status === 'Disetujui')
                Disetujui HRD pada {{ $ij->approved_hrd_at?->translatedFormat('d F Y, H:i') ?? '—' }}
            @elseif(str_starts_with($ij->status,'Ditolak'))
                {{ $ij->catatan_atasan ?? $ij->catatan_hrd ?? '—' }}
            @else
                Pengajuan sedang dalam proses persetujuan
            @endif
        </p>
    </div>
    <span class="font-mono text-xs text-gray-400 hidden sm:block">{{ $ij->no_pengajuan }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Kolom Kiri (2/3) ─────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Info Utama ────────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Jenis badge header --}}
            <div class="px-5 py-4 border-b border-gray-50 flex items-center gap-3">
                <div class="w-9 h-9 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ $ij->jenis->nama }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $ij->jenis->kode }}</p>
                </div>
            </div>

            {{-- Grid info --}}
            <div class="p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4">

                    <div>
                        <p class="text-xs font-medium text-gray-400 mb-1">Pegawai</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $ij->pegawai->nama }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $ij->pegawai->nik }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-gray-400 mb-1">Tanggal Mulai</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($ij->tanggal_mulai)->translatedFormat('d F Y') }}
                        </p>
                    </div>

                    @if($ij->tanggal_akhir && $ij->tanggal_akhir != $ij->tanggal_mulai)
                    <div>
                        <p class="text-xs font-medium text-gray-400 mb-1">Tanggal Akhir</p>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ \Carbon\Carbon::parse($ij->tanggal_akhir)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                    @endif

                    @if($ij->jam_mulai && $ij->jam_selesai)
                    <div>
                        <p class="text-xs font-medium text-gray-400 mb-1">Jam</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $ij->jam_mulai }} – {{ $ij->jam_selesai }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-xs font-medium text-gray-400 mb-1">Durasi</p>
                        <p class="text-sm font-bold text-purple-600">{{ $ij->durasi_label }}</p>
                    </div>

                    <div class="col-span-2 sm:col-span-3">
                        <p class="text-xs font-medium text-gray-400 mb-1">Alasan / Keterangan</p>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $ij->alasan }}</p>
                    </div>
                </div>

                {{-- Lampiran --}}
                @if($ij->file_lampiran)
                <div class="mt-4 pt-4 border-t border-gray-50">
                    <p class="text-xs font-medium text-gray-400 mb-2">Lampiran</p>
                    <a href="{{ route('ijin-khusus.download', $ij) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 text-sm text-blue-700 bg-blue-50 border border-blue-100 rounded-xl hover:bg-blue-100 transition-colors font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Unduh Lampiran
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- ── Action Card: Atasan ───────────────────────────────────────────── --}}
        @if($ij->bisaApproveAtasan())
        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden" x-data="{ showTolak: false }">
            <div class="px-5 py-3.5 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <h3 class="text-sm font-semibold text-amber-800">Tindakan Atasan</h3>
            </div>
            <div class="p-5">
                <form action="{{ route('ijin-khusus.approve.atasan', $ij) }}" method="POST" class="flex items-end gap-3 flex-wrap">
                    @csrf
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Catatan <span class="text-gray-400">(opsional)</span></label>
                        <input type="text" name="catatan_atasan" placeholder="Catatan untuk karyawan..."
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                    </div>
                    <button type="submit" onclick="return confirm('Setujui ijin ini?')"
                            class="px-5 py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-semibold whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Setujui
                    </button>
                    <button type="button" @click="showTolak = !showTolak"
                            class="px-5 py-2.5 text-sm bg-red-50 text-red-700 border border-red-200 rounded-xl hover:bg-red-100 transition-colors font-semibold">
                        Tolak
                    </button>
                </form>

                <div x-show="showTolak" x-cloak x-transition class="mt-4">
                    <form action="{{ route('ijin-khusus.tolak.atasan', $ij) }}" method="POST"
                          class="p-4 bg-red-50 border border-red-100 rounded-xl space-y-3">
                        @csrf
                        <label class="block text-xs font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="catatan_atasan" rows="3" required placeholder="Jelaskan alasan penolakan..."
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none bg-white"></textarea>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-semibold">
                                Konfirmasi Tolak
                            </button>
                            <button type="button" @click="showTolak = false"
                                    class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Action Card: HRD ──────────────────────────────────────────────── --}}
        @if($ij->bisaApproveHrd())
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden" x-data="{ showTolak: false }">
            <div class="px-5 py-3.5 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h3 class="text-sm font-semibold text-blue-800">Tindakan HRD</h3>
            </div>
            <div class="p-5">
                <form action="{{ route('ijin-khusus.approve.hrd', $ij) }}" method="POST" class="flex items-end gap-3 flex-wrap">
                    @csrf
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Catatan <span class="text-gray-400">(opsional)</span></label>
                        <input type="text" name="catatan_hrd" placeholder="Catatan HRD..."
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                    </div>
                    <button type="submit" onclick="return confirm('Setujui ijin ini?')"
                            class="px-5 py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-semibold whitespace-nowrap flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Setujui
                    </button>
                    <button type="button" @click="showTolak = !showTolak"
                            class="px-5 py-2.5 text-sm bg-red-50 text-red-700 border border-red-200 rounded-xl hover:bg-red-100 transition-colors font-semibold">
                        Tolak
                    </button>
                </form>

                <div x-show="showTolak" x-cloak x-transition class="mt-4">
                    <form action="{{ route('ijin-khusus.tolak.hrd', $ij) }}" method="POST"
                          class="p-4 bg-red-50 border border-red-100 rounded-xl space-y-3">
                        @csrf
                        <label class="block text-xs font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="catatan_hrd" rows="3" required placeholder="Jelaskan alasan penolakan..."
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none bg-white"></textarea>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-semibold">
                                Konfirmasi Tolak
                            </button>
                            <button type="button" @click="showTolak = false"
                                    class="px-4 py-2 text-sm text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

    </div>

    {{-- ── Kolom Kanan (1/3) ────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Alur Persetujuan ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Alur Persetujuan</h3>

            @php
                $atasanDone    = in_array($ij->status, ['Menunggu HRD','Disetujui','Ditolak HRD']);
                $atasanTolak   = $ij->status === 'Ditolak Atasan';
                $atasanPending = $ij->status === 'Menunggu Atasan';
                $hrdDone       = $ij->status === 'Disetujui';
                $hrdTolak      = $ij->status === 'Ditolak HRD';
                $hrdPending    = $ij->status === 'Menunggu HRD';
            @endphp

            <div class="relative">
                {{-- Garis vertikal connector --}}
                <div class="absolute left-4 top-8 bottom-8 w-0.5 bg-gray-100"></div>

                {{-- Step 1: Pengaju --}}
                <div class="flex gap-3 mb-5 relative">
                    <div class="w-8 h-8 rounded-full bg-gray-100 border-2 border-white ring-1 ring-gray-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="pt-1">
                        <p class="text-xs font-semibold text-gray-700">Pengaju</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $ij->pegawai->nama }}</p>
                        <p class="text-xs text-gray-400">{{ $ij->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>

                {{-- Step 2: Atasan --}}
                <div class="flex gap-3 mb-5 relative">
                    @if($atasanDone)
                    <div class="w-8 h-8 rounded-full bg-green-100 border-2 border-white ring-1 ring-green-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    @elseif($atasanTolak)
                    <div class="w-8 h-8 rounded-full bg-red-100 border-2 border-white ring-1 ring-red-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    @else
                    <div class="w-8 h-8 rounded-full bg-amber-50 border-2 border-white ring-1 ring-amber-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                    </div>
                    @endif
                    <div class="pt-1">
                        <p class="text-xs font-semibold text-gray-700">Atasan Langsung</p>
                        @if($atasanDone)
                        <p class="text-xs text-green-600 mt-0.5 font-medium">Disetujui oleh {{ $ij->approvedAtasanBy?->nama ?? '—' }}</p>
                        @if($ij->approved_atasan_at)<p class="text-xs text-gray-400">{{ $ij->approved_atasan_at->translatedFormat('d M Y, H:i') }}</p>@endif
                        @if($ij->catatan_atasan)<p class="text-xs text-gray-500 mt-1 italic">"{{ $ij->catatan_atasan }}"</p>@endif
                        @elseif($atasanTolak)
                        <p class="text-xs text-red-600 mt-0.5 font-medium">Ditolak</p>
                        @if($ij->catatan_atasan)<p class="text-xs text-red-500 mt-1 italic">"{{ $ij->catatan_atasan }}"</p>@endif
                        @else
                        <p class="text-xs text-amber-600 mt-0.5">Menunggu persetujuan</p>
                        @endif
                    </div>
                </div>

                {{-- Step 3: HRD --}}
                <div class="flex gap-3 relative">
                    @if($hrdDone)
                    <div class="w-8 h-8 rounded-full bg-green-100 border-2 border-white ring-1 ring-green-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    @elseif($hrdTolak)
                    <div class="w-8 h-8 rounded-full bg-red-100 border-2 border-white ring-1 ring-red-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    @elseif($hrdPending)
                    <div class="w-8 h-8 rounded-full bg-blue-50 border-2 border-white ring-1 ring-blue-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                    </div>
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-50 border-2 border-white ring-1 ring-gray-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                    </div>
                    @endif
                    <div class="pt-1">
                        <p class="text-xs font-semibold text-gray-700">HRD</p>
                        @if($hrdDone)
                        <p class="text-xs text-green-600 mt-0.5 font-medium">Disetujui oleh {{ $ij->approvedHrdBy?->nama ?? '—' }}</p>
                        @if($ij->approved_hrd_at)<p class="text-xs text-gray-400">{{ $ij->approved_hrd_at->translatedFormat('d M Y, H:i') }}</p>@endif
                        @if($ij->catatan_hrd)<p class="text-xs text-gray-500 mt-1 italic">"{{ $ij->catatan_hrd }}"</p>@endif
                        @elseif($hrdTolak)
                        <p class="text-xs text-red-600 mt-0.5 font-medium">Ditolak</p>
                        @if($ij->catatan_hrd)<p class="text-xs text-red-500 mt-1 italic">"{{ $ij->catatan_hrd }}"</p>@endif
                        @elseif($hrdPending)
                        <p class="text-xs text-blue-600 mt-0.5">Menunggu persetujuan HRD</p>
                        @else
                        <p class="text-xs text-gray-400 mt-0.5">Belum diproses</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Kembali ──────────────────────────────────────────────────────────── --}}
        <a href="{{ route('ijin-khusus.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke daftar
        </a>

    </div>

</div>

@endsection
