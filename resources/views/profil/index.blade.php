@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun dan data kepegawaian')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Info Akun ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-xl font-bold uppercase flex-shrink-0">
                {{ substr($user->nama ?? 'U', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-lg font-bold text-gray-800 truncate">{{ $user->nama }}</p>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold mt-1
                    {{ match($user->role) { 'admin'=>'bg-purple-100 text-purple-700','hrd'=>'bg-blue-100 text-blue-700','atasan'=>'bg-orange-100 text-orange-700',default=>'bg-gray-100 text-gray-600' } }}">
                    {{ $user->role_label }}
                </span>
            </div>
            @if($user->last_login_at)
            <div class="text-right text-xs text-gray-400 flex-shrink-0 hidden sm:block">
                <p>Login terakhir</p>
                <p class="font-medium text-gray-600">{{ $user->last_login_at->translatedFormat('d M Y') }}</p>
                <p>{{ $user->last_login_at->format('H:i') }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Foto Profil ──────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Foto Profil</h3>
        </div>
        <div class="p-5">
            <div class="flex items-center gap-5">
                <img src="{{ $pegawai?->foto_url ?? asset('images/avatar-default.svg') }}"
                     class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-100 flex-shrink-0"
                     onerror="this.src='{{ asset('images/avatar-default.svg') }}'">
                <div class="flex-1">
                    @if($pegawai)
                    <form method="POST" action="{{ parse_url(route('profil.foto'), PHP_URL_PATH) }}"
                          enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        @error('photo')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                        <p class="text-xs text-gray-400">JPG, PNG, WEBP. Maks 2MB. Akan di-crop persegi 400x400.</p>
                        <button type="submit"
                                class="px-4 py-1.5 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Upload Foto
                        </button>
                    </form>
                    @else
                    <p class="text-sm text-gray-400">Hubungkan ke data pegawai terlebih dahulu untuk mengganti foto.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Link / Ganti Data Pegawai SIK ───────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden"
         x-data="profilPegawai()">

        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Data Kepegawaian</h3>
            @if($pegawai)
            <button type="button" @click="gantiMode = !gantiMode"
                    class="text-xs text-blue-600 hover:underline" x-text="gantiMode ? 'Batal' : 'Ganti'"></button>
            @endif
        </div>

        <div class="p-5">
            {{-- Sudah terhubung --}}
            @if($pegawai)
            <div x-show="!gantiMode" class="flex items-center gap-4">
                <img src="{{ $pegawai->foto_url }}"
                     class="w-12 h-12 rounded-xl object-cover border border-gray-100 flex-shrink-0"
                     onerror="this.src='{{ asset('images/avatar-default.svg') }}'">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800">{{ $pegawai->nama }}</p>
                    <p class="text-xs text-gray-500">{{ $pegawai->jbtn }} · {{ $pegawai->departemenRef?->nama }}</p>
                    <p class="text-xs font-mono text-gray-400 mt-0.5">NIK: {{ $pegawai->nik }}</p>
                </div>
                @if(auth()->user()->hasRole(['hrd','admin']))
                <a href="{{ route('karyawan.show', $pegawai) }}"
                   class="px-3 py-1.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition flex-shrink-0">
                    Detail
                </a>
                @endif
            </div>
            @endif

            {{-- Form cari & ganti / pertama kali link --}}
            <div @if($pegawai) x-show="gantiMode" style="display:none" @endif class="space-y-3">
                <p class="text-xs text-gray-500">Cari nama atau NIK pegawai dari database Kepegawaian:</p>

                {{-- Search input --}}
                <div class="relative">
                    <input type="text" x-model="query" @input.debounce.300ms="cari()"
                           @focus="showResults = results.length > 0"
                           placeholder="Nama atau NIK pegawai..."
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 pr-8">
                    <span x-show="loading" class="absolute right-3 top-2.5">
                        <svg class="w-4 h-4 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </span>
                </div>

                {{-- Hasil pencarian --}}
                <div x-show="showResults && results.length > 0"
                     class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-50">
                    <template x-for="p in results" :key="p.nik">
                        <button type="button" @click="pilih(p)"
                                class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-blue-50 transition text-left">
                            <img :src="p.foto" class="w-8 h-8 rounded-lg object-cover flex-shrink-0"
                                 onerror="this.src='{{ asset('images/avatar-default.svg') }}'">
                            <div>
                                <p class="text-sm font-medium text-gray-800" x-text="p.nama"></p>
                                <p class="text-xs text-gray-400" x-text="p.jbtn + ' · NIK: ' + p.nik"></p>
                            </div>
                        </button>
                    </template>
                </div>

                <p x-show="showResults && results.length === 0 && query.length > 1"
                   class="text-xs text-gray-400 text-center py-2">Tidak ditemukan.</p>

                {{-- Form konfirmasi --}}
                <div x-show="selected" class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                    <p class="text-xs font-semibold text-blue-700 mb-2">Konfirmasi pilihan:</p>
                    <div class="flex items-center gap-3 mb-3">
                        <img :src="selected?.foto" class="w-10 h-10 rounded-lg object-cover flex-shrink-0"
                             onerror="this.src='{{ asset('images/avatar-default.svg') }}'">
                        <div>
                            <p class="text-sm font-semibold text-gray-800" x-text="selected?.nama"></p>
                            <p class="text-xs text-gray-500" x-text="selected?.jbtn + ' · ' + selected?.nik"></p>
                        </div>
                    </div>
                    <form method="POST" action="{{ parse_url(route('profil.link'), PHP_URL_PATH) }}">
                        @csrf
                        <input type="hidden" name="nik" :value="selected?.nik">
                        <button type="submit"
                                class="w-full py-2 text-xs font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            Hubungkan ke Akun Saya
                        </button>
                    </form>
                </div>

                @error('nik')
                <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- ── Atasan Langsung ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Atasan Langsung</h3>
        </div>
        <div class="p-5 space-y-4">
            {{-- Atasan saat ini --}}
            @if($atasan)
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center text-orange-700 font-bold text-sm flex-shrink-0">
                    {{ substr($atasan->nama ?? 'A', 0, 1) }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">{{ $atasan->nama }}</p>
                    <p class="text-xs text-gray-500">{{ $atasan->jabatan }} · {{ $atasan->role_label }}</p>
                </div>
                <span class="text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded-full font-medium">Aktif</span>
            </div>
            @else
            <p class="text-sm text-gray-400 text-center py-2">Belum ada atasan yang ditetapkan.</p>
            @endif

            {{-- Form ganti atasan --}}
            @if($pegawai)
            <form method="POST" action="{{ parse_url(route('profil.atasan'), PHP_URL_PATH) }}"
                  class="space-y-3" x-data="{ search: '', open: false }">
                @csrf
                @error('atasan_user_id')
                <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror

                <div class="relative">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        {{ $atasan ? 'Ganti Atasan' : 'Pilih Atasan' }}
                    </label>
                    <input type="text" x-model="search" @focus="open = true" @click.outside="open = false"
                           placeholder="Ketik nama atasan..."
                           autocomplete="off"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">

                    <input type="hidden" name="atasan_user_id" id="atasanUserId">

                    <div x-show="open && search.length > 0"
                         class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                        @foreach($calonAtasan as $ca)
                        <button type="button"
                                x-show="'{{ strtolower($ca->nama) }}'.includes(search.toLowerCase())"
                                @click="search = '{{ $ca->nama }}'; document.getElementById('atasanUserId').value = '{{ $ca->id }}'; open = false"
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

                <button type="submit"
                        class="w-full py-2 text-sm font-semibold bg-orange-500 hover:bg-orange-600 text-white rounded-xl transition">
                    Simpan Atasan Langsung
                </button>
            </form>
            @else
            <p class="text-xs text-gray-400">Hubungkan ke data pegawai terlebih dahulu.</p>
            @endif
        </div>
    </div>

    {{-- ── Ganti Password ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Ganti Password</h3>
        </div>
        <form method="POST" action="{{ parse_url(route('profil.password'), PHP_URL_PATH) }}"
              class="p-5 space-y-4">
            @csrf
            @if($errors->hasAny(['password_lama', 'password_baru']))
            <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                @foreach($errors->only(['password_lama','password_baru']) as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password Lama <span class="text-red-500">*</span></label>
                <input type="password" name="password_lama" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       placeholder="Password saat ini">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password_baru" required minlength="8"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       placeholder="Minimal 8 karakter">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                <input type="password" name="password_baru_confirmation" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       placeholder="Ulangi password baru">
            </div>
            <button type="submit"
                    class="w-full py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">
                Simpan Password Baru
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
function profilPegawai() {
    return {
        query:       '',
        results:     [],
        loading:     false,
        showResults: false,
        selected:    null,
        gantiMode:   false,

        async cari() {
            if (this.query.length < 2) { this.results = []; this.showResults = false; return; }
            this.loading = true;
            try {
                const res  = await fetch('{{ parse_url(route('profil.search'), PHP_URL_PATH) }}?q=' + encodeURIComponent(this.query), {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                this.results     = await res.json();
                this.showResults = true;
            } finally {
                this.loading = false;
            }
        },

        pilih(p) {
            this.selected    = p;
            this.query       = p.nama;
            this.showResults = false;
        },
    };
}
</script>
@endpush
