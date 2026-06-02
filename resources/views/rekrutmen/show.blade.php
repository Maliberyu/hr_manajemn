@extends('layouts.app')
@section('title', 'Detail Lowongan')
@section('page-title', 'Detail Lowongan')
@section('page-subtitle', $rekrutmen->posisi)

@section('content')
<div class="space-y-5">

    {{-- Flash ─────────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Header ─────────────────────────────────────────────────────────────────── --}}
    <div class="flex items-start gap-4">
        <a href="{{ route('rekrutmen.index') }}"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-xl font-bold text-gray-800">{{ $rekrutmen->posisi }}</h1>
                @php
                    $badgeColor = match($rekrutmen->status) {
                        'buka'           => 'green',
                        'proses_seleksi' => 'blue',
                        'tutup'          => 'gray',
                        'dibatalkan'     => 'red',
                        default          => 'gray',
                    };
                @endphp
                <span class="px-2.5 py-0.5 text-xs rounded-full font-semibold text-{{ $badgeColor }}-700 bg-{{ $badgeColor }}-50">
                    {{ ucfirst(str_replace('_', ' ', $rekrutmen->status)) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                {{ $rekrutmen->departemen?->nama ?? '-' }} &middot;
                Dibuka {{ $rekrutmen->tanggal_buka?->translatedFormat('d M Y') }} &mdash;
                Tutup {{ $rekrutmen->tanggal_tutup?->translatedFormat('d M Y') }}
            </p>
        </div>
        <a href="{{ route('rekrutmen.edit', $rekrutmen) }}"
           class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Edit</a>
    </div>

    {{-- Info Card ───────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-3">
            <h2 class="text-sm font-semibold text-gray-700">Informasi Lowongan</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs text-gray-400">Kuota</p>
                    <p class="font-semibold text-gray-800">{{ $rekrutmen->jumlah_dibutuhkan }} orang</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Sudah Diterima</p>
                    <p class="font-semibold text-gray-800">{{ $rekapStatus['diterima'] ?? 0 }} orang</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Total Pelamar</p>
                    <p class="font-semibold text-gray-800">{{ $pelamar->total() }} pelamar</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Dibuat oleh</p>
                    <p class="font-semibold text-gray-800">{{ $rekrutmen->dibuatOleh?->nama ?? '-' }}</p>
                </div>
            </div>

            {{-- Rekap status --}}
            @if($rekapStatus->isNotEmpty())
            <div class="pt-2 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-2">Rekap Status Pelamar</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(\App\Models\Pelamar::STATUS as $s)
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">
                        {{ ucfirst($s) }}: <strong>{{ $rekapStatus[$s] ?? 0 }}</strong>
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl p-5 space-y-3">
            @if($rekrutmen->deskripsi)
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-1">Deskripsi Pekerjaan</h2>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $rekrutmen->deskripsi }}</p>
            </div>
            @endif
            @if($rekrutmen->syarat)
            <div class="{{ $rekrutmen->deskripsi ? 'pt-3 border-t border-gray-100' : '' }}">
                <h2 class="text-sm font-semibold text-gray-700 mb-1">Persyaratan</h2>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $rekrutmen->syarat }}</p>
            </div>
            @endif
            @if(!$rekrutmen->deskripsi && !$rekrutmen->syarat)
            <p class="text-sm text-gray-400 italic">Belum ada deskripsi / persyaratan.</p>
            @endif
        </div>
    </div>

    {{-- Form Tambah Pelamar ──────────────────────────────────────────────────────── --}}
    @if($rekrutmen->status === 'buka')
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Tambah Pelamar</h3>
        </div>
        <form action="{{ route('rekrutmen.pelamar.store', $rekrutmen) }}" method="POST"
              enctype="multipart/form-data" class="px-5 py-4 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="100"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Telepon <span class="text-red-500">*</span></label>
                    <input type="text" name="telepon" value="{{ old('telepon') }}" required maxlength="20"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="100"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                    <select name="pendidikan_terakhir" required
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                        <option value="">-- Pilih --</option>
                        @foreach(['SD','SMP','SMA/SMK','D1','D2','D3','S1','S2','S3'] as $p)
                        <option value="{{ $p }}" {{ old('pendidikan_terakhir') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pengalaman (tahun)</label>
                    <input type="number" name="pengalaman_tahun" value="{{ old('pengalaman_tahun', 0) }}" min="0"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">File CV (PDF) <span class="text-red-500">*</span></label>
                    <input type="file" name="cv_file" accept=".pdf" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    <p class="text-xs text-gray-400 mt-0.5">Maks. 5 MB</p>
                </div>
            </div>
            <div>
                <button type="submit"
                        class="px-5 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition font-semibold">
                    Tambah Pelamar
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Daftar Pelamar ───────────────────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Daftar Pelamar</h3>
            <span class="text-xs text-gray-400">{{ $pelamar->total() }} pelamar</span>
        </div>

        @if($pelamar->isEmpty())
        <div class="flex flex-col items-center gap-2 py-10 text-gray-400">
            <p class="text-sm">Belum ada pelamar.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Nama</th>
                        <th class="px-4 py-3 text-left">Telepon</th>
                        <th class="px-4 py-3 text-left">Pendidikan</th>
                        <th class="px-4 py-3 text-center">Pengalaman</th>
                        <th class="px-4 py-3 text-left">Tgl Apply</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" x-data>
                    @foreach($pelamar as $p)
                    @php
                        $bc = match($p->status) {
                            'diterima'  => 'green',
                            'ditolak'   => 'red',
                            'interview' => 'blue',
                            'offering'  => 'purple',
                            'test'      => 'yellow',
                            default     => 'gray',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $p->nama }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->telepon }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $p->pendidikan_terakhir }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $p->pengalaman_tahun ?? 0 }} th</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $p->tanggal_apply?->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full font-medium text-{{ $bc }}-700 bg-{{ $bc }}-50">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                @if($p->cv_file)
                                <a href="{{ route('rekrutmen.pelamar.cv', [$rekrutmen, $p]) }}"
                                   class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition"
                                   data-no-loading>CV</a>
                                @endif
                                {{-- Update status inline --}}
                                <form action="{{ route('rekrutmen.pelamar.status', [$rekrutmen, $p]) }}" method="POST"
                                      class="flex gap-1">
                                    @csrf @method('PATCH')
                                    <select name="status"
                                            class="text-xs border border-gray-200 rounded-lg px-1.5 py-1 bg-white focus:outline-none">
                                        @foreach(\App\Models\Pelamar::STATUS as $s)
                                        <option value="{{ $s }}" {{ $p->status === $s ? 'selected' : '' }}>
                                            {{ ucfirst($s) }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <button type="submit"
                                            class="px-2 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                        Simpan
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-gray-100">
            {{ $pelamar->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
