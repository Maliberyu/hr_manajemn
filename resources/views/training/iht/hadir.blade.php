<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Absensi Training — {{ $iht->nama_training }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
</style>
</head>
<body class="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-4">

<div class="w-full max-w-sm">

    {{-- Header --}}
    <div class="text-center mb-6">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-3
                    {{ $jenis === 'masuk' ? 'bg-blue-100' : 'bg-orange-100' }}">
            @if($jenis === 'masuk')
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            @else
            <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            @endif
        </div>
        <h1 class="text-lg font-bold text-gray-800">
            Absensi {{ $jenis === 'masuk' ? 'Masuk' : 'Selesai' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">Training · Konfirmasi kehadiran Anda</p>
    </div>

    {{-- Info Training --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Training</p>
        <p class="text-base font-bold text-gray-800">{{ $iht->nama_training }}</p>
        <p class="text-sm text-gray-500 mt-1">{{ $iht->lokasi }}</p>
        <div class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-50 text-xs text-gray-500">
            <span>{{ $iht->tanggal_mulai->translatedFormat('d M Y') }}
                @if(!$iht->tanggal_mulai->equalTo($iht->tanggal_selesai))
                – {{ $iht->tanggal_selesai->translatedFormat('d M Y') }}
                @endif
            </span>
            @if($iht->jam_mulai)
            <span>{{ \Carbon\Carbon::parse($iht->jam_mulai)->format('H:i') }} –
                {{ \Carbon\Carbon::parse($iht->jam_selesai)->format('H:i') }}</span>
            @endif
        </div>
    </div>

    {{-- Info Peserta --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Peserta</p>
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg flex-shrink-0 uppercase">
                {{ substr($peserta->pegawai?->nama ?? '?', 0, 1) }}
            </div>
            <div>
                <p class="font-semibold text-gray-800">{{ $peserta->pegawai?->nama }}</p>
                <p class="text-sm text-gray-500">{{ $peserta->pegawai?->jbtn }}</p>
                <p class="text-xs text-gray-400">{{ $peserta->pegawai?->departemenRef?->nama }}</p>
            </div>
        </div>
    </div>

    {{-- Warning jika scan selesai tapi belum masuk --}}
    @if($belumMasuk)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4 flex items-start gap-2">
        <svg class="w-4 h-4 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-xs text-yellow-700">Absensi masuk belum tercatat. Pastikan Anda sudah scan QR masuk sebelumnya.</p>
    </div>
    @endif

    {{-- Tombol Konfirmasi --}}
    <form method="POST" action="{{ route('iht.hadir.simpan', ['iht' => $iht->id, 'jenis' => $jenis]) }}{{ '?' . http_build_query($request->query()) }}"
          id="formHadir">
        @csrf
        <button type="submit"
                class="w-full py-4 text-base font-bold rounded-2xl text-white transition active:scale-95
                       {{ $jenis === 'masuk' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-500 hover:bg-orange-600' }}"
                id="btnKonfirmasi">
            Konfirmasi Absensi {{ $jenis === 'masuk' ? 'Masuk' : 'Selesai' }}
        </button>
    </form>
    <p class="text-center text-xs text-gray-400 mt-3">
        Waktu saat ini: <span id="jamSekarang"></span>
    </p>

</div>

<script>
// Tampil jam realtime
function updateJam() {
    const now = new Date();
    const jam = now.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    document.getElementById('jamSekarang').textContent = jam;
}
updateJam();
setInterval(updateJam, 1000);

// Prevent double submit
document.getElementById('formHadir').addEventListener('submit', function() {
    const btn = document.getElementById('btnKonfirmasi');
    btn.disabled = true;
    btn.textContent = 'Memproses...';
});
</script>
</body>
</html>
