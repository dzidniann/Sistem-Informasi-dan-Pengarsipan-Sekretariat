@extends('library.layout')

@section('library-content')
    <!-- Document Search -->
    <div class="card library-search-banner" style="margin-bottom:24px;">
        <span class="library-search-label">Portal Arsip Terbuka</span>
        <h2 class="library-search-title">Cari Dokumen & Regulasi Resmi</h2>
        <p class="library-search-description">Akses publik internal untuk seluruh peraturan direksi, SOP, keputusan resmi,
            dan pedoman tata kelola instansi yang sah berlaku.</p>
        <form method="GET" action="{{ route('library.index') }}"
            style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
            <div style="flex:1 1 220px; min-width:0;">
                <input id="library-search" type="text" name="cari" class="form-control"
                    aria-label="Cari judul regulasi atau kata kunci" placeholder="Ketik judul regulasi atau kata kunci..."
                    value="{{ request('cari') }}">
            </div>
            @if (request('category_id'))
                <input type="hidden" name="category_id" value="{{ request('category_id') }}">
            @endif
            <button type="submit" class="btn btn-primary">Cari Dokumen</button>
        </form>
    </div>

    <!-- Category Filter Pills -->
    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:24px;">
        <a href="{{ route('library.index', ['cari' => request('cari')]) }}"
            class="btn {{ !request('category_id') ? 'btn-primary' : 'btn-light' }} btn-sm" style="white-space:nowrap;">
            Semua Kategori
        </a>
        @foreach ($categories as $cat)
            <a href="{{ route('library.index', ['category_id' => $cat->id, 'cari' => request('cari')]) }}"
                class="btn {{ request('category_id') == $cat->id ? 'btn-primary' : 'btn-light' }} btn-sm"
                style="white-space:nowrap;">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- Documents Grid -->
    <div class="library-grid">
        @forelse($documents as $doc)
            <div class="card" style="display:flex; flex-direction:column; justify-content:space-between; height:100%;">
                <div>
                    <div
                        style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:10px;">
                        <span class="badge badge-cat">{{ $doc->category->name ?? 'Regulasi' }}</span>
                        <span style="font-size:0.75rem; color:#64748b; font-weight:700;">v{{ $doc->version }}</span>
                    </div>

                    <h3 style="margin:0 0 8px; font-size:1.1rem; font-weight:700; color:#0f172a; line-height:1.4;">
                        <a href="{{ route('library.show', $doc) }}" style="color:inherit;">
                            {{ $doc->name }}
                        </a>
                    </h3>

                    <p style="margin:0 0 16px; font-size:0.88rem; color:#64748b; line-height:1.5;">
                        {{ Str::limit($doc->description ?? 'Tidak ada ringkasan keterangan dokumen.', 130) }}
                    </p>
                </div>

                <div
                    style="padding-top:14px; border-top:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:0.78rem; color:#94a3b8;">
                        {{ $doc->effective_date ? \Carbon\Carbon::parse($doc->effective_date)->format('d/m/Y') : '-' }}
                    </div>
                    <div style="display:flex; gap:8px;">
                        <a href="{{ route('library.show', $doc) }}" class="btn btn-light btn-sm">
                            Detail
                        </a>
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-primary btn-sm">
                            Unduh
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column:1 / -1;" class="card">
                <div style="text-align:center; padding:60px 20px; color:#94a3b8;">
                    <h3 style="margin:0; font-size:1.2rem; color:#0f172a;">Tidak ada dokumen yang ditemukan</h3>
                    <p style="margin:6px 0 16px; font-size:0.9rem;">Coba gunakan kata kunci pencarian yang berbeda atau
                        hapus filter kategori.</p>
                    <a href="{{ route('library.index') }}" class="btn btn-light btn-sm">Reset Pencarian</a>
                </div>
            </div>
        @endforelse
    </div>

    @if ($documents->hasPages())
        <div style="margin-top:28px;">
            {{ $documents->links() }}
        </div>
    @endif
@endsection
