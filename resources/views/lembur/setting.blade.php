@extends('layouts.app')
@section('title', 'Setting Lembur')
@section('page-title', 'Setting Lembur')
@section('page-subtitle', 'Aturan perhitungan, tarif, dan batas jam lembur')

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

<form action="{{ route('lembur.setting.update') }}" method="POST">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

    {{-- ── Panel Kiri: Master Setting ──────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Metode Perhitungan --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M12 7h.01M9 7H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4"/></svg>
                <h3 class="text-sm font-semibold text-gray-800">Metode Perhitungan Otomatis</h3>
            </div>
            <div class="p-5 space-y-2.5">
                @foreach(\App\Models\LemburSetting::METODE as $val => $label)
                @php
                    $active = $setting->metode === $val;
                    $titles = [
                        'keduanya'   => 'Otomatis (Rekomendasi)',
                        'shift'      => 'Selalu Berdasarkan Shift',
                        'jam_aktual' => 'Selalu Berdasarkan Jam Aktual',
                    ];
                @endphp
                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-xl border-2 transition-colors {{ $active ? 'border-blue-400 bg-blue-50/50' : 'border-gray-100 hover:border-gray-200' }}">
                    <input type="radio" name="metode" value="{{ $val }}" {{ $active ? 'checked' : '' }}
                           class="mt-0.5 text-blue-600 focus:ring-blue-300">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $titles[$val] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Batas Jam --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 class="text-sm font-semibold text-gray-800">Batas Jam Lembur</h3>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Min. Jam Aktual</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_jam_lembur" min="0" max="12" step="0.5"
                                   value="{{ $setting->min_jam_lembur }}"
                                   class="w-24 border border-gray-200 rounded-xl px-3 py-2 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span class="text-sm text-gray-500">jam</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Jika &lt; ini → tidak dihitung</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Min. Overtime Shift</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="min_jam_shift" min="0" max="4" step="0.25"
                                   value="{{ $setting->min_jam_shift }}"
                                   class="w-24 border border-gray-200 rounded-xl px-3 py-2 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span class="text-sm text-gray-500">jam</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Melewati jam selesai shift</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Maks. Harian</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="max_jam_harian" min="1" max="24" step="0.5"
                                   value="{{ $setting->max_jam_harian }}"
                                   class="w-24 border border-gray-200 rounded-xl px-3 py-2 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-orange-300">
                            <span class="text-sm text-gray-500">jam/hari</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Maks. Mingguan</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="max_jam_mingguan" min="1" max="72" step="1"
                                   value="{{ $setting->max_jam_mingguan }}"
                                   class="w-24 border border-gray-200 rounded-xl px-3 py-2 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-orange-300">
                            <span class="text-sm text-gray-500">jam/minggu</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Dipotong otomatis jika melewati</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Formula & Approval --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <h3 class="text-sm font-semibold text-gray-800">Formula Upah & Approval</h3>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-2">Formula Upah Per Jam</label>
                    @foreach(\App\Models\LemburSetting::FORMULA as $val => $label)
                    <label class="flex items-center gap-3 mb-2 cursor-pointer">
                        <input type="radio" name="formula_upah_jam" value="{{ $val }}"
                               {{ $setting->formula_upah_jam === $val ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-300">
                        <div>
                            <span class="text-sm font-semibold text-gray-800">{{ $label }}</span>
                            @if($val === 'gapok_173')
                            <span class="ml-1 text-xs text-gray-400">— sesuai Permenaker No. 4/2014</span>
                            @else
                            <span class="ml-1 text-xs text-gray-400">— pakai tabel tarif di panel kanan</span>
                            @endif
                        </div>
                    </label>
                    @endforeach
                    <div class="mt-2 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                        <p class="text-xs text-blue-700">
                            <strong>Prioritas:</strong> Departemen dengan tarif di tabel kanan → pakai tarif itu.
                            Departemen tanpa tarif → otomatis hitung <code class="bg-blue-100 px-1 rounded">Gaji Pokok ÷ 173</code>.
                        </p>
                    </div>
                </div>
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <div class="relative">
                        <input type="checkbox" name="wajib_approval" value="1"
                               {{ $setting->wajib_approval ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 rounded-full peer peer-checked:bg-blue-500 transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Wajib Approval</p>
                        <p class="text-xs text-gray-400">Atasan harus approve sebelum nominal dihitung sebagai pembayaran</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Multiplier info dari ShiftMaster --}}
        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
            <p class="text-xs font-semibold text-amber-800 mb-2">Multiplier dari Master Shift (otomatis)</p>
            <div class="space-y-1.5">
                @foreach(\App\Models\ShiftMaster::orderBy('urutan')->get() as $s)
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-700">{{ $s->nama }}
                        @if($s->jam_label !== '00:00 – 00:00') <span class="text-gray-400">({{ $s->jam_label }})</span> @endif
                    </span>
                    <span class="font-bold px-2 py-0.5 rounded-lg {{ $s->multiplier_lembur >= 2 ? 'bg-orange-100 text-orange-700' : ($s->multiplier_lembur >= 1.5 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
                        ×{{ $s->multiplier_lembur }}
                    </span>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-amber-600 mt-2">
                Ubah di <a href="{{ route('shift.master.index') }}" class="underline font-medium">Master Shift</a>
            </p>
        </div>
    </div>

    {{-- ── Panel Kanan: Tarif per Departemen ──────────────────────────────────── --}}
    <div>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-800">Tarif Override per Departemen</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Isi jika dept punya tarif khusus (IGD, ICU, OK dll). <strong>0 = auto formula Gaji Pokok ÷ 173</strong>.
                </p>
            </div>
            <div class="overflow-y-auto max-h-[600px]">
                <table class="w-full text-sm">
                    <thead class="sticky top-0">
                        <tr class="border-b border-gray-100 bg-gray-50/90 backdrop-blur-sm">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Departemen</th>
                            <th class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">HB (Rp/jam)</th>
                            <th class="text-center px-3 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">HR (Rp/jam)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($departemen as $dept)
                        @php $tarif = $tarifMap[$dept->dep_id] ?? null; @endphp
                        <tr class="hover:bg-gray-50/40">
                            <td class="px-4 py-3">
                                <input type="hidden" name="tarif[{{ $loop->index }}][dep_id]" value="{{ $dept->dep_id }}">
                                <p class="font-medium text-gray-800 text-xs">{{ $dept->nama }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $dept->dep_id }}</p>
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" name="tarif[{{ $loop->index }}][hb]"
                                       min="0" step="500"
                                       value="{{ $tarif && $tarif->tarif_hb > 0 ? (int)$tarif->tarif_hb : '' }}"
                                       placeholder="auto"
                                       class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-2 focus:ring-blue-300 {{ $tarif && $tarif->tarif_hb > 0 ? 'font-semibold text-blue-700' : 'text-gray-400' }}">
                            </td>
                            <td class="px-3 py-3">
                                <input type="number" name="tarif[{{ $loop->index }}][hr]"
                                       min="0" step="500"
                                       value="{{ $tarif && $tarif->tarif_hr > 0 ? (int)$tarif->tarif_hr : '' }}"
                                       placeholder="auto"
                                       class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs text-right focus:outline-none focus:ring-2 focus:ring-blue-300 {{ $tarif && $tarif->tarif_hr > 0 ? 'font-semibold text-orange-700' : 'text-gray-400' }}">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="mt-5 flex items-center gap-3">
    <button type="submit"
            class="flex items-center gap-2 px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors font-semibold">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Simpan Semua Setting
    </button>
    <a href="{{ route('lembur.index') }}" class="px-4 py-2.5 text-sm text-gray-600 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
        Kembali
    </a>
</div>

</form>
@endsection
