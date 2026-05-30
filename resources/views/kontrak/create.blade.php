@extends('layouts.app')
@section('title', 'Buat Kontrak Kerja')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
        <a href="{{ route('kontrak.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Buat Kontrak Kerja</h1>
            <p class="text-sm text-gray-500">Tambah kontrak baru untuk pegawai</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('kontrak.store') }}" enctype="multipart/form-data"
          class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5 shadow-sm"
          x-data="kontrakForm()">
        @csrf

        {{-- Pegawai --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Pegawai <span class="text-red-500">*</span></label>
            <select name="nik" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nik') border-red-400 @enderror">
                <option value="">— Pilih Pegawai —</option>
                @foreach($pegawaiList as $p)
                <option value="{{ $p->nik }}" @selected(old('nik') == $p->nik)>
                    {{ $p->nama }} — {{ $p->nik }} ({{ $p->jbtn }})
                </option>
                @endforeach
            </select>
        </div>

        {{-- Jenis & No Kontrak --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Kontrak <span class="text-red-500">*</span></label>
                <select name="jenis_kontrak_id" required x-model="jenisId"
                        @change="onJenisChange()"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Pilih —</option>
                    @foreach($jenisList as $j)
                    <option value="{{ $j->id }}"
                            data-tetap="{{ $j->is_tetap ? '1' : '0' }}"
                            data-durasi="{{ $j->durasi_default_bulan ?? '' }}"
                            @selected(old('jenis_kontrak_id') == $j->id)>
                        {{ $j->nama }}{{ $j->durasi_default_bulan ? ' ('.$j->durasi_default_bulan.' bln)' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">No. Kontrak</label>
                <input type="text" name="no_kontrak" value="{{ old('no_kontrak') }}"
                       placeholder="Opsional"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Tanggal --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                <input type="date" name="tgl_mulai" value="{{ old('tgl_mulai', today()->toDateString()) }}"
                       required x-model="tglMulai" @change="hitungSelesai()"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div x-show="!isTetap">
                <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <input type="date" name="tgl_selesai" x-model="tglSelesai"
                       :value="tglSelesai"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p x-show="isTetap" class="text-xs text-gray-400 mt-1">PKWTT tidak memiliki tanggal selesai.</p>
            </div>
            <div x-show="isTetap">
                <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                <div class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-400 bg-gray-50">
                    Tidak terbatas (Karyawan Tetap)
                </div>
            </div>
        </div>

        {{-- Tgl TTD & File --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal TTD Kontrak</label>
                <input type="date" name="tgl_tanda_tangan" value="{{ old('tgl_tanda_tangan') }}"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Upload File Kontrak</label>
                <input type="file" name="file_kontrak" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-blue-50 file:text-blue-700">
                <p class="text-xs text-gray-400 mt-1">PDF / JPG / PNG, maks 5 MB</p>
            </div>
        </div>

        {{-- Catatan --}}
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="catatan" rows="3" maxlength="500" placeholder="Opsional..."
                      class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('catatan') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('kontrak.index') }}"
               class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-700 hover:bg-gray-50 transition">Batal</a>
            <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition shadow-sm">
                Simpan Kontrak
            </button>
        </div>
    </form>
</div>

<script>
function kontrakForm() {
    return {
        jenisId: '{{ old('jenis_kontrak_id') }}',
        isTetap: false,
        durasi:  0,
        tglMulai: '{{ old('tgl_mulai', today()->toDateString()) }}',
        tglSelesai: '{{ old('tgl_selesai') }}',

        onJenisChange() {
            const sel = document.querySelector('select[name="jenis_kontrak_id"]');
            const opt = sel.options[sel.selectedIndex];
            this.isTetap = opt.dataset.tetap === '1';
            this.durasi  = parseInt(opt.dataset.durasi) || 0;
            this.hitungSelesai();
        },

        hitungSelesai() {
            if (this.isTetap || !this.tglMulai || !this.durasi) return;
            const d = new Date(this.tglMulai);
            d.setMonth(d.getMonth() + this.durasi);
            d.setDate(d.getDate() - 1);
            this.tglSelesai = d.toISOString().split('T')[0];
        },
    };
}
</script>
@endsection
