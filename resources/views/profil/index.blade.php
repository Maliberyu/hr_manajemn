@extends('layouts.app')
@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Informasi akun dan data kepegawaian')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Info Akun ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Informasi Akun</h3>
        </div>
        <div class="p-5">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-2xl font-bold uppercase flex-shrink-0">
                    {{ substr($user->nama ?? 'U', 0, 1) }}
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-800">{{ $user->nama }}</p>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                        {{ match($user->role) {
                            'admin'    => 'bg-purple-100 text-purple-700',
                            'hrd'      => 'bg-blue-100 text-blue-700',
                            'atasan'   => 'bg-orange-100 text-orange-700',
                            default    => 'bg-gray-100 text-gray-600',
                        } }}">
                        {{ $user->role_label }}
                    </span>
                </div>
            </div>

            <dl class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Email</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $user->email }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">NIK</dt>
                    <dd class="text-sm font-medium text-gray-800 font-mono">{{ $user->nik ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Jabatan</dt>
                    <dd class="text-sm font-medium text-gray-800">{{ $user->jabatan ?? $pegawai?->jbtn ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Login Terakhir</dt>
                    <dd class="text-sm text-gray-600">
                        {{ $user->last_login_at?->translatedFormat('d F Y, H:i') ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- ── Data Kepegawaian ─────────────────────────────────────────────── --}}
    @if($pegawai)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700">Data Kepegawaian</h3>
            @if(auth()->user()->hasRole(['hrd','admin']))
            <a href="{{ route('karyawan.show', $pegawai) }}"
               class="text-xs text-blue-600 hover:underline">
                Lihat Detail Lengkap →
            </a>
            @else
            <a href="{{ route('karyawan.show', $pegawai) }}"
               class="text-xs text-blue-600 hover:underline">
                Lihat Profil Karyawan →
            </a>
            @endif
        </div>
        <div class="p-5">
            <div class="flex items-center gap-4 mb-4">
                <img src="{{ $pegawai->foto_url }}"
                     class="w-14 h-14 rounded-xl object-cover border-2 border-gray-100"
                     onerror="this.src='{{ asset('images/avatar-default.svg') }}'">
                <div>
                    <p class="font-semibold text-gray-800">{{ $pegawai->nama }}</p>
                    <p class="text-sm text-gray-500">{{ $pegawai->jbtn }}</p>
                    <p class="text-xs text-gray-400">{{ $pegawai->departemenRef?->nama }}</p>
                </div>
            </div>
            <dl class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">NIK Kepegawaian</dt>
                    <dd class="text-sm font-mono font-medium text-gray-800">{{ $pegawai->nik }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Pendidikan</dt>
                    <dd class="text-sm text-gray-700">{{ $pegawai->pendidikan ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Status Kerja</dt>
                    <dd class="text-sm text-gray-700">{{ $pegawai->status_kerja ?? $pegawai->stts_kerja ?? '—' }}</dd>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Mulai Kerja</dt>
                    <dd class="text-sm text-gray-700">
                        {{ $pegawai->mulai_kerja?->translatedFormat('d F Y') ?? '—' }}
                        @if($pegawai->mulai_kerja)
                        <span class="text-gray-400 text-xs ml-1">({{ $pegawai->masa_kerja }})</span>
                        @endif
                    </dd>
                </div>
                <div class="flex items-center justify-between py-2">
                    <dt class="text-xs text-gray-400 uppercase tracking-wide">Atasan Langsung</dt>
                    <dd class="text-sm font-medium text-gray-800">
                        @if($atasan)
                        <div class="text-right">
                            <p class="font-semibold">{{ $atasan->nama }}</p>
                            <p class="text-xs text-gray-400">{{ $atasan->jabatan }}</p>
                        </div>
                        @else
                        <span class="text-gray-400">Belum diset</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-2">Data Kepegawaian</h3>
        <p class="text-sm text-gray-400">Akun ini belum terhubung ke data pegawai.</p>
    </div>
    @endif

    {{-- ── Ganti Password ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Ganti Password</h3>
        </div>
        <form method="POST" action="{{ parse_url(route('profil.password'), PHP_URL_PATH) }}"
              class="p-5 space-y-4">
            @csrf

            @if($errors->any())
            <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Password Lama <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_lama" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       placeholder="Masukkan password saat ini">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Password Baru <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_baru" required minlength="8"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       placeholder="Minimal 8 karakter">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Konfirmasi Password Baru <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_baru_confirmation" required
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400"
                       placeholder="Ulangi password baru">
            </div>

            <button type="submit"
                    class="w-full py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                Simpan Password Baru
            </button>
        </form>
    </div>

</div>
@endsection
