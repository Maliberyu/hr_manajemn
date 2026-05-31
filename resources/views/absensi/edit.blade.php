@extends('layouts.app')
@section('title', 'Koreksi Absensi')

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
            <h1 class="text-xl font-bold text-gray-800">Koreksi Absensi</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $absensi->pegawai?->nama }} &mdash; {{ $absensi->tanggal->translatedFormat('l, d F Y') }}
            </p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        {{ $errors->first() }}
    </div>
    @endif

    {{-- Info read-only --}}
    <div class="bg-gray-50 border border-gray-200 rounded-2xl px-5 py-4 grid grid-cols-2 gap-3 text-sm">
        <div>
            <p class="text-xs text-gray-400">Karyawan</p>
            <p class="font-medium text-gray-800">{{ $absensi->pegawai?->nama }}</p>
            <p class="text-xs text-gray-400">{{ $absensi->pegawai?->nik }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Metode Asal</p>
            <p class="font-medium text-gray-800 capitalize">{{ $absensi->metode ?? '-' }}</p>
        </div>
        @if($absensi->terlambat_menit > 0)
        <div>
            <p class="text-xs text-gray-400">Keterlambatan</p>
            <p class="font-medium text-red-600">{{ $absensi->terlambat_menit }} menit</p>
        </div>
        @endif
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <form action="{{ route('absensi.update', $absensi) }}" method="POST" class="px-6 py-5 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
                <select name="status" required
                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                    @foreach(\App\Models\Absensi::STATUS as $s)
                    <option value="{{ $s }}" {{ old('status', $absensi->status) === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Masuk <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_masuk" required
                           value="{{ old('jam_masuk', $absensi->jam_masuk?->format('H:i')) }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Keluar</label>
                    <input type="time" name="jam_keluar"
                           value="{{ old('jam_keluar', $absensi->jam_keluar?->format('H:i')) }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Kosongkan jika belum check-out</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                <input type="text" name="keterangan" maxlength="255"
                       value="{{ old('keterangan', $absensi->keterangan) }}"
                       placeholder="Alasan koreksi..."
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit"
                        class="flex-1 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                    Simpan Koreksi
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
