<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <title>Rekap CSR Humas</title>
        <style>
            @page {
                margin: 30px 26px 40px;
            }

            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 9px;
                color: #172033;
            }

            h1 {
                font-size: 18px;
                margin: 0 0 8px;
            }

            h2 {
                font-size: 13px;
                margin: 0 0 12px;
            }

            .header {
                text-align: center;
                margin-bottom: 16px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: auto;
            }

            th,
            td {
                border: 1px solid #94a3b8;
                padding: 6px;
                vertical-align: top;
                overflow-wrap: break-word;
                word-wrap: break-word;
            }

            th {
                background: #e2e8f0;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }

            .month+.month {
                page-break-before: always;
            }

            footer {
                position: fixed;
                bottom: -24px;
                color: #64748b;
                font-size: 8px;
            }
        </style>
    </head>

    <body>
        <footer>Dibuat pada {{ now()->locale('id')->translatedFormat('d F Y H:i') }} WITA</footer>
        @foreach ($bulan ? [$bulan] : range(1, 12) as $month)
            @php($rows = $records->filter(fn($row) => $row->tanggal_proses->month === $month)->values())
            <div class="month">
                <div class="header">
                    <h1>REKAP CSR HUMAS</h1>
                    <h2>{{ mb_strtoupper(\Carbon\Carbon::create($tahun, $month, 1)->locale('id')->translatedFormat('F Y')) }}
                    </h2><span>{{ $rows->count() }} arsip · Total nominal
                        Rp{{ number_format($rows->sum('nominal'), 2, ',', '.') }}</span>
                </div>
                <table>
                    <colgroup>
                        <col style="width:3%">
                        <col style="width:11%">
                        <col style="width:9%">
                        <col style="width:15%">
                        <col style="width:7%">
                        <col style="width:12%">
                        <col style="width:7%">
                        <col style="width:7%">
                        <col style="width:8%">
                        <col style="width:6%">
                        <col style="width:8%">
                        <col style="width:7%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th rowspan="2" style="width:3%">NO</th>
                            <th colspan="3">INFORMASI PEMOHON</th>
                            <th colspan="8">INFORMASI PERMOHONAN</th>
                        </tr>
                        <tr>
                            @foreach (['PEMOHON', 'NO SURAT', 'NAMA KEGIATAN', 'PROSES', 'KELUAR', 'NO VOUCHER', 'STATUS', 'KATEGORI', 'LUARAN', 'NOMINAL', 'KETERANGAN'] as $label)
                                <th width="{{ [11, 9, 15, 7, 12, 7, 7, 8, 6, 8, 7][$loop->index] }}%">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $csr)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $csr->pemohon }}</td>
                                <td>{{ $csr->nomor_surat ?: '-' }}</td>
                                <td>{{ $csr->nama_kegiatan }}</td>
                                <td>{{ $csr->tanggal_proses->format('d/m/Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($csr->keluar ?: '-', 180) }}</td>
                                <td>{{ $csr->nomor_voucher ?: '-' }}</td>
                                <td>{{ $csr->status_label }}</td>
                                <td>{{ $csr->kategori->nama }}</td>
                                <td>{{ $csr->luaran_label }}</td>
                                <td>Rp{{ number_format($csr->nominal, 2, ',', '.') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($csr->keterangan ?: '-', 180) }}</td>
                        </tr>@empty<tr>
                                <td colspan="12" style="text-align:center">Tidak ada arsip pada periode ini.</td>
                            </tr>
                        @endforelse
                        <tr>
                            <th colspan="10" style="text-align:right">TOTAL NOMINAL</th>
                            <th colspan="2">Rp{{ number_format($rows->sum('nominal'), 2, ',', '.') }}</th>
                        </tr>
                    </tbody>
                </table>
                @php($longRows = $rows->filter(fn($row) => mb_strlen($row->keluar ?? '') > 180 || mb_strlen($row->keterangan ?? '') > 180))
                @if ($longRows->isNotEmpty())
                    <p>Keluar/keterangan yang panjang diringkas pada tabel. Isi lengkap tercantum pada rincian berikut.
                    </p>
                    @foreach ($longRows as $csr)
                        <div style="margin-top:16px;word-wrap:break-word;">
                            <h3>Rincian: {{ $csr->pemohon }} — {{ $csr->nama_kegiatan }}</h3><strong>Keluar / Tindak
                                Lanjut</strong>
                            <p style="white-space:pre-line;">{{ $csr->keluar ?: '-' }}</p><strong>Keterangan</strong>
                            <p style="white-space:pre-line;">{{ $csr->keterangan ?: '-' }}</p>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    </body>

</html>
