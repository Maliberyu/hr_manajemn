@extends('layouts.app')
@section('title', 'Ajukan Ijin Khusus')
@section('page-title', 'Ajukan Ijin Khusus')
@section('page-subtitle', 'Isi formulir pengajuan ijin khusus')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    <p class="font-semibold mb-1">Terdapat kesalahan:</p>
    <ul class="list-disc list-inside space-y-0.5 text-xs">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

{{-- Data jenis ke JS — hindari masalah HTML attribute --}}
<script>
var __jenisList = {!! json_encode($jenisList->values()) !!};
</script>

<div class="max-w-2xl mx-auto">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
     x-data="ijinKhususForm()">

    <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
        <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-gray-800">Formulir Pengajuan Ijin Khusus</h2>
            <p class="text-xs text-gray-400 mt-0.5">Pilih jenis ijin terlebih dahulu</p>
        </div>
    </div>

    <form action="{{ route('ijin-khusus.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
        @csrf

        {{-- Jenis Ijin ──────────────────────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Jenis Ijin <span class="text-red-500">*</span></label>
            <select name="jenis_ijin_id" required @change="pickJenis($event.target.value)"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-300 @error('jenis_ijin_id') border-red-400 @enderror">
                <option value="">— Pilih Jenis Ijin —</option>
                @foreach($jenisList as $j)
                <option value="{{ $j->id }}" {{ old('jenis_ijin_id') == $j->id ? 'selected' : '' }}>
                    {{ $j->kode }} — {{ $j->nama }}
                </option>
                @endforeach
            </select>
            @error('jenis_ijin_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Info Card Jenis ─────────────────────────────────────────────────── --}}
        <div x-show="sel !== null" x-cloak x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="rounded-xl border border-purple-100 bg-gradient-to-r from-purple-50 to-violet-50 p-4">
                <p class="text-sm font-semibold text-purple-800 mb-2" x-text="sel && sel.nama"></p>
                <div class="flex flex-wrap gap-2">
                    <span x-show="sel && sel.max_hari"
                          class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs bg-white rounded-lg border border-purple-100 text-purple-700 font-medium">
                        <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Maks. <span x-text="sel && (sel.max_hari + ' hari')"></span>
                    </span>
                    <span x-show="sel && sel.wajib_lampiran"
                          class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs bg-white rounded-lg border border-amber-100 text-amber-700 font-medium">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        Wajib lampiran
                    </span>
                    <span x-show="sel && sel.butuh_waktu"
                          class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs bg-white rounded-lg border border-blue-100 text-blue-700 font-medium">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Isi jam (bukan seharian)
                    </span>
                </div>
                <p x-show="sel && sel.keterangan" class="text-xs text-purple-600 mt-2 italic" x-text="sel && sel.keterangan"></p>
            </div>
        </div>

        {{-- Pegawai ──────────────────────────────────────────────────────────── --}}
        @php $singlePegawai = $pegawai->count() === 1 ? $pegawai->first() : null; @endphp
        @if($singlePegawai)
        {{-- Karyawan/Atasan: auto-fill, tampilkan read-only --}}
        <input type="hidden" name="nik" value="{{ $singlePegawai->nik }}">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Pegawai</label>
            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                @php $initials = collect(explode(' ', $singlePegawai->nama))->take(2)->map(fn($w)=>strtoupper($w[0]))->join(''); @endphp
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ $initials }}</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $singlePegawai->nama }}</p>
                    <p class="text-xs text-gray-400">NIK {{ $singlePegawai->nik }}
                        @if($singlePegawai->jbtn ?? $singlePegawai->jabatan ?? null)
                         · {{ $singlePegawai->jbtn ?? $singlePegawai->jabatan }}
                        @endif
                    </p>
                </div>
                <span class="ml-auto text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full border border-green-100 font-medium">Anda</span>
            </div>
        </div>
        @else
        {{-- HRD/Admin: dropdown pilih pegawai --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Pegawai <span class="text-red-500">*</span></label>
            <select name="nik" required
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-purple-300 @error('nik') border-red-400 @enderror">
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

        <div class="border-t border-dashed border-gray-100"></div>

        {{-- Tanggal Mulai ────────────────────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal_mulai" id="tgl_mulai" required
                   value="{{ old('tanggal_mulai') }}"
                   @change="syncTglAkhirMin()"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 @error('tanggal_mulai') border-red-400 @enderror">
            @error('tanggal_mulai')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Tanggal Akhir (jika bukan butuh_waktu) ──────────────────────────── --}}
        <div x-show="!sel || !sel.butuh_waktu" x-cloak>
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                Tanggal Akhir
                <span x-show="sel && sel.max_hari" class="ml-1 font-normal text-amber-500 text-xs"
                      x-text="sel ? '(maks. ' + sel.max_hari + ' hari)' : ''"></span>
                <span x-show="!sel || !sel.max_hari" class="ml-1 font-normal text-gray-400 text-xs">
                    (kosongkan jika hanya 1 hari)
                </span>
            </label>
            <input type="date" name="tanggal_akhir" id="tgl_akhir"
                   value="{{ old('tanggal_akhir') }}"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 @error('tanggal_akhir') border-red-400 @enderror">
            @error('tanggal_akhir')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Jam (jika butuh_waktu) ───────────────────────────────────────────── --}}
        <div x-show="sel && sel.butuh_waktu" x-cloak>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Jam Ijin <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Jam Mulai</label>
                    <input type="time" name="jam_mulai"
                           :required="sel && sel.butuh_waktu"
                           value="{{ old('jam_mulai') }}"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 @error('jam_mulai') border-red-400 @enderror">
                    @error('jam_mulai')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Jam Selesai</label>
                    <input type="time" name="jam_selesai"
                           :required="sel && sel.butuh_waktu"
                           value="{{ old('jam_selesai') }}"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 @error('jam_selesai') border-red-400 @enderror">
                    @error('jam_selesai')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Alasan ───────────────────────────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Alasan / Keterangan <span class="text-red-500">*</span></label>
            <textarea name="alasan" rows="3" required maxlength="500"
                      placeholder="Jelaskan keperluan ijin Anda..."
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 resize-none @error('alasan') border-red-400 @enderror">{{ old('alasan') }}</textarea>
            @error('alasan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Lampiran (jika wajib_lampiran) ─────────────────────────────────── --}}
        <div x-show="sel && sel.wajib_lampiran" x-cloak>
            <label class="block text-xs font-semibold text-gray-600 mb-2">
                Lampiran <span class="text-red-500">*</span>
                <span class="ml-1 font-normal text-gray-400">(PDF / JPG / PNG, maks. 3 MB)</span>
            </label>
            <div class="relative border-2 border-dashed border-gray-200 rounded-xl hover:border-purple-300 transition-colors"
                 x-data="{ fileName: '' }">
                <input type="file" name="file_lampiran" accept=".pdf,.jpg,.jpeg,.png"
                       :required="sel && sel.wajib_lampiran"
                       @change="fileName = $event.target.files[0]?.name || ''"
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                <div class="px-4 py-5 text-center pointer-events-none">
                    <template x-if="!fileName">
                        <div>
                            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-xs text-gray-500">Klik atau seret file ke sini</p>
                            <p class="text-xs text-gray-400 mt-0.5">PDF, JPG, PNG — maks. 3 MB</p>
                        </div>
                    </template>
                    <template x-if="fileName">
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm text-green-700 font-medium" x-text="fileName"></span>
                        </div>
                    </template>
                </div>
            </div>
            @error('file_lampiran')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Actions ─────────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
            <button type="submit"
                    class="px-5 py-2.5 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold">
                Ajukan Ijin
            </button>
            <a href="{{ route('ijin-khusus.index') }}"
               class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>

</div>
</div>

@push('scripts')
<script>
function ijinKhususForm() {
    return {
        sel: null,
        pickJenis(id) {
            this.sel = __jenisList.find(j => j.id == id) || null;
        },
        syncTglAkhirMin() {
            const mulai = document.getElementById('tgl_mulai');
            const akhir = document.getElementById('tgl_akhir');
            if (mulai && akhir && mulai.value) {
                akhir.min = mulai.value;
                if (akhir.value && akhir.value < mulai.value) {
                    akhir.value = mulai.value;
                }
            }
        },
        init() {
            // Restore old value on validation error
            const oldId = '{{ old('jenis_ijin_id') }}';
            if (oldId) this.pickJenis(oldId);
        }
    };
}
</script>
@endpush

@endsection
