@extends('layouts.app')
@section('title', 'Ajukan Cuti')
@section('page-title', 'Ajukan Cuti')
@section('page-subtitle', 'Formulir pengajuan cuti karyawan')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        @if($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <p class="font-semibold mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('cuti.store') }}"
              x-data="cutiForm()" class="space-y-4">
            @csrf

            {{-- Pegawai --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pegawai <span class="text-red-500">*</span></label>
                @if($pegawai->count() === 1)
                    <input type="hidden" name="nik" value="{{ $pegawai->first()->nik }}">
                    <p class="px-3 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl text-gray-700">
                        {{ $pegawai->first()->nama }} — {{ $pegawai->first()->jbtn }}
                    </p>
                @else
                    <select name="nik" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($pegawai as $p)
                        <option value="{{ $p->nik }}" {{ old('nik') === $p->nik ? 'selected' : '' }}>
                            {{ $p->nama }} — {{ $p->jbtn }}
                            (sisa: {{ \App\Models\PengajuanCuti::HAK_CUTI_TAHUNAN - ($p->cuti_diambil ?? 0) }} hari)
                        </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Jenis Cuti --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Cuti <span class="text-red-500">*</span></label>
                <select name="urgensi" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Jenis Cuti --</option>
                    @foreach(\App\Models\PengajuanCuti::JENIS_CUTI as $j)
                    <option value="{{ $j }}" {{ old('urgensi') === $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Periode --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_awal" required
                           value="{{ old('tanggal_awal') }}"
                           min="{{ today()->format('Y-m-d') }}"
                           x-model="tglAwal" @change="hitungHari()"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_akhir" required
                           value="{{ old('tanggal_akhir') }}"
                           :min="tglAwal || '{{ today()->format('Y-m-d') }}'"
                           x-model="tglAkhir" @change="hitungHari()"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            {{-- Info jumlah hari --}}
            <div x-show="jumlahHari > 0"
                 class="px-3 py-2 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Estimasi: <strong x-text="jumlahHari + ' hari kerja'"></strong></span>
            </div>

            {{-- Alamat selama cuti --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Alamat Selama Cuti <span class="text-red-500">*</span></label>
                <input type="text" name="alamat" required maxlength="255"
                       value="{{ old('alamat') }}"
                       placeholder="Alamat lengkap yang dapat dihubungi"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Alasan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Alasan / Kepentingan <span class="text-red-500">*</span></label>
                <textarea name="kepentingan" required maxlength="500" rows="3"
                          placeholder="Jelaskan alasan pengajuan cuti..."
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('kepentingan') }}</textarea>
            </div>

            {{-- Penanggung jawab --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Penanggung Jawab Selama Cuti</label>
                <select name="nik_pj"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Tidak ada / Opsional --</option>
                    @foreach($pj as $p)
                    <option value="{{ $p->nik }}" {{ old('nik_pj') === $p->nik ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->nik }})
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Pegawai yang menggantikan tugas selama cuti</p>
            </div>

            {{-- Alur persetujuan info --}}
            <div class="px-4 py-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs font-semibold text-gray-600 mb-2">Alur Persetujuan:</p>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">Kamu</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full font-medium">Atasan Langsung</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium">HRD</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">Selesai</span>
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Ajukan Cuti
                </button>
                <a href="{{ route('cuti.index') }}"
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
function cutiForm() {
    return {
        tglAwal: '{{ old('tanggal_awal', '') }}',
        tglAkhir: '{{ old('tanggal_akhir', '') }}',
        jumlahHari: 0,

        hitungHari() {
            if (!this.tglAwal || !this.tglAkhir) { this.jumlahHari = 0; return; }
            const a = new Date(this.tglAwal);
            const b = new Date(this.tglAkhir);
            if (b < a) { this.jumlahHari = 0; return; }
            let count = 0;
            const d = new Date(a);
            while (d <= b) {
                const day = d.getDay();
                if (day !== 0 && day !== 6) count++;
                d.setDate(d.getDate() + 1);
            }
            this.jumlahHari = count;
        },

        init() { this.hitungHari(); }
    };
}
</script>
@endpush
