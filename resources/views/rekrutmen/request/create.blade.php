@extends('layouts.app')
@section('title', 'Ajukan Permintaan SDM')
@section('page-title', 'Permintaan SDM')
@section('page-subtitle', 'Formulir pengajuan kebutuhan sumber daya manusia')

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
                <h2 class="text-base font-semibold text-gray-800">Ajukan Permintaan SDM Baru</h2>
                <p class="text-xs text-gray-500">Permintaan akan diproses oleh HRD</p>
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

        <form method="POST" action="{{ route('rekrutmen.request.store') }}" class="space-y-4">
            @csrf

            {{-- Posisi --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Posisi yang Dibutuhkan <span class="text-red-500">*</span>
                </label>
                <input type="text" name="posisi" required maxlength="100"
                       value="{{ old('posisi') }}"
                       placeholder="Contoh: Staff Administrasi, Operator Produksi..."
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
                    <option value="{{ $dep->dep_id }}" {{ old('departemen_id') == $dep->dep_id ? 'selected' : '' }}>
                        {{ $dep->nama }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Jumlah --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Jumlah Kebutuhan <span class="text-red-500">*</span>
                </label>
                <input type="number" name="jumlah" required min="1" max="99"
                       value="{{ old('jumlah', 1) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                <p class="text-xs text-gray-400 mt-1">Jumlah karyawan yang dibutuhkan (1-99 orang)</p>
            </div>

            {{-- Alasan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Alasan / Justifikasi <span class="text-red-500">*</span>
                </label>
                <textarea name="alasan" required rows="4" maxlength="1000"
                          placeholder="Jelaskan alasan kebutuhan penambahan SDM, kondisi tim saat ini, dan urgensinya..."
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('alasan') }}</textarea>
            </div>

            {{-- Tanggal Dibutuhkan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Tanggal Dibutuhkan <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <input type="date" name="tanggal_dibutuhkan"
                       value="{{ old('tanggal_dibutuhkan') }}"
                       min="{{ today()->format('Y-m-d') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                <p class="text-xs text-gray-400 mt-1">Perkiraan tanggal karyawan baru harus sudah bergabung</p>
            </div>

            {{-- Alur Info --}}
            <div class="px-4 py-3 bg-blue-50 rounded-xl border border-blue-100">
                <p class="text-xs font-semibold text-blue-700 mb-2">Alur Persetujuan:</p>
                <div class="flex items-center gap-2 text-xs text-blue-600 flex-wrap">
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium">Pengajuan</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full font-medium">Review HRD</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full font-medium">Direktur (jika perlu)</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">Disetujui</span>
                </div>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Ajukan Permintaan SDM
                </button>
                <a href="{{ route('rekrutmen.request.index') }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
