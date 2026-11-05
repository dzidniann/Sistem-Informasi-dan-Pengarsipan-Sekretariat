@extends('legal.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle"><a href="{{ route('legal.documents.index') }}" style="color:#6366f1;">← Kembali ke
                    Daftar</a></p>
            <h1 class="page-title">Unggah Dokumen Hukum Baru</h1>
        </div>
    </div>

    <div class="card" style="max-width:850px;">
        <form method="POST" action="{{ route('legal.documents.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 280px), 1fr)); gap:18px;">
                <div class="form-group" style="grid-column:1 / -1;">
                    <label class="form-label">Nama Dokumen Hukum <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="name" class="form-control"
                        placeholder="Contoh: Peraturan Direksi No. 12 Tahun 2026 tentang Kepegawaian"
                        value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Kategori Dokumen <span style="color:#ef4444;">*</span></label>
                    <select name="category_id" class="form-control" required>
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color:#64748b;">Kategori diisi oleh Admin</small>
                    @if ($categories->isEmpty())
                        <p role="status" style="color:#b45309;">Belum ada kategori dokumen Hukum. Hubungi admin untuk
                            menambahkan kategori terlebih dahulu.</p>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label">Departemen / Bidang Terkait <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="department" class="form-control"
                        placeholder="Contoh: Kepegawaian, Hubungan Langganan" value="{{ old('department') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Penanggung Jawab (PIC) <span style="color:#ef4444;">*</span></label>
                    <input type="text" name="pic" class="form-control" placeholder="Nama PIC Dokumen"
                        value="{{ old('pic') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Mulai Berlaku <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date') }}"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Kadaluarsa (Batas Akhir) <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}" required>
                    <small style="color:#64748b; font-size:0.75rem;">Wajib diisi dan tidak boleh sebelum tanggal mulai
                        berlaku</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Jadwal Evaluasi / Review Berkala <span style="color:#ef4444;">*</span></label>
                    <input type="date" name="review_date" class="form-control" value="{{ old('review_date') }}"
                        required>
                </div>

                <div class="form-group" style="grid-column:1 / -1;">
                    <label class="form-label">Unggah Berkas Dokumen <span style="color:#ef4444;">*</span></label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx" required>
                    <small style="color:#64748b; font-size:0.75rem;">Format diizinkan: PDF, DOC, DOCX. Maksimal 8 MB.</small>
                </div>

                <div class="form-group" style="grid-column:1 / -1;">
                    <label class="form-label">Deskripsi / Ringkasan Isi <span style="color:#ef4444;">*</span></label>
                    <textarea required name="description" class="form-control" rows="4"
                        placeholder="Ringkasan atau poin-poin penting isi peraturan / dokumen hukum ini...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div
                style="margin-top:24px; padding-top:18px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:12px;">
                <a href="{{ route('legal.documents.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan & Unggah Dokumen</button>
            </div>
        </form>
    </div>
@endsection
