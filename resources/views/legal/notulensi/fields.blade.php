@csrf
@if ($record)
@method('PUT')
@endif
<label class="form-label" for="judul-rapat">
    Judul Rapat</label>
<input id="judul-rapat" type="text" name="judul_rapat" required maxlength="255" class="form-control"
    value="{{ old('judul_rapat', $record?->judul_rapat) }}">
<label class="form-label" for="tanggal-rapat">Tanggal Rapat</label>
<input id="tanggal-rapat" type="date" name="tanggal_rapat" required class="form-control"
    value="{{ old('tanggal_rapat', $record?->tanggal_rapat?->format('Y-m-d')) }}">
<label class="form-label" for="notulis">Nama Notulis</label>
<input id="notulis" type="text" name="notulis" required maxlength="100" class="form-control"
    value="{{ old('notulis', $record?->notulis) }}">
<label class="form-label" for="deskripsi-singkat">Hasil & Pembahasan</label>
<textarea id="deskripsi-singkat" name="deskripsi_singkat" rows="3" maxlength="20000" class="form-control">{{ old('deskripsi_singkat', $record?->deskripsi_singkat) }}</textarea>
<label class="form-label" for="foto-notulensi">Foto Dokumentasi (opsional)</label>
<input id="foto-notulensi" type="file" name="foto_path" accept=".jpg,.jpeg,.png" class="form-control">
<small>JPG/JPEG/PNG, maksimal 4 MB. @if ($record?->foto_path)
    Saat ini: {{ $record->foto_path_name }}.
    @endif
</small>
<label class="form-label" for="file-notulensi">File Notulensi</label>
<input id="file-notulensi" type="file" name="file_path" accept=".pdf,.doc,.docx" class="form-control">
<small>PDF/DOC/DOCX, maksimal 8 MB. @if ($record?->file_path)
    Saat ini: {{ $record->file_path_name }}. Kosongkan untuk mempertahankan berkas.
    @endif
</small>
@php($activityId = old('new_activity') ? '' : old('legal_activity_id', $record?->legal_activity_id))
<details style="margin-top:20px" @if ($errors->has('legal_activity_id') || $errors->has('legal_document_id')) open @endif>
    <summary style="cursor:pointer;font-weight:600;color:#334155">Kegiatan & Dokumen Hukum Terkait (opsional)</summary>
    <p style="color:#64748b;margin-top:10px">Jika tidak memilih kegiatan, kegiatan dibuat otomatis dari judul dan
        tanggal rapat.</p>
    <input type="hidden" id="new-activity" name="new_activity" value="{{ $activityId ? '' : '1' }}">
    <label class="form-label" for="kegiatan-terkait">Kegiatan Terkait</label>
    <select id="kegiatan-terkait" name="legal_activity_id" class="form-control"
        onchange="document.getElementById('new-activity').value = this.value ? '' : '1'">
        <option value="">Buat atau gunakan kegiatan secara otomatis</option>
        @foreach ($activities as $activity)
        <option value="{{ $activity->id }}" @selected($activityId==$activity->id)>
            {{ $activity->tanggal_kegiatan->format('d/m/Y') }} — {{ $activity->uraian_kegiatan }}
        </option>
        @endforeach
    </select>
    <label class="form-label" for="dokumen-terkait">Dokumen Hukum Terkait</label>
    <select id="dokumen-terkait" name="legal_document_id" class="form-control">
        <option value="">Tanpa dokumen</option>
        @foreach ($documents as $document)
        <option value="{{ $document->id }}" @selected(old('legal_document_id', $record?->legal_document_id) == $document->id)>{{ $document->name }}</option>
        @endforeach
    </select>
</details>
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:16px">
    <button class="button button-primary">Simpan</button>
    @if ($record)
    <a href="{{ route('legal.notulensi.show', $record->id) }}" class="button button-secondary">Batal</a>
    @endif
</div>