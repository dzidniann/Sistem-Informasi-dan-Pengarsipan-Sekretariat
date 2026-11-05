@extends('legal.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Sub Bidang Hukum dan Tata Kelola</p>
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                <h1 class="page-title">Dokumen Tata Kelola</h1>
                <a class="btn btn-primary" href="{{ route('legal.governance-documents.create') }}">+ Unggah Dokumen Baru</a>
            </div>
        </div>
    </div>

    <div class="card" style="padding:18px;">
        <form method="GET" action="{{ route('legal.governance-documents.index') }}" class="legal-filters">
            <div>
                <label class="form-label" for="cari">Cari Judul File</label>
                <input class="form-control" id="cari" name="cari" maxlength="255"
                    value="{{ request('cari') }}" placeholder="Judul dokumen...">
            </div>
            <div>
                <label class="form-label" for="tanggal">Tanggal</label>
                <input class="form-control" type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}">
            </div>
            <div style="display:flex; gap:8px; align-items:end;">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-secondary" href="{{ route('legal.governance-documents.index') }}">Reset</a>
            </div>
        </form>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Judul File</th>
                        <th>Tanggal</th>
                        <th>Berkas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>{{ $documents->firstItem() + $loop->index }}</td>
                            <td><strong>{{ $document->judul_file }}</strong></td>
                            <td>{{ $document->tanggal->format('d/m/Y') }}</td>
                            <td style="overflow-wrap:anywhere;">{{ $document->original_name }}</td>
                            <td>
                                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                                    <a class="btn btn-secondary btn-sm" href="{{ route('legal.governance-documents.show', $document) }}">Detail</a>
                                    <a class="btn btn-secondary btn-sm" href="{{ route('legal.governance-documents.edit', $document) }}">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center; padding:32px;">Tidak ada dokumen tata kelola yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $documents->links() }}
@endsection
