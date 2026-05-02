@extends('layouts.app')
@section('title', 'Dokumen — ' . $karyawan->nama)
@section('page-title', 'Manajemen Dokumen')
@section('page-subtitle', $karyawan->nama . ' · ' . ($karyawan->jbtn ?? '-'))

@section('content')

{{-- Breadcrumb --}}
<div class="mb-5 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('karyawan.index') }}" class="hover:text-blue-600 transition">Master Karyawan</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('karyawan.show', $karyawan) }}" class="hover:text-blue-600 transition">{{ $karyawan->nama }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium">Dokumen</span>
</div>

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

    {{-- ── Kolom kiri: Form Upload ──────────────────────────────────────────── --}}
    <div class="lg:col-span-1">

        {{-- Profil mini karyawan --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 flex items-center gap-3">
            <img src="{{ $karyawan->foto_url }}"
                 class="w-12 h-12 rounded-full object-cover flex-shrink-0 border-2 border-gray-100"
                 onerror="this.src='{{ asset('images/avatar-default.png') }}'">
            <div class="min-w-0">
                <p class="font-semibold text-gray-800 text-sm truncate">{{ $karyawan->nama }}</p>
                <p class="text-xs text-gray-400">NIK {{ $karyawan->nik }}</p>
            </div>
        </div>

        {{-- Form upload --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"
             x-data="{ namaInput: '', fileNama: '', isDrag: false }">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload Dokumen Baru
            </h3>

            <form method="POST"
                  action="{{ route('karyawan.berkas.store', $karyawan) }}"
                  enctype="multipart/form-data"
                  class="space-y-4">
                @csrf

                {{-- Nama dokumen (combobox dengan datalist) --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Nama Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="nama_dokumen"
                           list="jenis-list"
                           x-model="namaInput"
                           placeholder="cth: Ijazah, STR, Pelatihan ACLS..."
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
                           placeholder="cth: Ijazah S1 Keperawatan 2019"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
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
                <h3 class="text-sm font-semibold text-gray-700">Dokumen Tersimpan</h3>
                <span class="text-xs text-gray-400">{{ $berkas->count() }} file</span>
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
                <li class="px-5 py-4 flex items-start gap-3 hover:bg-gray-50/60 transition">
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
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $b->jenis?->nama ?? 'Dokumen' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $b->nama_file }}</p>
                        @if($b->keterangan)
                        <p class="text-xs text-gray-500 mt-0.5">{{ $b->keterangan }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">
                            Diupload: {{ $b->tgl_upload?->translatedFormat('d F Y') ?? '-' }} &nbsp;·&nbsp;
                            <span class="uppercase font-medium">{{ $b->ekstensi }}</span>
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex-shrink-0 flex items-center gap-1">
                        <a href="{{ route('karyawan.berkas.download', [$karyawan, $b]) }}"
                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Download">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                        </a>
                        <form method="POST"
                              action="{{ route('karyawan.berkas.destroy', [$karyawan, $b]) }}"
                              onsubmit="return confirm('Hapus berkas ini?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>

</div>
@endsection
