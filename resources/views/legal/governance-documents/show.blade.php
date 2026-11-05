@extends('legal.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Dokumen Tata Kelola</p>
            <h1 class="page-title" style="overflow-wrap:anywhere;">{{ $document->judul_file }}</h1>
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
            <a class="btn btn-primary" href="{{ route('legal.governance-documents.edit', $document) }}">Edit Dokumen</a>
            <a class="btn btn-secondary" href="{{ route('legal.governance-documents.index') }}">Kembali</a>
        </div>
    </div>

    <div class="card">
        <div style="display:flex; flex-wrap:wrap; gap:24px; margin-bottom:18px;">
            <div><p class="card-meta">Tanggal</p><strong>{{ $document->tanggal->format('d/m/Y') }}</strong></div>
            <div style="min-width:0; overflow-wrap:anywhere;"><p class="card-meta">Berkas</p><strong>{{ $document->original_name }}</strong></div>
            <div><p class="card-meta">Diunggah Oleh</p><strong>{{ $document->creator->name }}</strong></div>
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px;">
            <a class="btn btn-secondary" href="{{ route('legal.governance-documents.file', $document) }}" target="_blank" rel="noopener">Lihat Berkas</a>
            <a class="btn btn-primary" href="{{ route('legal.governance-documents.file', [$document, 'download' => 1]) }}">Unduh PDF</a>
        </div>
        <iframe src="{{ route('legal.governance-documents.file', $document) }}"
            title="Preview {{ $document->judul_file }}" style="width:100%; height:65vh; min-height:360px; border:1px solid #e2e8f0; border-radius:12px;"></iframe>
    </div>

    <form method="POST" action="{{ route('legal.governance-documents.destroy', $document) }}"
        onsubmit="return confirm('Hapus dokumen tata kelola ini?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">Hapus Dokumen</button>
    </form>
@endsection
