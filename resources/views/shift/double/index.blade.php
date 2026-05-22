@extends('layouts.app')
@section('title', 'Double Shift')
@section('page-title', 'Double Shift')
@section('page-subtitle', 'Pengajuan ambil dua shift berturut-turut')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Toolbar ──────────────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
    <form method="GET" action="{{ route('double-shift.index') }}" class="flex flex-wrap gap-2 flex-1">
        <select name="status" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300">
            <option value="">Semua Status</option>
            @foreach(\App\Models\DoubleShift::STATUS as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="bulan" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300">
            <option value="">Semua Bulan</option>
            @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
            </option>
            @endforeach
        </select>
        <select name="tahun" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300">
            @foreach(range(date('Y'), date('Y')-2) as $yr)
            <option value="{{ $yr }}" {{ request('tahun', date('Y')) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('double-shift.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors font-semibold whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Ajukan Double Shift
    </a>
</div>

{{-- Tabel ───────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    @if($list->isEmpty())
    <div class="py-14 text-center">
        <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <p class="text-sm text-gray-400">Belum ada pengajuan double shift.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">No.</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pegawai</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Shift</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Lembur</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($list as $item)
                @php
                    $badge = match($item->status) {
                        'disetujui' => 'bg-green-50 text-green-700 border border-green-200',
                        'ditolak'   => 'bg-red-50 text-red-700 border border-red-200',
                        default     => 'bg-amber-50 text-amber-700 border border-amber-200',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3.5 font-mono text-xs text-gray-500">{{ $item->no_pengajuan }}</td>
                    <td class="px-4 py-3.5">
                        <p class="font-medium text-gray-800">{{ $item->pegawai?->nama }}</p>
                        <p class="text-xs text-gray-400">{{ $item->pegawai?->nik }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-gray-700 text-xs">
                        {{ $item->tanggal->translatedFormat('l, d M Y') }}
                    </td>
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-1.5 text-xs flex-wrap">
                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 rounded-lg font-medium">
                                {{ $item->shiftPertama?->nama ?? $item->shift_pertama_kode }}
                            </span>
                            <span class="text-gray-400 font-bold">+</span>
                            <span class="px-2 py-0.5 bg-orange-50 text-orange-700 rounded-lg font-medium">
                                {{ $item->shiftKedua?->nama ?? $item->shift_kedua_kode }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if($item->lembur_id)
                        <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full border border-green-100 font-medium">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Dibuat
                        </span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex px-2.5 py-1 text-xs rounded-full font-medium {{ $badge }}">
                            {{ $item->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <a href="{{ route('double-shift.show', $item) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-orange-600 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($list->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">{{ $list->withQueryString()->links() }}</div>
    @endif
    @endif
</div>

@endsection
