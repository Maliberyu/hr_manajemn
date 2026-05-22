@extends('layouts.app')
@section('title', 'Ajukan Lembur')
@section('page-title', 'Ajukan Lembur')
@section('page-subtitle', 'Sistem otomatis menentukan metode & nominal berdasarkan jadwal shift')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

@php
    $singlePegawai = $pegawai->count() === 1 ? $pegawai->first() : null;
@endphp

<script>
var __pegawaiMap = {};
@foreach($pegawai as $p)
__pegawaiMap[{{ $p->id }}] = {
    dep: "{{ $p->departemen }}",
    gapok: {{ $p->gapok ?? 0 }},
    nama: "{{ addslashes($p->nama) }}"
};
@endforeach
var __previewUrl = '{{ parse_url(route('lembur.hitung-preview'), PHP_URL_PATH) }}';
</script>

<div class="max-w-2xl mx-auto" x-data="lemburForm()">

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    <p class="font-semibold mb-1">Terdapat kesalahan:</p>
    <ul class="list-disc list-inside text-xs space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
        <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-gray-800">Formulir Pengajuan Lembur</h2>
            <p class="text-xs text-gray-400 mt-0.5">
                Metode otomatis: shift (jika ada jadwal) atau jam aktual (jika tidak ada)
            </p>
        </div>
    </div>

    <form action="{{ route('lembur.store') }}" method="POST" class="p-6 space-y-4">
        @csrf

        {{-- Pegawai --}}
        @if($singlePegawai)
        <input type="hidden" name="pegawai_id" value="{{ $singlePegawai->id }}" x-model="pegawaiId" x-init="pegawaiId = {{ $singlePegawai->id }}">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Pegawai</label>
            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                @php $initials = collect(explode(' ', $singlePegawai->nama))->take(2)->map(fn($w)=>strtoupper($w[0]))->join(''); @endphp
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ $initials }}</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $singlePegawai->nama }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $singlePegawai->nik }}</p>
                </div>
            </div>
        </div>
        @else
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Pegawai <span class="text-red-500">*</span></label>
            <select name="pegawai_id" required x-model="pegawaiId" @change="hitungPreview()"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300 @error('pegawai_id') border-red-400 @enderror">
                <option value="">— Pilih Pegawai —</option>
                @foreach($pegawai as $p)
                <option value="{{ $p->id }}" {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->nik }} — {{ $p->nama }}
                </option>
                @endforeach
            </select>
            @error('pegawai_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        @endif

        {{-- Tanggal --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" required x-model="tanggal" @change="hitungPreview()"
                   value="{{ old('tanggal', today()->format('Y-m-d')) }}"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 @error('tanggal') border-red-400 @enderror">
            @error('tanggal')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Jam --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" name="jam_mulai" required x-model="jamMulai" @change="hitungPreview()"
                       value="{{ old('jam_mulai') }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 @error('jam_mulai') border-red-400 @enderror">
                @error('jam_mulai')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">Jam Selesai <span class="text-red-500">*</span></label>
                <input type="time" name="jam_selesai" required x-model="jamSelesai" @change="hitungPreview()"
                       value="{{ old('jam_selesai') }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 @error('jam_selesai') border-red-400 @enderror">
                @error('jam_selesai')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Jenis --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Jenis Hari <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-colors {{ old('jenis','HB') === 'HB' ? 'border-orange-400 bg-orange-50/40' : 'border-gray-100' }}">
                    <input type="radio" name="jenis" value="HB" x-model="jenis" @change="hitungPreview()"
                           {{ old('jenis','HB') === 'HB' ? 'checked' : '' }} class="text-orange-500">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Hari Biasa (HB)</p>
                        <p class="text-xs text-gray-400">Multiplier dari jenis shift</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-colors {{ old('jenis') === 'HR' ? 'border-red-400 bg-red-50/40' : 'border-gray-100' }}">
                    <input type="radio" name="jenis" value="HR" x-model="jenis" @change="hitungPreview()"
                           {{ old('jenis') === 'HR' ? 'checked' : '' }} class="text-red-500">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Hari Raya/Libur (HR)</p>
                        <p class="text-xs text-gray-400">Multiplier ×2.0</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Keterangan Pekerjaan <span class="text-red-500">*</span></label>
            <textarea name="keterangan" required rows="2" maxlength="255"
                      placeholder="Jelaskan pekerjaan yang dilakukan saat lembur..."
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 resize-none @error('keterangan') border-red-400 @enderror">{{ old('keterangan') }}</textarea>
            @error('keterangan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- ── Preview Kalkulasi Otomatis ──────────────────────────────────── --}}
        <div x-show="preview !== null" x-cloak x-transition class="rounded-2xl overflow-hidden border">

            {{-- Tidak dihitung --}}
            <template x-if="preview && !preview.dihitung">
                <div class="px-4 py-4 bg-gray-50 border-gray-200">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-gray-600">Lembur tidak dihitung</p>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="preview.alasan"></p>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Dihitung --}}
            <template x-if="preview && preview.dihitung">
                <div>
                    <div class="px-4 py-3 bg-orange-50 border-b border-orange-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M12 7h.01"/></svg>
                            <span class="text-xs font-semibold text-orange-800">Estimasi Kalkulasi Otomatis</span>
                        </div>
                        <span class="text-xs px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full font-medium"
                              x-text="preview.metode_label"></span>
                    </div>
                    <div class="px-4 py-4 grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white">
                        <div class="text-center p-2.5 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-400 mb-1">Shift</p>
                            <p class="text-sm font-bold text-gray-700" x-text="preview.shift_kode ?? '—'"></p>
                            <p class="text-xs text-gray-400" x-text="preview.jam_selesai_shift ? 'Selesai: '+preview.jam_selesai_shift : ''"></p>
                        </div>
                        <div class="text-center p-2.5 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-400 mb-1">Multiplier</p>
                            <p class="text-sm font-bold text-blue-600" x-text="'×' + preview.multiplier"></p>
                        </div>
                        <div class="text-center p-2.5 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-400 mb-1">Durasi</p>
                            <p class="text-sm font-bold text-gray-700" x-text="preview.durasi_label"></p>
                            <p class="text-xs text-orange-500" x-show="preview.durasi_aktual > preview.durasi_jam"
                               x-text="'Aktual: ' + preview.durasi_aktual + 'j'"></p>
                        </div>
                        <div class="text-center p-2.5 bg-orange-50 rounded-xl border border-orange-100">
                            <p class="text-xs text-orange-600 mb-1">Estimasi Nominal</p>
                            <p class="text-sm font-bold text-orange-700" x-text="preview.nominal_fmt"></p>
                            <p class="text-xs text-gray-400" x-text="preview.upah_per_jam_fmt + '/jam'"></p>
                        </div>
                    </div>
                    <div x-show="preview.catatan_sistem" class="px-4 py-2 bg-amber-50 border-t border-amber-100">
                        <p class="text-xs text-amber-700" x-text="'⚠ ' + preview.catatan_sistem"></p>
                    </div>
                </div>
            </template>

            {{-- Loading --}}
            <template x-if="preview === 'loading'">
                <div class="px-4 py-3 bg-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <p class="text-xs text-gray-500">Menghitung estimasi...</p>
                </div>
            </template>
        </div>

        {{-- Info setting --}}
        <div class="text-xs text-gray-400 bg-gray-50 rounded-xl px-3 py-2.5">
            Min. lembur: <strong>{{ $setting->min_jam_lembur }}j</strong> (jam aktual) /
            <strong>{{ $setting->min_jam_shift }}j</strong> (shift) ·
            Maks: <strong>{{ $setting->max_jam_harian }}j/hari</strong>,
            <strong>{{ $setting->max_jam_mingguan }}j/minggu</strong>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
            <button type="submit"
                    class="flex items-center gap-2 px-5 py-2.5 text-sm bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                Ajukan Lembur
            </button>
            <a href="{{ route('lembur.index') }}" class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">Batal</a>
        </div>
    </form>
</div>
</div>

@push('scripts')
<script>
function lemburForm() {
    return {
        pegawaiId: {{ $singlePegawai ? $singlePegawai->id : (old('pegawai_id') ?? 'null') }},
        tanggal:   '{{ old('tanggal', today()->format('Y-m-d')) }}',
        jamMulai:  '{{ old('jam_mulai', '') }}',
        jamSelesai:'{{ old('jam_selesai', '') }}',
        jenis:     '{{ old('jenis', 'HB') }}',
        preview:   null,
        _timer:    null,

        hitungPreview() {
            if (!this.pegawaiId || !this.tanggal || !this.jamMulai || !this.jamSelesai) {
                this.preview = null;
                return;
            }
            clearTimeout(this._timer);
            this.preview = 'loading';
            this._timer = setTimeout(() => this._fetch(), 400);
        },

        async _fetch() {
            const params = new URLSearchParams({
                pegawai_id: this.pegawaiId,
                tanggal:    this.tanggal,
                jam_mulai:  this.jamMulai,
                jam_selesai:this.jamSelesai,
                jenis:      this.jenis,
            });
            try {
                const res  = await fetch(__previewUrl + '?' + params, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '' }
                });
                this.preview = await res.json();
            } catch {
                this.preview = null;
            }
        },

        init() {
            // Auto-preview jika ada old values
            if (this.jamMulai && this.jamSelesai) this.hitungPreview();
        }
    };
}
</script>
@endpush
@endsection
