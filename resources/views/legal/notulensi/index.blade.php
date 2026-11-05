@extends('legal.layout')
@section('content')
    @include('legal.notulensi.styles')
    <div class="legal-minutes">
        <div class="page-header">
            <h2 class="page-title">Notulensi</h2>
            <p class="page-subtitle">Tambah notulensi rapat dan akses riwayat dengan gaya profesional.</p>
        </div>
        <div class="card" id="tambah-notulensi">
            <h3 style="margin:0 0 18px;font-size:1.1rem;color:#0f172a">Tambah Notulensi</h3>
            <form method="POST" action="{{ route('legal.notulensi.store') }}" enctype="multipart/form-data">
                @include('legal.notulensi.fields')</form>
        </div>
        <div class="card" id="daftar-notulensi">
            <div class="topbar" style="align-items:flex-end;gap:16px">
                <div>
                    <h3 class="page-title" style="font-size:1.1rem;margin:0">Riwayat Notulensi</h3>
                    <p class="page-subtitle" style="margin-top:6px">Semua notulensi rapat tersedia dalam daftar berikut.</p>
                </div>
                <form method="GET" action="{{ route('legal.notulensi.index') }}#daftar-notulensi" class="search-group">
                    <input type="text" name="cari" placeholder="Cari notulensi..." value="{{ request('cari') }}"
                        class="form-control">
                    <button class="button button-primary">Cari</button>
                    <details style="width:100%" @if (request('bulan') || request('tahun')) open @endif>
                        <summary style="cursor:pointer">Filter Bulan dan Tahun</summary>
                        <div style="margin-top:12px">@include('legal.records.filters', ['hideSearch' => true])</div>
                    </details>
                </form>
            </div>
            <div class="table-card">
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:38%">Judul Rapat</th>
                                <th style="text-align:center;width:20%">Tanggal</th>
                                <th style="width:27%">Notulis</th>
                                <th style="text-align:center;width:15%">Berkas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $n)
                                <tr>
                                    <td><a class="button button-secondary"
                                            href="{{ route('legal.notulensi.show', $n->id) }}">{{ $n->judul_rapat }}</a>
                                    </td>
                                    <td style="text-align:center">{{ $n->tanggal_rapat->format('d M Y') }}</td>
                                    <td>{{ $n->notulis }}</td>
                                    <td style="text-align:center">
                                        @if ($n->file_path)
                                            <a href="{{ route('legal.notulensi.attachment', [$n->id, 'file_path']) }}"
                                                target="_blank" rel="noopener" data-notulensi-preview
                                                data-filename="{{ $n->file_path_name ?? basename($n->file_path) }}"
                                                title="Lihat Berkas" aria-label="Preview berkas {{ $n->judul_rapat }}"
                                                style="display:inline-flex;color:#475569">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>
                                        @else<span style="color:#94a3b8;font-size:.9rem">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty<tr>
                                    <td colspan="4" style="text-align:center;color:#64748b;padding:20px">Tidak ada
                                        notulensi yang ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;justify-content:center">
                {{ $records->fragment('daftar-notulensi')->links('vendor.pagination.custom') }}</div>
        </div>
    </div>
    @include('legal.notulensi.preview')
@endsection
