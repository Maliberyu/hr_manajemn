@extends('layouts.app')
@section('title', 'Rekap Ijin')
@section('page-title', 'Rekap Ijin Bulanan')
@section('page-subtitle', 'Rekapitulasi sakit · telat · pulang duluan per karyawan')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <form method="GET" action="{{ route('ijin.rekap') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
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
                @foreach(range(now()->year-2, now()->year+1) as $t)
                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
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
        <div class="flex items-end gap-2">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer pb-2">
                <input type="checkbox" name="tampil_semua" value="1"
                       {{ request('tampil_semua') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600">
                Semua karyawan
            </label>
        </div>
        <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            Tampilkan
        </button>
        @if(request()->hasAny(['departemen','bidang','atasan_id','tampil_semua']))
        <a href="{{ route('ijin.rekap', ['bulan'=>$bulan,'tahun'=>$tahun]) }}"
           class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
            Reset
        </a>
        @endif
    </form>

    {{-- Summary chips --}}
    <div class="flex flex-wrap gap-3">
        @php
            $totalSakit = $rekap->sum(fn($r) => $r['rows']['sakit']?->kali ?? 0);
            $totalTelat = $rekap->sum(fn($r) => $r['rows']['terlambat']?->kali ?? 0);
            $totalPulang = $rekap->sum(fn($r) => $r['rows']['pulang_duluan']?->kali ?? 0);
        @endphp
        <div class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-yellow-400"></span>
            <span class="text-yellow-700">Sakit:</span>
            <span class="font-bold text-yellow-800">{{ $totalSakit }}x</span>
        </div>
        <div class="flex items-center gap-2 bg-orange-50 border border-orange-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
            <span class="text-orange-700">Terlambat:</span>
            <span class="font-bold text-orange-800">{{ $totalTelat }}x</span>
        </div>
        <div class="flex items-center gap-2 bg-purple-50 border border-purple-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-purple-400"></span>
            <span class="text-purple-700">Pulang Duluan:</span>
            <span class="font-bold text-purple-800">{{ $totalPulang }}x</span>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-800">
                    Rekap Ijin — {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Hanya pengajuan berstatus Disetujui</p>
            </div>
            <span class="text-sm text-gray-500">{{ $rekap->count() }} karyawan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Karyawan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Dept / Bidang</th>
                        @foreach($jenisList as $key => $label)
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600">{{ $label }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rekap as $i => $r)
                    @php $p = $r['pegawai']; @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $p->nama }}</div>
                            <div class="text-xs text-gray-400">{{ $p->nik }} · {{ $p->jbtn }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <div>{{ $p->departemenRef?->nama ?? $p->departemen ?? '-' }}</div>
                            @if($p->bidang)
                            <div class="text-gray-400">{{ $p->bidang }}</div>
                            @endif
                        </td>
                        @foreach($jenisList as $key => $label)
                        @php
                            $row = $r['rows'][$key] ?? null;
                            $colorMap = ['sakit' => 'yellow', 'terlambat' => 'orange', 'pulang_duluan' => 'purple'];
                            $color = $colorMap[$key] ?? 'blue';
                        @endphp
                        <td class="px-3 py-3 text-center">
                            @if($row && $row->kali > 0)
                            <span class="font-semibold text-{{ $color }}-600">{{ $row->kali }}x</span>
                            @if($row->total_menit)
                            <span class="block text-xs text-gray-400">
                                {{ floor($row->total_menit/60) }}j{{ $row->total_menit%60 > 0 ? ($row->total_menit%60).'m' : '' }}
                            </span>
                            @endif
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $r['total'] }}x</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 4 + count($jenisList) }}" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada data ijin untuk periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-xs font-semibold text-gray-600 text-right">Total</td>
                        @foreach($jenisList as $key => $label)
                        <td class="px-3 py-3 text-center text-xs font-bold text-gray-700">
                            {{ $rekap->sum(fn($r) => $r['rows'][$key]?->kali ?? 0) }}x
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-center font-bold text-gray-800">
                            {{ $rekap->sum('total') }}x
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
