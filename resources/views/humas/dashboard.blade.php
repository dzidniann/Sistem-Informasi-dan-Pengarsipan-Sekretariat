@extends('humas.layout')
@section('content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Humas - {{ auth()->user()->bidang->nama_bidang }}</p>
            <div class="humas-actions">
                <h1 class="page-title">Dashboard CSR Humas</h1><a class="btn btn-primary"
                    href="{{ route('humas.csr.create') }}">+ Arsip CSR Baru</a>
            </div>
        </div><span class="user-badge">{{ auth()->user()->name }}</span>
    </div>
    <div class="humas-grid">
        <div class="card">
            <p class="card-meta">Arsip Tahun {{ now()->year }}</p>
            <h2 style="font-size:28px;font-weight:700;margin:10px 0 0">{{ $total }}</h2>
        </div>
        <div class="card">
            <p class="card-meta">Permohonan Dibantu</p>
            <h2 style="font-size:28px;font-weight:700;margin:10px 0 0">{{ $dibantu }}</h2>
        </div>
        <div class="card">
            <p class="card-meta">Total Nominal Tahun {{ now()->year }}</p>
            <h2 style="font-size:28px;font-weight:700;margin:10px 0 0">Rp{{ number_format($nominal, 2, ',', '.') }}</h2>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Arsip Terbaru</h2><a class="btn btn-secondary"
                href="{{ route('humas.csr.index') }}">Lihat Riwayat</a>
        </div>
        <div class="table-wrapper">
            <table class="table humas-table">
                <thead>
                    <tr>
                        <th>Tanggal Proses</th>
                        <th>Pemohon / Kegiatan</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $csr)
                        <tr>
                            <td>{{ $csr->tanggal_proses->format('d/m/Y') }}</td>
                            <td><strong>{{ $csr->pemohon }}</strong><br>{{ $csr->nama_kegiatan }}</td>
                            <td>{{ $csr->kategori->nama }}</td>
                            <td>@include('humas.status')</td>
                            <td><a class="btn btn-secondary btn-sm" href="{{ route('humas.csr.show', $csr) }}">Detail</a>
                            </td>
                        </tr>
                    @empty<tr>
                            <td colspan="5" style="text-align:center">Belum ada arsip CSR tahun ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
