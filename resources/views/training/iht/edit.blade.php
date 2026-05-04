@extends('layouts.app')
@section('title', 'Edit IHT')
@section('page-title', 'Edit In-House Training')
@section('page-subtitle', $iht->nama_training)

@section('content')
<div class="max-w-2xl mx-auto">

    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('training.iht.update', $iht) }}" class="space-y-4">
        @csrf @method('PUT')

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
            <p class="text-sm font-semibold text-gray-700">Informasi Training</p>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Nama Training <span class="text-red-500">*</span></label>
                <input type="text" name="nama_training" value="{{ old('nama_training', $iht->nama_training) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Penyelenggara <span class="text-red-500">*</span></label>
                    <input type="text" name="penyelenggara" value="{{ old('penyelenggara', $iht->penyelenggara) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pemateri / Narasumber</label>
                    <input type="text" name="pemateri" value="{{ old('pemateri', $iht->pemateri) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Lokasi <span class="text-red-500">*</span></label>
                <input type="text" name="lokasi" value="{{ old('lokasi', $iht->lokasi) }}" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $iht->tanggal_mulai->toDateString()) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', $iht->tanggal_selesai->toDateString()) }}" required
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jam Mulai</label>
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai', $iht->jam_mulai) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jam Selesai</label>
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai', $iht->jam_selesai) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Kuota Peserta</label>
                    <input type="number" name="kuota" value="{{ old('kuota', $iht->kuota) }}" min="1"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                        @foreach(\App\Models\IHT::STATUS as $k => $v)
                        <option value="{{ $k }}" {{ old('status', $iht->status) === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Deskripsi</label>
                <textarea name="deskripsi" rows="3"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none">{{ old('deskripsi', $iht->deskripsi) }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Data Sertifikat</p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nama Penandatangan</label>
                    <input type="text" name="penandatangan_nama" value="{{ old('penandatangan_nama', $iht->penandatangan_nama) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jabatan Penandatangan</label>
                    <input type="text" name="penandatangan_jabatan" value="{{ old('penandatangan_jabatan', $iht->penandatangan_jabatan) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                Simpan Perubahan
            </button>
            <a href="{{ route('training.iht.show', $iht) }}"
               class="px-5 py-2.5 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
