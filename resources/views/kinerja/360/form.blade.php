@extends('layouts.app')
@section('title', 'Isi Penilaian 360°')
@section('page-title', 'Form Penilaian 360 Derajat')
@section('page-subtitle', 'Menilai: ' . $sesi->pegawai?->nama)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Info --}}
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-4">
        <p class="text-sm font-semibold text-purple-800">Anda menilai: <strong>{{ $sesi->pegawai?->nama }}</strong></p>
        <p class="text-xs text-purple-600 mt-1">
            Sebagai: {{ \App\Models\Rater360::HUBUNGAN[$rater->hubungan] ?? $rater->hubungan }} ·
            S{{ $sesi->semester }}/{{ $sesi->tahun }}
            @if($rater->is_anonim && $rater->hubungan !== 'self')
            · <span class="font-medium">Identitas Anda anonim</span>
            @endif
        </p>
    </div>

    {{-- Skala --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-600 mb-2">Panduan Skala (1–5)</p>
        <div class="flex flex-wrap gap-2">
            @foreach([1=>'Kecewa',2=>'Kurang',3=>'Biasa',4=>'Puas',5=>'Istimewa'] as $v => $l)
            <span class="px-2.5 py-1 text-xs font-medium rounded-lg bg-gray-50 border border-gray-200 text-gray-700">
                {{ $v }} = {{ $l }}
            </span>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('kinerja.360.form.submit', $sesi) }}" class="space-y-4">
        @csrf
        <input type="hidden" name="rater_id" value="{{ $rater->id }}">

        @foreach($dimensi as $dim)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $dim->nama }}</p>
                    <p class="text-xs text-gray-400">Bobot: {{ $dim->bobot }}%</p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach($dim->aspek as $aspek)
                @php $existing = $nilaiExisting[$aspek->id] ?? null; @endphp
                <div class="p-3 bg-gray-50 rounded-xl">
                    <p class="text-sm text-gray-700 mb-2">{{ $aspek->nama }}</p>
                    <div class="flex gap-2">
                        @foreach([1,2,3,4,5] as $val)
                        @php $colors=[1=>'red',2=>'orange',3=>'yellow',4=>'green',5=>'blue']; $c=$colors[$val]; @endphp
                        <label class="cursor-pointer flex-1 text-center">
                            <input type="radio" name="nilai[{{ $aspek->id }}]" value="{{ $val }}"
                                   {{ ($existing?->nilai ?? 0) == $val ? 'checked' : '' }}
                                   required class="sr-only peer">
                            <div class="py-2 rounded-xl border-2 border-gray-200 text-sm font-bold transition
                                        peer-checked:border-{{ $c }}-400 peer-checked:bg-{{ $c }}-50 peer-checked:text-{{ $c }}-700 text-gray-400">
                                {{ $val }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Komentar Kualitatif --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Komentar Kualitatif</p>
            @foreach([
                ['kekuatan',          'Kekuatan Utama',       'Hal terbaik yang dimiliki orang ini...'],
                ['area_pengembangan', 'Area Pengembangan',    'Hal yang perlu ditingkatkan...'],
                ['saran',             'Saran Peningkatan',    'Rekomendasi konkret untuk berkembang...'],
            ] as [$name, $label, $ph])
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                <textarea name="{{ $name }}" rows="2" placeholder="{{ $ph }}"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none resize-none">{{ old($name) }}</textarea>
            </div>
            @endforeach
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 text-xs text-yellow-700">
            Setelah submit, Anda tidak bisa mengubah penilaian ini.
        </div>

        <button type="submit"
                class="w-full py-2.5 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold transition">
            Submit Penilaian
        </button>
    </form>
</div>
@endsection
