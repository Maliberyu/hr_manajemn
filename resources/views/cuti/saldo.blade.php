@extends('layouts.app')
@section('title', 'Saldo Cuti')
@section('page-title', 'Saldo Cuti Karyawan')
@section('page-subtitle', 'Rekap hak & pemakaian cuti tahun ' . now()->year)

@section('content')

<div class="flex items-center justify-between mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="departemen" value="{{ request('departemen') }}"
               placeholder="Filter departemen..."
               class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
        <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
        <a href="{{ route('cuti.saldo') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>
    </form>
    <a href="{{ route('cuti.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
        ← Daftar Pengajuan
    </a>
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Saldo Cuti Tahunan {{ now()->year }}</h3>
        <span class="text-xs text-gray-400">{{ $pegawai->total() }} pegawai aktif</span>
    </div>

    @if($pegawai->isEmpty())
    <p class="text-sm text-gray-400 text-center py-10">Tidak ada data pegawai aktif.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Pegawai</th>
                    <th class="px-4 py-3 text-left">Departemen</th>
                    <th class="px-4 py-3 text-center">Hak Cuti</th>
                    <th class="px-4 py-3 text-center">Diambil</th>
                    <th class="px-4 py-3 text-center">Sisa</th>
                    <th class="px-4 py-3 text-center">Proporsi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($pegawai as $p)
                @php
                    $hak      = \App\Models\PengajuanCuti::HAK_CUTI_TAHUNAN;
                    $diambil  = (int)($p->cuti_tahun_ini ?? 0);
                    $sisa     = $hak - $diambil;
                    $pct      = $hak > 0 ? round(($diambil / $hak) * 100) : 0;
                    $barColor = $pct >= 100 ? 'bg-red-400' : ($pct >= 75 ? 'bg-yellow-400' : 'bg-green-400');
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $p->nama }}</p>
                        <p class="text-xs text-gray-400">{{ $p->jbtn }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $p->departemenRef?->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $hak }}</td>
                    <td class="px-4 py-3 text-center font-semibold {{ $diambil > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $diambil }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-bold text-base {{ $sisa <= 0 ? 'text-red-600' : ($sisa <= 3 ? 'text-orange-600' : 'text-green-600') }}">
                            {{ max(0, $sisa) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 w-32">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="{{ $barColor }} h-1.5 rounded-full transition-all"
                                     style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500 w-8 text-right">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $pegawai->links() }}
    </div>
    @endif
</div>
@endsection
