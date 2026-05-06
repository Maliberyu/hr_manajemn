@extends('layouts.app')
@section('title', 'Lembur')
@section('page-title', 'Manajemen Lembur')
@section('page-subtitle', 'Pengajuan dan persetujuan lembur karyawan')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')
<div class="space-y-4">

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $totalAtasan }}</div>
            <div class="text-xs text-yellow-600 mt-0.5">Menunggu Atasan</div>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $totalHrd }}</div>
            <div class="text-xs text-blue-600 mt-0.5">Menunggu HRD</div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-gray-700">{{ $lembur->total() }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Ditampilkan</div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 flex flex-col gap-1">
            <a href="{{ route('lembur.create') }}"
               class="block text-center py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                + Ajukan Lembur
            </a>
            <a href="{{ route('lembur.rekap') }}"
               class="block text-center py-1.5 text-xs border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Rekap Bulanan
            </a>
            @if(auth()->user()->hasRole(['hrd','admin']))
            <a href="{{ route('lembur.setting') }}"
               class="block text-center py-1.5 text-xs border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Setting Tarif
            </a>
            @endif
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filter --}}
    <form method="GET" action="{{ route('lembur.index') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Cari Pegawai</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama pegawai..."
                   class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none w-44">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">Semua Status</option>
                @foreach(\App\Models\Lembur::STATUS as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Bulan</label>
            <select name="bulan" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">Semua</option>
                @foreach(range(1,12) as $b)
                <option value="{{ $b }}" {{ request('bulan') == $b ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null,$b)->translatedFormat('F') }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Tahun</label>
            <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">Semua</option>
                @foreach(range(now()->year-1, now()->year+1) as $t)
                <option value="{{ $t }}" {{ request('tahun') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
            Filter
        </button>
        <a href="{{ route('lembur.index') }}"
           class="px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition">
            Reset
        </a>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Pegawai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Durasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Jenis</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Nominal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($lembur as $l)
                    @php
                        $color = $l->status_color;
                        $badgeCls = match($color) {
                            'yellow' => 'bg-yellow-100 text-yellow-700',
                            'blue'   => 'bg-blue-100 text-blue-700',
                            'green'  => 'bg-green-100 text-green-700',
                            'red'    => 'bg-red-100 text-red-700',
                            default  => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition" x-data="{ tolakOpen: false, level: '' }">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $l->pegawai?->nama ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $l->pegawai?->jbtn }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $l->tanggal?->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ $l->jam_mulai }} – {{ $l->jam_selesai }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-700">
                            {{ $l->durasi_label }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-lg text-xs font-medium
                                {{ $l->jenis === 'HR' ? 'bg-orange-100 text-orange-700' : 'bg-blue-50 text-blue-600' }}">
                                {{ $l->jenis }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700 font-medium">
                            Rp {{ number_format($l->nominal ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded-xl text-xs font-semibold {{ $badgeCls }}">
                                {{ $l->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                {{-- ACC & Tolak Atasan --}}
                                @if($l->bisaApproveAtasan())
                                <form method="POST" action="{{ route('lembur.approve.atasan', $l) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                                        ✓ ACC
                                    </button>
                                </form>
                                <button type="button"
                                        @click="tolakOpen = !tolakOpen; level = 'atasan'"
                                        class="px-2.5 py-1 text-xs bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg font-medium transition">
                                    ✗ Tolak
                                </button>
                                @endif

                                {{-- ACC & Tolak HRD --}}
                                @if($l->bisaApproveHrd())
                                <form method="POST" action="{{ route('lembur.approve.hrd', $l) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-2.5 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                                        ✓ ACC HRD
                                    </button>
                                </form>
                                <button type="button"
                                        @click="tolakOpen = !tolakOpen; level = 'hrd'"
                                        class="px-2.5 py-1 text-xs bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg font-medium transition">
                                    ✗ Tolak
                                </button>
                                @endif

                                <a href="{{ route('lembur.show', $l) }}"
                                   class="px-2.5 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition">
                                    Detail
                                </a>
                            </div>

                            {{-- Form tolak inline --}}
                            <div x-show="tolakOpen" x-cloak class="mt-2">
                                <form method="POST"
                                      :action="level === 'atasan'
                                          ? '{{ route('lembur.approve.atasan', $l) }}'
                                          : '{{ route('lembur.approve.hrd', $l) }}'">
                                    @csrf
                                    {{-- Gunakan hidden field untuk override ke tolak --}}
                                    <input type="hidden" name="_tolak" value="1">
                                    <div x-show="level === 'atasan'">
                                        <input type="hidden" name="_route_tolak_atasan"
                                               value="{{ route('lembur.tolak.atasan', $l) }}">
                                    </div>
                                    <div class="flex gap-1.5 items-end">
                                        <textarea :name="level === 'atasan' ? 'catatan_atasan' : 'catatan_hrd'"
                                                  rows="2" required placeholder="Alasan penolakan..."
                                                  class="flex-1 px-2 py-1 text-xs border border-red-200 rounded-lg focus:outline-none resize-none"></textarea>
                                        <button type="submit"
                                                :formaction="level === 'atasan'
                                                    ? '{{ route('lembur.tolak.atasan', $l) }}'
                                                    : '{{ route('lembur.tolak.hrd', $l) }}'"
                                                class="px-3 py-1.5 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex-shrink-0">
                                            Kirim
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada data lembur yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($lembur->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $lembur->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
