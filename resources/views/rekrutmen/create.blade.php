@extends('layouts.app')
@section('title', 'Buka Lowongan Baru')
@section('page-title', 'Buka Lowongan')
@section('page-subtitle', 'Tambah lowongan rekrutmen baru')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('rekrutmen.index') }}"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Buka Lowongan Baru</h1>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('rekrutmen.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Posisi / Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="posisi" value="{{ old('posisi') }}" required maxlength="100"
                       placeholder="Contoh: Staff Akuntansi, Perawat, dll"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Departemen <span class="text-red-500">*</span></label>
                    <select name="departemen_id" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                        <option value="">-- Pilih --</option>
                        @foreach($departemen as $id => $nama)
                        <option value="{{ $id }}" {{ old('departemen_id') == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Dibutuhkan <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_dibutuhkan" value="{{ old('jumlah_dibutuhkan', 1) }}" required min="1"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Buka <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_buka" value="{{ old('tanggal_buka', today()->format('Y-m-d')) }}" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Tutup <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_tutup" value="{{ old('tanggal_tutup') }}" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi Pekerjaan</label>
                <textarea name="deskripsi" rows="3" maxlength="2000"
                          placeholder="Uraian tugas dan tanggung jawab..."
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none">{{ old('deskripsi') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Persyaratan</label>
                <textarea name="syarat" rows="3" maxlength="2000"
                          placeholder="Kualifikasi yang dibutuhkan..."
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none">{{ old('syarat') }}</textarea>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                    Buka Lowongan
                </button>
                <a href="{{ route('rekrutmen.index') }}"
                   class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
