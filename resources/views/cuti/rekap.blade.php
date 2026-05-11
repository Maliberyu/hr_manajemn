@extends('layouts.app')
@section('title', 'Rekap Cuti')
@section('page-title', 'Rekap Cuti Tahunan')
@section('page-subtitle', 'Rekapitulasi cuti per karyawan berdasarkan jenis')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <form method="GET" action="{{ route('cuti.rekap') }}"
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
                Tampilkan semua (termasuk 0 hari)
            </label>
        </div>
        <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            Tampilkan
        </button>
        @if(request()->hasAny(['departemen','bidang','atasan_id','tampil_semua']))
        <a href="{{ route('cuti.rekap', ['tahun'=>$tahun]) }}"
           class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
            Reset
        </a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-800">Rekap Cuti — Tahun {{ $tahun }}</p>
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
                        @foreach($jenisList as $jenis)
                        <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 whitespace-nowrap">{{ $jenis }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Total Hari</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Sisa Tahunan</th>
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
                        @foreach($jenisList as $jenis)
                        @php $row = $r['rows'][$jenis] ?? null; @endphp
                        <td class="px-3 py-3 text-center text-xs">
                            @if($row && $row->hari > 0)
                            <span class="font-medium text-blue-700">{{ $row->hari }}h</span>
                            <span class="text-gray-400">({{ $row->kali }}x)</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ $r['total_hari'] }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $sisa = $r['sisa_tahunan']; @endphp
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                {{ $sisa >= 8 ? 'bg-green-100 text-green-700' : ($sisa >= 4 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $sisa }} hari
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 5 + count($jenisList) }}" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada data cuti untuk periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-xs font-semibold text-gray-600 text-right">Total</td>
                        @foreach($jenisList as $jenis)
                        <td class="px-3 py-3 text-center text-xs font-bold text-blue-700">
                            {{ $rekap->sum(fn($r) => $r['rows'][$jenis]?->hari ?? 0) }}h
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-center font-bold text-gray-800">
                            {{ $rekap->sum('total_hari') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
