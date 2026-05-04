@extends('layouts.app')
@section('title', 'Master Payroll')
@section('page-title', 'Master Payroll')
@section('page-subtitle', 'Kelola UMK, golongan gaji, komponen tunjangan & potongan')

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush

@section('content')
@php
    $defaultTab = 'umk';
    if (session('success_umk'))     $defaultTab = 'umk';
    elseif (session('success_gaji')) $defaultTab = 'gaji';
    elseif (session('success_tambah')) $defaultTab = 'tambah';
    elseif (session('success_kurang')) $defaultTab = 'kurang';
    elseif (session('success_config')) $defaultTab = 'config';
    elseif (session('success_pegawai')) $defaultTab = 'pegawai';
@endphp
<div x-data="{ tab: '{{ $defaultTab }}' }" class="space-y-4">

    {{-- Tab bar --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-1.5 flex gap-1 overflow-x-auto">
        @foreach([
            ['umk',     'UMK Kab. Tasik'],
            ['gaji',    'Master Gaji'],
            ['tambah',  'Tunjangan'],
            ['kurang',  'Potongan'],
            ['config',  'Config'],
            ['pegawai', 'Setting Pegawai'],
        ] as [$key, $label])
        <button @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'"
                class="px-4 py-2 text-xs font-semibold rounded-xl transition whitespace-nowrap flex-shrink-0">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Flash messages --}}
    @foreach(['success_umk','success_gaji','success_tambah','success_kurang','success_config','success_pegawai'] as $key)
    @if(session($key))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session($key) }}</div>
    @endif
    @endforeach

    {{-- ══════════════════ TAB: UMK ══════════════════ --}}
    <div x-show="tab === 'umk'" x-cloak>
        <div class="grid md:grid-cols-2 gap-4">
            {{-- Add UMK --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-700 mb-4">Tambah / Update UMK</p>
                <form method="POST" action="{{ route('payroll.master.umk.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tahun <span class="text-red-500">*</span></label>
                        <input type="number" name="tahun" required min="2000" max="2100"
                               value="{{ now()->year }}"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nominal UMK (Rp/bulan) <span class="text-red-500">*</span></label>
                        <input type="number" name="nominal" required min="0" step="1000"
                               placeholder="Contoh: 2500000"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" maxlength="100"
                               placeholder="Contoh: UMK Kab. Tasikmalaya 2024"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <button type="submit"
                            class="w-full py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                        Simpan UMK
                    </button>
                </form>
            </div>
            {{-- UMK List --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-700 mb-4">Daftar UMK</p>
                <div class="space-y-2">
                    @forelse($umkList as $u)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $u->tahun }}</p>
                            <p class="text-xs text-gray-500">Rp {{ number_format($u->nominal, 0, ',', '.') }}/bulan</p>
                            @if($u->keterangan)<p class="text-xs text-gray-400">{{ $u->keterangan }}</p>@endif
                        </div>
                        <form method="POST" action="{{ route('payroll.master.umk.destroy', $u) }}"
                              onsubmit="return confirm('Hapus UMK {{ $u->tahun }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                        </form>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada data UMK.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TAB: MASTER GAJI ══════════════════ --}}
    <div x-show="tab === 'gaji'" x-cloak>
        <div class="grid md:grid-cols-5 gap-4">
            {{-- Add form --}}
            <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-700 mb-4">Tambah Skala Gaji</p>
                <form method="POST" action="{{ route('payroll.master.gaji.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Golongan <span class="text-red-500">*</span></label>
                        <input type="text" name="golongan" required maxlength="100"
                               placeholder="Contoh: I, II, Staff, Manajer"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">UMK Tahun <span class="text-red-500">*</span></label>
                        <select name="umk_tahun" required class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                            <option value="">-- Pilih Tahun UMK --</option>
                            @foreach($umkTahunOpts as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Pendidikan (opsional — kosong = semua)</label>
                        <select name="pendidikan" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                            <option value="">— Semua Pendidikan —</option>
                            @foreach($pendidikan as $pd)
                            <option value="{{ $pd->tingkat }}">{{ $pd->tingkat }} — {{ $pd->nama ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Gaji Pokok (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="gaji_pokok" required min="0" step="1000"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tunjangan Jabatan (Rp)</label>
                        <input type="number" name="tunjangan_jabatan" min="0" step="1000" value="0"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <button type="submit"
                            class="w-full py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                        Simpan
                    </button>
                </form>
            </div>
            {{-- Table --}}
            <div class="md:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-700">Skala Gaji</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600">Golongan</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600">UMK</th>
                                <th class="px-3 py-2 text-left font-semibold text-gray-600">Pendidikan</th>
                                <th class="px-3 py-2 text-right font-semibold text-gray-600">Gaji Pokok</th>
                                <th class="px-3 py-2 text-right font-semibold text-gray-600">Tunj. Jabatan</th>
                                <th class="px-3 py-2 text-center font-semibold text-gray-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($masterGaji as $mg)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-3 py-2.5 font-semibold text-gray-800">{{ $mg->golongan }}</td>
                                <td class="px-3 py-2.5 text-gray-600">{{ $mg->umk_tahun }}</td>
                                <td class="px-3 py-2.5 text-gray-400">{{ $mg->pendidikan ?? 'Semua' }}</td>
                                <td class="px-3 py-2.5 text-right font-semibold text-gray-800">
                                    Rp {{ number_format($mg->gaji_pokok, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2.5 text-right text-gray-600">
                                    Rp {{ number_format($mg->tunjangan_jabatan, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <form method="POST" action="{{ route('payroll.master.gaji.destroy', $mg) }}"
                                          onsubmit="return confirm('Hapus?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TAB: TUNJANGAN ══════════════════ --}}
    <div x-show="tab === 'tambah'" x-cloak>
        @include('payroll._komponen_form', ['jenis' => 'tambah', 'list' => $tambahan, 'title' => 'Tunjangan', 'color' => 'green'])
    </div>

    {{-- ══════════════════ TAB: POTONGAN ══════════════════ --}}
    <div x-show="tab === 'kurang'" x-cloak>
        @include('payroll._komponen_form', ['jenis' => 'kurang', 'list' => $potongan, 'title' => 'Potongan', 'color' => 'red'])
    </div>

    {{-- ══════════════════ TAB: CONFIG ══════════════════ --}}
    <div x-show="tab === 'config'" x-cloak>
        <div class="max-w-lg">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <p class="text-sm font-semibold text-gray-700 mb-4">Konfigurasi Payroll</p>
                <form method="POST" action="{{ route('payroll.master.config.update') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Absensi</p>

                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-xl cursor-pointer">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Potongan Absensi Aktif</p>
                                <p class="text-xs text-gray-400">Potong gaji berdasarkan jumlah alfa</p>
                            </div>
                            <input type="hidden" name="config[potongan_absensi_aktif]" value="0">
                            <input type="checkbox" name="config[potongan_absensi_aktif]" value="1"
                                   {{ ($config['potongan_absensi_aktif'] ?? '0') === '1' ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded">
                        </label>

                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tarif Potongan Per Hari Alfa (Rp)</label>
                            <input type="number" name="config[tarif_potongan_absensi]" min="0" step="1000"
                                   value="{{ $config['tarif_potongan_absensi'] ?? 0 }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>
                    </div>

                    <div class="space-y-3 pt-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Pajak</p>
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-xl cursor-pointer">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Hitung PPh21 Otomatis</p>
                                <p class="text-xs text-gray-400">Hitung PPh21 berdasarkan status PTKP pegawai</p>
                            </div>
                            <input type="hidden" name="config[pph21_aktif]" value="0">
                            <input type="checkbox" name="config[pph21_aktif]" value="1"
                                   {{ ($config['pph21_aktif'] ?? '1') === '1' ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded">
                        </label>
                    </div>

                    <div class="space-y-3 pt-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Masa Kerja</p>
                        <label class="flex items-center justify-between p-3 bg-gray-50 rounded-xl cursor-pointer">
                            <div>
                                <p class="text-sm font-medium text-gray-800">Tunjangan Masa Kerja Aktif</p>
                                <p class="text-xs text-gray-400">Belum aktif — akan dikonfigurasi kemudian</p>
                            </div>
                            <input type="hidden" name="config[masa_kerja_aktif]" value="0">
                            <input type="checkbox" name="config[masa_kerja_aktif]" value="1" disabled
                                   class="w-4 h-4 text-blue-600 rounded opacity-40">
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                        Simpan Konfigurasi
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ══════════════════ TAB: SETTING PEGAWAI ══════════════════ --}}
    <div x-show="tab === 'pegawai'" x-cloak>
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-700">Setting Golongan & UMK Per Pegawai</p>
                <p class="text-xs text-gray-400">{{ $pegawaiList->count() }} pegawai</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Pegawai</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Departemen</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-gray-600">Pendidikan</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 w-40">Golongan</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 w-28">UMK Tahun</th>
                            <th class="px-4 py-2.5 text-center text-xs font-semibold text-gray-600 w-20">Simpan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($pegawaiList as $p)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-2.5">
                                <div class="font-medium text-gray-800 text-sm">{{ $p->nama }}</div>
                                <div class="text-xs text-gray-400">{{ $p->nik }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-500">{{ $p->departemenRef?->nama }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-500">{{ $p->pendidikan }}</td>
                            <td class="px-4 py-2.5">
                                <form method="POST" action="{{ route('payroll.master.pegawai.save') }}"
                                      class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="nik" value="{{ $p->nik }}">
                                    <input type="text" name="golongan" maxlength="100"
                                           value="{{ $p->payrollSetting?->golongan }}"
                                           placeholder="Golongan"
                                           class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            </td>
                            <td class="px-4 py-2.5">
                                    <select name="umk_tahun" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-400 focus:outline-none bg-white">
                                        <option value="">—</option>
                                        @foreach($umkTahunOpts as $t)
                                        <option value="{{ $t }}" {{ $p->payrollSetting?->umk_tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                                        @endforeach
                                    </select>
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-medium">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
