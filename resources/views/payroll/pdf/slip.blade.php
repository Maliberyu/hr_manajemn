<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a1a; background: white; padding: 16px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 2px solid #1d4ed8; }
        .company { font-size: 13px; font-weight: bold; color: #1d4ed8; }
        .company-sub { font-size: 9px; color: #6b7280; margin-top: 2px; }
        .slip-title { text-align: right; }
        .slip-title h2 { font-size: 12px; font-weight: bold; color: #1a1a1a; }
        .slip-title p { font-size: 9px; color: #6b7280; margin-top: 2px; }
        .emp-info { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; margin-bottom: 12px; background: #f8fafc; padding: 8px 10px; border-radius: 6px; }
        .emp-row { display: flex; gap: 6px; }
        .emp-label { color: #6b7280; width: 90px; flex-shrink: 0; }
        .emp-value { font-weight: 500; }
        .cols { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
        .section-title { font-weight: bold; font-size: 9px; color: #fff; background: #1d4ed8; padding: 4px 8px; border-radius: 4px 4px 0 0; margin-bottom: 0; }
        .section-title.red { background: #dc2626; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items tr { border-bottom: 1px solid #f1f5f9; }
        table.items tr:last-child { border-bottom: none; }
        table.items td { padding: 3px 8px; }
        table.items td.amt { text-align: right; font-weight: 500; }
        table.items tr.total-row { background: #f0f9ff; font-weight: bold; }
        .summary { background: #1d4ed8; color: white; padding: 10px 14px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .summary-label { font-size: 10px; opacity: 0.85; }
        .summary-amount { font-size: 16px; font-weight: bold; }
        .footer { font-size: 8px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 6px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 8px; font-weight: bold; background: #dcfce7; color: #166534; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <div class="company">RSIA RESPATI</div>
        <div class="company-sub">Rumah Sakit Ibu & Anak Respati · Kab. Tasikmalaya</div>
    </div>
    <div class="slip-title">
        <h2>SLIP GAJI KARYAWAN</h2>
        <p>Periode: {{ \Carbon\Carbon::create($slip->tahun, $slip->bulan)->translatedFormat('F Y') }}</p>
        <span class="badge">{{ strtoupper($slip->status) }}</span>
    </div>
</div>

<div class="emp-info">
    <div class="emp-row"><span class="emp-label">Nama</span><span class="emp-value">{{ $slip->pegawai?->nama }}</span></div>
    <div class="emp-row"><span class="emp-label">NIK</span><span class="emp-value">{{ $slip->nik }}</span></div>
    <div class="emp-row"><span class="emp-label">Jabatan</span><span class="emp-value">{{ $slip->pegawai?->jbtn }}</span></div>
    <div class="emp-row"><span class="emp-label">Departemen</span><span class="emp-value">{{ $slip->pegawai?->departemenRef?->nama }}</span></div>
    <div class="emp-row"><span class="emp-label">Golongan</span><span class="emp-value">{{ $slip->pegawai?->payrollSetting?->golongan ?? '-' }}</span></div>
    <div class="emp-row"><span class="emp-label">Tanggal Cetak</span><span class="emp-value">{{ now()->translatedFormat('d F Y') }}</span></div>
</div>

@php
    $tambah = $slip->komponenSlip->where('jenis','tambah');
    $kurang = $slip->komponenSlip->where('jenis','kurang');
    $totalTambah = $tambah->sum('nilai');
    $totalKurang = $kurang->sum('nilai');
@endphp

<div class="cols">
    <div>
        <div class="section-title">PENDAPATAN (+)</div>
        <table class="items">
            @foreach($tambah as $k)
            <tr>
                <td>{{ $k->nama }}</td>
                <td class="amt">Rp {{ number_format($k->nilai, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total Pendapatan</td>
                <td class="amt">Rp {{ number_format($totalTambah, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    <div>
        <div class="section-title red">POTONGAN (–)</div>
        <table class="items">
            @foreach($kurang as $k)
            <tr>
                <td>{{ $k->nama }}</td>
                <td class="amt">Rp {{ number_format($k->nilai, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td>Total Potongan</td>
                <td class="amt">Rp {{ number_format($totalKurang, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</div>

<div class="summary">
    <div>
        <div class="summary-label">GAJI BERSIH DITERIMA</div>
        <div style="font-size:8px;opacity:0.7;margin-top:2px">Pendapatan – Potongan</div>
    </div>
    <div class="summary-amount">Rp {{ number_format($slip->gaji_bersih, 0, ',', '.') }}</div>
</div>

<table style="width:100%;margin-top:20px;margin-bottom:8px">
    <tr>
        <td style="width:50%;text-align:center">
            <p style="font-size:9px;color:#6b7280">Mengetahui,</p>
            <p style="font-size:9px;font-weight:bold;margin-top:2px">HRD</p>
            <div style="height:40px;border-bottom:1px solid #d1d5db;width:120px;margin:4px auto 0"></div>
            <p style="font-size:8px;color:#6b7280;margin-top:2px">(.............................)</p>
        </td>
        <td style="width:50%;text-align:center">
            <p style="font-size:9px;color:#6b7280">Penerima,</p>
            <p style="font-size:9px;font-weight:bold;margin-top:2px">{{ $slip->pegawai?->nama }}</p>
            <div style="height:40px;border-bottom:1px solid #d1d5db;width:120px;margin:4px auto 0"></div>
            <p style="font-size:8px;color:#6b7280;margin-top:2px">(.............................)</p>
        </td>
    </tr>
</table>

<div class="footer">
    Dokumen ini dicetak secara elektronik oleh sistem HR Manajemen RSIA Respati &middot;
    Dicetak: {{ now()->translatedFormat('d F Y H:i') }}
</div>

</body>
</html>
