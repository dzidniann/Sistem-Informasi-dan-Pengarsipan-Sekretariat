@extends('legal.layout')
@section('content')
    <div class="page-header">
        <h2 class="page-title">{{ $record ? 'Edit' : 'Tambah' }} {{ $minutes ? 'Notulensi Legal' : 'Kegiatan Legal' }}</h2>
    </div>
    <form class="card" method="POST" enctype="multipart/form-data"
        action="{{ $record ? route($prefix . '.update', $record->id) : route($prefix . '.store') }}">
        @csrf @if ($record)
            @method('PUT')
        @endif
        @php($titleField = $minutes ? 'judul_rapat' : 'uraian_kegiatan')
        @php($dateField = $minutes ? 'tanggal_rapat' : 'tanggal_kegiatan')
        <div class="form-group"><label class="form-label"
                for="activity-title">{{ $minutes ? 'Judul Rapat / Pembahasan Dokumen' : 'Uraian Kegiatan (Judul Pembahasan)' }}
                *</label><input id="activity-title" class="form-control" name="{{ $titleField }}"
                value="{{ old($titleField, $record?->$titleField) }}" required maxlength="255"></div>
        <div class="form-group"><label class="form-label">Tanggal Kegiatan / Rapat *<input class="form-control"
                    type="date" name="{{ $dateField }}" required
                    value="{{ old($dateField, $record?->$dateField?->format('Y-m-d')) }}"></label></div>
        @if ($minutes)
            <div class="form-group"><label class="form-label">Nama Notulis *<input class="form-control" name="notulis"
                        maxlength="100" required value="{{ old('notulis', $record?->notulis) }}"></label></div>
            <div class="form-group"><label class="form-label">Kegiatan Terkait
                    <select class="form-control" name="legal_activity_id">
                        <option value="">Pilih kegiatan yang sudah ada</option>
                        @foreach ($activities as $activity)
                            <option value="{{ $activity->id }}" @selected(old('legal_activity_id', $record?->legal_activity_id) == $activity->id)>
                                {{ $activity->tanggal_kegiatan->format('d/m/Y') }} — {{ $activity->uraian_kegiatan }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label><input type="checkbox" name="new_activity" value="1" @checked(old('new_activity'))> Buat atau
                    gunakan kegiatan dengan judul, tanggal, dan dokumen yang sama secara otomatis</label>
            </div>
        @endif
        <div class="form-group"><label class="form-label">Dokumen Hukum Terkait
                <select id="legal-document" class="form-control" name="legal_document_id">
                    <option value="">Tanpa dokumen</option>
                    @foreach ($documents as $document)
                        <option value="{{ $document->id }}" @selected(old('legal_document_id', $record?->legal_document_id) == $document->id)>{{ $document->name }}</option>
                    @endforeach
                </select>
            </label>
            <button type="button" class="btn btn-secondary btn-sm"
                onclick="const s=document.getElementById('legal-document');if(s.value)document.getElementById('activity-title').value=('Pembahasan '+s.options[s.selectedIndex].text).slice(0,255)">Gunakan
                Nama Dokumen sebagai Judul</button>
        </div>
        @php($description = $minutes ? 'deskripsi_singkat' : 'keterangan')
        <div class="form-group"><label class="form-label">Deskripsi / Hasil Pembahasan
                <textarea class="form-control" name="{{ $description }}" maxlength="20000">{{ old($description, $record?->$description) }}</textarea>
            </label></div>
        @foreach ($minutes ? ['file_path' => 'File Notulensi', 'foto_path' => 'Foto Dokumentasi'] : ['dokumen_pendukung' => 'Dokumen Pendukung', 'foto_dokumentasi' => 'Foto Dokumentasi'] as $field => $label)
            <div class="form-group"><label class="form-label">{{ $label }}<input class="form-control" type="file"
                        name="{{ $field }}"
                        accept="{{ str_contains($field, 'foto') ? '.jpg,.jpeg,.png' : '.pdf,.doc,.docx' }}"></label><small>{{ str_contains($field, 'foto') ? 'JPG/PNG, maksimal 4 MB dan 6000 × 6000 piksel.' : 'PDF/DOC/DOCX, maksimal 8 MB.' }}
                    @if ($record?->$field)
                        Lampiran saat ini: {{ $record->{$field . '_name'} }}. Kosongkan untuk mempertahankan.
                    @endif
                </small></div>
        @endforeach
        <button class="btn btn-primary">Simpan</button> <a class="btn btn-secondary"
            href="{{ route($prefix . '.index') }}">Batal</a>
    </form>
@endsection
