@extends('legal.layout')

@section('content')
    <style>
        .legal-document-expiry {
            font-size: .78rem;
            color: #64748b;
        }

        .legal-document-expiry.is-expired {
            color: #dc2626;
        }
    </style>
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Manajemen Arsip & Regulasi</p>
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                <h1 class="page-title">Dokumen Hukum</h1>
                <a href="{{ route('legal.documents.create') }}" class="btn btn-primary">
                    + Unggah Dokumen Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card" style="padding:18px;">
        <form method="GET" action="{{ route('legal.documents.index') }}" class="legal-filters">
            <div>
                <label class="form-label" style="font-size:0.82rem;">Cari Kata Kunci</label>
                <input type="text" name="cari" class="form-control" placeholder="Nama, PIC, Departemen..."
                    value="{{ request('cari') }}">
            </div>

            <div>
                <label class="form-label" style="font-size:0.82rem;">Kategori</label>
                <select name="category_id" class="form-control">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label" style="font-size:0.82rem;">Status Workflow</label>
                <select name="status" class="form-control">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                            {{ $st }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary" style="width:100%; height:44px;">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Document List Table -->
    <div class="card" style="padding:0; overflow:hidden;">
        <div class="table-wrapper">
            <table class="table document-table record-table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Nama Dokumen</th>
                        <th>Kategori</th>
                        <th>Versi</th>
                        <th>Dept / PIC</th>
                        <th>Masa Berlaku</th>
                        <th>Status</th>
                        <th>Publikasi</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $index => $doc)
                        <tr>
                            <td class="row-number" data-label="No." style="color:#94a3b8; font-weight:600;">
                                {{ $documents->firstItem() + $index }}
                            </td>
                            <td data-label="Nama Dokumen" class="cell-primary">
                                <a href="{{ route('legal.documents.show', $doc) }}" title="{{ $doc->name }}"
                                    style="font-weight:700; color:#1e293b; display:block; font-size:0.95rem;">
                                    {{ $doc->name }}
                                </a>
                                <span style="font-size:0.78rem; color:#64748b;">
                                    Dibuat oleh {{ $doc->creator->name ?? 'System' }} •
                                    {{ $doc->created_at->format('d M Y') }}
                                </span>
                            </td>
                            <td data-label="Kategori" class="document-category">
                                <span
                                    style="display:inline-block; padding:3px 8px; background:#f1f5f9; border-radius:6px; font-size:0.82rem; color:#334155;">
                                    {{ $doc->category->name ?? '-' }}
                                </span>
                            </td>
                            <td data-label="Versi">
                                <span
                                    class="document-version" style="font-weight:700; font-size:0.85rem; color:#115e59;">v{{ $doc->version }}</span>
                            </td>
                            <td data-label="Dept / PIC" class="document-people">
                                <div style="font-size:0.85rem; color:#334155;">{{ $doc->department ?? '-' }}</div>
                                <div style="font-size:0.75rem; color:#64748b;">PIC: {{ $doc->pic ?? '-' }}</div>
                            </td>
                            <td data-label="Masa Berlaku" class="document-dates">
                                <div style="font-size:0.82rem; color:#334155;">
                                    Berlaku:
                                    <span class="document-date">{{ $doc->effective_date ? \Carbon\Carbon::parse($doc->effective_date)->format('d/m/Y') : '-' }}</span>
                                </div>
                                <div class="legal-document-expiry {{ $doc->isExpired() ? 'is-expired' : '' }}">
                                    Berakhir:
                                    <span class="document-date">{{ $doc->expiry_date ? \Carbon\Carbon::parse($doc->expiry_date)->format('d/m/Y') : 'Selamanya' }}</span>
                                </div>
                            </td>
                            <td data-label="Status">
                                @if ($doc->status === 'ACTIVE')
                                    <span class="badge badge-active">ACTIVE</span>
                                @elseif($doc->status === 'NEED_REVIEW')
                                    <span class="badge badge-need-review">NEED REVIEW</span>
                                @elseif($doc->status === 'EXPIRING_SOON')
                                    <span class="badge badge-expiring">EXPIRING</span>
                                @elseif($doc->status === 'EXPIRED')
                                    <span class="badge badge-expired">EXPIRED</span>
                                @else
                                    <span class="badge badge-draft">DRAFT</span>
                                @endif
                            </td>
                            <td data-label="Publikasi">
                                @if ($doc->is_published)
                                    <span class="badge badge-published">PUBLISHED</span>
                                @else
                                    <span style="font-size:0.78rem; color:#94a3b8;">Draft Internal</span>
                                @endif
                            </td>
                            <td data-label="Aksi" class="cell-actions document-actions" style="text-align:right;">
                                <div style="display:inline-flex; gap:6px;">
                                    <a href="{{ route('legal.documents.show', $doc) }}" class="btn btn-secondary btn-sm"
                                        title="Detail">
                                        Detail
                                    </a>
                                    <a href="{{ route('legal.documents.edit', $doc) }}" class="btn btn-secondary btn-sm"
                                        title="Edit">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="cell-empty" colspan="9" style="text-align:center; padding:50px 20px; color:#94a3b8;">
                                <p style="font-size:1rem; margin:0; font-weight:600;">Tidak ada dokumen hukum yang cocok
                                    dengan kriteria.</p>
                                <p style="font-size:0.85rem; margin:6px 0 0;">Coba sesuaikan kata kunci atau filter
                                    pencarian Anda.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($documents->hasPages())
            <div style="padding:16px 20px; border-top:1px solid #e2e8f0; background:#f8fafc;">
                {{ $documents->links() }}
            </div>
        @endif
    </div>
@endsection
