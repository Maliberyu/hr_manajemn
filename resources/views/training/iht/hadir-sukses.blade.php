<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Absensi Berhasil</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">

<div class="w-full max-w-sm text-center">

    {{-- Animasi centang --}}
    <div class="w-24 h-24 rounded-full {{ $jenis === 'masuk' ? 'bg-blue-100' : 'bg-orange-100' }}
                flex items-center justify-center mx-auto mb-6">
        <svg class="w-12 h-12 {{ $jenis === 'masuk' ? 'text-blue-600' : 'text-orange-500' }}"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="text-2xl font-extrabold text-gray-800 mb-1">Berhasil!</h1>
    <p class="text-gray-500 text-sm mb-6">{{ $pesan }}</p>

    {{-- Ringkasan --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-left mb-6">
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Nama</span>
                <span class="text-sm font-semibold text-gray-800">{{ $peserta->pegawai?->nama }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Training</span>
                <span class="text-sm font-medium text-gray-700 text-right max-w-[200px]">{{ $iht->nama_training }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Jenis Absensi</span>
                <span class="text-sm font-medium {{ $jenis === 'masuk' ? 'text-blue-700' : 'text-orange-600' }}">
                    {{ $jenis === 'masuk' ? 'Masuk' : 'Selesai' }}
                </span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-gray-50">
                <span class="text-xs text-gray-500">Jam Tercatat</span>
                <span class="text-lg font-extrabold {{ $jenis === 'masuk' ? 'text-blue-700' : 'text-orange-600' }}">
                    {{ $jam }}
                </span>
            </div>
            @if($jenis === 'selesai' && $peserta->check_in_at && $peserta->durasi_hadir)
            <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Durasi Hadir</span>
                <span class="text-sm font-semibold text-green-700">{{ $peserta->durasi_hadir }}</span>
            </div>
            @endif
        </div>
    </div>

    <p class="text-xs text-gray-400">Halaman ini bisa ditutup. Terima kasih!</p>

</div>

</body>
</html>
