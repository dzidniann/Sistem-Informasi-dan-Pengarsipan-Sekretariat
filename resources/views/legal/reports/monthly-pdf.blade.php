<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <title>Laporan Kegiatan Legal</title>
        <style>
            @page {
                margin: 30px 34px 48px;
            }

            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 10px;
                color: #172033;
            }

            header {
                text-align: center;
                border-bottom: 2px solid #243955;
                padding-bottom: 14px;
                margin-bottom: 16px;
            }

            h1 {
                font-size: 17px;
                margin: 4px 0;
            }

            h2 {
                font-size: 13px;
                margin: 4px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }

            th,
            td {
                border: 1px solid #8a98aa;
                padding: 7px;
                vertical-align: top;
                overflow-wrap: break-word;
                word-wrap: break-word;
            }

            th {
                background: #e9eef5;
                text-align: left;
            }

            td div {
                margin-bottom: 3px;
            }

            .photos {
                margin-top: 18px;
            }

            .photo {
                display: inline-block;
                margin: 6px;
                page-break-inside: avoid;
            }

            .photo img {
                max-width: 140px;
                max-height: 95px;
            }

            .signature {
                margin: 24px 0 0 auto;
                width: 240px;
                text-align: center;
                page-break-inside: avoid;
            }

            footer {
                position: fixed;
                bottom: -28px;
                font-size: 9px;
                color: #526174;
            }
        </style>
    </head>

    <body>
        <footer>Dibuat pada {{ now()->locale('id')->translatedFormat('d F Y H:i') }}</footer>
        <header>
            @if (is_file(public_path('images/logo-legal-report.jpg')))
                <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('images/logo-legal-report.jpg'))) }}"
                    style="max-height:44px;max-width:150px" alt="Logo PDAM">
            @endif
            <h1>LAPORAN KEGIATAN BULANAN</h1>
            <h2>SUB BIDANG HUKUM PDAM</h2>
            <div>PERIODE: {{ mb_strtoupper($namaBulan) }} {{ $tahun }}</div>
        </header>
        <p><strong>Total kegiatan: {{ $activities->count() }}</strong></p>
        <table>
            <thead>
                <tr>
                    <th style="width:4%">No</th>
                    <th style="width:12%">Tanggal</th>
                    <th style="width:37%">Uraian Kegiatan</th>
                    <th>Dokumen / Dokumentasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $activity->tanggal_kegiatan->format('d/m/Y') }}</td>
                        <td>{{ $activity->uraian_kegiatan }}</td>
                        <td>@include('legal.reports.documentation', ['pdf' => true])</td>
                    </tr>
                @empty<tr>
                        <td colspan="4" style="text-align:center">Tidak ada kegiatan pada periode ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{-- Documentation comes exclusively from the monthly activity record. --}}
        @foreach ($activities as $activity)
            @if ($activity->foto_dokumentasi)
                @if ($image = \App\Services\LegalPrivateFiles::image($activity->foto_dokumentasi))
                    <div class="photos"><strong>Dokumentasi: {{ $activity->uraian_kegiatan }}</strong><br>
                        <div class="photo"><img src="{{ $image }}" alt="Dokumentasi kegiatan"></div>
                    </div>
                @endif
            @endif
        @endforeach
        @if (config('legal.signatory_name'))
            <div class="signature">
                <p>{{ config('legal.signatory_title') }}</p><br><br><br>
                <p>{{ config('legal.signatory_name') }}</p>
            </div>
        @endif
    </body>

</html>
