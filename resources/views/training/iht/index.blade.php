@extends('layouts.app')
@section('title', 'IHT — In-House Training')
@section('page-title', 'In-House Training (IHT)')
@section('page-subtitle', 'Daftar program pelatihan internal')

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filter + Aksi --}}
    <div class="flex flex-wrap gap-3 items-end">
        <form method="GET" action="{{ route('training.iht.index') }}" class="flex gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cari</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama training..."
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none w-48">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\IHT::STATUS as $k => $v)
                    <option value="{{ $k }}" {{ request('status')===$k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm bg-gray-600 text-white rounded-xl hover:bg-gray-700 transition">Filter</button>
        </form>

        <div class="ml-auto flex gap-2">
            <a href="{{ route('training.iht.create') }}"
               class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
                + Buat IHT
            </a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Training</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Pemateri</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Tanggal</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Peserta</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($ihtList as $iht)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $iht->nama_training }}</div>
                        <div class="text-xs text-gray-400">{{ $iht->penyelenggara }} · {{ $iht->lokasi }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $iht->pemateri ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $iht->tanggal_mulai->translatedFormat('d M Y') }}
                        @if(!$iht->tanggal_mulai->equalTo($iht->tanggal_selesai))
                        <br><span class="text-gray-400">s/d {{ $iht->tanggal_selesai->translatedFormat('d M Y') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-semibold text-gray-700">{{ $iht->peserta_count }}</span>
                        @if($iht->kuota)<span class="text-xs text-gray-400">/{{ $iht->kuota }}</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-xl {{ \App\Models\IHT::STATUS_COLOR[$iht->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ \App\Models\IHT::STATUS[$iht->status] ?? $iht->status }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('training.iht.show', $iht) }}"
                           class="px-3 py-1 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg">Detail</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">
                    Belum ada data IHT.
                </td></tr>
                @endforelse
            </tbody>
        </table>
        @if($ihtList->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $ihtList->links() }}</div>
        @endif
    </div>
</div>
@endsection
