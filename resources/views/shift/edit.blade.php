@extends('layouts.app')
@section('title', 'Edit Jadwal Shift')
@section('page-title', 'Edit Jadwal Shift')
@section('page-subtitle', $karyawan->nama . ' — ' . \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y'))

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- Employee header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700 font-bold text-lg flex-shrink-0">
            {{ strtoupper(substr($karyawan->nama, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <p class="font-semibold text-gray-800">{{ $karyawan->nama }}</p>
            <p class="text-xs text-gray-400">{{ $karyawan->jbtn }} &middot; NIK {{ $karyawan->nik }}</p>
        </div>
        <div class="ml-auto text-right text-sm text-gray-500 flex-shrink-0">
            <p class="font-semibold text-gray-700">
                {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}
            </p>
            <p class="text-xs">{{ $jumlahHari }} hari</p>
        </div>
    </div>

    {{-- Form --}}
    <form method="POST"
          action="{{ route('shift.update', [$karyawan, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
          x-data="shiftEdit()">
        @csrf @method('PUT')
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        {{-- Quick fill --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
            <p class="text-xs font-semibold text-gray-600">Isi Cepat</p>
            <div class="flex flex-wrap gap-2">
                @foreach(\App\Models\JadwalPegawai::SHIFT_OPTIONS as $opt)
                @if($opt)
                <button type="button" @click="fillAll('{{ $opt }}')"
                        class="px-3 py-1.5 text-xs rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium transition">
                    Semua {{ $opt }}
                </button>
                @endif
                @endforeach
                <button type="button" @click="fillWeekdays()"
                        class="px-3 py-1.5 text-xs rounded-xl border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 font-medium transition">
                    Hari Kerja = Pagi (Sen–Jum)
                </button>
                <button type="button" @click="fillAll('')"
                        class="px-3 py-1.5 text-xs rounded-xl border border-dashed border-gray-200 text-gray-400 hover:bg-gray-50 transition">
                    Kosongkan Semua
                </button>
            </div>
        </div>

        {{-- Calendar grid --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mt-4">
            {{-- Day headers --}}
            <div class="grid grid-cols-7 gap-1.5 mb-1.5 text-center">
                @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $h)
                <div class="text-xs font-semibold py-1 {{ in_array($h, ['Min','Sab']) ? 'text-red-400' : 'text-gray-500' }}">
                    {{ $h }}
                </div>
                @endforeach
            </div>

            {{-- Cells --}}
            <div class="grid grid-cols-7 gap-1.5">
                {{-- Empty padding cells before day 1 --}}
                @php $startDow = \Carbon\Carbon::create($tahun, $bulan, 1)->dayOfWeek; @endphp
                @for($i = 0; $i < $startDow; $i++)
                <div></div>
                @endfor

                @for($d = 1; $d <= $jumlahHari; $d++)
                @php
                    $tgl       = \Carbon\Carbon::create($tahun, $bulan, $d);
                    $isWeekend = $tgl->isWeekend();
                    $shift     = $jadwal?->getHari($d) ?? '';
                @endphp
                <div class="border rounded-xl p-1.5 text-center transition-colors"
                     :class="cellClass(shifts.h{{ $d }}, {{ $isWeekend ? 'true' : 'false' }})">
                    <div class="text-xs mb-1 {{ $isWeekend ? 'text-red-400 font-semibold' : 'text-gray-400' }}">
                        {{ $d }}
                    </div>
                    <select name="h{{ $d }}"
                            x-model="shifts.h{{ $d }}"
                            class="w-full text-xs border-0 bg-transparent text-center font-medium focus:outline-none cursor-pointer rounded">
                        @foreach(\App\Models\JadwalPegawai::SHIFT_OPTIONS as $opt)
                        <option value="{{ $opt }}" {{ $shift === $opt ? 'selected' : '' }}>
                            {{ $opt ?: 'Libur' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endfor
            </div>
        </div>

        {{-- Live summary --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mt-4">
            <p class="text-xs font-semibold text-gray-600 mb-3">Ringkasan Bulan Ini</p>
            <div class="flex flex-wrap gap-2 text-sm">
                @foreach([
                    ['Pagi',         'bg-blue-100 text-blue-700'],
                    ['Siang',        'bg-amber-100 text-amber-700'],
                    ['Malam',        'bg-purple-100 text-purple-700'],
                    ['Midle Pagi1',  'bg-cyan-100 text-cyan-700'],
                    ['Midle Siang1', 'bg-orange-100 text-orange-700'],
                    ['Midle Malam1', 'bg-indigo-100 text-indigo-700'],
                ] as [$nama, $cls])
                <div class="px-3 py-1.5 rounded-xl {{ $cls }} flex items-center gap-1.5">
                    <span>{{ $nama }}:</span>
                    <span class="font-bold" x-text="count('{{ $nama }}')"></span>
                </div>
                @endforeach
                <div class="px-3 py-1.5 rounded-xl bg-gray-100 text-gray-700 flex items-center gap-1.5">
                    <span>Total Masuk:</span>
                    <span class="font-bold" x-text="totalMasuk()"></span>
                </div>
                <div class="px-3 py-1.5 rounded-xl bg-red-50 text-red-500 flex items-center gap-1.5">
                    <span>Libur/Off:</span>
                    <span class="font-bold" x-text="{{ $jumlahHari }} - totalMasuk()"></span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-2 mt-4">
            <button type="submit"
                    class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                Simpan Jadwal
            </button>
            <a href="{{ route('shift.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
               class="px-5 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const _JUMLAH_HARI = {{ $jumlahHari }};
const _TAHUN       = {{ $tahun }};
const _BULAN       = {{ $bulan }};

function getDow(d) {
    return new Date(_TAHUN, _BULAN - 1, d).getDay();
}

function shiftEdit() {
    return {
        shifts: {
            @for($d = 1; $d <= $jumlahHari; $d++)
            h{{ $d }}: '{{ addslashes($jadwal?->getHari($d) ?? '') }}',
            @endfor
        },

        fillAll(val) {
            for (let i = 1; i <= _JUMLAH_HARI; i++) {
                this.shifts['h' + i] = val;
            }
        },

        fillWeekdays() {
            for (let i = 1; i <= _JUMLAH_HARI; i++) {
                const dow = getDow(i);
                this.shifts['h' + i] = (dow === 0 || dow === 6) ? '' : 'Pagi';
            }
        },

        count(nama) {
            return Object.values(this.shifts).filter(v => v === nama).length;
        },

        totalMasuk() {
            return Object.values(this.shifts).filter(v => v !== '').length;
        },

        cellClass(shift, isWeekend) {
            const map = {
                'Pagi':         'bg-blue-50 border-blue-200',
                'Siang':        'bg-amber-50 border-amber-200',
                'Malam':        'bg-purple-50 border-purple-200',
                'Midle Pagi1':  'bg-cyan-50 border-cyan-200',
                'Midle Siang1': 'bg-orange-50 border-orange-200',
                'Midle Malam1': 'bg-indigo-50 border-indigo-200',
            };
            return map[shift] || (isWeekend ? 'bg-red-50 border-red-100' : 'bg-gray-50 border-gray-100');
        }
    };
}
</script>
@endpush
