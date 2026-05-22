@extends('layouts.app')
@section('title', 'Detail Tukar Shift — ' . $tukarShift->no_pengajuan)
@section('page-title', 'Detail Tukar Shift')
@section('page-subtitle', $tukarShift->no_pengajuan)

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

@php
    $ts = $tukarShift;
    [$statusBg, $statusText] = match($ts->status) {
        'disetujui'                      => ['bg-green-50 border-green-200',  'text-green-700'],
        'ditolak_rekan','ditolak_atasan' => ['bg-red-50 border-red-200',      'text-red-700'],
        default                          => ['bg-amber-50 border-amber-200',  'text-amber-700'],
    };
@endphp

{{-- Status Banner ────────────────────────────────────────────────────────────── --}}
<div class="mb-5 px-5 py-4 rounded-2xl border {{ $statusBg }} flex items-center gap-4">
    <div>
        <p class="text-sm font-bold {{ $statusText }}">{{ $ts->status_label }}</p>
        <p class="text-xs {{ $statusText }} opacity-70 mt-0.5 font-mono">{{ $ts->no_pengajuan }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Kolom Kiri: Detail ───────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Kartu pertukaran ─────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Detail Pertukaran</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Pemohon --}}
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide mb-2">Pemohon</p>
                    <p class="font-bold text-gray-800">{{ $ts->pemohon?->nama }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $ts->tgl_shift_pemohon->translatedFormat('l, d F Y') }}</p>
                    <div class="mt-2 inline-flex items-center px-2.5 py-1 bg-white rounded-lg border border-blue-200 text-xs font-semibold text-blue-700">
                        {{ $ts->shiftPemohon?->nama ?? $ts->shift_pemohon_kode }}
                        <span class="ml-1.5 text-blue-400 font-normal">{{ $ts->shiftPemohon?->jam_label }}</span>
                    </div>
                </div>
                {{-- Rekan --}}
                <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                    <p class="text-xs font-semibold text-purple-500 uppercase tracking-wide mb-2">Rekan</p>
                    <p class="font-bold text-gray-800">{{ $ts->rekan?->nama }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $ts->tgl_shift_rekan->translatedFormat('l, d F Y') }}</p>
                    <div class="mt-2 inline-flex items-center px-2.5 py-1 bg-white rounded-lg border border-purple-200 text-xs font-semibold text-purple-700">
                        {{ $ts->shiftRekan?->nama ?? $ts->shift_rekan_kode }}
                        <span class="ml-1.5 text-purple-400 font-normal">{{ $ts->shiftRekan?->jam_label }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-50">
                <p class="text-xs font-semibold text-gray-500 mb-1">Alasan</p>
                <p class="text-sm text-gray-700">{{ $ts->alasan }}</p>
            </div>
        </div>

        {{-- Action: Rekan ────────────────────────────────────────────────────── --}}
        @if($ts->bisaApproveRekan())
        <div class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden" x-data="{ tolak: false }">
            <div class="px-5 py-3.5 bg-purple-50 border-b border-purple-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <h3 class="text-sm font-semibold text-purple-800">Persetujuan Anda (sebagai Rekan)</h3>
            </div>
            <div class="p-5">
                <p class="text-xs text-gray-600 mb-4">{{ $ts->pemohon?->nama }} meminta tukar shift dengan Anda. Setujui atau tolak permintaan ini.</p>
                <div class="flex flex-wrap gap-3">
                    <form action="{{ route('tukar-shift.approve.rekan', $ts) }}" method="POST" class="flex items-end gap-2">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Catatan (opsional)</label>
                            <input type="text" name="catatan_rekan" placeholder="Catatan..."
                                   class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-300 w-52">
                        </div>
                        <button type="submit" onclick="return confirm('Setujui tukar shift ini?')"
                                class="px-5 py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Setujui
                        </button>
                    </form>
                    <button @click="tolak = !tolak"
                            class="px-5 py-2.5 text-sm bg-red-50 text-red-700 border border-red-200 rounded-xl hover:bg-red-100 font-semibold">
                        Tolak
                    </button>
                </div>
                <div x-show="tolak" x-cloak x-transition class="mt-4">
                    <form action="{{ route('tukar-shift.tolak.rekan', $ts) }}" method="POST"
                          class="p-4 bg-red-50 border border-red-100 rounded-xl space-y-3">
                        @csrf
                        <label class="block text-xs font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="catatan_rekan" rows="2" required placeholder="Jelaskan alasan..."
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none bg-white"></textarea>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">Konfirmasi Tolak</button>
                            <button type="button" @click="tolak=false" class="px-4 py-2 text-xs bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- Action: Atasan ───────────────────────────────────────────────────── --}}
        @if($ts->bisaApproveAtasan())
        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden" x-data="{ tolak: false }">
            <div class="px-5 py-3.5 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h3 class="text-sm font-semibold text-amber-800">Tindakan Atasan</h3>
            </div>
            <div class="p-5">
                <div class="flex flex-wrap gap-3">
                    <form action="{{ route('tukar-shift.approve.atasan', $ts) }}" method="POST" class="flex items-end gap-2">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Catatan (opsional)</label>
                            <input type="text" name="catatan_atasan" placeholder="Catatan atasan..."
                                   class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-300 w-52">
                        </div>
                        <button type="submit" onclick="return confirm('Setujui tukar shift?')"
                                class="px-5 py-2.5 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 font-semibold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Setujui
                        </button>
                    </form>
                    <button @click="tolak = !tolak"
                            class="px-5 py-2.5 text-sm bg-red-50 text-red-700 border border-red-200 rounded-xl hover:bg-red-100 font-semibold">
                        Tolak
                    </button>
                </div>
                <div x-show="tolak" x-cloak x-transition class="mt-4">
                    <form action="{{ route('tukar-shift.tolak.atasan', $ts) }}" method="POST"
                          class="p-4 bg-red-50 border border-red-100 rounded-xl space-y-3">
                        @csrf
                        <label class="block text-xs font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="catatan_atasan" rows="2" required
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none bg-white"></textarea>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">Konfirmasi Tolak</button>
                            <button type="button" @click="tolak=false" class="px-4 py-2 text-xs bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Kolom Kanan: Timeline ────────────────────────────────────────────────── --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Alur Persetujuan</h3>
            <div class="relative">
                <div class="absolute left-4 top-8 bottom-8 w-0.5 bg-gray-100"></div>

                {{-- Pemohon --}}
                <div class="flex gap-3 mb-5 relative">
                    <div class="w-8 h-8 rounded-full bg-blue-100 border-2 border-white ring-1 ring-blue-200 flex items-center justify-center flex-shrink-0 z-10">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div class="pt-1">
                        <p class="text-xs font-semibold text-gray-700">Diajukan oleh {{ $ts->pemohon?->nama }}</p>
                        <p class="text-xs text-gray-400">{{ $ts->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>

                {{-- Rekan --}}
                @php $rekanDone = in_array($ts->status, ['menunggu_atasan','disetujui']); @endphp
                <div class="flex gap-3 mb-5 relative">
                    <div class="w-8 h-8 rounded-full border-2 border-white ring-1 z-10 flex items-center justify-center flex-shrink-0
                        {{ $rekanDone ? 'bg-green-100 ring-green-200' : ($ts->status === 'ditolak_rekan' ? 'bg-red-100 ring-red-200' : 'bg-amber-50 ring-amber-200') }}">
                        @if($rekanDone)
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @elseif($ts->status === 'ditolak_rekan')
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        @else
                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                        @endif
                    </div>
                    <div class="pt-1">
                        <p class="text-xs font-semibold text-gray-700">Persetujuan {{ $ts->rekan?->nama }}</p>
                        @if($rekanDone)
                        <p class="text-xs text-green-600 mt-0.5">Disetujui {{ $ts->approved_rekan_at?->translatedFormat('d M Y, H:i') }}</p>
                        @elseif($ts->status === 'ditolak_rekan')
                        <p class="text-xs text-red-600 mt-0.5">Ditolak</p>
                        @if($ts->catatan_rekan)<p class="text-xs text-red-500 italic">"{{ $ts->catatan_rekan }}"</p>@endif
                        @else
                        <p class="text-xs text-amber-600 mt-0.5">Menunggu persetujuan</p>
                        @endif
                    </div>
                </div>

                {{-- Atasan --}}
                @php $atasanDone = $ts->status === 'disetujui'; @endphp
                <div class="flex gap-3 relative">
                    <div class="w-8 h-8 rounded-full border-2 border-white ring-1 z-10 flex items-center justify-center flex-shrink-0
                        {{ $atasanDone ? 'bg-green-100 ring-green-200' : ($ts->status === 'ditolak_atasan' ? 'bg-red-100 ring-red-200' : ($ts->status === 'menunggu_atasan' ? 'bg-blue-50 ring-blue-200' : 'bg-gray-50 ring-gray-200')) }}">
                        @if($atasanDone)
                        <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @elseif($ts->status === 'ditolak_atasan')
                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        @else
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
                        @endif
                    </div>
                    <div class="pt-1">
                        <p class="text-xs font-semibold text-gray-700">Persetujuan Atasan</p>
                        @if($atasanDone)
                        <p class="text-xs text-green-600 mt-0.5">Disetujui oleh {{ $ts->approvedAtasanBy?->nama }} — {{ $ts->approved_atasan_at?->translatedFormat('d M Y, H:i') }}</p>
                        <p class="text-xs text-green-500 mt-0.5 font-medium">Jadwal realisasi diperbarui otomatis.</p>
                        @elseif($ts->status === 'ditolak_atasan')
                        <p class="text-xs text-red-600 mt-0.5">Ditolak</p>
                        @if($ts->catatan_atasan)<p class="text-xs text-red-500 italic">"{{ $ts->catatan_atasan }}"</p>@endif
                        @elseif($ts->status === 'menunggu_atasan')
                        <p class="text-xs text-blue-600 mt-0.5">Menunggu persetujuan</p>
                        @else
                        <p class="text-xs text-gray-400 mt-0.5">Belum diproses</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <a href="{{ route('tukar-shift.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>
</div>

@endsection
