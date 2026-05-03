@extends('layouts.app')
@section('title', 'Detail Lembur')
@section('page-title', 'Detail Lembur')
@section('page-subtitle', $lembur->pegawai?->nama)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

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

    {{-- Detail card --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <p class="text-xs text-gray-400 mb-1">Pegawai</p>
                <p class="font-semibold text-gray-800">{{ $lembur->pegawai?->nama }}</p>
                <p class="text-xs text-gray-400">{{ $lembur->pegawai?->jbtn }} · {{ $lembur->pegawai?->departemenRef?->nama }}</p>
            </div>
            @php
                $color = $lembur->status_color;
                $badgeCls = match($color) {
                    'yellow' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'blue'   => 'bg-blue-100 text-blue-700 border-blue-200',
                    'green'  => 'bg-green-100 text-green-700 border-green-200',
                    'red'    => 'bg-red-100 text-red-700 border-red-200',
                    default  => 'bg-gray-100 text-gray-600 border-gray-200',
                };
            @endphp
            <span class="px-3 py-1.5 rounded-xl text-xs font-semibold border {{ $badgeCls }}">
                {{ $lembur->status }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">Tanggal</p>
                <p class="font-medium text-gray-800">
                    {{ $lembur->tanggal?->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Jenis Lembur</p>
                <span class="inline-block px-2 py-0.5 rounded-lg text-xs font-semibold
                    {{ $lembur->jenis === 'HR' ? 'bg-orange-100 text-orange-700' : 'bg-blue-50 text-blue-600' }}">
                    {{ $lembur->jenis }} — {{ \App\Models\Lembur::JENIS[$lembur->jenis] ?? $lembur->jenis }}
                </span>
            </div>
            <div>
                <p class="text-xs text-gray-400">Waktu</p>
                <p class="font-medium text-gray-800">
                    {{ $lembur->jam_mulai }} – {{ $lembur->jam_selesai }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Durasi</p>
                <p class="font-semibold text-blue-600 text-base">{{ $lembur->durasi_label }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Nominal Estimasi</p>
                <p class="font-bold text-gray-800 text-lg">
                    Rp {{ number_format($lembur->nominal ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Diajukan</p>
                <p class="font-medium text-gray-700">
                    {{ $lembur->created_at?->translatedFormat('d F Y, H:i') }}
                </p>
            </div>
            <div class="col-span-2">
                <p class="text-xs text-gray-400 mb-1">Keterangan Pekerjaan</p>
                <p class="text-gray-700 bg-gray-50 rounded-xl px-3 py-2">
                    {{ $lembur->keterangan ?: $lembur->alasan ?: '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Timeline approval --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <p class="text-xs font-semibold text-gray-600 mb-5">Alur Persetujuan</p>
        <div class="relative">
            {{-- Connector line --}}
            <div class="absolute left-4 top-4 bottom-4 w-0.5 bg-gray-100"></div>

            {{-- Step 1: Pengajuan --}}
            <div class="relative flex items-start gap-4 pb-6">
                <div class="w-8 h-8 rounded-full bg-green-100 border-2 border-green-400 flex items-center justify-center flex-shrink-0 z-10">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Pengajuan</p>
                    <p class="text-xs text-gray-500">{{ $lembur->pegawai?->nama }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $lembur->created_at?->translatedFormat('d F Y, H:i') }}</p>
                </div>
            </div>

            {{-- Step 2: Atasan --}}
            @php
                $atasanDone    = in_array($lembur->status, ['Menunggu HRD', 'Disetujui']);
                $atasanTolak   = $lembur->status === 'Ditolak Atasan';
                $atasanPending = $lembur->status === 'Menunggu Atasan';
                $atasanCircle  = $atasanDone   ? 'bg-green-100 border-green-400' :
                                 ($atasanTolak ? 'bg-red-100 border-red-400' :
                                 ($atasanPending ? 'bg-yellow-100 border-yellow-400 animate-pulse' : 'bg-gray-50 border-gray-200'));
            @endphp
            <div class="relative flex items-start gap-4 pb-6">
                <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center flex-shrink-0 z-10 {{ $atasanCircle }}">
                    @if($atasanDone)
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    @elseif($atasanTolak)
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    @elseif($atasanPending)
                    <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                    @else
                    <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">Persetujuan Atasan Langsung</p>
                    @if($atasanDone || $atasanTolak)
                    <p class="text-xs text-gray-500">{{ $lembur->approverAtasan?->nama ?? '-' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $lembur->approved_atasan_at?->translatedFormat('d F Y, H:i') }}</p>
                    @if($lembur->catatan_atasan)
                    <p class="text-xs mt-1.5 px-2 py-1.5 rounded-lg {{ $atasanTolak ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                        "{{ $lembur->catatan_atasan }}"
                    </p>
                    @endif
                    @else
                    <p class="text-xs text-gray-400 mt-0.5">{{ $atasanPending ? 'Menunggu persetujuan...' : 'Belum sampai tahap ini' }}</p>
                    @endif
                </div>
            </div>

            {{-- Step 3: HRD --}}
            @php
                $hrdDone    = $lembur->status === 'Disetujui';
                $hrdTolak   = $lembur->status === 'Ditolak HRD';
                $hrdPending = $lembur->status === 'Menunggu HRD';
                $hrdCircle  = $hrdDone   ? 'bg-green-100 border-green-400' :
                              ($hrdTolak ? 'bg-red-100 border-red-400' :
                              ($hrdPending ? 'bg-blue-100 border-blue-400 animate-pulse' : 'bg-gray-50 border-gray-200'));
            @endphp
            <div class="relative flex items-start gap-4">
                <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center flex-shrink-0 z-10 {{ $hrdCircle }}">
                    @if($hrdDone)
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    @elseif($hrdTolak)
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    @elseif($hrdPending)
                    <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                    @else
                    <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                    @endif
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">Persetujuan HRD</p>
                    @if($hrdDone || $hrdTolak)
                    <p class="text-xs text-gray-500">{{ $lembur->approverHrd?->nama ?? '-' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $lembur->approved_hrd_at?->translatedFormat('d F Y, H:i') }}</p>
                    @if($lembur->catatan_hrd)
                    <p class="text-xs mt-1.5 px-2 py-1.5 rounded-lg {{ $hrdTolak ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                        "{{ $lembur->catatan_hrd }}"
                    </p>
                    @endif
                    @else
                    <p class="text-xs text-gray-400 mt-0.5">{{ $hrdPending ? 'Menunggu persetujuan...' : 'Belum sampai tahap ini' }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Action panels --}}
    {{-- Approve / Tolak Atasan --}}
    @if($lembur->bisaApproveAtasan())
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5 space-y-3"
         x-data="{ tolak: false }">
        <p class="text-sm font-semibold text-yellow-800">Persetujuan Atasan Langsung</p>

        {{-- Approve --}}
        <form method="POST" action="{{ route('lembur.approve.atasan', $lembur) }}">
            @csrf
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs text-yellow-700 mb-1">Catatan (opsional)</label>
                    <input type="text" name="catatan_atasan" maxlength="255"
                           placeholder="Catatan persetujuan..."
                           class="w-full px-3 py-2 text-sm border border-yellow-200 rounded-xl focus:ring-2 focus:ring-yellow-400 focus:outline-none bg-white">
                </div>
                <button type="submit"
                        class="px-5 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition flex-shrink-0">
                    Setujui
                </button>
            </div>
        </form>

        {{-- Tolak --}}
        <div>
            <button type="button" @click="tolak = !tolak"
                    class="text-xs text-red-600 hover:underline">
                <span x-text="tolak ? 'Batal' : 'Tolak Pengajuan'"></span>
            </button>
            <form x-show="tolak" x-collapse method="POST"
                  action="{{ route('lembur.tolak.atasan', $lembur) }}" class="mt-2 space-y-2">
                @csrf
                <textarea name="catatan_atasan" required maxlength="255" rows="2"
                          placeholder="Alasan penolakan (wajib diisi)..."
                          class="w-full px-3 py-2 text-sm border border-red-200 rounded-xl focus:ring-2 focus:ring-red-400 focus:outline-none resize-none"></textarea>
                <button type="submit"
                        class="w-full py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition">
                    Konfirmasi Tolak
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- Approve / Tolak HRD --}}
    @if($lembur->bisaApproveHrd())
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 space-y-3"
         x-data="{ tolak: false }">
        <p class="text-sm font-semibold text-blue-800">Persetujuan HRD</p>

        <form method="POST" action="{{ route('lembur.approve.hrd', $lembur) }}">
            @csrf
            <div class="flex gap-2 items-end">
                <div class="flex-1">
                    <label class="block text-xs text-blue-700 mb-1">Catatan (opsional)</label>
                    <input type="text" name="catatan_hrd" maxlength="255"
                           placeholder="Catatan persetujuan..."
                           class="w-full px-3 py-2 text-sm border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                </div>
                <button type="submit"
                        class="px-5 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition flex-shrink-0">
                    Setujui
                </button>
            </div>
        </form>

        <div>
            <button type="button" @click="tolak = !tolak"
                    class="text-xs text-red-600 hover:underline">
                <span x-text="tolak ? 'Batal' : 'Tolak Pengajuan'"></span>
            </button>
            <form x-show="tolak" x-collapse method="POST"
                  action="{{ route('lembur.tolak.hrd', $lembur) }}" class="mt-2 space-y-2">
                @csrf
                <textarea name="catatan_hrd" required maxlength="255" rows="2"
                          placeholder="Alasan penolakan (wajib diisi)..."
                          class="w-full px-3 py-2 text-sm border border-red-200 rounded-xl focus:ring-2 focus:ring-red-400 focus:outline-none resize-none"></textarea>
                <button type="submit"
                        class="w-full py-2 text-sm bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition">
                    Konfirmasi Tolak
                </button>
            </form>
        </div>
    </div>
    @endif

    <a href="{{ route('lembur.index') }}"
       class="inline-block text-sm text-gray-500 hover:text-gray-700 transition">
        ← Kembali ke Daftar Lembur
    </a>
</div>
@endsection
