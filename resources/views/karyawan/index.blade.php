@extends('layouts.app')
@section('title', 'Master Karyawan')
@section('page-title', 'Master Karyawan')
@section('page-subtitle', 'Daftar seluruh pegawai aktif & non-aktif')

@section('content')

{{-- ── Header ────────────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Daftar Karyawan</h2>
        <p class="text-sm text-gray-500">Total {{ $pegawai->total() }} karyawan ditemukan</p>
    </div>
    <a href="{{ route('karyawan.create') }}"
        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl shadow-sm transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Karyawan
    </a>
</div>

{{-- ── Flash message ─────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
    </svg>
    {{ session('success') }}
</div>
@endif

{{-- ── Filter ─────────────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('karyawan.index') }}"
    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Search --}}
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Nama / NIK / Jabatan..."
                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        {{-- Departemen --}}
        <select name="departemen"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
            <option value="">Semua Departemen</option>
            @foreach($departemen as $id => $nama)
            <option value="{{ $id }}" {{ request('departemen') == $id ? 'selected' : '' }}>{{ $nama }}</option>
            @endforeach
        </select>
        {{-- Status --}}
        <select name="status"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
            <option value="">Semua Status</option>
            <option value="AKTIF" {{ request('status') === 'AKTIF' ? 'selected' : '' }}>Aktif</option>
            <option value="NON AKTIF" {{ request('status') === 'NON AKTIF' ? 'selected' : '' }}>Non Aktif</option>
        </select>
        {{-- Actions --}}
        <div class="flex gap-2">
            <button type="submit"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">
                Filter
            </button>
            <a href="{{ route('karyawan.index') }}"
                class="flex items-center justify-center px-3 py-2 text-sm border border-gray-200 rounded-xl text-gray-500 hover:bg-gray-50 transition">
                Reset
            </a>
        </div>
    </div>
</form>

{{-- ── Tabel ──────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Karyawan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Jabatan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Departemen</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Mulai Kerja</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($pegawai as $p)
                <tr class="hover:bg-gray-50/60 transition">
                    {{-- Karyawan --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $p->foto_url }}"
                                class="w-10 h-10 rounded-full object-cover flex-shrink-0 border-2 border-gray-100"
                                onerror="this.src='{{ asset('images/avatar-default.png') }}'">
                            <div>
                                <p class="font-semibold text-gray-800 leading-tight">{{ $p->nama }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">NIK {{ $p->nik }}</p>
                            </div>
                        </div>
                    </td>
                    {{-- Jabatan --}}
                    <td class="px-4 py-3 hidden md:table-cell">
                        <p class="text-gray-700">{{ $p->jbtn ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $p->stts_kerja ?? '' }}</p>
                    </td>
                    {{-- Departemen --}}
                    <td class="px-4 py-3 hidden lg:table-cell">
                        @if($p->departemenRef)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                            {{ $p->departemenRef->nama }}
                        </span>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    {{-- Mulai Kerja --}}
                    <td class="px-4 py-3 hidden lg:table-cell">

                        @php
                        $start = \Carbon\Carbon::parse($p->mulai_kerja);
                        $now = \Carbon\Carbon::now();
                        $diff = $start->diff($now);
                        @endphp

                        <p class="text-gray-700">
                            {{ $start->format('d M Y') }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $diff->y }} th {{ $diff->m }} bln
                        </p>

                        <!-- @if($p->mulai_kerja)
                            <p class="text-gray-700">{{ $p->mulai_kerja->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $p->masa_kerja }}</p>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif -->
                    </td>
                    {{-- Status --}}
                    <td class="px-4 py-3 text-center">
                        @if($p->stts_aktif === 'AKTIF')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">Non Aktif</span>
                        @endif
                    </td>
                    {{-- Aksi --}}
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1" x-data="{ open: false }">
                            <a href="{{ route('karyawan.show', $p) }}"
                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat profil">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c0 1.657-3.134 5-9 5S3 13.657 3 12 6.134 7 12 7s9 3.343 9 5z" />
                                </svg>
                            </a>
                            <a href="{{ route('karyawan.edit', $p) }}"
                                class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded-lg transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <a href="{{ route('karyawan.berkas.index', $p) }}"
                                class="p-1.5 text-purple-600 hover:bg-purple-50 rounded-lg transition" title="Dokumen">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center gap-3 text-gray-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <p class="font-medium">Tidak ada karyawan ditemukan</p>
                            <a href="{{ route('karyawan.index') }}" class="text-blue-500 hover:underline text-sm">Reset filter</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pegawai->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $pegawai->links() }}
    </div>
    @endif
</div>

@endsection