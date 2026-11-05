@extends('humas.layout')
@section('content')
    <div class="topbar">
        <div class="page-header">
            <h1 class="page-title">Riwayat Arsip CSR</h1>
            <p class="page-subtitle">Arsip CSR, sponsorship, iklan, dan kegiatan Humas sesuai kategori Admin.</p>
        </div><a class="btn btn-primary" href="{{ route('humas.csr.create') }}">+ Arsip CSR Baru</a>
    </div>
    <div class="card">
        <form class="humas-filters" method="GET" action="{{ route('humas.csr.index') }}">@include('humas.filters')</form>
    </div>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Daftar Arsip</h2><span class="card-meta">{{ $records->total() }} arsip ditemukan</span><a
                class="btn btn-secondary"
                href="{{ route('humas.reports.index', request()->only(['cari', 'tahun', 'bulan', 'status', 'kategori_id', 'luaran'])) }}">Rekap
                / Laporan</a>
        </div>
        <div class="table-wrapper">
            <table class="table humas-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Proses</th>
                        <th>Pemohon / Kegiatan</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $csr)
                        <tr>
                            <td>{{ $records->firstItem() + $loop->index }}</td>
                            <td>{{ $csr->tanggal_proses->format('d/m/Y') }}</td>
                            <td><strong>{{ $csr->pemohon }}</strong><br>{{ $csr->nama_kegiatan }}</td>
                            <td>{{ $csr->kategori->nama }}</td>
                            <td>@include('humas.status')</td>
                            <td>Rp{{ number_format($csr->nominal, 2, ',', '.') }}</td>
                            <td>
                                <div class="humas-actions"><a class="btn btn-secondary btn-sm"
                                        href="{{ route('humas.csr.show', $csr) }}">Detail</a><a
                                        class="btn btn-secondary btn-sm"
                                        href="{{ route('humas.csr.edit', $csr) }}">Edit</a></div>
                            </td>
                        </tr>
                    @empty<tr>
                            <td colspan="7" style="text-align:center">Tidak ada arsip yang sesuai pencarian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:20px">{{ $records->links('vendor.pagination.custom') }}</div>
    </div>
@endsection
