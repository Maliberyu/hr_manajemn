@extends('layouts.app')
@section('title', 'Setting Atasan Langsung')
@section('page-title', 'Setting Atasan Langsung')
@section('page-subtitle', 'Pemetaan karyawan ↔ atasan untuk approval Cuti & Lembur')

@section('content')
<div class="space-y-4">

    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    {{-- Alert karyawan belum diset --}}
    @if($totalBelumSet > 0)
    <div class="px-4 py-3 bg-orange-50 border border-orange-200 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <p class="text-sm font-semibold text-orange-800">
                    {{ $totalBelumSet }} karyawan belum memiliki atasan langsung
                </p>
                <p class="text-xs text-orange-600 mt-0.5">
                    Pengajuan cuti & lembur mereka akan langsung diteruskan ke HRD.
                </p>
            </div>
        </div>
        <a href="{{ request()->fullUrlWithQuery(['belum_diset' => 1]) }}"
           class="px-3 py-1.5 text-xs bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
            Tampilkan
        </a>
    </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" action="{{ route('pengaturan.atasan.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cari Karyawan</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / NIK..."
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none w-48">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Departemen</label>
                <select name="departemen" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                    <option value="">Semua Departemen</option>
                    @foreach($departemen as $depId => $depNama)
                    <option value="{{ $depId }}" {{ request('departemen') === $depId ? 'selected' : '' }}>
                        {{ $depNama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer pb-2">
                    <input type="checkbox" name="belum_diset" value="1"
                           {{ request('belum_diset') ? 'checked' : '' }}
                           class="rounded text-blue-600">
                    Hanya yang belum diset
                </label>
            </div>
            <button type="submit"
                    class="px-4 py-2 text-sm bg-gray-700 text-white rounded-xl hover:bg-gray-800 transition">
                Filter
            </button>
            @if(request()->hasAny(['q','departemen','belum_diset']))
            <a href="{{ route('pengaturan.atasan.index') }}"
               class="px-4 py-2 text-sm border border-gray-200 text-gray-500 hover:bg-gray-50 rounded-xl transition">
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Tabel bulk edit --}}
    <form method="POST" action="{{ route('pengaturan.atasan.bulk') }}">
        @csrf
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-gray-700">
                    Daftar Karyawan
                    <span class="text-gray-400 font-normal text-xs ml-1">({{ $pegawai->total() }} total)</span>
                </p>
                <button type="submit"
                        class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                    Simpan Semua
                </button>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Karyawan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Departemen</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Atasan Langsung</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Keterangan</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pegawai as $idx => $p)
                    @php $rec = $p->atasanRecord; @endphp
                    <tr class="{{ $rec ? '' : 'bg-orange-50/40' }} hover:bg-gray-50/50">
                        <td class="px-4 py-3">
                            <input type="hidden" name="atasan[{{ $idx }}][nik]" value="{{ $p->nik }}">
                            <div class="font-medium text-gray-800">{{ $p->nama }}</div>
                            <div class="text-xs text-gray-400">{{ $p->nik }} · {{ $p->jbtn }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">
                            {{ $p->departemenRef?->nama ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <select name="atasan[{{ $idx }}][user_id]"
                                    class="w-full px-2 py-1.5 text-sm border rounded-lg focus:ring-1 focus:ring-blue-400 focus:outline-none bg-white
                                           {{ $rec ? 'border-gray-200' : 'border-orange-300' }}">
                                <option value="">— Belum diset (langsung ke HRD) —</option>
                                @foreach($atasanList as $a)
                                <option value="{{ $a->id }}"
                                        {{ ($rec?->user_id == $a->id) ? 'selected' : '' }}>
                                    {{ $a->nama }}
                                    ({{ \App\Models\User::ROLES[$a->role] ?? $a->role }})
                                    @if($a->jabatan) — {{ $a->jabatan }}@endif
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" name="atasan[{{ $idx }}][keterangan]"
                                   value="{{ $rec?->keterangan }}"
                                   placeholder="misal: PJ IGD"
                                   class="w-full px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-400 focus:outline-none">
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($rec)
                            <span class="inline-block w-2 h-2 rounded-full bg-green-400" title="Sudah diset"></span>
                            @else
                            <span class="inline-block w-2 h-2 rounded-full bg-orange-400" title="Belum diset"></span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada karyawan ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($pegawai->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-500">
                    Menampilkan {{ $pegawai->firstItem() }}–{{ $pegawai->lastItem() }} dari {{ $pegawai->total() }}
                </p>
                {{ $pegawai->links() }}
            </div>
            @endif
        </div>

        {{-- Tombol simpan bawah --}}
        <div class="flex justify-end mt-3">
            <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
                Simpan Semua Perubahan
            </button>
        </div>
    </form>

</div>
@endsection
