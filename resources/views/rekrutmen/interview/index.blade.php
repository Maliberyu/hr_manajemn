@extends('layouts.app')
@section('title', 'Daftar Interview')
@section('page-title', 'Interview Rekrutmen')
@section('page-subtitle', 'Jadwal dan rekap interview pelamar')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

{{-- Flash ────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif

{{-- Filter ──────────────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Status</option>
        @foreach(['dijadwalkan','selesai','batal'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <input type="date" name="tanggal" value="{{ request('tanggal') }}"
           class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
    <a href="{{ route('rekrutmen.interview.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>
    <div class="ml-auto">
        <a href="{{ route('rekrutmen.interview.create') }}"
           class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Jadwalkan Interview
        </a>
    </div>
</form>

{{-- Tabel ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Daftar Interview</h3>
        <span class="text-xs text-gray-400">{{ $list->total() }} sesi</span>
    </div>

    @if($list->isEmpty())
    <div class="flex flex-col items-center gap-2 py-14 text-gray-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
        </svg>
        <p class="text-sm font-medium">Belum ada jadwal interview</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Jadwal</th>
                    <th class="px-4 py-3 text-left">Pelamar</th>
                    <th class="px-4 py-3 text-left">Posisi</th>
                    <th class="px-4 py-3 text-left">Tahap</th>
                    <th class="px-4 py-3 text-left">Pewawancara</th>
                    <th class="px-4 py-3 text-left">Metode</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Nilai Rata</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($list as $iv)
                @php
                    $ivColor = match($iv->status) {
                        'dijadwalkan' => 'blue',
                        'selesai'     => 'green',
                        'batal'       => 'red',
                        default       => 'gray',
                    };
                    $avgNilai = $iv->penilaian->avg('nilai');
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                        {{ $iv->jadwal ? \Carbon\Carbon::parse($iv->jadwal)->translatedFormat('d M Y') : '-' }}<br>
                        <span class="text-gray-400">{{ $iv->jadwal ? \Carbon\Carbon::parse($iv->jadwal)->format('H:i') : '' }}</span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $iv->pelamar?->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $iv->pelamar?->lowongan?->posisi ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        <p class="font-medium text-gray-700">{{ $iv->label_tahap ?? 'Tahap ' . $iv->tahap }}</p>
                        <p class="text-xs text-gray-400">Tahap ke-{{ $iv->tahap }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $iv->pewawancara?->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ ucfirst($iv->metode ?? '-') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $ivColor }}-700 bg-{{ $ivColor }}-50">
                            {{ ucfirst($iv->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($iv->status === 'selesai' && !is_null($avgNilai))
                            <span class="font-semibold text-purple-600">{{ number_format($avgNilai, 1) }}</span>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('rekrutmen.interview.show', $iv) }}"
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
