@extends('layouts.app')
@section('title', 'Input Absensi Manual')

@section('content')
<div class="max-w-xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('absensi.index') }}"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Input Absensi Manual</h1>
            <p class="text-sm text-gray-500 mt-0.5">Catat kehadiran secara manual oleh HRD</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('absensi.store') }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan <span class="text-red-500">*</span></label>
                <select name="pegawai_id" required
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach($pegawai as $p)
                    <option value="{{ $p->id }}" {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} ({{ $p->nik }}){{ $p->jbtn ? ' — '.$p->jbtn : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal" required
                           value="{{ old('tanggal', today()->format('Y-m-d')) }}"
                           max="{{ today()->format('Y-m-d') }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                        @foreach(\App\Models\Absensi::STATUS as $s)
                        <option value="{{ $s }}" {{ old('status', 'hadir') === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Masuk <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_masuk" required
                           value="{{ old('jam_masuk', '07:00') }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Keluar</label>
                    <input type="time" name="jam_keluar"
                           value="{{ old('jam_keluar') }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Kosongkan jika belum check-out</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                <input type="text" name="keterangan" maxlength="255"
                       value="{{ old('keterangan') }}"
                       placeholder="Alasan / catatan tambahan..."
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                    Simpan Absensi
                </button>
                <a href="{{ route('absensi.index') }}"
                   class="px-5 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
