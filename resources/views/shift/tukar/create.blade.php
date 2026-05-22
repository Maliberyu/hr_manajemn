@extends('layouts.app')
@section('title', 'Ajukan Tukar Shift')
@section('page-title', 'Ajukan Tukar Shift')
@section('page-subtitle', 'Minta tukar shift dengan rekan kerja')

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
        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        </div>
        <div>
            <h2 class="text-sm font-semibold text-gray-800">Formulir Tukar Shift</h2>
            <p class="text-xs text-gray-400 mt-0.5">Pilih rekan dan tanggal yang ingin ditukar</p>
        </div>
    </div>

    <form action="{{ route('tukar-shift.store') }}" method="POST" class="p-6 space-y-5">
        @csrf

        {{-- Shift Saya ──────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">Tanggal Shift Saya <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_shift_pemohon" required
                       min="{{ today()->format('Y-m-d') }}"
                       value="{{ old('tgl_shift_pemohon') }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 @error('tgl_shift_pemohon') border-red-400 @enderror">
                @error('tgl_shift_pemohon')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">Shift Saya Hari Itu <span class="text-red-500">*</span></label>
                <select name="shift_pemohon_kode" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-300 @error('shift_pemohon_kode') border-red-400 @enderror">
                    <option value="">— Pilih Shift —</option>
                    @foreach($shiftList as $s)
                    <option value="{{ $s->kode }}" {{ old('shift_pemohon_kode') == $s->kode ? 'selected' : '' }}>
                        {{ $s->nama }} ({{ $s->jam_label }})
                    </option>
                    @endforeach
                </select>
                @error('shift_pemohon_kode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Divider dengan panah --}}
        <div class="flex items-center gap-3">
            <div class="flex-1 border-t border-dashed border-gray-200"></div>
            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 rounded-xl">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span class="text-xs text-gray-500 font-medium">Ditukar dengan</span>
            </div>
            <div class="flex-1 border-t border-dashed border-gray-200"></div>
        </div>

        {{-- Shift Rekan ─────────────────────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Rekan yang Ditukar <span class="text-red-500">*</span></label>
            <select name="rekan_id" required
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-300 @error('rekan_id') border-red-400 @enderror">
                <option value="">— Pilih Rekan —</option>
                @foreach($pegawaiList as $p)
                @php $userId = \App\Models\User::where('nik', $p->nik)->value('id'); @endphp
                @if($userId)
                <option value="{{ $userId }}" {{ old('rekan_id') == $userId ? 'selected' : '' }}>
                    {{ $p->nama }} — {{ $p->nik }}
                </option>
                @endif
                @endforeach
            </select>
            @error('rekan_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">Tanggal Shift Rekan <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_shift_rekan" required
                       value="{{ old('tgl_shift_rekan') }}"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 @error('tgl_shift_rekan') border-red-400 @enderror">
                @error('tgl_shift_rekan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-2">Shift Rekan Hari Itu <span class="text-red-500">*</span></label>
                <select name="shift_rekan_kode" required
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-300 @error('shift_rekan_kode') border-red-400 @enderror">
                    <option value="">— Pilih Shift —</option>
                    @foreach($shiftList as $s)
                    <option value="{{ $s->kode }}" {{ old('shift_rekan_kode') == $s->kode ? 'selected' : '' }}>
                        {{ $s->nama }} ({{ $s->jam_label }})
                    </option>
                    @endforeach
                </select>
                @error('shift_rekan_kode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        {{-- Alasan ───────────────────────────────────────────────────────────── --}}
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-2">Alasan Tukar Shift <span class="text-red-500">*</span></label>
            <textarea name="alasan" rows="3" required maxlength="500"
                      placeholder="Jelaskan alasan mengapa perlu tukar shift..."
                      class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 resize-none @error('alasan') border-red-400 @enderror">{{ old('alasan') }}</textarea>
            @error('alasan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        {{-- Alur info ────────────────────────────────────────────────────────── --}}
        <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl">
            <p class="text-xs font-semibold text-blue-700 mb-2">Alur Persetujuan</p>
            <div class="flex items-center gap-2 text-xs text-blue-600 flex-wrap">
                <span class="px-2 py-0.5 bg-white rounded-full border border-blue-200">Anda ajukan</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="px-2 py-0.5 bg-white rounded-full border border-blue-200">Rekan setujui</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="px-2 py-0.5 bg-white rounded-full border border-blue-200">Atasan setujui</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full border border-green-200">Jadwal diperbarui</span>
            </div>
        </div>

        {{-- Actions ─────────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
            <button type="submit"
                    class="px-5 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold">
                Kirim Permintaan
            </button>
            <a href="{{ route('tukar-shift.index') }}"
               class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>
</div>

@endsection
