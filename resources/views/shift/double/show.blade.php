@extends('layouts.app')
@section('title', 'Detail Double Shift — ' . $doubleShift->no_pengajuan)
@section('page-title', 'Detail Double Shift')
@section('page-subtitle', $doubleShift->no_pengajuan)

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
    $ds = $doubleShift;
    [$bg, $txt] = match($ds->status) {
        'disetujui' => ['bg-green-50 border-green-200', 'text-green-700'],
        'ditolak'   => ['bg-red-50 border-red-200', 'text-red-700'],
        default     => ['bg-amber-50 border-amber-200', 'text-amber-700'],
    };
@endphp

{{-- Status Banner ────────────────────────────────────────────────────────────── --}}
<div class="mb-5 px-5 py-4 rounded-2xl border {{ $bg }} flex items-center justify-between">
    <div>
        <p class="text-sm font-bold {{ $txt }}">{{ $ds->status_label }}</p>
        <p class="text-xs {{ $txt }} opacity-70 mt-0.5 font-mono">{{ $ds->no_pengajuan }}</p>
    </div>
    @if($ds->lembur_id)
    <a href="{{ route('lembur.show', $ds->lembur_id) }}"
       class="flex items-center gap-1.5 text-xs font-semibold text-orange-700 bg-orange-50 border border-orange-200 px-3 py-1.5 rounded-xl hover:bg-orange-100 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Lihat Lembur
    </a>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Kolom Kiri ───────────────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Detail Pengajuan</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Pegawai</p>
                    <p class="font-semibold text-gray-800">{{ $ds->pegawai?->nama }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $ds->pegawai?->nik }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-1">Tanggal</p>
                    <p class="font-semibold text-gray-800">{{ $ds->tanggal->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <p class="text-xs text-gray-400 mb-1">Alasan</p>
                    <p class="text-sm text-gray-700">{{ $ds->alasan }}</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-50 grid grid-cols-2 gap-4">
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-xs text-blue-500 font-semibold uppercase tracking-wide mb-2">Shift Pertama</p>
                    <p class="font-bold text-gray-800">{{ $ds->shiftPertama?->nama }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $ds->shiftPertama?->jam_label }}</p>
                    <p class="text-xs text-blue-600 mt-1">Gaji normal</p>
                </div>
                <div class="p-4 bg-orange-50 rounded-xl border border-orange-100">
                    <p class="text-xs text-orange-500 font-semibold uppercase tracking-wide mb-2">Shift Kedua (Lembur)</p>
                    <p class="font-bold text-gray-800">{{ $ds->shiftKedua?->nama }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $ds->shiftKedua?->jam_label }}</p>
                    <p class="text-xs text-orange-600 mt-1">×{{ $ds->shiftKedua?->multiplier_lembur }} upah per jam</p>
                </div>
            </div>
        </div>

        {{-- Action Atasan ────────────────────────────────────────────────────── --}}
        @if($ds->bisaApprove())
        <div class="bg-white rounded-2xl border border-amber-100 shadow-sm overflow-hidden" x-data="{ tolak: false }">
            <div class="px-5 py-3.5 bg-amber-50 border-b border-amber-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <h3 class="text-sm font-semibold text-amber-800">Tindakan Atasan</h3>
            </div>
            <div class="p-5">
                <p class="text-xs text-gray-600 mb-4">Menyetujui double shift ini akan otomatis membuat pengajuan lembur untuk shift kedua.</p>
                <div class="flex flex-wrap gap-3">
                    <form action="{{ route('double-shift.approve', $ds) }}" method="POST" class="flex items-end gap-2">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Catatan (opsional)</label>
                            <input type="text" name="catatan_atasan" placeholder="Catatan atasan..."
                                   class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-300 w-48">
                        </div>
                        <button type="submit" onclick="return confirm('Setujui double shift ini? Lembur akan otomatis dibuat.')"
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
                    <form action="{{ route('double-shift.tolak', $ds) }}" method="POST"
                          class="p-4 bg-red-50 border border-red-100 rounded-xl space-y-3">
                        @csrf
                        <label class="block text-xs font-semibold text-gray-700">Alasan Penolakan <span class="text-red-500">*</span></label>
                        <textarea name="catatan_atasan" rows="2" required
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none bg-white"></textarea>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">Tolak</button>
                            <button type="button" @click="tolak=false" class="px-4 py-2 text-xs bg-white border border-gray-200 text-gray-600 rounded-lg">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Kolom Kanan: Info approval ───────────────────────────────────────────── --}}
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Status Persetujuan</h3>
            @if($ds->status === 'disetujui')
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-700">Disetujui</p>
                    <p class="text-xs text-green-600 mt-0.5">oleh {{ $ds->approvedBy?->nama }}</p>
                    <p class="text-xs text-gray-400">{{ $ds->approved_at?->translatedFormat('d F Y, H:i') }}</p>
                    @if($ds->catatan_atasan)
                    <p class="text-xs text-gray-500 mt-1 italic">"{{ $ds->catatan_atasan }}"</p>
                    @endif
                </div>
            </div>
            @elseif($ds->status === 'ditolak')
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-700">Ditolak oleh {{ $ds->approvedBy?->nama }}</p>
                    @if($ds->catatan_atasan)<p class="text-xs text-red-500 mt-0.5 italic">"{{ $ds->catatan_atasan }}"</p>@endif
                </div>
            </div>
            @else
            <p class="text-xs text-amber-600">Menunggu persetujuan atasan.</p>
            @endif
        </div>

        <a href="{{ route('double-shift.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>
</div>

@endsection
