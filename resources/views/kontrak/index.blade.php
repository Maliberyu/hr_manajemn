@extends('layouts.app')
@section('title', 'Kontrak Kerja')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Kontrak Kerja</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola kontrak kerja seluruh pegawai</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('kontrak.master-jenis') }}"
               class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 rounded-xl text-sm text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Master Jenis
            </a>
            <a href="{{ route('kontrak.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Kontrak
            </a>
        </div>
    </div>

    {{-- Alert akan berakhir --}}
    @if($akanBerakhir->count())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
        <div class="flex items-center gap-2 mb-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <span class="text-sm font-semibold text-amber-700">{{ $akanBerakhir->count() }} kontrak akan berakhir dalam 30 hari</span>
        </div>
        <div class="space-y-1.5">
            @foreach($akanBerakhir as $k)
            <div class="flex items-center justify-between bg-white border border-amber-100 rounded-xl px-3 py-2">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium bg-amber-100 text-amber-700 px-2 py-0.5 rounded-lg">{{ $k->jenis?->nama }}</span>
                    <span class="text-sm text-gray-800">{{ $k->pegawai?->nama ?? $k->nik }}</span>
                    <span class="text-xs text-gray-400">{{ $k->pegawai?->jbtn }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold {{ $k->sisa_hari <= 7 ? 'text-red-600' : 'text-amber-600' }}">
                        H-{{ $k->sisa_hari }}
                    </span>
                    <span class="text-xs text-gray-500">{{ $k->tgl_selesai->isoFormat('D MMM Y') }}</span>
                    <a href="{{ route('kontrak.show', $k) }}" class="text-xs text-blue-600 hover:underline">Detail</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 bg-white border border-gray-200 rounded-2xl px-4 py-3">
        <input type="text" name="nik" value="{{ request('nik') }}" placeholder="Cari NIK / Nama..."
               class="flex-1 min-w-[160px] px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        <select name="jenis_id" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Jenis</option>
            @foreach($jenisList as $j)
            <option value="{{ $j->id }}" @selected(request('jenis_id') == $j->id)>{{ $j->nama }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Status</option>
            <option value="aktif"      @selected(request('status')=='aktif')>Aktif</option>
            <option value="berakhir"   @selected(request('status')=='berakhir')>Berakhir</option>
            <option value="diperbarui" @selected(request('status')=='diperbarui')>Diperbarui</option>
            <option value="dibatalkan" @selected(request('status')=='dibatalkan')>Dibatalkan</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700 transition">Filter</button>
        @if(request()->hasAny(['nik','jenis_id','status']))
        <a href="{{ route('kontrak.index') }}" class="px-4 py-2 border border-gray-300 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Pegawai</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Jenis</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">No. Kontrak</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Periode</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Sisa</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($kontraks as $k)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $k->pegawai?->nama ?? $k->nik }}</p>
                        <p class="text-xs text-gray-400">{{ $k->nik }} · {{ $k->pegawai?->jbtn }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-50 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-lg">{{ $k->jenis?->nama }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $k->no_kontrak ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-700">
                        <span>{{ $k->tgl_mulai->isoFormat('D MMM Y') }}</span>
                        @if($k->tgl_selesai)
                        <span class="text-gray-400"> s/d </span>
                        <span>{{ $k->tgl_selesai->isoFormat('D MMM Y') }}</span>
                        @else
                        <span class="text-gray-400 text-xs"> (Tetap)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($k->sisa_hari !== null)
                            <span class="text-xs font-semibold {{ $k->sisa_hari <= 7 ? 'text-red-600' : ($k->sisa_hari <= 30 ? 'text-amber-600' : 'text-gray-600') }}">
                                H-{{ $k->sisa_hari }}
                            </span>
                        @else
                            <span class="text-gray-400 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $colors = ['aktif'=>'green','berakhir'=>'red','diperbarui'=>'blue','dibatalkan'=>'gray'];
                            $c = $colors[$k->status] ?? 'gray';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium
                            bg-{{ $c }}-50 text-{{ $c }}-700 border border-{{ $c }}-200">
                            {{ $k->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('kontrak.show', $k) }}"
                           class="text-xs text-blue-600 hover:underline font-medium">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">
                        Belum ada data kontrak.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($kontraks->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $kontraks->links() }}</div>
        @endif
    </div>
</div>
@endsection
