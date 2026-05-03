@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')
@section('page-subtitle', 'Kelola akun login karyawan, atasan, dan HRD')

@section('content')

{{-- Summary Cards ────────────────────────────────────────────────────────── --}}
@php
    $roleCounts = \App\Models\User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total','role');
@endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    @foreach(\App\Models\User::ROLES as $key => $label)
    @php
        $colors = ['karyawan'=>'blue','atasan'=>'yellow','hrd'=>'purple','admin'=>'gray'];
        $c = $colors[$key] ?? 'gray';
    @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-{{ $c }}-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-{{ $c }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <div>
            <p class="text-xl font-bold text-gray-800">{{ $roleCounts[$key] ?? 0 }}</p>
            <p class="text-xs text-gray-500">{{ $label }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Filter + Tambah ─────────────────────────────────────────────────────── --}}
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email / NIK..."
           class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 w-56">
    <select name="role" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
        <option value="">Semua Role</option>
        @foreach(\App\Models\User::ROLES as $key => $label)
        <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Filter</button>
    <a href="{{ route('pengaturan.users.index') }}" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition">Reset</a>
    <div class="ml-auto">
        <a href="{{ route('pengaturan.users.create') }}"
           class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User
        </a>
    </div>
</form>

{{-- Flash ─────────────────────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
@endif

{{-- Tabel ────────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">Daftar Akun User</h3>
        <span class="text-xs text-gray-400">{{ $users->total() }} user</span>
    </div>

    @if($users->isEmpty())
    <div class="flex flex-col items-center gap-2 py-14 text-gray-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="text-sm font-medium">Belum ada user</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">User</th>
                    <th class="px-4 py-3 text-left">NIK / Pegawai</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Login Terakhir</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $u)
                @php
                    $roleColors = ['karyawan'=>'blue','atasan'=>'yellow','hrd'=>'purple','admin'=>'gray'];
                    $rc = $roleColors[$u->role ?? 'karyawan'] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $u->nama }}</p>
                        <p class="text-xs text-gray-400">{{ $u->email }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @if($u->pegawai)
                        <p class="text-sm text-gray-700">{{ $u->pegawai->nama }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $u->nik }}</p>
                        @elseif($u->nik)
                        <p class="text-xs text-orange-500 font-mono">{{ $u->nik }} (tidak ditemukan)</p>
                        @else
                        <span class="text-xs text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium text-{{ $rc }}-700 bg-{{ $rc }}-50 px-2 py-0.5 rounded-full">
                            {{ $u->role_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium {{ $u->status === 'aktif' ? 'text-green-700 bg-green-50' : 'text-red-600 bg-red-50' }} px-2 py-0.5 rounded-full">
                            {{ ucfirst($u->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $u->last_login_at?->diffForHumans() ?? 'Belum pernah' }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('pengaturan.users.edit', $u) }}"
                               class="p-1.5 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('pengaturan.users.destroy', $u) }}"
                                  onsubmit="return confirm('Hapus user {{ addslashes($u->nama) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-400 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
