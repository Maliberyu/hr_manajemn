@extends('layouts.app')
@section('title', 'Ajukan Cuti Tahunan')
@section('page-title', 'Ajukan Cuti Tahunan')
@section('page-subtitle', 'Isi formulir pengajuan cuti tahunan')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

@php
    $hnBlocked = session('hn_blocked', false);
@endphp

<div class="max-w-2xl mx-auto space-y-4">

    {{-- Bypass aktif (HRD acc request) ────────────────────────────────────── --}}
    @if($hasHnBypass && !$hnBlocked)
    <div class="px-4 py-3 bg-green-50 border border-green-200 rounded-xl flex items-center gap-2.5">
        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-green-700">
            <strong>Bypass H-{{ $setting->min_hari_pengajuan }} disetujui.</strong>
            Kamu boleh mengajukan cuti mendadak untuk tanggal apapun.
        </p>
    </div>
    @else
    {{-- Info H-N normal --}}
    <div class="px-4 py-3 bg-blue-50 border border-blue-100 rounded-xl flex items-center gap-2.5">
        <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-blue-700">
            Cuti tahunan minimal diajukan <strong>H-{{ $setting->min_hari_pengajuan }}</strong> sebelum tanggal mulai cuti.
        </p>
    </div>
    @endif

    {{-- ══ BLOK H-N: muncul setelah submit jika tanggal terlalu dekat ════════ --}}
    @if($hnBlocked)
    <div class="rounded-2xl border border-orange-200 overflow-hidden">

        {{-- Header error --}}
        <div class="px-5 py-4 bg-orange-50 border-b border-orange-100 flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-orange-800">Pengajuan cuti terlalu mendadak</p>
                <p class="text-xs text-orange-700 mt-0.5">
                    {{ $errors->first('tanggal_awal') }}
                </p>
            </div>
        </div>

        {{-- Form request bypass ke HRD --}}
        <div class="p-5 bg-white">
            <p class="text-sm font-semibold text-gray-700 mb-1">Minta izin ke HRD</p>
            <p class="text-xs text-gray-500 mb-4">
                Jelaskan alasan kenapa kamu perlu cuti mendadak.
                HRD akan mempertimbangkan dan mengirimkan notifikasi persetujuan.
            </p>

            <form action="{{ route('cuti.tahunan.request-buka') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Tanggal otomatis dari input sebelumnya --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rencana Mulai Cuti</label>
                        <input type="date" name="tgl_rencana_mulai" required
                               value="{{ session('hn_tanggal_awal', old('tanggal_awal')) }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Rencana Selesai Cuti</label>
                        <input type="date" name="tgl_rencana_akhir" required
                               value="{{ session('hn_tanggal_akhir', old('tanggal_akhir')) }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        Alasan Mendesak <span class="text-red-500">*</span>
                    </label>
                    <textarea name="alasan" rows="3" required
                              placeholder="Contoh: Ada keperluan keluarga mendadak, kondisi darurat, dll."
                              class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 resize-none"></textarea>
                </div>

                <div class="flex items-center gap-3 pt-1">
                    <button type="submit"
                            class="flex items-center gap-2 px-5 py-2.5 text-sm bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirim Permintaan ke HRD
                    </button>
                    <a href="{{ route('cuti.tahunan.create') }}"
                       class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                        Ubah Tanggal
                    </a>
                </div>
            </form>
        </div>

        {{-- Alur info --}}
        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
            <div class="flex items-center gap-3 text-xs text-gray-500">
                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full font-medium">Kamu kirim alasan</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full font-medium">HRD tinjau</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full font-medium">Acc → bisa cuti</span>
            </div>
        </div>
    </div>
    @endif

    {{-- ══ FORM CUTI ══════════════════════════════════════════════════════════ --}}
    {{-- Sembunyikan form jika sedang di-block H-N (tunggu acc HRD dulu) --}}
    @if(!$hnBlocked)

    {{-- Error umum (selain hn_blocked) --}}
    @if($errors->any() && !$hnBlocked)
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        <p class="font-semibold mb-1">Terdapat kesalahan:</p>
        <ul class="list-disc list-inside space-y-0.5 text-xs">
            @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <form action="{{ route('cuti.tahunan.store') }}" method="POST" id="formCuti">
            @csrf

            {{-- Pegawai --}}
            @php
                $singlePegawai = $pegawai->count() === 1 ? $pegawai->first() : null;
            @endphp
            @if($singlePegawai)
            <input type="hidden" name="nik" value="{{ $singlePegawai->nik }}">
            <div class="mb-5">
                <label class="block text-xs font-semibold text-gray-600 mb-2">Pegawai</label>
                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                    @php $initials = collect(explode(' ', $singlePegawai->nama))->take(2)->map(fn($w)=>strtoupper($w[0]))->join(''); @endphp
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-bold text-white">{{ $initials }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $singlePegawai->nama }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $singlePegawai->nik }}</p>
                    </div>
                    <span class="ml-auto text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100 font-medium">Anda</span>
                </div>
            </div>
            @else
            <div class="mb-5">
                <label class="block text-xs font-semibold text-gray-600 mb-2">Pegawai <span class="text-red-500">*</span></label>
                <select name="nik" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-300 @error('nik') border-red-400 @enderror">
                    <option value="">— Pilih Pegawai —</option>
                    @foreach($pegawai as $p)
                    <option value="{{ $p->nik }}" {{ old('nik') == $p->nik ? 'selected' : '' }}>
                        {{ $p->nik }} — {{ $p->nama }}
                    </option>
                    @endforeach
                </select>
                @error('nik')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            @endif

            {{-- Tanggal --}}
            <div class="grid grid-cols-2 gap-4 mb-5" x-data="cutiPeriod()">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_awal" id="tanggal_awal" required
                           min="{{ today()->format('Y-m-d') }}"
                           value="{{ old('tanggal_awal') }}"
                           x-model="awal" @change="syncAkhir(); hitung()"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 @error('tanggal_awal') border-red-400 @enderror">
                    @error('tanggal_awal')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Tanggal Akhir <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_akhir" id="tanggal_akhir" required
                           :min="awal || '{{ today()->format('Y-m-d') }}'"
                           value="{{ old('tanggal_akhir') }}"
                           x-model="akhir" @change="hitung()"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 @error('tanggal_akhir') border-red-400 @enderror">
                    @error('tanggal_akhir')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div x-show="hari > 0" class="col-span-2">
                    <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-100 rounded-xl">
                        <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-xs text-blue-700">
                            Estimasi <strong x-text="hari + ' hari kerja'"></strong>
                            <span class="text-blue-400">(tidak termasuk Sabtu & Minggu)</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Alamat --}}
            <div class="mb-5">
                <label class="block text-xs font-semibold text-gray-600 mb-2">Alamat Selama Cuti <span class="text-red-500">*</span></label>
                <input type="text" name="alamat" required maxlength="255"
                       value="{{ old('alamat') }}"
                       placeholder="Alamat lengkap yang bisa dihubungi"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 @error('alamat') border-red-400 @enderror">
                @error('alamat')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Kepentingan --}}
            <div class="mb-5">
                <label class="block text-xs font-semibold text-gray-600 mb-2">Kepentingan / Alasan <span class="text-red-500">*</span></label>
                <textarea name="kepentingan" rows="3" required maxlength="500"
                          placeholder="Jelaskan keperluan cuti Anda..."
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 resize-none @error('kepentingan') border-red-400 @enderror">{{ old('kepentingan') }}</textarea>
                @error('kepentingan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Penanggung Jawab --}}
            <div class="mb-6">
                <label class="block text-xs font-semibold text-gray-600 mb-2">
                    Penanggung Jawab
                    <span class="ml-1 font-normal text-gray-400">(opsional)</span>
                </label>
                <select name="nik_pj"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <option value="">— Tidak ada —</option>
                    @foreach($pj as $p)
                    <option value="{{ $p->nik }}" {{ old('nik_pj') == $p->nik ? 'selected' : '' }}>
                        {{ $p->nik }} — {{ $p->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Ajukan Cuti
                </button>
                <a href="{{ route('cuti.tahunan.index') }}"
                   class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
    @endif

</div>

@push('scripts')
<script>
function cutiPeriod() {
    return {
        awal:  '{{ old('tanggal_awal', '') }}',
        akhir: '{{ old('tanggal_akhir', '') }}',
        hari:  0,
        syncAkhir() {
            if (this.akhir && this.akhir < this.awal) this.akhir = this.awal;
        },
        hitung() {
            if (!this.awal || !this.akhir || this.akhir < this.awal) { this.hari = 0; return; }
            let c = 0, d = new Date(this.awal), e = new Date(this.akhir);
            while (d <= e) { if (d.getDay() !== 0 && d.getDay() !== 6) c++; d.setDate(d.getDate()+1); }
            this.hari = c;
        },
        init() { this.hitung(); }
    };
}
</script>
@endpush

@endsection
