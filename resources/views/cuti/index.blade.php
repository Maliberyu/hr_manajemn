@extends('layouts.app')
@section('title', 'Pengajuan Cuti')
@section('page-title', 'Cuti Karyawan')
@section('page-subtitle', 'Manajemen pengajuan & persetujuan cuti')

@section('content')

{{-- Summary Cards ────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $totalAtasan }}</p>
            <p class="text-xs text-gray-500">Menunggu Atasan</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $totalHrd }}</p>
            <p class="text-xs text-gray-500">Menunggu HRD</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $pengajuan->total() }}</p>
            <p class="text-xs text-gray-500">Total Pengajuan</p>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-600">Saldo Cuti</p>
            <p class="text-xs text-gray-500">
                <a href="{{ route('cuti.saldo') }}" class="text-purple-600 hover:underline">Lihat rekap →</a>
            </p>
        </div>
    </div>
</div>

{{-- Filter ──────────────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / NIK..."
           class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 w-44">
    <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Status</option>
        @foreach(\App\Models\PengajuanCuti::STATUS as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
        @endforeach
    </select>
    <input type="number" name="tahun" value="{{ request('tahun', now()->year) }}" min="2020" max="2035"
           class="px-3 py-2 text-sm border border-gray-200 rounded-xl w-24 focus:outline-none focus:ring-2 focus:ring-blue-400">
    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
    <a href="{{ route('cuti.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>
    <div class="ml-auto">
        <a href="{{ route('cuti.create') }}"
           class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan Cuti
        </a>
    </div>
</form>

{{-- Flash ────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
@endif

{{-- Tabel ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Daftar Pengajuan Cuti</h3>
        <span class="text-xs text-gray-400">{{ $pengajuan->total() }} pengajuan</span>
    </div>

    @if($pengajuan->isEmpty())
    <div class="flex flex-col items-center gap-2 py-14 text-gray-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm font-medium">Belum ada pengajuan cuti</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">No. Pengajuan</th>
                    <th class="px-4 py-3 text-left">Pegawai</th>
                    <th class="px-4 py-3 text-left">Jenis</th>
                    <th class="px-4 py-3 text-left">Periode</th>
                    <th class="px-4 py-3 text-center">Hari</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Tanggal Ajuan</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($pengajuan as $p)
                @php
                    $color = match($p->status) {
                        'Menunggu Atasan' => 'yellow',
                        'Menunggu HRD'   => 'blue',
                        'Disetujui'      => 'green',
                        default          => 'red',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $p->no_pengajuan }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $p->pegawai?->nama }}</p>
                        <p class="text-xs text-gray-400">{{ $p->pegawai?->jbtn }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $p->urgensi }}</td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ $p->tanggal_awal->translatedFormat('d M Y') }}
                        @if($p->tanggal_awal != $p->tanggal_akhir)
                        <br>s/d {{ $p->tanggal_akhir->translatedFormat('d M Y') }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $p->jumlah }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium text-{{ $color }}-700 bg-{{ $color }}-50 px-2 py-0.5 rounded-full whitespace-nowrap">
                            {{ $p->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('cuti.show', $p) }}" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Detail</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $pengajuan->links() }}
    </div>
    @endif
</div>
@endsection
