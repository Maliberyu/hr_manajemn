@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', $user->nama)

@section('content')
<div class="max-w-xl mx-auto space-y-4">

    {{-- Form Edit ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Informasi User</h3>

        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif
        @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('pengaturan.users.update', $user) }}"
              x-data="userEditForm()" class="space-y-4">
            @csrf @method('PUT')

            {{-- Link Pegawai --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Link ke Pegawai</label>
                <select name="nik" x-model="nik" @change="pilihPegawai()"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Tidak dilink --</option>
                    @foreach($pegawai as $p)
                    <option value="{{ $p->nik }}"
                            data-nama="{{ $p->nama }}"
                            {{ old('nik', $user->nik) === $p->nik ? 'selected' : '' }}>
                        {{ $p->nama }} — {{ $p->jbtn }} ({{ $p->nik }})
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Nama --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required maxlength="150"
                       x-model="nama"
                       value="{{ old('nama', $user->nama) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required
                       value="{{ old('email', $user->email) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Jabatan --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan</label>
                <input type="text" name="jabatan" maxlength="100"
                       value="{{ old('jabatan', $user->jabatan) }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Role --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Role / Hak Akses <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(\App\Models\User::ROLES as $key => $label)
                    @php
                        $colors = ['karyawan'=>'blue','atasan'=>'yellow','hrd'=>'purple','admin'=>'gray'];
                        $c = $colors[$key];
                    @endphp
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="{{ $key }}"
                               {{ old('role', $user->role) === $key ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="px-3 py-2.5 rounded-xl border-2 border-gray-100 peer-checked:border-{{ $c }}-400 peer-checked:bg-{{ $c }}-50 transition text-center">
                            <p class="text-sm font-semibold text-gray-700">{{ $label }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status Akun</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="aktif"
                               {{ old('status', $user->status) === 'aktif' ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="nonaktif"
                               {{ old('status', $user->status) === 'nonaktif' ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Nonaktif</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Simpan Perubahan
                </button>
                <a href="{{ route('pengaturan.users.index') }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>

    {{-- Reset Password ──────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6"
         x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between text-sm font-semibold text-gray-700">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Reset Password
            </span>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-collapse class="mt-4">
            <form method="POST" action="{{ route('pengaturan.users.reset-password', $user) }}"
                  class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Password Baru</label>
                        <input type="password" name="password" required minlength="6"
                               placeholder="Min. 6 karakter"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Konfirmasi</label>
                        <input type="password" name="password_confirmation" required
                               placeholder="Ulangi password"
                               class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400">
                    </div>
                </div>
                <button type="submit"
                        class="w-full py-2 text-sm bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition">
                    Reset Password
                </button>
            </form>
        </div>
    </div>

    {{-- Info user --}}
    <div class="bg-gray-50 rounded-2xl border border-gray-100 p-4 text-xs text-gray-500 space-y-1">
        <p>Dibuat: {{ $user->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}</p>
        <p>Login terakhir: {{ $user->last_login_at?->translatedFormat('d F Y, H:i') ?? 'Belum pernah login' }}</p>
        <p>IP terakhir: {{ $user->last_login_ip ?? '-' }}</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
const pegawaiData = @json($pegawai->keyBy('nik'));
function userEditForm() {
    return {
        nik: '{{ old('nik', $user->nik ?? '') }}',
        nama: '{{ old('nama', $user->nama) }}',
        pilihPegawai() {
            if (!this.nik) return;
            const p = pegawaiData[this.nik];
            if (p) this.nama = p.nama || this.nama;
        }
    };
}
</script>
@endpush
