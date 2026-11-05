@extends('legal.layout')
@section('content')
    @include('legal.notulensi.styles')
    <div class="legal-minutes">
        <div class="page-header">
            <h2 class="page-title">{{ $record->judul_rapat }}</h2>
            <p class="page-subtitle">Detail notulensi rapat lengkap dengan file dan dokumentasi.</p>
        </div>
        <div class="card" style="max-width:780px">
            <div
                style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;align-items:center">
                <a href="{{ route('legal.notulensi.index') }}#daftar-notulensi" class="button button-secondary">←
                    Kembali</a><span style="color:#64748b">Diunggah pada
                    {{ $record->created_at->format('d M Y, H:i') }}</span>
            </div>
            <div style="display:grid;gap:18px">
                <div>
                    <p style="margin:0 0 8px;color:#334155;font-weight:600">Tanggal Rapat</p>
                    <p style="margin:0 0 12px">{{ $record->tanggal_rapat->locale('id')->translatedFormat('d F Y') }}</p>
                    <p style="margin:0;color:#334155;font-weight:600">Notulis</p>
                    <p style="margin:4px 0 0">{{ $record->notulis }}</p>
                </div>
                @if ($record->foto_path)
                    <img src="{{ route('legal.notulensi.attachment', [$record->id, 'foto_path']) }}"
                        alt="Dokumentasi {{ $record->judul_rapat }}"
                        style="width:100%;max-height:400px;object-fit:cover;border-radius:18px;border:1px solid #9ed8d0;box-shadow:0 14px 30px rgba(15,23,42,.08)">
                @endif
                @if ($record->deskripsi_singkat)
                    <div style="background:#fbfffe;border:1px solid #b8d6f5;border-radius:18px;padding:18px">
                        <p style="margin:0;color:#334155;font-weight:600">Deskripsi Singkat</p>
                        <p
                            style="margin:10px 0 0;line-height:1.7;color:#475569;white-space:pre-wrap;overflow-wrap:anywhere">
                            {{ $record->deskripsi_singkat }}</p>
                    </div>
                @endif
            </div>
            <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap">
                @if ($record->file_path)
                    <a href="{{ route('legal.notulensi.attachment', [$record->id, 'file_path']) }}" data-notulensi-preview
                        data-filename="{{ $record->file_path_name ?? basename($record->file_path) }}" target="_blank"
                        rel="noopener" class="button button-primary">Buka / Unduh File Notulensi</a>
                @endif
                <a class="button button-secondary" href="{{ route('legal.notulensi.edit', $record->id) }}">Edit</a>
                <form method="POST" action="{{ route('legal.notulensi.destroy', $record->id) }}"
                    onsubmit="return confirm('Hapus notulensi ini beserta lampirannya?')">@csrf @method('DELETE')<button
                        class="button button-danger">Hapus</button></form>
            </div>
            <details style="margin-top:20px">
                <summary style="cursor:pointer;color:#64748b">Kegiatan & Dokumen Hukum Terkait</summary>
                <p>Kegiatan: <a
                        href="{{ route('legal.activities.show', $record->legal_activity_id) }}">{{ $record->activity->uraian_kegiatan }}</a>
                </p>
                <p>Dokumen Hukum: {{ $record->document?->name ?? '-' }}</p>
            </details>
        </div>
    </div>
    @include('legal.notulensi.preview')
@endsection
