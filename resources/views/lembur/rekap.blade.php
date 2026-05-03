@extends('layouts.app')
@section('title', 'Rekap Lembur')
@section('page-title', 'Rekap Lembur Bulanan')
@section('page-subtitle', 'Ringkasan lembur yang sudah disetujui')

@section('content')
<div class="space-y-4">

    {{-- Filter --}}
    <form method="GET" action="{{ route('lembur.rekap') }}"
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
                @foreach(range(now()->year-1, now()->year+1) as $t)
                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            Tampilkan
        </button>
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-800">
                    Rekap Lembur — {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Hanya pengajuan berstatus Disetujui</p>
            </div>
            <span class="text-sm text-gray-500">{{ $rekap->total() }} pegawai</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Pegawai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Departemen</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Pengajuan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Total Jam</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Total Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rekap as $i => $p)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ $rekap->firstItem() + $i }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $p->nama }}</div>
                            <div class="text-xs text-gray-400">{{ $p->jbtn }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $p->departemenRef?->nama ?? $p->departemen ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-gray-700 font-medium">{{ $p->total_pengajuan ?? 0 }}x</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $jam = (int) ($p->total_jam ?? 0);
                                $mnt = (int)((($p->total_jam ?? 0) - $jam) * 60);
                            @endphp
                            <span class="font-semibold text-blue-600">
                                {{ $jam }}j{{ $mnt > 0 ? ' '.$mnt.'m' : '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                            Rp {{ number_format($p->total_nominal ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada data lembur yang disetujui pada bulan ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($rekap->count() > 0)
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-xs font-semibold text-gray-600 text-right">
                            Total
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-blue-700">
                            @php
                                $totalJam = $rekap->sum('total_jam');
                                $tj  = (int) $totalJam;
                                $tm  = (int)(($totalJam - $tj) * 60);
                            @endphp
                            {{ $tj }}j{{ $tm > 0 ? ' '.$tm.'m' : '' }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-gray-800">
                            Rp {{ number_format($rekap->sum('total_nominal'), 0, ',', '.') }}
                        </td>
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
