@extends('layouts.app')
@section('title', 'Setting Tarif Lembur')
@section('page-title', 'Setting Tarif Lembur')
@section('page-subtitle', 'Tarif lembur per jam berdasarkan departemen')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
        @endif

        <div class="mb-5">
            <p class="text-sm text-gray-500">
                Atur tarif lembur per jam untuk setiap departemen.
                <span class="font-medium text-gray-700">HB</span> = Hari Biasa,
                <span class="font-medium text-gray-700">HR</span> = Hari Raya/Libur.
            </p>
        </div>

        <form method="POST" action="{{ route('lembur.setting.update') }}" class="space-y-3">
            @csrf

            <div class="grid grid-cols-12 gap-2 px-1 text-xs font-semibold text-gray-500 mb-1">
                <div class="col-span-5">Departemen</div>
                <div class="col-span-3 text-right">Tarif HB (Rp/jam)</div>
                <div class="col-span-3 text-right">Tarif HR (Rp/jam)</div>
                <div class="col-span-1"></div>
            </div>

            @foreach($departemen as $i => $dep)
            @php $tarif = $tarifMap[$dep->dep_id] ?? null; @endphp
            <div class="grid grid-cols-12 gap-2 items-center p-3 rounded-xl border border-gray-100 bg-gray-50/50">
                <input type="hidden" name="tarif[{{ $i }}][dep_id]" value="{{ $dep->dep_id }}">

                <div class="col-span-5">
                    <p class="text-sm font-medium text-gray-800">{{ $dep->nama }}</p>
                    <p class="text-xs text-gray-400">{{ $dep->dep_id }}</p>
                </div>
                <div class="col-span-3">
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                        <input type="number" name="tarif[{{ $i }}][hb]" min="0" step="1000"
                               value="{{ old("tarif.$i.hb", $tarif?->tarif_hb ?? 0) }}"
                               class="w-full pl-7 pr-2 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none text-right bg-white">
                    </div>
                </div>
                <div class="col-span-3">
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                        <input type="number" name="tarif[{{ $i }}][hr]" min="0" step="1000"
                               value="{{ old("tarif.$i.hr", $tarif?->tarif_hr ?? 0) }}"
                               class="w-full pl-7 pr-2 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none text-right bg-white">
                    </div>
                </div>
                <div class="col-span-1 text-center">
                    @if($tarif)
                    <span class="w-2 h-2 rounded-full bg-green-400 inline-block" title="Tarif sudah diset"></span>
                    @else
                    <span class="w-2 h-2 rounded-full bg-gray-200 inline-block" title="Belum diset"></span>
                    @endif
                </div>
            </div>
            @endforeach

            @if($departemen->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Tidak ada departemen ditemukan.</p>
            @endif

            <div class="pt-2 flex gap-2">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Simpan Tarif
                </button>
                <a href="{{ route('lembur.index') }}"
                   class="px-5 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
