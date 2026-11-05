@extends('employee.consultations.layout')

@section('consultation-content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle"><a href="{{ route('employee.consultations.index') }}" style="color:#0284c7;">← Kembali ke
                    Daftar Konsultasi</a></p>
            <h1 class="page-title">Ajukan Permohonan Konsultasi Hukum</h1>
        </div>
    </div>

    <div class="card" style="max-width:800px;">
        <form method="POST" action="{{ route('employee.consultations.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label class="form-label">Subjek / Judul Permasalahan <span style="color:#ef4444;">*</span></label>
                <input type="text" name="title" class="form-control"
                    placeholder="Contoh: Telaah klausul denda dalam kontrak pengadaan vendor PT ABC"
                    value="{{ old('title') }}" required>
                <small style="color:#64748b; font-size:0.75rem;">Buat judul yang ringkas dan menggambarkan pokok
                    permasalahan.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Uraian / Kronologi Permasalahan Secara Rinci <span
                        style="color:#ef4444;">*</span></label>
                <textarea name="description" class="form-control" rows="6"
                    placeholder="Jelaskan kronologi kejadian, pasal/klausul yang ingin ditanyakan, pihak yang terlibat, serta harapan atau saran yang dibutuhkan..."
                    required>{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Berkas Pendukung / Surat / Draft Kontrak (Opsional)</label>
                <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <small style="color:#64748b; font-size:0.75rem;">Format diizinkan: PDF, DOC, DOCX, JPG, PNG. Maksimal
                    8MB.</small>
            </div>

            <div
                style="margin-top:24px; padding-top:18px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:12px;">
                <a href="{{ route('employee.consultations.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Kirim Permohonan Konsultasi</button>
            </div>
        </form>
    </div>
@endsection
