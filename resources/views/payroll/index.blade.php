@extends('layouts.app')
@section('title', 'Payroll')
@section('page-title', 'Payroll Gaji')
@section('page-subtitle', 'Generate dan kelola slip gaji bulanan')

@section('content')
<div class="space-y-4" x-data="payrollPage()">

    {{-- Stats + actions --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ $totalDraft }}</div>
            <div class="text-xs text-yellow-600 mt-0.5">Slip Draft</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $totalFinal }}</div>
            <div class="text-xs text-green-600 mt-0.5">Slip Final</div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 text-center">
            <div class="text-2xl font-bold text-gray-700">{{ $pegawai->total() }}</div>
            <div class="text-xs text-gray-500 mt-0.5">Total Pegawai</div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-3 flex flex-col gap-1.5">
            <a href="{{ route('payroll.master') }}"
               class="block text-center py-1.5 text-xs border border-blue-200 text-blue-600 hover:bg-blue-50 rounded-xl transition font-medium">
                Master Gaji
            </a>
            <a href="{{ route('payroll.export', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
               class="block text-center py-1.5 text-xs border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl transition">
                Export CSV
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Filter + Generate + Finalisasi --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <form method="GET" action="{{ route('payroll.index') }}" class="flex flex-wrap gap-3 items-end flex-1">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Bulan</label>
                <select name="bulan" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    @foreach(range(1,12) as $b)
                    <option value="{{ $b }}" {{ $bulan == $b ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null,$b)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Tahun</label>
                <select name="tahun" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    @foreach(range(now()->year-1, now()->year+1) as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Departemen</label>
                <select name="departemen" class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="">Semua</option>
                    @foreach($departemen as $id => $nama)
                    <option value="{{ $id }}" {{ $depId == $id ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-medium">
                Tampilkan
            </button>
        </form>

        <div class="flex items-center gap-2">
            {{-- Generate Slip --}}
            <form method="POST" action="{{ route('payroll.generate') }}"
                  onsubmit="return confirm('Generate slip draft untuk {{ \Carbon\Carbon::create($tahun,$bulan)->translatedFormat('F Y') }}? Slip final tidak akan ditimpa.')">
                @csrf
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <button type="submit"
                        class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold transition">
                    Generate Slip Draft
                </button>
            </form>

            {{-- Finalisasi Bulk --}}
            <form method="POST" action="{{ route('payroll.slip.finalize.bulk') }}" id="formFinalisasi">
                @csrf
                <input type="hidden" name="bulan" value="{{ $bulan }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                {{-- Hidden inputs diisi JS dari checkbox yang dipilih --}}
                <div id="hiddenSlipIds"></div>
                <button type="button" id="btnFinalisasi"
                        @click="submitFinalisasi()"
                        :disabled="selectedCount === 0"
                        :class="selectedCount > 0
                            ? 'bg-green-600 hover:bg-green-700 text-white cursor-pointer'
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                        class="px-4 py-2 text-sm rounded-xl font-semibold transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Finalisasi Gaji
                    <span x-show="selectedCount > 0"
                          class="bg-white text-green-700 text-xs font-bold px-1.5 py-0.5 rounded-full leading-none"
                          x-text="'(' + selectedCount + ')'"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- Toolbar seleksi (muncul jika ada yang dipilih) --}}
    <div x-show="selectedCount > 0" x-transition
         class="flex items-center gap-3 px-4 py-2.5 bg-green-50 border border-green-200 rounded-xl text-sm">
        <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-green-700 font-medium">
            <span x-text="selectedCount"></span> slip dipilih untuk difinalisasi
        </span>
        <button type="button" @click="clearSelection()"
                class="ml-auto text-xs text-green-600 hover:text-green-800 underline">
            Batal Pilih
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-500 bg-gray-50 rounded-lg px-2 py-1">
                Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
            </span>
            @if($totalDraft > 0)
            <button type="button" @click="selectAllDraft()"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tandai Semua Draft ({{ $totalDraft }})
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" id="checkAll"
                                   @change="toggleAll($event.target.checked)"
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Pegawai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600">Golongan / UMK</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600">Gaji Bersih</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pegawai as $p)
                    @php $slip = $p->slipGaji->first(); @endphp
                    <tr class="hover:bg-gray-50/50 transition"
                        :class="selectedIds.includes({{ $slip?->id ?? 'null' }}) ? 'bg-green-50/60' : ''">
                        <td class="px-4 py-3 text-center">
                            @if($slip && $slip->status === 'draft')
                            <input type="checkbox"
                                   class="slip-check rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer"
                                   value="{{ $slip->id }}"
                                   :checked="selectedIds.includes({{ $slip->id }})"
                                   @change="toggleSlip({{ $slip->id }}, $event.target.checked)">
                            @else
                            <span class="block w-4 h-4 mx-auto rounded border border-gray-200 bg-gray-50"></span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $p->nama }}</div>
                            <div class="text-xs text-gray-400">{{ $p->jbtn }} · {{ $p->departemenRef?->nama }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if($p->payrollSetting?->golongan)
                            <div class="text-xs font-medium text-gray-700">{{ $p->payrollSetting->golongan }}</div>
                            <div class="text-xs text-gray-400">UMK {{ $p->payrollSetting->umk_tahun }}</div>
                            @else
                            <span class="text-xs text-orange-500 font-medium">Belum diset</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700">
                            @if($slip)
                            Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800">
                            @if($slip)
                            Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(!$slip)
                            <span class="px-2 py-1 text-xs bg-gray-100 text-gray-400 rounded-xl">Belum Diproses</span>
                            @elseif($slip->status === 'final')
                            <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-xl font-semibold flex items-center gap-1 justify-center w-fit mx-auto">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Final
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-xl font-semibold">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($slip)
                            <a href="{{ route('payroll.slip.show', $slip) }}"
                               class="px-3 py-1 text-xs bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg transition font-medium">
                                Detail
                            </a>
                            @else
                            <span class="text-xs text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-400">
                            Tidak ada data pegawai aktif.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pegawai->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $pegawai->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function payrollPage() {
    return {
        selectedIds: [],

        get selectedCount() {
            return this.selectedIds.length;
        },

        toggleSlip(id, checked) {
            if (checked) {
                if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
            } else {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            }
            this.syncCheckAll();
        },

        toggleAll(checked) {
            const boxes = document.querySelectorAll('.slip-check');
            boxes.forEach(cb => {
                const id = parseInt(cb.value);
                cb.checked = checked;
                if (checked && !this.selectedIds.includes(id)) {
                    this.selectedIds.push(id);
                }
            });
            if (!checked) this.selectedIds = [];
        },

        selectAllDraft() {
            const boxes = document.querySelectorAll('.slip-check');
            boxes.forEach(cb => {
                const id = parseInt(cb.value);
                cb.checked = true;
                if (!this.selectedIds.includes(id)) this.selectedIds.push(id);
            });
            this.syncCheckAll();
        },

        clearSelection() {
            this.selectedIds = [];
            document.querySelectorAll('.slip-check').forEach(cb => cb.checked = false);
            const checkAll = document.getElementById('checkAll');
            if (checkAll) checkAll.checked = false;
        },

        syncCheckAll() {
            const boxes = document.querySelectorAll('.slip-check');
            const allChecked = boxes.length > 0 && [...boxes].every(cb => cb.checked);
            const checkAll = document.getElementById('checkAll');
            if (checkAll) checkAll.checked = allChecked;
        },

        submitFinalisasi() {
            if (this.selectedIds.length === 0) return;

            const jumlah = this.selectedIds.length;
            if (!confirm(`Finalisasi ${jumlah} slip gaji? Slip yang sudah final tidak bisa diedit.`)) return;

            // Isi hidden inputs
            const container = document.getElementById('hiddenSlipIds');
            container.innerHTML = '';
            this.selectedIds.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'slip_ids[]';
                inp.value = id;
                container.appendChild(inp);
            });

            document.getElementById('formFinalisasi').submit();
        },
    };
}
</script>
@endpush
