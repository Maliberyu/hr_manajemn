@extends('layouts.app')
@section('title', 'Detail Kontrak')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Back + Actions --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('kontrak.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-800">Detail Kontrak</h1>
                <p class="text-sm text-gray-500">{{ $kontrak->pegawai?->nama ?? $kontrak->nik }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            @if($kontrak->status === 'aktif')
            <a href="{{ route('kontrak.create') }}?nik={{ $kontrak->nik }}"
               class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Perpanjang
            </a>
            @endif
            <a href="{{ route('kontrak.edit', $kontrak) }}"
               class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-xl text-sm text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Kartu utama --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        {{-- Header kartu --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="bg-blue-50 text-blue-700 text-sm font-semibold px-3 py-1 rounded-lg">
                    {{ $kontrak->jenis?->nama }}
                </span>
                @if($kontrak->no_kontrak)
                <span class="text-sm text-gray-500">No. {{ $kontrak->no_kontrak }}</span>
                @endif
            </div>
            @php $c = $kontrak->status_color; @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold
                bg-{{ $c }}-50 text-{{ $c }}-700 border border-{{ $c }}-200">
                {{ $kontrak->status_label }}
            </span>
        </div>

        <div class="px-6 py-5 grid grid-cols-2 gap-x-8 gap-y-4">
            <div>
                <p class="text-xs text-gray-400 mb-0.5">NIK</p>
                <p class="text-sm font-medium text-gray-800">{{ $kontrak->nik }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Jabatan</p>
                <p class="text-sm text-gray-800">{{ $kontrak->pegawai?->jbtn ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Tanggal Mulai</p>
                <p class="text-sm font-medium text-gray-800">{{ $kontrak->tgl_mulai->isoFormat('D MMMM Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Tanggal Selesai</p>
                @if($kontrak->tgl_selesai)
                <p class="text-sm font-medium text-gray-800">{{ $kontrak->tgl_selesai->isoFormat('D MMMM Y') }}</p>
                @if($kontrak->status === 'aktif' && $kontrak->sisa_hari !== null)
                <p class="text-xs {{ $kontrak->sisa_hari <= 7 ? 'text-red-600 font-semibold' : ($kontrak->sisa_hari <= 30 ? 'text-amber-600' : 'text-gray-400') }}">
                    H-{{ $kontrak->sisa_hari }} hari lagi
                </p>
                @endif
                @else
                <p class="text-sm text-gray-400">Tidak terbatas (Karyawan Tetap)</p>
                @endif
            </div>
            @if($kontrak->tgl_tanda_tangan)
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Tanggal TTD</p>
                <p class="text-sm text-gray-800">{{ $kontrak->tgl_tanda_tangan->isoFormat('D MMMM Y') }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Dibuat Oleh</p>
                <p class="text-sm text-gray-800">{{ $kontrak->pembuatUser?->nama ?? '—' }}</p>
            </div>
        </div>

        @if($kontrak->catatan)
        <div class="px-6 pb-4">
            <p class="text-xs text-gray-400 mb-1">Catatan</p>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-xl px-3 py-2">{{ $kontrak->catatan }}</p>
        </div>
        @endif

        @if($kontrak->file_url)
        <div class="px-6 pb-5">
            <a href="{{ $kontrak->file_url }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 border border-blue-300 text-blue-600 rounded-xl text-sm hover:bg-blue-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Lihat / Download File Kontrak
            </a>
        </div>
        @endif
    </div>

    {{-- Riwayat Kontrak --}}
    @if($riwayat->count() > 1)
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Riwayat Kontrak</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($riwayat as $r)
            <div class="flex items-center justify-between px-5 py-3 {{ $r->id === $kontrak->id ? 'bg-blue-50' : '' }}">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg">{{ $r->jenis?->nama }}</span>
                    <span class="text-sm text-gray-700">{{ $r->tgl_mulai->isoFormat('D MMM Y') }}</span>
                    <span class="text-gray-300">→</span>
                    <span class="text-sm text-gray-700">{{ $r->tgl_selesai?->isoFormat('D MMM Y') ?? 'Tetap' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    @php $c = $r->status_color; @endphp
                    <span class="text-xs px-2 py-0.5 rounded-lg bg-{{ $c }}-50 text-{{ $c }}-700">{{ $r->status_label }}</span>
                    @if($r->id !== $kontrak->id)
                    <a href="{{ route('kontrak.show', $r) }}" class="text-xs text-blue-600 hover:underline">Lihat</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
