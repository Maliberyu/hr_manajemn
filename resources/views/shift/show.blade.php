@extends('layouts.app')
@section('title', 'Rekap Shift')
@section('page-title', 'Rekap Shift Pegawai')
@section('page-subtitle', $karyawan->nama)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700 font-bold text-lg flex-shrink-0">
            {{ strtoupper(substr($karyawan->nama, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <p class="font-semibold text-gray-800">{{ $karyawan->nama }}</p>
            <p class="text-xs text-gray-400">{{ $karyawan->jbtn }} &middot; NIK {{ $karyawan->nik }}</p>
        </div>
        <a href="{{ route('shift.edit', [$karyawan, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="ml-auto px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium flex-shrink-0">
            Edit Jadwal
        </a>
    </div>

    {{-- Month navigation --}}
    @php
        $prevBln = $bulan == 1 ? 12 : $bulan - 1;
        $prevThn = $bulan == 1 ? $tahun - 1 : $tahun;
        $nextBln = $bulan == 12 ? 1 : $bulan + 1;
        $nextThn = $bulan == 12 ? $tahun + 1 : $tahun;
    @endphp
    <div class="flex items-center justify-between bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-3">
        <a href="{{ route('shift.show', [$karyawan, 'bulan' => $prevBln, 'tahun' => $prevThn]) }}"
           class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600">
            ← Bulan Lalu
        </a>
        <span class="font-semibold text-gray-700">
            {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
        </span>
        <a href="{{ route('shift.show', [$karyawan, 'bulan' => $nextBln, 'tahun' => $nextThn]) }}"
           class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg hover:bg-gray-50 transition text-gray-600">
            Bulan Depan →
        </a>
    </div>

    @if(!$jadwal)
    {{-- No data --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
        <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="text-gray-400 text-sm">Belum ada jadwal untuk bulan ini.</p>
        <a href="{{ route('shift.edit', [$karyawan, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="inline-block mt-3 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
            Buat Jadwal
        </a>
    </div>

    @else

    {{-- Summary cards --}}
    @php
        $shiftCounts = [];
        for ($i = 1; $i <= $jumlahHari; $i++) {
            $s = $jadwal->getHari($i);
            if ($s) $shiftCounts[$s] = ($shiftCounts[$s] ?? 0) + 1;
        }
        $shiftMeta = [
            'Pagi'         => ['bg-blue-50',   'border-blue-200',   'text-blue-700'],
            'Siang'        => ['bg-amber-50',   'border-amber-200',  'text-amber-700'],
            'Malam'        => ['bg-purple-50',  'border-purple-200', 'text-purple-700'],
            'Midle Pagi1'  => ['bg-cyan-50',    'border-cyan-200',   'text-cyan-700'],
            'Midle Siang1' => ['bg-orange-50',  'border-orange-200', 'text-orange-700'],
            'Midle Malam1' => ['bg-indigo-50',  'border-indigo-200', 'text-indigo-700'],
        ];
        $totalMasuk = $jadwal->total_masuk;
        $totalLibur = $jumlahHari - $totalMasuk;
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-gray-800">{{ $totalMasuk }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Masuk</div>
        </div>
        <div class="bg-red-50 rounded-2xl border border-red-100 p-4 text-center">
            <div class="text-2xl font-bold text-red-500">{{ $totalLibur }}</div>
            <div class="text-xs text-red-400 mt-0.5">Libur / Off</div>
        </div>
        @foreach($shiftMeta as $nama => [$bg, $border, $text])
        @if($shiftCounts[$nama] ?? 0)
        <div class="{{ $bg }} rounded-2xl border {{ $border }} p-4 text-center">
            <div class="text-2xl font-bold {{ $text }}">{{ $shiftCounts[$nama] }}</div>
            <div class="text-xs {{ $text }} mt-0.5">{{ $nama }}</div>
        </div>
        @endif
        @endforeach
    </div>

    {{-- Calendar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-600 mb-3">Kalender Shift</p>

        <div class="grid grid-cols-7 gap-1.5 mb-1.5 text-center">
            @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $h)
            <div class="text-xs font-semibold py-1 {{ in_array($h, ['Min','Sab']) ? 'text-red-400' : 'text-gray-500' }}">
                {{ $h }}
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-1.5">
            @php $startDow = \Carbon\Carbon::create($tahun, $bulan, 1)->dayOfWeek; @endphp
            @for($i = 0; $i < $startDow; $i++)
            <div></div>
            @endfor

            @for($d = 1; $d <= $jumlahHari; $d++)
            @php
                $tgl       = \Carbon\Carbon::create($tahun, $bulan, $d);
                $isWeekend = $tgl->isWeekend();
                $shift     = $jadwal->getHari($d);
                $abbr      = match($shift) {
                    'Pagi'         => 'P',
                    'Siang'        => 'S',
                    'Malam'        => 'M',
                    'Midle Pagi1'  => 'MP',
                    'Midle Siang1' => 'MS',
                    'Midle Malam1' => 'MM',
                    default        => '',
                };
                $cellCls = match($shift) {
                    'Pagi'         => 'bg-blue-100 border-blue-200 text-blue-700',
                    'Siang'        => 'bg-amber-100 border-amber-200 text-amber-700',
                    'Malam'        => 'bg-purple-100 border-purple-200 text-purple-700',
                    'Midle Pagi1'  => 'bg-cyan-100 border-cyan-200 text-cyan-700',
                    'Midle Siang1' => 'bg-orange-100 border-orange-200 text-orange-700',
                    'Midle Malam1' => 'bg-indigo-100 border-indigo-200 text-indigo-700',
                    default        => $isWeekend ? 'bg-red-50 border-red-100 text-red-300' : 'bg-gray-50 border-gray-100 text-gray-300',
                };
            @endphp
            <div class="border rounded-xl p-2 text-center {{ $cellCls }}">
                <div class="text-xs mb-1 {{ $isWeekend && !$shift ? 'text-red-400' : 'opacity-60' }} font-medium">{{ $d }}</div>
                <div class="text-xs font-bold">{{ $abbr ?: '·' }}</div>
                @if($shift)
                <div class="leading-none mt-0.5 opacity-70" style="font-size:0.55rem">{{ $shift }}</div>
                @endif
            </div>
            @endfor
        </div>
    </div>

    @endif

    <a href="{{ route('shift.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
       class="inline-block text-sm text-gray-500 hover:text-gray-700 transition">
        ← Kembali ke Roster
    </a>

</div>
@endsection
