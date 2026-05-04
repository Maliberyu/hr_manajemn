@extends('layouts.app')
@section('title', 'Detail Penilaian')
@section('page-title', 'Detail Penilaian Prestasi')
@section('page-subtitle', $penilaian->pegawai?->nama . ' — S' . $penilaian->semester . '/' . $penilaian->tahun)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-start justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700 font-bold text-xl">
                {{ strtoupper(substr($penilaian->pegawai?->nama ?? 'U', 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-base">{{ $penilaian->pegawai?->nama }}</p>
                <p class="text-xs text-gray-400">{{ $penilaian->pegawai?->jbtn }} · {{ $penilaian->pegawai?->departemenRef?->nama }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Penilai: {{ $penilaian->penilai?->nama }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-xs text-gray-400 mb-1">Semester {{ $penilaian->semester }} / {{ $penilaian->tahun }}</p>
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-xl
                {{ $penilaian->status === 'final' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($penilaian->status) }}
            </span>
        </div>
    </div>

    {{-- Nilai Akhir --}}
    @if($penilaian->nilai_akhir)
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-5 text-white flex items-center justify-between">
        <div>
            <p class="text-sm font-medium opacity-90">Nilai Akhir</p>
            <p class="text-xs opacity-70 mt-0.5">Rata-rata berbobot dari semua kriteria</p>
        </div>
        <div class="text-right">
            <p class="text-3xl font-bold">{{ $penilaian->nilai_akhir }}</p>
            <p class="text-sm font-semibold mt-0.5 opacity-90">{{ $penilaian->predikat }}</p>
        </div>
    </div>
    @endif

    {{-- Detail per Kriteria --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Penilaian per Kriteria</p>
        <div class="space-y-3">
            @foreach($kriteria as $k)
            @php
                $nilaiRow = $penilaian->nilaiList->firstWhere('kriteria_id', $k->id);
                $val      = $nilaiRow?->nilai ?? 0;
                $label    = \App\Models\PenilaianPrestasi::SKALA[$val] ?? '-';
                $pct      = $val > 0 ? (($val / 5) * $k->bobot) : 0;
                $barColor = match(true) {
                    $val >= 5 => 'bg-blue-500',
                    $val >= 4 => 'bg-green-500',
                    $val >= 3 => 'bg-yellow-500',
                    $val >= 2 => 'bg-orange-500',
                    default   => 'bg-red-500',
                };
            @endphp
            <div class="p-3 bg-gray-50 rounded-xl">
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-gray-800">{{ $k->nama }}</p>
                        <span class="text-xs text-gray-400">{{ $k->bobot }}%</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($val)
                        <span class="text-lg font-bold text-gray-800">{{ $val }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-lg
                            @if($val>=5) bg-blue-100 text-blue-700
                            @elseif($val>=4) bg-green-100 text-green-700
                            @elseif($val>=3) bg-yellow-100 text-yellow-700
                            @elseif($val>=2) bg-orange-100 text-orange-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ $label }}
                        </span>
                        @else <span class="text-gray-300 text-sm">Belum dinilai</span> @endif
                    </div>
                </div>
                @if($val)
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ ($val/5)*100 }}%"></div>
                </div>
                @endif
                @if($nilaiRow?->catatan)
                <p class="text-xs text-gray-500 mt-1.5 italic">"{{ $nilaiRow->catatan }}"</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Evaluasi Akhir --}}
    @if($penilaian->kelebihan || $penilaian->kekurangan || $penilaian->saran || $penilaian->rekomendasi)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 grid md:grid-cols-2 gap-4">
        @foreach([
            ['kelebihan',   'Kelebihan',   'green'],
            ['kekurangan',  'Kekurangan',  'red'],
            ['saran',       'Saran',       'blue'],
            ['rekomendasi', 'Rekomendasi', 'purple'],
        ] as [$field, $label, $c])
        @if($penilaian->{$field})
        <div class="p-3 bg-{{ $c }}-50 border border-{{ $c }}-100 rounded-xl">
            <p class="text-xs font-semibold text-{{ $c }}-600 mb-1">{{ $label }}</p>
            <p class="text-sm text-gray-700">{{ $penilaian->{$field} }}</p>
        </div>
        @endif
        @endforeach
    </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-wrap gap-2">
        @if($penilaian->status === 'draft')
        <a href="{{ route('kinerja.prestasi.edit', $penilaian) }}"
           class="px-4 py-2 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-medium transition">
            Edit Penilaian
        </a>
        <form method="POST" action="{{ route('kinerja.prestasi.finalize', $penilaian) }}"
              onsubmit="return confirm('Finalisasi penilaian? Tidak bisa diedit setelah final.')">
            @csrf
            <button type="submit" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                Finalisasi
            </button>
        </form>
        @endif
        <a href="{{ route('kinerja.prestasi.pdf', $penilaian) }}"
           class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition">
            Download PDF
        </a>
        <a href="{{ route('kinerja.prestasi.index', ['semester'=>$penilaian->semester,'tahun'=>$penilaian->tahun]) }}"
           class="px-4 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
            ← Kembali
        </a>
    </div>
</div>
@endsection
