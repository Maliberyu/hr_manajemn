@extends('layouts.app')
@section('title', 'Ajukan Training Eksternal')
@section('page-title', 'Ajukan Training Eksternal')
@section('page-subtitle', 'Pengajuan pelatihan di luar institusi')

@section('content')
<div class="max-w-2xl mx-auto" x-data="{
    mode: '{{ old('mode', 'pengajuan') }}',
    modeRekam() { return this.mode === 'rekam_langsung'; }
}">

    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('training.eksternal.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        {{-- Mode Pilih --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-700 mb-3">Jenis Pengajuan</p>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="mode" value="pengajuan" x-model="mode" class="sr-only peer">
                    <div class="p-3 rounded-xl border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition text-center">
                        <div class="text-sm font-semibold text-gray-700 peer-checked:text-blue-700">📋 Pengajuan</div>
                        <div class="text-xs text-gray-400 mt-1">Minta persetujuan sebelum training</div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="mode" value="rekam_langsung" x-model="mode" class="sr-only peer">
                    <div class="p-3 rounded-xl border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 transition text-center">
                        <div class="text-sm font-semibold text-gray-700 peer-checked:text-green-700">✅ Rekam Langsung</div>
                        <div class="text-xs text-gray-400 mt-1">Training sudah selesai, upload sertifikat</div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Data Training --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
            <p class="text-sm font-semibold text-gray-700">Data Training</p>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Karyawan yang Mengikuti <span class="text-red-500">*</span></label>
                <select name="pegawai_id" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($pegawai as $p)
                    <option value="{{ $p->id }}" {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} — {{ $p->jbtn }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Nama Training <span class="text-red-500">*</span></label>
                <input type="text" name="nama_training" value="{{ old('nama_training') }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Lembaga Penyelenggara <span class="text-red-500">*</span></label>
                    <input type="text" name="lembaga" value="{{ old('lembaga') }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Lokasi</label>
                    <input type="text" name="lokasi" value="{{ old('lokasi') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tanggal Selesai <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Estimasi Biaya (Rp)</label>
                    <input type="number" name="biaya" value="{{ old('biaya', 0) }}" min="0"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Deskripsi / Tujuan</label>
                <textarea name="deskripsi" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none">{{ old('deskripsi') }}</textarea>
            </div>
        </div>

        {{-- Pengajuan: Pilih Atasan --}}
        <div x-show="!modeRekam()" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-700 mb-3">Atasan Langsung</p>
            <p class="text-xs text-gray-400 mb-2">Pilih atasan yang akan memberikan persetujuan pertama.</p>
            <select name="atasan_id"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                <option value="">-- Pilih Atasan --</option>
                @foreach($atasanList as $a)
                <option value="{{ $a->id }}" {{ old('atasan_id') == $a->id ? 'selected' : '' }}>
                    {{ $a->nama }} ({{ \App\Models\User::ROLES[$a->role] ?? $a->role }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Rekam Langsung: Upload Sertifikat --}}
        <div x-show="modeRekam()" x-cloak class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Data Sertifikat</p>
            <p class="text-xs text-gray-400">Upload sertifikat yang sudah diperoleh untuk divalidasi HR.</p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nomor Sertifikat</label>
                    <input type="text" name="nomor_sertifikat" value="{{ old('nomor_sertifikat') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Masa Berlaku</label>
                    <input type="date" name="masa_berlaku" value="{{ old('masa_berlaku') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">File Sertifikat (PDF/Gambar)</label>
                <input type="file" name="file_sertifikat" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full text-sm text-gray-600 border border-gray-200 rounded-xl px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                <span x-text="modeRekam() ? 'Rekam & Submit ke HR' : 'Ajukan Training'"></span>
            </button>
            <a href="{{ route('training.eksternal.index') }}"
               class="px-5 py-2.5 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush
@endsection
