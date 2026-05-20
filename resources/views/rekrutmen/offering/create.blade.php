@extends('layouts.app')
@section('title', 'Buat Offering')
@section('page-title', 'Buat Offering')
@section('page-subtitle', 'Formulir surat penawaran kerja')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-gray-800">Buat Surat Penawaran Kerja</h2>
                <p class="text-xs text-gray-500">Lengkapi detail offering untuk pelamar</p>
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

        <form method="POST" action="{{ route('rekrutmen.offering.store') }}" class="space-y-4">
            @csrf

            {{-- Pelamar --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Pelamar <span class="text-red-500">*</span>
                </label>
                <select name="pelamar_id" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Pilih Pelamar --</option>
                    @foreach($pelamar as $p)
                    <option value="{{ $p->id }}"
                        {{ old('pelamar_id', $selected) == $p->id ? 'selected' : '' }}>
                        {{ $p->nama }} &mdash; {{ $p->lowongan?->posisi ?? 'N/A' }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Hanya menampilkan pelamar yang sudah melewati tahap interview</p>
            </div>

            {{-- Gaji Ditawarkan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Gaji Ditawarkan <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500">Rp</span>
                    <input type="number" name="gaji_ditawarkan" min="0"
                           value="{{ old('gaji_ditawarkan') }}"
                           placeholder="0"
                           class="w-full pl-10 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika belum ditentukan</p>
            </div>

            {{-- Tanggal Offering --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Tanggal Offering <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_offering" required
                       value="{{ old('tanggal_offering', today()->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Catatan <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <textarea name="catatan" rows="4" maxlength="1000"
                          placeholder="Catatan tambahan mengenai penawaran kerja, benefit, syarat dan ketentuan..."
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('catatan') }}</textarea>
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Buat Offering
                </button>
                <a href="{{ route('rekrutmen.offering.index') }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
