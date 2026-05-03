@extends('layouts.app')
@section('title', 'Ajukan Lembur')
@section('page-title', 'Ajukan Lembur')
@section('page-subtitle', 'Buat pengajuan lembur baru')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('lembur.store') }}"
              x-data="lemburForm()" class="space-y-4">
            @csrf

            {{-- Pegawai --}}
            @if(auth()->user()->hasRole('karyawan'))
                <input type="hidden" name="pegawai_id" value="{{ auth()->user()->pegawai?->id }}">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-700 font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->nama }}</p>
                        <p class="text-xs text-gray-400">{{ auth()->user()->pegawai?->jbtn }}</p>
                    </div>
                </div>
            @else
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Pegawai <span class="text-red-500">*</span>
                </label>
                <select name="pegawai_id" x-model="pegawaiId" @change="updateTarif()"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawai as $p)
                    <option value="{{ $p->id }}" data-dep="{{ $p->departemen }}"
                            {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} — {{ $p->jbtn }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Tanggal --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Tanggal Lembur <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal" required
                       value="{{ old('tanggal', today()->toDateString()) }}"
                       x-model="tanggal"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            {{-- Jam --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Jam Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_mulai" required
                           value="{{ old('jam_mulai') }}"
                           x-model="jamMulai" @change="hitungDurasi()"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Jam Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" name="jam_selesai" required
                           value="{{ old('jam_selesai') }}"
                           x-model="jamSelesai" @change="hitungDurasi()"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>

            {{-- Durasi estimasi --}}
            <div x-show="durasi > 0"
                 class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl text-sm">
                <div class="text-blue-600">
                    <span class="font-semibold">Durasi:</span>
                    <span x-text="durasiLabel"></span>
                </div>
                <div class="text-blue-500 ml-auto">
                    <span class="font-semibold">Estimasi:</span>
                    Rp <span x-text="nominalLabel"></span>
                </div>
            </div>

            {{-- Jenis --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">
                    Jenis Lembur <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis" value="HB"
                               {{ old('jenis', 'HB') === 'HB' ? 'checked' : '' }}
                               x-model="jenis" @change="hitungDurasi()"
                               class="sr-only peer">
                        <div class="px-3 py-2.5 rounded-xl border-2 border-gray-100 peer-checked:border-blue-400 peer-checked:bg-blue-50 transition text-center">
                            <p class="text-sm font-semibold text-gray-700 peer-checked:text-blue-700">Hari Biasa (HB)</p>
                            <p class="text-xs text-gray-400 mt-0.5">Senin–Sabtu bukan libur nasional</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="jenis" value="HR"
                               {{ old('jenis') === 'HR' ? 'checked' : '' }}
                               x-model="jenis" @change="hitungDurasi()"
                               class="sr-only peer">
                        <div class="px-3 py-2.5 rounded-xl border-2 border-gray-100 peer-checked:border-orange-400 peer-checked:bg-orange-50 transition text-center">
                            <p class="text-sm font-semibold text-gray-700 peer-checked:text-orange-700">Hari Raya/Libur (HR)</p>
                            <p class="text-xs text-gray-400 mt-0.5">Minggu atau libur nasional</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Keterangan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Keterangan / Pekerjaan yang Dilakukan <span class="text-red-500">*</span>
                </label>
                <textarea name="keterangan" required maxlength="255" rows="3"
                          placeholder="Jelaskan pekerjaan yang dilakukan saat lembur..."
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none">{{ old('keterangan') }}</textarea>
            </div>

            {{-- Alur approval --}}
            <div class="flex items-center gap-1.5 text-xs text-gray-400 pt-1">
                <span class="px-2 py-1 bg-gray-100 rounded-lg">Anda</span>
                <span>→</span>
                <span class="px-2 py-1 bg-yellow-50 text-yellow-600 rounded-lg">Atasan Langsung</span>
                <span>→</span>
                <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-lg">HRD</span>
                <span>→</span>
                <span class="px-2 py-1 bg-green-50 text-green-600 rounded-lg">Disetujui</span>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Ajukan Lembur
                </button>
                <a href="{{ route('lembur.index') }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const _tarifMap = @json($tarifMap);

// dep_id per pegawai (non-karyawan role)
@if(!auth()->user()->hasRole('karyawan'))
const _pegawaiDep = {};
@foreach($pegawai as $p)
_pegawaiDep['{{ $p->id }}'] = '{{ $p->departemen }}';
@endforeach
@else
const _pegawaiDep = { '{{ auth()->user()->pegawai?->id }}': '{{ auth()->user()->pegawai?->departemen }}' };
@endif

function lemburForm() {
    return {
        pegawaiId: '{{ old('pegawai_id', auth()->user()->hasRole('karyawan') ? auth()->user()->pegawai?->id : '') }}',
        jenis: '{{ old('jenis', 'HB') }}',
        jamMulai: '{{ old('jam_mulai', '') }}',
        jamSelesai: '{{ old('jam_selesai', '') }}',
        durasi: 0,
        durasiLabel: '',
        nominalLabel: '0',

        updateTarif() {
            this.hitungDurasi();
        },

        hitungDurasi() {
            if (!this.jamMulai || !this.jamSelesai) { this.durasi = 0; return; }
            const [hm, mm] = this.jamMulai.split(':').map(Number);
            const [hs, ms] = this.jamSelesai.split(':').map(Number);
            const menitMulai   = hm * 60 + mm;
            const menitSelesai = hs * 60 + ms;
            if (menitSelesai <= menitMulai) { this.durasi = 0; return; }
            this.durasi = (menitSelesai - menitMulai) / 60;
            const jam = Math.floor(this.durasi);
            const mnt = Math.round((this.durasi - jam) * 60);
            this.durasiLabel = jam + 'j' + (mnt > 0 ? ' ' + mnt + 'm' : '');
            this.hitungNominal();
        },

        hitungNominal() {
            const depId = _pegawaiDep[this.pegawaiId] || '';
            const tarif = _tarifMap[depId];
            if (!tarif || this.durasi <= 0) { this.nominalLabel = '0'; return; }
            const rate    = this.jenis === 'HR' ? tarif.tarif_hr : tarif.tarif_hb;
            const nominal = Math.round(this.durasi * rate);
            this.nominalLabel = nominal.toLocaleString('id-ID');
        },

        init() { this.hitungDurasi(); }
    };
}
</script>
@endpush
