@csrf

@if ($record)
    @method('PUT')
@endif

<label class="form-label" for="uraian-kegiatan">
    Uraian Kegiatan / Judul Pembahasan</label>
<input id="uraian-kegiatan" type="text" name="uraian_kegiatan" required maxlength="255" class="form-control"
    value="{{ old('uraian_kegiatan', $record?->uraian_kegiatan) }}">

<label class="form-label" for="tanggal-kegiatan">Tanggal Kegiatan</label>
<input id="tanggal-kegiatan" type="date" name="tanggal_kegiatan" required class="form-control"
    value="{{ old('tanggal_kegiatan', $record?->tanggal_kegiatan?->format('Y-m-d')) }}">

<label class="form-label" for="keterangan">Deskripsi Singkat / Hasil Kegiatan</label>
<textarea id="keterangan" name="keterangan" rows="3" maxlength="20000" class="form-control">{{ old('keterangan', $record?->keterangan) }}</textarea>

<label class="form-label" for="foto-kegiatan">Foto Dokumentasi (opsional)</label>
<input id="foto-kegiatan" type="file" name="foto_dokumentasi" accept=".jpg,.jpeg,.png" class="form-control">
<small>JPG/JPEG/PNG, maksimal 4 MB. @if ($record?->foto_dokumentasi)
        Saat ini: {{ $record->foto_dokumentasi_name }}.
    @endif
</small>
<label class="form-label" for="dokumen-pendukung">Dokumen Pendukung (opsional)</label>
<input id="dokumen-pendukung" type="file" name="dokumen_pendukung" accept=".pdf,.doc,.docx" class="form-control">
<small>PDF/DOC/DOCX, maksimal 8 MB. @if ($record?->dokumen_pendukung)
        Saat ini: {{ $record->dokumen_pendukung_name }}. Kosongkan untuk mempertahankan berkas.
    @endif
</small>

<details style="margin-top:20px" @if (old('legal_document_id', $record?->legal_document_id) || $errors->has('legal_document_id')) open @endif>
    <summary style="cursor:pointer;font-weight:600;color:#334155">Dokumen Hukum Terkait (opsional)</summary>

    <label class="form-label" for="dokumen-terkait">Dokumen Hukum</label>
    <select id="dokumen-terkait" name="legal_document_id" class="form-control">
        <option value="">Tanpa dokumen</option>
        @foreach ($documents as $document)
            <option value="{{ $document->id }}" @selected(old('legal_document_id', $record?->legal_document_id) == $document->id)>
                {{ $document->name }}
            </option>
        @endforeach
    </select>

    <button type="button" class="button button-secondary" style="margin-top:12px"
        onclick="const s=document.getElementById('dokumen-terkait');if(s.value)document.getElementById('uraian-kegiatan').value=('Pembahasan '+s.options[s.selectedIndex].text).slice(0,255)">
        Gunakan Nama Dokumen sebagai Judul
    </button>
</details>

<div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:16px">
    <button class="button button-primary">Simpan</button>
    @if ($record)
        <a href="{{ route('legal.activities.show', $record->id) }}" class="button button-secondary">Batal</a>
    @endif
</div>
