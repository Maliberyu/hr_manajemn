@php
    $colorMap = [
        'green' => ['border-green-200','bg-green-50','text-green-700','bg-green-600 hover:bg-green-700'],
        'red'   => ['border-red-200',  'bg-red-50',  'text-red-700',  'bg-red-600 hover:bg-red-700'],
    ];
    [$borderCls, $bgCls, $textCls, $btnCls] = $colorMap[$color] ?? $colorMap['green'];
@endphp

<div class="grid md:grid-cols-5 gap-4">
    {{-- Add form --}}
    <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-semibold text-gray-700 mb-4">Tambah {{ $title }}</p>
        <form method="POST" action="{{ route('payroll.master.komponen.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="jenis" value="{{ $jenis }}">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Nama {{ $title }} <span class="text-red-500">*</span></label>
                <input type="text" name="nama" required maxlength="100"
                       placeholder="Contoh: {{ $jenis === 'tambah' ? 'Tunjangan Transport' : 'BPJS Kesehatan' }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tipe Nilai <span class="text-red-500">*</span></label>
                <select name="tipe" x-data="{ tipe: 'tetap' }" x-model="tipe"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none bg-white">
                    <option value="tetap">Nominal Tetap (Rp)</option>
                    <option value="persen_gapok">Persen dari Gaji Pokok (%)</option>
                    <option value="persen_umk">Persen dari UMK (%)</option>
                </select>
            </div>
            <div x-data="{ tipe: 'tetap' }">
                <label class="block text-xs text-gray-500 mb-1">
                    Nilai <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="nilai" required min="0" step="0.01"
                           placeholder="0"
                           class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <p class="text-xs text-gray-400 mt-1">Nominal (Rp) atau persen (%) sesuai tipe di atas</p>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Urutan Tampil</label>
                <input type="number" name="urutan" min="1" max="999" value="{{ $jenis === 'tambah' ? 20 : 60 }}"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Keterangan</label>
                <input type="text" name="keterangan" maxlength="200"
                       class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>
            <button type="submit" class="w-full py-2 text-sm {{ $btnCls }} text-white rounded-xl font-semibold transition">
                Tambah {{ $title }}
            </button>
        </form>
    </div>

    {{-- List --}}
    <div class="md:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-700">Daftar {{ $title }} ({{ $list->count() }})</p>
            <p class="text-xs text-gray-400">Urut berdasarkan nomor urutan</p>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($list as $k)
            <div class="flex items-center gap-3 px-4 py-3 {{ !$k->aktif ? 'opacity-50' : '' }}">
                <div class="w-6 text-center text-xs text-gray-300 font-mono">{{ $k->urutan }}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-medium text-gray-800">{{ $k->nama }}</p>
                        @if(!$k->aktif)
                        <span class="text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">nonaktif</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        @if($k->tipe === 'tetap')
                            Tetap: Rp {{ number_format($k->nilai, 0, ',', '.') }}
                        @elseif($k->tipe === 'persen_gapok')
                            {{ $k->nilai }}% × Gaji Pokok
                        @else
                            {{ $k->nilai }}% × UMK
                        @endif
                        @if($k->keterangan) · {{ $k->keterangan }} @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <form method="POST" action="{{ route('payroll.master.komponen.toggle', $k) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="text-xs px-2 py-1 rounded-lg transition
                                {{ $k->aktif ? 'bg-green-50 text-green-600 hover:bg-green-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                            {{ $k->aktif ? 'Aktif' : 'Nonaktif' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('payroll.master.komponen.destroy', $k) }}"
                          onsubmit="return confirm('Hapus {{ $k->nama }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-400 hover:text-red-600">×</button>
                    </form>
                </div>
            </div>
            @empty
            <p class="px-4 py-8 text-center text-sm text-gray-400">
                Belum ada {{ strtolower($title) }}. Tambahkan di form sebelah kiri.
            </p>
            @endforelse
        </div>
    </div>
</div>
