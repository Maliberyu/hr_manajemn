@extends('layouts.app')

@section('title', 'Dashboard — HR Manajemen')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas SDM hari ini')

@section('content')

{{-- Banner: atasan mode — data dibatasi ke bawahan langsung --}}
@if($isAtasan ?? false)
<div class="mb-4 px-4 py-3 bg-blue-50 border border-blue-200 rounded-2xl flex items-center gap-3">
    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
    </svg>
    <div>
        <p class="text-sm font-semibold text-blue-800">Data bawahan langsung Anda</p>
        <p class="text-xs text-blue-600 mt-0.5">
            Menampilkan data {{ count($nikBawahan ?? []) }} karyawan yang menjadi tanggung jawab Anda.
            @if(empty($nikBawahan))
                <span class="font-semibold text-orange-600">Belum ada bawahan yang di-mapping.</span>
            @endif
        </p>
    </div>
</div>
@endif

{{-- Notif: karyawan belum ada atasan --}}
@if(($pegawaiBelumAdaAtasan ?? 0) > 0)
<div class="mb-4 px-4 py-3 bg-orange-50 border border-orange-200 rounded-2xl flex items-center justify-between">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="text-sm font-semibold text-orange-800">
                {{ $pegawaiBelumAdaAtasan }} karyawan aktif belum memiliki atasan langsung
            </p>
            <p class="text-xs text-orange-600 mt-0.5">
                Pengajuan cuti & lembur mereka akan langsung masuk ke HRD.
            </p>
        </div>
    </div>
    <a href="{{ route('pengaturan.atasan.index', ['belum_diset' => 1]) }}"
       class="flex-shrink-0 px-3 py-1.5 text-xs bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-medium">
        Setting Sekarang
    </a>
</div>
@endif

{{-- Notif: berkas karyawan akan kadaluarsa --}}
@if(($hrdBerkasKadaluarsa ?? collect())->isNotEmpty())
@php
    $bkExpired = $hrdBerkasKadaluarsa->filter(fn($b) => $b->status_kadaluarsa === 'kadaluarsa');
    $bkUrgent  = $hrdBerkasKadaluarsa->filter(fn($b) => $b->status_kadaluarsa === 'urgent');
    $isUrgent  = $bkExpired->isNotEmpty() || $bkUrgent->isNotEmpty();
@endphp
<div class="mb-4 rounded-2xl border overflow-hidden {{ $isUrgent ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }}"
     x-data="{ expandBerkas: false }">
    <div class="px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 {{ $isUrgent ? 'text-red-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold {{ $isUrgent ? 'text-red-800' : 'text-amber-800' }}">
                    {{ $hrdBerkasKadaluarsa->count() }} dokumen karyawan perlu diperbarui
                    @if($bkExpired->isNotEmpty())
                    <span class="ml-1 px-1.5 py-0.5 text-xs bg-red-200 text-red-800 rounded-full">{{ $bkExpired->count() }} kadaluarsa</span>
                    @endif
                    @if($bkUrgent->isNotEmpty())
                    <span class="ml-1 px-1.5 py-0.5 text-xs bg-orange-200 text-orange-800 rounded-full">{{ $bkUrgent->count() }} urgent</span>
                    @endif
                </p>
                <p class="text-xs {{ $isUrgent ? 'text-red-600' : 'text-amber-600' }} mt-0.5">
                    Dokumen SIP/STR atau sertifikat yang masa berlakunya habis atau hampir habis.
                </p>
            </div>
        </div>
        <button @click="expandBerkas = !expandBerkas"
                class="flex-shrink-0 flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg transition
                       {{ $isUrgent ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-amber-500 text-white hover:bg-amber-600' }}">
            <span x-text="expandBerkas ? 'Tutup' : 'Lihat Detail'"></span>
            <svg class="w-3 h-3 transition-transform" :class="expandBerkas ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

    <div x-show="expandBerkas" x-cloak class="border-t {{ $isUrgent ? 'border-red-200' : 'border-amber-200' }}">
        <div class="divide-y {{ $isUrgent ? 'divide-red-100' : 'divide-amber-100' }} max-h-60 overflow-y-auto">
            @foreach($hrdBerkasKadaluarsa as $bk)
            <div class="px-4 py-2.5 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-full bg-white border {{ $isUrgent ? 'border-red-200' : 'border-amber-200' }} flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @if($bk->pegawai?->photo)
                        <img src="{{ asset('storage/' . $bk->pegawai->photo) }}" class="w-full h-full object-cover">
                        @else
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-800 truncate">{{ $bk->pegawai?->nama ?? $bk->nik }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $bk->jenis?->nama ?? 'Dokumen' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <p class="text-xs text-gray-500">{{ $bk->tgl_kadaluarsa?->translatedFormat('d M Y') }}</p>
                    @if($bk->status_kadaluarsa === 'kadaluarsa')
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">Kadaluarsa</span>
                    @elseif($bk->status_kadaluarsa === 'urgent')
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-orange-100 text-orange-700 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                        H-{{ $bk->hari_sisa }}
                    </span>
                    @else
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">{{ $bk->hari_sisa }} hari</span>
                    @endif
                    @if($bk->pegawai)
                    <a href="{{ route('karyawan.berkas.index', $bk->pegawai) }}"
                       class="text-blue-500 hover:text-blue-700 transition" title="Buka dokumen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- STATS CARDS --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">

    {{-- Total Karyawan --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total_pegawai'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Karyawan Aktif</p>
        </div>
    </div>

    {{-- Hadir Hari Ini --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['hadir_hari_ini'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Hadir Hari Ini</p>
        </div>
    </div>

    {{-- Cuti Menunggu --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['cuti_menunggu'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Cuti Menunggu</p>
        </div>
    </div>

    {{-- Ijin Menunggu --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['ijin_menunggu'] ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Ijin Menunggu</p>
        </div>
    </div>

    {{-- Lembur Menunggu --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['lembur_menunggu'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Lembur Menunggu</p>
        </div>
    </div>

</div>

{{-- Info chips --}}
<div class="flex flex-wrap gap-3 mb-6">
    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm text-sm">
        <span class="w-2 h-2 rounded-full bg-red-400"></span>
        <span class="text-gray-500">Terlambat:</span>
        <span class="font-semibold text-gray-800">{{ $stats['terlambat_hari'] }}</span>
    </div>
    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm text-sm">
        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
        <span class="text-gray-500">Lowongan Buka:</span>
        <span class="font-semibold text-gray-800">{{ $stats['lowongan_buka'] }}</span>
    </div>
    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm text-sm">
        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
        <span class="text-gray-500">Training Berjalan:</span>
        <span class="font-semibold text-gray-800">{{ $stats['training_berjalan'] }}</span>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 2: CHART + BIRTHDAY --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Grafik Absensi 7 Hari --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Grafik Kehadiran</h3>
                <p class="text-xs text-gray-400">7 hari terakhir</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-blue-500 inline-block"></span>Hadir</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-yellow-400 inline-block"></span>Terlambat</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-red-400 inline-block"></span>Alfa</span>
            </div>
        </div>
        <div class="h-52">
            <canvas id="chartAbsensi"></canvas>
        </div>
    </div>

    {{-- Ulang Tahun --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-lg"></span>
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Ulang Tahun Bulan Ini</h3>
                <p class="text-xs text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        @if($ultah->isEmpty())
        <div class="text-center py-6 text-gray-400 text-sm">
            Tidak ada ulang tahun bulan ini
        </div>
        @else
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @foreach($ultah as $pegawai)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 text-xs font-bold uppercase flex-shrink-0">
                    {{ substr($pegawai->nama, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-800 truncate">{{ $pegawai->nama }}</p>
                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($pegawai->tgl_lahir)->format('d M') }}</p>
                </div>
                @if(\Carbon\Carbon::parse($pegawai->tgl_lahir)->format('d') == now()->format('d'))
                <span class="text-xs bg-pink-100 text-pink-600 px-2 py-0.5 rounded-full font-medium">Hari ini!</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 3: CUTI TERBARU + LEMBUR MENUNGGU --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Cuti Terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Pengajuan Cuti Terbaru</h3>
            <a href="{{ route('cuti.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        @if($cutiTerbaru->isEmpty())
        <div class="text-center py-8 text-gray-400 text-sm">Tidak ada pengajuan cuti</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($cutiTerbaru as $cuti)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold uppercase flex-shrink-0">
                    {{ substr($cuti->pegawai->nama ?? '?', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $cuti->pegawai->nama ?? '-' }}</p>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M') }}
                        @if(isset($cuti->tanggal_selesai))
                        — {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                        @endif
                    </p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-medium
                    {{ $cuti->status === 'approved' ? 'bg-green-100 text-green-700' :
                       ($cuti->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($cuti->status ?? 'Menunggu') }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Lembur Menunggu --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Lembur Menunggu Approval</h3>
            <a href="{{ route('lembur.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        @if($lemburMenunggu->isEmpty())
        <div class="text-center py-8 text-gray-400 text-sm">Tidak ada lembur menunggu</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($lemburMenunggu as $lembur)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs font-bold uppercase flex-shrink-0">
                    {{ substr($lembur->pegawai->nama ?? '?', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $lembur->pegawai->nama ?? '-' }}</p>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($lembur->tanggal)->format('d M Y') }}
                        @if(isset($lembur->jam_mulai) && isset($lembur->jam_selesai))
                        · {{ $lembur->jam_mulai }} – {{ $lembur->jam_selesai }}
                        @endif
                    </p>
                </div>
                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium">
                    Menunggu
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- SECTION: REKAP SDM --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="mt-8">

    {{-- Section header + filter --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="text-sm font-bold text-gray-800">Rekap SDM</h2>
            <p class="text-xs text-gray-400">
                {{ $periodLabel }}
                @if($rekapDep)
                · {{ $departemen->firstWhere('dep_id', $rekapDep)?->nama }}
                @endif
            </p>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-center gap-2">

            {{-- Dari tanggal --}}
            <div class="flex items-center gap-1">
                <label class="text-xs text-gray-400 whitespace-nowrap">Dari</label>
                <input type="date" name="dari_tgl"
                       value="{{ $dariTgl->format('Y-m-d') }}"
                       max="{{ now()->format('Y-m-d') }}"
                       class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
            </div>

            {{-- Sampai tanggal --}}
            <div class="flex items-center gap-1">
                <label class="text-xs text-gray-400 whitespace-nowrap">s/d</label>
                <input type="date" name="sampai_tgl"
                       value="{{ $sampaiTgl->format('Y-m-d') }}"
                       max="{{ now()->format('Y-m-d') }}"
                       class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
            </div>

            {{-- Filter Departemen — HANYA untuk HRD & Admin, bukan Atasan --}}
            @if(!($isAtasan ?? false))
            <select name="rekap_dep"
                    class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                <option value="">Semua Departemen</option>
                @foreach($departemen as $dep)
                <option value="{{ $dep->dep_id }}" {{ $rekapDep == $dep->dep_id ? 'selected' : '' }}>
                    {{ $dep->nama }}
                </option>
                @endforeach
            </select>
            @endif

            <button type="submit"
                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                Tampilkan
            </button>
            @if(request('dari_tgl') || request('sampai_tgl') || request('rekap_dep'))
            <a href="{{ route('dashboard') }}" class="text-xs text-gray-400 hover:text-gray-600">Reset</a>
            @endif
        </form>
    </div>

    {{-- KPI totals (computed from chart data) --}}
    @php
        $kpiHadir    = $grafikRekapAbsensi->sum('hadir');
        $kpiSakit    = $grafikRekapAbsensi->sum('sakit');
        $kpiAlfa     = $grafikRekapAbsensi->sum('alfa');
        $kpiJamTrain = $grafikRekapPelatihan->sum(fn($d) => ($d['jam_iht'] ?? 0) + ($d['jam_eksternal'] ?? 0));
        $kpiHariCuti = $grafikRekapCuti->sum('total_hari');
        $kpiIjin     = $grafikRekapIjin->sum('jumlah');
    @endphp

    {{-- 2×2 chart grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- ── Card 1: Kehadiran per Departemen ─────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #e0e7ff; border-top:3px solid #3b82f6;">
            <div class="px-5 pt-4 pb-3 flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-blue-500 uppercase tracking-widest">Kehadiran</p>
                    <h3 class="text-sm font-bold text-gray-800 mt-0.5">Per Departemen</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $periodLabel }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $kpiHadir ?: '—' }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">total hadir</p>
                    <div class="flex items-center gap-1.5 mt-1.5 justify-end">
                        <span class="text-[10px] text-amber-600 font-medium">{{ $kpiSakit }} sakit</span>
                        <span class="text-gray-300">·</span>
                        <span class="text-[10px] text-red-500 font-medium">{{ $kpiAlfa }} alfa</span>
                    </div>
                </div>
            </div>
            <div class="px-4 pb-2">
                <div class="relative" style="height:168px">
                    <canvas id="chartRekapAbsensi"></canvas>
                </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
                <div class="flex flex-wrap gap-x-3 gap-y-1">
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-blue-500 inline-block"></span>Hadir</span>
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-amber-400 inline-block"></span>Sakit</span>
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-violet-400 inline-block"></span>Ijin</span>
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-red-400 inline-block"></span>Alfa</span>
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-orange-400 inline-block"></span>Terlambat</span>
                </div>
                @if(auth()->user()->hasRole(['hrd','admin']))
                <a href="{{ route('absensi.rekap', ['bulan'=>$bulanRekap,'tahun'=>$tahunRekap]) }}"
                   class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 whitespace-nowrap">
                    Selengkapnya
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif
            </div>
        </div>

        {{-- ── Card 2: Jam Pelatihan per Departemen ──────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #ede9fe; border-top:3px solid #6366f1;">
            <div class="px-5 pt-4 pb-3 flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-indigo-500 uppercase tracking-widest">Pelatihan</p>
                    <h3 class="text-sm font-bold text-gray-800 mt-0.5">Jam per Departemen</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Tahun {{ $tahunRekap }} · IHT + Eksternal</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $kpiJamTrain > 0 ? number_format($kpiJamTrain, 0) : '—' }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">total jam</p>
                </div>
            </div>
            <div class="px-4 pb-2">
                <div class="relative" style="height:168px">
                    <canvas id="chartRekapPelatihan"></canvas>
                </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
                <div class="flex flex-wrap gap-x-3 gap-y-1">
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-indigo-500 inline-block"></span>IHT</span>
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500 inline-block"></span>Eksternal</span>
                    <span class="text-[10px] text-gray-400">(eksternal: asumsi 8j/hari)</span>
                </div>
                @if(auth()->user()->hasRole(['hrd','admin']))
                <a href="{{ route('training.rekap', ['tahun'=>$tahunRekap]) }}"
                   class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1 whitespace-nowrap">
                    Selengkapnya
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif
            </div>
        </div>

        {{-- ── Card 3: Cuti per Jenis ─────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #fef3c7; border-top:3px solid #f59e0b;">
            <div class="px-5 pt-4 pb-3 flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-amber-500 uppercase tracking-widest">Cuti</p>
                    <h3 class="text-sm font-bold text-gray-800 mt-0.5">Disetujui per Jenis</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Tahun {{ $tahunRekap }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $kpiHariCuti ?: '—' }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">total hari</p>
                    <p class="text-[10px] text-amber-600 mt-1 font-medium">
                        {{ $grafikRekapCuti->sum('jumlah') }} pengajuan
                    </p>
                </div>
            </div>
            <div class="px-4 pb-2">
                <div class="relative" style="height:168px">
                    <canvas id="chartRekapCuti"></canvas>
                </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
                <div class="flex flex-wrap gap-x-3 gap-y-1">
                    @foreach(['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#f97316'] as $ci => $color)
                    @if($ci < $grafikRekapCuti->count())
                    <span class="flex items-center gap-1 text-[10px] text-gray-500">
                        <span class="w-2.5 h-2.5 rounded-sm inline-block" style="background:{{ $color }}"></span>
                        {{ $grafikRekapCuti[$ci]['jenis'] ?? '' }}
                    </span>
                    @endif
                    @endforeach
                </div>
                <a href="{{ route('cuti.rekap', ['tahun'=>$tahunRekap]) }}"
                   class="text-xs text-amber-600 hover:text-amber-800 font-medium flex items-center gap-1 whitespace-nowrap">
                    Selengkapnya
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- ── Card 4: Ijin per Jenis ─────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden" style="border:1px solid #ffedd5; border-top:3px solid #f97316;">
            <div class="px-5 pt-4 pb-3 flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-semibold text-orange-500 uppercase tracking-widest">Ijin</p>
                    <h3 class="text-sm font-bold text-gray-800 mt-0.5">Disetujui per Jenis</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $periodLabel }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-extrabold text-gray-900 leading-none">{{ $kpiIjin ?: '—' }}</p>
                    <p class="text-[10px] text-gray-400 mt-0.5">total pengajuan</p>
                </div>
            </div>
            <div class="px-4 pb-2">
                <div class="relative" style="height:168px">
                    <canvas id="chartRekapIjin"></canvas>
                </div>
            </div>
            <div class="px-5 py-3 border-t border-gray-50 flex items-center justify-between">
                <div class="flex flex-wrap gap-x-3 gap-y-1">
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-amber-400 inline-block"></span>Ijin Sakit</span>
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-orange-400 inline-block"></span>Ijin Terlambat</span>
                    <span class="flex items-center gap-1 text-[10px] text-gray-500"><span class="w-2.5 h-2.5 rounded-sm bg-violet-400 inline-block"></span>Pulang Duluan</span>
                </div>
                <a href="{{ route('ijin.rekap', ['bulan'=>$bulanRekap,'tahun'=>$tahunRekap]) }}"
                   class="text-xs text-orange-600 hover:text-orange-800 font-medium flex items-center gap-1 whitespace-nowrap">
                    Selengkapnya
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartAbsensi');
    if (!ctx) return;

    const data = @json($grafikAbsensi);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                {
                    label: 'Hadir',
                    data: data.map(d => d.hadir),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                },
                {
                    label: 'Terlambat',
                    data: data.map(d => d.terlambat),
                    borderColor: '#facc15',
                    backgroundColor: 'rgba(250,204,21,0.08)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#facc15',
                },
                {
                    label: 'Alfa',
                    data: data.map(d => d.alfa),
                    borderColor: '#f87171',
                    backgroundColor: 'rgba(248,113,113,0.08)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#f87171',
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#1e293b',
                    padding: 10,
                    titleFont: { size: 11 },
                    bodyFont: { size: 11 },
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 }, color: '#94a3b8', stepSize: 1 }
                }
            }
        }
    });
});

// ─── Rekap SDM charts (inside DOMContentLoaded so Chart.js is ready) ────────
document.addEventListener('DOMContentLoaded', function () {

const noDataPlugin = {
    id: 'noData',
    afterDraw(chart) {
        const hasData = chart.data.datasets.some(ds => ds.data.some(v => v > 0));
        if (!hasData) {
            const { ctx, width, height } = chart;
            ctx.save();
            ctx.clearRect(0, 0, width, height);
            ctx.fillStyle = '#f8fafc';
            ctx.fillRect(0, 0, width, height);
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 1.5;
            ctx.setLineDash([4, 4]);
            const r = Math.min(width, height) * 0.35;
            ctx.beginPath();
            ctx.arc(width / 2, height / 2, r, 0, Math.PI * 2);
            ctx.stroke();
            ctx.setLineDash([]);
            ctx.font = '12px Inter, system-ui, sans-serif';
            ctx.fillStyle = '#94a3b8';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Belum ada data periode ini', width / 2, height / 2);
            ctx.restore();
        }
    }
};

const tip = {
    backgroundColor: '#1e293b',
    titleColor: '#f1f5f9',
    bodyColor: '#cbd5e1',
    borderColor: '#334155',
    borderWidth: 1,
    padding: { x: 12, y: 8 },
    cornerRadius: 8,
    titleFont: { size: 11, weight: 'bold' },
    bodyFont: { size: 11 },
};

// ─── Chart 1: Kehadiran per Departemen ───────────────────────────────────
const ctxAbsensi = document.getElementById('chartRekapAbsensi');
if (ctxAbsensi) {
    const raw = @json($grafikRekapAbsensi);
    new Chart(ctxAbsensi, {
        type: 'bar',
        plugins: [noDataPlugin],
        data: {
            labels: raw.length ? raw.map(d => d.dep) : ['—'],
            datasets: [
                { label: 'Hadir',     data: raw.map(d => d.hadir),     backgroundColor: '#3b82f6', borderRadius: 4, borderSkipped: false },
                { label: 'Sakit',     data: raw.map(d => d.sakit),     backgroundColor: '#fbbf24', borderRadius: 4, borderSkipped: false },
                { label: 'Ijin',      data: raw.map(d => d.izin),      backgroundColor: '#a78bfa', borderRadius: 4, borderSkipped: false },
                { label: 'Alfa',      data: raw.map(d => d.alfa),      backgroundColor: '#f87171', borderRadius: 4, borderSkipped: false },
                { label: 'Terlambat', data: raw.map(d => d.terlambat), backgroundColor: '#fb923c', borderRadius: 4, borderSkipped: false },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { ...tip, mode: 'index', intersect: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8', maxRotation: 30 } },
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8', stepSize: 1 } }
            },
            animation: { duration: 700, easing: 'easeOutQuart' }
        }
    });
}

// ─── Chart 2: Jam Pelatihan per Departemen ────────────────────────────────
const ctxPelatihan = document.getElementById('chartRekapPelatihan');
if (ctxPelatihan) {
    const raw = @json($grafikRekapPelatihan);
    new Chart(ctxPelatihan, {
        type: 'bar',
        plugins: [noDataPlugin],
        data: {
            labels: raw.length ? raw.map(d => d.dep) : ['—'],
            datasets: [
                { label: 'IHT',       data: raw.map(d => d.jam_iht),       backgroundColor: '#6366f1', stack: 'j', borderRadius: 4, borderSkipped: false },
                { label: 'Eksternal', data: raw.map(d => d.jam_eksternal), backgroundColor: '#10b981', stack: 'j', borderRadius: 4, borderSkipped: false },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tip, mode: 'index', intersect: false, callbacks: { label: c => ` ${c.dataset.label}: ${c.parsed.y.toFixed(1)} jam` } }
            },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8', maxRotation: 30 } },
                y: { stacked: true, beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, color: '#94a3b8', callback: v => v + 'j' } }
            },
            animation: { duration: 700, easing: 'easeOutQuart' }
        }
    });
}

// ─── Chart 3: Cuti per Jenis ──────────────────────────────────────────────
const ctxCuti = document.getElementById('chartRekapCuti');
if (ctxCuti) {
    const raw = @json($grafikRekapCuti);
    const palette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#f97316'];
    new Chart(ctxCuti, {
        type: 'doughnut',
        plugins: [noDataPlugin],
        data: {
            labels: raw.length ? raw.map(d => d.jenis) : ['—'],
            datasets: [{
                data: raw.length ? raw.map(d => d.total_hari) : [0],
                backgroundColor: raw.length ? palette.slice(0, raw.length) : ['#e2e8f0'],
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tip,
                    callbacks: { label: c => ` ${c.label}: ${c.parsed} hari (${raw[c.dataIndex]?.jumlah ?? 0}x)` }
                }
            },
            animation: { animateRotate: true, duration: 800, easing: 'easeOutQuart' }
        }
    });
}

// ─── Chart 4: Ijin per Jenis ──────────────────────────────────────────────
const ctxIjin = document.getElementById('chartRekapIjin');
if (ctxIjin) {
    const raw = @json($grafikRekapIjin);
    const colorMap = { sakit: '#fbbf24', terlambat: '#fb923c', pulang_duluan: '#a78bfa' };
    new Chart(ctxIjin, {
        type: 'bar',
        plugins: [noDataPlugin],
        data: {
            labels: raw.length ? raw.map(d => d.jenis) : ['—'],
            datasets: [{
                label: 'Pengajuan',
                data: raw.length ? raw.map(d => d.jumlah) : [0],
                backgroundColor: raw.length ? raw.map(d => colorMap[d.key] ?? '#6366f1') : ['#e2e8f0'],
                borderRadius: 8,
                borderSkipped: false,
                barThickness: 40,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { ...tip, callbacks: { label: c => ` ${c.parsed.x} pengajuan` } }
            },
            scales: {
                x: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 }, color: '#94a3b8', stepSize: 1 } },
                y: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#374151' } }
            },
            animation: { duration: 700, easing: 'easeOutQuart' }
        }
    });
}

}); // end DOMContentLoaded rekap charts
</script>
@endpush
