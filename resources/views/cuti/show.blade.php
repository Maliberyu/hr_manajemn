@extends('layouts.app')
@section('title', 'Detail Cuti ' . $cuti->no_pengajuan)
@section('page-title', 'Detail Pengajuan Cuti')
@section('page-subtitle', $cuti->no_pengajuan)

@section('content')

@php
    $statusColor = match($cuti->status) {
        'Menunggu Atasan' => 'yellow',
        'Menunggu HRD'   => 'blue',
        'Disetujui'      => 'green',
        default          => 'red',
    };
@endphp

{{-- Flash ─────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Kolom Kiri: Info Pengajuan ──────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Header status --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="font-mono text-sm text-gray-500">{{ $cuti->no_pengajuan }}</p>
                    <h2 class="text-lg font-bold text-gray-800 mt-0.5">{{ $cuti->urgensi }}</h2>
                    <p class="text-xs text-gray-400 mt-1">Diajukan {{ $cuti->tanggal->translatedFormat('d F Y') }}</p>
                </div>
                <span class="text-sm font-semibold text-{{ $statusColor }}-700 bg-{{ $statusColor }}-50 px-3 py-1.5 rounded-full whitespace-nowrap">
                    {{ $cuti->status }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Tanggal Mulai</p>
                    <p class="font-semibold text-gray-700">{{ $cuti->tanggal_awal->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Tanggal Selesai</p>
                    <p class="font-semibold text-gray-700">{{ $cuti->tanggal_akhir->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Jumlah</p>
                    <p class="font-bold text-blue-600 text-base">{{ $cuti->jumlah }} hari kerja</p>
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-xs text-gray-400 mb-0.5">Alamat Selama Cuti</p>
                    <p class="text-gray-700">{{ $cuti->alamat }}</p>
                </div>
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-xs text-gray-400 mb-0.5">Alasan / Kepentingan</p>
                    <p class="text-gray-700">{{ $cuti->kepentingan }}</p>
                </div>
                @if($cuti->penanggungJawab)
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-xs text-gray-400 mb-0.5">Penanggung Jawab</p>
                    <p class="text-gray-700">{{ $cuti->penanggungJawab->nama }} ({{ $cuti->nik_pj }})</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Info Pegawai --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Data Pegawai</h3>
            <div class="flex items-center gap-3">
                <img src="{{ $cuti->pegawai?->foto_url }}"
                     class="w-12 h-12 rounded-xl object-cover border border-gray-100"
                     onerror="this.src='{{ asset('images/avatar-default.png') }}'">
                <div>
                    <p class="font-semibold text-gray-800">{{ $cuti->pegawai?->nama }}</p>
                    <p class="text-xs text-gray-500">{{ $cuti->pegawai?->jbtn }}</p>
                    <p class="text-xs text-gray-400">{{ $cuti->pegawai?->departemenRef?->nama }}</p>
                </div>
                <div class="ml-auto text-right">
                    <p class="text-xs text-gray-400">Sisa Cuti</p>
                    <p class="font-bold text-lg text-gray-800">
                        {{ \App\Models\PengajuanCuti::HAK_CUTI_TAHUNAN - ($cuti->pegawai?->cuti_diambil ?? 0) }}
                        <span class="text-xs font-normal text-gray-500">/ {{ \App\Models\PengajuanCuti::HAK_CUTI_TAHUNAN }} hari</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Kolom Kanan: Alur Persetujuan ──────────────────────────────────── --}}
    <div class="space-y-4"
         x-data="{ showTolakAtasan: false, showTolakHrd: false, showApproveAtasan: false, showApproveHrd: false }">

        {{-- Timeline Approval --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Alur Persetujuan</h3>

            <div class="space-y-0">
                {{-- Step 1: Pengajuan --}}
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="w-0.5 bg-gray-200 flex-1 mt-1 min-h-[2rem]"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm font-semibold text-gray-800">Pengajuan</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $cuti->tanggal->translatedFormat('d M Y') }}</p>
                    </div>
                </div>

                {{-- Step 2: Atasan --}}
                @php
                    $atasanDone   = in_array($cuti->status, ['Menunggu HRD', 'Disetujui', 'Ditolak HRD']);
                    $atasanTolak  = $cuti->status === 'Ditolak Atasan';
                    $atasanPending= $cuti->status === 'Menunggu Atasan';
                @endphp
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        @if($atasanTolak)
                            <div class="w-7 h-7 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        @elseif($atasanDone)
                            <div class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-7 h-7 rounded-full {{ $atasanPending ? 'bg-yellow-400 ring-2 ring-yellow-200' : 'bg-gray-200' }} flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 {{ $atasanPending ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="w-0.5 bg-gray-200 flex-1 mt-1 min-h-[2rem]"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm font-semibold text-gray-800">Atasan Langsung</p>
                        @if($atasanDone)
                            <p class="text-xs text-green-600 mt-0.5">Disetujui · {{ $cuti->approved_atasan_at?->translatedFormat('d M Y, H:i') }}</p>
                        @elseif($atasanTolak)
                            <p class="text-xs text-red-600 mt-0.5">Ditolak</p>
                        @else
                            <p class="text-xs text-yellow-600 mt-0.5">Menunggu persetujuan</p>
                        @endif
                        @if($cuti->catatan_atasan)
                            <p class="text-xs text-gray-500 mt-1 bg-gray-50 px-2 py-1 rounded-lg italic">"{{ $cuti->catatan_atasan }}"</p>
                        @endif
                    </div>
                </div>

                {{-- Step 3: HRD --}}
                @php
                    $hrdDone   = $cuti->status === 'Disetujui';
                    $hrdTolak  = $cuti->status === 'Ditolak HRD';
                    $hrdPending= $cuti->status === 'Menunggu HRD';
                @endphp
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        @if($hrdTolak)
                            <div class="w-7 h-7 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                        @elseif($hrdDone)
                            <div class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        @else
                            <div class="w-7 h-7 rounded-full {{ $hrdPending ? 'bg-blue-400 ring-2 ring-blue-200' : 'bg-gray-200' }} flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 {{ $hrdPending ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="w-0.5 bg-gray-200 flex-1 mt-1 min-h-[2rem]"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm font-semibold text-gray-800">HRD</p>
                        @if($hrdDone)
                            <p class="text-xs text-green-600 mt-0.5">Disetujui · {{ $cuti->approved_hrd_at?->translatedFormat('d M Y, H:i') }}</p>
                        @elseif($hrdTolak)
                            <p class="text-xs text-red-600 mt-0.5">Ditolak</p>
                        @elseif($hrdPending)
                            <p class="text-xs text-blue-600 mt-0.5">Menunggu persetujuan</p>
                        @else
                            <p class="text-xs text-gray-400 mt-0.5">Belum diproses</p>
                        @endif
                        @if($cuti->catatan_hrd)
                            <p class="text-xs text-gray-500 mt-1 bg-gray-50 px-2 py-1 rounded-lg italic">"{{ $cuti->catatan_hrd }}"</p>
                        @endif
                    </div>
                </div>

                {{-- Step 4: Selesai --}}
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-7 h-7 rounded-full {{ $cuti->status === 'Disetujui' ? 'bg-green-500' : 'bg-gray-200' }} flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 {{ $cuti->status === 'Disetujui' ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold {{ $cuti->status === 'Disetujui' ? 'text-green-700' : 'text-gray-400' }}">Selesai</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Tombol Aksi ──────────────────────────────────────────────────── --}}

        {{-- Approve Atasan --}}
        @if($cuti->bisaApproveAtasan())
        <div class="bg-white rounded-2xl border border-yellow-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Tindakan Atasan Langsung</p>

            {{-- Setujui --}}
            <div x-show="!showTolakAtasan">
                <form method="POST" action="{{ route('cuti.approve.atasan', $cuti) }}" x-show="showApproveAtasan || true">
                    @csrf
                    <textarea name="catatan_atasan" rows="2" maxlength="500" placeholder="Catatan (opsional)..."
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400 resize-none mb-2"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                            Setujui → Teruskan ke HRD
                        </button>
                        <button type="button" @click="showTolakAtasan = true"
                                class="px-3 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-xl transition">
                            Tolak
                        </button>
                    </div>
                </form>
            </div>

            {{-- Form Tolak --}}
            <div x-show="showTolakAtasan" x-cloak>
                <form method="POST" action="{{ route('cuti.tolak.atasan', $cuti) }}">
                    @csrf
                    <p class="text-xs text-red-600 font-medium mb-2">Masukkan alasan penolakan:</p>
                    <textarea name="catatan_atasan" rows="3" required maxlength="500"
                              placeholder="Alasan penolakan..."
                              class="w-full px-3 py-2 text-sm border border-red-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-400 resize-none mb-2"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition">
                            Konfirmasi Tolak
                        </button>
                        <button type="button" @click="showTolakAtasan = false"
                                class="px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Approve HRD --}}
        @if($cuti->bisaApproveHrd())
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Tindakan HRD</p>

            <div x-show="!showTolakHrd">
                <form method="POST" action="{{ route('cuti.approve.hrd', $cuti) }}">
                    @csrf
                    <textarea name="catatan_hrd" rows="2" maxlength="500" placeholder="Catatan HRD (opsional)..."
                              class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none mb-2"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                            Setujui — Final
                        </button>
                        <button type="button" @click="showTolakHrd = true"
                                class="px-3 py-2 text-sm border border-red-200 text-red-600 hover:bg-red-50 rounded-xl transition">
                            Tolak
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="showTolakHrd" x-cloak>
                <form method="POST" action="{{ route('cuti.tolak.hrd', $cuti) }}">
                    @csrf
                    <p class="text-xs text-red-600 font-medium mb-2">Alasan penolakan HRD:</p>
                    <textarea name="catatan_hrd" rows="3" required maxlength="500"
                              placeholder="Alasan penolakan..."
                              class="w-full px-3 py-2 text-sm border border-red-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-400 resize-none mb-2"></textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition">
                            Konfirmasi Tolak
                        </button>
                        <button type="button" @click="showTolakHrd = false"
                                class="px-3 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Cetak PDF (jika sudah disetujui) --}}
        @if($cuti->status === 'Disetujui')
        <a href="{{ route('cuti.cetak', $cuti) }}"
           class="flex items-center justify-center gap-2 w-full py-2.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak Surat Cuti
        </a>
        @endif

        {{-- Tombol kembali --}}
        <a href="{{ route('cuti.index') }}"
           class="flex items-center justify-center gap-2 w-full py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
            ← Kembali ke Daftar
        </a>
    </div>
</div>
@endsection
