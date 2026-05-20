@extends('layouts.app')
@section('title', 'Daftar Offering')
@section('page-title', 'Offering Rekrutmen')
@section('page-subtitle', 'Manajemen surat penawaran kerja')

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
    <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Status</option>
        @foreach($statusList as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
    <a href="{{ route('rekrutmen.offering.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>
    <div class="ml-auto">
        <a href="{{ route('rekrutmen.offering.create') }}"
           class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Offering
        </a>
    </div>
</form>

{{-- Tabel ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Daftar Offering</h3>
        <span class="text-xs text-gray-400">{{ $list->total() }} offering</span>
    </div>

    @if($list->isEmpty())
    <div class="flex flex-col items-center gap-2 py-14 text-gray-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-sm font-medium">Belum ada offering</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Pelamar</th>
                    <th class="px-4 py-3 text-left">Posisi</th>
                    <th class="px-4 py-3 text-right">Gaji Ditawarkan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Tgl Offering</th>
                    <th class="px-4 py-3 text-left">Update Oleh</th>
                    <th class="px-4 py-3">Update Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($list as $of)
                @php
                    $ofColor = match($of->status) {
                        'menunggu' => 'yellow',
                        'diterima' => 'green',
                        'ditolak'  => 'red',
                        'negosiasi'=> 'blue',
                        default    => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $of->pelamar?->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $of->pelamar?->lowongan?->posisi ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-700">
                        {{ $of->gaji_ditawarkan ? 'Rp ' . number_format($of->gaji_ditawarkan, 0, ',', '.') : '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $ofColor }}-700 bg-{{ $ofColor }}-50">
                            {{ ucfirst($of->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $of->tanggal_offering ? \Carbon\Carbon::parse($of->tanggal_offering)->translatedFormat('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $of->updatedBy?->nama ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('rekrutmen.offering.updateStatus', $of) }}"
                              class="flex items-center gap-1.5">
                            @csrf
                            @method('PATCH')
                            <select name="status"
                                    class="px-2 py-1 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white">
                                @foreach($statusList as $s)
                                <option value="{{ $s }}" {{ $of->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="px-2.5 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition whitespace-nowrap">
                                Simpan
                            </button>
                        </form>
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
