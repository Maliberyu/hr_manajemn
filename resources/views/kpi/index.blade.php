@extends('layouts.app')
@section('title', 'Dashboard KPI')
@section('page-title', 'Dashboard KPI')
@section('page-subtitle', 'Key Performance Indicator — Semester {{ $semester }} Tahun {{ $tahun }}')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <form method="GET" action="{{ route('kpi.index') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tahun</label>
            <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                @foreach(range(now()->year-1, now()->year+1) as $t)
                <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Semester</label>
            <select name="semester" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="1" {{ $semester == 1 ? 'selected' : '' }}>Semester 1 (Jan–Jun)</option>
                <option value="2" {{ $semester == 2 ? 'selected' : '' }}>Semester 2 (Jul–Des)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Departemen</label>
            <select name="departemen" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">Semua</option>
                @foreach($departemen as $dep)
                <option value="{{ $dep->dep_id }}" {{ $depId == $dep->dep_id ? 'selected' : '' }}>{{ $dep->nama }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            Tampilkan
        </button>
        @if($depId)
        <a href="{{ route('kpi.index', ['tahun'=>$tahun,'semester'=>$semester]) }}"
           class="px-4 py-2 text-sm border border-gray-200 text-gray-500 rounded-xl hover:bg-gray-50 transition">Reset</a>
        @endif
        <div class="ml-auto flex gap-2">
            <a href="{{ route('kpi.rekap', ['tahun'=>$tahun,'semester'=>$semester]) }}"
               class="px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Rekap
            </a>
            <a href="{{ route('kpi.target', ['tahun'=>$tahun]) }}"
               class="px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                </svg>
                Target
            </a>
        </div>
    </form>

    {{-- Ringkasan predikat --}}
    @if($ringkasan->isNotEmpty())
    @php
        $predikatConfig = [
            'Istimewa' => ['bg-emerald-50','border-emerald-200','text-emerald-700'],
            'Puas'     => ['bg-blue-50',   'border-blue-200',   'text-blue-700'],
            'Biasa'    => ['bg-yellow-50',  'border-yellow-200',  'text-yellow-700'],
            'Kurang'   => ['bg-orange-50', 'border-orange-200', 'text-orange-700'],
            'Kecewa'   => ['bg-red-50',    'border-red-200',    'text-red-700'],
        ];
    @endphp
    <div class="flex flex-wrap gap-3">
        @foreach($predikatConfig as $pred => $cls)
        @php $count = $ringkasan[$pred] ?? 0; @endphp
        <div class="flex items-center gap-3 px-4 py-2.5 bg-white border border-gray-100 rounded-2xl shadow-sm">
            <span class="text-xl font-extrabold {{ $cls[2] }}">{{ $count }}</span>
            <div>
                <p class="text-xs font-semibold {{ $cls[2] }}">{{ $pred }}</p>
                <p class="text-[10px] text-gray-400">karyawan</p>
            </div>
        </div>
        @endforeach
        <div class="flex items-center gap-3 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl">
            <span class="text-xl font-extrabold text-gray-400">{{ $kpiList->whereNull('skor_kpi')->count() }}</span>
            <div>
                <p class="text-xs font-semibold text-gray-500">Belum Ada Data</p>
                <p class="text-[10px] text-gray-400">karyawan</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Info bobot komponen --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-3 flex flex-wrap gap-4 text-xs text-gray-500">
        <span class="font-semibold text-gray-700">Bobot KPI:</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>Kehadiran 25%</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>Disiplin 15%</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>Penilaian Prestasi 30%</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500 inline-block"></span>360° 20%</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-indigo-400 inline-block"></span>Pelatihan 10%</span>
        <span class="ml-auto text-gray-400">Skor dihitung dari komponen yang tersedia</span>
    </div>

    {{-- Tabel KPI --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">
                KPI Semester {{ $semester }} — {{ $tahun }}
            </p>
            <span class="text-xs text-gray-400">{{ $kpiList->count() }} karyawan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-600">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-3 py-3 text-center">
                            <span class="text-green-600">Kehadiran</span><br>
                            <span class="text-[10px] font-normal text-gray-400">25%</span>
                        </th>
                        <th class="px-3 py-3 text-center">
                            <span class="text-orange-500">Disiplin</span><br>
                            <span class="text-[10px] font-normal text-gray-400">15%</span>
                        </th>
                        <th class="px-3 py-3 text-center">
                            <span class="text-blue-600">Penilaian</span><br>
                            <span class="text-[10px] font-normal text-gray-400">30%</span>
                        </th>
                        <th class="px-3 py-3 text-center">
                            <span class="text-purple-600">360°</span><br>
                            <span class="text-[10px] font-normal text-gray-400">20%</span>
                        </th>
                        <th class="px-3 py-3 text-center">
                            <span class="text-indigo-500">Pelatihan</span><br>
                            <span class="text-[10px] font-normal text-gray-400">10%</span>
                        </th>
                        <th class="px-4 py-3 text-center">Skor KPI</th>
                        <th class="px-4 py-3 text-center">Predikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($kpiList as $i => $r)
                    @php
                        $p = $r['pegawai'];
                        $predColors = [
                            'Istimewa' => 'bg-emerald-100 text-emerald-700',
                            'Puas'     => 'bg-blue-100 text-blue-700',
                            'Biasa'    => 'bg-yellow-100 text-yellow-700',
                            'Kurang'   => 'bg-orange-100 text-orange-700',
                            'Kecewa'   => 'bg-red-100 text-red-700',
                        ];
                        $predCls = $predColors[$r['predikat']] ?? 'bg-gray-100 text-gray-500';
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $p->nama }}</div>
                            <div class="text-xs text-gray-400">{{ $p->departemenRef?->nama ?? $p->departemen }}</div>
                        </td>

                        {{-- Kehadiran --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skor_kehadiran'] !== null)
                            <span class="font-semibold text-green-700">{{ $r['skor_kehadiran'] }}</span>
                            <span class="block text-[10px] text-gray-400">{{ $r['hadir_pct'] }}% hadir</span>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Disiplin --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skor_disiplin'] !== null)
                            <span class="font-semibold {{ $r['skor_disiplin'] >= 80 ? 'text-green-700' : ($r['skor_disiplin'] >= 60 ? 'text-orange-600' : 'text-red-600') }}">
                                {{ $r['skor_disiplin'] }}
                            </span>
                            <span class="block text-[10px] text-gray-400">
                                {{ $r['terlambat'] }}x terlambat · {{ $r['alfa'] }}x alfa
                            </span>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Penilaian Prestasi --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skor_penilaian'] !== null)
                            <span class="font-semibold text-blue-700">{{ number_format($r['skor_penilaian'], 1) }}</span>
                            @else
                            <span class="text-[10px] text-gray-400 italic">Belum dinilai</span>
                            @endif
                        </td>

                        {{-- 360° --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skor_p360'] !== null)
                            <span class="font-semibold text-purple-700">{{ number_format($r['skor_p360'], 1) }}</span>
                            @else
                            <span class="text-[10px] text-gray-400 italic">Belum dinilai</span>
                            @endif
                        </td>

                        {{-- Pelatihan --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skor_pelatihan'] !== null)
                            <span class="font-semibold text-indigo-700">{{ $r['skor_pelatihan'] }}</span>
                            <span class="block text-[10px] text-gray-400">{{ $r['jam_pelatihan'] }}j / {{ \App\Http\Controllers\Kpi\KpiController::TARGET_JAM_PELATIHAN }}j</span>
                            @else
                            <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Skor KPI --}}
                        <td class="px-4 py-3 text-center">
                            @if($r['skor_kpi'] !== null)
                            @php
                                $warna = $r['skor_kpi'] >= 75 ? 'text-emerald-700' : ($r['skor_kpi'] >= 60 ? 'text-blue-700' : ($r['skor_kpi'] >= 45 ? 'text-orange-600' : 'text-red-600'));
                            @endphp
                            <span class="text-lg font-extrabold {{ $warna }}">{{ $r['skor_kpi'] }}</span>
                            <span class="block text-[10px] text-gray-400">dari {{ $r['komponen_ada'] }} komponen</span>
                            @else
                            <span class="text-gray-300 text-sm">—</span>
                            @endif
                        </td>

                        {{-- Predikat --}}
                        <td class="px-4 py-3 text-center">
                            @if($r['predikat'])
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $predCls }}">
                                {{ $r['predikat'] }}
                            </span>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-400">
                            Tidak ada data karyawan aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Catatan --}}
    <div class="text-xs text-gray-400 space-y-1 px-1">
        <p><strong>Catatan:</strong> Skor KPI dihitung dari komponen yang sudah ada datanya. Komponen kosong (—) tidak diperhitungkan dalam bobot.</p>
        <p>Disiplin: nilai 100 dikurangi 5 per hari alfa dan 2 per kejadian terlambat. Pelatihan: target {{ \App\Http\Controllers\Kpi\KpiController::TARGET_JAM_PELATIHAN }} jam/semester.</p>
    </div>

</div>
@endsection
