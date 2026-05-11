@extends('layouts.app')
@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi Bulanan')
@section('page-subtitle', 'Detail kehadiran per karyawan')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <form method="GET" action="{{ route('absensi.rekap') }}"
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
        <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            Tampilkan
        </button>
        @if(request()->hasAny(['departemen','bidang','atasan_id']))
        <a href="{{ route('absensi.rekap', ['bulan'=>$bulan,'tahun'=>$tahun]) }}"
           class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
            Reset Filter
        </a>
        @endif

        {{-- Generate rekap --}}
        <div class="ml-auto">
            <form method="POST" action="{{ route('absensi.rekap.generate') }}" class="inline">
                @csrf
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <button type="submit"
                        class="px-4 py-2 text-sm bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition font-medium"
                        onclick="return confirm('Generate/update rekap bulan ini?')">
                    Generate Rekap
                </button>
            </form>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-800">
                    Rekap Absensi — {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Data dari tabel rekap_absensi (generate tiap akhir bulan)</p>
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
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Jam Lembur</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">% Hadir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rekap as $i => $r)
                    @php $p = $r->pegawai; @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $rekap->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $p?->nama ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $p?->nik ?? '' }} · {{ $p?->jbtn ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            <div>{{ $p?->departemenRef?->nama ?? $p?->departemen ?? '-' }}</div>
                            @if($p?->bidang)
                            <div class="text-gray-400">{{ $p->bidang }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-semibold text-green-600">{{ $r->total_hadir }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="{{ $r->total_sakit > 0 ? 'text-yellow-600 font-medium' : 'text-gray-400' }}">{{ $r->total_sakit }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="{{ $r->total_izin > 0 ? 'text-purple-600 font-medium' : 'text-gray-400' }}">{{ $r->total_izin }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="{{ $r->total_alfa > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $r->total_alfa }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $r->total_cuti }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($r->total_terlambat > 0)
                            <span class="inline-flex flex-col items-center">
                                <span class="text-orange-600 font-medium">{{ $r->total_terlambat }}x</span>
                                <span class="text-xs text-gray-400">{{ $r->total_menit_terlambat }}m</span>
                            </span>
                            @else
                            <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $tj = (int)$r->total_lembur_jam; $tm = (int)(($r->total_lembur_jam - $tj) * 60); @endphp
                            <span class="text-blue-600 font-medium">
                                {{ $tj > 0 || $tm > 0 ? $tj.'j'.($tm > 0 ? ' '.$tm.'m' : '') : '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $pct = $r->persentase_hadir; @endphp
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                {{ $pct >= 90 ? 'bg-green-100 text-green-700' : ($pct >= 75 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $pct }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada data rekap untuk periode ini. Coba generate rekap terlebih dahulu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-xs font-semibold text-gray-600 text-right">Total</td>
                        <td class="px-4 py-3 text-center font-bold text-green-700">{{ $rekap->sum('total_hadir') }}</td>
                        <td class="px-4 py-3 text-center font-bold text-yellow-700">{{ $rekap->sum('total_sakit') }}</td>
                        <td class="px-4 py-3 text-center font-bold text-purple-700">{{ $rekap->sum('total_izin') }}</td>
                        <td class="px-4 py-3 text-center font-bold text-red-700">{{ $rekap->sum('total_alfa') }}</td>
                        <td class="px-4 py-3 text-center font-bold text-gray-600">{{ $rekap->sum('total_cuti') }}</td>
                        <td class="px-4 py-3 text-center font-bold text-orange-700">{{ $rekap->sum('total_terlambat') }}x</td>
                        <td class="px-4 py-3 text-center font-bold text-blue-700">
                            @php $totalLembur = $rekap->sum('total_lembur_jam'); $tl = (int)$totalLembur; $tlm = (int)(($totalLembur - $tl)*60); @endphp
                            {{ $tl }}j{{ $tlm > 0 ? ' '.$tlm.'m' : '' }}
                        </td>
                        <td class="px-4 py-3 text-center"></td>
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
