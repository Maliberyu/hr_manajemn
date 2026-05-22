<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Realisasi Jadwal Shift — {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a1a; }
@page { size: A4 landscape; margin: 12mm; }
@media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }

h1  { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
h2  { font-size: 11px; color: #555; margin-bottom: 8px; }
.header { border-bottom: 2px solid #1d4ed8; padding-bottom: 6px; margin-bottom: 10px; }
.meta { display: flex; gap: 24px; font-size: 9px; color: #666; margin-top: 3px; }

table { width: 100%; border-collapse: collapse; font-size: 9px; }
th    { background: #1d4ed8; color: white; padding: 4px 3px; text-align: center; white-space: nowrap; }
th.nama { text-align: left; padding-left: 6px; min-width: 120px; }
td    { padding: 3px 2px; text-align: center; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
td.nama { text-align: left; padding-left: 6px; font-weight: 600; border-right: 1px solid #d1d5db; }
td.nik  { font-size: 8px; color: #6b7280; font-family: monospace; }
tr:nth-child(even) { background: #f9fafb; }
tr.weekend { background: #fef2f2; }
tr.today   { background: #eff6ff; }

.cell { display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 5px; font-weight: bold; font-size: 9px; position: relative; }
.pagi   { background: #dbeafe; color: #1d4ed8; }
.sore   { background: #fef3c7; color: #92400e; }
.malam  { background: #ede9fe; color: #6d28d9; }
.libur  { background: #f3f4f6; color: #9ca3af; }
.custom { background: #d1fae5; color: #065f46; }

.dot-hadir  { width:5px; height:5px; background:#22c55e; border-radius:50%; display:inline-block; margin-left:1px; }
.dot-absen  { width:5px; height:5px; background:#ef4444; border-radius:50%; display:inline-block; margin-left:1px; }
.badge-ts   { position:absolute; top:-3px; right:-3px; width:7px; height:7px; background:#f97316; border-radius:50%; }
.badge-lb   { position:absolute; top:-3px; left:-3px;  width:7px; height:7px; background:#eab308; border-radius:50%; }

.legend { display: flex; gap: 12px; margin-top: 8px; font-size: 8px; }
.legend-item { display: flex; align-items: center; gap: 4px; }
.lbl { padding: 1px 5px; border-radius: 4px; font-weight: bold; font-size: 8px; }

.detail-table td { padding: 4px 8px; border: 1px solid #e5e7eb; }
.detail-table th { background: #1d4ed8; color: white; padding: 5px 8px; text-align: left; }
</style>
</head>
<body>

<div class="header">
    <h1>Realisasi Jadwal Shift Karyawan</h1>
    <h2>{{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
        @if($depId && isset($departemen[$depId])) — {{ $departemen[$depId] }} @endif
    </h2>
    <div class="meta">
        <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
        <span>Total karyawan: {{ count($realisasi) }}</span>
        @if($mode === 'orang' && $pegawaiDipilih)
        <span>Pegawai: {{ $pegawaiDipilih->nama }} ({{ $pegawaiDipilih->nik }})</span>
        @endif
    </div>
</div>

@if($mode === 'orang' && isset($realisasi[0]))
{{-- Detail per orang --}}
@php $row = $realisasi[0]; $peg = $row['pegawai']; @endphp
<h2 style="margin-bottom:6px">{{ $peg->nama }} — {{ $peg->nik }}</h2>
<table class="detail-table">
    <tr>
        <th>Tgl</th><th>Hari</th><th>Shift Rencana</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Status</th><th>Keterangan</th>
    </tr>
    @foreach($row['hari_data'] as $hari => $d)
    <tr>
        <td>{{ $hari }}</td>
        <td>{{ $d['tanggal']->translatedFormat('D') }}</td>
        <td>{{ $d['shift_rencana'] ? $d['shift_rencana']->nama . ' (' . $d['shift_rencana']->jam_label . ')' : ($d['nama_rencana'] ?: '—') }}</td>
        <td>{{ $d['jam_masuk'] ?? '—' }}</td>
        <td>{{ $d['jam_keluar'] ?? '—' }}</td>
        <td>
            @if($d['is_libur'] && !$d['absensi']) Libur
            @elseif($d['absensi']) Hadir
            @elseif($d['shift_rencana'] && !$d['is_libur']) Absen
            @else —
            @endif
        </td>
        <td>
            @if($d['is_tukar']) [Tukar Shift] @endif
            @if($d['ovt_menit'] >= 30) [Lembur {{ floor($d['ovt_menit']/60) }}j {{ $d['ovt_menit']%60 }}m] @endif
            @foreach($d['lembur'] as $lb) [Lembur {{ $lb->status }}] @endforeach
        </td>
    </tr>
    @endforeach
</table>

@else
{{-- Grid per departemen --}}
<table>
    <thead>
        <tr>
            <th class="nama">Pegawai / NIK</th>
            @for($h = 1; $h <= $jumlahHari; $h++)
            @php $tgl = \Carbon\Carbon::create($tahun, $bulan, $h); @endphp
            <th style="{{ $tgl->isWeekend() ? 'background:#ef4444' : '' }}">
                {{ $h }}<br>{{ $tgl->translatedFormat('D') }}
            </th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach($realisasi as $row)
        @php $peg = $row['pegawai']; @endphp
        <tr>
            <td class="nama">
                {{ $peg->nama }}<br>
                <span class="nik">{{ $peg->nik }}</span>
            </td>
            @foreach($row['hari_data'] as $hari => $d)
            @php
                $kode  = $d['shift_real']?->kode ?? 'libur';
                $cls   = match($kode) {
                    'pagi'  => 'pagi',
                    'sore'  => 'sore',
                    'malam' => 'malam',
                    'libur' => 'libur',
                    default => 'custom',
                };
                $abbr  = match($kode) {
                    'pagi'  => 'P', 'sore' => 'S', 'malam' => 'M', 'libur' => '·',
                    default => strtoupper(substr($kode,0,2)),
                };
                $hadir = $d['absensi'] !== null;
            @endphp
            <td style="{{ $d['tanggal']->isWeekend() ? 'background:#fef2f2' : '' }}">
                <span class="cell {{ $cls }}" style="position:relative">
                    {{ $abbr }}
                    @if($d['is_tukar'])<span class="badge-ts"></span>@endif
                    @if($d['ovt_menit'] >= 30 || $d['lembur']->isNotEmpty())<span class="badge-lb"></span>@endif
                </span>
                @if($hadir)<span class="dot-hadir"></span>@elseif($d['shift_rencana'] && !$d['is_libur'])<span class="dot-absen"></span>@endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="legend">
    <div class="legend-item"><span class="lbl pagi">P</span> Pagi (07–14)</div>
    <div class="legend-item"><span class="lbl sore">S</span> Sore (14–21)</div>
    <div class="legend-item"><span class="lbl malam">M</span> Malam (21–07)</div>
    <div class="legend-item"><span class="dot-hadir"></span> Hadir</div>
    <div class="legend-item"><span class="dot-absen"></span> Absen</div>
    <div class="legend-item"><span style="width:7px;height:7px;background:#f97316;border-radius:50%;display:inline-block"></span> Tukar Shift</div>
    <div class="legend-item"><span style="width:7px;height:7px;background:#eab308;border-radius:50%;display:inline-block"></span> Lembur</div>
</div>

<script>window.onload = () => window.print();</script>
</body>
</html>
