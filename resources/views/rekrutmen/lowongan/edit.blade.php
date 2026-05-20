@extends('layouts.app')
@section('title', 'Edit Lowongan')
@section('page-title', 'Edit Lowongan')
@section('page-subtitle', $lowongan->no_lowongan)

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-800">Edit Lowongan</h2>
                <p class="font-mono text-xs text-gray-400">{{ $lowongan->no_lowongan }}</p>
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

        <form method="POST" action="{{ route('rekrutmen.lowongan.update', $lowongan) }}" class="space-y-4">
            @csrf
            @method('PUT')

            {{-- Posisi --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Posisi <span class="text-red-500">*</span>
                </label>
                <input type="text" name="posisi" required maxlength="100"
                       value="{{ old('posisi', $lowongan->posisi) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Departemen --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Departemen <span class="text-red-500">*</span>
                </label>
                <select name="departemen_id" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Departemen --</option>
                    @foreach($departemen as $dep)
                    <option value="{{ $dep->dep_id }}"
                        {{ old('departemen_id', $lowongan->departemen_id) == $dep->dep_id ? 'selected' : '' }}>
                        {{ $dep->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Kuota --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Kuota <span class="text-red-500">*</span>
                </label>
                <input type="number" name="kuota" required min="1" max="999"
                       value="{{ old('kuota', $lowongan->kuota) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Tanggal --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Tanggal Buka <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tgl_buka" required
                           value="{{ old('tgl_buka', $lowongan->tgl_buka ? \Carbon\Carbon::parse($lowongan->tgl_buka)->format('Y-m-d') : '') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Tanggal Tutup <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tgl_tutup" required
                           value="{{ old('tgl_tutup', $lowongan->tgl_tutup ? \Carbon\Carbon::parse($lowongan->tgl_tutup)->format('Y-m-d') : '') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    @foreach(['draft','aktif','ditutup','selesai'] as $s)
                    <option value="{{ $s }}" {{ old('status', $lowongan->status) === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi Pekerjaan</label>
                <textarea name="deskripsi" rows="4" maxlength="2000"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('deskripsi', $lowongan->deskripsi) }}</textarea>
            </div>

            {{-- Syarat --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Persyaratan</label>
                <textarea name="syarat" rows="4" maxlength="2000"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('syarat', $lowongan->syarat) }}</textarea>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('rekrutmen.lowongan.show', $lowongan) }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
