@extends('humas.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <h1 class="page-title">Preview Lampiran</h1>
            <p class="page-subtitle">{{ $attachment->original_name }}</p>
        </div>
        <div class="humas-actions">
            <a class="btn btn-secondary" href="{{ route('humas.csr.show', $csr) }}">Kembali ke Arsip</a>
            <a class="btn btn-primary"
                href="{{ route('humas.csr.attachment', [$csr, $attachment, 'download' => 1]) }}">Unduh Berkas</a>
        </div>
    </div>

    <div class="card">
        @if ($paragraphs !== null && count($paragraphs) > 0)
            <p class="humas-help">Preview teks dokumen Word. Tata letak, tabel, dan gambar dapat berbeda dari berkas asli.</p>
            @foreach ($paragraphs as $paragraph)
                <p style="white-space:pre-wrap; overflow-wrap:anywhere;">{{ $paragraph }}</p>
            @endforeach
        @else
            <p>Preview isi berkas ini belum tersedia. Gunakan berkas PDF atau DOCX untuk preview, atau unduh berkas asli.</p>
        @endif
    </div>
@endsection
