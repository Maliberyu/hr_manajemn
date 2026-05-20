@extends('layouts.app')
@section('title', 'Permintaan Rekrutmen')
@section('page-title', 'Permintaan SDM')
@section('page-subtitle', 'Manajemen permintaan kebutuhan sumber daya manusia')

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
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari posisi / no. request..."
           class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 w-48">
    <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Status</option>
        @foreach($statusList as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
    <a href="{{ route('rekrutmen.request.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>

    @if(!$isHrd)
    <div class="ml-auto">
        <a href="{{ route('rekrutmen.request.create') }}"
           class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Permintaan
        </a>
    </div>
    @endif
</form>

{{-- Tabel ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">
            {{ $isHrd ? 'Semua Permintaan SDM' : 'Permintaan SDM Saya' }}
        </h3>
        <span class="text-xs text-gray-400">{{ $list->total() }} permintaan</span>
    </div>

    @if($list->isEmpty())
    <div class="flex flex-col items-center gap-2 py-14 text-gray-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm font-medium">Belum ada permintaan SDM</p>
        @if(!$isHrd)
        <a href="{{ route('rekrutmen.request.create') }}" class="text-xs text-blue-600 hover:underline">Ajukan sekarang</a>
        @endif
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">No. Request</th>
                    <th class="px-4 py-3 text-left">Posisi</th>
                    <th class="px-4 py-3 text-left">Departemen</th>
                    <th class="px-4 py-3 text-center">Jumlah</th>
                    @if($isHrd)
                    <th class="px-4 py-3 text-left">Pengaju</th>
                    @endif
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Dibutuhkan</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($list as $req)
                @php
                    $color = match($req->status) {
                        'menunggu_hrd'       => 'yellow',
                        'menunggu_direktur'  => 'blue',
                        'disetujui'          => 'green',
                        'ditolak'            => 'red',
                        default              => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $req->no_request }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $req->posisi }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $req->departemenRef?->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $req->jumlah }}</td>
                    @if($isHrd)
                    <td class="px-4 py-3 text-gray-600">{{ $req->pengaju?->nama ?? '-' }}</td>
                    @endif
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $color }}-700 bg-{{ $color }}-50">
                            {{ ucfirst(str_replace('_', ' ', $req->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $req->tanggal_dibutuhkan ? \Carbon\Carbon::parse($req->tanggal_dibutuhkan)->translatedFormat('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('rekrutmen.request.show', $req) }}"
                           class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                            Lihat
                        </a>
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
