@extends('layouts.app')
@section('title', $karyawan->nama)
@section('page-title', 'Profil Karyawan')
@section('page-subtitle', $karyawan->nama . ' — ' . ($karyawan->jbtn ?? '-'))

@section('content')

{{-- Breadcrumb --}}
<div class="mb-5 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('karyawan.index') }}" class="hover:text-blue-600 transition">Master Karyawan</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-700 font-medium">{{ $karyawan->nama }}</span>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    {{ session('success') }}
</div>
@endif

<div x-data="{ tab: 'info' }">

{{-- ── Profile Header ────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-5">
    <div class="flex flex-col sm:flex-row gap-5 items-start">
        {{-- Foto --}}
        <div class="flex-shrink-0">
            <img src="{{ $karyawan->foto_url }}"
                 class="w-24 h-24 rounded-2xl object-cover border-4 border-gray-100 shadow"
                 onerror="this.src='{{ asset('images/avatar-default.png') }}'">
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-start gap-2 mb-1">
                <h2 class="text-xl font-bold text-gray-900">{{ $karyawan->nama }}</h2>
                @if($karyawan->stts_aktif === 'AKTIF')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-600">Non Aktif</span>
                @endif
                @if($karyawan->stts_kerja)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-600">{{ $karyawan->stts_kerja }}</span>
                @endif
            </div>
            <p class="text-gray-600 text-sm">{{ $karyawan->jbtn ?? '-' }}</p>
            <p class="text-gray-400 text-sm">{{ $karyawan->departemenRef?->nama ?? '-' }}</p>

            <div class="flex flex-wrap gap-4 mt-3 text-sm text-gray-500">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                    NIK: {{ $karyawan->nik }}
                </span>
                @if($karyawan->mulai_kerja)
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $karyawan->masa_kerja }}
                </span>
                @endif
                @if($karyawan->tgl_lahir)
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $karyawan->usia }} tahun
                </span>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-2 flex-shrink-0">
            <a href="{{ route('karyawan.edit', $karyawan) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Data
            </a>
            <a href="{{ route('karyawan.berkas.index', $karyawan) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Dokumen
                @if($karyawan->berkas->count() > 0)
                <span class="bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $karyawan->berkas->count() }}</span>
                @endif
            </a>
        </div>
    </div>

    {{-- ── Tabs ──────────────────────────────────────────────────────────────── --}}
    <div class="mt-5 -mb-6 border-t border-gray-100 pt-4">
        <div class="flex gap-1">
            @foreach([['info','Info Pribadi'],['kepegawaian','Kepegawaian'],['rekap','Rekap Absensi']] as [$key,$label])
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'bg-blue-50 text-blue-700 font-semibold'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                    class="px-4 py-2 text-sm rounded-xl transition">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Tab: Info Pribadi ────────────────────────────────────────────────────── --}}
<div x-show="tab === 'info'" x-transition>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Data Pribadi</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            @php
            $fields = [
                ['label' => 'NIK',              'value' => $karyawan->nik],
                ['label' => 'Nama Lengkap',     'value' => $karyawan->nama],
                ['label' => 'Jenis Kelamin',    'value' => $karyawan->jk],
                ['label' => 'Tempat Lahir',     'value' => $karyawan->tmp_lahir ?? '-'],
                ['label' => 'Tanggal Lahir',    'value' => $karyawan->tgl_lahir?->translatedFormat('d F Y') ?? '-'],
                ['label' => 'Usia',             'value' => ($karyawan->tgl_lahir ? $karyawan->usia . ' tahun' : '-')],
                ['label' => 'No. KTP',          'value' => $karyawan->no_ktp ?? '-'],
                ['label' => 'NPWP',             'value' => $karyawan->npwp ?? '-'],
                ['label' => 'Alamat',           'value' => $karyawan->alamat ?? '-'],
            ];
            @endphp
            @foreach($fields as $f)
            <div class="py-2 border-b border-gray-50">
                <dt class="text-xs text-gray-400 uppercase tracking-wide">{{ $f['label'] }}</dt>
                <dd class="text-sm text-gray-800 font-medium mt-0.5">{{ $f['value'] ?? '-' }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
</div>

{{-- ── Tab: Kepegawaian ─────────────────────────────────────────────────────── --}}
<div x-show="tab === 'kepegawaian'" x-transition>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Data Kepegawaian</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            @php
            $fields2 = [
                ['label' => 'Jabatan',           'value' => $karyawan->jbtn ?? '-'],
                ['label' => 'Departemen',         'value' => $karyawan->departemenRef?->nama ?? '-'],
                ['label' => 'Pendidikan',         'value' => $karyawan->pendidikan ?? '-'],
                ['label' => 'Status Kerja',       'value' => $karyawan->stts_kerja ?? '-'],
                ['label' => 'Status Aktif',       'value' => $karyawan->stts_aktif],
                ['label' => 'Mulai Kerja',        'value' => $karyawan->mulai_kerja?->translatedFormat('d F Y') ?? '-'],
                ['label' => 'Masa Kerja',         'value' => $karyawan->mulai_kerja ? $karyawan->masa_kerja : '-'],
                ['label' => 'Gaji Pokok',         'value' => 'Rp ' . number_format($karyawan->gapok ?? 0, 0, ',', '.')],
                ['label' => 'Hari Wajib Masuk',   'value' => ($karyawan->wajibmasuk ?? '-') . ' hari/bulan'],
            ];
            @endphp
            @foreach($fields2 as $f)
            <div class="py-2 border-b border-gray-50">
                <dt class="text-xs text-gray-400 uppercase tracking-wide">{{ $f['label'] }}</dt>
                <dd class="text-sm text-gray-800 font-medium mt-0.5">{{ $f['value'] ?? '-' }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
</div>

{{-- ── Tab: Rekap Absensi ───────────────────────────────────────────────────── --}}
<div x-show="tab === 'rekap'" x-transition>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Rekap Absensi (3 Bulan Terakhir)</h3>
            <a href="{{ route('absensi.show', $karyawan) }}"
               class="text-xs text-blue-600 hover:underline">Lihat semua &rarr;</a>
        </div>

        @forelse($karyawan->rekapAbsensi as $rekap)
        <div class="flex flex-wrap items-center gap-4 p-4 bg-gray-50 rounded-xl mb-3">
            <div class="text-sm font-semibold text-gray-700 w-24">
                {{ \Carbon\Carbon::create($rekap->tahun, $rekap->bulan)->translatedFormat('F Y') }}
            </div>
            <div class="flex flex-wrap gap-3 text-sm">
                <span class="flex items-center gap-1 text-green-600">
                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                    Hadir: <strong>{{ $rekap->total_hadir ?? $rekap->hadir ?? 0 }}</strong>
                </span>
                <span class="flex items-center gap-1 text-red-500">
                    <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>
                    Alfa: <strong>{{ $rekap->total_alfa ?? $rekap->alfa ?? 0 }}</strong>
                </span>
                <span class="flex items-center gap-1 text-yellow-600">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span>
                    Izin/Sakit: <strong>{{ ($rekap->total_izin ?? $rekap->izin ?? 0) + ($rekap->total_sakit ?? $rekap->sakit ?? 0) }}</strong>
                </span>
                <span class="flex items-center gap-1 text-orange-500">
                    <span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span>
                    Terlambat: <strong>{{ $rekap->total_terlambat ?? $rekap->terlambat ?? 0 }}x</strong>
                </span>
            </div>
        </div>
        @empty
        <p class="text-sm text-gray-400 text-center py-6">Belum ada rekap absensi.</p>
        @endforelse

        {{-- Cuti Terbaru --}}
        @if($karyawan->pengajuanCuti->count() > 0)
        <h3 class="text-sm font-semibold text-gray-700 mt-6 mb-3">Pengajuan Cuti Terbaru</h3>
        <div class="space-y-2">
            @foreach($karyawan->pengajuanCuti as $cuti)
            <div class="flex items-center gap-3 text-sm py-2 border-b border-gray-50">
                <span class="text-gray-500">{{ \Carbon\Carbon::parse($cuti->tanggal)->format('d M Y') }}</span>
                <span class="flex-1 text-gray-700">{{ $cuti->keterangan ?? $cuti->alasan ?? '-' }}</span>
                @php $st = $cuti->status ?? $cuti->stts ?? '-'; @endphp
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                    {{ in_array($st, ['Disetujui','disetujui','approved']) ? 'bg-green-100 text-green-700' : (in_array($st, ['Ditolak','ditolak','rejected']) ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ ucfirst($st) }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

</div>{{-- /x-data --}}
@endsection
