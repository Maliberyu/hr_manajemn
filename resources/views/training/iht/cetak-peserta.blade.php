<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta — {{ $iht->nama_training }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
            padding: 20px 28px;
        }

        /* ─── Toolbar (screen only) ── */
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
        #qr-status {
            font-size: 11px;
            color: #6b7280;
            margin-left: auto;
        }

        /* ─── Kop ── */
        .kop {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1d4ed8;
        }
        .kop img       { height: 52px; width: auto; }
        .kop-text h1   { font-size: 14px; font-weight: 700; color: #1d4ed8; letter-spacing: 0.3px; }
        .kop-text p    { font-size: 10px; color: #6b7280; margin-top: 1px; }

        /* ─── Judul ── */
        .doc-title        { text-align: center; margin: 12px 0 10px; }
        .doc-title h2     { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-title p      { font-size: 10px; color: #6b7280; margin-top: 2px; }

        /* ─── Info grid ── */
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

        /* ─── Ringkasan ── */
        .summary         { display: flex; gap: 12px; margin-bottom: 12px; }
        .summary-box     { flex: 1; text-align: center; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; }
        .summary-box .num{ font-size: 20px; font-weight: 700; color: #1d4ed8; }
        .summary-box .lbl{ font-size: 9px; color: #6b7280; margin-top: 2px; text-transform: uppercase; }

        /* ─── Tabel ── */
        table        { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead th     {
            background: #1d4ed8;
            color: #fff;
            padding: 6px 6px;
            text-align: center;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        thead th.left { text-align: left; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: middle;
        }
        td.center { text-align: center; }

        .badge        { display: inline-block; padding: 1px 6px; border-radius: 20px; font-size: 9px; font-weight: 700; }
        .badge-hadir  { background: #dcfce7; color: #15803d; }
        .badge-selesai{ background: #dbeafe; color: #1d4ed8; }
        .badge-tidak  { background: #fee2e2; color: #b91c1c; }
        .badge-default{ background: #f1f5f9; color: #64748b; }

        /* ─── QR ── */
        .qr-cell      { text-align: center; padding: 3px !important; width: 76px; }
        .qr-cell img,
        .qr-cell canvas{ display: block; margin: 0 auto; }
        .qr-cell .qr-label { font-size: 7.5px; color: #94a3b8; margin-top: 2px; }

        /* ─── Penandatangan ── */
        .ttd-section  { display: flex; justify-content: flex-end; margin-top: 24px; }
        .ttd-box      { text-align: center; width: 200px; }
        .ttd-box .ttd-label  { font-size: 10px; margin-bottom: 4px; }
        .ttd-box .ttd-nama   { margin-top: 48px; border-top: 1px solid #374151; padding-top: 4px; font-size: 10px; font-weight: 700; }
        .ttd-box .ttd-jabatan{ font-size: 9px; color: #6b7280; }

        /* ─── Footer ── */
        .print-footer {
            margin-top: 16px;
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
            body         { padding: 8px 14px; }
            @page        { size: A4 landscape; margin: 8mm 10mm; }
        }
    </style>
</head>
<body>

    {{-- Toolbar --}}
    <div id="toolbar">
        <button id="btnCetak" onclick="window.print()" disabled>
            &#128438; Cetak / Simpan PDF
        </button>
        <a href="{{ route('training.iht.show', $iht) }}">← Kembali ke Detail IHT</a>
        <span id="qr-status">Memuat QR code...</span>
    </div>

    {{-- Kop --}}
    <div class="kop">
        @if($logoUrl)
        <img src="{{ $logoUrl }}" alt="Logo">
        @endif
        <div class="kop-text">
            <h1>Daftar Peserta In-House Training (IHT)</h1>
            <p>HR Manajemen &mdash; dicetak {{ now()->translatedFormat('d F Y, H:i') }}</p>
        </div>
    </div>

    {{-- Judul --}}
    <div class="doc-title">
        <h2>{{ $iht->nama_training }}</h2>
        <p>{{ $iht->penyelenggara }}{{ $iht->pemateri ? ' · Pemateri: ' . $iht->pemateri : '' }}</p>
    </div>

    {{-- Info Training --}}
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
                @else —
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

    {{-- Ringkasan --}}
    @php
        $totalHadir   = $peserta->whereIn('status', ['hadir','selesai'])->count();
        $totalTidak   = $peserta->where('status', 'tidak_hadir')->count();
        $totalPending = $peserta->where('status', 'terdaftar')->count();
        $nilaiRata    = $peserta->whereNotNull('nilai')->avg('nilai');
    @endphp
    <div class="summary">
        <div class="summary-box">
            <div class="num">{{ $peserta->count() }}</div>
            <div class="lbl">Total</div>
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

    {{-- Tabel Peserta --}}
    @if($peserta->isEmpty())
    <p style="text-align:center;color:#94a3b8;padding:20px 0">Belum ada peserta terdaftar.</p>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:24px">No</th>
                <th class="left">Nama Peserta</th>
                <th class="left">Jabatan / Unit</th>
                <th style="width:66px">Status</th>
                <th style="width:48px">Masuk</th>
                <th style="width:48px">Selesai</th>
                <th style="width:46px">Durasi</th>
                <th style="width:34px">Nilai</th>
                <th style="width:86px">No. Sertifikat</th>
                <th style="width:76px">Verifikasi QR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta as $i => $p)
            @php
                $badgeClass = match($p->status) {
                    'hadir'       => 'badge-hadir',
                    'selesai'     => 'badge-selesai',
                    'tidak_hadir' => 'badge-tidak',
                    default       => 'badge-default',
                };
            @endphp
            <tr>
                <td class="center">{{ $i + 1 }}</td>
                <td>
                    <strong>{{ $p->pegawai?->nama ?? '—' }}</strong>
                    <br><span style="font-size:8.5px;color:#6b7280">{{ $p->pegawai?->nik }}</span>
                </td>
                <td>
                    {{ $p->pegawai?->jbtn ?? '—' }}
                    @if($p->pegawai?->departemenRef)
                    <br><span style="font-size:8.5px;color:#6b7280">{{ $p->pegawai->departemenRef->nama }}</span>
                    @endif
                </td>
                <td class="center">
                    <span class="badge {{ $badgeClass }}">{{ \App\Models\IHTPeserta::STATUS[$p->status] ?? $p->status }}</span>
                </td>
                <td class="center">
                    @if($p->check_in_at)
                        <strong>{{ $p->check_in_at->format('H:i') }}</strong>
                        <br><span style="font-size:8.5px;color:#6b7280">{{ $p->check_in_at->format('d/m') }}</span>
                    @else
                        <span style="color:#d1d5db">—</span>
                    @endif
                </td>
                <td class="center">
                    @if($p->check_out_at)
                        <strong>{{ $p->check_out_at->format('H:i') }}</strong>
                        <br><span style="font-size:8.5px;color:#6b7280">{{ $p->check_out_at->format('d/m') }}</span>
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
                <td class="center" style="font-size:8.5px;font-family:monospace;color:#15803d;font-weight:700;">
                    {{ $p->nomor_sertifikat ?? '—' }}
                </td>
                {{-- QR Cell --}}
                <td class="qr-cell">
                    <div id="qr-{{ $p->id }}"
                         data-peserta-id="{{ $p->id }}"
                         data-verify-url="{{ route('training.iht.peserta.verify', [$iht->id, $p->id]) }}">
                    </div>
                    <div class="qr-label">Scan untuk verifikasi</div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Penandatangan --}}
    <div class="ttd-section">
        <div class="ttd-box">
            <div class="ttd-label">
                {{ $iht->tanggal_selesai->translatedFormat('d F Y') }},
                {{ $iht->lokasi }}
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

    {{-- Footer --}}
    <div class="print-footer">
        <span>HR Manajemen &mdash; Daftar Peserta IHT</span>
        <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }}</span>
    </div>

    <script>
    (function () {
        // Kumpulkan semua sel QR dari data-attribute
        const cells = document.querySelectorAll('[data-verify-url]');
        let done = 0;

        if (cells.length === 0) {
            // Tidak ada peserta, langsung print
            triggerPrint();
            return;
        }

        cells.forEach(function (el) {
            const url = el.getAttribute('data-verify-url');
            try {
                new QRCode(el, {
                    text:         url,
                    width:        66,
                    height:       66,
                    colorDark:    '#1d4ed8',
                    colorLight:   '#ffffff',
                    correctLevel: QRCode.CorrectLevel.M,
                });
            } catch (e) {
                // QR gagal — isi teks URL pendek
                el.style.fontSize = '7px';
                el.style.wordBreak = 'break-all';
                el.textContent = url;
            }
            done++;
            if (done === cells.length) {
                document.getElementById('qr-status').textContent = cells.length + ' QR berhasil dimuat.';
                document.getElementById('btnCetak').disabled = false;
                // Auto-print setelah semua QR selesai
                setTimeout(triggerPrint, 500);
            }
        });

        function triggerPrint() {
            document.getElementById('qr-status').textContent = 'Siap cetak.';
            if (document.getElementById('btnCetak')) {
                document.getElementById('btnCetak').disabled = false;
            }
            setTimeout(function () { window.print(); }, 300);
        }
    })();
    </script>
</body>
</html>
