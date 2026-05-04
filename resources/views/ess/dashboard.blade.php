@extends('layouts.app')
@section('title', 'Portal Karyawan')
@section('page-title', 'Portal Karyawan')
@section('page-subtitle', now()->translatedFormat('l, d F Y'))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #mapEss { height: 220px; border-radius: 1rem; overflow: hidden; z-index: 0; }
    .pulse-ring { animation: pulse-ring 1.2s ease-out infinite; }
    @keyframes pulse-ring { 0%{transform:scale(1);opacity:.9} 100%{transform:scale(2.2);opacity:0} }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="max-w-md mx-auto space-y-4"
     x-data="essPortal()"
     x-init="init()">

    {{-- ── Profil Pegawai ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <img src="{{ $pegawai->foto_url }}"
             class="w-14 h-14 rounded-full object-cover border-2 border-gray-100 flex-shrink-0"
             onerror="this.src='{{ asset('images/avatar-default.png') }}'">
        <div class="flex-1 min-w-0">
            <p class="font-bold text-gray-800 truncate">{{ $pegawai->nama }}</p>
            <p class="text-sm text-gray-500 truncate">{{ $pegawai->jbtn }}</p>
            <p class="text-xs text-gray-400">NIK {{ $pegawai->nik }}</p>
        </div>
        <div class="text-right flex-shrink-0">
            <p class="text-2xl font-bold text-blue-600">{{ $sisaCuti }}</p>
            <p class="text-xs text-gray-400">sisa cuti</p>
        </div>
    </div>

    {{-- ── Tab Bar ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-1 flex gap-1">
        <button @click="tab = 'absensi'"
                :class="tab === 'absensi'
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'text-gray-500 hover:bg-gray-50'"
                class="flex-1 py-2 text-sm font-semibold rounded-xl transition flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Absensi
        </button>
        <button @click="tab = 'cuti'"
                :class="tab === 'cuti'
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'text-gray-500 hover:bg-gray-50'"
                class="flex-1 py-2 text-sm font-semibold rounded-xl transition flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Cuti
            @if($sisaCuti <= 3)
            <span class="w-1.5 h-1.5 rounded-full bg-orange-400 flex-shrink-0"></span>
            @endif
        </button>
        <button @click="tab = 'training'"
                :class="tab === 'training'
                    ? 'bg-blue-600 text-white shadow-sm'
                    : 'text-gray-500 hover:bg-gray-50'"
                class="flex-1 py-2 text-sm font-semibold rounded-xl transition flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Training
            @if($expiringSoon > 0)
            <span class="w-1.5 h-1.5 rounded-full bg-orange-400 flex-shrink-0"></span>
            @endif
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB ABSENSI                                                         --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'absensi'" x-cloak>

        {{-- Status absensi hari ini --}}
        @if($absensiHariIni)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Status Hari Ini</p>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="bg-green-50 rounded-xl p-3">
                    <p class="text-lg font-bold text-green-700">
                        {{ $absensiHariIni->jam_masuk ? \Carbon\Carbon::parse($absensiHariIni->jam_masuk)->format('H:i') : '—' }}
                    </p>
                    <p class="text-xs text-green-600">Check-In</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-3">
                    <p class="text-lg font-bold text-blue-700">
                        {{ $absensiHariIni->jam_keluar ? \Carbon\Carbon::parse($absensiHariIni->jam_keluar)->format('H:i') : '—' }}
                    </p>
                    <p class="text-xs text-blue-600">Check-Out</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <p class="text-lg font-bold text-gray-700">{{ $absensiHariIni->durasi_kerja ?? '—' }}</p>
                    <p class="text-xs text-gray-500">Durasi</p>
                </div>
            </div>
            @if(($absensiHariIni->terlambat_menit ?? 0) > 0)
            <p class="mt-2 text-xs text-orange-600 bg-orange-50 px-3 py-1.5 rounded-lg">
                Terlambat {{ $absensiHariIni->terlambat_label }}
            </p>
            @endif
        </div>
        @endif

        {{-- Peta lokasi --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-4">
            <div id="mapEss"></div>
            <div class="p-3 flex items-center justify-between">
                <div>
                    <template x-if="!lokasiku">
                        <p class="text-sm text-gray-500">Lokasi belum diambil</p>
                    </template>
                    <template x-if="lokasiku">
                        <div>
                            <p class="text-sm font-medium text-gray-800">
                                <span x-text="statusRadius ? '✅ Dalam radius' : '⚠ Di luar radius'"></span>
                            </p>
                            <p class="text-xs text-gray-400">
                                Jarak: <span x-text="jarakMeter + ' m'"></span>
                                &nbsp;·&nbsp;Akurasi: <span x-text="akurasi + ' m'"></span>
                            </p>
                        </div>
                    </template>
                </div>
                <button @click="ambilLokasi()" :disabled="loadingGps"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl transition"
                        :class="loadingGps ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-blue-50 text-blue-600 hover:bg-blue-100'">
                    <svg x-show="!loadingGps" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <svg x-show="loadingGps" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span x-text="loadingGps ? 'Mengambil...' : 'Ambil Lokasi'"></span>
                </button>
            </div>
        </div>

        {{-- Notifikasi absensi --}}
        <template x-if="notif.msg">
            <div class="mt-4 px-4 py-3 rounded-xl text-sm font-medium"
                 :class="notif.type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'"
                 x-text="notif.msg"></div>
        </template>

        {{-- Tombol check-in / check-out --}}
        <div class="mt-4">
        @if(!$absensiHariIni)
        <button @click="doCheckIn()"
                :disabled="!lokasiku || loading"
                class="w-full py-4 rounded-2xl text-white font-bold text-base transition flex items-center justify-center gap-2"
                :class="lokasiku && !loading ? 'bg-green-600 hover:bg-green-700 shadow-lg shadow-green-200' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
            <span x-show="!loading" class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                Check-In Sekarang
            </span>
            <span x-show="loading" class="flex items-center gap-2">
                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Memproses...
            </span>
        </button>

        @elseif(!$absensiHariIni->jam_keluar)
        <button @click="doCheckOut()"
                :disabled="!lokasiku || loading"
                class="w-full py-4 rounded-2xl text-white font-bold text-base transition flex items-center justify-center gap-2"
                :class="lokasiku && !loading ? 'bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-200' : 'bg-gray-200 text-gray-400 cursor-not-allowed'">
            <span x-show="!loading" class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                Check-Out Sekarang
            </span>
            <span x-show="loading">Memproses...</span>
        </button>

        @else
        <div class="w-full py-4 rounded-2xl bg-gray-100 text-gray-500 font-semibold text-center text-sm">
            ✓ Absensi hari ini sudah selesai
        </div>
        @endif
        </div>

    </div>{{-- end tab absensi --}}

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB CUTI                                                            --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'cuti'" x-cloak class="space-y-4">

        {{-- Flash sukses pengajuan --}}
        @if(session('cuti_success'))
        <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('cuti_success') }}
        </div>
        @endif
        @if($errors->any() && request('tab') !== 'absensi')
        <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        {{-- Saldo cuti --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Saldo Cuti {{ now()->year }}</p>
                    <div class="flex items-end gap-1">
                        <span class="text-3xl font-bold {{ $sisaCuti <= 3 ? 'text-orange-600' : 'text-blue-600' }}">{{ $sisaCuti }}</span>
                        <span class="text-sm text-gray-400 mb-0.5">/ {{ \App\Models\PengajuanCuti::HAK_CUTI_TAHUNAN }} hari</span>
                    </div>
                    @if($sisaCuti <= 3 && $sisaCuti > 0)
                    <p class="text-xs text-orange-500 mt-0.5">Sisa cuti hampir habis</p>
                    @elseif($sisaCuti === 0)
                    <p class="text-xs text-red-500 mt-0.5">Cuti tahun ini sudah habis</p>
                    @endif
                </div>
                <div class="w-16 h-16 relative">
                    <svg viewBox="0 0 36 36" class="w-16 h-16 -rotate-90">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none" stroke="#e5e7eb" stroke-width="3"/>
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                              fill="none"
                              stroke="{{ $sisaCuti <= 3 ? '#f97316' : '#2563eb' }}"
                              stroke-width="3"
                              stroke-dasharray="{{ round(($sisaCuti / \App\Models\PengajuanCuti::HAK_CUTI_TAHUNAN) * 100, 1) }}, 100"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Form Ajukan Cuti ─────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
             x-data="{ open: {{ $errors->any() ? 'true' : 'false' }} }">

            <button type="button" @click="open = !open"
                    class="w-full px-4 py-3.5 flex items-center justify-between text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajukan Cuti Baru
                </span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-collapse class="border-t border-gray-100">
                <form method="POST" action="{{ route('ess.cuti.store') }}"
                      class="p-4 space-y-3">
                    @csrf

                    {{-- Jenis Cuti --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Cuti <span class="text-red-500">*</span></label>
                        <select name="urgensi" required
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                            <option value="">-- Pilih jenis cuti --</option>
                            @foreach(\App\Models\PengajuanCuti::JENIS_CUTI as $j)
                            <option value="{{ $j }}" {{ old('urgensi') === $j ? 'selected' : '' }}>{{ $j }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Periode --}}
                    <div class="grid grid-cols-2 gap-2" x-data="cutiPeriod()">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_awal" required
                                   value="{{ old('tanggal_awal') }}"
                                   min="{{ today()->format('Y-m-d') }}"
                                   x-model="awal" @change="hitung()"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_akhir" required
                                   value="{{ old('tanggal_akhir') }}"
                                   :min="awal || '{{ today()->format('Y-m-d') }}'"
                                   x-model="akhir" @change="hitung()"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div x-show="hari > 0" class="col-span-2">
                            <p class="text-xs text-blue-600 bg-blue-50 px-3 py-1.5 rounded-lg">
                                Estimasi: <strong x-text="hari + ' hari kerja'"></strong>
                            </p>
                        </div>
                    </div>

                    {{-- Alamat --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alamat Selama Cuti <span class="text-red-500">*</span></label>
                        <input type="text" name="alamat" required maxlength="255"
                               value="{{ old('alamat') }}"
                               placeholder="Alamat yang bisa dihubungi"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>

                    {{-- Alasan --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alasan <span class="text-red-500">*</span></label>
                        <textarea name="kepentingan" required maxlength="500" rows="2"
                                  placeholder="Keperluan cuti..."
                                  class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('kepentingan') }}</textarea>
                    </div>

                    {{-- Penanggung Jawab --}}
                    @if($pegawaiPj->isNotEmpty())
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Penanggung Jawab</label>
                        <select name="nik_pj"
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                            <option value="">-- Tidak ada --</option>
                            @foreach($pegawaiPj as $p)
                            <option value="{{ $p->nik }}" {{ old('nik_pj') === $p->nik ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="submit"
                            class="w-full py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                        Ajukan Sekarang
                    </button>
                </form>
            </div>
        </div>

        {{-- Histori Pengajuan ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-4 py-3.5 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-700">Histori Pengajuan</p>
                <span class="text-xs text-gray-400">{{ $cutiSaya->count() }} data</span>
            </div>

            @if($cutiSaya->isEmpty())
            <p class="text-sm text-gray-400 text-center py-8">Belum ada pengajuan cuti.</p>
            @else
            <ul class="divide-y divide-gray-50">
                @foreach($cutiSaya as $c)
                @php
                    $sc = match($c->status) {
                        'Menunggu Atasan' => ['color' => 'yellow', 'bg' => 'bg-yellow-50', 'text' => 'text-yellow-700'],
                        'Menunggu HRD'   => ['color' => 'blue',   'bg' => 'bg-blue-50',   'text' => 'text-blue-700'],
                        'Disetujui'      => ['color' => 'green',  'bg' => 'bg-green-50',  'text' => 'text-green-700'],
                        default          => ['color' => 'red',    'bg' => 'bg-red-50',    'text' => 'text-red-700'],
                    };
                @endphp
                <li class="px-4 py-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <p class="text-xs font-mono text-gray-400">{{ $c->no_pengajuan }}</p>
                                <span class="text-xs font-medium px-1.5 py-0.5 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }} whitespace-nowrap">
                                    {{ $c->status }}
                                </span>
                            </div>
                            <p class="text-sm font-semibold text-gray-800">{{ $c->urgensi }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $c->tanggal_awal->translatedFormat('d M Y') }}
                                @if($c->tanggal_awal != $c->tanggal_akhir)
                                    – {{ $c->tanggal_akhir->translatedFormat('d M Y') }}
                                @endif
                                <span class="text-gray-400">· {{ $c->jumlah }} hari</span>
                            </p>
                            @if($c->catatan_atasan && str_starts_with($c->status, 'Ditolak'))
                            <p class="text-xs text-red-500 mt-1 italic">
                                Alasan: {{ $c->catatan_atasan ?? $c->catatan_hrd }}
                            </p>
                            @endif
                        </div>
                        <a href="{{ route('cuti.show', $c) }}"
                           class="text-xs text-blue-500 hover:text-blue-700 flex-shrink-0 mt-1">
                            Detail →
                        </a>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- Info alur --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4 text-xs text-blue-700 space-y-1">
            <p class="font-semibold">Alur Persetujuan Cuti:</p>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-2 py-0.5 bg-white rounded-full border border-blue-200">Kamu</span>
                <span>→</span>
                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full">Atasan Langsung</span>
                <span>→</span>
                <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full">HRD</span>
                <span>→</span>
                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full">Disetujui</span>
            </div>
        </div>

    </div>{{-- end tab cuti --}}

    {{-- ════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB TRAINING                                                         --}}
    {{-- ════════════════════════════════════════════════════════════════════ --}}
    <div x-show="tab === 'training'" x-cloak class="space-y-4">

        @if($expiringSoon > 0)
        <div class="px-4 py-3 bg-orange-50 border border-orange-200 text-orange-700 rounded-xl text-sm">
            ⚠️ <strong>{{ $expiringSoon }}</strong> sertifikat Anda akan expired dalam 30 hari. Segera perbarui.
        </div>
        @endif

        {{-- IHT yang Diikuti --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <p class="text-sm font-semibold text-gray-700 mb-3">IHT yang Diikuti</p>
            @if($trainingIHT->isEmpty())
            <p class="text-xs text-gray-400 text-center py-4">Belum ada IHT yang diikuti.</p>
            @else
            <ul class="space-y-2">
                @foreach($trainingIHT as $t)
                @php
                    $stColor = match($t->status) {
                        'selesai' => 'bg-green-100 text-green-700',
                        'hadir'   => 'bg-blue-100 text-blue-700',
                        'tidak_hadir' => 'bg-red-100 text-red-600',
                        default   => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <li class="flex items-center justify-between gap-3 p-3 bg-gray-50 rounded-xl">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $t->iht?->nama_training }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $t->iht?->tanggal_mulai?->translatedFormat('d M Y') }} · {{ $t->iht?->lokasi }}
                        </p>
                        @if($t->nomor_sertifikat)
                        <p class="text-xs font-mono text-green-600 mt-0.5">{{ $t->nomor_sertifikat }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-lg {{ $stColor }}">
                            {{ \App\Models\IHTPeserta::STATUS[$t->status] ?? $t->status }}
                        </span>
                        @if($t->sudahSertifikat())
                        <a href="{{ route('training.iht.peserta.sertifikat.download', [$t->iht_id, $t->id]) }}"
                           class="text-xs text-blue-500 hover:text-blue-700">⬇</a>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- Training Eksternal --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-gray-700">Training Eksternal</p>
                <a href="{{ route('training.eksternal.create') }}"
                   class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    + Ajukan
                </a>
            </div>
            @if($trainingEksternal->isEmpty())
            <p class="text-xs text-gray-400 text-center py-4">Belum ada training eksternal.</p>
            @else
            <ul class="space-y-2">
                @foreach($trainingEksternal as $e)
                @php
                    $expired  = $e->isExpired();
                    $expiring = !$expired && $e->isExpiringSoon();
                @endphp
                <li class="flex items-center justify-between gap-3 p-3 bg-gray-50 rounded-xl">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 truncate">{{ $e->nama_training }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $e->lembaga }}
                            · {{ $e->tanggal_mulai->translatedFormat('d M Y') }}</p>
                        @if($e->masa_berlaku)
                        <p class="text-xs mt-0.5 {{ $expired ? 'text-red-500 font-semibold' : ($expiring ? 'text-orange-500' : 'text-gray-400') }}">
                            Berlaku s/d {{ $e->masa_berlaku->translatedFormat('d M Y') }}
                            @if($expired)(Expired)@elseif($expiring)({{ $e->masa_berlaku->diffInDays(now()) }} hari lagi)@endif
                        </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="px-2 py-0.5 text-xs font-medium rounded-lg
                            {{ \App\Models\TrainingEksternal::STATUS_COLOR[$e->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ \App\Models\TrainingEksternal::STATUS_LABEL[$e->status] ?? $e->status }}
                        </span>
                        <a href="{{ route('training.eksternal.show', $e) }}"
                           class="text-xs text-blue-500 hover:text-blue-700">Detail →</a>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

    </div>{{-- end tab training --}}

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Lokasi perusahaan dari server
@php
    $lokasiEss = \App\Models\LokasiAbsensi::aktif()->get(['nama','lat','lng','radius_meter'])
        ->map(function($l) {
            return ['nama'=>$l->nama,'lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'radius_meter'=>$l->radius_meter];
        })->values();
@endphp
const lokasiPerusahaan = @json($lokasiEss);

// Tab awal: jika ada param ?tab=cuti atau ada error form cuti
const initialTab = '{{ request('tab', 'absensi') }}';

function essPortal() {
    return {
        tab:         initialTab,
        lokasiku:    null,
        jarakMeter:  0,
        akurasi:     0,
        statusRadius: false,
        loadingGps:  false,
        loading:     false,
        notif:       { msg: '', type: '' },
        map:         null,
        userMarker:  null,
        userCircle:  null,

        init() {
            // Init peta hanya saat tab absensi aktif/visible
            this.$nextTick(() => {
                this.initMap();
                if (this.tab === 'absensi') {
                    this.ambilLokasi();
                }
            });

            // Kalau ada error validasi cuti, pindah ke tab cuti
            @if($errors->any())
            this.tab = 'cuti';
            @endif
        },

        initMap() {
            this.map = L.map('mapEss');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(this.map);

            if (lokasiPerusahaan.length > 0) {
                lokasiPerusahaan.forEach(l => {
                    L.circle([l.lat, l.lng], {
                        radius: l.radius_meter, color: '#2563eb', fillOpacity: 0.15
                    }).addTo(this.map).bindTooltip(l.nama + ' (' + l.radius_meter + ' m)');
                    L.marker([l.lat, l.lng]).addTo(this.map)
                        .bindPopup('<b>' + l.nama + '</b>');
                });
                const bounds = L.latLngBounds(lokasiPerusahaan.map(l => [l.lat, l.lng]));
                this.map.fitBounds(bounds.pad(0.5));
            } else {
                this.map.setView([-2.5, 118], 5);
            }
        },

        ambilLokasi() {
            if (!navigator.geolocation) {
                this.setNotif('Browser tidak mendukung GPS.', 'error');
                return;
            }
            this.loadingGps = true;
            this.notif = { msg: '', type: '' };

            navigator.geolocation.getCurrentPosition(
                pos => {
                    this.loadingGps = false;
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    this.akurasi  = Math.round(pos.coords.accuracy);
                    this.lokasiku = { lat, lng };

                    if (lokasiPerusahaan.length > 0) {
                        const jarakList = lokasiPerusahaan.map(l => this.haversine(l.lat, l.lng, lat, lng));
                        this.jarakMeter   = Math.round(Math.min(...jarakList));
                        const minIdx      = jarakList.indexOf(Math.min(...jarakList));
                        this.statusRadius = jarakList[minIdx] <= lokasiPerusahaan[minIdx].radius_meter;
                    } else {
                        this.statusRadius = true;
                    }

                    if (this.userMarker) this.map.removeLayer(this.userMarker);
                    if (this.userCircle) this.map.removeLayer(this.userCircle);

                    this.userMarker = L.circleMarker([lat, lng], {
                        radius: 8,
                        color: this.statusRadius ? '#16a34a' : '#ea580c',
                        fillColor: this.statusRadius ? '#22c55e' : '#f97316',
                        fillOpacity: 0.8
                    }).addTo(this.map).bindPopup('Lokasi Anda');

                    this.userCircle = L.circle([lat, lng], {
                        radius: this.akurasi, color: '#6b7280', fillOpacity: 0.08, weight: 1
                    }).addTo(this.map);

                    this.map.setView([lat, lng], 17);
                    this.map.invalidateSize();
                },
                err => {
                    this.loadingGps = false;
                    const msg = {
                        1: 'Izin GPS ditolak. Aktifkan lokasi di browser.',
                        2: 'Posisi tidak tersedia.',
                        3: 'Timeout mengambil lokasi.',
                    }[err.code] || 'Gagal mendapatkan lokasi.';
                    this.setNotif(msg, 'error');
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 }
            );
        },

        doCheckIn() {
            if (!this.lokasiku || this.loading) return;
            this.loading = true;
            this.notif   = { msg: '', type: '' };
            fetch('{{ route('ess.checkin') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ lat: this.lokasiku.lat, lng: this.lokasiku.lng }),
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                this.loading = false;
                if (ok) { this.setNotif(data.message, 'success'); setTimeout(() => location.reload(), 1500); }
                else    { this.setNotif(data.message || 'Gagal check-in.', 'error'); }
            })
            .catch(() => { this.loading = false; this.setNotif('Terjadi kesalahan koneksi.', 'error'); });
        },

        doCheckOut() {
            if (!this.lokasiku || this.loading) return;
            this.loading = true;
            this.notif   = { msg: '', type: '' };
            fetch('{{ route('ess.checkout') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ lat: this.lokasiku.lat, lng: this.lokasiku.lng }),
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                this.loading = false;
                if (ok) { this.setNotif(data.message, 'success'); setTimeout(() => location.reload(), 1500); }
                else    { this.setNotif(data.message || 'Gagal check-out.', 'error'); }
            })
            .catch(() => { this.loading = false; this.setNotif('Terjadi kesalahan koneksi.', 'error'); });
        },

        haversine(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const φ1 = lat1 * Math.PI / 180, φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lng2 - lng1) * Math.PI / 180;
            const a  = Math.sin(Δφ/2)**2 + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)**2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        },

        setNotif(msg, type) {
            this.notif = { msg, type };
            if (type === 'success') setTimeout(() => this.notif = { msg: '', type: '' }, 5000);
        },
    };
}

function cutiPeriod() {
    return {
        awal: '{{ old('tanggal_awal', '') }}',
        akhir: '{{ old('tanggal_akhir', '') }}',
        hari: 0,
        hitung() {
            if (!this.awal || !this.akhir) { this.hari = 0; return; }
            const a = new Date(this.awal), b = new Date(this.akhir);
            if (b < a) { this.hari = 0; return; }
            let count = 0;
            const d = new Date(a);
            while (d <= b) { if (d.getDay() !== 0 && d.getDay() !== 6) count++; d.setDate(d.getDate() + 1); }
            this.hari = count;
        },
        init() { this.hitung(); }
    };
}
</script>
@endpush
