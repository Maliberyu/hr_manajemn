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
                    <span>📍 {{ $iht->lokasi }}</span>
                    <span>📅 {{ $iht->tanggal_mulai->translatedFormat('d M Y') }}
                        @if(!$iht->tanggal_mulai->equalTo($iht->tanggal_selesai))
                        — {{ $iht->tanggal_selesai->translatedFormat('d M Y') }}
                        @endif
                    </span>
                    @if($iht->jam_mulai)
                    <span>🕐 {{ substr($iht->jam_mulai,0,5) }}
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
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">Daftar Peserta ({{ $peserta->count() }})</p>
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

        @if($peserta->isEmpty())
        <p class="px-5 py-8 text-center text-sm text-gray-400">Belum ada peserta terdaftar.</p>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Peserta</th>
                    <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600">Status</th>
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
                            @if(!$p->sudahSertifikat() && in_array($p->status, ['hadir','selesai']))
                            <form method="POST" action="{{ route('training.iht.peserta.sertifikat.generate', [$iht, $p]) }}"
                                  onsubmit="return confirm('Generate sertifikat untuk {{ $p->pegawai?->nama }}?')">
                                @csrf
                                <button type="submit"
                                        class="px-2.5 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Generate Sertifikat
                                </button>
                            </form>
                            @endif
                            @if($p->sudahSertifikat())
                            <a href="{{ route('training.iht.peserta.sertifikat.download', [$iht, $p]) }}"
                               class="px-2.5 py-1 text-xs bg-green-50 text-green-600 hover:bg-green-100 rounded-lg">
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
@endsection
