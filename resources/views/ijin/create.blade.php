@extends('layouts.app')
@section('title', 'Ajukan ' . $labelJenis)
@section('page-title', 'Ajukan ' . $labelJenis)
@section('page-subtitle', 'Isi formulir pengajuan di bawah ini')

@section('content')
<div class="max-w-xl mx-auto">

    @if($errors->any())
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-5 flex items-center gap-2">
            <span class="text-xl">{{ \App\Models\PengajuanIjin::JENIS_ICON[$jenis] }}</span>
            {{ $labelJenis }}
        </h3>

        <form method="POST" action="{{ parse_url(route('ijin.store', $jenis), PHP_URL_PATH) }}"
              enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Pegawai (HRD input untuk semua, karyawan/atasan hanya diri sendiri) --}}
            @if(auth()->user()->hasRole(['hrd','admin']) && $pegawai->count() > 1)
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pegawai <span class="text-red-500">*</span></label>
                <select name="nik" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawai as $p)
                    <option value="{{ $p->nik }}" {{ old('nik') === $p->nik ? 'selected' : '' }}>
                        {{ $p->nama }} — {{ $p->jbtn }}
                    </option>
                    @endforeach
                </select>
            </div>
            @else
            <input type="hidden" name="nik" value="{{ $pegawai->first()?->nik }}">
            <div class="px-3 py-2 bg-gray-50 rounded-xl text-sm text-gray-700">
                <span class="text-gray-400 text-xs">Pegawai:</span>
                <span class="font-medium">{{ $pegawai->first()?->nama }}</span>
            </div>
            @endif

            {{-- Tanggal --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal <span class="text-red-500">*</span></label>
                <input type="date" name="tanggal" required
                       value="{{ old('tanggal', today()->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Jam (untuk terlambat & pulang duluan) --}}
            @if($jenis === 'terlambat')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Masuk Shift <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" required value="{{ old('jam_mulai') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <p class="text-xs text-gray-400 mt-0.5">Jam masuk seharusnya</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Tiba <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_selesai" required value="{{ old('jam_selesai') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <p class="text-xs text-gray-400 mt-0.5">Jam tiba sebenarnya</p>
                </div>
            </div>
            @elseif($jenis === 'pulang_duluan')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Pulang Duluan <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_mulai" required value="{{ old('jam_mulai') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <p class="text-xs text-gray-400 mt-0.5">Jam keluar lebih awal</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jam Keluar Seharusnya <span class="text-red-500">*</span></label>
                    <input type="time" name="jam_selesai" required value="{{ old('jam_selesai') }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <p class="text-xs text-gray-400 mt-0.5">Jam keluar sesuai shift</p>
                </div>
            </div>
            @endif

            {{-- Alasan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    @if($jenis === 'sakit') Diagnosis / Keluhan @else Alasan @endif
                    <span class="text-red-500">*</span>
                </label>
                <textarea name="alasan" required maxlength="500" rows="3"
                          placeholder="{{ $jenis === 'sakit' ? 'Contoh: Demam, flu, sakit kepala...' : 'Jelaskan alasan pengajuan...' }}"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('alasan') }}</textarea>
            </div>

            {{-- Upload surat sakit (wajib untuk sakit) --}}
            @if($jenis === 'sakit')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Surat Sakit / Bukti <span class="text-red-500">*</span>
                </label>
                <input type="file" name="file_surat" required accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                <p class="text-xs text-gray-400 mt-1">Format: PDF, JPG, PNG. Maks 2MB.</p>
            </div>
            @endif

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Ajukan Sekarang
                </button>
                <a href="{{ route('ijin.index', $jenis) }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

</div>
@endsection
