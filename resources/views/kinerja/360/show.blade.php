@extends('layouts.app')
@section('title', 'Sesi 360°')
@section('page-title', 'Sesi Penilaian 360 Derajat')
@section('page-subtitle', $sesi->pegawai?->nama . ' — S' . $sesi->semester . '/' . $sesi->tahun)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-start justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-700 font-bold text-xl">
                {{ strtoupper(substr($sesi->pegawai?->nama ?? 'U', 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-base">{{ $sesi->pegawai?->nama }}</p>
                <p class="text-xs text-gray-400">{{ $sesi->pegawai?->jbtn }} · {{ $sesi->pegawai?->departemenRef?->nama }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Dibuat: {{ $sesi->created_at?->translatedFormat('d F Y') }} ·
                    Deadline: {{ $sesi->deadline?->translatedFormat('d F Y') ?? 'Tidak diatur' }}
                </p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-400 mb-1">S{{ $sesi->semester }} / {{ $sesi->tahun }}</p>
            @php
                $stColor = match($sesi->status) {
                    'aktif'   => 'bg-blue-100 text-blue-700',
                    'selesai' => 'bg-green-100 text-green-700',
                    default   => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-xl {{ $stColor }}">
                {{ ucfirst($sesi->status) }}
            </span>
        </div>
    </div>

    {{-- Progress --}}
    @php
        $total  = $sesi->raters->count();
        $submit = $sesi->raters->filter->sudahSubmit()->count();
        $pct    = $total > 0 ? round(($submit / $total) * 100) : 0;
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-semibold text-gray-700">Progress Pengisian</p>
            <span class="text-sm font-bold text-purple-600">{{ $submit }}/{{ $total }} rater</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
            <div class="bg-purple-500 h-2 rounded-full transition-all" style="width:{{ $pct }}%"></div>
        </div>

        {{-- Rater list --}}
        <div class="space-y-2">
            @foreach($sesi->raters as $rater)
            <div class="flex items-center gap-3 p-3 rounded-xl {{ $rater->sudahSubmit() ? 'bg-green-50' : 'bg-gray-50' }}">
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $rater->sudahSubmit() ? 'bg-green-100' : 'bg-gray-200' }}">
                    @if($rater->sudahSubmit())
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    @else
                    <div class="w-2 h-2 rounded-full bg-gray-400"></div>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-800">
                        {{ $rater->is_anonim && $rater->hubungan !== 'self' ? \App\Models\Rater360::HUBUNGAN[$rater->hubungan] . ' (Anonim)' : $rater->nama_rater }}
                    </p>
                    <p class="text-xs text-gray-400">
                        {{ \App\Models\Rater360::HUBUNGAN[$rater->hubungan] ?? $rater->hubungan }}
                        @if($rater->sudahSubmit())
                        · Disubmit {{ $rater->submitted_at?->translatedFormat('d M Y H:i') }}
                        @endif
                    </p>
                </div>
                @if(!$rater->sudahSubmit() && $sesi->status === 'aktif')
                <a href="{{ route('kinerja.360.form', [$sesi, 'rater' => $rater->id]) }}"
                   class="px-3 py-1 text-xs bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Isi Form
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap gap-2">
        @if($sesi->status === 'aktif')
        <form method="POST" action="{{ route('kinerja.360.tutup', $sesi) }}"
              onsubmit="return confirm('Tutup sesi dan hitung nilai akhir sekarang?')">
            @csrf
            <button type="submit"
                    class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                Tutup Sesi & Hitung Nilai
            </button>
        </form>
        @endif
        @if($sesi->status === 'selesai' || $submit > 0)
        <a href="{{ route('kinerja.360.rekap', $sesi) }}"
           class="px-4 py-2 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-medium transition">
            Lihat Rekap Hasil
        </a>
        @endif
        <a href="{{ route('kinerja.360.index', ['semester'=>$sesi->semester,'tahun'=>$sesi->tahun]) }}"
           class="px-4 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
            ← Kembali
        </a>
    </div>
</div>
@endsection
