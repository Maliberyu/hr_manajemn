@extends('layouts.app')
@section('title', 'Rekap KPI')
@section('page-title', 'Rekap KPI')
@section('page-subtitle', 'Laporan dan rekap skor KPI per periode')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-12 flex flex-col items-center gap-4 text-center">
            <div class="w-20 h-20 bg-emerald-50 rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-800">Rekap KPI</h2>
                <p class="text-sm text-gray-500 mt-1 max-w-md">
                    Fitur rekap dan laporan KPI sedang dikembangkan. Akan menampilkan tabel skor
                    semua karyawan, distribusi predikat, dan perbandingan antar departemen per semester.
                </p>
            </div>
            <a href="{{ route('kpi.index') }}"
               class="mt-2 px-4 py-2 text-sm border border-gray-200 text-gray-600 rounded-xl hover:bg-gray-50 transition">
                Kembali ke Dashboard KPI
            </a>
        </div>
    </div>

</div>
@endsection
