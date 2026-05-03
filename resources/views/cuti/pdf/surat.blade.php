<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 0; padding: 20px 40px; }
    h2 { text-align: center; font-size: 15px; margin-bottom: 4px; }
    .subtitle { text-align: center; font-size: 11px; color: #444; margin-bottom: 16px; }
    hr { border: 1.5px solid #111; margin-bottom: 18px; }
    table.info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    table.info td { padding: 3px 6px; vertical-align: top; }
    table.info td:first-child { width: 160px; font-weight: bold; }
    .no-surat { text-align: right; margin-bottom: 16px; font-size: 11px; }
    .ttd { margin-top: 40px; display: flex; justify-content: space-between; }
    .ttd-box { text-align: center; width: 200px; }
    .ttd-line { margin-top: 60px; border-top: 1px solid #111; padding-top: 4px; font-size: 11px; }
</style>
</head>
<body>

<h2>SURAT IZIN CUTI</h2>
<div class="subtitle">Sistem Manajemen SDM</div>
<hr>

<div class="no-surat">No: {{ $cuti->no_pengajuan }}</div>

<p>Yang bertanda tangan di bawah ini menerangkan bahwa:</p>

<table class="info">
    <tr><td>Nama</td><td>: {{ $cuti->pegawai?->nama }}</td></tr>
    <tr><td>NIK</td><td>: {{ $cuti->nik }}</td></tr>
    <tr><td>Jabatan</td><td>: {{ $cuti->pegawai?->jbtn }}</td></tr>
    <tr><td>Departemen</td><td>: {{ $cuti->pegawai?->departemenRef?->nama ?? '-' }}</td></tr>
</table>

<p>Diberikan izin cuti dengan keterangan sebagai berikut:</p>

<table class="info">
    <tr><td>Jenis Cuti</td><td>: {{ $cuti->urgensi }}</td></tr>
    <tr><td>Mulai Cuti</td><td>: {{ $cuti->tanggal_awal->translatedFormat('d F Y') }}</td></tr>
    <tr><td>Selesai Cuti</td><td>: {{ $cuti->tanggal_akhir->translatedFormat('d F Y') }}</td></tr>
    <tr><td>Jumlah Hari</td><td>: {{ $cuti->jumlah }} hari kerja</td></tr>
    <tr><td>Alamat Selama Cuti</td><td>: {{ $cuti->alamat }}</td></tr>
    <tr><td>Kepentingan</td><td>: {{ $cuti->kepentingan }}</td></tr>
    @if($cuti->penanggungJawab)
    <tr><td>Penanggung Jawab</td><td>: {{ $cuti->penanggungJawab->nama }}</td></tr>
    @endif
</table>

<p>Demikian surat izin cuti ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

<p style="margin-top: 12px;">Disetujui pada: {{ $cuti->approved_hrd_at?->translatedFormat('d F Y') }}</p>

<div class="ttd">
    <div class="ttd-box">
        <p>Atasan Langsung,</p>
        <div class="ttd-line">
            <p style="font-size:10px; color:#555;">Disetujui: {{ $cuti->approved_atasan_at?->translatedFormat('d M Y') }}</p>
        </div>
    </div>
    <div class="ttd-box">
        <p>HRD,</p>
        <div class="ttd-line">
            <p style="font-size:10px; color:#555;">Disetujui: {{ $cuti->approved_hrd_at?->translatedFormat('d M Y') }}</p>
        </div>
    </div>
    <div class="ttd-box">
        <p>Yang bersangkutan,</p>
        <div class="ttd-line">
            <p>{{ $cuti->pegawai?->nama }}</p>
        </div>
    </div>
</div>

</body>
</html>
