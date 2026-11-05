@extends('legal.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle"><a href="{{ route('legal.documents.index') }}" style="color:#0f766e;">← Kembali ke Daftar
                    Dokumen</a></p>
            <h1 class="page-title">{{ $document->name }}</h1>
        </div>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('legal.documents.edit', $document) }}" class="btn btn-secondary btn-sm">
                Edit Dokumen
            </a>
            <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-primary btn-sm">
                <x-ui-icon name="download" width="16" height="16" /> Unduh Berkas
            </a>
            <form method="POST" action="{{ route('legal.documents.destroy', $document) }}"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
        </div>
    </div>

    <div class="legal-detail-grid">
        <!-- Main Info & Metadata -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Informasi Dokumen</h3>
                        <p class="card-meta">Versi saat ini: <strong
                                style="color:#0f766e;">v{{ $document->version }}</strong></p>
                    </div>
                    <div>
                        @if ($document->status === 'ACTIVE')
                            <span class="badge badge-active" style="font-size:0.85rem; padding:6px 14px;">ACTIVE</span>
                        @elseif($document->status === 'NEED_REVIEW')
                            <span class="badge badge-need-review" style="font-size:0.85rem; padding:6px 14px;">NEED
                                REVIEW</span>
                        @elseif($document->status === 'EXPIRING_SOON')
                            <span class="badge badge-expiring" style="font-size:0.85rem; padding:6px 14px;">EXPIRING
                                SOON</span>
                        @elseif($document->status === 'EXPIRED')
                            <span class="badge badge-expired" style="font-size:0.85rem; padding:6px 14px;">EXPIRED</span>
                        @else
                            <span class="badge badge-draft" style="font-size:0.85rem; padding:6px 14px;">DRAFT</span>
                        @endif
                    </div>
                </div>

                <div
                    style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 200px), 1fr)); gap:16px; margin-bottom:20px;">
                    <div>
                        <label
                            style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Kategori</label>
                        <p style="margin:4px 0 0; font-weight:600; color:#0f172a;">{{ $document->category->name ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <label
                            style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Departemen
                            / Bidang</label>
                        <p style="margin:4px 0 0; font-weight:600; color:#0f172a;">{{ $document->department ?? '-' }}</p>
                    </div>
                    <div>
                        <label
                            style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Penanggung
                            Jawab (PIC)</label>
                        <p style="margin:4px 0 0; font-weight:600; color:#0f172a;">{{ $document->pic ?? '-' }}</p>
                    </div>
                    <div>
                        <label style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Dibuat
                            Oleh</label>
                        <p style="margin:4px 0 0; font-weight:600; color:#0f172a;">
                            {{ $document->creator->name ?? 'System' }}</p>
                    </div>
                    <div>
                        <label style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Masa
                            Berlaku</label>
                        <p style="margin:4px 0 0; font-weight:600; color:#0f172a;">
                            {{ $document->effective_date ? \Carbon\Carbon::parse($document->effective_date)->format('d F Y') : '-' }}
                            s/d
                            {{ $document->expiry_date ? \Carbon\Carbon::parse($document->expiry_date)->format('d F Y') : 'Seterusnya' }}
                        </p>
                    </div>
                    <div>
                        <label style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Jadwal
                            Review</label>
                        <p style="margin:4px 0 0; font-weight:600; color:#0f172a;">
                            {{ $document->review_date ? \Carbon\Carbon::parse($document->review_date)->format('d F Y') : 'Belum diatur' }}
                        </p>
                    </div>
                </div>

                @if ($document->description)
                    <div style="border-top:1px solid #f1f5f9; padding-top:16px;">
                        <label
                            style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700; display:block; margin-bottom:6px;">Deskripsi
                            / Ringkasan</label>
                        <div
                            style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:14px; font-size:0.92rem; color:#334155; line-height:1.6; white-space:pre-line;">
                            {{ $document->description }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Version History Timeline -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title" id="document-history-title">Riwayat Versi & Aktivitas Dokumen</h3>
                        <p class="card-meta">Catatan audit log setiap perubahan dokumen</p>
                    </div>
                </div>
                <div role="region" aria-labelledby="document-history-title" tabindex="0"
                    style="max-height:360px; overflow-y:auto; overflow-x:hidden; overscroll-behavior-y:contain; scrollbar-gutter:stable; padding-right:8px;">
                    <div
                        style="display:flex; flex-direction:column; gap:14px; position:relative; padding-left:16px; border-left:2px solid #e2e8f0; margin-left:8px; overflow-wrap:anywhere;">
                        @forelse($document->histories as $history)
                            <div style="position:relative;">
                                <div
                                    style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:flex-start; gap:6px 10px;">
                                    <div style="flex:1 1 220px; min-width:0;">
                                        <strong style="font-size:0.92rem; color:#0f172a;">{{ $history->action }}</strong>
                                        <span
                                            style="font-size:0.75rem; color:#0f766e; font-weight:700; margin-left:6px;">v{{ $history->version }}</span>
                                        <p style="margin:2px 0 0; font-size:0.85rem; color:#64748b;">
                                            {{ $history->description }}</p>
                                    </div>
                                    <span style="font-size:0.75rem; color:#94a3b8; white-space:nowrap;">
                                        {{ $history->created_at->translatedFormat('d M Y, H:i') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p style="color:#94a3b8; font-size:0.88rem; margin:0;">Belum ada riwayat tercatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Status & Library Actions -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Status Workflow Controls -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom:14px;">Alur Status (Workflow)</h3>
                <p style="font-size:0.85rem; color:#64748b; margin-top:0;">Status saat ini:
                    <strong>{{ $document->status }}</strong>. NEED_REVIEW mengikuti tanggal review yang sudah tiba.
                    EXPIRING_SOON dan EXPIRED mengikuti tanggal kedaluwarsa; EXPIRED didahulukan.</p>
                <p class="card-meta">Setelah review selesai, atur tanggal review berikutnya melalui Edit Dokumen, lalu pilih ACTIVE.</p>

                <form method="POST" action="{{ route('legal.documents.update-status', $document) }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label class="form-label" style="font-size:0.82rem;">Tahap Dokumen</label>
                        <select name="status" class="form-control" style="font-weight:600;">
                            <option value="DRAFT" {{ $document->status === 'DRAFT' ? 'selected' : '' }}>DRAFT (Konsep)
                            </option>
                            <option value="NEED_REVIEW" {{ $document->status === 'NEED_REVIEW' ? 'selected' : '' }}>NEED
                                REVIEW (Perlu Ditelaah)</option>
                            <option value="ACTIVE" @selected(in_array($document->status, \App\Models\LegalDocument::DATE_MANAGED_STATUSES, true))>ACTIVE (status mengikuti tanggal)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; border-radius:12px;">
                        Update Status Dokumen
                    </button>
                </form>
            </div>

            <!-- Digital Library Publishing -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom:14px;">Publikasi Perpustakaan</h3>
                @if ($document->is_published)
                    <div
                        style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:12px; padding:12px; margin-bottom:14px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div>
                                <strong
                                    style="color:#065f46; font-size:0.88rem; display:block;">{{ $document->canPublish() ? 'Terbit di Perpustakaan' : 'Tidak tampil karena dokumen tidak berlaku' }}</strong>
                                <span style="font-size:0.75rem; color:#047857;">Dipublish pada
                                    {{ $document->published_at ? \Carbon\Carbon::parse($document->published_at)->format('d M Y H:i') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('legal.documents.unpublish', $document) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary"
                            style="width:100%; border-radius:12px; color:#dc2626;">
                            Tarik dari Perpustakaan (Unpublish)
                        </button>
                    </form>
                @else
                    <p style="font-size:0.85rem; color:#64748b; margin-top:0;">
                        Dokumen ini belum tampil di portal umum. Dokumen berstatus <strong>ACTIVE</strong> atau
                        <strong>EXPIRING_SOON</strong> yang belum kedaluwarsa dapat dipublikasikan.
                    </p>
                    @if ($document->canPublish())
                        <form method="POST" action="{{ route('legal.documents.publish', $document) }}">
                            @csrf
                            <button type="submit" class="btn btn-success" style="width:100%; border-radius:12px;">
                                Terbitkan ke Perpustakaan
                            </button>
                        </form>
                    @else
                        <button disabled class="btn btn-secondary"
                            style="width:100%; border-radius:12px; opacity:0.6; cursor:not-allowed;">
                            Wajib Status ACTIVE untuk Terbit
                        </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
