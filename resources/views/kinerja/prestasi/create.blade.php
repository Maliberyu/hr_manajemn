@extends('layouts.app')
@section('title', 'Buat Penilaian Prestasi')
@section('page-title', 'Buat Penilaian Prestasi Kerja')
@section('page-subtitle', 'Semester ' . $semester . ' / ' . $tahun)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('kinerja.prestasi.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="semester" value="{{ $semester }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        {{-- Pilih Pegawai --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-700 mb-3">Informasi Dasar</p>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pegawai yang Dinilai <span class="text-red-500">*</span></label>
                    <select name="pegawai_id" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawai as $p)
                        <option value="{{ $p->id }}" {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }} — {{ $p->jbtn }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 mb-1">Semester</label>
                        <input type="text" value="Semester {{ $semester }} / {{ $tahun }}" readonly
                               class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-500">
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Penilai</label>
                    <input type="text" value="{{ auth()->user()->nama }} (Anda)" readonly
                           class="w-full px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-500">
                </div>
            </div>
        </div>

        {{-- Skala --}}
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

        {{-- Penilaian per Kriteria --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-5">
            <p class="text-sm font-semibold text-gray-700">Penilaian per Kriteria</p>

            @foreach($kriteria as $k)
            <div class="border border-gray-100 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $k->nama }}</p>
                        <p class="text-xs text-gray-400">Bobot: {{ $k->bobot }}%</p>
                    </div>
                    <div class="flex gap-1.5">
                        @foreach([1,2,3,4,5] as $val)
                        @php
                            $labels = [1=>'Kecewa',2=>'Kurang',3=>'Biasa',4=>'Puas',5=>'Istimewa'];
                            $colors = [1=>'red',2=>'orange',3=>'yellow',4=>'green',5=>'blue'];
                            $c = $colors[$val];
                        @endphp
                        <label class="cursor-pointer">
                            <input type="radio" name="nilai[{{ $k->id }}]" value="{{ $val }}"
                                   {{ old("nilai.{$k->id}", 3) == $val ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-10 h-10 rounded-xl border-2 border-gray-200 flex items-center justify-center text-sm font-bold
                                        transition peer-checked:border-{{ $c }}-400 peer-checked:bg-{{ $c }}-50 peer-checked:text-{{ $c }}-700 text-gray-400">
                                {{ $val }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                {{-- Sub-indikator sebagai panduan --}}
                @if($k->subIndikator->count())
                <div class="bg-gray-50 rounded-xl px-3 py-2">
                    <p class="text-xs text-gray-400 mb-1">Panduan:</p>
                    @foreach($k->subIndikator->where('aktif', true) as $sub)
                    <p class="text-xs text-gray-500">• {{ $sub->nama }}</p>
                    @endforeach
                </div>
                @endif
                <div class="mt-2">
                    <input type="text" name="catatan[{{ $k->id }}]"
                           placeholder="Catatan (opsional)"
                           class="w-full px-3 py-1.5 text-xs border border-gray-200 rounded-xl focus:ring-1 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>
            @endforeach
        </div>

        {{-- Evaluasi Akhir --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Evaluasi Akhir</p>
            @foreach([
                ['kelebihan',   'Kelebihan Karyawan',   'Hal positif yang perlu dipertahankan...'],
                ['kekurangan',  'Kekurangan Karyawan',  'Area yang perlu diperbaiki...'],
                ['saran',       'Saran',                'Rekomendasi pengembangan...'],
                ['rekomendasi', 'Rekomendasi',          'Promosi, rotasi, pelatihan khusus, dll...'],
            ] as [$name, $label, $ph])
            <div>
                <label class="block text-xs text-gray-500 mb-1">{{ $label }}</label>
                <textarea name="{{ $name }}" rows="2" placeholder="{{ $ph }}"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none">{{ old($name) }}</textarea>
            </div>
            @endforeach
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                Simpan Penilaian (Draft)
            </button>
            <a href="{{ route('kinerja.prestasi.index', ['semester'=>$semester,'tahun'=>$tahun]) }}"
               class="px-5 py-2.5 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
