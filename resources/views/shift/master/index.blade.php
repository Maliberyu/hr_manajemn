@extends('layouts.app')
@section('title', 'Master Shift & Setting')
@section('page-title', 'Master Shift')
@section('page-subtitle', 'Kelola definisi shift dan pengaturan global')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
    <p class="font-semibold mb-1">Terdapat kesalahan:</p>
    <ul class="list-disc list-inside text-xs space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5" x-data="{ showAdd: {{ $errors->any() ? 'true' : 'false' }} }">

    {{-- ── Kolom Kiri: Daftar Shift ──────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-4">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <p class="text-sm font-semibold text-gray-700">Daftar Shift</p>
                <span class="px-2.5 py-0.5 text-xs rounded-full bg-blue-50 text-blue-700 border border-blue-100 font-semibold">
                    {{ $list->count() }} shift
                </span>
            </div>
            <button @click="showAdd = !showAdd"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Shift
            </button>
        </div>

        {{-- Form Tambah --}}
        <div x-show="showAdd" x-cloak x-transition class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <h3 class="text-sm font-semibold text-blue-800">Tambah Shift Baru</h3>
            </div>
            <form action="{{ route('shift.master.store') }}" method="POST" class="p-5">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kode <span class="text-red-500">*</span></label>
                        <input type="text" name="kode" required maxlength="30"
                               value="{{ old('kode') }}" placeholder="mis. pagi_2"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-300 @error('kode') border-red-400 @enderror">
                        @error('kode')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" required value="{{ old('nama') }}" placeholder="Nama shift"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 @error('nama') border-red-400 @enderror">
                        @error('nama')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_mulai" required value="{{ old('jam_mulai') }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_selesai" required value="{{ old('jam_selesai') }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Multiplier Lembur <span class="text-red-500">*</span></label>
                        <input type="number" name="multiplier_lembur" required step="0.5" min="0.5" max="5"
                               value="{{ old('multiplier_lembur', 1.0) }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <p class="text-xs text-gray-400 mt-0.5">1.0 = normal, 1.5 = malam, 2.0 = libur</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Urutan</label>
                        <input type="number" name="urutan" min="0" value="{{ old('urutan', 0) }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" name="melewati_tengah_malam" value="1"
                                       {{ old('melewati_tengah_malam') ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                            </div>
                            <span class="text-xs text-gray-700 font-semibold">Melewati tengah malam</span>
                        </label>
                    </div>
                </div>
                <div class="flex items-center gap-3 pt-3 border-t border-gray-100">
                    <button type="submit" class="px-5 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-semibold">Simpan</button>
                    <button type="button" @click="showAdd=false" class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200">Batal</button>
                </div>
            </form>
        </div>

        {{-- Tabel shift --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70">
                            <th class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider w-10">#</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kode</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jam</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Durasi</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Multiplier</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($list as $s)
                        <tr class="hover:bg-gray-50/50" x-data="{ edit: false }">
                            <td class="px-3 py-3.5 text-center text-xs text-gray-400" x-show="!edit">{{ $s->urutan }}</td>
                            <td class="px-4 py-3.5" x-show="!edit">
                                <span class="inline-flex px-2.5 py-1 text-xs font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-100 rounded-lg">
                                    {{ $s->kode }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-gray-800" x-show="!edit">{{ $s->nama }}</td>
                            <td class="px-4 py-3.5 text-center text-xs text-gray-600 whitespace-nowrap" x-show="!edit">
                                {{ $s->jam_label }}
                                @if($s->melewati_tengah_malam)
                                <span class="ml-1 text-purple-500" title="Melewati tengah malam">*</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-center text-xs font-semibold text-gray-600" x-show="!edit">
                                {{ $s->durasi_jam }} jam
                            </td>
                            <td class="px-4 py-3.5 text-center" x-show="!edit">
                                @php
                                    $mc = match((string)$s->multiplier_lembur) {
                                        '1'   => 'bg-gray-100 text-gray-600',
                                        '1.5' => 'bg-blue-100 text-blue-700',
                                        '2'   => 'bg-orange-100 text-orange-700',
                                        default => 'bg-purple-100 text-purple-700',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $mc }}">
                                    {{ $s->multiplier_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center" x-show="!edit">
                                @if($s->aktif)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-green-50 text-green-700 border border-green-100 font-medium">
                                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>Aktif
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-gray-50 text-gray-500 border border-gray-100 font-medium">
                                    <span class="w-1.5 h-1.5 bg-gray-300 rounded-full"></span>Nonaktif
                                </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5" x-show="!edit">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button @click="edit=true"
                                            class="px-3 py-1.5 text-xs font-semibold text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                        Edit
                                    </button>
                                    <form action="{{ route('shift.master.toggle', $s) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $s->aktif ? 'text-orange-600 bg-orange-50 hover:bg-orange-100' : 'text-green-600 bg-green-50 hover:bg-green-100' }}">
                                            {{ $s->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </td>

                            {{-- Edit mode --}}
                            <td colspan="8" class="px-5 py-4 bg-blue-50/30" x-show="edit" x-cloak>
                                <form action="{{ route('shift.master.update', $s) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Kode</label>
                                            <p class="px-2.5 py-2 text-xs font-mono font-bold text-blue-700 bg-white rounded-lg border border-blue-100">{{ $s->kode }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Nama <span class="text-red-400">*</span></label>
                                            <input type="text" name="nama" required value="{{ $s->nama }}"
                                                   class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Jam Mulai</label>
                                            <input type="time" name="jam_mulai" required value="{{ substr($s->jam_mulai,0,5) }}"
                                                   class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Jam Selesai</label>
                                            <input type="time" name="jam_selesai" required value="{{ substr($s->jam_selesai,0,5) }}"
                                                   class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Multiplier</label>
                                            <input type="number" name="multiplier_lembur" step="0.5" min="0.5" max="5"
                                                   value="{{ $s->multiplier_lembur }}"
                                                   class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Urutan</label>
                                            <input type="number" name="urutan" min="0" value="{{ $s->urutan }}"
                                                   class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                                        </div>
                                        <div class="flex items-end pb-1 col-span-2">
                                            <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-700 select-none">
                                                <input type="checkbox" name="melewati_tengah_malam" value="1"
                                                       {{ $s->melewati_tengah_malam ? 'checked' : '' }}
                                                       class="w-4 h-4 rounded text-blue-600 border-gray-300 focus:ring-blue-300">
                                                Melewati tengah malam
                                            </label>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="px-4 py-2 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Simpan</button>
                                        <button type="button" @click="edit=false" class="px-4 py-2 text-xs bg-white border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50">Batal</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada shift.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Kolom Kanan: Setting ──────────────────────────────────────────────── --}}
    <div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <h3 class="text-sm font-semibold text-gray-700">Setting Global</h3>
            </div>
            <form action="{{ route('shift.master.setting') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Toleransi Mismatch Absensi</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="toleransi_mismatch_menit" min="0" max="120"
                               value="{{ $setting->toleransi_mismatch_menit }}"
                               class="w-20 border border-gray-200 rounded-xl px-3 py-2 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <span class="text-sm text-gray-500">menit</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Selisih jam masuk dari shift rencana sebelum dianggap mismatch</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Maks. Tukar Shift / Bulan</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="max_tukar_shift_per_bulan" min="1" max="10"
                               value="{{ $setting->max_tukar_shift_per_bulan }}"
                               class="w-20 border border-gray-200 rounded-xl px-3 py-2 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <span class="text-sm text-gray-500">kali per karyawan</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="wajib_approval_double_shift" value="1"
                                   {{ $setting->wajib_approval_double_shift ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-700 font-semibold">Double shift butuh approval</p>
                            <p class="text-xs text-gray-400">Atasan harus menyetujui sebelum lembur dibuat</p>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="notif_mismatch_ke_atasan" value="1"
                                   {{ $setting->notif_mismatch_ke_atasan ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                        </div>
                        <div>
                            <p class="text-sm text-gray-700 font-semibold">Notif mismatch ke atasan</p>
                            <p class="text-xs text-gray-400">Atasan dinotifikasi jika absensi tidak sesuai shift</p>
                        </div>
                    </label>
                </div>

                <button type="submit"
                        class="w-full px-4 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-semibold transition-colors">
                    Simpan Setting
                </button>
            </form>
        </div>
    </div>

</div>

@endsection
