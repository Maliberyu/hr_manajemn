@extends('layouts.app')
@section('title', 'Daftar Pelamar')
@section('page-title', 'Pelamar Rekrutmen')
@section('page-subtitle', 'Manajemen data pelamar')

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
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama pelamar..."
           class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 w-44">
    <select name="lowongan_id" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Lowongan</option>
        @foreach($lowonganList as $lw)
            <option value="{{ $lw->id }}" {{ request('lowongan_id') == $lw->id ? 'selected' : '' }}>
                {{ $lw->posisi }} ({{ $lw->no_lowongan }})
            </option>
        @endforeach
    </select>
    <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Status</option>
        @foreach($statusList as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
    <a href="{{ route('rekrutmen.pelamar.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>
    <div class="ml-auto">
        <a href="{{ route('rekrutmen.pelamar.create') }}"
           class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pelamar
        </a>
    </div>
</form>

{{-- Tabel ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Daftar Pelamar</h3>
        <span class="text-xs text-gray-400">{{ $list->total() }} pelamar</span>
    </div>

    @if($list->isEmpty())
    <div class="flex flex-col items-center gap-2 py-14 text-gray-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="text-sm font-medium">Belum ada data pelamar</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Lowongan</th>
                    <th class="px-4 py-3 text-left">Sumber</th>
                    <th class="px-4 py-3 text-left">Pendidikan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Tgl Apply</th>
                    <th class="px-4 py-3 text-center">Nilai Interview</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($list as $p)
                @php
                    $pColor = match($p->status) {
                        'baru'      => 'blue',
                        'screening' => 'yellow',
                        'interview' => 'purple',
                        'offering'  => 'orange',
                        'diterima'  => 'green',
                        'ditolak'   => 'red',
                        default     => 'gray',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $p->nama }}</p>
                        @if($p->email)
                        <p class="text-xs text-gray-400">{{ $p->email }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        <p class="font-medium text-gray-700">{{ $p->lowongan?->posisi ?? '-' }}</p>
                        <p class="text-gray-400">{{ $p->lowongan?->no_lowongan }}</p>
                    </td>
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
                    <td class="px-4 py-3 text-center">
                        @if(!is_null($p->nilai_interview_rata))
                            <span class="font-semibold text-purple-600">{{ number_format($p->nilai_interview_rata, 1) }}</span>
                        @else
                            <span class="text-gray-300">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('rekrutmen.pelamar.show', $p) }}"
                               class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                                Lihat
                            </a>
                            @if($p->cv_path)
                            <a href="{{ Storage::url($p->cv_path) }}" target="_blank"
                               class="px-2.5 py-1 text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                                CV
                            </a>
                            @endif
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
