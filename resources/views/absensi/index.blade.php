@extends('layouts.app')
@section('title', 'Absensi Harian')
@section('page-title', 'Absensi')
@section('page-subtitle', 'Data kehadiran — ' . $tanggal->translatedFormat('l, d F Y'))

@section('content')

{{-- Filter ──────────────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <input type="date" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}"
           class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
    <select name="status"
            class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Status</option>
        @foreach(['hadir','terlambat','izin','sakit','alfa','cuti'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
    <a href="{{ route('absensi.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>
    <div class="ml-auto flex gap-2">
        <a href="{{ route('absensi.create') }}" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Input Manual
        </a>
        <a href="{{ route('absensi.lokasi.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Lokasi GPS
        </a>
    </div>
</form>

{{-- Ringkasan ────────────────────────────────────────────────────────────── --}}
@php
    $cards = [
        ['label' => 'Total Aktif', 'val' => $totalPegawaiAktif, 'color' => 'gray', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['label' => 'Hadir',       'val' => $ringkasan['hadir']     ?? 0, 'color' => 'green',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Terlambat',   'val' => $ringkasan['terlambat'] ?? $absensi->where('terlambat_menit', '>', 0)->count(), 'color' => 'yellow', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['label' => 'Izin/Sakit',  'val' => ($ringkasan['izin'] ?? 0) + ($ringkasan['sakit'] ?? 0), 'color' => 'blue', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
        ['label' => 'Alfa',        'val' => $ringkasan['alfa']      ?? 0, 'color' => 'red',    'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
@endphp
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-5">
    @foreach($cards as $c)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-{{ $c['color'] }}-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-{{ $c['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['icon'] }}"/>
            </svg>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $c['val'] }}</p>
            <p class="text-xs text-gray-500">{{ $c['label'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Flash ────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif

{{-- Tabel ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Daftar Kehadiran</h3>
        <span class="text-xs text-gray-400">{{ $absensi->total() }} pegawai</span>
    </div>

    @if($absensi->isEmpty())
    <div class="flex flex-col items-center gap-2 py-14 text-gray-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p class="text-sm font-medium">Belum ada data absensi</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Pegawai</th>
                    <th class="px-4 py-3 text-left">Jam Masuk</th>
                    <th class="px-4 py-3 text-left">Jam Keluar</th>
                    <th class="px-4 py-3 text-left">Durasi</th>
                    <th class="px-4 py-3 text-left">Terlambat</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Lokasi</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($absensi as $a)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <img src="{{ $a->pegawai?->foto_url }}" class="w-8 h-8 rounded-full object-cover border border-gray-100"
                                 onerror="this.src='{{ asset('images/avatar-default.png') }}'">
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $a->pegawai?->nama }}</p>
                                <p class="text-xs text-gray-400">{{ $a->pegawai?->jbtn }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 font-mono text-gray-700">
                        {{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3 font-mono text-gray-700">
                        {{ $a->jam_keluar ? \Carbon\Carbon::parse($a->jam_keluar)->format('H:i') : '-' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $a->durasi_kerja ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($a->terlambat_menit > 0)
                        <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">{{ $a->terlambat_label }}</span>
                        @else
                        <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $badges = ['hadir'=>'green','izin'=>'blue','sakit'=>'yellow','alfa'=>'red','cuti'=>'purple','libur'=>'gray'];
                            $bc = $badges[$a->status] ?? 'gray';
                        @endphp
                        <span class="text-xs font-medium text-{{ $bc }}-700 bg-{{ $bc }}-50 px-2 py-0.5 rounded-full capitalize">{{ $a->status }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($a->lat_masuk)
                        <span title="{{ $a->lokasi_valid ? 'Dalam radius' : 'Di luar radius' }}"
                              class="inline-flex items-center gap-1 text-xs {{ $a->lokasi_valid ? 'text-green-600' : 'text-orange-500' }}">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                            {{ $a->lokasi_valid ? 'Valid' : 'Luar radius' }}
                        </span>
                        @else
                        <span class="text-xs text-gray-300">manual</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('absensi.edit', $a) }}" class="text-blue-500 hover:text-blue-700 text-xs">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $absensi->links() }}
    </div>
    @endif
</div>
@endsection
