@extends('layouts.app')
@section('title', 'Edit Penilaian')
@section('page-title', 'Edit Penilaian Prestasi')
@section('page-subtitle', $penilaian->pegawai?->nama)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700 font-bold">
            {{ strtoupper(substr($penilaian->pegawai?->nama ?? 'U', 0, 1)) }}
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $penilaian->pegawai?->nama }}</p>
            <p class="text-xs text-gray-400">S{{ $penilaian->semester }}/{{ $penilaian->tahun }} · {{ $penilaian->pegawai?->jbtn }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('kinerja.prestasi.update', $penilaian) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4">
            <p class="text-xs font-semibold text-blue-700 mb-2">Panduan Skala Penilaian</p>
            <div class="flex flex-wrap gap-2">
                @foreach([1=>'Kecewa',2=>'Kurang',3=>'Biasa',4=>'Puas',5=>'Istimewa'] as $val => $label)
                <span class="px-3 py-1 text-xs font-medium rounded-lg bg-white border border-blue-200 text-blue-700">
                    {{ $val }} = {{ $label }}
                </span>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-5">
            <p class="text-sm font-semibold text-gray-700">Penilaian per Kriteria</p>
            @foreach($kriteria as $k)
            @php $existing = $penilaian->nilaiList->firstWhere('kriteria_id', $k->id); @endphp
            <div class="border border-gray-100 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $k->nama }}</p>
                        <p class="text-xs text-gray-400">Bobot: {{ $k->bobot }}%</p>
                    </div>
                    <div class="flex gap-1.5">
                        @foreach([1,2,3,4,5] as $val)
                        @php $colors=[1=>'red',2=>'orange',3=>'yellow',4=>'green',5=>'blue']; $c=$colors[$val]; @endphp
                        <label class="cursor-pointer">
                            <input type="radio" name="nilai[{{ $k->id }}]" value="{{ $val }}"
                                   {{ ($existing?->nilai ?? 3) == $val ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-10 rounded-xl border-2 border-gray-200 flex items-center justify-center text-sm font-bold transition
                                        peer-checked:border-{{ $c }}-400 peer-checked:bg-{{ $c }}-50 peer-checked:text-{{ $c }}-700 text-gray-400">
                                {{ $val }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                <input type="text" name="catatan[{{ $k->id }}]"
                       value="{{ $existing?->catatan }}" placeholder="Catatan (opsional)"
                       class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-xl focus:ring-1 focus:ring-blue-400 focus:outline-none">
            </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Evaluasi Akhir</p>
            @foreach([
                ['kelebihan','Kelebihan Karyawan'],
                ['kekurangan','Kekurangan Karyawan'],
                ['saran','Saran'],
                ['rekomendasi','Rekomendasi'],
            ] as [$name, $label])
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                <textarea name="{{ $name }}" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none">{{ old($name, $penilaian->{$name}) }}</textarea>
            </div>
            @endforeach
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                Simpan Perubahan
            </button>
            <a href="{{ route('kinerja.prestasi.show', $penilaian) }}"
               class="px-5 py-2.5 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
