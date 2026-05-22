@extends('layouts.app')
@section('title', 'Master Jenis Ijin Khusus')
@section('page-title', 'Master Jenis Ijin Khusus')
@section('page-subtitle', 'Kelola jenis-jenis ijin khusus yang tersedia')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

{{-- Flash ───────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    <p class="font-semibold mb-1">Terdapat kesalahan:</p>
    <ul class="list-disc list-inside space-y-0.5 text-xs">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
    </ul>
</div>
@endif

<div x-data="{ showAdd: {{ $errors->any() ? 'true' : 'false' }} }">

    {{-- Toolbar ─────────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <p class="text-sm font-semibold text-gray-700">Daftar Jenis Ijin</p>
            <span class="px-2.5 py-0.5 text-xs rounded-full bg-purple-50 text-purple-700 border border-purple-100 font-semibold">
                {{ $list->count() }} jenis
            </span>
        </div>
        <button @click="showAdd = !showAdd"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jenis
        </button>
    </div>

    {{-- Form Tambah (collapsible) ──────────────────────────────────────────── --}}
    <div x-show="showAdd" x-cloak x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mb-5">
        <div class="bg-white rounded-2xl border border-purple-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-purple-50 border-b border-purple-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <h3 class="text-sm font-semibold text-purple-800">Tambah Jenis Ijin Baru</h3>
            </div>
            <form action="{{ route('ijin-khusus.master.store') }}" method="POST" class="p-5">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kode <span class="text-red-500">*</span></label>
                        <input type="text" name="kode" required maxlength="30"
                               value="{{ old('kode') }}"
                               placeholder="mis. NIKAH"
                               oninput="this.value=this.value.toUpperCase()"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-purple-300 @error('kode') border-red-400 @enderror">
                        @error('kode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Jenis <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" required
                               value="{{ old('nama') }}"
                               placeholder="Nama jenis ijin"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300 @error('nama') border-red-400 @enderror">
                        @error('nama')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Maks. Hari</label>
                        <input type="number" name="max_hari" min="1" max="365"
                               value="{{ old('max_hari') }}"
                               placeholder="Kosong = ∞"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Keterangan <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <input type="text" name="keterangan"
                               value="{{ old('keterangan') }}"
                               placeholder="Deskripsi singkat..."
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Urutan</label>
                        <input type="number" name="urutan" min="0"
                               value="{{ old('urutan', 0) }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-300">
                    </div>
                </div>

                <div class="flex flex-wrap gap-5 mb-5">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="wajib_lampiran" value="1"
                                   {{ old('wajib_lampiran') ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-purple-500 transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                        </div>
                        <span class="text-sm text-gray-700">Wajib Lampiran</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="butuh_waktu" value="1"
                                   {{ old('butuh_waktu') ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-purple-500 transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                        </div>
                        <span class="text-sm text-gray-700">Gunakan Jam (bukan seharian)</span>
                    </label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit"
                            class="px-5 py-2.5 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-semibold">
                        Simpan Jenis Baru
                    </button>
                    <button type="button" @click="showAdd = false"
                            class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Info --}}
    <div class="mb-4 flex items-center gap-2.5 px-4 py-2.5 bg-amber-50 border border-amber-100 rounded-xl text-xs text-amber-700">
        <svg class="w-4 h-4 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Kode tidak dapat diubah setelah digunakan. Nonaktifkan jenis yang sudah tidak dipakai.
    </div>

    {{-- Tabel ───────────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        @if($list->isEmpty())
        <div class="py-16 text-center">
            <div class="w-12 h-12 bg-purple-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <p class="text-sm text-gray-500">Belum ada jenis ijin khusus.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/70">
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider w-12">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama Jenis</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Maks.</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Lampiran</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pakai Jam</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($list as $j)
                    <tr class="hover:bg-gray-50/50 transition-colors" x-data="{ editing: false }">

                        {{-- ── View Mode ─────────────────────────────────────── --}}
                        <td class="px-4 py-3.5 text-center text-xs text-gray-400 font-mono" x-show="!editing">{{ $j->urutan }}</td>
                        <td class="px-4 py-3.5" x-show="!editing">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-semibold rounded-lg bg-purple-50 text-purple-700 border border-purple-100">
                                {{ $j->kode }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5" x-show="!editing">
                            <p class="font-medium text-gray-800 text-sm">{{ $j->nama }}</p>
                            @if($j->keterangan)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $j->keterangan }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center text-xs text-gray-600 font-semibold" x-show="!editing">
                            @if($j->max_hari)
                            <span class="px-2 py-0.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">{{ $j->max_hari }} hr</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center" x-show="!editing">
                            @if($j->wajib_lampiran)
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-green-100 rounded-full">
                                <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            @else
                            <span class="text-gray-200 text-lg">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center" x-show="!editing">
                            @if($j->butuh_waktu)
                            <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full">
                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            @else
                            <span class="text-gray-200 text-lg">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-center" x-show="!editing">
                            @if($j->aktif)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-full font-medium bg-green-50 text-green-700 border border-green-100">
                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs rounded-full font-medium bg-gray-50 text-gray-500 border border-gray-100">
                                <span class="w-1.5 h-1.5 bg-gray-300 rounded-full"></span>Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5" x-show="!editing">
                            <div class="flex items-center justify-center gap-1.5">
                                <button @click="editing = true"
                                        class="px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                    Edit
                                </button>
                                <form action="{{ route('ijin-khusus.master.toggle', $j) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors
                                            {{ $j->aktif ? 'text-orange-600 bg-orange-50 hover:bg-orange-100' : 'text-green-600 bg-green-50 hover:bg-green-100' }}">
                                        {{ $j->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </div>
                        </td>

                        {{-- ── Edit Mode (inline colspan) ───────────────────── --}}
                        <td colspan="8" class="px-4 py-4 bg-blue-50/30" x-show="editing" x-cloak>
                            <form action="{{ route('ijin-khusus.master.update', $j) }}" method="POST">
                                @csrf @method('PUT')
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Kode</label>
                                        <p class="px-2.5 py-2 text-xs font-mono font-bold text-purple-700 bg-white rounded-lg border border-purple-100">{{ $j->kode }}</p>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs text-gray-500 mb-1">Nama <span class="text-red-400">*</span></label>
                                        <input type="text" name="nama" required value="{{ $j->nama }}"
                                               class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Maks. Hari</label>
                                        <input type="number" name="max_hari" min="1" value="{{ $j->max_hari }}" placeholder="∞"
                                               class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label class="block text-xs text-gray-500 mb-1">Keterangan</label>
                                        <input type="text" name="keterangan" value="{{ $j->keterangan }}" placeholder="Opsional"
                                               class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Urutan</label>
                                        <input type="number" name="urutan" min="0" value="{{ $j->urutan }}"
                                               class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-5 mb-4">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 select-none">
                                        <input type="checkbox" name="wajib_lampiran" value="1"
                                               {{ $j->wajib_lampiran ? 'checked' : '' }}
                                               class="w-4 h-4 rounded text-purple-600 border-gray-300 focus:ring-purple-300">
                                        Wajib Lampiran
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 select-none">
                                        <input type="checkbox" name="butuh_waktu" value="1"
                                               {{ $j->butuh_waktu ? 'checked' : '' }}
                                               class="w-4 h-4 rounded text-purple-600 border-gray-300 focus:ring-purple-300">
                                        Gunakan Jam
                                    </label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="submit"
                                            class="px-4 py-2 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                        Simpan
                                    </button>
                                    <button type="button" @click="editing = false"
                                            class="px-4 py-2 text-xs bg-white text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

@endsection
