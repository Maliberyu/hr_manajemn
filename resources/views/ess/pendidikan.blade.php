@extends('layouts.app')
@section('title', 'Pendidikan & Beasiswa Saya')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ tab: 'riwayat' }">

    <div>
        <h1 class="text-xl font-bold text-gray-800">Pendidikan & Beasiswa</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola riwayat pendidikan dan pengajuan bantuan pendidikan Anda</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="flex border-b border-gray-200">
        <button @click="tab = 'riwayat'"
                :class="tab === 'riwayat' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400'"
                class="px-5 py-2.5 text-sm font-medium border-b-2 transition">
            Riwayat Pendidikan
            <span class="ml-1.5 bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded-full">{{ $riwayats->count() }}</span>
        </button>
        <button @click="tab = 'beasiswa'"
                :class="tab === 'beasiswa' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400'"
                class="px-5 py-2.5 text-sm font-medium border-b-2 transition">
            Bantuan Pendidikan
            <span class="ml-1.5 bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded-full">{{ $beasiswas->count() }}</span>
        </button>
    </div>

    {{-- ═══════════════ TAB RIWAYAT ═══════════════ --}}
    <div x-show="tab === 'riwayat'" class="space-y-4">

        {{-- Daftar Riwayat --}}
        @if($riwayats->count())
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden divide-y divide-gray-50">
            @foreach($riwayats as $r)
            <div class="flex items-start justify-between px-5 py-4" x-data="{ editOpen: false }">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-bold text-blue-700">{{ $r->jenjang }}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-800">{{ $r->nama_institusi }}</p>
                            @if($r->is_terakhir)
                            <span class="bg-green-100 text-green-600 text-xs px-1.5 py-0.5 rounded-lg">Terakhir</span>
                            @endif
                        </div>
                        @if($r->jurusan)<p class="text-xs text-gray-500">{{ $r->jurusan }}</p>@endif
                        <div class="flex items-center gap-3 mt-1">
                            @if($r->tahun_masuk || $r->tahun_lulus)
                            <span class="text-xs text-gray-400">
                                {{ $r->tahun_masuk ?? '?' }} – {{ $r->tahun_lulus ?? 'sekarang' }}
                            </span>
                            @endif
                            @if($r->ipk)
                            <span class="text-xs text-gray-400">IPK {{ number_format($r->ipk, 2) }}</span>
                            @endif
                            @if($r->file_url)
                            <a href="{{ $r->file_url }}" target="_blank"
                               class="text-xs text-blue-500 hover:text-blue-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Ijazah
                            </a>
                            @endif
                        </div>

                        {{-- Edit inline --}}
                        <div x-show="editOpen" x-cloak class="mt-3">
                            <form action="{{ route('ess.pendidikan.riwayat.destroy', $r) }}" method="POST"
                                  class="hidden" id="del-{{ $r->id }}">@csrf @method('DELETE')</form>
                            {{-- Gunakan route HRD untuk update karena karyawan juga bisa update miliknya --}}
                            <form action="{{ route('ess.pendidikan.riwayat.store') }}" method="POST"
                                  enctype="multipart/form-data" class="bg-gray-50 border border-gray-200 rounded-xl p-3 space-y-2 max-w-sm">
                                {{-- Override: gunakan hidden input untuk simulate PUT --}}
                                @csrf
                                {{-- Simpan sebagai store baru, hapus yang lama --}}
                                <input type="hidden" name="_delete_id" value="{{ $r->id }}">
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
                                           required class="w-full border border-gray-200 rounded-lg px-2 py-1.5 text-xs">
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
                                    <div class="flex items-end pb-1">
                                        <label class="flex items-center gap-1 text-xs text-gray-600">
                                            <input type="checkbox" name="is_terakhir" value="1"
                                                   {{ $r->is_terakhir ? 'checked' : '' }}>
                                            Pend. Terakhir
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs text-gray-500">Upload Ijazah Baru (opsional)</label>
                                    <input type="file" name="file_ijazah" accept=".pdf,.jpg,.jpeg,.png"
                                           class="w-full text-xs text-gray-500">
                                </div>
                                <div class="flex gap-2 pt-1">
                                    <button type="submit"
                                            class="flex-1 bg-blue-600 text-white rounded-lg py-1.5 text-xs font-medium">
                                        Simpan
                                    </button>
                                    <button type="button" @click="editOpen = false"
                                            class="flex-1 bg-gray-200 text-gray-700 rounded-lg py-1.5 text-xs font-medium">
                                        Batal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1 ml-3 flex-shrink-0">
                    <button @click="editOpen = !editOpen"
                            class="p-1.5 text-gray-300 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <form action="{{ route('ess.pendidikan.riwayat.destroy', $r) }}" method="POST"
                          onsubmit="return confirm('Hapus data ini?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-gray-50 border border-gray-200 rounded-2xl py-10 text-center">
            <p class="text-gray-400 text-sm">Belum ada riwayat pendidikan.</p>
        </div>
        @endif

        {{-- Form Tambah --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                <span class="text-sm font-semibold text-gray-700">+ Tambah Riwayat Pendidikan</span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak class="border-t border-gray-100 px-5 py-4">
                <form action="{{ route('ess.pendidikan.riwayat.store') }}" method="POST"
                      enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Jenjang <span class="text-red-500">*</span></label>
                            <select name="jenjang" required
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                                @foreach($jenjangList as $j)
                                <option value="{{ $j }}">{{ $j }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tahun Masuk</label>
                            <input type="number" name="tahun_masuk" min="1950" max="{{ date('Y') + 1 }}"
                                   placeholder="{{ date('Y') - 4 }}"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Nama Sekolah / Universitas <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_institusi" required maxlength="200"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tahun Lulus</label>
                            <input type="number" name="tahun_lulus" min="1950" max="{{ date('Y') + 1 }}"
                                   placeholder="{{ date('Y') }}"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Jurusan / Program Studi</label>
                            <input type="text" name="jurusan" maxlength="100"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">IPK / Nilai Akhir</label>
                            <input type="number" step="0.01" name="ipk" min="0" max="4" placeholder="3.50"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Upload Ijazah</label>
                            <input type="file" name="file_ijazah" accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full text-sm text-gray-500">
                            <p class="text-xs text-gray-400 mt-0.5">PDF / Gambar, maks 5 MB</p>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" name="is_terakhir" value="1" class="rounded">
                                Ini pendidikan terakhir saya
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Keterangan</label>
                        <input type="text" name="keterangan" maxlength="300"
                               placeholder="Catatan tambahan (opsional)"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>
                    <button type="submit"
                            class="w-full py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                        Simpan Riwayat
                    </button>
                </form>
            </div>
        </div>

    </div>

    {{-- ═══════════════ TAB BEASISWA ═══════════════ --}}
    <div x-show="tab === 'beasiswa'" class="space-y-4">

        {{-- Daftar Pengajuan --}}
        @if($beasiswas->count())
        <div class="space-y-3">
            @foreach($beasiswas as $b)
            @php $c = $b->status_color; @endphp
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $b->nama_program }}</p>
                        <p class="text-xs text-gray-400">{{ $b->jenis_label }} · {{ $b->institusi }}</p>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full
                        bg-{{ $c }}-100 text-{{ $c }}-700 border border-{{ $c }}-200">
                        {{ $b->status_label }}
                    </span>
                </div>
                <div class="px-5 pb-3 grid grid-cols-3 gap-3 text-xs">
                    <div>
                        <p class="text-gray-400">Biaya Diajukan</p>
                        <p class="font-medium text-gray-700">Rp {{ number_format($b->biaya_diajukan, 0, ',', '.') }}</p>
                    </div>
                    @if($b->biaya_disetujui !== null)
                    <div>
                        <p class="text-gray-400">Biaya Disetujui</p>
                        <p class="font-medium text-green-700">Rp {{ number_format($b->biaya_disetujui, 0, ',', '.') }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-gray-400">Mulai</p>
                        <p class="font-medium text-gray-700">{{ $b->tgl_mulai->isoFormat('D MMM Y') }}</p>
                    </div>
                </div>
                @if($b->catatan_hrd)
                <div class="px-5 pb-3">
                    <p class="text-xs text-gray-400 mb-0.5">Catatan HRD:</p>
                    <p class="text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">{{ $b->catatan_hrd }}</p>
                </div>
                @endif

                {{-- Aksi untuk atasan yang punya akses --}}
                @if($b->status === 'menunggu_atasan' && in_array(auth()->user()->role, ['atasan','hrd','admin']))
                <div class="px-5 pb-4" x-data="{ showForm: false }">
                    <button @click="showForm = !showForm"
                            class="w-full py-2 border border-amber-300 text-amber-700 rounded-xl text-sm font-medium hover:bg-amber-50 transition">
                        Berikan Keputusan
                    </button>
                    <div x-show="showForm" x-cloak class="mt-3">
                        <form action="{{ route('ess.pendidikan.beasiswa.approve-atasan', $b) }}" method="POST"
                              class="space-y-3">
                            @csrf
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="keputusan" value="lanjut" required class="text-green-600">
                                    <span class="text-sm text-green-700 font-medium">Teruskan ke HRD</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="keputusan" value="ditolak" class="text-red-600">
                                    <span class="text-sm text-red-700 font-medium">Tolak</span>
                                </label>
                            </div>
                            <textarea name="catatan_atasan" rows="2" maxlength="400"
                                      placeholder="Catatan (opsional)"
                                      class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-blue-400 outline-none"></textarea>
                            <button type="submit"
                                    class="w-full py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">
                                Kirim
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-gray-50 border border-gray-200 rounded-2xl py-10 text-center">
            <p class="text-gray-400 text-sm">Belum ada pengajuan bantuan pendidikan.</p>
        </div>
        @endif

        {{-- Form Ajukan Baru --}}
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden" x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                <span class="text-sm font-semibold text-gray-700">+ Ajukan Bantuan Pendidikan</span>
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak class="border-t border-gray-100 px-5 py-4">
                <form action="{{ route('ess.pendidikan.beasiswa.store') }}" method="POST"
                      enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Jenis Bantuan <span class="text-red-500">*</span></label>
                            <select name="jenis" required
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                                @foreach($jenisBeasiswaList as $v => $l)
                                <option value="{{ $v }}">{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Nama Program / Kursus <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_program" required maxlength="200"
                                   placeholder="Contoh: S2 Manajemen, Kursus Python, ..."
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Nama Institusi <span class="text-red-500">*</span></label>
                            <input type="text" name="institusi" required maxlength="150"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Kota</label>
                            <input type="text" name="kota" maxlength="100"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="tgl_mulai" required
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Total Biaya Yang Diajukan (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="biaya_diajukan" required min="0" step="1000"
                               placeholder="0"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Alasan / Keterangan</label>
                        <textarea name="catatan_pengaju" rows="3" maxlength="1000"
                                  placeholder="Jelaskan mengapa Anda memerlukan bantuan ini dan manfaatnya untuk pekerjaan..."
                                  class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 outline-none resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Upload Proposal / Brosur</label>
                        <input type="file" name="file_proposal" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full text-sm text-gray-500">
                        <p class="text-xs text-gray-400 mt-0.5">PDF / Gambar, maks 10 MB</p>
                    </div>
                    <button type="submit"
                            class="w-full py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                        Ajukan Sekarang
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
