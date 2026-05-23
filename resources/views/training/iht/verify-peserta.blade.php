<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Kehadiran — {{ $peserta->pegawai?->nama }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">

<div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">

    {{-- Header --}}
    <div class="bg-blue-600 px-6 py-5 text-white text-center">
        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-lg font-bold">Verifikasi Kehadiran</h1>
        <p class="text-blue-200 text-sm mt-0.5">In-House Training (IHT)</p>
    </div>

    {{-- Info Training --}}
    <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
        <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide mb-1">Training</p>
        <p class="text-sm font-bold text-gray-800">{{ $iht->nama_training }}</p>
        <p class="text-xs text-gray-500 mt-0.5">
            {{ $iht->penyelenggara }}
            @if($iht->pemateri) &middot; Pemateri: {{ $iht->pemateri }} @endif
        </p>
        <div class="flex gap-3 mt-2 text-xs text-gray-600">
            <span>
                {{ $iht->tanggal_mulai->translatedFormat('d M Y') }}
                @if(!$iht->tanggal_mulai->equalTo($iht->tanggal_selesai))
                — {{ $iht->tanggal_selesai->translatedFormat('d M Y') }}
                @endif
            </span>
            <span>&middot; {{ $iht->lokasi }}</span>
        </div>
    </div>

    {{-- Info Peserta --}}
    <div class="px-6 py-5 space-y-4">

        {{-- Nama & Status --}}
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Peserta</p>
                <p class="text-base font-bold text-gray-800">{{ $peserta->pegawai?->nama ?? '—' }}</p>
                <p class="text-sm text-gray-500">{{ $peserta->pegawai?->jbtn }}</p>
                @if($peserta->pegawai?->departemenRef)
                <p class="text-xs text-gray-400">{{ $peserta->pegawai->departemenRef->nama }}</p>
                @endif
            </div>
            <div class="flex-shrink-0">
                @php
                    $statusColor = match($peserta->status) {
                        'hadir','selesai' => 'bg-green-100 text-green-700 border-green-200',
                        'tidak_hadir'     => 'bg-red-100 text-red-700 border-red-200',
                        default           => 'bg-gray-100 text-gray-600 border-gray-200',
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusColor }}">
                    @if(in_array($peserta->status, ['hadir','selesai']))
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    @endif
                    {{ \App\Models\IHTPeserta::STATUS[$peserta->status] ?? $peserta->status }}
                </span>
            </div>
        </div>

        {{-- Kehadiran --}}
        <div class="grid grid-cols-3 gap-3">
            <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Masuk</p>
                <p class="text-sm font-bold text-blue-700">
                    {{ $peserta->check_in_at ? $peserta->check_in_at->format('H:i') : '—' }}
                </p>
                @if($peserta->check_in_at)
                <p class="text-xs text-gray-400">{{ $peserta->check_in_at->translatedFormat('d M') }}</p>
                @endif
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Selesai</p>
                <p class="text-sm font-bold text-orange-600">
                    {{ $peserta->check_out_at ? $peserta->check_out_at->format('H:i') : '—' }}
                </p>
                @if($peserta->check_out_at)
                <p class="text-xs text-gray-400">{{ $peserta->check_out_at->translatedFormat('d M') }}</p>
                @endif
            </div>
            <div class="text-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Durasi</p>
                <p class="text-sm font-bold text-green-700">
                    {{ $peserta->durasi_hadir ?? '—' }}
                </p>
            </div>
        </div>

        {{-- Nilai --}}
        @if($peserta->nilai !== null)
        <div class="flex items-center justify-between p-3 bg-purple-50 rounded-xl border border-purple-100">
            <span class="text-sm text-purple-700 font-medium">Nilai</span>
            <span class="text-lg font-bold text-purple-700">{{ number_format($peserta->nilai, 0) }}</span>
        </div>
        @endif

        {{-- No Sertifikat --}}
        @if($peserta->nomor_sertifikat)
        <div class="p-4 bg-green-50 rounded-xl border border-green-200 text-center">
            <p class="text-xs text-green-600 font-semibold uppercase tracking-wide mb-1">Nomor Sertifikat</p>
            <p class="text-base font-bold font-mono text-green-800 tracking-wider">{{ $peserta->nomor_sertifikat }}</p>
            @if($peserta->sertifikat_at)
            <p class="text-xs text-green-500 mt-1">Diterbitkan {{ $peserta->sertifikat_at->translatedFormat('d F Y') }}</p>
            @endif
        </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 text-center">
        <p class="text-xs text-gray-400">
            Data ini diverifikasi oleh sistem HR Manajemen.<br>
            Dokumen cetak oleh: {{ $iht->penandatangan_nama ?? 'HR/Admin' }}
        </p>
    </div>

</div>

</body>
</html>
