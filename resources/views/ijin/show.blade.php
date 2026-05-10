@extends('layouts.app')
@section('title', 'Detail ' . $labelJenis)
@section('page-title', 'Detail ' . $labelJenis)
@section('page-subtitle', $ijin->no_pengajuan . ' — ' . $ijin->tanggal->translatedFormat('d F Y'))

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-2xl">
                    {{ \App\Models\PengajuanIjin::JENIS_ICON[$ijin->jenis] }}
                </div>
                <div>
                    <p class="font-bold text-gray-800">{{ $ijin->pegawai?->nama ?? $ijin->nik }}</p>
                    <p class="text-xs text-gray-400">{{ $ijin->pegawai?->jbtn }} · NIK {{ $ijin->nik }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $ijin->tanggal->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
            @php
                $statusColor = [
                    'Menunggu Atasan' => 'bg-yellow-100 text-yellow-700',
                    'Menunggu HRD'    => 'bg-blue-100 text-blue-700',
                    'Disetujui'       => 'bg-green-100 text-green-700',
                    'Ditolak Atasan'  => 'bg-red-100 text-red-600',
                    'Ditolak HRD'     => 'bg-red-100 text-red-600',
                ][$ijin->status] ?? 'bg-gray-100 text-gray-600';
            @endphp
            <span class="px-3 py-1 text-xs font-semibold rounded-xl {{ $statusColor }}">
                {{ $ijin->status }}
            </span>
        </div>
    </div>

    {{-- Detail --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Informasi Pengajuan</h3>
        <dl class="space-y-3">
            <div class="flex justify-between text-sm">
                <dt class="text-gray-500">No. Pengajuan</dt>
                <dd class="font-mono text-gray-700 font-medium">{{ $ijin->no_pengajuan }}</dd>
            </div>
            <div class="flex justify-between text-sm">
                <dt class="text-gray-500">Jenis Ijin</dt>
                <dd class="font-medium text-gray-700">{{ $ijin->label_jenis }}</dd>
            </div>
            <div class="flex justify-between text-sm">
                <dt class="text-gray-500">Tanggal</dt>
                <dd class="text-gray-700">{{ $ijin->tanggal->translatedFormat('d F Y') }}</dd>
            </div>
            @if($ijin->jam_mulai && $ijin->jam_selesai)
            <div class="flex justify-between text-sm">
                <dt class="text-gray-500">
                    {{ $jenis === 'terlambat' ? 'Jam Masuk Shift → Jam Tiba' : 'Jam Pulang → Jam Seharusnya' }}
                </dt>
                <dd class="font-mono text-gray-700">
                    {{ \Carbon\Carbon::parse($ijin->jam_mulai)->format('H:i') }}
                    → {{ \Carbon\Carbon::parse($ijin->jam_selesai)->format('H:i') }}
                    <span class="text-gray-400 text-xs">({{ $ijin->durasi_label }})</span>
                </dd>
            </div>
            @endif
            <div class="text-sm">
                <dt class="text-gray-500 mb-1">{{ $jenis === 'sakit' ? 'Keluhan / Diagnosis' : 'Alasan' }}</dt>
                <dd class="text-gray-700 bg-gray-50 rounded-xl px-3 py-2">{{ $ijin->alasan }}</dd>
            </div>
            @if($ijin->file_surat_url)
            <div class="flex justify-between text-sm items-center">
                <dt class="text-gray-500">Surat Sakit</dt>
                <dd>
                    <a href="{{ $ijin->file_surat_url }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Lihat Surat
                    </a>
                </dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Timeline approval --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Riwayat Persetujuan</h3>
        <ol class="space-y-3">
            {{-- Pengaju --}}
            <li class="flex items-start gap-3">
                <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Diajukan</p>
                    <p class="text-xs text-gray-400">{{ $ijin->created_at->translatedFormat('d F Y, H:i') }}</p>
                </div>
            </li>

            {{-- Atasan --}}
            <li class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                    {{ $ijin->approved_atasan_at ? ($ijin->status === 'Ditolak Atasan' ? 'bg-red-100' : 'bg-green-100') : 'bg-gray-100' }}">
                    <svg class="w-3.5 h-3.5 {{ $ijin->approved_atasan_at ? ($ijin->status === 'Ditolak Atasan' ? 'text-red-500' : 'text-green-600') : 'text-gray-400' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="{{ $ijin->status === 'Ditolak Atasan' ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7' }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Atasan Langsung</p>
                    @if($ijin->approved_atasan_at)
                    <p class="text-xs text-gray-400">
                        {{ $ijin->approvedAtasanBy?->nama ?? '-' }} ·
                        {{ $ijin->approved_atasan_at->translatedFormat('d F Y, H:i') }}
                    </p>
                    @if($ijin->catatan_atasan)
                    <p class="text-xs text-gray-500 italic mt-0.5">"{{ $ijin->catatan_atasan }}"</p>
                    @endif
                    @else
                    <p class="text-xs text-gray-400">Menunggu persetujuan...</p>
                    @endif
                </div>
            </li>

            {{-- HRD --}}
            <li class="flex items-start gap-3">
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5
                    {{ $ijin->approved_hrd_at ? ($ijin->status === 'Ditolak HRD' ? 'bg-red-100' : 'bg-green-100') : 'bg-gray-100' }}">
                    <svg class="w-3.5 h-3.5 {{ $ijin->approved_hrd_at ? ($ijin->status === 'Ditolak HRD' ? 'text-red-500' : 'text-green-600') : 'text-gray-400' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="{{ $ijin->status === 'Ditolak HRD' ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7' }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">HRD</p>
                    @if($ijin->approved_hrd_at)
                    <p class="text-xs text-gray-400">
                        {{ $ijin->approvedHrdBy?->nama ?? '-' }} ·
                        {{ $ijin->approved_hrd_at->translatedFormat('d F Y, H:i') }}
                    </p>
                    @if($ijin->catatan_hrd)
                    <p class="text-xs text-gray-500 italic mt-0.5">"{{ $ijin->catatan_hrd }}"</p>
                    @endif
                    @else
                    <p class="text-xs text-gray-400">Menunggu persetujuan atasan...</p>
                    @endif
                </div>
            </li>
        </ol>
    </div>

    {{-- Tombol approval --}}
    @if($ijin->bisaApproveAtasan())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <p class="text-sm font-semibold text-gray-700">Tindakan Anda sebagai Atasan</p>
        <div class="grid grid-cols-2 gap-3">
            <form method="POST" action="{{ parse_url(route('ijin.approve.atasan', $ijin), PHP_URL_PATH) }}">
                @csrf
                <textarea name="catatan_atasan" placeholder="Catatan (opsional)" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none mb-2 resize-none"></textarea>
                <button type="submit"
                        class="w-full py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                    Setujui
                </button>
            </form>
            <form method="POST" action="{{ parse_url(route('ijin.tolak.atasan', $ijin), PHP_URL_PATH) }}">
                @csrf
                <textarea name="catatan_atasan" placeholder="Alasan penolakan" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none mb-2 resize-none"></textarea>
                <button type="submit"
                        class="w-full py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold transition">
                    Tolak
                </button>
            </form>
        </div>
    </div>
    @endif

    @if($ijin->bisaApproveHrd())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
        <p class="text-sm font-semibold text-gray-700">Tindakan HRD</p>
        <div class="grid grid-cols-2 gap-3">
            <form method="POST" action="{{ parse_url(route('ijin.approve.hrd', $ijin), PHP_URL_PATH) }}">
                @csrf
                <textarea name="catatan_hrd" placeholder="Catatan (opsional)" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none mb-2 resize-none"></textarea>
                <button type="submit"
                        class="w-full py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                    Setujui
                </button>
            </form>
            <form method="POST" action="{{ parse_url(route('ijin.tolak.hrd', $ijin), PHP_URL_PATH) }}">
                @csrf
                <textarea name="catatan_hrd" placeholder="Alasan penolakan" rows="2"
                          class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none mb-2 resize-none"></textarea>
                <button type="submit"
                        class="w-full py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold transition">
                    Tolak
                </button>
            </form>
        </div>
    </div>
    @endif

    <a href="{{ route('ijin.index', $jenis) }}"
       class="inline-block text-sm text-gray-500 hover:text-gray-700 transition">← Kembali</a>

</div>
@endsection
