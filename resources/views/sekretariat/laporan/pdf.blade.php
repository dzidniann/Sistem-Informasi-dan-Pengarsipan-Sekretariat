<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #0f172a;
            background: #f8fafc;
            margin: 0;
            padding: 24px;
        }

        .report-shell {
            max-width: 900px;
            margin: 0 auto;
        }

        .report-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .report-header h2 {
            margin: 0 0 8px;
            font-size: 24px;
            letter-spacing: -0.02em;
        }

        .report-header p {
            margin: 0;
            color: #475569;
            font-size: 14px;
        }

        .summary-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 24px;
        }

        .section-title {
            margin: 0 0 14px;
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            text-align: left;
            font-size: 13px;
        }

        th {
            background: #f8fafc;
            color: #0f172a;
            font-weight: 700;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
    </style>
</head>
<body>
    <div class="report-shell">
        <div class="report-header">
            <h2>Laporan Bulanan — {{ $namaBulan }} {{ $tahun }}</h2>
            <p>Total arsip: {{ $totalArsip }}</p>
        </div>

        <div class="summary-card">
            <p class="section-title">Rincian per Bidang</p>
            @foreach($rekapPerBidang as $rekap)
                <h4 style="margin: 0 0 8px; font-size: 14px; color: #334155;">{{ $rekap['bidang']->nama_bidang }} — Total: {{ $rekap['total'] }} arsip</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap['perKategori'] as $namaKategori => $jumlah)
                            <tr>
                                <td>{{ $namaKategori }}</td>
                                <td>{{ $jumlah }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>

        <h3>Rekap Nomor Surat</h3>
        <p>Total dirilis: {{ $totalNomorSurat }} (Normal: {{ $totalNomorNormal }}, Sisipan/THB: {{ $totalNomorSisipan }})</p>

        <table>
            <tr><th>Kategori Surat</th><th>Jumlah</th></tr>
            @foreach($rekapPerKategoriSurat as $namaKategori => $jumlah)
                <tr><td>{{ $namaKategori }}</td><td>{{ $jumlah }}</td></tr>
            @endforeach
        </table>

        <h4>Daftar Nomor Surat</h4>
        <table>
            <tr><th>Nomor</th><th>Perihal</th><th>Kategori</th><th>Status</th></tr>
            @foreach($nomorSuratPeriode as $ns)
                <tr>
                    <td>{{ $ns->full_nomor }}</td>
                    <td>{{ $ns->perihal ?? '-' }}</td>
                    <td>{{ $ns->kategori->nama_kategori }}</td>
                    <td>{{ $ns->is_backdate ? 'Sisipan/THB' : 'Normal' }}</td>
                </tr>
            @endforeach
        </table>

        <div class="summary-card">
            <p class="section-title">Daftar Rapat</p>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Judul Rapat</th>
                        <th>Notulis</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($notulensi as $n)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($n->tanggal_rapat)->format('d M Y') }}</td>
                            <td>{{ $n->judul_rapat }}</td>
                            <td>{{ $n->notulis }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>