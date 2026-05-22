@extends('layouts.app')
@section('title', 'Ijin Khusus')
@section('page-title', 'Ijin Khusus')
@section('page-subtitle', 'Pengajuan & riwayat ijin khusus karyawan')

@section('content')

{{-- Flash ───────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Summary Chips (HRD/Atasan) ──────────────────────────────────────────────── --}}
@if($isHrd)
@php
    $pending = $list->getCollection()->whereIn('status', ['Menunggu Atasan','Menunggu HRD'])->count();
    $disetujui = $list->getCollection()->where('status','Disetujui')->count();
    $ditolak = $list->getCollection()->whereIn('status',['Ditolak Atasan','Ditolak HRD'])->count();
@endphp
@if($list->getCollection()->isNotEmpty())
<div class="flex flex-wrap gap-2 mb-5">
    @if($pending)
    <div class="flex items-center gap-2 px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-xl">
        <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
        <span class="text-xs font-semibold text-yellow-700">{{ $pending }} Menunggu</span>
    </div>
    @endif
    @if($disetujui)
    <div class="flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-xl">
        <div class="w-2 h-2 rounded-full bg-green-400"></div>
        <span class="text-xs font-semibold text-green-700">{{ $disetujui }} Disetujui</span>
    </div>
    @endif
    @if($ditolak)
    <div class="flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-xl">
        <div class="w-2 h-2 rounded-full bg-red-400"></div>
        <span class="text-xs font-semibold text-red-700">{{ $ditolak }} Ditolak</span>
    </div>
    @endif
</div>
@endif
@endif

{{-- Toolbar ──────────────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-4">
    <form method="GET" action="{{ route('ijin-khusus.index') }}" class="flex flex-wrap gap-2 flex-1">
        <select name="jenis_id" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
            <option value="">Semua Jenis</option>
            @foreach($jenisList as $j)
            <option value="{{ $j->id }}" {{ request('jenis_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
            @endforeach
        </select>

        <select name="status" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
            <option value="">Semua Status</option>
            @foreach(['Menunggu Atasan','Menunggu HRD','Disetujui','Ditolak Atasan','Ditolak HRD'] as $st)
            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
            @endforeach
        </select>

        <select name="bulan" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
            <option value="">Semua Bulan</option>
            @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create(null,$m)->translatedFormat('F') }}
            </option>
            @endforeach
        </select>

        <select name="tahun" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-300">
            @foreach(range(date('Y'), date('Y')-4) as $yr)
            <option value="{{ $yr }}" {{ request('tahun', date('Y')) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>

        @if(request()->hasAny(['jenis_id','status','bulan']) || request('tahun') != date('Y'))
        <a href="{{ route('ijin-khusus.index') }}"
           class="flex items-center gap-1.5 px-3 py-2 text-sm text-gray-500 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset
        </a>
        @endif
    </form>

    <a href="{{ route('ijin-khusus.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Ajukan Ijin Khusus
    </a>
</div>

{{-- Table ───────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    @if($list->isEmpty())
    <div class="py-16 text-center">
        <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-500">Belum ada pengajuan ijin khusus</p>
        <p class="text-xs text-gray-400 mt-1">Gunakan tombol "Ajukan Ijin Khusus" untuk membuat pengajuan baru.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Pengajuan</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pegawai</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Ijin</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Durasi</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($list as $item)
                @php
                    $badge = match(true) {
                        $item->status === 'Menunggu Atasan'                           => 'bg-amber-50 text-amber-700 border border-amber-200',
                        $item->status === 'Menunggu HRD'                              => 'bg-blue-50 text-blue-700 border border-blue-200',
                        $item->status === 'Disetujui'                                 => 'bg-green-50 text-green-700 border border-green-200',
                        str_starts_with($item->status, 'Ditolak')                     => 'bg-red-50 text-red-700 border border-red-200',
                        default                                                        => 'bg-gray-50 text-gray-600 border border-gray-200',
                    };
                    $dot = match(true) {
                        str_contains($item->status,'Menunggu') => 'bg-amber-400',
                        $item->status === 'Disetujui'          => 'bg-green-400',
                        default                                => 'bg-red-400',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3.5">
                        <span class="font-mono text-xs text-gray-500">{{ $item->no_pengajuan }}</span>
                    </td>
                    <td class="px-4 py-3.5">
                        @php $initials = collect(explode(' ', $item->pegawai->nama))->take(2)->map(fn($w)=>strtoupper($w[0]))->join(''); @endphp
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-white">{{ $initials }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 text-sm">{{ $item->pegawai->nama }}</p>
                                <p class="text-xs text-gray-400">{{ $item->pegawai->nik }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs rounded-lg font-medium bg-purple-50 text-purple-700 border border-purple-100">
                            {{ $item->jenis->nama }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-gray-700 text-xs">
                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M Y') }}
                        @if($item->tanggal_akhir && $item->tanggal_akhir != $item->tanggal_mulai)
                        <span class="text-gray-400">–</span>
                        {{ \Carbon\Carbon::parse($item->tanggal_akhir)->translatedFormat('d M Y') }}
                        @endif
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="text-xs font-semibold text-gray-700">{{ $item->durasi_label }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-full font-medium {{ $badge }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <a href="{{ route('ijin-khusus.show', $item) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($list->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $list->withQueryString()->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
