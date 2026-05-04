@extends('layouts.app')
@section('title', 'Penilaian Prestasi Kerja')
@section('page-title', 'Penilaian Prestasi Kerja')
@section('page-subtitle', 'Semester ' . $semester . ' / ' . $tahun)

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Filter + Actions --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <form method="GET" action="{{ route('kinerja.prestasi.index') }}" class="flex flex-wrap gap-3 items-end flex-1">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Semester</label>
                <select name="semester" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="1" {{ $semester==1?'selected':'' }}>Semester 1</option>
                    <option value="2" {{ $semester==2?'selected':'' }}>Semester 2</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    @foreach(range(now()->year-1, now()->year+1) as $t)
                    <option value="{{ $t }}" {{ $tahun==$t?'selected':'' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="">Semua</option>
                    <option value="draft" {{ request('status')==='draft'?'selected':'' }}>Draft</option>
                    <option value="final" {{ request('status')==='final'?'selected':'' }}>Final</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
        </form>
        <a href="{{ route('kinerja.prestasi.create', ['semester'=>$semester,'tahun'=>$tahun]) }}"
           class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
            + Buat Penilaian
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Pegawai</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Penilai</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Nilai Akhir</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Predikat</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($penilaian as $p)
                @php
                    $predikatColor = match($p->predikat) {
                        'Istimewa' => 'bg-blue-100 text-blue-700',
                        'Puas'     => 'bg-green-100 text-green-700',
                        'Biasa'    => 'bg-yellow-100 text-yellow-700',
                        'Kurang'   => 'bg-orange-100 text-orange-700',
                        default    => 'bg-red-100 text-red-700',
                    };
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ $p->pegawai?->nama }}</div>
                        <div class="text-xs text-gray-400">{{ $p->pegawai?->jbtn }} · {{ $p->pegawai?->departemenRef?->nama }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $p->penilai?->nama ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-lg font-bold text-gray-800">{{ $p->nilai_akhir ?? '-' }}</span>
                        @if($p->nilai_akhir)<span class="text-xs text-gray-400">/100</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($p->predikat)
                        <span class="px-2 py-1 rounded-xl text-xs font-semibold {{ $predikatColor }}">{{ $p->predikat }}</span>
                        @else<span class="text-gray-300 text-xs">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 rounded-xl text-xs font-semibold
                            {{ $p->status === 'final' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex justify-center gap-1">
                            <a href="{{ route('kinerja.prestasi.show', $p) }}"
                               class="px-2.5 py-1 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg">Detail</a>
                            @if($p->status === 'draft')
                            <a href="{{ route('kinerja.prestasi.edit', $p) }}"
                               class="px-2.5 py-1 text-xs bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg">Edit</a>
                            @endif
                            <a href="{{ route('kinerja.prestasi.pdf', $p) }}"
                               class="px-2.5 py-1 text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg">PDF</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-gray-400">
                    Belum ada penilaian untuk periode ini.
                    <a href="{{ route('kinerja.prestasi.create', ['semester'=>$semester,'tahun'=>$tahun]) }}"
                       class="text-blue-600 hover:underline ml-1">Buat sekarang →</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
        @if($penilaian->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $penilaian->links() }}</div>
        @endif
    </div>
</div>
@endsection
