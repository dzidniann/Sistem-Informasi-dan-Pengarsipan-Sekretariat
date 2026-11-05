@extends('legal.layout')
@section('content')
    <div class="page-header">
        <h2 class="page-title">Laporan Kegiatan Bulanan</h2>
        <p class="page-subtitle">Sub Bidang Hukum PDAM — {{ $namaBulan }} {{ $tahun }}</p>
    </div>
    <form class="card" method="GET" action="{{ route('legal.reports.monthly') }}">
        @include('legal.records.filters', ['periodOnly' => true])
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">
            <button class="btn btn-secondary" formtarget="_blank"
                formaction="{{ route('legal.reports.monthly.preview') }}">Preview PDF</button>
            <button class="btn btn-primary" formaction="{{ route('legal.reports.monthly.pdf') }}">Download PDF</button>
            <a class="btn btn-secondary" href="{{ route('legal.activities.create') }}">Tambah Kegiatan</a>
        </div>
    </form>
    <div class="card">
        <p><strong>Total kegiatan: {{ $activities->count() }}</strong></p>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Uraian Kegiatan</th>
                        <th>Dokumen / Dokumentasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $activity->tanggal_kegiatan->format('d/m/Y') }}</td>
                            <td><a
                                    href="{{ route('legal.activities.show', $activity->id) }}">{{ $activity->uraian_kegiatan }}</a>
                            </td>
                            <td>@include('legal.reports.documentation', ['pdf' => false])</td>
                        </tr>
                    @empty<tr>
                            <td colspan="4">Tidak ada kegiatan pada periode ini</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
