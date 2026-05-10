<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Akun — HR Manajemen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 flex items-center justify-center p-4">

<div class="w-full max-w-lg" x-data="registerForm()">

    {{-- Logo --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-14 h-14 bg-white rounded-2xl shadow-lg mb-3">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-white">HR Manajemen</h1>
        <p class="text-blue-200 text-xs mt-0.5">Buat akun baru</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-2xl p-7">
        <h2 class="text-base font-semibold text-gray-800 mb-5">Formulir Pendaftaran</h2>

        {{-- Error --}}
        @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5 text-sm">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
        </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf

            {{-- ── Nama + Email ──────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required maxlength="150"
                           placeholder="Nama sesuai KTP"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nama') border-red-400 bg-red-50 @enderror">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="nama@perusahaan.com"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-400 bg-red-50 @enderror">
                </div>
            </div>

            {{-- ── Jabatan ───────────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                <input type="text" name="jabatan" value="{{ old('jabatan') }}" required maxlength="100"
                       placeholder="Contoh: Perawat, Staff Keuangan, dll"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('jabatan') border-red-400 bg-red-50 @enderror">
            </div>

            {{-- ── Password ──────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input :type="showPass ? 'text' : 'password'" name="password" required minlength="8"
                           placeholder="Min. 8 karakter"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password_confirmation" required
                               placeholder="Ulangi password"
                               class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="button" @click="showPass = !showPass"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                            <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c0 1.657-3.134 5-9 5S3 13.657 3 12 6.134 7 12 7s9 3.343 9 5z"/>
                            </svg>
                            <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5.866 0-9-3.343-9-7 0-.656.122-1.29.344-1.898M9.88 9.88A3 3 0 0115 12m-3-9c5.866 0 9 3.343 9 7a9.96 9.96 0 01-1.17 3M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ── Cari Data Pegawai SIK ─────────────────────────────── --}}
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button type="button" @click="showPegawai = !showPegawai"
                        class="w-full px-4 py-3 flex items-center justify-between text-sm bg-gray-50 hover:bg-gray-100 transition">
                    <span class="flex items-center gap-2 text-gray-700 font-medium">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Hubungkan ke Data Pegawai SIK
                        <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </span>
                    <span x-show="selectedPegawai" class="text-xs text-green-600 font-semibold" x-text="selectedPegawai?.nama"></span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="showPegawai ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="showPegawai" x-collapse class="px-4 pb-4 pt-3 space-y-3 border-t border-gray-100">
                    <p class="text-xs text-gray-500">Cari nama atau NIK Anda di database SIK untuk menghubungkan akun dengan data kepegawaian:</p>

                    <div class="relative">
                        <input type="text" x-model="queryPegawai"
                               @input.debounce.400ms="cariPegawai()"
                               placeholder="Ketik nama atau NIK..."
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 pr-8">
                        <span x-show="loadingPegawai" class="absolute right-3 top-2.5">
                            <svg class="w-4 h-4 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </span>
                    </div>

                    {{-- Hasil --}}
                    <div x-show="hasilPegawai.length > 0"
                         class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-50 max-h-40 overflow-y-auto">
                        <template x-for="p in hasilPegawai" :key="p.nik">
                            <button type="button" @click="pilihPegawai(p)"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-blue-50 transition text-left">
                                <img :src="p.foto" class="w-8 h-8 rounded-lg object-cover flex-shrink-0"
                                     onerror="this.src='/hr_manajemn/public/images/avatar-default.svg'">
                                <div>
                                    <p class="text-sm font-medium text-gray-800" x-text="p.nama"></p>
                                    <p class="text-xs text-gray-400" x-text="p.jbtn + ' — NIK: ' + p.nik"></p>
                                </div>
                                <svg x-show="selectedPegawai?.nik === p.nik" class="w-4 h-4 text-green-500 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </template>
                    </div>

                    <p x-show="queryPegawai.length > 1 && hasilPegawai.length === 0 && !loadingPegawai"
                       class="text-xs text-gray-400 text-center py-1">Tidak ditemukan.</p>

                    {{-- Dipilih --}}
                    <div x-show="selectedPegawai" class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-3 py-2">
                        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p class="text-xs text-green-700 flex-1">
                            Terpilih: <strong x-text="selectedPegawai?.nama"></strong>
                            <span class="text-green-500 ml-1" x-text="'(NIK: ' + selectedPegawai?.nik + ')'"></span>
                        </p>
                        <button type="button" @click="selectedPegawai = null; queryPegawai = ''"
                                class="text-xs text-red-500 hover:text-red-700">Batal</button>
                    </div>

                    <input type="hidden" name="nik" :value="selectedPegawai?.nik">
                </div>
            </div>

            {{-- ── Atasan Langsung (muncul jika pegawai dipilih) ────── --}}
            <div x-show="selectedPegawai" x-collapse class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <p class="text-sm font-medium text-gray-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Atasan Langsung
                        <span class="text-xs text-gray-400 font-normal">(opsional)</span>
                    </p>
                </div>
                <div class="px-4 py-3" x-data="{ searchAtasan: '', openAtasan: false }">
                    <div class="relative">
                        <input type="text" x-model="searchAtasan"
                               @focus="openAtasan = true" @click.outside="openAtasan = false"
                               placeholder="Ketik nama atasan..."
                               autocomplete="off"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <input type="hidden" name="atasan_user_id" id="regAtasanId">

                        <div x-show="openAtasan && searchAtasan.length > 0"
                             class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden max-h-40 overflow-y-auto">
                            @foreach($calonAtasan as $ca)
                            <button type="button"
                                    x-show="'{{ strtolower($ca->nama) }}'.includes(searchAtasan.toLowerCase())"
                                    @click="searchAtasan = '{{ $ca->nama }}'; document.getElementById('regAtasanId').value = '{{ $ca->id }}'; openAtasan = false"
                                    class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-blue-50 transition text-left">
                                <div class="w-7 h-7 rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-bold flex-shrink-0">
                                    {{ substr($ca->nama, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $ca->nama }}</p>
                                    <p class="text-xs text-gray-400">{{ $ca->jabatan }} · {{ \App\Models\User::ROLES[$ca->role] ?? $ca->role }}</p>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-xl text-sm transition shadow-md shadow-blue-200">
                Daftar Sekarang
            </button>
        </form>

        <p class="text-center text-gray-500 text-xs mt-4">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Masuk di sini</a>
        </p>
    </div>

    <p class="text-center text-blue-200 text-xs mt-5">
        &copy; {{ date('Y') }} HR Manajemen. By IT RSIA Respati; All rights reserved.
    </p>
</div>

<script>
function registerForm() {
    return {
        showPass:       false,
        showPegawai:    false,
        queryPegawai:   '',
        hasilPegawai:   [],
        loadingPegawai: false,
        selectedPegawai: null,

        async cariPegawai() {
            if (this.queryPegawai.length < 2) { this.hasilPegawai = []; return; }
            this.loadingPegawai = true;
            try {
                const res = await fetch('{{ parse_url(route('profil.search'), PHP_URL_PATH) }}?q=' + encodeURIComponent(this.queryPegawai), {
                    headers: { 'Accept': 'application/json' }
                });
                this.hasilPegawai = await res.json();
            } finally {
                this.loadingPegawai = false;
            }
        },

        pilihPegawai(p) {
            this.selectedPegawai = p;
            this.queryPegawai    = p.nama;
            this.hasilPegawai    = [];
        },
    };
}
</script>
</body>
</html>
