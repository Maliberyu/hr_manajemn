@extends('layouts.app')
@section('title', 'Buat Sesi 360°')
@section('page-title', 'Buat Sesi Penilaian 360 Derajat')
@section('page-subtitle', 'Setup rater untuk karyawan yang dinilai')

@section('content')
<div class="max-w-2xl mx-auto space-y-4" x-data="form360()">

    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('kinerja.360.store') }}" class="space-y-4">
        @csrf

        {{-- Info Dasar --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-3">
            <p class="text-sm font-semibold text-gray-700">Informasi Sesi</p>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Pegawai yang Dinilai <span class="text-red-500">*</span></label>
                <select name="pegawai_id" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none bg-white">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawai as $p)
                    <option value="{{ $p->id }}">{{ $p->nama }} — {{ $p->jbtn }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Semester</label>
                    <select name="semester" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none bg-white">
                        <option value="1" {{ $semester==1?'selected':'' }}>Semester 1 (Jan–Jun)</option>
                        <option value="2" {{ $semester==2?'selected':'' }}>Semester 2 (Jul–Des)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                    <select name="tahun" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none bg-white">
                        @foreach(range(now()->year-1, now()->year+1) as $t)
                        <option value="{{ $t }}" {{ $tahun==$t?'selected':'' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Deadline Pengisian</label>
                <input type="date" name="deadline" min="{{ now()->addDay()->toDateString() }}"
                       value="{{ now()->addDays(14)->toDateString() }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-400 focus:outline-none">
            </div>
        </div>

        {{-- Raters --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-700">Daftar Rater</p>
                <button type="button" @click="addRater()"
                        class="px-3 py-1.5 text-xs bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition font-medium">
                    + Tambah Rater
                </button>
            </div>

            <div class="space-y-2">
                <template x-for="(rater, idx) in raters" :key="idx">
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-xl">
                        <select :name="`raters[${idx}][user_id]`" required x-model="rater.user_id"
                                class="flex-1 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-purple-400 focus:outline-none bg-white">
                            <option value="">-- Pilih User --</option>
                            @foreach($userList as $u)
                            <option value="{{ $u->id }}">{{ $u->nama }} ({{ $u->role }})</option>
                            @endforeach
                        </select>
                        <select :name="`raters[${idx}][hubungan]`" required x-model="rater.hubungan"
                                class="w-36 px-2 py-1.5 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-purple-400 focus:outline-none bg-white">
                            <option value="">Hubungan</option>
                            <option value="atasan">Atasan Langsung</option>
                            <option value="rekan">Rekan Sejawat</option>
                            <option value="bawahan">Bawahan</option>
                            <option value="self">Diri Sendiri</option>
                        </select>
                        <button type="button" @click="raters.splice(idx, 1)"
                                class="text-red-400 hover:text-red-600 text-lg font-bold px-1">×</button>
                    </div>
                </template>
                <p x-show="raters.length === 0" class="text-sm text-gray-400 text-center py-4">
                    Belum ada rater. Klik "+ Tambah Rater" untuk menambahkan.
                </p>
            </div>

            <div class="mt-3 p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700">
                <strong>Panduan:</strong> Minimal 1 rater. Rater bertipe "Diri Sendiri" (Self) diisi oleh pegawai itu sendiri.
                Identitas rater rekan & bawahan akan bersifat anonim di laporan.
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" :disabled="raters.length === 0"
                    :class="raters.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-purple-700'"
                    class="flex-1 py-2.5 text-sm bg-purple-600 text-white rounded-xl font-semibold transition">
                Buat Sesi 360°
            </button>
            <a href="{{ route('kinerja.360.index') }}"
               class="px-5 py-2.5 text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function form360() {
    return {
        raters: [{ user_id: '', hubungan: 'self' }],
        addRater() {
            this.raters.push({ user_id: '', hubungan: '' });
        }
    };
}
</script>
@endpush
