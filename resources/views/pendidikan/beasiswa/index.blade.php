@extends('layouts.app')
@section('title', 'Pengajuan Bantuan Pendidikan & Beasiswa')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Bantuan Pendidikan & Beasiswa</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola pengajuan bantuan pendidikan dari karyawan</p>
        </div>
        <a href="{{ route('pendidikan.riwayat.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-600 rounded-xl text-sm hover:bg-gray-50 transition">
            Riwayat Pendidikan
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($menunggu > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-amber-700">
            <span class="font-semibold">{{ $menunggu }}</span> pengajuan menunggu keputusan HRD.
        </p>
    </div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-white border border-gray-200 rounded-2xl px-4 py-3 flex flex-wrap gap-3 items-end shadow-sm">
        <div class="w-44">
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Semua Status</option>
                @foreach(['menunggu_atasan'=>'Menunggu Atasan','menunggu_hrd'=>'Menunggu HRD','disetujui'=>'Disetujui','ditolak'=>'Ditolak','selesai'=>'Selesai'] as $v => $l)
                <option value="{{ $v }}" {{ request('status') == $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-40">
            <label class="block text-xs text-gray-500 mb-1">Jenis</label>
            <select name="jenis" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400">
                <option value="">Semua Jenis</option>
                @foreach(\App\Models\Beasiswa::jenisLabel() as $v => $l)
                <option value="{{ $v }}" {{ request('jenis') == $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">Filter</button>
        @if(request()->anyFilled(['status','jenis','nik']))
        <a href="{{ route('pendidikan.beasiswa.index') }}" class="px-3 py-2 text-gray-500 text-sm hover:text-gray-700">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        @if($beasiswas->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left">Program</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-right">Biaya Diajukan</th>
                        <th class="px-4 py-3 text-center">Tanggal Mulai</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($beasiswas as $b)
                    @php $c = $b->status_color; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $b->pegawai?->nama ?? $b->nik }}</p>
                            <p class="text-xs text-gray-400">{{ $b->nik }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-700">{{ $b->nama_program }}</p>
                            <p class="text-xs text-gray-400">{{ $b->institusi }}{{ $b->kota ? ', '.$b->kota : '' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-600">{{ $b->jenis_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            Rp {{ number_format($b->biaya_diajukan, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $b->tgl_mulai->isoFormat('D MMM Y') }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                bg-{{ $c }}-100 text-{{ $c }}-700 border border-{{ $c }}-200">
                                {{ $b->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('pendidikan.beasiswa.show', $b) }}"
                               class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $beasiswas->links() }}
        </div>
        @else
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-gray-400 text-sm">Belum ada pengajuan beasiswa.</p>
        </div>
        @endif
    </div>

</div>
@endsection
