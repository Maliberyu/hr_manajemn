@extends('layouts.app')
@section('title', 'Edit Kontrak')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('kontrak.show', $kontrak) }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Edit Kontrak</h1>
            <p class="text-sm text-gray-500">{{ $kontrak->pegawai?->nama ?? $kontrak->nik }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('kontrak.update', $kontrak) }}" enctype="multipart/form-data"
          class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5 shadow-sm"
          x-data="{ isTetap: {{ $kontrak->jenis?->is_tetap ? 'true' : 'false' }} }">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Kontrak <span class="text-red-500">*</span></label>
                <select name="jenis_kontrak_id" required
                        @change="isTetap = $event.target.options[$event.target.selectedIndex].dataset.tetap === '1'"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($jenisList as $j)
                    <option value="{{ $j->id }}" data-tetap="{{ $j->is_tetap ? '1' : '0' }}"
                            @selected(old('jenis_kontrak_id', $kontrak->jenis_kontrak_id) == $j->id)>
                        {{ $j->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['aktif','berakhir','diperbarui','dibatalkan'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $kontrak->status) === $s)>
                        {{ ucfirst($s) }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">No. Kontrak</label>
            <input type="text" name="no_kontrak" value="{{ old('no_kontrak', $kontrak->no_kontrak) }}"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_mulai" value="{{ old('tgl_mulai', $kontrak->tgl_mulai->toDateString()) }}"
                       required class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div x-show="!isTetap">
                <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai"
                       value="{{ old('tgl_selesai', $kontrak->tgl_selesai?->toDateString()) }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal TTD</label>
                <input type="date" name="tgl_tanda_tangan"
                       value="{{ old('tgl_tanda_tangan', $kontrak->tgl_tanda_tangan?->toDateString()) }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Ganti File Kontrak</label>
                @if($kontrak->file_kontrak)
                <div class="mb-2">
                    <a href="{{ $kontrak->file_url }}" target="_blank" class="text-xs text-blue-600 hover:underline">
                        File saat ini →
                    </a>
                </div>
                @endif
                <input type="file" name="file_kontrak" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="catatan" rows="3" maxlength="500"
                      class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('catatan', $kontrak->catatan) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-2">
            <form method="POST" action="{{ route('kontrak.destroy', $kontrak) }}"
                  onsubmit="return confirm('Hapus kontrak ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm text-red-500 hover:text-red-700 transition">Hapus Kontrak</button>
            </form>
            <div class="flex gap-3">
                <a href="{{ route('kontrak.show', $kontrak) }}"
                   class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 hover:bg-gray-50 transition">Batal</a>
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
