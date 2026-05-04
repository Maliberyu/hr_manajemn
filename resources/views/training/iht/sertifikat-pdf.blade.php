<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        background: #fff;
        width: 297mm;
        height: 210mm;
        overflow: hidden;
    }
    .page {
        width: 297mm;
        height: 210mm;
        padding: 12mm 18mm;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
        border: 12px solid #1e3a8a;
        position: relative;
    }
    .inner-border {
        position: absolute;
        inset: 6px;
        border: 2px solid #93c5fd;
        pointer-events: none;
    }
    .header {
        display: flex;
        align-items: center;
        gap: 20px;
        width: 100%;
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 10px;
    }
    .logo { width: 70px; height: 70px; object-fit: contain; }
    .logo-placeholder {
        width: 70px; height: 70px;
        background: #dbeafe;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 9px; color: #3b82f6; text-align: center; font-weight: bold;
    }
    .rs-info { flex: 1; }
    .rs-name { font-size: 16px; font-weight: bold; color: #1e3a8a; }
    .rs-sub  { font-size: 9px; color: #6b7280; margin-top: 2px; }
    .title-block { text-align: center; margin: 8px 0; }
    .sertifikat-label {
        font-size: 32px; font-weight: bold; color: #1e3a8a;
        letter-spacing: 4px; text-transform: uppercase;
    }
    .subtitle { font-size: 11px; color: #6b7280; margin-top: 2px; }
    .nomor { font-size: 10px; color: #9ca3af; margin-top: 4px; font-family: monospace; }
    .diberikan { font-size: 11px; color: #374151; margin-bottom: 4px; }
    .nama-penerima {
        font-size: 26px; font-weight: bold; color: #1e3a8a;
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 4px; margin-bottom: 6px;
        min-width: 200px; text-align: center;
    }
    .ket { font-size: 10px; color: #6b7280; text-align: center; line-height: 1.5; }
    .nilai-box {
        display: inline-block;
        background: #eff6ff; border: 1px solid #93c5fd;
        border-radius: 6px; padding: 4px 14px;
        font-size: 13px; font-weight: bold; color: #1d4ed8;
        margin-top: 6px;
    }
    .footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        width: 100%;
        border-top: 1px solid #e5e7eb;
        padding-top: 10px;
        margin-top: 4px;
    }
    .ttd-block { text-align: center; min-width: 130px; }
    .ttd-garis { border-top: 1px solid #374151; margin-top: 28px; }
    .ttd-nama { font-size: 11px; font-weight: bold; color: #1e3a8a; margin-top: 3px; }
    .ttd-jabatan { font-size: 9px; color: #6b7280; }
    .tanggal-block { font-size: 9px; color: #9ca3af; text-align: center; }
    .ornament { color: #93c5fd; font-size: 14px; margin: 0 6px; }
</style>
</head>
<body>
<div class="page">
    <div class="inner-border"></div>

    {{-- Header --}}
    <div class="header">
        @if($logo)
        <img src="{{ public_path('storage/' . ltrim(str_replace('/storage/', '', $logo), '/')) }}" class="logo" alt="Logo RS">
        @else
        <div class="logo-placeholder">RSIA<br>Respati</div>
        @endif
        <div class="rs-info">
            <div class="rs-name">RSIA RESPATI</div>
            <div class="rs-sub">Rumah Sakit Ibu dan Anak Respati</div>
        </div>
    </div>

    {{-- Title --}}
    <div class="title-block">
        <div class="sertifikat-label">✦ Sertifikat ✦</div>
        <div class="subtitle">Penghargaan Keikutsertaan dalam Pelatihan</div>
        <div class="nomor">{{ $nomor }}</div>
    </div>

    {{-- Penerima --}}
    <div style="text-align:center">
        <div class="diberikan">Diberikan kepada</div>
        <div class="nama-penerima">{{ $peserta->pegawai?->nama }}</div>
        <div class="ket">
            {{ $peserta->pegawai?->jbtn }}
            @if($peserta->pegawai?->departemenRef?->nama)
            — {{ $peserta->pegawai?->departemenRef?->nama }}
            @endif
        </div>
        <div class="ket" style="margin-top:6px;">
            atas keikutsertaan dalam pelatihan
        </div>
        <div style="font-size:15px; font-weight:bold; color:#1e3a8a; margin-top:4px;">
            {{ $iht->nama_training }}
        </div>
        <div class="ket" style="margin-top:3px;">
            {{ $iht->lokasi }} · {{ $iht->tanggal_mulai->translatedFormat('d F Y') }}
            @if(!$iht->tanggal_mulai->equalTo($iht->tanggal_selesai))
            — {{ $iht->tanggal_selesai->translatedFormat('d F Y') }}
            @endif
        </div>
        @if($peserta->nilai)
        <div class="nilai-box">Nilai: {{ number_format($peserta->nilai, 1) }}</div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="tanggal-block">
            <div>Ditetapkan di Tasikmalaya</div>
            <div>{{ $iht->tanggal_selesai->translatedFormat('d F Y') }}</div>
        </div>
        <div class="ttd-block">
            <div class="ttd-garis"></div>
            <div class="ttd-nama">{{ $iht->penandatangan_nama ?? '................................' }}</div>
            <div class="ttd-jabatan">{{ $iht->penandatangan_jabatan ?? '' }}</div>
        </div>
    </div>
</div>
</body>
</html>
