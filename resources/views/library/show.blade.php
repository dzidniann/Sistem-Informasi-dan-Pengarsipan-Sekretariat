@extends('library.layout')

@section('library-content')
    <div style="margin-bottom:20px;">
        <a href="{{ route('library.index') }}" style="color:#0f766e; font-weight:600; font-size:0.9rem;">
            ← Kembali ke Katalog Perpustakaan
        </a>
    </div>

    <div class="library-detail-grid">
        <!-- Document Main Content -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:16px;">
                <span class="badge badge-cat">{{ $document->category->name ?? 'Regulasi' }}</span>
                <span class="badge badge-active">BERLAKU SAH</span>
            </div>

            <h1 style="margin:0 0 16px; font-size:1.6rem; font-weight:800; color:#0f172a; line-height:1.4;">
                {{ $document->name }}
            </h1>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:20px; margin-bottom:24px;">
                <h4
                    style="margin:0 0 10px; font-size:0.92rem; text-transform:uppercase; color:#64748b; letter-spacing:0.04em;">
                    Ringkasan & Penjelasan Dokumen
                </h4>
                <div style="font-size:0.95rem; color:#334155; line-height:1.7; white-space:pre-line;">
                    {{ $document->description ?? 'Tidak ada ringkasan deskripsi khusus untuk dokumen ini.' }}
                </div>
            </div>

            <div
                style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; padding-top:18px; border-top:1px solid #f1f5f9;">
                <div style="font-size:0.85rem; color:#64748b;">
                    Dipublikasikan pada
                    {{ $document->published_at ? \Carbon\Carbon::parse($document->published_at)->translatedFormat('d F Y') : '-' }}
                </div>
                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-primary">
                    Unduh Berkas Dokumen (v{{ $document->version }})
                </a>
            </div>
        </div>

        <!-- Metadata Sidebar -->
        <div class="card">
            <h3
                style="margin:0 0 16px; font-size:1.1rem; font-weight:700; color:#0f172a; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
                Informasi Legalitas
            </h3>

            <div style="display:flex; flex-direction:column; gap:14px;">
                <div>
                    <label style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Nomor
                        Versi</label>
                    <p style="margin:2px 0 0; font-weight:700; color:#0f172a;">v{{ $document->version }}</p>
                </div>

                <div>
                    <label
                        style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Kategori</label>
                    <p style="margin:2px 0 0; font-weight:600; color:#0f172a;">{{ $document->category->name ?? '-' }}</p>
                </div>

                <div>
                    <label style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Unit
                        Terkait</label>
                    <p style="margin:2px 0 0; font-weight:600; color:#0f172a;">{{ $document->department ?? '-' }}</p>
                </div>

                <div>
                    <label style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Penanggung
                        Jawab (PIC)</label>
                    <p style="margin:2px 0 0; font-weight:600; color:#0f172a;">{{ $document->pic ?? '-' }}</p>
                </div>

                <div>
                    <label style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Tanggal
                        Mulai Berlaku</label>
                    <p style="margin:2px 0 0; font-weight:600; color:#0f172a;">
                        {{ $document->effective_date ? \Carbon\Carbon::parse($document->effective_date)->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>

                <div>
                    <label style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Masa Berlaku
                        Berakhir</label>
                    <p style="margin:2px 0 0; font-weight:600; color:#0f172a;">
                        {{ $document->expiry_date ? \Carbon\Carbon::parse($document->expiry_date)->translatedFormat('d F Y') : 'Seterusnya (Tanpa Batas)' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
