@extends('layouts.app')

@section('title', 'Dashboard — HR Manajemen')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan aktivitas SDM hari ini')

@section('content')

{{-- Notif: karyawan belum ada atasan --}}
@if(($pegawaiBelumAdaAtasan ?? 0) > 0)
<div class="mb-4 px-4 py-3 bg-orange-50 border border-orange-200 rounded-2xl flex items-center justify-between">
    <div class="flex items-center gap-3">
        <span class="text-orange-400 text-lg">⚠️</span>
        <div>
            <p class="text-sm font-semibold text-orange-800">
                {{ $pegawaiBelumAdaAtasan }} karyawan aktif belum memiliki atasan langsung
            </p>
            <p class="text-xs text-orange-600 mt-0.5">
                Pengajuan cuti & lembur mereka akan langsung masuk ke HRD.
            </p>
        </div>
    </div>
    <a href="{{ route('pengaturan.atasan.index', ['belum_diset' => 1]) }}"
       class="flex-shrink-0 px-3 py-1.5 text-xs bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-medium">
        Setting Sekarang
    </a>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- STATS CARDS --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

    {{-- Total Karyawan --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total_pegawai'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Karyawan Aktif</p>
        </div>
    </div>

    {{-- Hadir Hari Ini --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['hadir_hari_ini'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Hadir Hari Ini</p>
        </div>
    </div>

    {{-- Cuti Menunggu --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['cuti_menunggu'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Cuti Menunggu</p>
        </div>
    </div>

    {{-- Lembur Menunggu --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
        <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['lembur_menunggu'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Lembur Menunggu</p>
        </div>
    </div>

</div>

{{-- Info chips --}}
<div class="flex flex-wrap gap-3 mb-6">
    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm text-sm">
        <span class="w-2 h-2 rounded-full bg-red-400"></span>
        <span class="text-gray-500">Terlambat:</span>
        <span class="font-semibold text-gray-800">{{ $stats['terlambat_hari'] }}</span>
    </div>
    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm text-sm">
        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
        <span class="text-gray-500">Lowongan Buka:</span>
        <span class="font-semibold text-gray-800">{{ $stats['lowongan_buka'] }}</span>
    </div>
    <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm text-sm">
        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
        <span class="text-gray-500">Training Berjalan:</span>
        <span class="font-semibold text-gray-800">{{ $stats['training_berjalan'] }}</span>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 2: CHART + BIRTHDAY --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    {{-- Grafik Absensi 7 Hari --}}
    <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Grafik Kehadiran</h3>
                <p class="text-xs text-gray-400">7 hari terakhir</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-blue-500 inline-block"></span>Hadir</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-yellow-400 inline-block"></span>Terlambat</span>
                <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 rounded-full bg-red-400 inline-block"></span>Alfa</span>
            </div>
        </div>
        <div class="h-52">
            <canvas id="chartAbsensi"></canvas>
        </div>
    </div>

    {{-- Ulang Tahun --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-lg"></span>
            <div>
                <h3 class="text-sm font-semibold text-gray-800">Ulang Tahun Bulan Ini</h3>
                <p class="text-xs text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        @if($ultah->isEmpty())
        <div class="text-center py-6 text-gray-400 text-sm">
            Tidak ada ulang tahun bulan ini
        </div>
        @else
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @foreach($ultah as $pegawai)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 text-xs font-bold uppercase flex-shrink-0">
                    {{ substr($pegawai->nama, 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium text-gray-800 truncate">{{ $pegawai->nama }}</p>
                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($pegawai->tgl_lahir)->format('d M') }}</p>
                </div>
                @if(\Carbon\Carbon::parse($pegawai->tgl_lahir)->format('d') == now()->format('d'))
                <span class="text-xs bg-pink-100 text-pink-600 px-2 py-0.5 rounded-full font-medium">Hari ini!</span>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 3: CUTI TERBARU + LEMBUR MENUNGGU --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    {{-- Cuti Terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Pengajuan Cuti Terbaru</h3>
            <a href="{{ route('cuti.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        @if($cutiTerbaru->isEmpty())
        <div class="text-center py-8 text-gray-400 text-sm">Tidak ada pengajuan cuti</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($cutiTerbaru as $cuti)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold uppercase flex-shrink-0">
                    {{ substr($cuti->pegawai->nama ?? '?', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $cuti->pegawai->nama ?? '-' }}</p>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M') }}
                        @if(isset($cuti->tanggal_selesai))
                        — {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                        @endif
                    </p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-medium
                    {{ $cuti->status === 'approved' ? 'bg-green-100 text-green-700' :
                       ($cuti->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($cuti->status ?? 'Menunggu') }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Lembur Menunggu --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Lembur Menunggu Approval</h3>
            <a href="{{ route('lembur.index') }}" class="text-xs text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        @if($lemburMenunggu->isEmpty())
        <div class="text-center py-8 text-gray-400 text-sm">Tidak ada lembur menunggu</div>
        @else
        <div class="divide-y divide-gray-50">
            @foreach($lemburMenunggu as $lembur)
            <div class="flex items-center gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 text-xs font-bold uppercase flex-shrink-0">
                    {{ substr($lembur->pegawai->nama ?? '?', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $lembur->pegawai->nama ?? '-' }}</p>
                    <p class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($lembur->tanggal)->format('d M Y') }}
                        @if(isset($lembur->jam_mulai) && isset($lembur->jam_selesai))
                        · {{ $lembur->jam_mulai }} – {{ $lembur->jam_selesai }}
                        @endif
                    </p>
                </div>
                <span class="text-xs bg-orange-100 text-orange-700 px-2 py-1 rounded-full font-medium">
                    Menunggu
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartAbsensi');
    if (!ctx) return;

    const data = @json($grafikAbsensi);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.label),
            datasets: [
                {
                    label: 'Hadir',
                    data: data.map(d => d.hadir),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                },
                {
                    label: 'Terlambat',
                    data: data.map(d => d.terlambat),
                    borderColor: '#facc15',
                    backgroundColor: 'rgba(250,204,21,0.08)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#facc15',
                },
                {
                    label: 'Alfa',
                    data: data.map(d => d.alfa),
                    borderColor: '#f87171',
                    backgroundColor: 'rgba(248,113,113,0.08)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#f87171',
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#1e293b',
                    padding: 10,
                    titleFont: { size: 11 },
                    bodyFont: { size: 11 },
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 }, color: '#94a3b8', stepSize: 1 }
                }
            }
        }
    });
});
</script>
@endpush
