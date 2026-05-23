@extends('layouts.app')
@section('title', 'Detail IHT')
@section('page-title', 'Detail IHT')
@section('page-subtitle', $iht->nama_training)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">{{ $iht->nama_training }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $iht->penyelenggara }}
                    @if($iht->pemateri) · Pemateri: <span class="font-medium text-gray-700">{{ $iht->pemateri }}</span>@endif
                </p>
                <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500">
                    <span> {{ $iht->lokasi }}</span>
                    <span> {{ $iht->tanggal_mulai->translatedFormat('d M Y') }}
                        @if(!$iht->tanggal_mulai->equalTo($iht->tanggal_selesai))
                        — {{ $iht->tanggal_selesai->translatedFormat('d M Y') }}
                        @endif
                    </span>
                    @if($iht->jam_mulai)
                    <span> {{ substr($iht->jam_mulai,0,5) }}
                        @if($iht->jam_selesai)— {{ substr($iht->jam_selesai,0,5) }}@endif
                    </span>
                    @endif
                    @if($iht->kuota)<span>👥 Kuota: {{ $iht->kuota }}</span>@endif
                </div>
                @if($iht->deskripsi)
                <p class="mt-3 text-xs text-gray-500 leading-relaxed">{{ $iht->deskripsi }}</p>
                @endif
                @if($iht->penandatangan_nama)
                <p class="mt-2 text-xs text-gray-400">
                    Penandatangan sertifikat: <span class="font-medium text-gray-600">{{ $iht->penandatangan_nama }}</span>
                    @if($iht->penandatangan_jabatan)({{ $iht->penandatangan_jabatan }})@endif
                </p>
                @endif
            </div>
            <div class="flex flex-col items-end gap-2">
                <span class="px-3 py-1 text-xs font-semibold rounded-xl {{ \App\Models\IHT::STATUS_COLOR[$iht->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ \App\Models\IHT::STATUS[$iht->status] ?? $iht->status }}
                </span>
                @if($iht->status !== 'selesai' && $iht->status !== 'dibatalkan')
                <a href="{{ route('training.iht.edit', $iht) }}"
                   class="text-xs text-blue-500 hover:text-blue-700">Edit</a>
                @endif
            </div>
        </div>
    </div>

    {{-- QR Code Absensi --}}
    @if(in_array($iht->status, ['aktif','selesai']))
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5"
         x-data="qrAbsensi({{ $iht->id }})">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-800">QR Absensi Peserta</h3>
                <p class="text-xs text-gray-400 mt-0.5">Peserta scan QR ini dari HP untuk absensi. Berlaku 24 jam.</p>
            </div>
            <button @click="loadQr()"
                    :class="shown ? 'bg-gray-100 text-gray-600' : 'bg-blue-600 text-white hover:bg-blue-700'"
                    class="px-4 py-2 text-xs font-semibold rounded-xl transition"
                    x-text="shown ? 'Sembunyikan QR' : 'Tampilkan QR Absensi'">
            </button>
        </div>

        <div x-show="shown" x-cloak>
            <div x-show="loading" class="text-center py-6 text-sm text-gray-400">
                Memuat QR code...
            </div>

            <div x-show="!loading" class="grid grid-cols-2 gap-6">
                {{-- QR Masuk --}}
                <div class="text-center">
                    <div class="mb-3">
                        <span class="inline-block px-3 py-1 text-xs font-bold bg-blue-100 text-blue-700 rounded-full">
                            ABSENSI MASUK
                        </span>
                    </div>
                    <div id="qrMasuk" class="flex justify-center mb-2"></div>
                    <p class="text-xs text-gray-400">Scan saat tiba</p>
                </div>

                {{-- QR Selesai --}}
                <div class="text-center">
                    <div class="mb-3">
                        <span class="inline-block px-3 py-1 text-xs font-bold bg-orange-100 text-orange-700 rounded-full">
                            ABSENSI SELESAI
                        </span>
                    </div>
                    <div id="qrSelesai" class="flex justify-center mb-2"></div>
                    <p class="text-xs text-gray-400">Scan saat pulang</p>
                </div>
            </div>

            <div x-show="!loading" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-xl text-xs text-yellow-700 flex items-start gap-2">
                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Peserta scan QR → login dengan akun HR Manajemen → konfirmasi absensi. QR berlaku 24 jam sejak dibuka.</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Tambah Peserta --}}
    @if($iht->status === 'aktif' && $pegawaiBelum->count())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5" x-data="{ open: false }">
        <button @click="open = !open"
                class="flex items-center justify-between w-full text-sm font-semibold text-gray-700">
            <span>+ Tambah Peserta</span>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-cloak class="mt-4">
            <form method="POST" action="{{ route('training.iht.peserta.store', $iht) }}">
                @csrf
                <div class="max-h-48 overflow-y-auto space-y-1.5 mb-3">
                    @foreach($pegawaiBelum as $p)
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="pegawai_ids[]" value="{{ $p->id }}"
                               class="rounded text-blue-600">
                        <div>
                            <span class="text-sm font-medium text-gray-700">{{ $p->nama }}</span>
                            <span class="text-xs text-gray-400 ml-1">{{ $p->jbtn }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                <button type="submit"
                        class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                    Daftarkan Peserta Terpilih
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Daftar Peserta --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
            <p class="text-sm font-semibold text-gray-700">Daftar Peserta ({{ $peserta->count() }})</p>
            <div class="flex items-center gap-2">
                @if($peserta->isNotEmpty())
                <a href="{{ route('training.iht.cetak-peserta', $iht) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Peserta
                </a>
                @endif
                @if($iht->status === 'aktif')
                <form method="POST" action="{{ route('training.iht.tutup', $iht) }}"
                      onsubmit="return confirm('Tutup IHT ini sekarang?')">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Tutup IHT
                    </button>
                </form>
                @endif
            </div>
        </div>

        @if($peserta->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-400">Belum ada peserta terdaftar.</p>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Peserta</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-blue-600">Masuk</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-orange-500">Selesai</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600">Durasi</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600">Nilai</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600">No. Sertifikat</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($peserta as $p)
                @php
                    $stColor = match($p->status) {
                        'hadir','selesai' => 'bg-green-100 text-green-700',
                        'tidak_hadir'     => 'bg-red-100 text-red-600',
                        default           => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $p->pegawai?->nama }}</div>
                        <div class="text-xs text-gray-400">{{ $p->pegawai?->jbtn }} · {{ $p->pegawai?->departemenRef?->nama }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('training.iht.peserta.status', [$iht, $p]) }}" class="inline-flex items-center gap-1">
                            @csrf @method('PUT')
                            <select name="status" onchange="this.form.submit()"
                                    class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white focus:outline-none">
                                @foreach(\App\Models\IHTPeserta::STATUS as $k => $v)
                                <option value="{{ $k }}" {{ $p->status === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    {{-- Jam Masuk --}}
                    <td class="px-3 py-3 text-center">
                        @if($p->check_in_at)
                        <span class="text-sm font-bold text-blue-700">{{ $p->check_in_at->format('H:i') }}</span>
                        <span class="block text-[10px] text-gray-400">{{ $p->check_in_at->format('d/m') }}</span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    {{-- Jam Selesai --}}
                    <td class="px-3 py-3 text-center">
                        @if($p->check_out_at)
                        <span class="text-sm font-bold text-orange-600">{{ $p->check_out_at->format('H:i') }}</span>
                        <span class="block text-[10px] text-gray-400">{{ $p->check_out_at->format('d/m') }}</span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    {{-- Durasi --}}
                    <td class="px-3 py-3 text-center">
                        @if($p->durasi_hadir)
                        <span class="text-xs font-semibold text-green-700">{{ $p->durasi_hadir }}</span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    {{-- Nilai --}}
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('training.iht.peserta.status', [$iht, $p]) }}" class="inline-flex items-center gap-1">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $p->status }}">
                            <input type="number" name="nilai" value="{{ $p->nilai }}" min="0" max="100" step="0.01"
                                   placeholder="—" class="w-16 text-xs text-center border border-gray-200 rounded-lg px-1 py-1 focus:outline-none">
                            <button type="submit" class="text-xs text-blue-500 hover:text-blue-700">✓</button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-center text-xs">
                        @if($p->nomor_sertifikat)
                        <span class="font-mono text-green-700">{{ $p->nomor_sertifikat }}</span>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            @if(in_array($p->status, ['hadir','selesai']))
                            {{-- Preview (selalu tampil jika hadir/selesai) --}}
                            <a href="{{ route('training.iht.peserta.sertifikat.preview', [$iht, $p]) }}"
                               target="_blank"
                               class="px-2.5 py-1 text-xs bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg transition">
                                Preview
                            </a>
                            @endif
                            @if(!$p->sudahSertifikat() && in_array($p->status, ['hadir','selesai']))
                            <form method="POST" action="{{ route('training.iht.peserta.sertifikat.generate', [$iht, $p]) }}"
                                  onsubmit="return confirm('Generate & simpan sertifikat untuk {{ $p->pegawai?->nama }}?')">
                                @csrf
                                <button type="submit"
                                        class="px-2.5 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Generate
                                </button>
                            </form>
                            @endif
                            @if($p->sudahSertifikat())
                            <a href="{{ route('training.iht.peserta.sertifikat.download', [$iht, $p]) }}"
                               class="px-2.5 py-1 text-xs bg-green-50 text-green-600 hover:bg-green-100 rounded-lg border border-green-200 transition">
                                <svg class="w-3 h-3 inline-block mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Download
                            </a>
                            @endif
                            @if(!$p->sudahSertifikat())
                            <form method="POST" action="{{ route('training.iht.peserta.destroy', [$iht, $p]) }}"
                                  onsubmit="return confirm('Hapus peserta ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-xs text-red-400 hover:text-red-600">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <a href="{{ route('training.iht.index') }}"
       class="inline-block text-sm text-gray-500 hover:text-gray-700 transition">← Kembali ke Daftar IHT</a>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@push('scripts')
{{-- QRCode.js untuk generate QR di browser --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
function qrAbsensi(ihtId) {
    return {
        shown:   false,
        loading: false,
        qrMasuk:   null,
        qrSelesai: null,

        // Path-only (tanpa domain) — agar tidak tergantung APP_URL, jalan di server manapun
        urlMasuk:   '{{ parse_url(route("training.iht.peserta.absensi.url", [$iht->id, "masuk"]), PHP_URL_PATH) }}',
        urlSelesai: '{{ parse_url(route("training.iht.peserta.absensi.url", [$iht->id, "selesai"]), PHP_URL_PATH) }}',
        csrf:       '{{ csrf_token() }}',

        async loadQr() {
            this.shown = !this.shown;
            if (!this.shown || this.qrMasuk) return;

            this.loading = true;
            await this.$nextTick();

            try {
                const headers = { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' };
                const [resMasuk, resSelesai] = await Promise.all([
                    fetch(this.urlMasuk,   { headers }),
                    fetch(this.urlSelesai, { headers }),
                ]);

                const dataMasuk   = await resMasuk.json();
                const dataSelesai = await resSelesai.json();

                this.loading = false;
                await this.$nextTick();

                // Generate QR masuk
                document.getElementById('qrMasuk').innerHTML = '';
                new QRCode(document.getElementById('qrMasuk'), {
                    text:   dataMasuk.url,
                    width:  180,
                    height: 180,
                    colorDark:  '#1d4ed8',
                    colorLight: '#eff6ff',
                    correctLevel: QRCode.CorrectLevel.M,
                });

                // Generate QR selesai
                document.getElementById('qrSelesai').innerHTML = '';
                new QRCode(document.getElementById('qrSelesai'), {
                    text:   dataSelesai.url,
                    width:  180,
                    height: 180,
                    colorDark:  '#ea580c',
                    colorLight: '#fff7ed',
                    correctLevel: QRCode.CorrectLevel.M,
                });

                this.qrMasuk   = dataMasuk.url;
                this.qrSelesai = dataSelesai.url;

            } catch (e) {
                this.loading = false;
                alert('Gagal memuat QR code. Coba lagi.');
            }
        }
    };
}
</script>
@endpush
@endsection
