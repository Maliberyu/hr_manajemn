@extends('layouts.app')
@section('title', 'Cuti Tahunan')
@section('page-title', 'Cuti Tahunan')
@section('page-subtitle', 'Pengajuan & riwayat cuti tahunan')

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
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- ① Global Lock Banner (HRD emergency lock) ──────────────────────────────── --}}
@if($lock->is_locked)
<div class="mb-4 bg-red-50 border border-red-200 rounded-2xl p-4" x-data="{ showForm: false }">
    <div class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4.5 h-4.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-bold text-red-800">Cuti tahunan sedang ditutup oleh HRD</p>
            <p class="text-xs text-red-700 mt-0.5">Alasan: {{ $lock->alasan_kunci }}</p>

            @if(!$isHrd)
                @if(is_null($unlockReqSaya))
                <button @click="showForm = !showForm"
                        class="mt-2 text-xs font-medium text-red-700 underline underline-offset-2 hover:text-red-900">
                    <span x-text="showForm ? 'Sembunyikan' : 'Minta akses ke HRD'"></span>
                </button>
                <div x-show="showForm" x-cloak x-transition class="mt-3">
                    <form action="{{ route('cuti.tahunan.request-buka') }}" method="POST"
                          class="space-y-3 bg-white border border-red-200 rounded-xl p-4">
                        @csrf
                        <p class="text-xs font-semibold text-gray-700">Permintaan Akses Cuti</p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Rencana Mulai</label>
                                <input type="date" name="tgl_rencana_mulai" required
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Rencana Selesai</label>
                                <input type="date" name="tgl_rencana_akhir" required
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Alasan Mendesak</label>
                            <textarea name="alasan" rows="2" required
                                      placeholder="Jelaskan mengapa perlu cuti saat periode ditutup..."
                                      class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium">
                            Kirim Permintaan
                        </button>
                    </form>
                </div>
                @elseif($unlockReqSaya->status === 'menunggu')
                <p class="mt-2 text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 px-3 py-1.5 rounded-lg inline-block">
                    Permintaan Anda sedang ditinjau HRD.
                </p>
                @elseif($unlockReqSaya->status === 'disetujui')
                <p class="mt-2 text-xs text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-lg inline-block">
                    HRD menyetujui — kamu boleh mengajukan cuti.
                </p>
                @elseif($unlockReqSaya->status === 'ditolak')
                <p class="mt-2 text-xs text-red-700">Permintaan ditolak: {{ $unlockReqSaya->catatan_hrd }}</p>
                @endif
            @endif
        </div>
    </div>
</div>
@endif

{{-- ② H-N Info + Bypass Section (untuk karyawan & atasan) ──────────────────── --}}
@if(!$isHrd)
@php
    $hasApprovedBypass = $unlockReqSaya && $unlockReqSaya->status === 'disetujui';
@endphp

@if($hasApprovedBypass)
{{-- Bypass aktif --}}
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-2xl flex items-center gap-3">
    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div>
        <p class="text-sm font-semibold text-green-800">Bypass H-{{ $setting->min_hari_pengajuan }} disetujui HRD</p>
        <p class="text-xs text-green-700 mt-0.5">Kamu boleh mengajukan cuti mendadak (H-1, H-2, bahkan hari-H).</p>
    </div>
</div>
@else
{{-- H-N rule info + form request bypass --}}
<div class="mb-4 rounded-2xl border border-blue-100 overflow-hidden" x-data="{ showBypass: false }">
    <div class="px-4 py-3 bg-blue-50 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <div>
                <p class="text-sm font-semibold text-blue-800">
                    Cuti minimal diajukan <strong>H-{{ $setting->min_hari_pengajuan }}</strong> sebelum tanggal mulai
                </p>
                <p class="text-xs text-blue-600 mt-0.5">
                    Tanggal mulai paling awal: <strong>{{ \Carbon\Carbon::parse($minTanggal)->translatedFormat('d F Y') }}</strong>
                </p>
            </div>
        </div>
        {{-- Tombol request bypass --}}
        @if(is_null($unlockReqSaya) || $unlockReqSaya->status === 'ditolak')
        <button @click="showBypass = !showBypass"
                class="flex-shrink-0 text-xs font-semibold text-blue-700 bg-white border border-blue-200 px-3 py-1.5 rounded-xl hover:bg-blue-50 transition-colors whitespace-nowrap">
            <span x-text="showBypass ? 'Tutup' : 'Cuti Mendadak?'"></span>
        </button>
        @endif
    </div>

    {{-- Status request yang sudah ada --}}
    @if($unlockReqSaya && $unlockReqSaya->status === 'menunggu')
    <div class="px-4 py-3 bg-yellow-50 border-t border-yellow-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-yellow-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-xs text-yellow-700 font-medium">
            Permintaan cuti mendadak sedang ditinjau HRD
            @if($unlockReqSaya->tgl_rencana_mulai)
            — rencana {{ \Carbon\Carbon::parse($unlockReqSaya->tgl_rencana_mulai)->translatedFormat('d M Y') }}
            s/d {{ \Carbon\Carbon::parse($unlockReqSaya->tgl_rencana_akhir)->translatedFormat('d M Y') }}
            @endif
        </p>
    </div>
    @elseif($unlockReqSaya && $unlockReqSaya->status === 'ditolak')
    <div class="px-4 py-3 bg-red-50 border-t border-red-100">
        <p class="text-xs text-red-700 font-medium">Permintaan sebelumnya ditolak: {{ $unlockReqSaya->catatan_hrd }}</p>
    </div>
    @endif

    {{-- Form bypass H-N --}}
    <div x-show="showBypass" x-cloak x-transition class="border-t border-blue-100">
        <form action="{{ route('cuti.tahunan.request-buka') }}" method="POST" class="p-4 space-y-3 bg-white">
            @csrf
            <p class="text-xs font-semibold text-gray-700">Permintaan Cuti Mendadak (kurang dari H-{{ $setting->min_hari_pengajuan }})</p>
            <p class="text-xs text-gray-500">Jelaskan alasan kenapa kamu perlu cuti dalam waktu dekat. HRD akan meninjau dan memutuskan.</p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rencana Mulai Cuti <span class="text-red-400">*</span></label>
                    <input type="date" name="tgl_rencana_mulai" required
                           max="{{ $minTanggal }}"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rencana Selesai Cuti <span class="text-red-400">*</span></label>
                    <input type="date" name="tgl_rencana_akhir" required
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Alasan Mendesak <span class="text-red-400">*</span></label>
                <textarea name="alasan" rows="3" required
                          placeholder="Contoh: ada keperluan keluarga mendadak, kondisi darurat, dll."
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 resize-none"></textarea>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium">
                    Kirim ke HRD
                </button>
                <button type="button" @click="showBypass = false"
                        class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endif

{{-- ③ Toolbar ────────────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <form method="GET" action="{{ route('cuti.tahunan.index') }}" class="flex flex-wrap gap-2">
        <select name="status" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-300">
            <option value="">Semua Status</option>
            @foreach(['Menunggu Atasan','Menunggu HRD','Disetujui','Ditolak Atasan','Ditolak HRD'] as $st)
            <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
            @endforeach
        </select>
        <select name="tahun" onchange="this.form.submit()"
                class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-300">
            @foreach(range(date('Y'), date('Y')-4) as $yr)
            <option value="{{ $yr }}" {{ request('tahun', date('Y')) == $yr ? 'selected' : '' }}>{{ $yr }}</option>
            @endforeach
        </select>
    </form>

    @php
        $canApply = !$lock->is_locked || ($unlockReqSaya && $unlockReqSaya->status === 'disetujui');
    @endphp
    @if($canApply || $isHrd)
    <a href="{{ route('cuti.tahunan.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Ajukan Cuti Tahunan
    </a>
    @else
    <span class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-gray-100 text-gray-400 rounded-xl cursor-not-allowed whitespace-nowrap"
          title="Cuti sedang ditutup HRD">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        Ajukan Cuti Tahunan
    </span>
    @endif
</div>

{{-- ④ Tabel ──────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    @if($list->isEmpty())
    <div class="py-14 text-center">
        <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-sm text-gray-400">Belum ada pengajuan cuti tahunan.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Pengajuan</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pegawai</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Periode Cuti</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Hari</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($list as $item)
                @php
                    $badge = match(true) {
                        in_array($item->status, ['Menunggu Atasan','Menunggu HRD']) => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                        $item->status === 'Disetujui'                               => 'bg-green-50 text-green-700 border border-green-200',
                        default                                                      => 'bg-red-50 text-red-700 border border-red-200',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3.5 font-mono text-xs text-gray-500">{{ $item->no_pengajuan }}</td>
                    <td class="px-4 py-3.5">
                        <p class="font-medium text-gray-800">{{ $item->pegawai?->nama ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $item->nik }}</p>
                    </td>
                    <td class="px-4 py-3.5 text-gray-700 text-xs">
                        {{ \Carbon\Carbon::parse($item->tanggal_awal)->translatedFormat('d M Y') }}
                        <span class="text-gray-400">–</span>
                        {{ \Carbon\Carbon::parse($item->tanggal_akhir)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-4 py-3.5 text-center font-bold text-blue-600">{{ $item->jumlah }}</td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 text-xs rounded-full font-medium {{ $badge }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <a href="{{ route('cuti.show', $item) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Lihat
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($list->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">{{ $list->withQueryString()->links() }}</div>
    @endif
    @endif
</div>

@endsection
