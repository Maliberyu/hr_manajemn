@extends('layouts.app')
@section('title', 'Rekap KPI')
@section('page-title', 'Rekap KPI')
@section('page-subtitle', 'Laporan skor KPI Semester {{ $semester }} — {{ $tahun }}')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <form method="GET" action="{{ route('kpi.rekap') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tahun</label>
            <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                @foreach(range(now()->year-1, now()->year+1) as $t)
                <option value="{{ $t }}" {{ $tahun==$t?'selected':'' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Semester</label>
            <select name="semester" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="1" {{ $semester==1?'selected':'' }}>Semester 1 (Jan–Jun)</option>
                <option value="2" {{ $semester==2?'selected':'' }}>Semester 2 (Jul–Des)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Departemen</label>
            <select name="departemen" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">Semua</option>
                @foreach($departemen as $dep)
                <option value="{{ $dep->dep_id }}" {{ $depId==$dep->dep_id?'selected':'' }}>{{ $dep->nama }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">Tampilkan</button>
        <a href="{{ route('kpi.index', ['tahun'=>$tahun,'semester'=>$semester]) }}"
           class="px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition ml-auto">
            Dashboard KPI
        </a>
    </form>

    {{-- ═══ STATS CARDS ═══ --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-3xl font-extrabold text-gray-800">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Total Karyawan</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-3xl font-extrabold text-blue-600">{{ $stats['dinilai'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Ada Data KPI</p>
        </div>

        <div class="rounded-2xl p-4 text-center {{ $stats['rata_rata'] >= 75 ? 'bg-emerald-50 border border-emerald-200' : ($stats['rata_rata'] >= 60 ? 'bg-blue-50 border border-blue-200' : 'bg-orange-50 border border-orange-200') }}">
            <p class="text-3xl font-extrabold {{ $stats['rata_rata'] >= 75 ? 'text-emerald-600' : ($stats['rata_rata'] >= 60 ? 'text-blue-600' : 'text-orange-600') }}">
                {{ $stats['rata_rata'] ?? '—' }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Rata-rata KPI</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-3xl font-extrabold text-emerald-600">{{ $stats['tertinggi'] ?? '—' }}</p>
            <p class="text-xs text-gray-500 mt-1">Skor Tertinggi</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
            <p class="text-3xl font-extrabold text-red-500">{{ $stats['terendah'] ?? '—' }}</p>
            <p class="text-xs text-gray-500 mt-1">Skor Terendah</p>
        </div>

    </div>

    {{-- ═══ CHARTS ROW ═══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Distribusi Predikat --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">Distribusi Predikat</h3>
            <p class="text-xs text-gray-400 mb-4">Semester {{ $semester }} — {{ $tahun }}</p>

            @php
                $predConfig = [
                    'Istimewa' => ['#10b981','bg-emerald-100 text-emerald-700'],
                    'Puas'     => ['#3b82f6','bg-blue-100 text-blue-700'],
                    'Biasa'    => ['#f59e0b','bg-yellow-100 text-yellow-700'],
                    'Kurang'   => ['#f97316','bg-orange-100 text-orange-700'],
                    'Kecewa'   => ['#ef4444','bg-red-100 text-red-700'],
                ];
                $totalPred = $distribusi->sum();
            @endphp

            @if($totalPred > 0)
            <div class="flex items-center gap-6">
                <div class="w-40 h-40 flex-shrink-0">
                    <canvas id="chartDistribusi"></canvas>
                </div>
                <div class="flex-1 space-y-2">
                    @foreach($predConfig as $pred => [$color, $cls])
                    @php $n = $distribusi[$pred] ?? 0; $pct = $totalPred > 0 ? round($n/$totalPred*100) : 0; @endphp
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:{{ $color }}"></span>
                        <span class="text-xs text-gray-600 w-20">{{ $pred }}</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all" style="width:{{ $pct }}%; background:{{ $color }}"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-700 w-6 text-right">{{ $n }}</span>
                    </div>
                    @endforeach
                    <p class="text-xs text-gray-400 pt-1">{{ $totalPred }} karyawan dinilai dari {{ $stats['total'] }}</p>
                </div>
            </div>
            @else
            <div class="h-40 flex items-center justify-center text-sm text-gray-400">Belum ada data predikat periode ini</div>
            @endif
        </div>

        {{-- Rata-rata per Departemen --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">Rata-rata KPI per Departemen</h3>
            <p class="text-xs text-gray-400 mb-4">Diurutkan dari tertinggi</p>
            @if($perDep->isNotEmpty())
            <div style="height:160px"><canvas id="chartDep"></canvas></div>
            @else
            <div class="h-40 flex items-center justify-center text-sm text-gray-400">Belum ada data departemen</div>
            @endif
        </div>

    </div>

    {{-- ═══ TABEL REKAP ═══ --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
            <div>
                <p class="font-semibold text-gray-800">Detail Rekap KPI per Karyawan</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Bobot: Kehadiran {{ $bobot['kehadiran'] }}% · Disiplin {{ $bobot['disiplin'] }}% ·
                    Penilaian {{ $bobot['penilaian'] }}% · 360° {{ $bobot['p360'] }}% · Pelatihan {{ $bobot['pelatihan'] }}%
                </p>
            </div>
            <span class="text-sm text-gray-400">{{ $kpiList->count() }} karyawan · diurutkan dari skor tertinggi</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Karyawan</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-green-600">Kehadiran<br><span class="text-gray-400 font-normal">{{ $bobot['kehadiran'] }}%</span></th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-orange-500">Disiplin<br><span class="text-gray-400 font-normal">{{ $bobot['disiplin'] }}%</span></th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-600">Penilaian<br><span class="text-gray-400 font-normal">{{ $bobot['penilaian'] }}%</span></th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-purple-600">360°<br><span class="text-gray-400 font-normal">{{ $bobot['p360'] }}%</span></th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-indigo-500">Pelatihan<br><span class="text-gray-400 font-normal">{{ $bobot['pelatihan'] }}%</span></th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Skor KPI</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Predikat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($kpiList as $i => $r)
                    @php
                        $p = $r['pegawai'];
                        $predCls = match($r['predikat']) {
                            'Istimewa' => 'bg-emerald-100 text-emerald-700',
                            'Puas'     => 'bg-blue-100 text-blue-700',
                            'Biasa'    => 'bg-yellow-100 text-yellow-700',
                            'Kurang'   => 'bg-orange-100 text-orange-700',
                            'Kecewa'   => 'bg-red-100 text-red-700',
                            default    => 'bg-gray-100 text-gray-500',
                        };
                        $kpiColor = match(true) {
                            $r['skorKPI'] === null => '',
                            $r['skorKPI'] >= 75    => 'text-emerald-600',
                            $r['skorKPI'] >= 60    => 'text-blue-600',
                            $r['skorKPI'] >= 45    => 'text-orange-500',
                            default                => 'text-red-500',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center flex-shrink-0 uppercase">
                                    {{ substr($p->nama, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-sm">{{ $p->nama }}</p>
                                    <p class="text-xs text-gray-400">{{ $p->departemenRef?->nama ?? $p->departemen }} · {{ $p->jbtn }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Kehadiran --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skorKehadiran'] !== null)
                            <span class="font-bold text-green-700">{{ $r['skorKehadiran'] }}</span>
                            <span class="block text-[10px] text-gray-400">{{ $r['hadirPct'] }}% hadir</span>
                            @else <span class="text-gray-300">—</span> @endif
                        </td>

                        {{-- Disiplin --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skorDisiplin'] !== null)
                            <span class="font-bold {{ $r['skorDisiplin']>=80?'text-green-700':($r['skorDisiplin']>=60?'text-orange-500':'text-red-500') }}">
                                {{ $r['skorDisiplin'] }}
                            </span>
                            <span class="block text-[10px] text-gray-400">{{ $r['terlambat'] }}x tlb · {{ $r['alfa'] }}x alfa</span>
                            @else <span class="text-gray-300">—</span> @endif
                        </td>

                        {{-- Penilaian Prestasi --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skorPenilaian'] !== null)
                            <span class="font-bold text-blue-700">{{ number_format($r['skorPenilaian'],1) }}</span>
                            @else <span class="text-[10px] text-gray-400 italic">Belum dinilai</span> @endif
                        </td>

                        {{-- 360° --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skorP360'] !== null)
                            <span class="font-bold text-purple-700">{{ number_format($r['skorP360'],1) }}</span>
                            @else <span class="text-[10px] text-gray-400 italic">Belum</span> @endif
                        </td>

                        {{-- Pelatihan --}}
                        <td class="px-3 py-3 text-center">
                            @if($r['skorPelatihan'] !== null)
                            <span class="font-bold text-indigo-700">{{ $r['skorPelatihan'] }}</span>
                            <span class="block text-[10px] text-gray-400">{{ $r['jamTotal'] }}j</span>
                            @else <span class="text-gray-300">—</span> @endif
                        </td>

                        {{-- Skor KPI + mini bar --}}
                        <td class="px-4 py-3 text-center">
                            @if($r['skorKPI'] !== null)
                            <span class="text-lg font-extrabold {{ $kpiColor }}">{{ $r['skorKPI'] }}</span>
                            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-1">
                                <div class="h-1.5 rounded-full transition-all"
                                     style="width:{{ $r['skorKPI'] }}%; background:{{ $r['skorKPI']>=75?'#10b981':($r['skorKPI']>=60?'#3b82f6':($r['skorKPI']>=45?'#f97316':'#ef4444')) }}">
                                </div>
                            </div>
                            @else <span class="text-gray-300">—</span> @endif
                        </td>

                        {{-- Predikat --}}
                        <td class="px-4 py-3 text-center">
                            @if($r['predikat'])
                            <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full {{ $predCls }}">
                                {{ $r['predikat'] }}
                            </span>
                            @else <span class="text-xs text-gray-400">—</span> @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="px-6 py-12 text-center text-sm text-gray-400">Tidak ada data karyawan aktif.</td></tr>
                    @endforelse
                </tbody>

                {{-- Footer totals --}}
                @if($kpiList->count() > 0)
                @php
                    $punya = $kpiList->whereNotNull('skorKPI');
                @endphp
                <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-xs font-semibold text-gray-600">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-right">Rata-rata</td>
                        <td class="px-3 py-3 text-center text-green-700">
                            {{ $punya->count() ? round($punya->avg('skorKehadiran'),1) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-center text-orange-600">
                            {{ $punya->count() ? round($punya->avg('skorDisiplin'),1) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-center text-blue-700">
                            {{ $kpiList->whereNotNull('skorPenilaian')->count() ? round($kpiList->whereNotNull('skorPenilaian')->avg('skorPenilaian'),1) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-center text-purple-700">
                            {{ $kpiList->whereNotNull('skorP360')->count() ? round($kpiList->whereNotNull('skorP360')->avg('skorP360'),1) : '—' }}
                        </td>
                        <td class="px-3 py-3 text-center text-indigo-700">
                            {{ $punya->count() ? round($punya->avg('skorPelatihan'),1) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-800 text-base">
                            {{ $stats['rata_rata'] ?? '—' }}
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ─ Chart: Distribusi Predikat (doughnut) ─────────────────────────────────
    const ctxDist = document.getElementById('chartDistribusi');
    if (ctxDist) {
        const dist = @json($distribusi);
        const predLabels = ['Istimewa','Puas','Biasa','Kurang','Kecewa'];
        const predColors = ['#10b981','#3b82f6','#f59e0b','#f97316','#ef4444'];
        new Chart(ctxDist, {
            type: 'doughnut',
            data: {
                labels: predLabels,
                datasets: [{
                    data: predLabels.map(l => dist[l] ?? 0),
                    backgroundColor: predColors,
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#cbd5e1',
                        cornerRadius: 8, padding: { x:12, y:8 },
                        callbacks: { label: c => ` ${c.label}: ${c.parsed} orang` }
                    }
                }
            }
        });
    }

    // ─ Chart: Rata-rata per Departemen (horizontal bar) ──────────────────────
    const ctxDep = document.getElementById('chartDep');
    if (ctxDep) {
        const deps   = @json($depLabels);
        const depData = @json($perDep);
        const avgs   = depData.map(d => d.avg ?? 0);
        const counts = depData.map(d => d.count ?? 0);
        const colors  = avgs.map(v => v>=75?'#10b981': v>=60?'#3b82f6': v>=45?'#f97316':'#ef4444');

        new Chart(ctxDep, {
            type: 'bar',
            data: {
                labels: deps,
                datasets: [{
                    label: 'Rata-rata KPI',
                    data: avgs,
                    backgroundColor: colors,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b', titleColor: '#f1f5f9', bodyColor: '#cbd5e1',
                        cornerRadius: 8, padding: { x:12, y:8 },
                        callbacks: {
                            label: (c) => ` Avg KPI: ${c.parsed.x}`,
                            afterLabel: (c) => ` Jumlah: ${counts[c.dataIndex]} karyawan`,
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, max: 100, grid: { color: '#f1f5f9' },
                         ticks: { font: { size:11 }, color:'#94a3b8', callback: v=>v+'%' } },
                    y: { grid: { display: false }, ticks: { font: { size:11 }, color:'#374151' } }
                }
            }
        });
    }

});
</script>
@endpush
