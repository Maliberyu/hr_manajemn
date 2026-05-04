@extends('layouts.app')
@section('title', 'Rekap 360°')
@section('page-title', 'Rekap Penilaian 360 Derajat')
@section('page-subtitle', $sesi->pegawai?->nama . ' — S' . $sesi->semester . '/' . $sesi->tahun)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    {{-- Header + Nilai Akhir --}}
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-2xl p-5 text-white flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center font-bold text-xl">
                {{ strtoupper(substr($sesi->pegawai?->nama ?? 'U', 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-base">{{ $sesi->pegawai?->nama }}</p>
                <p class="text-xs text-purple-200">{{ $sesi->pegawai?->jbtn }} · {{ $sesi->pegawai?->departemenRef?->nama }}</p>
                <p class="text-xs text-purple-300 mt-0.5">S{{ $sesi->semester }} / {{ $sesi->tahun }}</p>
            </div>
        </div>
        @if($sesi->nilai_akhir)
        <div class="text-right">
            <p class="text-xs opacity-80">Nilai Akhir</p>
            <p class="text-3xl font-bold">{{ $sesi->nilai_akhir }}</p>
            <p class="text-xs opacity-80">/100</p>
        </div>
        @endif
    </div>

    {{-- Bobot Rater --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-600 mb-3">Bobot Suara Per Hubungan</p>
        <div class="flex flex-wrap gap-2">
            @foreach(['atasan'=>'Atasan','rekan'=>'Rekan','bawahan'=>'Bawahan','self'=>'Self'] as $key => $label)
            <div class="px-3 py-2 bg-gray-50 rounded-xl text-center">
                <div class="text-sm font-bold text-gray-800">{{ $bobotRater[$key] ?? 0 }}%</div>
                <div class="text-xs text-gray-500">{{ $label }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Rekap per Dimensi --}}
    @foreach($dimensi as $dim)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-700">{{ $dim->nama }}</p>
            <span class="text-xs text-gray-400">Bobot {{ $dim->bobot }}%</span>
        </div>

        <div class="space-y-3">
            @foreach($dim->aspek as $aspek)
            <div class="p-3 bg-gray-50 rounded-xl">
                <p class="text-sm font-medium text-gray-800 mb-2">{{ $aspek->nama }}</p>
                <div class="space-y-1.5">
                    @foreach(['atasan'=>'Atasan','rekan'=>'Rekan','bawahan'=>'Bawahan','self'=>'Self'] as $hubKey => $hubLabel)
                    @php
                        $nilaiArr = $rekapNilai[$hubKey][$aspek->id] ?? [];
                        $avg      = count($nilaiArr) > 0 ? round(array_sum($nilaiArr) / count($nilaiArr), 1) : null;
                        $skala    = \App\Models\PenilaianPrestasi::SKALA[round($avg)] ?? '';
                    @endphp
                    @if($avg !== null)
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500 w-16">{{ $hubLabel }}</span>
                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                            <div class="bg-purple-500 h-1.5 rounded-full" style="width:{{ ($avg/5)*100 }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 w-8 text-right">{{ $avg }}</span>
                        <span class="text-xs text-gray-400 w-16">{{ $skala }}</span>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- Komentar Kualitatif (anonim) --}}
    @php $komentarList = $sesi->komentar->filter(fn($k) => $k->kekuatan || $k->area_pengembangan || $k->saran); @endphp
    @if($komentarList->count())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Komentar Kualitatif</p>
        @foreach($komentarList as $kom)
        <div class="mb-4 p-4 bg-gray-50 rounded-xl">
            <p class="text-xs font-medium text-gray-500 mb-2">
                {{ \App\Models\Rater360::HUBUNGAN[$kom->rater?->hubungan] ?? 'Anonim' }}
            </p>
            @if($kom->kekuatan)
            <p class="text-xs text-gray-600 mb-1"><span class="font-semibold text-green-600">Kekuatan:</span> {{ $kom->kekuatan }}</p>
            @endif
            @if($kom->area_pengembangan)
            <p class="text-xs text-gray-600 mb-1"><span class="font-semibold text-orange-600">Pengembangan:</span> {{ $kom->area_pengembangan }}</p>
            @endif
            @if($kom->saran)
            <p class="text-xs text-gray-600"><span class="font-semibold text-blue-600">Saran:</span> {{ $kom->saran }}</p>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <a href="{{ route('kinerja.360.show', $sesi) }}"
       class="inline-block text-sm text-gray-500 hover:text-gray-700 transition">
        ← Kembali ke Detail Sesi
    </a>
</div>
@endsection
