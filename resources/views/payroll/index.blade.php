@extends('layouts.app')
@section('title', 'Payroll')
@section('page-title', 'Payroll Gaji')
@section('page-subtitle', 'Generate dan kelola slip gaji bulanan')

@section('content')
<div class="space-y-4">

    {{-- Stats + actions --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $totalDraft }}</div>
            <div class="text-xs text-yellow-600 mt-0.5">Slip Draft</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $totalFinal }}</div>
            <div class="text-xs text-green-600 mt-0.5">Slip Final</div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-gray-700">{{ $pegawai->total() }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Pegawai</div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-3 flex flex-col gap-1.5">
            <a href="{{ route('payroll.master') }}"
               class="block text-center py-1.5 text-xs border border-blue-200 text-blue-600 hover:bg-blue-50 rounded-xl transition font-medium">
                 Master Gaji
            </a>
            <a href="{{ route('payroll.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
               class="block text-center py-1.5 text-xs border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Export CSV
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filter + Generate --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <form method="GET" action="{{ route('payroll.index') }}" class="flex flex-wrap gap-3 items-end flex-1">
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
            <div>
                <label class="block text-xs text-gray-500 mb-1">Departemen</label>
                <select name="departemen" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach($departemen as $id => $nama)
                    <option value="{{ $id }}" {{ $depId == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                Tampilkan
            </button>
        </form>

        {{-- Generate Slip --}}
        <form method="POST" action="{{ route('payroll.generate') }}"
              onsubmit="return confirm('Generate slip draft untuk {{ \Carbon\Carbon::create($tahun,$bulan)->translatedFormat('F Y') }}? Slip final tidak akan ditimpa.')">
            @csrf
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <button type="submit"
                    class="px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold transition">
                 Generate Slip Draft
            </button>
        </form>
    </div>

    {{-- Table pegawai --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 text-xs font-semibold text-gray-500 bg-gray-50">
            Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Pegawai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Golongan / UMK</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Gaji Bersih</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Status Slip</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pegawai as $p)
                    @php $slip = $p->slipGaji->first(); @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $p->nama }}</div>
                            <div class="text-xs text-gray-400">{{ $p->jbtn }} · {{ $p->departemenRef?->nama }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($p->payrollSetting?->golongan)
                            <div class="text-xs font-medium text-gray-700">{{ $p->payrollSetting->golongan }}</div>
                            <div class="text-xs text-gray-400">UMK {{ $p->payrollSetting->umk_tahun }}</div>
                            @else
                            <span class="text-xs text-orange-500 font-medium">Belum diset</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            @if($slip)
                            Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                            @if($slip)
                            Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(!$slip)
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-400 rounded-xl">Belum Diproses</span>
                            @elseif($slip->status === 'final')
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-xl font-semibold">Final</span>
                            @else
                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-xl font-semibold">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($slip)
                            <a href="{{ route('payroll.slip.show', $slip) }}"
                               class="px-3 py-1 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition font-medium">
                                Detail
                            </a>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada data pegawai aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pegawai->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $pegawai->links() }}</div>
        @endif
    </div>
</div>
@endsection
