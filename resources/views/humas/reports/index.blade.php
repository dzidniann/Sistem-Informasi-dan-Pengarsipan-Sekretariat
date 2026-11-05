@extends('humas.layout')
@section('content')
    <div class="topbar">
        <div class="page-header">
            <h1 class="page-title">Rekap CSR Humas</h1>
            <p class="page-subtitle">{{ $periode }} · Periode berdasarkan tanggal proses.</p>
        </div>
    </div>
    <div class="card">
        <form class="humas-filters" action="{{ route('humas.reports.index') }}" method="GET">@include('humas.filters')
        </form>
    </div>
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">{{ $records->count() }} Arsip · {{ $periode }}</h2>
                <p class="card-meta">Total nominal: Rp{{ number_format($records->sum('nominal'), 2, ',', '.') }}</p>
            </div>
            <div class="humas-actions"><a class="btn btn-primary" href="{{ route('humas.reports.excel', $filters) }}">Unduh
                    Excel</a><a class="btn btn-secondary" href="{{ route('humas.reports.pdf', $filters) }}">Unduh PDF</a>
            </div>
        </div>
        <p class="humas-help">Pilih bulan untuk satu sheet Excel, atau Semua bulan untuk 12 sheet Januari–Desember. Filter
            yang dipilih berlaku juga untuk ekspor.</p>
        <div class="table-wrapper">
            <table class="table humas-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pemohon / Kegiatan</th>
                        <th>Proses</th>
                        <th>Keluar</th>
                        <th>Status</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $csr)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $csr->pemohon }}</strong><br>{{ $csr->nama_kegiatan }}</td>
                            <td>{{ $csr->tanggal_proses->format('d/m/Y') }}</td>
                            <td>{{ $csr->keluar ?: '-' }}</td>
                            <td>@include('humas.status')</td>
                            <td>{{ $csr->kategori->nama }}</td>
                            <td>Rp{{ number_format($csr->nominal, 2, ',', '.') }}</td>
                            <td><a class="btn btn-secondary btn-sm" href="{{ route('humas.csr.show', $csr) }}">Detail</a>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="8" style="text-align:center">Tidak ada arsip pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
