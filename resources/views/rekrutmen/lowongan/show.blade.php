@extends('layouts.app')
@section('title', 'Detail Lowongan')
@section('page-title', 'Detail Lowongan')
@section('page-subtitle', $lowongan->no_lowongan)

@section('content')

@php
    $statusColor = match($lowongan->status) {
        'aktif'   => 'green',
        'ditutup' => 'gray',
        'draft'   => 'yellow',
        'selesai' => 'blue',
        default   => 'gray',
    };
    $totalPelamar = $lowongan->pelamar->count();
    $sisaKuota = max(0, $lowongan->kuota - $totalPelamar);
@endphp

{{-- Flash ─────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- ── Info Lowongan ────────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="font-mono text-sm text-gray-400">{{ $lowongan->no_lowongan }}</p>
                    <h2 class="text-lg font-bold text-gray-800 mt-0.5">{{ $lowongan->posisi }}</h2>
                    <p class="text-xs text-gray-400 mt-1">{{ $lowongan->departemenRef?->nama }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 text-sm font-semibold rounded-full text-{{ $statusColor }}-700 bg-{{ $statusColor }}-50">
                        {{ ucfirst($lowongan->status) }}
                    </span>
                    <a href="{{ route('rekrutmen.lowongan.edit', $lowongan) }}"
                       class="px-3 py-1.5 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                        Edit
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-4">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Kuota</p>
                    <p class="font-bold text-blue-600 text-base">{{ $lowongan->kuota }} orang</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Tgl Buka</p>
                    <p class="font-semibold text-gray-700">
                        {{ $lowongan->tgl_buka ? \Carbon\Carbon::parse($lowongan->tgl_buka)->translatedFormat('d M Y') : '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Tgl Tutup</p>
                    <p class="font-semibold text-gray-700">
                        {{ $lowongan->tgl_tutup ? \Carbon\Carbon::parse($lowongan->tgl_tutup)->translatedFormat('d M Y') : '-' }}
                    </p>
                </div>
            </div>

            @if($lowongan->deskripsi)
            <div class="mb-4">
                <p class="text-xs text-gray-400 mb-1">Deskripsi Pekerjaan</p>
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $lowongan->deskripsi }}</p>
            </div>
            @endif

            @if($lowongan->syarat)
            <div>
                <p class="text-xs text-gray-400 mb-1">Persyaratan</p>
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ $lowongan->syarat }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Sidebar Stats ────────────────────────────────────────────────────── --}}
    <div class="space-y-4">
        {{-- Stats --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Statistik</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Total Pelamar</span>
                    <span class="font-bold text-blue-600 text-base">{{ $totalPelamar }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Kuota</span>
                    <span class="font-bold text-gray-700">{{ $lowongan->kuota }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Sisa Kuota</span>
                    <span class="font-bold {{ $sisaKuota > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $sisaKuota }}</span>
                </div>
                @if($lowongan->kuota > 0)
                <div class="pt-1">
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(100, ($totalPelamar / $lowongan->kuota) * 100) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ round(min(100, ($totalPelamar / $lowongan->kuota) * 100)) }}% terisi</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Link ke Request --}}
        @if($lowongan->request)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Permintaan SDM Terkait</h3>
            <p class="font-mono text-xs text-gray-400">{{ $lowongan->request->no_request }}</p>
            <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $lowongan->request->posisi }}</p>
            <a href="{{ route('rekrutmen.request.show', $lowongan->request) }}"
               class="mt-2 text-xs text-blue-600 hover:underline flex items-center gap-1">
                Lihat detail request →
            </a>
        </div>
        @endif

        {{-- Tambah Pelamar --}}
        <a href="{{ route('rekrutmen.pelamar.create', ['lowongan_id' => $lowongan->id]) }}"
           class="flex items-center justify-center gap-2 w-full py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pelamar
        </a>

        <a href="{{ route('rekrutmen.lowongan.index') }}"
           class="flex items-center justify-center w-full py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
            ← Kembali ke Daftar
        </a>
    </div>
</div>

{{-- ── Tabel Pelamar ─────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Daftar Pelamar</h3>
        <span class="text-xs text-gray-400">{{ $totalPelamar }} pelamar</span>
    </div>

    @if($lowongan->pelamar->isEmpty())
    <div class="flex flex-col items-center gap-2 py-10 text-gray-400">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="text-sm">Belum ada pelamar</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Sumber</th>
                    <th class="px-4 py-3 text-left">Pendidikan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Tgl Apply</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($lowongan->pelamar as $p)
                @php
                    $pColor = match($p->status) {
                        'baru'          => 'blue',
                        'screening'     => 'yellow',
                        'interview'     => 'purple',
                        'offering'      => 'orange',
                        'diterima'      => 'green',
                        'ditolak'       => 'red',
                        default         => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $p->nama }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $p->sumber ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $p->pendidikan_terakhir ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $pColor }}-700 bg-{{ $pColor }}-50">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $p->tanggal_apply ? \Carbon\Carbon::parse($p->tanggal_apply)->translatedFormat('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('rekrutmen.pelamar.show', $p) }}"
                           class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                            Lihat
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
