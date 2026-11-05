@extends('sekretariat.layout')

@section('content')
    <div class="page-header">
        <h2 class="page-title">Laporan Bulanan — {{ $namaBulan }} {{ $tahun }}</h2>
        <p class="page-subtitle">Ringkasan arsip dan notulensi untuk bulan yang dipilih.</p>
    </div>

    <div class="card">
        <p style="margin: 0 0 14px; font-weight: 600; color: #334155;">Total arsip: <span
                style="color: #0f172a;">{{ $totalArsip }}</span></p>

        <div class="table-card" style="margin-bottom: 24px;">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Bidang</th>
                            <th>Kategori</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rekapPerBidang as $rekap)
                            <tr>
                                <td>{{ $rekap['bidang']->nama_bidang }}</td>
                                <td colspan="2">Total: {{ $rekap['total'] }} arsip</td>
                            </tr>
                            @foreach ($rekap['perKategori'] as $namaKategori => $jumlah)
                                <tr>
                                    <td></td>
                                    <td>{{ $namaKategori }}</td>
                                    <td>{{ $jumlah }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <h3>Rekap Nomor Surat</h3>
        <p>
            Total nomor dirilis: <strong>{{ $totalNomorSurat }}</strong>
            &middot; Normal: <strong>{{ $totalNomorNormal }}</strong>
            &middot; Sisipan/THB: <strong>{{ $totalNomorSisipan }}</strong>
        </p>

        <table border="1" cellpadding="8">
            <tr>
                <th>Kategori Surat</th>
                <th>Jumlah Dirilis</th>
            </tr>
            @forelse($rekapPerKategoriSurat as $namaKategori => $jumlah)
                <tr>
                    <td>{{ $namaKategori }}</td>
                    <td>{{ $jumlah }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" style="text-align:center; color:#94a3b8;">Belum ada nomor surat dirilis bulan ini
                    </td>
                </tr>
            @endforelse
        </table>

        <h4 style="margin-top:16px;">Daftar Nomor Surat Dirilis</h4>
        <table border="1" cellpadding="8">
            <tr>
                <th>Nomor Surat</th>
                <th>Perihal</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Tanggal</th>
            </tr>
            @forelse($nomorSuratPeriode as $ns)
                <tr>
                    <td>{{ $ns->full_nomor }}</td>
                    <td>{{ $ns->perihal ?? '-' }}</td>
                    <td>{{ $ns->kategori->nama_kategori }}</td>
                    <td>{{ $ns->is_backdate ? 'Sisipan/THB' : 'Normal' }}</td>
                    <td>{{ $ns->created_at->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:#94a3b8;">Tidak ada data</td>
                </tr>
            @endforelse
        </table>

        <h3 class="page-title" style="font-size: 1rem; margin-bottom: 16px;">Daftar Rapat</h3>
        <div class="table-card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul Rapat</th>
                            <th>Notulis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notulensi as $n)
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

        <div style="margin-top: 18px; display: flex; gap: 10px;">
            <a href="{{ route('laporan.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                class="button button-primary">Ekspor PDF</a>
            <a href="{{ route('laporan.index') }}" class="button button-secondary">&larr; Kembali ke Pilih Periode</a>
        </div>
    </div>
@endsection
