@extends('legal.layout')
@section('content')
    <div class="page-header">
        <h2 class="page-title">{{ $minutes ? $record->judul_rapat : $record->uraian_kegiatan }}</h2>
    </div>
    <div class="card">
        <p>Tanggal:
            {{ ($minutes ? $record->tanggal_rapat : $record->tanggal_kegiatan)->locale('id')->translatedFormat('d F Y') }}
        </p>
        @if ($minutes)
            <p>Notulis: {{ $record->notulis }}</p>
            <p>Kegiatan: <a
                    href="{{ route('legal.activities.show', $record->legal_activity_id) }}">{{ $record->activity->uraian_kegiatan }}</a>
            </p>
        @endif
        <p>Dokumen Hukum: {{ $record->document?->name ?? '-' }}</p>
        <p>Pembuat: {{ $record->creator?->name ?? '-' }}</p>
        <p style="white-space:pre-wrap;overflow-wrap:anywhere">
            {{ $minutes ? $record->deskripsi_singkat : $record->keterangan }}</p>
        @foreach ($fields as $field)
            @if ($record->$field)
                <p>{{ $record->{$field . '_name'} }} <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener"
                        href="{{ route($prefix . '.attachment', [$record->id, $field]) }}">Preview</a> <a
                        class="btn btn-primary btn-sm"
                        href="{{ route($prefix . '.attachment', [$record->id, $field, 'download' => 1]) }}">Unduh</a></p>
                @if (str_contains($field, 'foto'))
                    <img src="{{ route($prefix . '.attachment', [$record->id, $field]) }}" alt="Foto dokumentasi"
                        style="max-width:100%;max-height:280px">
                @endif
            @endif
        @endforeach
        @unless ($minutes)
            <h3>Notulensi Kegiatan</h3>
            @forelse($record->notulensi as $note)
                <p><a href="{{ route('legal.notulensi.show', $note->id) }}">{{ $note->judul_rapat }}</a> —
                {{ $note->notulis }}</p>@empty<p>Belum ada notulensi.</p>
            @endforelse
        @endunless
        <a class="btn btn-secondary" href="{{ route($prefix . '.index') }}">Kembali</a> <a class="btn btn-primary"
            href="{{ route($prefix . '.edit', $record->id) }}">Edit</a>
        <form style="display:inline" method="POST" action="{{ route($prefix . '.destroy', $record->id) }}"
            onsubmit="return confirm('Hapus data ini beserta lampirannya?')">@csrf @method('DELETE')<button
                class="btn btn-danger">Hapus</button></form>
    </div>
@endsection
