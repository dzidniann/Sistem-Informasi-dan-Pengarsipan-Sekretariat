@extends('legal.layout')
@section('content')
    @include('legal.notulensi.styles')
    <div class="legal-minutes">
        <div class="page-header">
            <h2 class="page-title">Kegiatan Bulanan</h2>
            <p class="page-subtitle">Tambah kegiatan dan akses riwayat kegiatan Sub Bidang Hukum.</p>
        </div>
        <div class="card" id="tambah-kegiatan">
            <h3 style="margin:0 0 18px;font-size:1.1rem;color:#0f172a">Tambah Kegiatan</h3>
            <form method="POST" action="{{ route('legal.activities.store') }}" enctype="multipart/form-data">
                @include('legal.activities.fields')</form>
        </div>
        <div class="card" id="daftar-kegiatan">
            <div class="topbar" style="align-items:flex-end;gap:16px">
                <div>
                    <h3 class="page-title" style="font-size:1.1rem;margin:0">Riwayat Kegiatan</h3>
                    <p class="page-subtitle" style="margin-top:6px">Semua kegiatan tersedia dalam daftar berikut.</p>
                </div>
                <a class="button button-secondary" href="{{ route('legal.reports.monthly') }}">Laporan Kegiatan Bulanan</a>
                <form method="GET" action="{{ route('legal.activities.index') }}#daftar-kegiatan" class="search-group">
                    <input type="text" name="cari" placeholder="Cari kegiatan..." value="{{ request('cari') }}"
                        class="form-control"><button class="button button-primary">Cari</button>
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
                                <th style="width:38%">Uraian Kegiatan</th>
                                <th style="text-align:center;width:20%">Tanggal</th>
                                <th style="width:27%">Dokumen Hukum</th>
                                <th style="text-align:center;width:15%">Berkas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $activity)
                                <tr>
                                    <td><a class="button button-secondary"
                                            href="{{ route('legal.activities.show', $activity->id) }}">{{ $activity->uraian_kegiatan }}</a>
                                    </td>
                                    <td style="text-align:center">{{ $activity->tanggal_kegiatan->format('d M Y') }}</td>
                                    <td>{{ $activity->document?->name ?? '-' }}</td>
                                    <td style="text-align:center">
                                        @if ($activity->dokumen_pendukung || $activity->foto_dokumentasi)
                                            @php($field = $activity->dokumen_pendukung ? 'dokumen_pendukung' : 'foto_dokumentasi')
                                            <a href="{{ route('legal.activities.attachment', [$activity->id, $field]) }}"
                                                data-notulensi-preview
                                                data-filename="{{ $activity->{$field . '_name'} ?? basename($activity->$field) }}"
                                                target="_blank" rel="noopener" title="Lihat Berkas"
                                                aria-label="Preview berkas {{ $activity->uraian_kegiatan }}"
                                                style="display:inline-flex;color:#475569"><svg width="22" height="22"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" aria-hidden="true">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg></a>
                                        @else<span style="color:#94a3b8;font-size:.9rem">Tidak ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty<tr>
                                    <td colspan="4" style="text-align:center;color:#64748b;padding:20px">Tidak ada
                                        kegiatan yang ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;justify-content:center">
                {{ $records->fragment('daftar-kegiatan')->links('vendor.pagination.custom') }}</div>
        </div>
    </div>
    @include('legal.notulensi.preview')
@endsection
