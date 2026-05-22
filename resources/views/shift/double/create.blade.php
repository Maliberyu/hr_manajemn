@extends('layouts.app')
@section('title', 'Ajukan Double Shift')
@section('page-title', 'Ajukan Double Shift')
@section('page-subtitle', 'Ambil dua shift berturut-turut — shift kedua dihitung lembur')

@section('content')

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    <p class="font-semibold mb-1">Terdapat kesalahan:</p>
    <ul class="list-disc list-inside text-xs space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="max-w-2xl mx-auto">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-3">
        <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-gray-800">Formulir Double Shift</h2>
            <p class="text-xs text-gray-400 mt-0.5">Shift kedua akan otomatis dihitung sebagai lembur setelah disetujui</p>
        </div>
    </div>

    {{-- Info lembur otomatis ──────────────────────────────────────────────── --}}
    <div class="mx-6 mt-5 px-4 py-3 bg-orange-50 border border-orange-100 rounded-xl flex items-start gap-2.5">
        <svg class="w-4 h-4 text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-xs text-orange-700">
            Setelah disetujui atasan, sistem akan otomatis membuat <strong>pengajuan lembur</strong> untuk shift kedua.
            Nominal lembur dihitung berdasarkan durasi shift kedua × multiplier × upah per jam.
        </p>
    </div>

    <form action="{{ route('double-shift.store') }}" method="POST" class="p-6 space-y-5">
        @csrf

        {{-- Pegawai ──────────────────────────────────────────────────────────── --}}
        @php $singlePegawai = $pegawai->count() === 1 ? $pegawai->first() : null; @endphp
        @if($singlePegawai)
        <input type="hidden" name="pegawai_id" value="{{ $singlePegawai->id }}">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Pegawai</label>
            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                @php $initials = collect(explode(' ', $singlePegawai->nama))->take(2)->map(fn($w)=>strtoupper($w[0]))->join(''); @endphp
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-white">{{ $initials }}</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $singlePegawai->nama }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $singlePegawai->nik }}</p>
                </div>
                <span class="ml-auto text-xs text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full border border-orange-100 font-medium">Anda</span>
            </div>
        </div>
        @else
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Pegawai <span class="text-red-500">*</span></label>
            <select name="pegawai_id" required
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300 @error('pegawai_id') border-red-400 @enderror">
                <option value="">— Pilih Pegawai —</option>
                @foreach($pegawai as $p)
                <option value="{{ $p->id }}" {{ old('pegawai_id') == $p->id ? 'selected' : '' }}>
                    {{ $p->nik }} — {{ $p->nama }}
                </option>
                @endforeach
            </select>
            @error('pegawai_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        @endif

        {{-- Tanggal ──────────────────────────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" required
                   min="{{ today()->format('Y-m-d') }}"
                   value="{{ old('tanggal') }}"
                   class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 @error('tanggal') border-red-400 @enderror">
            @error('tanggal')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Shift Pertama + Kedua ────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">
                    Shift Pertama <span class="text-red-500">*</span>
                    <span class="ml-1 font-normal text-gray-400">(shift sesuai jadwal)</span>
                </label>
                <select name="shift_pertama_kode" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300 @error('shift_pertama_kode') border-red-400 @enderror">
                    <option value="">— Pilih Shift —</option>
                    @foreach($shiftList as $s)
                    <option value="{{ $s->kode }}" {{ old('shift_pertama_kode') == $s->kode ? 'selected' : '' }}>
                        {{ $s->nama }} ({{ $s->jam_label }})
                    </option>
                    @endforeach
                </select>
                @error('shift_pertama_kode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">
                    Shift Kedua <span class="text-red-500">*</span>
                    <span class="ml-1 font-normal text-orange-500">(dihitung lembur)</span>
                </label>
                <select name="shift_kedua_kode" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300 @error('shift_kedua_kode') border-red-400 @enderror">
                    <option value="">— Pilih Shift —</option>
                    @foreach($shiftList as $s)
                    <option value="{{ $s->kode }}" {{ old('shift_kedua_kode') == $s->kode ? 'selected' : '' }}>
                        {{ $s->nama }} ({{ $s->jam_label }}) — ×{{ $s->multiplier_lembur }}
                    </option>
                    @endforeach
                </select>
                @error('shift_kedua_kode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Alasan ───────────────────────────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Alasan / Keperluan <span class="text-red-500">*</span></label>
            <textarea name="alasan" rows="3" required maxlength="500"
                      placeholder="Jelaskan mengapa perlu ambil double shift..."
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300 resize-none @error('alasan') border-red-400 @enderror">{{ old('alasan') }}</textarea>
            @error('alasan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Alur approval ────────────────────────────────────────────────────── --}}
        @if($setting->wajib_approval_double_shift)
        <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl">
            <div class="flex items-center gap-2 text-xs text-amber-700 flex-wrap">
                <span class="px-2 py-0.5 bg-white rounded-full border border-amber-200">Anda ajukan</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="px-2 py-0.5 bg-white rounded-full border border-amber-200">Atasan setujui</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded-full border border-orange-200 font-medium">Lembur otomatis dibuat</span>
            </div>
        </div>
        @endif

        {{-- Actions ─────────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
            <button type="submit"
                    class="px-5 py-2.5 text-sm bg-orange-600 text-white rounded-xl hover:bg-orange-700 transition-colors font-semibold">
                Ajukan Double Shift
            </button>
            <a href="{{ route('double-shift.index') }}"
               class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
</div>

@endsection
