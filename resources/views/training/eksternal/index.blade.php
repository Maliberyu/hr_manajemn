@extends('layouts.app')
@section('title', 'Training Eksternal')
@section('page-title', 'Training Eksternal')
@section('page-subtitle', 'Pengajuan & riwayat training di luar institusi')

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Badge Summary --}}
    @if($pendingAtasan + $pendingHrd + $pendingValidasi > 0)
    <div class="flex flex-wrap gap-3">
        @if($pendingAtasan)
        <div class="px-3 py-2 bg-yellow-50 border border-yellow-200 rounded-xl text-xs font-medium text-yellow-700">
            ⏳ {{ $pendingAtasan }} menunggu persetujuan atasan
        </div>
        @endif
        @if($pendingHrd)
        <div class="px-3 py-2 bg-orange-50 border border-orange-200 rounded-xl text-xs font-medium text-orange-700">
            ⏳ {{ $pendingHrd }} menunggu persetujuan HRD
        </div>
        @endif
        @if($pendingValidasi)
        <div class="px-3 py-2 bg-purple-50 border border-purple-200 rounded-xl text-xs font-medium text-purple-700">
            🔍 {{ $pendingValidasi }} sertifikat menunggu validasi HR
        </div>
        @endif
    </div>
    @endif

    {{-- Filter + Aksi --}}
    <div class="flex flex-wrap gap-3 items-end">
        <form method="GET" action="{{ route('training.eksternal.index') }}" class="flex gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama training / lembaga..."
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none w-48">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                    <option value="">Semua</option>
                    @foreach(\App\Models\TrainingEksternal::STATUS_LABEL as $k => $v)
                    <option value="{{ $k }}" {{ request('status')===$k?'selected':'' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition">Filter</button>
        </form>
        <a href="{{ route('training.eksternal.create') }}"
           class="ml-auto px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
            + Ajukan Training
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Karyawan</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Training</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Mode</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Tanggal</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Masa Berlaku</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($list as $item)
                @php
                    $expired = $item->isExpired();
                    $expiring = !$expired && $item->isExpiringSoon();
                @endphp
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $item->pegawai?->nama }}</div>
                        <div class="text-xs text-gray-400">{{ $item->pegawai?->jbtn }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $item->nama_training }}</div>
                        <div class="text-xs text-gray-400">{{ $item->lembaga }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 text-xs rounded-lg
                            {{ $item->mode === 'rekam_langsung' ? 'bg-gray-100 text-gray-600' : 'bg-blue-50 text-blue-600' }}">
                            {{ $item->mode === 'rekam_langsung' ? 'Rekam' : 'Pengajuan' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $item->tanggal_mulai->translatedFormat('d M Y') }}
                        @if(!$item->tanggal_mulai->equalTo($item->tanggal_selesai))
                        <br>s/d {{ $item->tanggal_selesai->translatedFormat('d M Y') }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-xs">
                        @if($item->masa_berlaku)
                        <span class="{{ $expired ? 'text-red-600 font-semibold' : ($expiring ? 'text-orange-600 font-medium' : 'text-gray-500') }}">
                            {{ $item->masa_berlaku->translatedFormat('d M Y') }}
                            @if($expired) <br><span class="text-red-400">(Expired)</span>
                            @elseif($expiring) <br><span class="text-orange-400">({{ $item->masa_berlaku->diffInDays(now()) }}h lagi)</span>
                            @endif
                        </span>
                        @else
                        <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-xl
                            {{ \App\Models\TrainingEksternal::STATUS_COLOR[$item->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ \App\Models\TrainingEksternal::STATUS_LABEL[$item->status] ?? $item->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('training.eksternal.show', $item) }}"
                           class="px-2.5 py-1 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">
                    Belum ada data training eksternal.
                </td></tr>
                @endforelse
            </tbody>
        </table>
        @if($list->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $list->links() }}</div>
        @endif
    </div>
</div>
@endsection
