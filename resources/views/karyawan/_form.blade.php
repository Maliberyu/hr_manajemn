{{--
  Form partial untuk create & edit karyawan.
  Variabel tersedia: $departemen, $pendidikan
  Opsional: $karyawan (jika edit)
--}}
@php $k = $karyawan ?? null; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Kolom kiri: Foto ──────────────────────────────────────────────────── --}}
    <div class="lg:col-span-1">
        <div class="bg-gray-50 rounded-2xl border border-dashed border-gray-200 p-6 text-center"
             x-data="fotoPreview()">
            <div class="mb-4">
                <img :src="preview" x-ref="img"
                     class="w-32 h-32 rounded-full object-cover mx-auto border-4 border-white shadow-md"
                     onerror="this.src='{{ asset('images/avatar-default.png') }}'">
            </div>
            <label class="cursor-pointer inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Pilih Foto
                <input type="file" name="photo" accept="image/jpeg,image/png" class="hidden" @change="onFileChange">
            </label>
            <p class="text-xs text-gray-400 mt-2">JPG / PNG, maks. 2 MB</p>
            @error('photo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- ── Kolom kanan: Data ─────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Data Identitas --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Data Identitas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- NIK --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NIK <span class="text-red-500">*</span></label>
                    <input type="text" name="nik" value="{{ old('nik', $k?->nik) }}" required maxlength="20"
                           placeholder="cth: 2025001"
                           class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 @error('nik') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    @error('nik') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Nama --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $k?->nama) }}" required maxlength="100"
                           placeholder="Nama sesuai KTP"
                           class="w-full px-3 py-2 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 @error('nama') border-red-400 bg-red-50 @else border-gray-200 @enderror">
                    @error('nama') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Jenis Kelamin --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="jk" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white @error('jk') border-red-400 @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="Pria" {{ old('jk', $k?->jk) === 'Pria' ? 'selected' : '' }}>Pria</option>
                        <option value="Wanita" {{ old('jk', $k?->jk) === 'Wanita' ? 'selected' : '' }}>Wanita</option>
                    </select>
                    @error('jk') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- No. KTP --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. KTP</label>
                    <input type="text" name="no_ktp" value="{{ old('no_ktp', $k?->no_ktp) }}" maxlength="16"
                           placeholder="16 digit NIK KTP"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 @error('no_ktp') border-red-400 bg-red-50 @enderror">
                    @error('no_ktp') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Tempat Lahir --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tempat Lahir</label>
                    <input type="text" name="tmp_lahir" value="{{ old('tmp_lahir', $k?->tmp_lahir) }}" maxlength="50"
                           placeholder="cth: Bandung"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                {{-- Tanggal Lahir --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" name="tgl_lahir" required
                           value="{{ old('tgl_lahir', $k?->tgl_lahir?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 @error('tgl_lahir') border-red-400 @enderror">
                    @error('tgl_lahir') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- NPWP --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NPWP</label>
                    <input type="text" name="npwp" value="{{ old('npwp', $k?->npwp) }}" maxlength="30"
                           placeholder="XX.XXX.XXX.X-XXX.XXX"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                {{-- Alamat --}}
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alamat</label>
                    <input type="text" name="alamat" value="{{ old('alamat', $k?->alamat) }}" maxlength="200"
                           placeholder="Alamat tempat tinggal sekarang"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

            </div>
        </div>

        {{-- Data Kepegawaian --}}
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Data Kepegawaian</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Jabatan --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" name="jbtn" value="{{ old('jbtn', $k?->jbtn) }}" required maxlength="50"
                           placeholder="cth: Perawat, Dokter Umum"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 @error('jbtn') border-red-400 bg-red-50 @enderror">
                    @error('jbtn') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Departemen --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Departemen <span class="text-red-500">*</span></label>
                    <select name="departemen" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white @error('departemen') border-red-400 @enderror">
                        <option value="">-- Pilih Departemen --</option>
                        @foreach($departemen as $id => $nama)
                            <option value="{{ $id }}" {{ old('departemen', $k?->departemen) == $id ? 'selected' : '' }}>{{ $nama }}</option>
                        @endforeach
                    </select>
                    @error('departemen') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Pendidikan --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pendidikan Terakhir <span class="text-red-500">*</span></label>
                    <select name="pendidikan" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white @error('pendidikan') border-red-400 @enderror">
                        <option value="">-- Pilih --</option>
                        @foreach($pendidikan as $val)
                            <option value="{{ $val }}" {{ old('pendidikan', $k?->pendidikan) == $val ? 'selected' : '' }}>{{ $val }}</option>
                        @endforeach
                    </select>
                    @error('pendidikan') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Mulai Kerja --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai Kerja <span class="text-red-500">*</span></label>
                    <input type="date" name="mulai_kerja" required
                           value="{{ old('mulai_kerja', $k?->mulai_kerja?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 @error('mulai_kerja') border-red-400 @enderror">
                    @error('mulai_kerja') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status Kerja --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status Kerja <span class="text-red-500">*</span></label>
                    <select name="stts_kerja" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white @error('stts_kerja') border-red-400 @enderror">
                        <option value="">-- Pilih --</option>
                        @foreach(['Tet' => 'Tetap', 'Kon' => 'Kontrak', 'Mag' => 'Magang', 'Hon' => 'Honorer'] as $val => $label)
                            <option value="{{ $val }}" {{ old('stts_kerja', $k?->stts_kerja) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('stts_kerja') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status Aktif --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status Aktif <span class="text-red-500">*</span></label>
                    <select name="stts_aktif" required
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white @error('stts_aktif') border-red-400 @enderror">
                        @foreach(['AKTIF' => 'Aktif', 'CUTI' => 'Cuti', 'KELUAR' => 'Keluar', 'TENAGA LUAR' => 'Tenaga Luar'] as $val => $label)
                            <option value="{{ $val }}" {{ old('stts_aktif', $k?->stts_aktif ?? 'AKTIF') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('stts_aktif') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Gaji Pokok --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Gaji Pokok (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="gapok" value="{{ old('gapok', $k?->gapok) }}" required min="0"
                           placeholder="cth: 3500000"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 @error('gapok') border-red-400 bg-red-50 @enderror">
                    @error('gapok') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Wajib Masuk --}}
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Hari Wajib Masuk/Bulan <span class="text-red-500">*</span></label>
                    <input type="number" name="wajibmasuk" value="{{ old('wajibmasuk', $k?->wajibmasuk ?? 25) }}" required min="0" max="31"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-400 @error('wajibmasuk') border-red-400 @enderror">
                    @error('wajibmasuk') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

    </div>{{-- /kolom kanan --}}
</div>

@push('scripts')
<script>
function fotoPreview() {
    return {
        preview: '{{ $k?->foto_url ?? asset('images/avatar-default.png') }}',
        onFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => { this.preview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    }
}
</script>
@endpush
