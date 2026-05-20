@extends('layouts.app')
@section('title', 'Dashboard Rekrutmen')
@section('page-title', 'Rekrutmen')
@section('page-subtitle', 'Dashboard manajemen rekrutmen & SDM')

@section('content')
<div class="space-y-5">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $cards = [
                ['label'=>'Permintaan SDM', 'val'=>$stats['request_menunggu'], 'color'=>'yellow', 'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'desc'=>'Menunggu review', 'route'=>'rekrutmen.request.index'],
                ['label'=>'Lowongan Aktif', 'val'=>$stats['lowongan_aktif'],   'color'=>'green',  'icon'=>'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'desc'=>'Sedang buka', 'route'=>'rekrutmen.lowongan.index'],
                ['label'=>'Total Pelamar',  'val'=>$stats['total_pelamar'],    'color'=>'blue',   'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'desc'=>'Semua lowongan', 'route'=>'rekrutmen.pelamar.index'],
                ['label'=>'Interview Hari Ini','val'=>$stats['interview_hari_ini'],'color'=>'purple','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'desc'=>'Dijadwalkan hari ini','route'=>'rekrutmen.interview.index'],
            ];
        @endphp
        @foreach($cards as $c)
        <a href="{{ route($c['route']) }}"
           class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition group">
            <div class="w-12 h-12 rounded-xl bg-{{ $c['color'] }}-100 flex items-center justify-center flex-shrink-0 group-hover:bg-{{ $c['color'] }}-200 transition">
                <svg class="w-6 h-6 text-{{ $c['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $c['icon'] }}"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-extrabold text-gray-800">{{ $c['val'] }}</p>
                <p class="text-xs text-gray-500 font-medium">{{ $c['label'] }}</p>
                <p class="text-[10px] text-gray-400">{{ $c['desc'] }}</p>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Pipeline Funnel (HRD only) --}}
    @if($isHrd && $pipeline->isNotEmpty())
    @php
        $pipelineConfig = [
            'baru'       => ['Baru',        'bg-gray-100',   'text-gray-600',   $pipeline['baru']       ?? 0],
            'seleksi_cv' => ['Seleksi CV',  'bg-blue-100',   'text-blue-700',   $pipeline['seleksi_cv'] ?? 0],
            'interview'  => ['Interview',   'bg-yellow-100', 'text-yellow-700', $pipeline['interview']  ?? 0],
            'offering'   => ['Offering',    'bg-purple-100', 'text-purple-700', $pipeline['offering']   ?? 0],
            'diterima'   => ['Diterima',    'bg-green-100',  'text-green-700',  $pipeline['diterima']   ?? 0],
            'ditolak'    => ['Ditolak',     'bg-red-100',    'text-red-700',    $pipeline['ditolak']    ?? 0],
        ];
        $totalPipeline = array_sum(array_column($pipelineConfig, 3));
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Pipeline Rekrutmen</h3>
                <p class="text-xs text-gray-400">Total {{ $totalPipeline }} pelamar aktif</p>
            </div>
            <a href="{{ route('rekrutmen.pelamar.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
        </div>
        <div class="flex items-stretch gap-2">
            @foreach($pipelineConfig as $key => [$label, $bg, $color, $count])
            <div class="flex-1 text-center">
                <div class="{{ $bg }} rounded-xl p-3">
                    <p class="text-xl font-extrabold {{ $color }}">{{ $count }}</p>
                    <p class="text-xs font-medium {{ $color }} mt-0.5">{{ $label }}</p>
                </div>
                @if(!$loop->last)
                <div class="text-gray-300 text-lg mt-1">→</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Permintaan SDM Terbaru --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800">Permintaan SDM Terbaru</h3>
                <a href="{{ route('rekrutmen.request.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            @forelse($requestTerbaru as $r)
            <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 last:border-0">
                <div class="w-9 h-9 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 text-xs font-bold flex-shrink-0 uppercase">
                    {{ substr($r->pengaju?->nama ?? '?', 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $r->posisi }}</p>
                    <p class="text-xs text-gray-400">{{ $r->pengaju?->nama }} · {{ $r->departemenRef?->nama }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $r->badge_status }}">
                    {{ $r->label_status }}
                </span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-400">Belum ada permintaan SDM</div>
            @endforelse
            <div class="px-5 py-3 border-t border-gray-100">
                <a href="{{ route('rekrutmen.request.create') }}"
                   class="w-full block text-center py-2 text-xs font-semibold text-blue-600 border border-blue-200 rounded-xl hover:bg-blue-50 transition">
                    + Ajukan Permintaan SDM
                </a>
            </div>
        </div>

        {{-- Interview Terdekat --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800">Interview Terdekat</h3>
                <a href="{{ route('rekrutmen.interview.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            @forelse($interviewTerdekat as $iv)
            <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 last:border-0">
                <div class="flex-shrink-0 text-center w-10">
                    <p class="text-lg font-extrabold text-blue-600 leading-none">{{ $iv->jadwal->format('d') }}</p>
                    <p class="text-[10px] text-gray-400 uppercase">{{ $iv->jadwal->translatedFormat('M') }}</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $iv->pelamar?->nama }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $iv->label_tahap }} · {{ $iv->jadwal->format('H:i') }}
                        @if($iv->pewawancara) · {{ $iv->pewawancara->nama }} @endif
                    </p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $iv->badge_status }}">
                    {{ ucfirst($iv->metode) }}
                </span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-sm text-gray-400">Tidak ada jadwal interview</div>
            @endforelse
            @if($isHrd)
            <div class="px-5 py-3 border-t border-gray-100">
                <a href="{{ route('rekrutmen.interview.create') }}"
                   class="w-full block text-center py-2 text-xs font-semibold text-purple-600 border border-purple-200 rounded-xl hover:bg-purple-50 transition">
                    + Jadwalkan Interview
                </a>
            </div>
            @endif
        </div>

    </div>

    {{-- Grafik Pelamar (HRD only) --}}
    @if($isHrd && $grafikPelamar->isNotEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-1">Pelamar Masuk per Bulan</h3>
        <p class="text-xs text-gray-400 mb-4">6 bulan terakhir</p>
        <div class="h-44"><canvas id="chartPelamar"></canvas></div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
@if($isHrd && $grafikPelamar->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const data = @json($grafikPelamar);
    new Chart(document.getElementById('chartPelamar'), {
        type: 'bar',
        data: {
            labels: data.map(d => d.label),
            datasets: [{
                label: 'Pelamar',
                data: data.map(d => d.total),
                backgroundColor: '#3b82f6',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false },
                tooltip: { backgroundColor:'#1e293b', callbacks: { label: c => ` ${c.parsed.y} pelamar` } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size:11 }, color:'#94a3b8' } },
                y: { beginAtZero: true, grid: { color:'#f1f5f9' }, ticks: { font: { size:11 }, color:'#94a3b8', stepSize:1 } }
            }
        }
    });
});
</script>
@endif
@endpush
