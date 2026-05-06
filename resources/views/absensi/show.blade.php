@extends('layouts.app')
@section('title', 'Rekap Absensi — ' . $karyawan->nama)
@section('page-title', 'Rekap Absensi Karyawan')
@section('page-subtitle', $karyawan->nama . ' · ' . \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y'))

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Header Karyawan --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700 font-bold text-xl flex-shrink-0">
            {{ strtoupper(substr($karyawan->nama, 0, 1)) }}
        </div>
        <div class="flex-1">
            <p class="font-bold text-gray-800">{{ $karyawan->nama }}</p>
            <p class="text-sm text-gray-500">{{ $karyawan->jbtn }} · {{ $karyawan->departemenRef?->nama }}</p>
            <p class="text-xs text-gray-400 mt-0.5">NIK {{ $karyawan->nik }}</p>
        </div>
        {{-- Filter bulan/tahun --}}
        <form method="GET" class="flex gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-400 mb-1">Bulan</label>
                <select name="bulan" class="px-2 py-1.5 text-sm border border-gray-200 rounded-xl focus:outline-none">
                    @foreach(range(1,12) as $b)
                    <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null,$b)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1">Tahun</label>
                <select name="tahun" class="px-2 py-1.5 text-sm border border-gray-200 rounded-xl focus:outline-none">
                    @foreach(range(now()->year-1, now()->year+1) as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                Tampilkan
            </button>
        </form>
    </div>

    {{-- Rekap ringkasan --}}
    @if($rekap)
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach(['hadir'=>['Hadir','green'],'izin'=>['Izin','blue'],'sakit'=>['Sakit','yellow'],'alfa'=>['Alfa','red'],'terlambat'=>['Terlambat','orange']] as $key => [$label, $color])
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-{{ $color }}-600">{{ $rekap->$key ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Tabel absensi harian --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-700">Detail Absensi Harian</p>
        </div>

        @if($absensi->isEmpty())
        <p class="px-5 py-10 text-center text-sm text-gray-400">Tidak ada data absensi bulan ini.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Masuk</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Keluar</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Durasi</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Terlambat</th>
                        @if(auth()->user()->hasRole(['hrd','admin']))
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Foto Masuk</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Foto Keluar</th>
                        @endif
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($absensi as $a)
                    @php
                        $stColor = match($a->status) {
                            'hadir'  => 'bg-green-100 text-green-700',
                            'izin'   => 'bg-blue-100 text-blue-700',
                            'sakit'  => 'bg-yellow-100 text-yellow-700',
                            'alfa'   => 'bg-red-100 text-red-600',
                            default  => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-4 py-3 text-gray-700 font-medium">
                            {{ $a->tanggal->translatedFormat('d M Y') }}
                            <span class="text-xs text-gray-400 ml-1">{{ $a->tanggal->translatedFormat('l') }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-xl {{ $stColor }}">
                                {{ ucfirst($a->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 font-mono text-xs">
                            {{ $a->jam_masuk?->format('H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 font-mono text-xs">
                            {{ $a->jam_keluar?->format('H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 text-xs">
                            {{ $a->durasi_kerja ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-xs">
                            @if($a->terlambat_menit > 0)
                            <span class="text-orange-600 font-medium">{{ $a->terlambat_label }}</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>

                        {{-- Foto — hanya HRD & Admin --}}
                        @if(auth()->user()->hasRole(['hrd','admin']))
                        <td class="px-4 py-3 text-center">
                            @if($a->foto_masuk_url)
                            <a href="{{ $a->foto_masuk_url }}" target="_blank"
                               class="inline-block group">
                                <img src="{{ $a->foto_masuk_url }}"
                                     class="w-10 h-10 rounded-lg object-cover border border-gray-200 group-hover:border-blue-400 transition"
                                     alt="Foto masuk">
                            </a>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($a->foto_keluar_url)
                            <a href="{{ $a->foto_keluar_url }}" target="_blank"
                               class="inline-block group">
                                <img src="{{ $a->foto_keluar_url }}"
                                     class="w-10 h-10 rounded-lg object-cover border border-gray-200 group-hover:border-blue-400 transition"
                                     alt="Foto keluar">
                            </a>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        @endif

                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('absensi.edit', $a) }}"
                               class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <a href="{{ route('absensi.index') }}"
       class="inline-block text-sm text-gray-500 hover:text-gray-700 transition">← Kembali ke Absensi</a>
</div>
@endsection
