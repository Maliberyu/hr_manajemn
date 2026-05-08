@extends('layouts.app')
@section('title', 'Lokasi Absensi GPS')
@section('page-title', 'Lokasi Absensi')
@section('page-subtitle', 'Titik koordinat & radius yang diizinkan')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5"
     x-data="lokasiManager()">

    {{-- ── Form tambah/edit ──────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4" x-text="editId ? 'Edit Lokasi' : 'Tambah Lokasi Baru'"></h3>

            <form method="POST"
                  :action="editId ? '{{ parse_url(route('absensi.lokasi.update', ':id'), PHP_URL_PATH) }}'.replace(':id', editId) : '{{ parse_url(route('absensi.lokasi.store'), PHP_URL_PATH) }}'"
                  class="space-y-3">
                @csrf
                <input type="hidden" name="_method" :value="editId ? 'PUT' : 'POST'">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" x-model="form.nama" required maxlength="100"
                           placeholder="cth: Kantor Pusat"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                    <input type="text" name="alamat" x-model="form.alamat" maxlength="255"
                           placeholder="Alamat lengkap (opsional)"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Latitude <span class="text-red-500">*</span></label>
                        <input type="number" name="lat" x-model="form.lat" step="0.0000001" required
                               placeholder="-6.2000"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Longitude <span class="text-red-500">*</span></label>
                        <input type="number" name="lng" x-model="form.lng" step="0.0000001" required
                               placeholder="106.8166"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                </div>

                <p class="text-xs text-blue-600 cursor-pointer hover:underline" @click="getMyLocation()">
                     Gunakan lokasi saya sekarang
                </p>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Radius: <strong x-text="form.radius_meter + ' m'"></strong>
                    </label>
                    <input type="range" name="radius_meter" x-model="form.radius_meter"
                           min="10" max="2000" step="10"
                           class="w-full accent-blue-600"
                           @input="updatePreviewRadius()">
                    <div class="flex justify-between text-xs text-gray-400 mt-0.5">
                        <span>10 m</span><span>2.000 m</span>
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="submit"
                            class="flex-1 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                        Simpan Lokasi
                    </button>
                    <button type="button" x-show="editId" @click="resetForm()"
                            class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>

        {{-- Panduan --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-100 p-4 text-xs text-blue-700 space-y-1">
            <p class="font-semibold"> Cara set koordinat</p>
            <p>1. Klik tombol "Gunakan lokasi saya" di atas</p>
            <p>2. Atau klik titik di peta → koordinat terisi otomatis</p>
            <p>3. Geser slider untuk atur radius (lingkaran biru di peta)</p>
        </div>
    </div>

    {{-- ── Peta + Daftar ─────────────────────────────────────────────────── --}}
    <div class="lg:col-span-3 space-y-4">

        {{-- Peta Leaflet --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div id="map" class="w-full h-72 rounded-2xl"></div>
        </div>

        {{-- Daftar lokasi --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Daftar Lokasi</h3>
                <span class="text-xs text-gray-400">{{ $lokasi->count() }} lokasi</span>
            </div>

            @if($lokasi->isEmpty())
            <p class="text-sm text-gray-400 text-center py-10">Belum ada lokasi absensi.</p>
            @else
            <ul class="divide-y divide-gray-50">
                @foreach($lokasi as $l)
                <li class="px-5 py-4 flex items-center gap-3">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center {{ $l->aktif ? 'bg-green-50' : 'bg-gray-100' }}">
                        <svg class="w-5 h-5 {{ $l->aktif ? 'text-green-500' : 'text-gray-400' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800">{{ $l->nama }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ $l->alamat ?: 'Lat: '.$l->lat.' · Lng: '.$l->lng }}</p>
                        <p class="text-xs text-blue-600 font-medium">Radius {{ number_format($l->radius_meter) }} m</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        {{-- Toggle aktif --}}
                        <form method="POST" action="{{ parse_url(route('absensi.lokasi.toggle', $l), PHP_URL_PATH) }}">
                            @csrf @method('PATCH')
                            <button type="submit" title="{{ $l->aktif ? 'Nonaktifkan' : 'Aktifkan' }}"
                                    class="p-1.5 rounded-lg {{ $l->aktif ? 'text-green-600 hover:bg-green-50' : 'text-gray-400 hover:bg-gray-100' }} transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $l->aktif ? 'M5 13l4 4L19 7' : 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' }}"/>
                                </svg>
                            </button>
                        </form>
                        {{-- Edit --}}
                        <button type="button"
                                @click="editLokasi({{ $l->id }}, '{{ addslashes($l->nama) }}', '{{ addslashes($l->alamat) }}', {{ $l->lat }}, {{ $l->lng }}, {{ $l->radius_meter }})"
                                class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        {{-- Hapus --}}
                        <form method="POST" action="{{ parse_url(route('absensi.lokasi.destroy', $l), PHP_URL_PATH) }}"
                              onsubmit="return confirm('Hapus lokasi ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ─── Data lokasi dari server ───────────────────────────────────────────────
@php
    $lokasiJs = $lokasi->map(function($l) {
        return ['id'=>$l->id,'nama'=>$l->nama,'lat'=>(float)$l->lat,'lng'=>(float)$l->lng,'radius'=>$l->radius_meter,'aktif'=>(bool)$l->aktif];
    })->values();
@endphp
const serverLokasi = @json($lokasiJs);

// ─── Leaflet map ──────────────────────────────────────────────────────────
let map, previewMarker, previewCircle;
const markers = [];

document.addEventListener('DOMContentLoaded', () => {
    map = L.map('map').setView([-2.5, 118], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Plot existing locations
    serverLokasi.forEach(l => {
        const color = l.aktif ? '#16a34a' : '#9ca3af';
        L.circle([l.lat, l.lng], { radius: l.radius, color, fillOpacity: 0.15 }).addTo(map)
            .bindTooltip(`<b>${l.nama}</b><br>Radius: ${l.radius} m`);
        L.marker([l.lat, l.lng]).addTo(map).bindPopup(`<b>${l.nama}</b><br>Radius: ${l.radius} m`);
    });

    if (serverLokasi.length > 0) {
        const bounds = L.latLngBounds(serverLokasi.map(l => [l.lat, l.lng]));
        map.fitBounds(bounds.pad(0.3));
    }

    // Klik peta → isi koordinat
    map.on('click', e => {
        window.dispatchEvent(new CustomEvent('map-click', {
            detail: { lat: e.latlng.lat, lng: e.latlng.lng }
        }));
    });
});

// ─── Alpine component ─────────────────────────────────────────────────────
function lokasiManager() {
    return {
        editId: null,
        form: { nama: '', alamat: '', lat: '', lng: '', radius_meter: 100 },

        init() {
            window.addEventListener('map-click', e => {
                this.form.lat = e.detail.lat.toFixed(7);
                this.form.lng = e.detail.lng.toFixed(7);
                this.updatePreviewMarker();
            });
        },

        editLokasi(id, nama, alamat, lat, lng, radius) {
            this.editId = id;
            this.form   = { nama, alamat, lat, lng, radius_meter: radius };
            this.updatePreviewMarker();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        resetForm() {
            this.editId = null;
            this.form   = { nama: '', alamat: '', lat: '', lng: '', radius_meter: 100 };
            if (previewMarker) { map.removeLayer(previewMarker); previewMarker = null; }
            if (previewCircle) { map.removeLayer(previewCircle); previewCircle = null; }
        },

        getMyLocation() {
            if (!navigator.geolocation) return alert('Browser tidak mendukung GPS.');
            navigator.geolocation.getCurrentPosition(pos => {
                this.form.lat = pos.coords.latitude.toFixed(7);
                this.form.lng = pos.coords.longitude.toFixed(7);
                this.updatePreviewMarker();
            }, () => alert('Tidak dapat mengambil lokasi.'), { enableHighAccuracy: true });
        },

        updatePreviewMarker() {
            const lat = parseFloat(this.form.lat);
            const lng = parseFloat(this.form.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            if (previewMarker) map.removeLayer(previewMarker);
            if (previewCircle) map.removeLayer(previewCircle);

            previewMarker = L.marker([lat, lng], {
                icon: L.divIcon({ className: 'text-blue-600', html: '📍', iconSize: [24, 24] })
            }).addTo(map).bindPopup('Lokasi baru').openPopup();

            previewCircle = L.circle([lat, lng], {
                radius: parseInt(this.form.radius_meter) || 100,
                color: '#2563eb', fillOpacity: 0.15
            }).addTo(map);

            map.setView([lat, lng], 17);
        },

        updatePreviewRadius() {
            if (previewCircle) previewCircle.setRadius(parseInt(this.form.radius_meter));
        },
    };
}
</script>
@endpush
