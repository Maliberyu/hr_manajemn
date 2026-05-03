@extends('layouts.app')
@section('title', 'Jadwal Shift Kerja')
@section('page-title', 'Jadwal Shift Kerja')
@section('page-subtitle', 'Roster shift bulanan seluruh pegawai')

@section('content')
<div x-data="{ editMode: false }" class="space-y-4">

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('shift.index') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Bulan</label>
            <select name="bulan" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                @foreach(range(1,12) as $bln)
                <option value="{{ $bln }}" {{ $bulan == $bln ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null, $bln)->translatedFormat('F') }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tahun</label>
            <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                @foreach(range(now()->year - 1, now()->year + 1) as $thn)
                <option value="{{ $thn }}" {{ $tahun == $thn ? 'selected' : '' }}>{{ $thn }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Departemen</label>
            <select name="departemen" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">Semua Departemen</option>
                @foreach($departemen as $id => $nama)
                <option value="{{ $id }}" {{ $depId == $id ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            Tampilkan
        </button>
        <button type="button" @click="editMode = !editMode"
                :class="editMode ? 'bg-orange-500 hover:bg-orange-600' : 'bg-gray-500 hover:bg-gray-600'"
                class="px-4 py-2 text-sm text-white rounded-xl transition font-medium ml-auto">
            <span x-text="editMode ? 'Keluar Mode Edit' : 'Mode Edit Massal'"></span>
        </button>
    </form>

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Legend --}}
    <div class="flex flex-wrap gap-2 text-xs">
        @foreach([
            ['P',  'Pagi',          'bg-blue-100 text-blue-700'],
            ['S',  'Siang',         'bg-amber-100 text-amber-700'],
            ['M',  'Malam',         'bg-purple-100 text-purple-700'],
            ['MP', 'Midle Pagi',    'bg-cyan-100 text-cyan-700'],
            ['MS', 'Midle Siang',   'bg-orange-100 text-orange-700'],
            ['MM', 'Midle Malam',   'bg-indigo-100 text-indigo-700'],
            ['·',  'Libur/Off',     'bg-gray-100 text-gray-400'],
        ] as [$abbr, $nama, $cls])
        <span class="px-2 py-1 rounded-lg font-medium {{ $cls }}">{{ $abbr }} = {{ $nama }}</span>
        @endforeach
    </div>

    {{-- Roster table --}}
    <form method="POST" action="{{ route('shift.massal') }}" id="formMassal">
        @csrf
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Edit mode banner --}}
            <div x-show="editMode" style="display:none"
                 class="flex items-center gap-3 px-4 py-3 bg-orange-50 border-b border-orange-100">
                <svg class="w-4 h-4 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span class="text-sm text-orange-700 font-medium">Mode Edit Massal — ubah shift lalu klik Simpan</span>
                <button type="submit" form="formMassal"
                        class="ml-auto px-4 py-1.5 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition">
                    Simpan Semua
                </button>
            </div>

            {{-- Scrollable table --}}
            <div class="overflow-x-auto">
                <table class="min-w-max w-full text-xs">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="sticky left-0 z-10 bg-gray-50 px-3 py-2.5 text-left font-semibold text-gray-600 min-w-[170px]">
                                Nama Pegawai
                            </th>
                            @for($d = 1; $d <= $jumlahHari; $d++)
                            @php
                                $tgl = \Carbon\Carbon::create($tahun, $bulan, $d);
                                $isWeekend = $tgl->isWeekend();
                            @endphp
                            <th class="px-1 py-2 text-center min-w-[38px] {{ $isWeekend ? 'bg-red-50 text-red-400' : 'text-gray-500' }}">
                                <div class="text-gray-400">{{ $tgl->locale('id')->isoFormat('dd') }}</div>
                                <div class="font-bold text-gray-700">{{ $d }}</div>
                            </th>
                            @endfor
                            <th class="px-3 py-2.5 text-center font-semibold text-gray-600 min-w-[52px] bg-gray-50">
                                Masuk
                            </th>
                            <th class="px-3 py-2.5 text-center font-semibold text-gray-600 min-w-[52px]">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pegawai as $p)
                        @php
                            $jadwal      = $p->jadwalBulanan->first();
                            $totalMasuk  = $jadwal?->total_masuk ?? 0;
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="sticky left-0 z-10 bg-white hover:bg-gray-50/60 px-3 py-2 border-r border-gray-50">
                                <div class="font-medium text-gray-800 whitespace-nowrap">{{ $p->nama }}</div>
                                <div class="text-gray-400 text-xs truncate max-w-[160px]">{{ $p->jbtn }}</div>
                            </td>
                            @for($d = 1; $d <= $jumlahHari; $d++)
                            @php
                                $shift = $jadwal?->getHari($d) ?? '';
                                $abbr  = match($shift) {
                                    'Pagi'         => 'P',
                                    'Siang'        => 'S',
                                    'Malam'        => 'M',
                                    'Midle Pagi1'  => 'MP',
                                    'Midle Siang1' => 'MS',
                                    'Midle Malam1' => 'MM',
                                    default        => '·',
                                };
                                $badgeCls = match($shift) {
                                    'Pagi'         => 'bg-blue-100 text-blue-700',
                                    'Siang'        => 'bg-amber-100 text-amber-700',
                                    'Malam'        => 'bg-purple-100 text-purple-700',
                                    'Midle Pagi1'  => 'bg-cyan-100 text-cyan-700',
                                    'Midle Siang1' => 'bg-orange-100 text-orange-700',
                                    'Midle Malam1' => 'bg-indigo-100 text-indigo-700',
                                    default        => 'bg-gray-50 text-gray-300',
                                };
                                $tgl2 = \Carbon\Carbon::create($tahun, $bulan, $d);
                                $isWknd = $tgl2->isWeekend();
                            @endphp
                            <td class="px-0.5 py-1 text-center {{ $isWknd ? 'bg-red-50/20' : '' }}">
                                {{-- View badge --}}
                                <span x-show="!editMode"
                                      class="inline-flex items-center justify-center w-8 h-7 rounded-lg text-xs font-bold {{ $badgeCls }}">
                                    {{ $abbr }}
                                </span>
                                {{-- Edit select --}}
                                <select x-show="editMode" style="display:none"
                                        name="shifts[{{ $p->id }}][h{{ $d }}]"
                                        class="w-10 py-1 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-400 bg-white">
                                    @foreach(\App\Models\JadwalPegawai::SHIFT_OPTIONS as $opt)
                                    <option value="{{ $opt }}" {{ $shift === $opt ? 'selected' : '' }}>
                                        {{ $opt ?: '-' }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            @endfor
                            <td class="px-3 py-2 text-center font-semibold text-gray-700 bg-gray-50/50">
                                {{ $totalMasuk }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                <a href="{{ route('shift.edit', [$p, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                                   class="inline-flex items-center px-2.5 py-1 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition font-medium">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $jumlahHari + 3 }}"
                                class="px-6 py-10 text-center text-sm text-gray-400">
                                Tidak ada pegawai ditemukan untuk filter ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    {{-- Copy bulan lalu --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-800 w-full">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            Copy Jadwal dari Bulan Sebelumnya
            <svg class="w-4 h-4 ml-1 transition-transform" :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-collapse class="mt-4">
            @php
                $bAsal = $bulan == 1 ? 12 : $bulan - 1;
                $tAsal = $bulan == 1 ? $tahun - 1 : $tahun;
            @endphp
            <form method="POST" action="{{ route('shift.copy') }}"
                  class="flex flex-wrap gap-3 items-end"
                  onsubmit="return confirm('Copy jadwal dari {{ $bAsal }}/{{ $tAsal }} ke {{ $bulan }}/{{ $tahun }}? Data yang sudah ada akan ditimpa.')">
                @csrf
                <input type="hidden" name="bulan_tujuan" value="{{ $bulan }}">
                <input type="hidden" name="tahun_tujuan" value="{{ $tahun }}">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Filter Departemen</label>
                    <select name="departemen"
                            class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">Semua Departemen</option>
                        @foreach($departemen as $id => $nama)
                        <option value="{{ $id }}" {{ $depId == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-sm text-gray-500 self-center">
                    Dari <strong>{{ \Carbon\Carbon::create($tAsal, $bAsal)->translatedFormat('F Y') }}</strong>
                    → <strong>{{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}</strong>
                </p>
                <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                    Copy Sekarang
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
