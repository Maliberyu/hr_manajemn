@extends('layouts.app')
@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi')
@section('page-subtitle', 'Data real-time dari tabel absensi')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4"
         x-data="{ mode: '{{ $modeRange ? 'range' : 'bulan' }}' }">

        <form method="GET" action="{{ route('absensi.rekap') }}" id="filterForm">

            {{-- Mode toggle --}}
            <div class="flex items-center gap-2 mb-4">
                <span class="text-xs font-medium text-gray-500">Filter:</span>
                <button type="button" @click="mode = 'bulan'"
                        :class="mode === 'bulan' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1 text-xs font-semibold rounded-lg transition">
                    Per Bulan
                </button>
                <button type="button" @click="mode = 'range'"
                        :class="mode === 'range' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1 text-xs font-semibold rounded-lg transition">
                    Per Tanggal
                </button>
            </div>

            <div class="flex flex-wrap gap-3 items-end">

                {{-- Mode: Bulan/Tahun --}}
                <template x-if="mode === 'bulan'">
                    <div class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                            <select name="bulan" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                @foreach(range(1,12) as $b)
                                <option value="{{ $b }}" {{ (!$modeRange && $bulan == $b) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null,$b)->translatedFormat('F') }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                            <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                @foreach(range(now()->year-2, now()->year+1) as $t)
                                <option value="{{ $t }}" {{ (!$modeRange && $tahun == $t) ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </template>

                {{-- Mode: Per Tanggal --}}
                <template x-if="mode === 'range'">
                    <div class="flex flex-wrap gap-3 items-end">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
                            <input type="date" name="tgl_mulai"
                                   value="{{ $modeRange ? $tglMulai : \Carbon\Carbon::create($tahun, $bulan, 1)->toDateString() }}"
                                   class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
                            <input type="date" name="tgl_akhir"
                                   value="{{ $modeRange ? $tglAkhir : \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->toDateString() }}"
                                   class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>
                    </div>
                </template>

                {{-- Filter lanjutan --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Departemen</label>
                    <select name="departemen" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">Semua</option>
                        @foreach($departemen as $dep)
                        <option value="{{ $dep->dep_id }}" {{ request('departemen') == $dep->dep_id ? 'selected' : '' }}>
                            {{ $dep->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Bidang / Unit</label>
                    <select name="bidang" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">Semua</option>
                        @foreach($bidangList as $b)
                        <option value="{{ $b }}" {{ request('bidang') == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Per Atasan</label>
                    <select name="atasan_id" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">Semua</option>
                        @foreach($atasanList as $a)
                        <option value="{{ $a->id }}" {{ request('atasan_id') == $a->id ? 'selected' : '' }}>
                            {{ $a->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                    Tampilkan
                </button>
                @if(request()->hasAny(['departemen','bidang','atasan_id','tgl_mulai','tgl_akhir']) || $modeRange)
                <a href="{{ route('absensi.rekap') }}"
                   class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Summary chips --}}
    @if($rekap->count() > 0)
    <div class="flex flex-wrap gap-3">
        <div class="flex items-center gap-2 bg-green-50 border border-green-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            <span class="text-green-700">Hadir:</span>
            <span class="font-bold text-green-800">{{ $totals->hadir ?? 0 }}</span>
        </div>
        <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
            <span class="text-yellow-700">Sakit:</span>
            <span class="font-bold text-yellow-800">{{ $totals->sakit ?? 0 }}</span>
        </div>
        <div class="flex items-center gap-2 bg-purple-50 border border-purple-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
            <span class="text-purple-700">Izin:</span>
            <span class="font-bold text-purple-800">{{ $totals->izin ?? 0 }}</span>
        </div>
        <div class="flex items-center gap-2 bg-red-50 border border-red-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-red-500"></span>
            <span class="text-red-700">Alfa:</span>
            <span class="font-bold text-red-800">{{ $totals->alfa ?? 0 }}</span>
        </div>
        <div class="flex items-center gap-2 bg-orange-50 border border-orange-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-orange-500"></span>
            <span class="text-orange-700">Terlambat:</span>
            <span class="font-bold text-orange-800">{{ $totals->terlambat ?? 0 }}x</span>
        </div>
    </div>
    @endif

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
            <div>
                <p class="font-semibold text-gray-800">
                    Rekap Absensi —
                    @if($modeRange)
                        {{ \Carbon\Carbon::parse($tglMulai)->translatedFormat('d M Y') }}
                        s/d {{ \Carbon\Carbon::parse($tglAkhir)->translatedFormat('d M Y') }}
                    @else
                        {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Data real-time dari tabel absensi</p>
            </div>
            <span class="text-sm text-gray-500">{{ $rekap->total() }} karyawan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Karyawan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Dept / Bidang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Hadir</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Sakit</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Ijin</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Alfa</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Cuti</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Terlambat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Tercatat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rekap as $i => $r)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $rekap->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $r->nama }}</div>
                            <div class="text-xs text-gray-400">{{ $r->nik }} · {{ $r->jbtn }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <div>{{ $r->dep_nama }}</div>
                            @if($r->bidang)
                            <div class="text-gray-400">{{ $r->bidang }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-green-600">{{ $r->total_hadir }}</td>
                        <td class="px-4 py-3 text-center {{ $r->total_sakit > 0 ? 'font-medium text-yellow-600' : 'text-gray-300' }}">
                            {{ $r->total_sakit ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center {{ $r->total_izin > 0 ? 'font-medium text-purple-600' : 'text-gray-300' }}">
                            {{ $r->total_izin ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center {{ $r->total_alfa > 0 ? 'font-semibold text-red-600' : 'text-gray-300' }}">
                            {{ $r->total_alfa ?: '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $r->total_cuti ?: '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($r->total_terlambat > 0)
                            <span class="text-orange-600 font-medium">{{ $r->total_terlambat }}x</span>
                            @if($r->total_menit_terlambat > 0)
                            <span class="block text-xs text-gray-400">
                                {{ floor($r->total_menit_terlambat / 60) > 0 ? floor($r->total_menit_terlambat/60).'j ' : '' }}{{ $r->total_menit_terlambat % 60 }}m
                            </span>
                            @endif
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-gray-400">{{ $r->total_hari_tercatat }}h</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center">
                            <div class="text-gray-300 text-4xl mb-3">📋</div>
                            <p class="text-sm text-gray-500 font-medium">Tidak ada data absensi</p>
                            <p class="text-xs text-gray-400 mt-1">untuk periode yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr class="font-semibold">
                        <td colspan="3" class="px-4 py-3 text-xs text-gray-600 text-right">Total (halaman ini)</td>
                        <td class="px-4 py-3 text-center text-green-700">{{ $rekap->sum('total_hadir') }}</td>
                        <td class="px-4 py-3 text-center text-yellow-700">{{ $rekap->sum('total_sakit') ?: '—' }}</td>
                        <td class="px-4 py-3 text-center text-purple-700">{{ $rekap->sum('total_izin') ?: '—' }}</td>
                        <td class="px-4 py-3 text-center text-red-700">{{ $rekap->sum('total_alfa') ?: '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $rekap->sum('total_cuti') ?: '—' }}</td>
                        <td class="px-4 py-3 text-center text-orange-700">{{ $rekap->sum('total_terlambat') }}x</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $rekap->sum('total_hari_tercatat') }}h</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($rekap->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $rekap->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
