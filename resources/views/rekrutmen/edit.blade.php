@extends('layouts.app')
@section('title', 'Edit Lowongan')
@section('page-title', 'Edit Lowongan')
@section('page-subtitle', $rekrutmen->posisi)

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('rekrutmen.show', $rekrutmen) }}"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Edit Lowongan</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $rekrutmen->posisi }}</p>
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
        <form action="{{ route('rekrutmen.update', $rekrutmen) }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Posisi / Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="posisi" value="{{ old('posisi', $rekrutmen->posisi) }}" required maxlength="100"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Departemen <span class="text-red-500">*</span></label>
                    <select name="departemen_id" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                        <option value="">-- Pilih --</option>
                        @foreach($departemen as $id => $nama)
                        <option value="{{ $id }}" {{ old('departemen_id', $rekrutmen->departemen_id) == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah Dibutuhkan <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah_dibutuhkan" value="{{ old('jumlah_dibutuhkan', $rekrutmen->jumlah_dibutuhkan) }}" required min="1"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Buka <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_buka" value="{{ old('tanggal_buka', $rekrutmen->tanggal_buka?->format('Y-m-d')) }}" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Tutup <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_tutup" value="{{ old('tanggal_tutup', $rekrutmen->tanggal_tutup?->format('Y-m-d')) }}" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                    @foreach(\App\Models\Rekrutmen::STATUS as $s)
                    <option value="{{ $s }}" {{ old('status', $rekrutmen->status) === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi Pekerjaan</label>
                <textarea name="deskripsi" rows="3" maxlength="2000"
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none">{{ old('deskripsi', $rekrutmen->deskripsi) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Persyaratan</label>
                <textarea name="syarat" rows="3" maxlength="2000"
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none">{{ old('syarat', $rekrutmen->syarat) }}</textarea>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('rekrutmen.show', $rekrutmen) }}"
                   class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Zona Bahaya --}}
    <div class="bg-white border border-red-100 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4">
            <h3 class="text-sm font-semibold text-red-600 mb-1">Tutup Lowongan</h3>
            <p class="text-xs text-gray-500 mb-3">Status akan diubah ke "dibatalkan". Tindakan ini tidak dapat dibatalkan.</p>
            <form action="{{ route('rekrutmen.destroy', $rekrutmen) }}" method="POST"
                  onsubmit="return confirm('Yakin tutup lowongan ini?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 transition font-medium">
                    Tutup Lowongan
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
