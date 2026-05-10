@extends('layouts.app')
@section('title', $labelJenis)
@section('page-title', $labelJenis)
@section('page-subtitle', 'Riwayat & status pengajuan ' . strtolower($labelJenis))

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Header aksi --}}
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('ijin.create', $jenis) }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajukan {{ $labelJenis }}
        </a>

        {{-- Filter --}}
        <form method="GET" class="flex flex-wrap gap-2 ml-auto">
            <select name="status" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none">
                <option value="">Semua Status</option>
                @foreach(\App\Models\PengajuanIjin::STATUS as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <select name="bulan" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none">
                <option value="">Semua Bulan</option>
                @foreach(range(1,12) as $b)
                <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null,$b)->translatedFormat('F') }}
                </option>
                @endforeach
            </select>
            <select name="tahun" onchange="this.form.submit()"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none">
                @foreach(range(now()->year, now()->year - 2) as $t)
                <option value="{{ $t }}" {{ request('tahun', now()->year) == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">Daftar {{ $labelJenis }}</p>
            <span class="text-xs text-gray-400">{{ $daftar->total() }} pengajuan</span>
        </div>

        @if($daftar->isEmpty())
        <div class="text-center py-14 text-gray-400">
            <p class="text-sm">Belum ada pengajuan {{ strtolower($labelJenis) }}.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Pengajuan</th>
                        @if(auth()->user()->hasRole(['hrd','admin','atasan']))
                        <th class="px-4 py-3 text-left">Pegawai</th>
                        @endif
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        @if($jenis !== 'sakit')
                        <th class="px-4 py-3 text-center">Durasi</th>
                        @endif
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($daftar as $item)
                    @php
                        $colors = [
                            'Menunggu Atasan' => 'bg-yellow-100 text-yellow-700',
                            'Menunggu HRD'    => 'bg-blue-100 text-blue-700',
                            'Disetujui'       => 'bg-green-100 text-green-700',
                            'Ditolak Atasan'  => 'bg-red-100 text-red-600',
                            'Ditolak HRD'     => 'bg-red-100 text-red-600',
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $item->no_pengajuan }}</td>
                        @if(auth()->user()->hasRole(['hrd','admin','atasan']))
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $item->pegawai?->nama ?? $item->nik }}</p>
                            <p class="text-xs text-gray-400">{{ $item->pegawai?->jbtn }}</p>
                        </td>
                        @endif
                        <td class="px-4 py-3 text-gray-700">
                            {{ $item->tanggal->translatedFormat('d M Y') }}
                        </td>
                        @if($jenis !== 'sakit')
                        <td class="px-4 py-3 text-center text-xs text-gray-600 font-mono">
                            @if($item->jam_mulai && $item->jam_selesai)
                                {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }}
                                – {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                                <span class="text-gray-400">({{ $item->durasi_label }})</span>
                            @else
                                —
                            @endif
                        </td>
                        @endif
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-xl {{ $colors[$item->status] ?? 'bg-gray-100 text-gray-500' }}">
                                {{ $item->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('ijin.show', [$jenis, $item]) }}"
                               class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $daftar->withQueryString()->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
