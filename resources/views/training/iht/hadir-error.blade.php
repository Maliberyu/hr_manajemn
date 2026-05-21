<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Absensi — Info</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">

<div class="w-full max-w-sm text-center">

    <div class="w-24 h-24 rounded-full {{ ($sudah ?? false) ? 'bg-yellow-100' : 'bg-red-100' }}
                flex items-center justify-center mx-auto mb-6">
        @if($sudah ?? false)
        <svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        @else
        <svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        @endif
    </div>

    <h1 class="text-xl font-bold text-gray-800 mb-2">
        {{ ($sudah ?? false) ? 'Sudah Tercatat' : 'Tidak Dapat Dilanjutkan' }}
    </h1>
    <p class="text-sm text-gray-500 mb-6">{{ $pesan }}</p>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-left">
        <p class="text-xs text-gray-500 mb-1">Training</p>
        <p class="text-sm font-semibold text-gray-800">{{ $iht->nama_training }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $iht->tanggal_mulai->translatedFormat('d M Y') }}</p>
    </div>

    <p class="text-xs text-gray-400 mt-6">
        Jika ada masalah, hubungi HRD.
    </p>
</div>

</body>
</html>
