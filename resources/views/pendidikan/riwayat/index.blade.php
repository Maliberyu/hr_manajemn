@extends('layouts.app')
@section('title', 'Riwayat Pendidikan Karyawan')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Riwayat Pendidikan</h1>
            <p class="text-sm text-gray-500 mt-0.5">Data pendidikan formal seluruh karyawan</p>
        </div>
        <a href="{{ route('pendidikan.beasiswa.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Pengajuan Beasiswa
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="bg-white border border-gray-200 rounded-2xl px-4 py-3 flex flex-wrap gap-3 items-end shadow-sm">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs text-gray-500 mb-1">NIK / Nama</label>
            <input type="text" name="nik" value="{{ request('nik') }}"
                   placeholder="Cari NIK atau nama..."
                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
        </div>
        <div class="w-40">
            <label class="block text-xs text-gray-500 mb-1">Jenjang</label>
            <select name="jenjang" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                <option value="">Semua</option>
                @foreach($jenjangList as $j)
                <option value="{{ $j }}" {{ request('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">Filter</button>
        @if(request()->anyFilled(['nik','jenjang']))
        <a href="{{ route('pendidikan.riwayat.index') }}" class="px-3 py-2 text-gray-500 text-sm hover:text-gray-700">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        @if($riwayats->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left">Karyawan</th>
                        <th class="px-4 py-3 text-left">Jenjang</th>
                        <th class="px-4 py-3 text-left">Institusi / Jurusan</th>
                        <th class="px-4 py-3 text-center">Tahun Lulus</th>
                        <th class="px-4 py-3 text-center">IPK</th>
                        <th class="px-4 py-3 text-center">Ijazah</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($riwayats as $r)
                    <tr class="hover:bg-gray-50 transition" x-data="{ editOpen: false }">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $r->pegawai?->nama ?? $r->nik }}</p>
                            <p class="text-xs text-gray-400">{{ $r->nik }} · {{ $r->pegawai?->jbtn }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-lg">{{ $r->jenjang }}</span>
                                @if($r->is_terakhir)
                                <span class="bg-green-100 text-green-600 text-xs px-2 py-0.5 rounded-lg">Terakhir</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-gray-800">{{ $r->nama_institusi }}</p>
                            @if($r->jurusan)<p class="text-xs text-gray-400">{{ $r->jurusan }}</p>@endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $r->tahun_lulus ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">{{ $r->ipk ? number_format($r->ipk, 2) : '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($r->file_url)
                            <a href="{{ $r->file_url }}" target="_blank"
                               class="text-blue-500 hover:text-blue-700 transition">
                                <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </a>
                            @else
                            <span class="text-gray-300 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="editOpen = !editOpen"
                                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('pendidikan.riwayat.destroy', $r) }}" method="POST"
                                      onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            {{-- Edit inline --}}
                            <div x-show="editOpen" x-cloak class="text-left mt-2">
                                <form action="{{ route('pendidikan.riwayat.update', $r) }}" method="POST"
                                      enctype="multipart/form-data" class="bg-gray-50 border border-gray-200 rounded-xl p-3 space-y-2 w-72">
                                    @csrf @method('PUT')
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-xs text-gray-500">Jenjang</label>
                                            <select name="jenjang" class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs">
                                                @foreach($jenjangList as $j)
                                                <option value="{{ $j }}" {{ $r->jenjang == $j ? 'selected' : '' }}>{{ $j }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs text-gray-500">Tahun Lulus</label>
                                            <input type="number" name="tahun_lulus" value="{{ $r->tahun_lulus }}"
                                                   class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Institusi</label>
                                        <input type="text" name="nama_institusi" value="{{ $r->nama_institusi }}"
                                               class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Jurusan</label>
                                        <input type="text" name="jurusan" value="{{ $r->jurusan }}"
                                               class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs">
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-xs text-gray-500">IPK</label>
                                            <input type="number" step="0.01" name="ipk" value="{{ $r->ipk }}"
                                                   class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs">
                                        </div>
                                        <div class="flex items-end">
                                            <label class="flex items-center gap-1.5 text-xs text-gray-600">
                                                <input type="checkbox" name="is_terakhir" value="1"
                                                       {{ $r->is_terakhir ? 'checked' : '' }}>
                                                Pend. Terakhir
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Upload Ijazah Baru</label>
                                        <input type="file" name="file_ijazah" accept=".pdf,.jpg,.jpeg,.png"
                                               class="w-full text-xs text-gray-500">
                                    </div>
                                    <div class="flex gap-2 pt-1">
                                        <button type="submit"
                                                class="flex-1 bg-blue-600 text-white rounded-lg py-1.5 text-xs font-medium hover:bg-blue-700">
                                            Simpan
                                        </button>
                                        <button type="button" @click="editOpen = false"
                                                class="flex-1 bg-gray-200 text-gray-700 rounded-lg py-1.5 text-xs font-medium">
                                            Batal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $riwayats->links() }}
        </div>
        @else
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="text-gray-400 text-sm">Belum ada data riwayat pendidikan.</p>
        </div>
        @endif
    </div>

    {{-- Tambah Data Baru (HRD bisa tambah untuk karyawan manapun) --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden" x-data="{ open: false }">
        <button @click="open = !open"
                class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
            <span class="text-sm font-semibold text-gray-700">+ Tambah Data Pendidikan</span>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-cloak class="border-t border-gray-100 px-5 py-4">
            <form action="{{ route('pendidikan.riwayat.store') }}" method="POST" enctype="multipart/form-data"
                  class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Karyawan <span class="text-red-500">*</span></label>
                    <select name="nik" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach(\App\Models\Pegawai::aktif()->orderBy('nama')->get() as $p)
                        <option value="{{ $p->nik }}">{{ $p->nama }} ({{ $p->nik }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jenjang <span class="text-red-500">*</span></label>
                    <select name="jenjang" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        @foreach($jenjangList as $j)
                        <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nama Institusi <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_institusi" required maxlength="200"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Jurusan / Program Studi</label>
                    <input type="text" name="jurusan" maxlength="100"
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tahun Masuk</label>
                        <input type="number" name="tahun_masuk" min="1950" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') - 4 }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Tahun Lulus</label>
                        <input type="number" name="tahun_lulus" min="1950" max="{{ date('Y') + 1 }}" placeholder="{{ date('Y') }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">IPK / Nilai</label>
                        <input type="number" step="0.01" name="ipk" min="0" max="4" placeholder="3.50"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="is_terakhir" value="1" class="rounded">
                            Pendidikan Terakhir
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">File Ijazah</label>
                    <input type="file" name="file_ijazah" accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full text-sm text-gray-500">
                    <p class="text-xs text-gray-400 mt-0.5">PDF / Gambar, maks 5 MB</p>
                </div>
                <div class="md:col-span-3 flex justify-end">
                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
