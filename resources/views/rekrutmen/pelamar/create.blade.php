@extends('layouts.app')
@section('title', 'Tambah Pelamar')
@section('page-title', 'Tambah Pelamar')
@section('page-subtitle', 'Formulir pendaftaran pelamar baru')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-800">Tambah Pelamar Baru</h2>
                <p class="text-xs text-gray-500">Lengkapi data pelamar</p>
            </div>
        </div>

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

        <form method="POST" action="{{ route('rekrutmen.pelamar.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Lowongan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Lowongan <span class="text-red-500">*</span>
                </label>
                <select name="lowongan_id" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Lowongan --</option>
                    @foreach($lowonganList as $lw)
                    <option value="{{ $lw->id }}"
                        {{ old('lowongan_id', $selected) == $lw->id ? 'selected' : '' }}>
                        {{ $lw->posisi }} ({{ $lw->no_lowongan }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Nama --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nama" required maxlength="100"
                       value="{{ old('nama') }}"
                       placeholder="Nama lengkap pelamar"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Email & No HP --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input type="email" name="email" maxlength="100"
                           value="{{ old('email') }}"
                           placeholder="email@contoh.com"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. HP <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="no_hp" maxlength="20"
                           value="{{ old('no_hp') }}"
                           placeholder="08xxxxxxxxxx"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            {{-- Tanggal Lahir --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Lahir <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <input type="date" name="tanggal_lahir"
                       value="{{ old('tanggal_lahir') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Pendidikan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pendidikan Terakhir</label>
                <select name="pendidikan_terakhir"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Pendidikan --</option>
                    @foreach(['SMA/SMK','D3','S1','S2','S3','Lainnya'] as $pend)
                    <option value="{{ $pend }}" {{ old('pendidikan_terakhir') === $pend ? 'selected' : '' }}>{{ $pend }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Pengalaman --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pengalaman Kerja (Tahun) <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <input type="number" name="pengalaman_tahun" min="0" max="50"
                       value="{{ old('pengalaman_tahun') }}"
                       placeholder="0"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Sumber --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sumber Pelamar</label>
                <select name="sumber"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Sumber --</option>
                    @foreach(\App\Models\HrPelamar::SUMBER as $src)
                    <option value="{{ $src }}" {{ old('sumber') === $src ? 'selected' : '' }}>{{ $src }}</option>
                    @endforeach
                </select>
            </div>

            {{-- CV Upload --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Upload CV (PDF) <span class="text-gray-400 font-normal">(Opsional)</span></label>
                <input type="file" name="cv" accept=".pdf"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white file:mr-3 file:py-1 file:px-3 file:text-xs file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-400 mt-1">Maksimal 5MB, format PDF</p>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Tambah Pelamar
                </button>
                <a href="{{ route('rekrutmen.pelamar.index') }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
