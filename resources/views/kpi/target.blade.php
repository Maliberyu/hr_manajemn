@extends('layouts.app')
@section('title', 'Setting KPI')
@section('page-title', 'Setting KPI')
@section('page-subtitle', 'Atur bobot dan target komponen KPI')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->has('bobot'))
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">
        {{ $errors->first('bobot') }}
    </div>
    @endif

    <form method="POST" action="{{ route('kpi.target.save') }}"
          x-data="kpiSetting()" @submit.prevent="submitIfValid">
        @csrf

        {{-- ── Bobot Komponen ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800">Bobot Komponen KPI</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Total harus tepat 100%</p>
                </div>
                {{-- Total indicator --}}
                <div class="text-right">
                    <span class="text-2xl font-extrabold"
                          :class="total === 100 ? 'text-emerald-600' : 'text-red-500'"
                          x-text="total + '%'"></span>
                    <p class="text-xs" :class="total === 100 ? 'text-emerald-500' : 'text-red-400'"
                       x-text="total === 100 ? '✓ Valid' : 'Harus 100%'"></p>
                </div>
            </div>

            <div class="p-5 space-y-4">
                @php
                    $komponen = [
                        ['key' => 'kehadiran', 'label' => 'Kehadiran',         'color' => 'green',  'desc' => '% hadir dari hari wajib masuk'],
                        ['key' => 'disiplin',  'label' => 'Kedisiplinan',      'color' => 'orange', 'desc' => 'Penalti dari terlambat dan alfa'],
                        ['key' => 'penilaian', 'label' => 'Penilaian Prestasi','color' => 'blue',   'desc' => 'Nilai akhir penilaian atasan langsung'],
                        ['key' => 'p360',      'label' => 'Penilaian 360°',    'color' => 'purple', 'desc' => 'Nilai akhir dari multi-rater'],
                        ['key' => 'pelatihan', 'label' => 'Pelatihan',         'color' => 'indigo', 'desc' => 'Jam pelatihan IHT + eksternal vs target'],
                    ];
                @endphp

                @foreach($komponen as $k)
                @php $fieldName = 'bobot_'.$k['key']; @endphp
                <div class="flex items-center gap-4">
                    {{-- Label --}}
                    <div class="w-44 flex-shrink-0">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-{{ $k['color'] }}-500 flex-shrink-0"></span>
                            <span class="text-sm font-medium text-gray-700">{{ $k['label'] }}</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5 ml-5">{{ $k['desc'] }}</p>
                    </div>
                    {{-- Slider --}}
                    <input type="range" min="0" max="100" step="1"
                           x-model.number="bobot.{{ $k['key'] }}"
                           class="flex-1 h-2 rounded-full accent-{{ $k['color'] }}-500 cursor-pointer">
                    {{-- Number input --}}
                    <div class="relative w-20 flex-shrink-0">
                        <input type="number" name="{{ $fieldName }}" min="0" max="100"
                               x-model.number="bobot.{{ $k['key'] }}"
                               class="w-full px-3 py-1.5 text-sm font-semibold text-center border border-gray-200 rounded-xl focus:ring-2 focus:ring-{{ $k['color'] }}-400 focus:outline-none"
                               :class="total !== 100 ? 'border-red-300' : ''">
                        <span class="absolute right-2 top-1.5 text-xs text-gray-400 pointer-events-none">%</span>
                    </div>
                </div>
                @endforeach

                {{-- Progress bar visual --}}
                <div class="mt-2">
                    <div class="flex h-3 rounded-full overflow-hidden gap-0.5">
                        @foreach($komponen as $k)
                        @php $fieldName = 'bobot_'.$k['key']; @endphp
                        <div class="bg-{{ $k['color'] }}-400 transition-all duration-300"
                             :style="'width:' + bobot.{{ $k['key'] }} + '%'"
                             :title="'{{ $k['label'] }}: ' + bobot.{{ $k['key'] }} + '%'"></div>
                        @endforeach
                        <div class="bg-gray-100 flex-1"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                        @foreach($komponen as $k)
                        <span x-text="bobot.{{ $k['key'] }} + '%'" class="text-{{ $k['color'] }}-500 font-medium"></span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Target & Parameter ─────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Target & Parameter</h3>
                <p class="text-xs text-gray-400 mt-0.5">Nilai acuan untuk kalkulasi skor per komponen</p>
            </div>
            <div class="p-5 space-y-5">

                {{-- Target kehadiran --}}
                <div class="flex items-start gap-4">
                    <div class="w-56 flex-shrink-0">
                        <p class="text-sm font-medium text-gray-700">Target Kehadiran</p>
                        <p class="text-xs text-gray-400 mt-0.5">% hadir minimum yang dianggap ideal (skor 100)</p>
                    </div>
                    <div class="relative w-28">
                        <input type="number" name="target_hadir_pct" min="1" max="100"
                               value="{{ old('target_hadir_pct', $setting->target_hadir_pct) }}"
                               class="w-full px-3 py-2 text-sm font-semibold text-center border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-400 focus:outline-none">
                        <span class="absolute right-3 top-2 text-xs text-gray-400">%</span>
                    </div>
                </div>

                {{-- Target jam pelatihan --}}
                <div class="flex items-start gap-4">
                    <div class="w-56 flex-shrink-0">
                        <p class="text-sm font-medium text-gray-700">Target Jam Pelatihan</p>
                        <p class="text-xs text-gray-400 mt-0.5">Jam per semester yang dianggap ideal (skor 100)</p>
                    </div>
                    <div class="relative w-28">
                        <input type="number" name="target_jam_pelatihan" min="1" max="999"
                               value="{{ old('target_jam_pelatihan', $setting->target_jam_pelatihan) }}"
                               class="w-full px-3 py-2 text-sm font-semibold text-center border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                        <span class="absolute right-3 top-2 text-xs text-gray-400">jam</span>
                    </div>
                </div>

                {{-- Penalti disiplin --}}
                <div class="p-4 bg-orange-50 border border-orange-100 rounded-xl space-y-3">
                    <p class="text-xs font-semibold text-orange-700">Penalti Disiplin (mulai dari 100, dikurangi tiap pelanggaran)</p>
                    <div class="flex items-center gap-4">
                        <p class="text-sm text-gray-700 w-56">Pengurangan per hari <strong>Alfa</strong></p>
                        <div class="relative w-28">
                            <input type="number" name="penalti_alfa" min="0" max="50"
                                   value="{{ old('penalti_alfa', $setting->penalti_alfa) }}"
                                   class="w-full px-3 py-2 text-sm font-semibold text-center border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-400 focus:outline-none bg-white">
                            <span class="absolute right-3 top-2 text-xs text-gray-400">poin</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <p class="text-sm text-gray-700 w-56">Pengurangan per kejadian <strong>Terlambat</strong></p>
                        <div class="relative w-28">
                            <input type="number" name="penalti_terlambat" min="0" max="50"
                                   value="{{ old('penalti_terlambat', $setting->penalti_terlambat) }}"
                                   class="w-full px-3 py-2 text-sm font-semibold text-center border border-orange-200 rounded-xl focus:ring-2 focus:ring-orange-400 focus:outline-none bg-white">
                            <span class="absolute right-3 top-2 text-xs text-gray-400">poin</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Tombol simpan --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    :disabled="total !== 100"
                    :class="total === 100
                        ? 'bg-blue-600 hover:bg-blue-700 text-white cursor-pointer'
                        : 'bg-gray-200 text-gray-400 cursor-not-allowed'"
                    class="px-6 py-2.5 text-sm font-semibold rounded-xl transition">
                Simpan Setting
            </button>
            <span x-show="total !== 100" class="text-sm text-red-500 font-medium">
                Total bobot masih <span x-text="total"></span>% — harus 100% untuk bisa disimpan.
            </span>
            <a href="{{ route('kpi.index') }}"
               class="ml-auto px-4 py-2 text-sm border border-gray-200 text-gray-500 rounded-xl hover:bg-gray-50 transition">
                Kembali ke Dashboard
            </a>
        </div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function kpiSetting() {
    return {
        bobot: {
            kehadiran: {{ old('bobot_kehadiran', $setting->bobot_kehadiran) }},
            disiplin:  {{ old('bobot_disiplin',  $setting->bobot_disiplin) }},
            penilaian: {{ old('bobot_penilaian', $setting->bobot_penilaian) }},
            p360:      {{ old('bobot_p360',      $setting->bobot_p360) }},
            pelatihan: {{ old('bobot_pelatihan', $setting->bobot_pelatihan) }},
        },

        get total() {
            return Object.values(this.bobot).reduce((a, b) => a + (parseInt(b) || 0), 0);
        },

        submitIfValid() {
            if (this.total !== 100) return;
            this.$el.submit();
        },
    };
}
</script>
@endpush
