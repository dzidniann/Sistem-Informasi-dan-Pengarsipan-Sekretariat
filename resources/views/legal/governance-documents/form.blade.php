@extends('legal.layout')

@section('content')
    <style>
        .governance-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        @media (max-width: 640px) {
            .governance-form-grid {
                grid-template-columns: minmax(0, 1fr);
            }
        }
    </style>
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Sub Bidang Hukum dan Tata Kelola</p>
            <h1 class="page-title">{{ $document ? 'Edit Dokumen Tata Kelola' : 'Unggah Dokumen Tata Kelola' }}</h1>
        </div>
        <a class="btn btn-secondary" href="{{ route('legal.governance-documents.index') }}">Kembali</a>
    </div>

    <div class="card">
        <form method="POST" enctype="multipart/form-data"
            action="{{ $document ? route('legal.governance-documents.update', $document) : route('legal.governance-documents.store') }}">
            @csrf
            @if ($document)
                @method('PUT')
            @endif

            <div class="governance-form-grid">
                <div class="form-group">
                    <label class="form-label" for="judul_file">Judul File <span style="color:#ef4444;">*</span></label>
                    <input class="form-control" id="judul_file" name="judul_file" maxlength="255"
                        value="{{ old('judul_file', $document?->judul_file) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="tanggal">Tanggal <span style="color:#ef4444;">*</span></label>
                    <input class="form-control" type="date" id="tanggal" name="tanggal"
                        value="{{ old('tanggal', $document?->tanggal?->format('Y-m-d') ?? today()->toDateString()) }}" required>
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label class="form-label" for="file">{{ $document ? 'Ganti Berkas (Opsional)' : 'Upload File *' }}</label>
                    <input class="form-control" type="file" id="file" name="file" accept=".pdf" @required(!$document)>
                    <small style="color:#64748b;">Format diizinkan: PDF. Maksimal 5 MB.</small>
                    @if ($document)
                        <p class="card-meta">Berkas saat ini: {{ $document->original_name }}. Kosongkan jika tidak mengganti berkas.</p>
                    @endif
                </div>
            </div>

            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top:20px;">
                <button class="btn btn-primary" type="submit">Simpan Dokumen</button>
                <a class="btn btn-secondary" href="{{ route('legal.governance-documents.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection
