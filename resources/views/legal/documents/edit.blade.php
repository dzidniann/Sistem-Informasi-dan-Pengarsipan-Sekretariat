@extends('legal.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle"><a href="{{ route('legal.documents.show', $document) }}" style="color:#0f766e;">← Kembali
                    ke Detail Dokumen</a></p>
            <h1 class="page-title">Edit Dokumen Hukum</h1>
        </div>
    </div>

    <div class="card" style="max-width:850px;">
        <form method="POST" action="{{ route('legal.documents.update', $document) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap:18px;">
                <div class="form-group" style="grid-column:1 / -1;">
                    <label class="form-label">Nama Dokumen Hukum <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $document->name) }}"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori Dokumen <span style="color:#ef4444;">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $document->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color:#64748b;">Kategori diisi admin melalui Kategori Arsip → Sub Bidang Hukum.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Departemen / Bidang</label>
                    <input type="text" name="department" class="form-control"
                        value="{{ old('department', $document->department) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Penanggung Jawab (PIC)</label>
                    <input type="text" name="pic" class="form-control" value="{{ old('pic', $document->pic) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Mulai Berlaku</label>
                    <input type="date" name="effective_date" class="form-control"
                        value="{{ old('effective_date', $document->effective_date ? \Carbon\Carbon::parse($document->effective_date)->format('Y-m-d') : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Kadaluarsa</label>
                    <input type="date" name="expiry_date" class="form-control"
                        value="{{ old('expiry_date', $document->expiry_date ? \Carbon\Carbon::parse($document->expiry_date)->format('Y-m-d') : '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Jadwal Evaluasi / Review</label>
                    <input type="date" name="review_date" class="form-control"
                        value="{{ old('review_date', $document->review_date ? \Carbon\Carbon::parse($document->review_date)->format('Y-m-d') : '') }}">
                </div>

                <div class="form-group" style="grid-column:1 / -1;">
                    <label class="form-label">Perbarui Berkas Dokumen (Opsional)</label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                    <small style="color:#64748b; font-size:0.75rem;">
                        Format diizinkan: PDF, DOC, DOCX. Maksimal 8 MB.
                        Kosongkan jika tidak mengganti berkas fisik. Jika mengunggah berkas baru, versi dokumen saat ini
                        (<strong>v{{ $document->version }}</strong>) akan otomatis bertambah (misal ke
                        v{{ (float) $document->version + 0.1 }}).
                    </small>
                </div>

                <div class="form-group" style="grid-column:1 / -1;">
                    <label class="form-label">Deskripsi / Ringkasan Isi</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $document->description) }}</textarea>
                </div>
            </div>

            <div
                style="margin-top:24px; padding-top:18px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:12px;">
                <a href="{{ route('legal.documents.show', $document) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
