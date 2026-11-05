@extends('sekretariat.layout')

@section('content')
    <div class="page-header">
        <h2 class="page-title">{{ $notulensi->judul_rapat }}</h2>
        <p class="page-subtitle">Detail notulensi rapat lengkap dengan file dan dokumentasi.</p>
    </div>

    <div class="card" style="max-width: 780px;">
        <div
            style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 22px; align-items: center;">
            <a href="{{ route('notulensi.index') }}" class="button button-secondary">&larr; Kembali</a>
            <span style="color:#64748b;">Diunggah pada {{ $notulensi->created_at->format('d M Y, H:i') }} WIB</span>
        </div>

        <div style="display: grid; gap: 18px;">
            <div>
                <p style="margin: 0 0 8px; color: #334155; font-weight: 600;">Tanggal Rapat</p>
                <p style="margin: 0 0 12px;">
                    {{ \Carbon\Carbon::parse($notulensi->tanggal_rapat)->translatedFormat('d F Y') }}</p>
                <p style="margin: 0; color: #334155; font-weight: 600;">Notulis</p>
                <p style="margin: 4px 0 0;">{{ $notulensi->notulis }}</p>
            </div>

            @if ($notulensi->foto_path)
                <img src="{{ Storage::url($notulensi->foto_path) }}" alt="Dokumentasi {{ $notulensi->judul_rapat }}"
                    style="width: 100%; max-height: 400px; object-fit: cover; border-radius: 18px; border: 1px solid #9ed8d0; box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);">
            @endif

            @if ($notulensi->deskripsi_singkat)
                <div style="background: #fbfffe; border: 1px solid #b8d6f5; border-radius: 18px; padding: 18px;">
                    <p style="margin: 0; color: #334155; font-weight: 600;">Deskripsi Singkat</p>
                    <p style="margin: 10px 0 0; line-height: 1.7; color: #475569;">{{ $notulensi->deskripsi_singkat }}</p>
                </div>
            @endif
        </div>

        <div style="margin-top: 24px;">
            <a href="{{ Storage::url($notulensi->file_path) }}" target="_blank" class="button button-primary">Buka / Unduh
                File Notulensi</a>
        </div>
    </div>
@endsection
