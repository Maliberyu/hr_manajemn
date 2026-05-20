@extends('layouts.app')
@section('title', 'Daftar Lowongan')
@section('page-title', 'Lowongan Rekrutmen')
@section('page-subtitle', 'Manajemen lowongan pekerjaan aktif')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

{{-- Flash ────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
@endif

{{-- Filter ──────────────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari posisi / no. lowongan..."
           class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 w-48">
    <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Status</option>
        @foreach($statusList as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
    <a href="{{ route('rekrutmen.lowongan.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>
    <div class="ml-auto">
        <a href="{{ route('rekrutmen.lowongan.create') }}"
           class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buka Lowongan
        </a>
    </div>
</form>

{{-- Tabel ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Daftar Lowongan</h3>
        <span class="text-xs text-gray-400">{{ $list->total() }} lowongan</span>
    </div>

    @if($list->isEmpty())
    <div class="flex flex-col items-center gap-2 py-14 text-gray-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        <p class="text-sm font-medium">Belum ada lowongan</p>
        <a href="{{ route('rekrutmen.lowongan.create') }}" class="text-xs text-blue-600 hover:underline">Buka lowongan baru</a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">No. Lowongan</th>
                    <th class="px-4 py-3 text-left">Posisi</th>
                    <th class="px-4 py-3 text-left">Departemen</th>
                    <th class="px-4 py-3 text-center">Kuota</th>
                    <th class="px-4 py-3 text-center">Pelamar</th>
                    <th class="px-4 py-3 text-left">Tgl Buka</th>
                    <th class="px-4 py-3 text-left">Tgl Tutup</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($list as $lw)
                @php
                    $color = match($lw->status) {
                        'aktif'   => 'green',
                        'ditutup' => 'gray',
                        'draft'   => 'yellow',
                        'selesai' => 'blue',
                        default   => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $lw->no_lowongan }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $lw->posisi }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $lw->departemenRef?->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $lw->kuota }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-blue-50 text-blue-700">
                            {{ $lw->pelamar_count ?? 0 }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $lw->tgl_buka ? \Carbon\Carbon::parse($lw->tgl_buka)->translatedFormat('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $lw->tgl_tutup ? \Carbon\Carbon::parse($lw->tgl_tutup)->translatedFormat('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $color }}-700 bg-{{ $color }}-50">
                            {{ ucfirst($lw->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('rekrutmen.lowongan.show', $lw) }}"
                               class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                                Lihat
                            </a>
                            <a href="{{ route('rekrutmen.lowongan.edit', $lw) }}"
                               class="px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $list->links() }}
    </div>
    @endif
</div>

@endsection
