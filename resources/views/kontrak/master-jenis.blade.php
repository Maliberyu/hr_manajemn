@extends('layouts.app')
@section('title', 'Master Jenis Kontrak')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('kontrak.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Master Jenis Kontrak</h1>
            <p class="text-sm text-gray-500">Kelola jenis-jenis kontrak kerja</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Form Tambah --}}
    <form method="POST" action="{{ route('kontrak.master-jenis.store') }}"
          class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm space-y-4">
        @csrf
        <h3 class="text-sm font-semibold text-gray-700">Tambah Jenis Baru</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="50"
                       placeholder="cth: PKWT, Magang..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Durasi Default (bulan)</label>
                <input type="number" name="durasi_default_bulan" value="{{ old('durasi_default_bulan') }}"
                       min="1" max="120" placeholder="Kosongkan jika tidak tentu"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" name="is_tetap" value="1" {{ old('is_tetap') ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-blue-600">
                Karyawan Tetap (PKWTT — tidak ada tgl selesai)
            </label>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Keterangan</label>
            <input type="text" name="keterangan" value="{{ old('keterangan') }}" maxlength="200"
                   class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex justify-end">
            <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                Tambahkan
            </button>
        </div>
    </form>

    {{-- List --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Nama</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Durasi</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tipe</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Digunakan</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($jenisList as $j)
                <tr x-data="{ edit: false }" class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span x-show="!edit" class="font-medium text-gray-800">{{ $j->nama }}</span>
                        <form x-show="edit" method="POST" action="{{ route('kontrak.master-jenis.update', $j) }}" class="flex gap-2 items-center">
                            @csrf @method('PUT')
                            <input type="text" name="nama" value="{{ $j->nama }}" required
                                   class="px-2 py-1 border border-gray-300 rounded-lg text-sm w-28 focus:outline-none focus:ring-1 focus:ring-blue-400">
                            <input type="number" name="durasi_default_bulan" value="{{ $j->durasi_default_bulan }}"
                                   min="1" max="120" placeholder="bln"
                                   class="px-2 py-1 border border-gray-300 rounded-lg text-sm w-16 focus:outline-none focus:ring-1 focus:ring-blue-400">
                            <input type="text" name="keterangan" value="{{ $j->keterangan }}"
                                   placeholder="keterangan"
                                   class="px-2 py-1 border border-gray-300 rounded-lg text-sm w-36 focus:outline-none focus:ring-1 focus:ring-blue-400">
                            <input type="hidden" name="is_tetap" value="{{ $j->is_tetap ? 1 : 0 }}">
                            <button type="submit" class="text-xs text-blue-600 hover:underline font-medium">Simpan</button>
                            <button type="button" @click="edit=false" class="text-xs text-gray-400 hover:text-gray-600">Batal</button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-gray-500">
                        <span x-show="!edit">{{ $j->durasi_default_bulan ? $j->durasi_default_bulan.' bln' : '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span x-show="!edit" class="{{ $j->is_tetap ? 'text-green-600' : 'text-blue-600' }} text-xs font-medium">
                            {{ $j->is_tetap ? 'Tetap' : 'Kontrak' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $j->kontraks_count }}x</td>
                    <td class="px-4 py-3 text-right" x-show="!edit">
                        <button @click="edit=true" class="text-xs text-gray-500 hover:text-blue-600 mr-3">Edit</button>
                        @if($j->kontraks_count == 0)
                        <form method="POST" action="{{ route('kontrak.master-jenis.destroy', $j) }}"
                              class="inline" onsubmit="return confirm('Hapus jenis ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
