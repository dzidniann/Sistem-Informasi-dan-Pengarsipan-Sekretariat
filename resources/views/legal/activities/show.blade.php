@extends('legal.layout')
@section('content')
    @include('legal.notulensi.styles')
    <div class="legal-minutes">
        <div class="page-header">
            <h2 class="page-title">{{ $record->uraian_kegiatan }}</h2>
            <p class="page-subtitle">Detail kegiatan lengkap dengan file dan dokumentasi.</p>
        </div>
        <div class="card" style="max-width:780px">
            <div
                style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;align-items:center">
                <a href="{{ route('legal.activities.index') }}#daftar-kegiatan" class="button button-secondary">←
                    Kembali</a><span style="color:#64748b">Dicatat pada
                    {{ $record->created_at->format('d M Y, H:i') }}</span></div>
            <div style="display:grid;gap:18px">
                <div>
                    <p style="margin:0 0 8px;color:#334155;font-weight:600">Tanggal Kegiatan</p>
                    <p style="margin:0 0 12px">{{ $record->tanggal_kegiatan->locale('id')->translatedFormat('d F Y') }}</p>
                    <p style="margin:0;color:#334155;font-weight:600">Dokumen Hukum</p>
                    <p style="margin:4px 0 0">{{ $record->document?->name ?? '-' }}</p>
                </div>
                @if ($record->foto_dokumentasi)
                    <img src="{{ route('legal.activities.attachment', [$record->id, 'foto_dokumentasi']) }}"
                        alt="Dokumentasi {{ $record->uraian_kegiatan }}"
                        style="width:100%;max-height:400px;object-fit:cover;border-radius:18px;border:1px solid #9ed8d0;box-shadow:0 14px 30px rgba(15,23,42,.08)">
                @endif
                @if ($record->keterangan)
                    <div style="background:#fbfffe;border:1px solid #b8d6f5;border-radius:18px;padding:18px">
                        <p style="margin:0;color:#334155;font-weight:600">Deskripsi Singkat / Hasil Kegiatan</p>
                        <p
                            style="margin:10px 0 0;line-height:1.7;color:#475569;white-space:pre-wrap;overflow-wrap:anywhere">
                            {{ $record->keterangan }}</p>
                    </div>
                @endif
            </div>
            <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap">
                @foreach ($fields as $field)
                    @if ($record->$field)
                        <a href="{{ route('legal.activities.attachment', [$record->id, $field]) }}" data-notulensi-preview
                            data-filename="{{ $record->{$field . '_name'} ?? basename($record->$field) }}" target="_blank"
                            rel="noopener"
                            class="button button-primary">{{ $field === 'dokumen_pendukung' ? 'Buka / Unduh Dokumen Pendukung' : 'Buka / Unduh Foto' }}</a>
                    @endif
                @endforeach
                <a class="button button-secondary" href="{{ route('legal.activities.edit', $record->id) }}">Edit</a>
                <form method="POST" action="{{ route('legal.activities.destroy', $record->id) }}"
                    onsubmit="return confirm('Hapus kegiatan ini beserta lampirannya?')">@csrf @method('DELETE')<button
                        class="button button-danger">Hapus</button></form>
            </div>
            <div style="margin-top:24px">
                <h3 style="font-size:1.1rem;font-weight:600;color:#334155">Notulensi Kegiatan</h3>
                @forelse($record->notulensi as $note)
                    <p><a href="{{ route('legal.notulensi.show', $note->id) }}"
                        class="button button-secondary">{{ $note->judul_rapat }}</a> — {{ $note->notulis }}</p>@empty
                    <p style="color:#64748b">Belum ada notulensi.</p>
                @endforelse
            </div>
        </div>
    </div>
    @include('legal.notulensi.preview')
@endsection
