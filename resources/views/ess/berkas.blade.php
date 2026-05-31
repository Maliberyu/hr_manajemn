@extends('layouts.app')
@section('title', 'Dokumen Saya')
@section('page-title', 'Dokumen Saya')
@section('page-subtitle', 'Upload & kelola berkas dokumen pribadi Anda')

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4 text-sm">
    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
    </svg>
    <span>{{ $errors->first() }}</span>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Kolom kiri: Info + Form Upload ─────────────────────────────────── --}}
    <div class="lg:col-span-1 space-y-4">

        {{-- Info identitas --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
            <img src="{{ $pegawai->foto_url }}"
                 class="w-12 h-12 rounded-full object-cover flex-shrink-0 border-2 border-gray-100"
                 onerror="this.src='{{ asset('images/avatar-default.png') }}'">
            <div class="min-w-0">
                <p class="font-semibold text-gray-800 text-sm truncate">{{ $pegawai->nama }}</p>
                <p class="text-xs text-gray-400">NIK {{ $pegawai->nik }} &nbsp;·&nbsp; {{ $pegawai->jbtn ?? '-' }}</p>
            </div>
        </div>

        {{-- Info panduan --}}
        <div class="bg-blue-50 border border-blue-100 rounded-2xl px-4 py-3">
            <p class="text-xs font-semibold text-blue-700 mb-1">Panduan Upload</p>
            <ul class="text-xs text-blue-600 space-y-1 list-disc list-inside">
                <li>Format: PDF, JPG, PNG — maks. 5 MB</li>
                <li>Upload ulang menggantikan dokumen lama dengan jenis yang sama</li>
                <li>Aktifkan notifikasi agar sistem mengingatkan saat dokumen mendekati kadaluarsa</li>
            </ul>
        </div>

        {{-- Form upload --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"
             x-data="{ namaInput: '', fileNama: '', isDrag: false, adaKadaluarsa: false }">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload Dokumen Baru
            </h3>

            <form method="POST" action="{{ route('ess.berkas.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                {{-- Nama dokumen --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Jenis Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="nama_dokumen"
                           list="jenis-list"
                           x-model="namaInput"
                           value="{{ old('nama_dokumen') }}"
                           placeholder="cth: Ijazah, STR, SIP, KTP..."
                           autocomplete="off"
                           required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <datalist id="jenis-list">
                        @foreach($jenisList as $nama)
                        <option value="{{ $nama }}">
                        @endforeach
                    </datalist>
                    <p class="text-xs text-gray-400 mt-1">Pilih dari daftar atau ketik nama baru</p>
                </div>

                {{-- Keterangan --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" maxlength="255"
                           value="{{ old('keterangan') }}"
                           placeholder="cth: Berlaku s.d. Des 2026"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                {{-- Toggle Kadaluarsa --}}
                <div class="border border-gray-100 rounded-xl p-3 bg-gray-50/60">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <button type="button"
                                @click="adaKadaluarsa = !adaKadaluarsa"
                                :class="adaKadaluarsa ? 'bg-amber-500' : 'bg-gray-300'"
                                class="relative inline-flex h-5 w-9 flex-shrink-0 rounded-full transition-colors focus:outline-none">
                            <span :class="adaKadaluarsa ? 'translate-x-4' : 'translate-x-0.5'"
                                  class="inline-block h-4 w-4 mt-0.5 rounded-full bg-white shadow transform transition-transform"></span>
                        </button>
                        <span class="text-xs font-medium text-gray-700">Dokumen ini punya tanggal kadaluarsa</span>
                    </label>
                    <div x-show="adaKadaluarsa" x-cloak class="mt-3 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Kadaluarsa</label>
                            <input type="date" name="tgl_kadaluarsa"
                                   value="{{ old('tgl_kadaluarsa') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="notif_aktif" value="1" checked
                                   class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400">
                            <span class="text-xs text-gray-600">Aktifkan notifikasi kadaluarsa</span>
                        </label>
                    </div>
                </div>

                {{-- File input --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        File <span class="text-red-500">*</span>
                    </label>
                    <label class="block cursor-pointer"
                           :class="isDrag ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:border-blue-300'"
                           @dragover.prevent="isDrag = true"
                           @dragleave="isDrag = false"
                           @drop.prevent="isDrag = false; fileNama = $event.dataTransfer.files[0]?.name; $refs.fileInput.files = $event.dataTransfer.files"
                           class="flex flex-col items-center justify-center gap-2 p-5 border-2 border-dashed rounded-xl transition text-center">
                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span x-text="fileNama || 'Klik atau drag file ke sini'" class="text-sm text-gray-500"></span>
                        <span class="text-xs text-gray-400">PDF, JPG, PNG — maks. 5 MB</span>
                        <input type="file" name="file" x-ref="fileInput" required
                               accept=".pdf,.jpg,.jpeg,.png"
                               @change="fileNama = $event.target.files[0]?.name"
                               class="hidden">
                    </label>
                    @error('file') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                        :disabled="!namaInput || !fileNama"
                        :class="(namaInput && fileNama) ? 'bg-blue-600 hover:bg-blue-700 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                        class="w-full py-2.5 text-white text-sm font-semibold rounded-xl transition">
                    Upload Dokumen
                </button>
            </form>
        </div>

    </div>

    {{-- ── Kolom kanan: Daftar Berkas ──────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700">Dokumen Tersimpan</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $berkas->count() }} file · HRD dapat melihat dan memverifikasi dokumen Anda</p>
                </div>
                @php
                    $adaKdl = $berkas->filter(fn($b) => in_array($b->status_kadaluarsa, ['kadaluarsa','urgent']))->count();
                @endphp
                @if($adaKdl > 0)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-50 text-red-700 text-xs font-semibold rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                    {{ $adaKdl }} perlu diperbarui
                </span>
                @endif
            </div>

            @if($berkas->isEmpty())
            <div class="flex flex-col items-center gap-3 text-gray-400 py-16">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="font-medium text-sm">Belum ada dokumen diupload</p>
                <p class="text-xs">Upload dokumen menggunakan form di sebelah kiri</p>
            </div>
            @else
            <ul class="divide-y divide-gray-50">
                @foreach($berkas as $b)
                <li class="px-5 py-4 hover:bg-gray-50/60 transition"
                    x-data="{ editKdl: false }">

                    <div class="flex items-start gap-3">
                        {{-- Ikon tipe file --}}
                        <div class="flex-shrink-0 mt-0.5">
                            @if($b->is_pdf)
                            <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            @else
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-gray-800">{{ $b->jenis?->nama ?? 'Dokumen' }}</p>
                                {{-- Badge status kadaluarsa --}}
                                @if($b->status_kadaluarsa === 'kadaluarsa')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Kadaluarsa
                                </span>
                                @elseif($b->status_kadaluarsa === 'urgent')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>Sisa {{ $b->hari_sisa }} hari
                                </span>
                                @elseif($b->status_kadaluarsa === 'warning')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Sisa {{ $b->hari_sisa }} hari
                                </span>
                                @elseif($b->status_kadaluarsa === 'aktif')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aktif
                                </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $b->nama_file }}</p>
                            @if($b->keterangan)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $b->keterangan }}</p>
                            @endif
                            <div class="flex items-center gap-3 mt-1 flex-wrap">
                                <p class="text-xs text-gray-400">
                                    Upload: {{ $b->tgl_upload?->translatedFormat('d F Y') ?? '-' }}
                                    &nbsp;·&nbsp; <span class="uppercase font-medium">{{ $b->ekstensi }}</span>
                                </p>
                                @if($b->tgl_kadaluarsa)
                                <p class="text-xs {{ $b->status_kadaluarsa === 'kadaluarsa' ? 'text-red-500 font-semibold' : 'text-gray-400' }}">
                                    Exp: {{ $b->tgl_kadaluarsa->translatedFormat('d F Y') }}
                                    @if(!$b->notif_aktif)<span class="text-gray-300">(notif off)</span>@endif
                                </p>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex-shrink-0 flex items-center gap-1">
                            {{-- Atur kadaluarsa --}}
                            <button type="button"
                                    @click="editKdl = !editKdl"
                                    :class="editKdl ? 'bg-amber-50 text-amber-600' : 'text-gray-400 hover:bg-gray-100'"
                                    class="p-1.5 rounded-lg transition" title="Atur Kadaluarsa">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            {{-- Download --}}
                            <a href="{{ route('ess.berkas.download', $b) }}"
                               class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Download">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </a>
                            {{-- Hapus --}}
                            <form method="POST" action="{{ route('ess.berkas.destroy', $b) }}"
                                  onsubmit="return confirm('Hapus berkas \"{{ $b->jenis?->nama }}\" ini?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Panel kadaluarsa (collapsible) --}}
                    <div x-show="editKdl" x-cloak
                         class="mt-3 ml-12 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <p class="text-xs font-semibold text-amber-700 mb-2">Atur Tanggal Kadaluarsa</p>
                        <form method="POST" action="{{ route('ess.berkas.kadaluarsa', $b) }}"
                              class="flex items-end gap-3 flex-wrap">
                            @csrf @method('PATCH')
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Tanggal Kadaluarsa</label>
                                <input type="date" name="tgl_kadaluarsa"
                                       value="{{ $b->tgl_kadaluarsa?->format('Y-m-d') ?? '' }}"
                                       class="px-3 py-1.5 text-sm border border-amber-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-400">
                            </div>
                            <label class="flex items-center gap-1.5 cursor-pointer select-none pb-1.5">
                                <input type="checkbox" name="notif_aktif" value="1"
                                       {{ $b->notif_aktif ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400">
                                <span class="text-xs text-gray-700">Aktifkan notifikasi</span>
                            </label>
                            <div class="flex gap-2 pb-1.5">
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition">
                                    Simpan
                                </button>
                                <button type="button" @click="editKdl = false"
                                        class="px-3 py-1.5 text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 rounded-lg transition">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>

                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>

</div>

{{-- ── Ijazah dari Modul Pendidikan ──────────────────────────────────────────── --}}
@if($ijazahList->isNotEmpty())
<div class="mt-5 bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-blue-50 bg-blue-50/50 flex items-center gap-2">
        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
        </svg>
        <h3 class="text-sm font-semibold text-blue-700">Ijazah (dari Modul Pendidikan)</h3>
        <span class="ml-auto text-xs text-blue-400">{{ $ijazahList->count() }} file</span>
    </div>
    <ul class="divide-y divide-gray-50">
        @foreach($ijazahList as $ij)
        <li class="px-5 py-3 flex items-center gap-3 hover:bg-gray-50/60 transition">
            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-gray-800">Ijazah {{ $ij->jenjang }}</p>
                    @if($ij->is_terakhir)
                    <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 rounded-full">Terakhir</span>
                    @endif
                </div>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $ij->nama_institusi }}{{ $ij->jurusan ? ' · '.$ij->jurusan : '' }}
                    @if($ij->tahun_lulus) · Lulus {{ $ij->tahun_lulus }} @endif
                </p>
            </div>
            <a href="{{ $ij->file_url }}" target="_blank"
               class="flex-shrink-0 p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Lihat / Unduh">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif
@endsection
