<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;font-size:10px;color:#1a1a1a;padding:20px}
.header{text-align:center;border-bottom:2px solid #1d4ed8;padding-bottom:10px;margin-bottom:14px}
.header h1{font-size:13px;font-weight:bold;color:#1d4ed8}
.header h2{font-size:11px;font-weight:bold;margin-top:2px}
.header p{font-size:9px;color:#666;margin-top:2px}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:4px;background:#f8fafc;padding:8px 10px;border-radius:6px;margin-bottom:12px}
.info-row{display:flex;gap:6px}
.info-label{color:#6b7280;width:110px;flex-shrink:0}
.info-value{font-weight:500}
table.kriteria{width:100%;border-collapse:collapse;margin-bottom:12px}
table.kriteria th{background:#1d4ed8;color:white;padding:5px 8px;text-align:left;font-size:9px}
table.kriteria td{padding:5px 8px;border-bottom:1px solid #f0f0f0;font-size:9px}
table.kriteria tr:nth-child(even)td{background:#f8fafc}
.nilai-badge{display:inline-block;padding:2px 8px;border-radius:99px;font-weight:bold;font-size:9px}
.nilai-5{background:#dbeafe;color:#1d4ed8}
.nilai-4{background:#dcfce7;color:#166534}
.nilai-3{background:#fef9c3;color:#854d0e}
.nilai-2{background:#ffedd5;color:#9a3412}
.nilai-1{background:#fee2e2;color:#991b1b}
.summary{background:#1d4ed8;color:white;padding:10px 14px;border-radius:6px;display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.evaluasi{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
.eval-box{background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:8px}
.eval-title{font-weight:bold;font-size:9px;color:#374151;margin-bottom:4px}
.tanda-tangan{display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-top:20px}
.ttd-box{text-align:center;border-top:1px solid #d1d5db;padding-top:6px}
</style>
</head>
<body>

<div class="header">
    <h1>RSIA RESPATI — FORM PENILAIAN PRESTASI KERJA KARYAWAN</h1>
    <h2>Semester {{ $penilaian->semester }} Tahun {{ $penilaian->tahun }}</h2>
    <p>{{ now()->translatedFormat('d F Y') }}</p>
</div>

<div class="info-grid">
    <div class="info-row"><span class="info-label">Nama</span><span class="info-value">{{ $penilaian->pegawai?->nama }}</span></div>
    <div class="info-row"><span class="info-label">NIK</span><span class="info-value">{{ $penilaian->nik }}</span></div>
    <div class="info-row"><span class="info-label">Jabatan</span><span class="info-value">{{ $penilaian->pegawai?->jbtn }}</span></div>
    <div class="info-row"><span class="info-label">Departemen</span><span class="info-value">{{ $penilaian->pegawai?->departemenRef?->nama }}</span></div>
    <div class="info-row"><span class="info-label">Penilai</span><span class="info-value">{{ $penilaian->penilai?->nama }}</span></div>
    <div class="info-row"><span class="info-label">Periode</span><span class="info-value">Semester {{ $penilaian->semester }} / {{ $penilaian->tahun }}</span></div>
</div>

<table class="kriteria">
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th>Kriteria Penilaian</th>
            <th style="width:8%">Bobot</th>
            <th style="width:10%">Nilai</th>
            <th style="width:15%">Predikat</th>
            <th style="width:10%">Skor</th>
            <th>Catatan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($kriteria as $i => $k)
        @php
            $nilaiRow = $penilaian->nilaiList->firstWhere('kriteria_id', $k->id);
            $val      = $nilaiRow?->nilai ?? 0;
            $skor     = $val > 0 ? round(($val/5) * $k->bobot, 2) : 0;
            $skalaLabel = \App\Models\PenilaianPrestasi::SKALA[$val] ?? '-';
            $cls = 'nilai-' . ($val ?: 3);
        @endphp
        <tr>
            <td style="text-align:center">{{ $i+1 }}</td>
            <td>{{ $k->nama }}</td>
            <td style="text-align:center">{{ $k->bobot }}%</td>
            <td style="text-align:center"><strong>{{ $val ?: '-' }}</strong></td>
            <td style="text-align:center"><span class="nilai-badge {{ $cls }}">{{ $skalaLabel }}</span></td>
            <td style="text-align:center">{{ $skor }}</td>
            <td style="color:#6b7280;font-style:italic">{{ $nilaiRow?->catatan }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary">
    <span style="font-size:11px;font-weight:bold">NILAI AKHIR</span>
    <div style="text-align:right">
        <span style="font-size:18px;font-weight:bold">{{ $penilaian->nilai_akhir ?? '-' }}</span>
        <span style="font-size:11px;margin-left:8px">/ 100 — {{ $penilaian->predikat ?? '-' }}</span>
    </div>
</div>

@if($penilaian->kelebihan || $penilaian->kekurangan || $penilaian->saran || $penilaian->rekomendasi)
<div class="evaluasi">
    @if($penilaian->kelebihan)
    <div class="eval-box"><div class="eval-title">Kelebihan Karyawan</div><p>{{ $penilaian->kelebihan }}</p></div>
    @endif
    @if($penilaian->kekurangan)
    <div class="eval-box"><div class="eval-title">Kekurangan Karyawan</div><p>{{ $penilaian->kekurangan }}</p></div>
    @endif
    @if($penilaian->saran)
    <div class="eval-box"><div class="eval-title">Saran</div><p>{{ $penilaian->saran }}</p></div>
    @endif
    @if($penilaian->rekomendasi)
    <div class="eval-box"><div class="eval-title">Rekomendasi</div><p>{{ $penilaian->rekomendasi }}</p></div>
    @endif
</div>
@endif

<div class="tanda-tangan">
    @foreach(['Atasan Penilai','Kepegawaian','Direktur','Karyawan'] as $pihak)
    <div class="ttd-box">
        <p style="font-size:9px;font-weight:bold;margin-bottom:40px">{{ $pihak }}</p>
        <p style="font-size:8px;color:#9ca3af">(............................)</p>
    </div>
    @endforeach
</div>

</body>
</html>
