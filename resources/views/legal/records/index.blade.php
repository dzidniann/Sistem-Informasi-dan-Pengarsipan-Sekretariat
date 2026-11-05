@extends('legal.layout')
@section('content')
    <div class="page-header">
        <h2 class="page-title">{{ $minutes ? 'Notulensi Legal' : 'Kegiatan Bulanan Legal' }}</h2>
        <p class="page-subtitle">Riwayat kegiatan Sub Bidang Hukum PDAM</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px">
        <a class="btn btn-primary"
            href="{{ route($prefix . '.create') }}">{{ $minutes ? 'Tambah Notulensi' : 'Tambah Kegiatan' }}</a>
        <a class="btn btn-secondary" href="{{ route('legal.reports.monthly') }}">Laporan Kegiatan Bulanan</a>
        <a class="btn btn-secondary"
            href="{{ route($minutes ? 'legal.activities.index' : 'legal.notulensi.index') }}">{{ $minutes ? 'Daftar Kegiatan' : 'Notulensi Legal' }}</a>
    </div>
    <form class="card" method="GET">@include('legal.records.filters')</form>
    <div class="card">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Judul</th>
                        <th>{{ $minutes ? 'Notulis' : 'Dokumen Hukum' }}</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ ($minutes ? $record->tanggal_rapat : $record->tanggal_kegiatan)->format('d/m/Y') }}</td>
                            <td><a
                                    href="{{ route($prefix . '.show', $record->id) }}">{{ $minutes ? $record->judul_rapat : $record->uraian_kegiatan }}</a>
                            </td>
                            <td>{{ $minutes ? $record->notulis : $record->document?->name ?? '-' }}</td>
                            <td><a class="btn btn-secondary btn-sm"
                                    href="{{ route($prefix . '.edit', $record->id) }}">Edit</a>
                                <form style="display:inline" method="POST"
                                    action="{{ route($prefix . '.destroy', $record->id) }}"
                                    onsubmit="return confirm('Hapus data ini beserta lampirannya?')">@csrf
                                    @method('DELETE')<button class="btn btn-danger btn-sm">Hapus</button></form>
                            </td>
                        </tr>
                    @empty<tr>
                            <td colspan="4">Belum ada data sesuai pencarian atau periode.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>{{ $records->links() }}
    </div>
@endsection
