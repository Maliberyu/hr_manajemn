@extends('layouts.app')
@section('title', 'Tambah User')
@section('page-title', 'Tambah User')
@section('page-subtitle', 'Buat akun login untuk karyawan, atasan, atau HRD')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

        @if($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('pengaturan.users.store') }}"
              x-data="userForm()" class="space-y-4">
            @csrf

            {{-- Pilih Pegawai ─────────────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Link ke Pegawai
                    <span class="text-gray-400 font-normal">(opsional — untuk akses ESS / absensi)</span>
                </label>
                <select name="nik" x-model="nik" @change="pilihPegawai()"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    <option value="">-- Tidak dilink ke pegawai --</option>
                    @foreach($pegawai as $p)
                    <option value="{{ $p->nik }}" data-nama="{{ $p->nama }}" data-email="{{ $p->email ?? '' }}">
                        {{ $p->nama }} — {{ $p->jbtn }} ({{ $p->nik }})
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-blue-600 mt-1">
                    Pilih pegawai untuk otomatis isi nama & email. Role "Karyawan" harus dilink agar bisa absensi.
                </p>
            </div>

            {{-- Nama ─────────────────────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nama <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required maxlength="150"
                       x-model="nama" :placeholder="nik ? '' : 'Nama lengkap'"
                       value="{{ old('nama') }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Email ────────────────────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required
                       x-model="email"
                       value="{{ old('email') }}"
                       placeholder="email@domain.com (isi manual)"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Password ──────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="6"
                           placeholder="Min. 6 karakter"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                           placeholder="Ulangi password"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            {{-- Role ─────────────────────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Role / Hak Akses <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(\App\Models\User::ROLES as $key => $label)
                    @php
                        $desc = match($key) {
                            'karyawan' => 'Akses portal ESS — absensi & cuti',
                            'atasan'   => 'Approve cuti level 1 (atasan langsung)',
                            'hrd'      => 'Approve cuti level 2, kelola data HR',
                            'admin'    => 'Akses penuh semua fitur',
                        };
                        $colors = ['karyawan'=>'blue','atasan'=>'yellow','hrd'=>'purple','admin'=>'gray'];
                        $c = $colors[$key];
                    @endphp
                    <label class="relative cursor-pointer">
                        <input type="radio" name="role" value="{{ $key }}"
                               {{ old('role', 'karyawan') === $key ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="px-3 py-2.5 rounded-xl border-2 border-gray-100 peer-checked:border-{{ $c }}-400 peer-checked:bg-{{ $c }}-50 transition">
                            <p class="text-sm font-semibold text-gray-700 peer-checked:text-{{ $c }}-700">{{ $label }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $desc }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Jabatan ───────────────────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan <span class="text-gray-400 font-normal">(opsional)</span></label>
                <input type="text" name="jabatan" maxlength="100"
                       value="{{ old('jabatan') }}"
                       placeholder="cth: Staff HRD, Kepala Bagian"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            {{-- Status ───────────────────────────────────────────────────── --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status Akun</label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="aktif" {{ old('status','aktif') === 'aktif' ? 'checked' : '' }}
                               class="text-green-600">
                        <span class="text-sm text-gray-700">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="nonaktif" {{ old('status') === 'nonaktif' ? 'checked' : '' }}
                               class="text-red-500">
                        <span class="text-sm text-gray-700">Nonaktif</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="submit"
                        class="flex-1 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition">
                    Buat User
                </button>
                <a href="{{ route('pengaturan.users.index') }}"
                   class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Data pegawai dari server untuk autofill
const pegawaiData = @json($pegawai->keyBy('nik'));

function userForm() {
    return {
        nik: '{{ old('nik', '') }}',
        nama: '{{ old('nama', '') }}',
        email: '{{ old('email', '') }}',

        pilihPegawai() {
            if (!this.nik) return;
            const p = pegawaiData[this.nik];
            if (!p) return;
            this.nama = p.nama || this.nama;
        },
        init() {
            if (this.nik) this.pilihPegawai();
        }
    };
}
</script>
@endpush
