@extends('layouts.app')
@section('title', 'Absensi Mandiri')
@section('page-title', 'Absensi Mandiri')
@section('page-subtitle', now()->translatedFormat('l, d F Y'))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #mapEss { height: 220px; border-radius: 1rem; overflow: hidden; z-index: 0; }
    .pulse-ring { animation: pulse-ring 1.2s ease-out infinite; }
    @keyframes pulse-ring { 0%{transform:scale(1);opacity:.9} 100%{transform:scale(2.2);opacity:0} }
</style>
@endpush

@section('content')
<div class="max-w-md mx-auto space-y-4"
     x-data="essAbsensi()"
     x-init="init()">

    {{-- Profil pegawai ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <img src="{{ $pegawai->foto_url }}"
             class="w-14 h-14 rounded-full object-cover border-2 border-gray-100 flex-shrink-0"
             onerror="this.src='{{ asset('images/avatar-default.png') }}'">
        <div>
            <p class="font-bold text-gray-800">{{ $pegawai->nama }}</p>
            <p class="text-sm text-gray-500">{{ $pegawai->jbtn }}</p>
            <p class="text-xs text-gray-400">NIK {{ $pegawai->nik }}</p>
        </div>
    </div>

    {{-- Status absensi hari ini ─────────────────────────────────────────── --}}
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
        @if($absensiHariIni->terlambat_menit > 0)
        <p class="mt-2 text-xs text-orange-600 bg-orange-50 px-3 py-1.5 rounded-lg">
            ⚠ Terlambat {{ $absensiHariIni->terlambat_label }}
        </p>
        @endif
    </div>
    @endif

    {{-- Peta lokasi ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
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
                <span x-show="!loadingGps">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                <svg x-show="loadingGps" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="loadingGps ? 'Mengambil...' : 'Ambil Lokasi'"></span>
            </button>
        </div>
    </div>

    {{-- Notifikasi ──────────────────────────────────────────────────────── --}}
    <template x-if="notif.msg">
        <div class="px-4 py-3 rounded-xl text-sm font-medium"
             :class="notif.type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'"
             x-text="notif.msg"></div>
    </template>

    {{-- Tombol check-in / check-out ─────────────────────────────────────── --}}
    @if(!$absensiHariIni)
    {{-- Belum check-in --}}
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
    {{-- Sudah check-in, belum check-out --}}
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
    {{-- Sudah check-in & check-out --}}
    <div class="w-full py-4 rounded-2xl bg-gray-100 text-gray-500 font-semibold text-center text-sm">
        ✓ Absensi hari ini sudah selesai
    </div>
    @endif

    {{-- Riwayat cuti ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sisa Cuti</p>
            <span class="text-2xl font-bold text-blue-600">{{ $sisaCuti }}</span>
        </div>
        @if($cutiSaya->isNotEmpty())
        <ul class="divide-y divide-gray-50 text-xs">
            @foreach($cutiSaya->take(3) as $c)
            <li class="py-2 flex items-center justify-between">
                <span class="text-gray-600">
                    {{ \Carbon\Carbon::parse($c->tanggal ?? $c->tgl_mulai ?? now())->translatedFormat('d M Y') }}
                    @if(isset($c->jumlah_hari)) · {{ $c->jumlah_hari }} hari @endif
                </span>
                <span class="px-2 py-0.5 rounded-full font-medium
                    {{ ($c->status ?? '') === 'disetujui' ? 'bg-green-50 text-green-600' :
                       (($c->status ?? '') === 'ditolak'  ? 'bg-red-50 text-red-600' :
                        'bg-yellow-50 text-yellow-600') }}">
                    {{ ucfirst($c->status ?? '-') }}
                </span>
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-xs text-gray-400">Belum ada pengajuan cuti.</p>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Lokasi perusahaan dari server
@php
    $lokasiEss = \App\Models\LokasiAbsensi::aktif()->get(['nama','lat','lng','radius_meter'])
        ->map(fn($l) => ['nama'=>$l->nama,'lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'radius_meter'=>$l->radius_meter]);
@endphp
const lokasiPerusahaan = @json($lokasiEss);

function essAbsensi() {
    return {
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
            this.map = L.map('mapEss');
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(this.map);

            // Plot lokasi perusahaan
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

            // Auto ambil lokasi saat halaman load
            this.$nextTick(() => this.ambilLokasi());
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
                    this.akurasi = Math.round(pos.coords.accuracy);
                    this.lokasiku = { lat, lng };

                    // Hitung jarak ke lokasi terdekat
                    if (lokasiPerusahaan.length > 0) {
                        const jarakList = lokasiPerusahaan.map(l => this.haversine(l.lat, l.lng, lat, lng));
                        this.jarakMeter  = Math.round(Math.min(...jarakList));
                        const minIdx     = jarakList.indexOf(Math.min(...jarakList));
                        this.statusRadius = jarakList[minIdx] <= lokasiPerusahaan[minIdx].radius_meter;
                    } else {
                        this.statusRadius = true; // tidak ada lokasi = bebas
                    }

                    // Update peta
                    if (this.userMarker) this.map.removeLayer(this.userMarker);
                    if (this.userCircle) this.map.removeLayer(this.userCircle);

                    this.userMarker = L.circleMarker([lat, lng], {
                        radius: 8, color: this.statusRadius ? '#16a34a' : '#ea580c',
                        fillColor: this.statusRadius ? '#22c55e' : '#f97316', fillOpacity: 0.8
                    }).addTo(this.map).bindPopup('Lokasi Anda');

                    this.userCircle = L.circle([lat, lng], {
                        radius: this.akurasi, color: '#6b7280', fillOpacity: 0.08, weight: 1
                    }).addTo(this.map);

                    this.map.setView([lat, lng], 17);
                },
                err => {
                    this.loadingGps = false;
                    const msg = {
                        1: 'Izin GPS ditolak. Aktifkan lokasi di browser.',
                        2: 'Posisi tidak tersedia.',
                        3: 'Timeout mengambil lokasi.'
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
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ lat: this.lokasiku.lat, lng: this.lokasiku.lng }),
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                this.loading = false;
                if (ok) {
                    this.setNotif(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.setNotif(data.message || 'Gagal check-in.', 'error');
                }
            })
            .catch(() => {
                this.loading = false;
                this.setNotif('Terjadi kesalahan koneksi.', 'error');
            });
        },

        doCheckOut() {
            if (!this.lokasiku || this.loading) return;
            this.loading = true;
            this.notif   = { msg: '', type: '' };

            fetch('{{ route('ess.checkout') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ lat: this.lokasiku.lat, lng: this.lokasiku.lng }),
            })
            .then(r => r.json().then(d => ({ ok: r.ok, data: d })))
            .then(({ ok, data }) => {
                this.loading = false;
                if (ok) {
                    this.setNotif(data.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.setNotif(data.message || 'Gagal check-out.', 'error');
                }
            })
            .catch(() => {
                this.loading = false;
                this.setNotif('Terjadi kesalahan koneksi.', 'error');
            });
        },

        haversine(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const φ1 = lat1 * Math.PI / 180, φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(Δφ/2)**2 + Math.cos(φ1)*Math.cos(φ2)*Math.sin(Δλ/2)**2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        },

        setNotif(msg, type) {
            this.notif = { msg, type };
            if (type === 'success') setTimeout(() => this.notif = { msg: '', type: '' }, 5000);
        },
    };
}
</script>
@endpush
