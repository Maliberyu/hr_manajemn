<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta — {{ $iht->nama_training }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
            padding: 20px 28px;
        }

        /* ─── Toolbar (hanya di layar, hilang saat cetak) ── */
        #toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 1px solid #e5e7eb;
        }
        #toolbar button {
            padding: 7px 18px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        #toolbar button:hover { background: #1d4ed8; }
        #toolbar a {
            font-size: 12px;
            color: #6b7280;
            text-decoration: none;
        }
        #toolbar a:hover { color: #374151; }

        /* ─── Header kop ── */
        .kop {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1d4ed8;
        }
        .kop img { height: 52px; width: auto; }
        .kop-text h1 { font-size: 14px; font-weight: 700; color: #1d4ed8; letter-spacing: 0.3px; }
        .kop-text p  { font-size: 10px; color: #6b7280; margin-top: 1px; }

        /* ─── Judul dokumen ── */
        .doc-title {
            text-align: center;
            margin: 12px 0 10px;
        }
        .doc-title h2 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .doc-title p { font-size: 10px; color: #6b7280; margin-top: 2px; }

        /* ─── Info training ── */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4px 16px;
            margin-bottom: 12px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .info-item label { font-size: 9px; color: #94a3b8; text-transform: uppercase; display: block; }
        .info-item span  { font-size: 11px; font-weight: 600; color: #1e293b; }

        /* ─── Tabel ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        thead th {
            background: #1d4ed8;
            color: #fff;
            padding: 6px 8px;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        thead th.left { text-align: left; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        tbody tr:hover td { background: #eff6ff; }
        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
            vertical-align: middle;
        }
        td.center { text-align: center; }
        td.ttd    {
            height: 36px;
            min-width: 60px;
        }

        .badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 20px;
            font-size: 9.5px;
            font-weight: 700;
        }
        .badge-hadir   { background: #dcfce7; color: #15803d; }
        .badge-selesai { background: #dbeafe; color: #1d4ed8; }
        .badge-tidak   { background: #fee2e2; color: #b91c1c; }
        .badge-default { background: #f1f5f9; color: #64748b; }

        /* ─── Ringkasan ── */
        .summary {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        .summary-box {
            flex: 1;
            text-align: center;
            padding: 8px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .summary-box .num  { font-size: 20px; font-weight: 700; color: #1d4ed8; }
        .summary-box .lbl  { font-size: 9px; color: #6b7280; margin-top: 2px; text-transform: uppercase; }

        /* ─── Penandatangan ── */
        .ttd-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
        }
        .ttd-box {
            text-align: center;
            width: 200px;
        }
        .ttd-box .ttd-label { font-size: 10px; margin-bottom: 4px; }
        .ttd-box .ttd-nama  {
            margin-top: 48px;
            border-top: 1px solid #374151;
            padding-top: 4px;
            font-size: 10px;
            font-weight: 700;
        }
        .ttd-box .ttd-jabatan { font-size: 9px; color: #6b7280; }

        /* ─── Footer ── */
        .print-footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* ─── Print ── */
        @media print {
            #toolbar     { display: none !important; }
            body         { padding: 10px 16px; }
            @page        { size: A4 portrait; margin: 10mm 12mm; }
        }
    </style>
</head>
<body>

    {{-- ── Toolbar (screen only) ── --}}
    <div id="toolbar">
        <button onclick="window.print()">
            &#128438; Cetak / Simpan PDF
        </button>
        <a href="{{ route('training.iht.show', $iht) }}">← Kembali ke Detail IHT</a>
    </div>

    {{-- ── Kop Surat ── --}}
    <div class="kop">
        @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="Logo">
        @endif
        <div class="kop-text">
            <h1>Daftar Peserta In-House Training (IHT)</h1>
            <p>HR Manajemen &mdash; dicetak {{ now()->translatedFormat('d F Y, H:i') }}</p>
        </div>
    </div>

    {{-- ── Judul ── --}}
    <div class="doc-title">
        <h2>{{ $iht->nama_training }}</h2>
        <p>{{ $iht->penyelenggara }}{{ $iht->pemateri ? ' · Pemateri: ' . $iht->pemateri : '' }}</p>
    </div>

    {{-- ── Info Training ── --}}
    <div class="info-grid">
        <div class="info-item">
            <label>Tanggal</label>
            <span>
                {{ $iht->tanggal_mulai->translatedFormat('d M Y') }}
                @if(!$iht->tanggal_mulai->equalTo($iht->tanggal_selesai))
                — {{ $iht->tanggal_selesai->translatedFormat('d M Y') }}
                @endif
            </span>
        </div>
        <div class="info-item">
            <label>Lokasi</label>
            <span>{{ $iht->lokasi }}</span>
        </div>
        <div class="info-item">
            <label>Jam</label>
            <span>
                @if($iht->jam_mulai)
                    {{ substr($iht->jam_mulai, 0, 5) }}{{ $iht->jam_selesai ? ' – ' . substr($iht->jam_selesai, 0, 5) : '' }}
                @else
                    —
                @endif
            </span>
        </div>
        <div class="info-item">
            <label>Status</label>
            <span>{{ \App\Models\IHT::STATUS[$iht->status] ?? $iht->status }}</span>
        </div>
        <div class="info-item">
            <label>Kuota</label>
            <span>{{ $iht->kuota ? $iht->kuota . ' orang' : 'Tidak dibatasi' }}</span>
        </div>
        <div class="info-item">
            <label>Total Terdaftar</label>
            <span>{{ $peserta->count() }} peserta</span>
        </div>
    </div>

    {{-- ── Ringkasan ── --}}
    @php
        $totalHadir   = $peserta->whereIn('status', ['hadir', 'selesai'])->count();
        $totalTidak   = $peserta->where('status', 'tidak_hadir')->count();
        $totalPending = $peserta->where('status', 'terdaftar')->count();
        $nilaiRata    = $peserta->whereNotNull('nilai')->avg('nilai');
    @endphp
    <div class="summary">
        <div class="summary-box">
            <div class="num">{{ $peserta->count() }}</div>
            <div class="lbl">Total Peserta</div>
        </div>
        <div class="summary-box">
            <div class="num" style="color:#15803d">{{ $totalHadir }}</div>
            <div class="lbl">Hadir</div>
        </div>
        <div class="summary-box">
            <div class="num" style="color:#b91c1c">{{ $totalTidak }}</div>
            <div class="lbl">Tidak Hadir</div>
        </div>
        <div class="summary-box">
            <div class="num" style="color:#d97706">{{ $totalPending }}</div>
            <div class="lbl">Belum Absen</div>
        </div>
        @if($nilaiRata !== null)
        <div class="summary-box">
            <div class="num" style="color:#7c3aed">{{ number_format($nilaiRata, 1) }}</div>
            <div class="lbl">Rata-rata Nilai</div>
        </div>
        @endif
    </div>

    {{-- ── Tabel Peserta ── --}}
    @if($peserta->isEmpty())
    <p style="text-align:center; color:#94a3b8; padding:20px 0;">Belum ada peserta terdaftar.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:28px">No</th>
                <th class="left">Nama Peserta</th>
                <th class="left">Jabatan / Unit</th>
                <th style="width:72px">Status</th>
                <th style="width:52px">Masuk</th>
                <th style="width:52px">Selesai</th>
                <th style="width:52px">Durasi</th>
                <th style="width:38px">Nilai</th>
                <th style="width:72px">No. Sertifikat</th>
                <th style="width:60px">TTD Peserta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta as $i => $p)
            @php
                $badgeClass = match($p->status) {
                    'hadir'        => 'badge-hadir',
                    'selesai'      => 'badge-selesai',
                    'tidak_hadir'  => 'badge-tidak',
                    default        => 'badge-default',
                };
                $statusLabel = \App\Models\IHTPeserta::STATUS[$p->status] ?? $p->status;
            @endphp
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $p->pegawai?->nama ?? '—' }}</strong>
                    <br><span style="font-size:9px;color:#6b7280;">{{ $p->pegawai?->nik }}</span>
                </td>
                <td>
                    {{ $p->pegawai?->jbtn ?? '—' }}
                    @if($p->pegawai?->departemenRef)
                    <br><span style="font-size:9px;color:#6b7280;">{{ $p->pegawai->departemenRef->nama }}</span>
                    @endif
                </td>
                <td class="center">
                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                </td>
                <td class="center">
                    @if($p->check_in_at)
                        <strong>{{ $p->check_in_at->format('H:i') }}</strong>
                        <br><span style="font-size:9px;color:#6b7280;">{{ $p->check_in_at->format('d/m') }}</span>
                    @else
                        <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($p->check_out_at)
                        <strong>{{ $p->check_out_at->format('H:i') }}</strong>
                        <br><span style="font-size:9px;color:#6b7280;">{{ $p->check_out_at->format('d/m') }}</span>
                    @else
                        <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($p->durasi_hadir)
                        <strong style="color:#15803d">{{ $p->durasi_hadir }}</strong>
                    @else
                        <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($p->nilai !== null)
                        <strong>{{ number_format($p->nilai, 0) }}</strong>
                    @else
                        <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                <td class="center" style="font-size:9px; font-family:monospace;">
                    {{ $p->nomor_sertifikat ?? '—' }}
                </td>
                <td class="ttd center"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Penandatangan ── --}}
    <div class="ttd-section">
        <div class="ttd-box">
            <div class="ttd-label">
                {{ $iht->tanggal_selesai->translatedFormat('d F Y') }},
                @if($iht->lokasi) {{ $iht->lokasi }} @endif
            </div>
            @if($iht->penandatangan_nama)
            <div class="ttd-nama">{{ $iht->penandatangan_nama }}</div>
            <div class="ttd-jabatan">{{ $iht->penandatangan_jabatan }}</div>
            @else
            <div class="ttd-nama">&nbsp;</div>
            <div class="ttd-jabatan">Penanggung Jawab</div>
            @endif
        </div>
    </div>

    {{-- ── Footer ── --}}
    <div class="print-footer">
        <span>HR Manajemen &mdash; Daftar Peserta IHT</span>
        <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
    </div>

    <script>
        // Auto-print saat halaman terbuka
        window.onload = function () {
            // Kecil delay agar font/gambar selesai load
            setTimeout(function () {
                window.print();
            }, 400);
        };
    </script>
</body>
</html>
