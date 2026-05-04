@extends('layouts.app')
@section('title', 'Detail Slip Gaji')
@section('page-title', 'Detail Slip Gaji')
@section('page-subtitle', ($slip->pegawai?->nama ?? '–') . ' — ' . $slip->periode_label)

@section('content')
<div class="max-w-3xl mx-auto space-y-4" x-data="{ editMode: false, newRows: [] }">

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Header card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-start justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-700 font-bold text-xl flex-shrink-0">
                {{ strtoupper(substr($slip->pegawai?->nama ?? 'U', 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-base">{{ $slip->pegawai?->nama }}</p>
                <p class="text-xs text-gray-400">{{ $slip->pegawai?->jbtn }} · {{ $slip->pegawai?->departemenRef?->nama }}</p>
                @if($slip->pegawai?->payrollSetting?->golongan)
                <p class="text-xs text-blue-600 mt-0.5">
                    Golongan: {{ $slip->pegawai->payrollSetting->golongan }} &middot;
                    UMK {{ $slip->pegawai->payrollSetting->umk_tahun }}
                </p>
                @endif
            </div>
        </div>
        <div class="text-right flex-shrink-0">
            <p class="text-sm font-semibold text-gray-700">{{ $slip->periode_label }}</p>
            <span class="inline-block mt-1 px-3 py-1 text-xs font-semibold rounded-xl
                {{ $slip->status === 'final' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($slip->status) }}
            </span>
        </div>
    </div>

    {{-- Komponen form --}}
    <form method="POST" action="{{ route('payroll.slip.update', $slip) }}" id="formSlip">
        @csrf @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            {{-- Pendapatan --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-green-50 border-b border-green-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-green-700">Pendapatan (+)</p>
                    <p class="text-sm font-bold text-green-700">
                        Rp {{ number_format($slip->komponenSlip->where('jenis','tambah')->sum('nilai'), 0, ',', '.') }}
                    </p>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($slip->komponenSlip->where('jenis','tambah') as $k)
                    <div class="px-4 py-2.5 flex items-center gap-3">
                        <div class="flex-1">
                            <p class="text-sm text-gray-700">{{ $k->nama }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $k->sumber === 'manual' ? 'manual' : ($k->sumber === 'sik' ? 'dari SIK' : 'otomatis') }}
                            </p>
                        </div>
                        <div x-show="!editMode" class="text-sm font-medium text-gray-800">
                            Rp {{ number_format($k->nilai, 0, ',', '.') }}
                        </div>
                        <div x-show="editMode" style="display:none">
                            <input type="number" name="komponen[{{ $k->id }}]"
                                   value="{{ $k->nilai }}" min="0" step="1000"
                                   class="w-36 px-2 py-1 text-sm text-right border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-400 focus:outline-none">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Potongan --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 bg-red-50 border-b border-red-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-red-700">Potongan (–)</p>
                    <p class="text-sm font-bold text-red-700">
                        Rp {{ number_format($slip->komponenSlip->where('jenis','kurang')->sum('nilai'), 0, ',', '.') }}
                    </p>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach($slip->komponenSlip->where('jenis','kurang') as $k)
                    <div class="px-4 py-2.5 flex items-center gap-3">
                        <div class="flex-1">
                            <p class="text-sm text-gray-700">{{ $k->nama }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $k->sumber === 'manual' ? 'manual' : ($k->sumber === 'sik' ? 'dari SIK' : 'otomatis') }}
                            </p>
                        </div>
                        <div x-show="!editMode" class="text-sm font-medium text-gray-800">
                            Rp {{ number_format($k->nilai, 0, ',', '.') }}
                        </div>
                        <div x-show="editMode" class="flex items-center gap-1.5" style="display:none">
                            <input type="number" name="komponen[{{ $k->id }}]"
                                   value="{{ $k->nilai }}" min="0" step="1000"
                                   class="w-32 px-2 py-1 text-sm text-right border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            @if($k->sumber === 'manual')
                            <label class="flex items-center gap-1 text-xs text-red-500">
                                <input type="checkbox" name="hapus[]" value="{{ $k->id }}" class="rounded">
                                Hapus
                            </label>
                            @endif
                        </div>
                    </div>
                    @endforeach

                    {{-- Tambah potongan manual baru --}}
                    <template x-for="(row, idx) in newRows" :key="idx">
                        <div class="px-4 py-2.5 flex items-center gap-2 bg-yellow-50">
                            <input type="text" :name="`komponen_baru[${idx}][nama]`"
                                   x-model="row.nama" placeholder="Nama potongan"
                                   class="flex-1 px-2 py-1 text-sm border border-yellow-200 rounded-lg focus:ring-1 focus:ring-yellow-400 focus:outline-none">
                            <input type="hidden" :name="`komponen_baru[${idx}][jenis]`" value="kurang">
                            <input type="number" :name="`komponen_baru[${idx}][nilai]`"
                                   x-model="row.nilai" placeholder="0" min="0" step="1000"
                                   class="w-32 px-2 py-1 text-sm text-right border border-yellow-200 rounded-lg focus:ring-1 focus:ring-yellow-400 focus:outline-none">
                            <button type="button" @click="newRows.splice(idx, 1)"
                                    class="text-red-400 hover:text-red-600 text-sm">×</button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Tambah potongan manual button (edit mode) --}}
        <div x-show="editMode" style="display:none" class="mt-2">
            <button type="button"
                    @click="newRows.push({ nama: '', nilai: 0 })"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                + Tambah Potongan Manual
            </button>
            <p class="text-xs text-gray-400 mt-0.5">Contoh: Potongan Obat, Kasbon, dll</p>
        </div>

        {{-- Gaji Bersih --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-5 text-white flex items-center justify-between mt-2">
            <div>
                <p class="text-sm font-medium opacity-90">Gaji Bersih Diterima</p>
                <p class="text-xs opacity-70">{{ $slip->periode_label }} · {{ $slip->pegawai?->nama }}</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold">
                    Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                </p>
                <p class="text-xs opacity-70">
                    Pendapatan: Rp {{ number_format($slip->gaji_pokok + $slip->total_tunjangan, 0, ',', '.') }} –
                    Potongan: Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-wrap gap-2 mt-3">
            @if($slip->status === 'draft')
            {{-- Edit mode toggle --}}
            <button type="button" @click="editMode = !editMode"
                    :class="editMode ? 'bg-orange-500 hover:bg-orange-600' : 'bg-gray-600 hover:bg-gray-700'"
                    class="px-4 py-2 text-sm text-white rounded-xl font-medium transition">
                <span x-text="editMode ? 'Batal Edit' : 'Edit Komponen'"></span>
            </button>

            {{-- Save edit --}}
            <button type="submit" form="formSlip"
                    x-show="editMode" style="display:none"
                    class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                Simpan Perubahan
            </button>

            {{-- Finalize --}}
            <form method="POST" action="{{ route('payroll.slip.finalize', $slip) }}"
                  onsubmit="return confirm('Finalisasi slip? Tidak bisa diedit setelah final.')">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                    Finalisasi Slip
                </button>
            </form>
            @else
            {{-- Unfinalze --}}
            <form method="POST" action="{{ route('payroll.slip.unfinalize', $slip) }}"
                  onsubmit="return confirm('Kembalikan ke draft?')">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium transition">
                    Buka Kembali (Draft)
                </button>
            </form>
            @endif

            {{-- PDF --}}
            <a href="{{ route('payroll.slip.pdf', $slip) }}"
               class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium transition">
                Download PDF
            </a>

            <a href="{{ route('payroll.index', ['bulan' => $slip->bulan, 'tahun' => $slip->tahun]) }}"
               class="px-4 py-2 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                ← Kembali
            </a>
        </div>
    </form>

    {{-- Info --}}
    <div class="text-xs text-gray-400 space-y-0.5">
        <p>Digenerate: {{ $slip->generated_at?->translatedFormat('d F Y, H:i') ?? '-' }}</p>
        @if($slip->finalized_at)
        <p>Difinalisasi: {{ $slip->finalized_at->translatedFormat('d F Y, H:i') }}</p>
        @endif
    </div>

</div>
@endsection
