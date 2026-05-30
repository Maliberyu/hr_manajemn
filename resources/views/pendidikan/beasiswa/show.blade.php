@extends('layouts.app')
@section('title', 'Detail Pengajuan Bantuan Pendidikan')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('pendidikan.beasiswa.index') }}"
           class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Detail Pengajuan</h1>
            <p class="text-sm text-gray-500">{{ $beasiswa->nama_program }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    @php $c = $beasiswa->status_color; @endphp

    {{-- Info Utama --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
            <div>
                <p class="font-semibold text-gray-800">{{ $beasiswa->pegawai?->nama }}</p>
                <p class="text-xs text-gray-400">{{ $beasiswa->nik }} · {{ $beasiswa->pegawai?->jbtn }}</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full
                bg-{{ $c }}-100 text-{{ $c }}-700 border border-{{ $c }}-200">
                {{ $beasiswa->status_label }}
            </span>
        </div>
        <div class="px-5 py-4 grid grid-cols-2 gap-x-8 gap-y-3">
            <div>
                <p class="text-xs text-gray-400">Jenis Bantuan</p>
                <p class="text-sm font-medium text-gray-800">{{ $beasiswa->jenis_label }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Nama Program</p>
                <p class="text-sm font-medium text-gray-800">{{ $beasiswa->nama_program }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Institusi</p>
                <p class="text-sm text-gray-700">{{ $beasiswa->institusi }}{{ $beasiswa->kota ? ', '.$beasiswa->kota : '' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Periode</p>
                <p class="text-sm text-gray-700">
                    {{ $beasiswa->tgl_mulai->isoFormat('D MMM Y') }}
                    @if($beasiswa->tgl_selesai) – {{ $beasiswa->tgl_selesai->isoFormat('D MMM Y') }} @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Biaya Diajukan</p>
                <p class="text-sm font-semibold text-gray-800">Rp {{ number_format($beasiswa->biaya_diajukan, 0, ',', '.') }}</p>
            </div>
            @if($beasiswa->biaya_disetujui !== null)
            <div>
                <p class="text-xs text-gray-400">Biaya Disetujui</p>
                <p class="text-sm font-semibold text-green-700">Rp {{ number_format($beasiswa->biaya_disetujui, 0, ',', '.') }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs text-gray-400">Diajukan Oleh</p>
                <p class="text-sm text-gray-700">{{ $beasiswa->pengajuUser?->nama ?? '-' }}</p>
                <p class="text-xs text-gray-400">{{ $beasiswa->created_at->isoFormat('D MMM Y, HH:mm') }}</p>
            </div>
        </div>

        @if($beasiswa->catatan_pengaju)
        <div class="px-5 pb-4">
            <p class="text-xs text-gray-400 mb-1">Catatan Pengaju</p>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-xl px-3 py-2">{{ $beasiswa->catatan_pengaju }}</p>
        </div>
        @endif

        @if($beasiswa->file_proposal_url)
        <div class="px-5 pb-4">
            <a href="{{ $beasiswa->file_proposal_url }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 border border-blue-300 text-blue-600 rounded-xl text-sm hover:bg-blue-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh Proposal
            </a>
        </div>
        @endif
    </div>

    {{-- Timeline Approval --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Alur Persetujuan</h3>
        </div>
        <div class="px-5 py-4 space-y-4">

            {{-- Atasan --}}
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center
                    {{ in_array($beasiswa->status, ['menunggu_hrd','disetujui','ditolak','selesai']) ? 'bg-green-100' : ($beasiswa->status === 'menunggu_atasan' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                    @if(in_array($beasiswa->status, ['menunggu_hrd','disetujui','selesai']))
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @elseif($beasiswa->status === 'ditolak' && $beasiswa->approve_atasan_oleh)
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @elseif($beasiswa->status === 'menunggu_atasan')
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                    <span class="w-2 h-2 rounded-full bg-gray-300 block"></span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Persetujuan Atasan Langsung</p>
                    @if($beasiswa->approveAtasanUser)
                    <p class="text-xs text-gray-400">{{ $beasiswa->approveAtasanUser->nama }}</p>
                    @endif
                    @if($beasiswa->catatan_atasan)
                    <p class="text-xs text-gray-600 bg-gray-50 rounded-lg px-2 py-1 mt-1">{{ $beasiswa->catatan_atasan }}</p>
                    @endif
                </div>
            </div>

            {{-- HRD --}}
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center
                    {{ in_array($beasiswa->status, ['disetujui','selesai']) ? 'bg-green-100' : ($beasiswa->status === 'ditolak' && $beasiswa->approve_hrd_oleh ? 'bg-red-100' : ($beasiswa->status === 'menunggu_hrd' ? 'bg-yellow-100' : 'bg-gray-100')) }}">
                    @if(in_array($beasiswa->status, ['disetujui','selesai']))
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    @elseif($beasiswa->status === 'ditolak' && $beasiswa->approve_hrd_oleh)
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    @elseif($beasiswa->status === 'menunggu_hrd')
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                    <span class="w-2 h-2 rounded-full bg-gray-300 block"></span>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-700">Keputusan HRD</p>
                    @if($beasiswa->approveHrdUser)
                    <p class="text-xs text-gray-400">{{ $beasiswa->approveHrdUser->nama }}</p>
                    @endif
                    @if($beasiswa->catatan_hrd)
                    <p class="text-xs text-gray-600 bg-gray-50 rounded-lg px-2 py-1 mt-1">{{ $beasiswa->catatan_hrd }}</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Aksi HRD --}}
    @if($beasiswa->status === 'menunggu_hrd')
    <div class="bg-white border border-amber-200 rounded-2xl shadow-sm overflow-hidden" x-data="{ keputusan: '' }">
        <div class="px-5 py-3 bg-amber-50 border-b border-amber-100">
            <h3 class="text-sm font-semibold text-amber-700">Berikan Keputusan</h3>
        </div>
        <form action="{{ route('pendidikan.beasiswa.approve-hrd', $beasiswa) }}" method="POST" class="px-5 py-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-2">Keputusan <span class="text-red-500">*</span></label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="keputusan" value="disetujui" x-model="keputusan" required
                               class="text-green-600">
                        <span class="text-sm font-medium text-green-700">Setujui</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="keputusan" value="ditolak" x-model="keputusan"
                               class="text-red-600">
                        <span class="text-sm font-medium text-red-700">Tolak</span>
                    </label>
                </div>
            </div>
            <div x-show="keputusan === 'disetujui'" x-cloak>
                <label class="block text-xs text-gray-500 mb-1">Biaya Disetujui (Rp)</label>
                <input type="number" name="biaya_disetujui" value="{{ $beasiswa->biaya_diajukan }}"
                       min="0" step="1000"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                <p class="text-xs text-gray-400 mt-0.5">Kosongkan untuk menggunakan jumlah yang diajukan</p>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Catatan HRD</label>
                <textarea name="catatan_hrd" rows="2" maxlength="400"
                          class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none"
                          placeholder="Alasan atau informasi tambahan..."></textarea>
            </div>
            <button type="submit"
                    :disabled="!keputusan"
                    :class="!keputusan ? 'opacity-50 cursor-not-allowed' : ''"
                    class="w-full py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                Kirim Keputusan
            </button>
        </form>
    </div>
    @endif

    {{-- Upload Hasil + Tandai Selesai (jika disetujui) --}}
    @if($beasiswa->status === 'disetujui')
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Dokumen Hasil & Penyelesaian</h3>
        </div>
        <div class="px-5 py-4 space-y-4">
            @if($beasiswa->file_hasil_url)
            <a href="{{ $beasiswa->file_hasil_url }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 border border-green-300 text-green-600 rounded-xl text-sm hover:bg-green-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh File Hasil
            </a>
            @endif
            <form action="{{ route('pendidikan.beasiswa.upload-hasil', $beasiswa) }}" method="POST"
                  enctype="multipart/form-data" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Upload File Hasil / Sertifikat</label>
                    <input type="file" name="file_hasil" required accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full text-sm text-gray-500">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-gray-700 text-white rounded-xl text-sm font-medium hover:bg-gray-800 transition">
                    Upload
                </button>
            </form>
            <form action="{{ route('pendidikan.beasiswa.selesai', $beasiswa) }}" method="POST">
                @csrf
                <button type="submit"
                        onclick="return confirm('Tandai pengajuan ini sebagai Selesai?')"
                        class="w-full py-2.5 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition">
                    Tandai Selesai
                </button>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
