@extends('layouts.app')
@section('title', 'Rekap Pelatihan')
@section('page-title', 'Rekap Jam Pelatihan')
@section('page-subtitle', 'Total jam IHT & pelatihan eksternal per karyawan')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <form method="GET" action="{{ route('training.rekap') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
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
        <a href="{{ route('training.rekap', ['tahun'=>$tahun]) }}"
           class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
            Reset
        </a>
        @endif
    </form>

    {{-- Summary chips --}}
    @php
        $totalIHT   = $rekap->sum('jam_iht');
        $totalEkst  = $rekap->sum('jam_eksternal');
        $totalAll   = $rekap->sum('jam_total');
        $ikutLatihan = $rekap->filter(fn($r) => $r['jam_total'] > 0)->count();
    @endphp
    <div class="flex flex-wrap gap-3">
        <div class="flex items-center gap-2 bg-indigo-50 border border-indigo-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
            <span class="text-indigo-700">IHT:</span>
            <span class="font-bold text-indigo-800">{{ number_format($totalIHT, 1) }} jam</span>
        </div>
        <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-emerald-700">Eksternal:</span>
            <span class="font-bold text-emerald-800">{{ number_format($totalEkst, 1) }} jam</span>
        </div>
        <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 px-4 py-2 rounded-xl text-sm">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
            <span class="text-blue-700">Total:</span>
            <span class="font-bold text-blue-800">{{ number_format($totalAll, 1) }} jam</span>
        </div>
        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 px-4 py-2 rounded-xl text-sm">
            <span class="text-gray-500">Ikut pelatihan:</span>
            <span class="font-bold text-gray-700">{{ $ikutLatihan }} karyawan</span>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-800">Rekap Pelatihan — Tahun {{ $tahun }}</p>
                <p class="text-xs text-gray-400 mt-0.5">IHT: jam dihitung dari jam_mulai–jam_selesai × hari. Eksternal: asumsi 8 jam/hari.</p>
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
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Jam IHT</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Jam Eksternal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Total Jam</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Jumlah Pelatihan</th>
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
                        <td class="px-4 py-3 text-center">
                            @if($r['jam_iht'] > 0)
                            <span class="font-semibold text-indigo-700">{{ number_format($r['jam_iht'], 1) }}</span>
                            <span class="text-xs text-gray-400 block">{{ $r['kali_iht'] }}x</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($r['jam_eksternal'] > 0)
                            <span class="font-semibold text-emerald-700">{{ number_format($r['jam_eksternal'], 1) }}</span>
                            <span class="text-xs text-gray-400 block">{{ $r['kali_eksternal'] }}x</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-blue-700">{{ number_format($r['jam_total'], 1) }}</span>
                            <span class="text-xs text-gray-400 block">jam</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $r['kali_iht'] + $r['kali_eksternal'] }}x
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada data pelatihan untuk periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-xs font-semibold text-gray-600 text-right">Total</td>
                        <td class="px-4 py-3 text-center font-bold text-indigo-700">{{ number_format($totalIHT, 1) }}</td>
                        <td class="px-4 py-3 text-center font-bold text-emerald-700">{{ number_format($totalEkst, 1) }}</td>
                        <td class="px-4 py-3 text-center font-bold text-blue-700">{{ number_format($totalAll, 1) }}</td>
                        <td class="px-4 py-3 text-center font-bold text-gray-700">
                            {{ $rekap->sum(fn($r) => $r['kali_iht'] + $r['kali_eksternal']) }}x
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
