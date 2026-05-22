@extends('layouts.app')
@section('title', 'Kelola Lock Cuti Tahunan')
@section('page-title', 'Kelola Cuti Tahunan')
@section('page-subtitle', 'Kunci / buka akses pengajuan & atur setting H-N')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')

{{-- Flash ───────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    {{ session('error') }}
</div>
@endif

{{-- Top Cards ───────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

    {{-- Card 1: Status Lock ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5" x-data="{ showKunciForm: false, showTolakForm: {} }">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Status Kunci Cuti Tahunan
        </h2>

        {{-- Big Status Badge --}}
        <div class="flex items-center justify-center mb-5">
            @if($lock->is_locked)
            <span class="px-6 py-2 text-base font-bold tracking-widest rounded-2xl bg-red-100 text-red-700 border border-red-200">
                TERKUNCI
            </span>
            @else
            <span class="px-6 py-2 text-base font-bold tracking-widest rounded-2xl bg-green-100 text-green-700 border border-green-200">
                TERBUKA
            </span>
            @endif
        </div>

        {{-- Info --}}
        @if($lock->is_locked)
        <div class="space-y-2 mb-5 text-sm">
            <div class="flex justify-between py-1.5 border-b border-gray-50">
                <span class="text-gray-500">Alasan kunci</span>
                <span class="font-medium text-gray-800 text-right max-w-xs">{{ $lock->alasan_kunci }}</span>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-50">
                <span class="text-gray-500">Dikunci oleh</span>
                <span class="font-medium text-gray-800">{{ $lock->dikunciOleh?->nama ?? '—' }}</span>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-gray-500">Dikunci pada</span>
                <span class="font-medium text-gray-800">
                    {{ $lock->dikunci_at ? \Carbon\Carbon::parse($lock->dikunci_at)->translatedFormat('d F Y, H:i') : '—' }}
                </span>
            </div>
        </div>

        {{-- Action: Buka --}}
        <form action="{{ route('cuti.lock.buka') }}" method="POST">
            @csrf
            <button type="submit"
                    onclick="return confirm('Buka kunci cuti tahunan? Pegawai akan bisa mengajukan cuti kembali.')"
                    class="w-full px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                </svg>
                Buka Kunci Cuti Tahunan
            </button>
        </form>

        @else
        <div class="space-y-2 mb-5 text-sm">
            @if($lock->dibuka_oleh)
            <div class="flex justify-between py-1.5 border-b border-gray-50">
                <span class="text-gray-500">Dibuka oleh</span>
                <span class="font-medium text-gray-800">{{ $lock->dibukaOleh?->nama ?? '—' }}</span>
            </div>
            <div class="flex justify-between py-1.5">
                <span class="text-gray-500">Dibuka pada</span>
                <span class="font-medium text-gray-800">
                    {{ $lock->dibuka_at ? \Carbon\Carbon::parse($lock->dibuka_at)->translatedFormat('d F Y, H:i') : '—' }}
                </span>
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-2">Belum pernah dikunci sebelumnya.</p>
            @endif
        </div>

        {{-- Action: Kunci --}}
        <div>
            <button @click="showKunciForm = !showKunciForm"
                    class="w-full px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors font-medium flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Kunci Cuti Tahunan
            </button>
            <div x-show="showKunciForm" x-cloak x-transition class="mt-3">
                <form action="{{ route('cuti.lock.kunci') }}" method="POST" class="space-y-3 bg-red-50 border border-red-100 rounded-xl p-4">
                    @csrf
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan Penguncian <span class="text-red-500">*</span></label>
                    <textarea name="alasan_kunci" rows="3" required
                              placeholder="Jelaskan alasan penguncian fitur cuti tahunan..."
                              class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 resize-none bg-white"></textarea>
                    <button type="submit"
                            class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 transition-colors">
                        Konfirmasi Kunci
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Card 2: Setting H-N ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Setting Batas Waktu Pengajuan
        </h2>

        <form action="{{ route('cuti.lock.setting') }}" method="POST" x-data="{ val: {{ $setting->min_hari_pengajuan }} }">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-2">
                    Pengajuan cuti minimal
                    <span class="text-blue-600 font-bold" x-text="'H-' + val"></span>
                    hari sebelumnya
                </label>
                <div class="flex items-center gap-3">
                    <input type="number" name="min_hari_pengajuan"
                           min="0" max="30" x-model="val"
                           class="w-24 border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <span class="text-sm text-gray-500">hari (0 = tidak ada batas)</span>
                </div>
            </div>

            <div class="flex items-center gap-2 p-3 bg-blue-50 border border-blue-100 rounded-xl mb-4">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-xs text-blue-700">
                    Pegawai hanya bisa memilih tanggal mulai cuti minimal
                    <strong x-text="val + ' hari'"></strong> dari hari ini.
                </p>
            </div>

            <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-medium">
                Simpan Setting
            </button>
        </form>
    </div>
</div>

{{-- Unlock Requests Table ───────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Permintaan Buka Akses
            @if($menunggu > 0)
            <span class="ml-1 px-2 py-0.5 text-xs rounded-full font-bold bg-yellow-100 text-yellow-700">{{ $menunggu }} menunggu</span>
            @endif
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm" x-data="{ tolakId: null }">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Request</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pegawai</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rencana Cuti</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Alasan</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($requests as $req)
                @php
                    $rb = match($req->status) {
                        'menunggu'  => 'bg-yellow-50 text-yellow-700',
                        'disetujui' => 'bg-green-50 text-green-700',
                        default     => 'bg-red-50 text-red-700',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition-colors" x-data="{ showTolak: false }">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $req->no_request ?? ('URQ-' . str_pad($req->id, 5, '0', STR_PAD_LEFT)) }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $req->user->nama ?? $req->user->name ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($req->tgl_rencana_mulai)->translatedFormat('d M Y') }}
                        <span class="text-gray-400">–</span>
                        {{ \Carbon\Carbon::parse($req->tgl_rencana_akhir)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 max-w-xs">
                        <p class="line-clamp-2 text-xs">{{ $req->alasan }}</p>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $rb }}">
                            {{ ucfirst($req->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($req->status === 'menunggu')
                        <div class="flex items-center justify-center gap-2">
                            {{-- Setujui --}}
                            <form action="{{ route('cuti.lock.setujui', $req) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Setujui permintaan ini?')"
                                        class="px-3 py-1.5 text-xs font-medium bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors">
                                    Setujui
                                </button>
                            </form>

                            {{-- Tolak toggle --}}
                            <button @click="showTolak = !showTolak"
                                    class="px-3 py-1.5 text-xs font-medium bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors">
                                Tolak
                            </button>
                        </div>

                        {{-- Tolak form inline --}}
                        <div x-show="showTolak" x-cloak x-transition class="mt-2">
                            <form action="{{ route('cuti.lock.tolak', $req) }}" method="POST" class="space-y-2 bg-red-50 border border-red-100 rounded-xl p-3">
                                @csrf
                                <label class="block text-xs font-semibold text-gray-700">Catatan Penolakan <span class="text-red-500">*</span></label>
                                <textarea name="catatan_hrd" rows="2" required placeholder="Alasan penolakan..."
                                          class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-red-300 resize-none bg-white"></textarea>
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                    Konfirmasi Tolak
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-xs text-gray-400 block text-center">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">
                        Belum ada permintaan buka akses.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $requests->links() }}
    </div>
    @endif
</div>

@endsection
