@extends('layouts.app')
@section('title', 'Penilaian 360 Derajat')
@section('page-title', 'Penilaian Kinerja 360 Derajat')
@section('page-subtitle', 'Semester ' . $semester . ' / ' . $tahun)

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-3 items-center">
        <form method="GET" action="{{ route('kinerja.360.index') }}" class="flex gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Semester</label>
                <select name="semester" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none">
                    <option value="1" {{ $semester==1?'selected':'' }}>Semester 1</option>
                    <option value="2" {{ $semester==2?'selected':'' }}>Semester 2</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none">
                    @foreach(range(now()->year-1, now()->year+1) as $t)
                    <option value="{{ $t }}" {{ $tahun==$t?'selected':'' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition">Filter</button>
        </form>
        <a href="{{ route('kinerja.360.create') }}"
           class="ml-auto px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition font-semibold">
            + Buat Sesi 360°
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Pegawai</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Rater</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Sudah Isi</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Deadline</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Nilai Akhir</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sesiList as $s)
                @php
                    $total   = $s->raters->count();
                    $submit  = $s->raters->filter->sudahSubmit()->count();
                    $pct     = $total > 0 ? round(($submit / $total) * 100) : 0;
                @endphp
                <tr class="hover:bg-gray-50/50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $s->pegawai?->nama }}</div>
                        <div class="text-xs text-gray-400">{{ $s->pegawai?->jbtn }} · {{ $s->pegawai?->departemenRef?->nama }}</div>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-700 font-medium">{{ $total }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                <div class="bg-purple-500 h-1.5 rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="text-xs text-gray-600">{{ $submit }}/{{ $total }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center text-xs text-gray-500">
                        {{ $s->deadline?->translatedFormat('d M Y') ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($s->nilai_akhir)
                        <span class="font-bold text-gray-800">{{ $s->nilai_akhir }}</span>
                        <span class="text-xs text-gray-400">/100</span>
                        @else<span class="text-gray-300 text-xs">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $stColor = match($s->status) {
                                'aktif'   => 'bg-blue-100 text-blue-700',
                                'selesai' => 'bg-green-100 text-green-700',
                                default   => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <span class="px-2 py-1 rounded-xl text-xs font-semibold {{ $stColor }}">{{ ucfirst($s->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('kinerja.360.show', $s) }}"
                               class="px-2.5 py-1 text-xs bg-purple-50 text-purple-600 hover:bg-purple-100 rounded-lg">Detail</a>
                            @if($s->status === 'aktif')
                            <a href="{{ route('kinerja.360.rekap', $s) }}"
                               class="px-2.5 py-1 text-xs bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg">Rekap</a>
                            @endif
                            @if($s->status === 'selesai')
                            <a href="{{ route('kinerja.360.rekap', $s) }}"
                               class="px-2.5 py-1 text-xs bg-green-50 text-green-600 hover:bg-green-100 rounded-lg">Hasil</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">
                    Belum ada sesi 360° untuk periode ini.
                </td></tr>
                @endforelse
            </tbody>
        </table>
        @if($sesiList->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $sesiList->links() }}</div>
        @endif
    </div>
</div>
@endsection
