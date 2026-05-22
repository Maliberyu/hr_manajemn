<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Jadwal Rencana Shift — {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a1a; }
@page { size: A4 landscape; margin: 12mm; }
@media print { body { print-color-adjust: exact; -webkit-print-color-adjust: exact; } }

h1  { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
h2  { font-size: 11px; color: #555; margin-bottom: 8px; }
.header { border-bottom: 2px solid #1d4ed8; padding-bottom: 6px; margin-bottom: 10px; }
.meta   { display: flex; gap: 24px; font-size: 9px; color: #666; margin-top: 3px; }

table { width: 100%; border-collapse: collapse; font-size: 9px; }
th    { background: #1d4ed8; color: white; padding: 4px 3px; text-align: center; white-space: nowrap; }
th.nama { text-align: left; padding-left: 6px; min-width: 120px; }
td    { padding: 3px 2px; text-align: center; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
td.nama { text-align: left; padding-left: 6px; font-weight: 600; border-right: 1px solid #d1d5db; }
td.nik  { font-size: 8px; color: #6b7280; font-family: monospace; }
tr:nth-child(even) { background: #f9fafb; }

.cell { display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 5px; font-weight: bold; font-size: 9px; }
.P  { background: #dbeafe; color: #1d4ed8; }
.S  { background: #fef3c7; color: #92400e; }
.M  { background: #ede9fe; color: #6d28d9; }
.MP { background: #cffafe; color: #0e7490; }
.MS { background: #ffedd5; color: #c2410c; }
.MM { background: #e0e7ff; color: #4338ca; }
.xx { background: #f3f4f6; color: #9ca3af; }

.legend { display: flex; gap: 10px; margin-top: 8px; font-size: 8px; flex-wrap: wrap; }
.lbl    { padding: 1px 5px; border-radius: 4px; font-weight: bold; font-size: 8px; }
</style>
</head>
<body>

<div class="header">
    <h1>Jadwal Rencana Shift Karyawan</h1>
    <h2>{{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
        @if(isset($departemen[$depId])) — {{ $departemen[$depId] }} @endif
    </h2>
    <div class="meta">
        <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
        <span>Total karyawan: {{ $pegawai->count() }}</span>
    </div>
</div>

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
            <th>Masuk</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pegawai as $peg)
        @php $jadwal = $peg->jadwalBulanan->first(); $masuk = 0; @endphp
        <tr>
            <td class="nama">
                {{ $peg->nama }}<br>
                <span class="nik">{{ $peg->nik }}</span>
            </td>
            @for($h = 1; $h <= $jumlahHari; $h++)
            @php
                $shift = $jadwal ? ($jadwal->{"h{$h}"} ?? '') : '';
                $tgl   = \Carbon\Carbon::create($tahun, $bulan, $h);
                $abbr  = match(true) {
                    str_starts_with($shift, 'Pagi')        => 'P',
                    str_starts_with($shift, 'Siang')       => 'S',
                    str_starts_with($shift, 'Malam')       => 'M',
                    str_starts_with($shift, 'Midle Pagi')  => 'MP',
                    str_starts_with($shift, 'Midle Siang') => 'MS',
                    str_starts_with($shift, 'Midle Malam') => 'MM',
                    default                                 => '·',
                };
                $cls = $abbr === '·' ? 'xx' : $abbr;
                if ($abbr !== '·') $masuk++;
            @endphp
            <td style="{{ $tgl->isWeekend() ? 'background:#fef2f2' : '' }}">
                <span class="cell {{ $cls }}">{{ $abbr }}</span>
            </td>
            @endfor
            <td><strong>{{ $masuk }}</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="legend">
    <div><span class="lbl P">P</span> Pagi</div>
    <div><span class="lbl S">S</span> Siang/Sore</div>
    <div><span class="lbl M">M</span> Malam</div>
    <div><span class="lbl MP">MP</span> Midle Pagi</div>
    <div><span class="lbl MS">MS</span> Midle Siang</div>
    <div><span class="lbl MM">MM</span> Midle Malam</div>
    <div><span class="lbl xx">·</span> Libur / Off</div>
</div>

<script>window.onload = () => window.print();</script>
</body>
</html>
