@extends('layouts.app')
@section('title', 'Detail Training Eksternal')
@section('page-title', 'Detail Training Eksternal')
@section('page-subtitle', $eksternal->nama_training)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    {{-- Header Info --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <h2 class="text-base font-bold text-gray-800">{{ $eksternal->nama_training }}</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $eksternal->lembaga }}
                    @if($eksternal->lokasi) · {{ $eksternal->lokasi }}@endif
                </p>
                <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500">
                    <span>👤 {{ $eksternal->pegawai?->nama }} ({{ $eksternal->pegawai?->jbtn }})</span>
                    <span>📅 {{ $eksternal->tanggal_mulai->translatedFormat('d M Y') }}
                        @if(!$eksternal->tanggal_mulai->equalTo($eksternal->tanggal_selesai))
                        — {{ $eksternal->tanggal_selesai->translatedFormat('d M Y') }}
                        @endif
                    </span>
                    @if($eksternal->biaya > 0)
                    <span>💰 Rp {{ number_format($eksternal->biaya, 0, ',', '.') }}</span>
                    @endif
                </div>
                @if($eksternal->deskripsi)
                <p class="mt-3 text-xs text-gray-500 leading-relaxed">{{ $eksternal->deskripsi }}</p>
                @endif
                <p class="mt-2 text-xs text-gray-400">
                    Mode: <span class="font-medium">{{ $eksternal->mode === 'rekam_langsung' ? 'Rekam Langsung' : 'Pengajuan' }}</span>
                    · Diajukan oleh: <span class="font-medium">{{ $eksternal->submittedBy?->nama ?? 'Sistem' }}</span>
                </p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold rounded-xl flex-shrink-0
                {{ \App\Models\TrainingEksternal::STATUS_COLOR[$eksternal->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ \App\Models\TrainingEksternal::STATUS_LABEL[$eksternal->status] ?? $eksternal->status }}
            </span>
        </div>
    </div>

    {{-- Timeline Approval --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Alur Persetujuan</p>
        <div class="space-y-3">

            {{-- Step 1: Atasan --}}
            @if($eksternal->mode === 'pengajuan')
            @php
                $atasanDone = in_array($eksternal->status, ['menunggu_hrd','disetujui','menunggu_validasi','tervalidasi']);
                $atasanTolak = $eksternal->status === 'ditolak_atasan';
            @endphp
            <div class="flex gap-3">
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                    {{ $atasanDone ? 'bg-green-100 text-green-700' : ($atasanTolak ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ $atasanDone ? '✓' : ($atasanTolak ? '✗' : '1') }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-700">Persetujuan Atasan
                        @if($eksternal->atasan) — {{ $eksternal->atasan->nama }}@endif
                    </p>
                    @if($atasanDone)
                    <p class="text-xs text-gray-400">Disetujui oleh {{ $eksternal->approvedAtasanBy?->nama }}
                        · {{ $eksternal->approved_atasan_at?->translatedFormat('d M Y H:i') }}</p>
                    @elseif($atasanTolak)
                    <p class="text-xs text-red-500">Ditolak: {{ $eksternal->catatan_atasan }}</p>
                    @elseif($eksternal->status === 'menunggu_atasan')
                    <p class="text-xs text-yellow-600">Menunggu persetujuan</p>
                    @if($eksternal->bisaApproveAtasan())
                    <div class="flex gap-2 mt-2">
                        <form method="POST" action="{{ route('training.eksternal.approve.atasan', $eksternal) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                Setujui
                            </button>
                        </form>
                        <form method="POST" action="{{ route('training.eksternal.tolak.atasan', $eksternal) }}"
                              x-data="{ show: false }" @submit.prevent="show=true">
                            @csrf
                            <button type="button" @click="show=true"
                                    class="px-3 py-1.5 text-xs bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition">
                                Tolak
                            </button>
                            <div x-show="show" x-cloak class="mt-2 flex gap-2 items-end">
                                <textarea name="catatan_atasan" rows="2" placeholder="Alasan penolakan..." required
                                          class="flex-1 px-2 py-1 text-xs border border-gray-200 rounded-lg focus:outline-none resize-none"></textarea>
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs bg-red-600 text-white rounded-lg">Kirim</button>
                            </div>
                        </form>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            {{-- Step 2: HRD --}}
            @php
                $hrdDone = in_array($eksternal->status, ['disetujui','menunggu_validasi','tervalidasi']);
                $hrdTolak = $eksternal->status === 'ditolak_hrd';
                $hrdActive = $eksternal->status === 'menunggu_hrd';
            @endphp
            <div class="flex gap-3">
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                    {{ $hrdDone ? 'bg-green-100 text-green-700' : ($hrdTolak ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                    {{ $hrdDone ? '✓' : ($hrdTolak ? '✗' : '2') }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-700">Persetujuan HRD</p>
                    @if($hrdDone)
                    <p class="text-xs text-gray-400">Disetujui oleh {{ $eksternal->approvedHrdBy?->nama }}
                        · {{ $eksternal->approved_hrd_at?->translatedFormat('d M Y H:i') }}</p>
                    @elseif($hrdTolak)
                    <p class="text-xs text-red-500">Ditolak: {{ $eksternal->catatan_hrd }}</p>
                    @elseif($hrdActive)
                    <p class="text-xs text-yellow-600">Menunggu persetujuan HRD</p>
                    @if($eksternal->bisaApproveHrd())
                    <div class="flex gap-2 mt-2">
                        <form method="POST" action="{{ route('training.eksternal.approve.hrd', $eksternal) }}">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                Setujui
                            </button>
                        </form>
                        <form method="POST" action="{{ route('training.eksternal.tolak.hrd', $eksternal) }}"
                              x-data="{ show: false }">
                            @csrf
                            <button type="button" @click="show=true"
                                    class="px-3 py-1.5 text-xs bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition">
                                Tolak
                            </button>
                            <div x-show="show" x-cloak class="mt-2 flex gap-2 items-end">
                                <textarea name="catatan_hrd" rows="2" placeholder="Alasan penolakan..." required
                                          class="flex-1 px-2 py-1 text-xs border border-gray-200 rounded-lg focus:outline-none resize-none"></textarea>
                                <button type="submit"
                                        class="px-3 py-1.5 text-xs bg-red-600 text-white rounded-lg">Kirim</button>
                            </div>
                        </form>
                    </div>
                    @endif
                    @else
                    <p class="text-xs text-gray-400">Menunggu persetujuan atasan</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Step 3: Upload Sertifikat --}}
            @php
                $uploadDone = in_array($eksternal->status, ['menunggu_validasi','tervalidasi']);
                $uploadActive = $eksternal->status === 'disetujui' || $eksternal->mode === 'rekam_langsung' && $eksternal->file_sertifikat;
            @endphp
            <div class="flex gap-3">
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                    {{ $uploadDone ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $uploadDone ? '✓' : ($eksternal->mode === 'pengajuan' ? '3' : '1') }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-700">Upload Sertifikat</p>
                    @if($uploadDone)
                    <p class="text-xs text-gray-400">
                        No. Sertifikat: <span class="font-mono font-medium text-gray-700">{{ $eksternal->nomor_sertifikat }}</span>
                        @if($eksternal->masa_berlaku)
                        · Berlaku s/d: {{ $eksternal->masa_berlaku->translatedFormat('d M Y') }}
                        @endif
                    </p>
                    @if($eksternal->file_sertifikat)
                    <a href="{{ Storage::url($eksternal->file_sertifikat) }}" target="_blank"
                       class="mt-1 inline-block text-xs text-blue-500 hover:text-blue-700">
                        📄 Lihat / Download Sertifikat
                    </a>
                    @endif
                    @elseif($eksternal->bisaUploadSertifikat())
                    <form method="POST" action="{{ route('training.eksternal.upload.sertifikat', $eksternal) }}"
                          enctype="multipart/form-data" class="mt-2 space-y-2">
                        @csrf
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="nomor_sertifikat" placeholder="Nomor sertifikat" required
                                   class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none">
                            <input type="date" name="masa_berlaku"
                                   class="px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none">
                        </div>
                        <input type="file" name="file_sertifikat" accept=".pdf,.jpg,.jpeg,.png" required
                               class="w-full text-xs text-gray-600 border border-gray-200 rounded-lg px-2 py-1 file:mr-2 file:py-0.5 file:px-2 file:rounded file:border-0 file:text-xs file:bg-blue-50 file:text-blue-600">
                        <button type="submit"
                                class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Upload & Submit ke HR
                        </button>
                    </form>
                    @else
                    <p class="text-xs text-gray-400">Setelah training selesai, upload sertifikat di sini.</p>
                    @endif
                </div>
            </div>

            {{-- Step 4: Validasi HR --}}
            @php
                $valStep = $eksternal->mode === 'pengajuan' ? '4' : '2';
                $valDone = $eksternal->status === 'tervalidasi';
            @endphp
            <div class="flex gap-3">
                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                    {{ $valDone ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $valDone ? '✓' : $valStep }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-700">Validasi HR</p>
                    @if($valDone)
                    <p class="text-xs text-gray-400">Divalidasi oleh {{ $eksternal->validatedBy?->nama }}
                        · {{ $eksternal->validated_at?->translatedFormat('d M Y H:i') }}</p>
                    <p class="text-xs text-green-600 font-medium mt-1">✓ Sertifikat sudah masuk ke dokumen karyawan</p>
                    @elseif($eksternal->status === 'menunggu_validasi')
                    <p class="text-xs text-purple-600">Menunggu validasi HR</p>
                    @if($eksternal->bisaValidasiHr())
                    <form method="POST" action="{{ route('training.eksternal.validasi', $eksternal) }}" class="mt-2">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Validasi Sertifikat
                        </button>
                    </form>
                    @endif
                    @else
                    <p class="text-xs text-gray-400">HR akan memvalidasi sertifikat yang diupload.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('training.eksternal.index') }}"
       class="inline-block text-sm text-gray-500 hover:text-gray-700 transition">← Kembali ke Daftar External</a>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush
@endsection
