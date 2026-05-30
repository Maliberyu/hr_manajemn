@extends('layouts.app')
@section('title', 'Kontrak Saya')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div>
        <h1 class="text-xl font-bold text-gray-800">Kontrak Kerja Saya</h1>
        <p class="text-sm text-gray-500 mt-0.5">Informasi kontrak kerja Anda dengan perusahaan</p>
    </div>

    {{-- Kontrak Aktif --}}
    @if($kontrakAktif)
    <div class="bg-white border border-green-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-green-50 border-b border-green-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-semibold text-green-700">Kontrak Aktif</span>
            </div>
            <span class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-lg">
                {{ $kontrakAktif->jenis?->nama }}
            </span>
        </div>
        <div class="px-5 py-4 grid grid-cols-2 gap-x-8 gap-y-3">
            @if($kontrakAktif->no_kontrak)
            <div class="col-span-2">
                <p class="text-xs text-gray-400">No. Kontrak</p>
                <p class="text-sm font-medium text-gray-800">{{ $kontrakAktif->no_kontrak }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs text-gray-400">Tanggal Mulai</p>
                <p class="text-sm font-medium text-gray-800">{{ $kontrakAktif->tgl_mulai->isoFormat('D MMMM Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tanggal Selesai</p>
                @if($kontrakAktif->tgl_selesai)
                <p class="text-sm font-medium text-gray-800">{{ $kontrakAktif->tgl_selesai->isoFormat('D MMMM Y') }}</p>
                @if($kontrakAktif->sisa_hari !== null)
                <p class="text-xs mt-0.5 {{ $kontrakAktif->sisa_hari <= 30 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
                    {{ $kontrakAktif->sisa_hari }} hari lagi
                </p>
                @endif
                @else
                <p class="text-sm text-gray-500">Tidak terbatas (Karyawan Tetap)</p>
                @endif
            </div>
            @if($kontrakAktif->tgl_tanda_tangan)
            <div>
                <p class="text-xs text-gray-400">Tanggal TTD</p>
                <p class="text-sm text-gray-700">{{ $kontrakAktif->tgl_tanda_tangan->isoFormat('D MMMM Y') }}</p>
            </div>
            @endif
        </div>
        @if($kontrakAktif->catatan)
        <div class="px-5 pb-4">
            <p class="text-xs text-gray-400 mb-1">Catatan dari HRD</p>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-xl px-3 py-2">{{ $kontrakAktif->catatan }}</p>
        </div>
        @endif
        @if($kontrakAktif->file_url)
        <div class="px-5 pb-4">
            <a href="{{ $kontrakAktif->file_url }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 border border-blue-300 text-blue-600 rounded-xl text-sm hover:bg-blue-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download File Kontrak
            </a>
        </div>
        @endif
    </div>

    {{-- Warning H-30 --}}
    @if($kontrakAktif->sisa_hari !== null && $kontrakAktif->sisa_hari <= 30)
    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-700">Kontrak akan berakhir dalam {{ $kontrakAktif->sisa_hari }} hari</p>
            <p class="text-xs text-amber-600 mt-0.5">Silakan hubungi HRD untuk informasi perpanjangan kontrak Anda.</p>
        </div>
    </div>
    @endif

    @else
    <div class="bg-gray-50 border border-gray-200 rounded-2xl px-6 py-10 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-500 text-sm">Belum ada kontrak aktif.</p>
        <p class="text-gray-400 text-xs mt-1">Hubungi HRD untuk informasi lebih lanjut.</p>
    </div>
    @endif

    {{-- Riwayat --}}
    @if($riwayat->count() > 1 || ($riwayat->count() == 1 && $riwayat->first()->id !== $kontrakAktif?->id))
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Riwayat Kontrak</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($riwayat as $r)
            <div class="flex items-center justify-between px-5 py-3">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2 py-0.5 rounded-lg">{{ $r->jenis?->nama }}</span>
                    <div>
                        <span class="text-sm text-gray-700">{{ $r->tgl_mulai->isoFormat('D MMM Y') }}</span>
                        <span class="text-gray-300 mx-1">→</span>
                        <span class="text-sm text-gray-700">{{ $r->tgl_selesai?->isoFormat('D MMM Y') ?? 'Tetap' }}</span>
                    </div>
                </div>
                @php $c = $r->status_color; @endphp
                <span class="text-xs px-2 py-0.5 rounded-lg bg-{{ $c }}-50 text-{{ $c }}-700 border border-{{ $c }}-100">
                    {{ $r->status_label }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
