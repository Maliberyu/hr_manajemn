@extends('layouts.app')
@section('title', 'Realisasi Jadwal Shift')
@section('page-title', 'Realisasi Jadwal Shift')
@section('page-subtitle', 'Kehadiran aktual vs jadwal rencana')

@section('content')

@php
    $bulanNama = \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y');
    $shiftColor = [
        'pagi'  => 'bg-blue-100 text-blue-700',
        'sore'  => 'bg-amber-100 text-amber-700',
        'malam' => 'bg-purple-100 text-purple-700',
        'libur' => 'bg-gray-100 text-gray-400',
    ];
    $shiftAbbr = fn($kode) => match($kode) {
        'pagi'  => 'P',
        'sore'  => 'S',
        'malam' => 'M',
        'libur' => '-',
        default => strtoupper(substr($kode, 0, 2)),
    };
@endphp

{{-- ── Filter Bar ────────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('shift.realisasi.index') }}"
      class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end mb-4">
    <div>
        <label class="block text-xs text-gray-500 mb-1">Bulan</label>
        <select name="bulan" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            @foreach(range(1,12) as $b)
            <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create(null,$b)->translatedFormat('F') }}
            </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Tahun</label>
        <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            @foreach(range(now()->year-1, now()->year+1) as $y)
            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Mode</label>
        <select name="mode" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="departemen" {{ $mode === 'departemen' ? 'selected' : '' }}>Per Departemen</option>
            <option value="orang"      {{ $mode === 'orang'      ? 'selected' : '' }}>Per Orang</option>
        </select>
    </div>
    <div>
        <label class="block text-xs text-gray-500 mb-1">Departemen</label>
        <select name="departemen" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua</option>
            @foreach($departemen as $id => $nama)
            <option value="{{ $id }}" {{ $depId == $id ? 'selected' : '' }}>{{ $nama }}</option>
            @endforeach
        </select>
    </div>
    @if($mode === 'orang')
    <div>
        <label class="block text-xs text-gray-500 mb-1">Pegawai</label>
        <select name="pegawai" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            @foreach($pegawaiList as $p)
            <option value="{{ $p->nik }}" {{ $nikPeg == $p->nik ? 'selected' : '' }}>{{ $p->nama }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
        Tampilkan
    </button>
    {{-- Print button --}}
    <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank"
       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Cetak
    </a>
</form>

{{-- ── Legend ────────────────────────────────────────────────────────────────── --}}
<div class="flex flex-wrap gap-2 text-xs mb-4">
    <span class="px-2 py-1 rounded-lg font-medium bg-blue-100 text-blue-700">P = Pagi</span>
    <span class="px-2 py-1 rounded-lg font-medium bg-amber-100 text-amber-700">S = Sore</span>
    <span class="px-2 py-1 rounded-lg font-medium bg-purple-100 text-purple-700">M = Malam</span>
    <span class="px-2 py-1 rounded-lg font-medium bg-gray-100 text-gray-400">– = Libur</span>
    <span class="px-2 py-1 rounded-lg font-medium bg-green-100 text-green-700">✓ = Hadir</span>
    <span class="px-2 py-1 rounded-lg font-medium bg-red-100 text-red-600">✗ = Absen</span>
    <span class="px-2 py-1 rounded-lg font-medium bg-orange-100 text-orange-700">⇄ = Tukar Shift</span>
    <span class="px-2 py-1 rounded-lg font-medium bg-yellow-100 text-yellow-700">L = Lembur</span>
</div>

@if($realisasi)

{{-- ══════════════════════════════ MODE PER ORANG ═══════════════════════════ --}}
@if($mode === 'orang' && isset($realisasi[0]))
@php $row = $realisasi[0]; $peg = $row['pegawai']; @endphp
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <p class="font-bold text-gray-800">{{ $peg->nama }}</p>
            <p class="text-xs text-gray-400 font-mono">{{ $peg->nik }} · {{ $peg->departemen_nama ?? $peg->departemen }}</p>
        </div>
        <p class="text-sm font-semibold text-blue-600">{{ $bulanNama }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider w-32">Hari</th>
                    <th class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Shift Rencana</th>
                    <th class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Masuk</th>
                    <th class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Keluar</th>
                    <th class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-left px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($row['hari_data'] as $hari => $d)
                @php
                    $isToday = $d['tanggal']->isToday();
                    $isWeekend = $d['tanggal']->isWeekend();
                    $hadir = $d['absensi'] !== null;
                    $rowBg = $isToday ? 'bg-blue-50/40' : ($isWeekend ? 'bg-gray-50/40' : '');
                @endphp
                <tr class="{{ $rowBg }} hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-2.5">
                        <p class="font-semibold text-sm {{ $isToday ? 'text-blue-600' : 'text-gray-800' }}">
                            {{ $d['tanggal']->translatedFormat('l') }}
                        </p>
                        <p class="text-xs text-gray-400">{{ $hari }} {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('M Y') }}</p>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @if($d['shift_rencana'])
                        @php $kode = $d['shift_rencana']->kode; $cls = $shiftColor[$kode] ?? 'bg-gray-100 text-gray-600'; @endphp
                        <span class="inline-block px-2 py-0.5 text-xs font-bold rounded-lg {{ $cls }}">
                            {{ $d['shift_rencana']->nama }}
                        </span>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $d['shift_rencana']->jam_label }}</p>
                        @elseif($d['nama_rencana'])
                        <span class="text-xs text-gray-500">{{ $d['nama_rencana'] }}</span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="text-sm font-mono {{ $hadir ? 'text-gray-800' : 'text-gray-300' }}">
                            {{ $d['jam_masuk'] ?? '—' }}
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="text-sm font-mono {{ $hadir ? 'text-gray-800' : 'text-gray-300' }}">
                            {{ $d['jam_keluar'] ?? '—' }}
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        @if($d['is_libur'] && !$hadir)
                        <span class="text-xs text-gray-400">Libur</span>
                        @elseif($hadir)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full border border-green-100">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            Hadir
                        </span>
                        @elseif($d['shift_rencana'] && !$d['is_libur'])
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 px-2 py-0.5 rounded-full border border-red-100">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            Absen
                        </span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5">
                        <div class="flex flex-wrap gap-1.5">
                            @if($d['is_tukar'])
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-orange-50 text-orange-700 rounded-full border border-orange-100">
                                ⇄ Tukar Shift
                            </span>
                            @endif
                            @if($d['ovt_menit'] >= 30)
                            @php $ovtJam = floor($d['ovt_menit'] / 60); $ovtMin = $d['ovt_menit'] % 60; @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-yellow-50 text-yellow-700 rounded-full border border-yellow-100">
                                L {{ $ovtJam > 0 ? $ovtJam.'j' : '' }} {{ $ovtMin > 0 ? $ovtMin.'m' : '' }}
                            </span>
                            @endif
                            @foreach($d['lembur'] as $lb)
                            @php
                                $lbColor = match($lb->status) {
                                    'Draft'    => 'bg-gray-50 text-gray-500 border-gray-200',
                                    'Disetujui'=> 'bg-green-50 text-green-700 border-green-100',
                                    default    => 'bg-yellow-50 text-yellow-700 border-yellow-100',
                                };
                            @endphp
                            <span class="px-2 py-0.5 text-xs rounded-full border {{ $lbColor }}">
                                Lembur {{ $lb->durasi_label ?? ($lb->durasi_jam . 'j') }}
                                @if($lb->status === 'Draft')
                                <a href="{{ route('lembur.show', $lb) }}" class="ml-1 underline">Konfirmasi</a>
                                @endif
                            </span>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════ MODE PER DEPARTEMEN ═══════════════════════ --}}
@else
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <p class="font-semibold text-gray-800">Realisasi Jadwal — {{ $bulanNama }}
            @if($depId) · {{ $departemen[$depId] ?? '' }} @endif
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="text-xs border-collapse w-max min-w-full">
            <thead>
                <tr class="bg-gray-50/70 border-b border-gray-200">
                    <th class="sticky left-0 z-10 bg-gray-50 px-3 py-2 text-left font-semibold text-gray-500 uppercase tracking-wider min-w-40 border-r border-gray-200">
                        Pegawai
                    </th>
                    @for($h = 1; $h <= $jumlahHari; $h++)
                    @php
                        $tgl = \Carbon\Carbon::create($tahun, $bulan, $h);
                        $isWe = $tgl->isWeekend();
                    @endphp
                    <th class="px-1.5 py-2 text-center font-semibold min-w-10 {{ $isWe ? 'text-red-400' : 'text-gray-500' }}">
                        <div>{{ $h }}</div>
                        <div class="text-gray-300 font-normal">{{ $tgl->translatedFormat('D') }}</div>
                    </th>
                    @endfor
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($realisasi as $row)
                @php $peg = $row['pegawai']; @endphp
                <tr class="hover:bg-gray-50/40">
                    <td class="sticky left-0 z-10 bg-white px-3 py-2 border-r border-gray-100">
                        <p class="font-semibold text-gray-800 truncate max-w-36">{{ $peg->nama }}</p>
                        <p class="text-gray-400 font-mono">{{ $peg->nik }}</p>
                    </td>
                    @foreach($row['hari_data'] as $hari => $d)
                    @php
                        $kode    = $d['shift_real']?->kode ?? null;
                        $cls     = $shiftColor[$kode] ?? 'bg-gray-50 text-gray-400';
                        $abbr    = $d['shift_real'] ? $shiftAbbr($kode) : ($d['nama_rencana'] ? strtoupper(substr($d['nama_rencana'],0,1)) : '·');
                        $hadir   = $d['absensi'] !== null;
                        $border  = $hadir ? 'ring-1 ring-green-400' : ($d['shift_rencana'] && !$d['is_libur'] ? 'ring-1 ring-red-200' : '');
                    @endphp
                    <td class="px-1 py-1 text-center {{ $d['tanggal']->isWeekend() ? 'bg-gray-50/50' : '' }}">
                        <div class="relative inline-flex flex-col items-center">
                            <span class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold {{ $cls }} {{ $border }}">
                                {{ $abbr }}
                            </span>
                            {{-- Badge indicators --}}
                            @if($d['is_tukar'] || $d['ovt_menit'] >= 30 || $d['lembur']->isNotEmpty())
                            <span class="absolute -top-1 -right-1 flex gap-0.5">
                                @if($d['is_tukar'])
                                <span class="w-2.5 h-2.5 bg-orange-400 rounded-full" title="Tukar Shift"></span>
                                @endif
                                @if($d['ovt_menit'] >= 30 || $d['lembur']->isNotEmpty())
                                <span class="w-2.5 h-2.5 bg-yellow-400 rounded-full" title="Lembur"></span>
                                @endif
                            </span>
                            @endif
                            {{-- Attendance dot --}}
                            @if($hadir)
                            <span class="w-1 h-1 bg-green-500 rounded-full mt-0.5"></span>
                            @elseif($d['shift_rencana'] && !$d['is_libur'])
                            <span class="w-1 h-1 bg-red-400 rounded-full mt-0.5"></span>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Legend badges --}}
    <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap gap-3 text-xs text-gray-500">
        <span class="flex items-center gap-1"><span class="w-2 h-2 bg-green-400 rounded-full"></span> Hadir</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 bg-red-400 rounded-full"></span> Absen</span>
        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-orange-400 rounded-full"></span> Tukar Shift</span>
        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 bg-yellow-400 rounded-full"></span> Lembur</span>
    </div>
</div>
@endif

@else
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-14 text-center">
    <p class="text-sm text-gray-400">Tidak ada data untuk ditampilkan.</p>
</div>
@endif

@endsection
