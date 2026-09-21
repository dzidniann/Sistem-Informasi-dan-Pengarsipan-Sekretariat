@extends('admin.layout')

@section('content')
    <div class="page-container">
        <div class="card card-sm">
            <div class="page-header">
                <h2 class="page-title">Kelola Kategori Arsip</h2>
                <p class="page-subtitle">Atur kategori arsip berdasarkan bidang dan sub kategori secara profesional.</p>
            </div>
        </div>

        @if(session('success') || session('error'))
            <div class="alert {{ session('success') ? 'alert-success' : 'alert-error' }}">
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        <div class="grid-2">
            <div class="middle-section">
                @foreach($bidangList as $b)
                    <div class="category-card">
                        <div class="category-card-header">
                            <div>
                                <h3 class="category-title">{{ $b->nama_bidang }}</h3>
                                <p class="section-meta">{{ $b->kategoriArsip->count() }} kategori</p>
                            </div>
                            <span class="category-label">Bidang</span>
                        </div>

                        @if($b->kategoriArsip->isEmpty())
                            <p class="section-meta">Belum ada kategori di bidang ini.</p>
                        @else
                            <div class="category-list">
                            @foreach($b->kategoriArsip as $kategori)
                            <div class="category-card">
                                <div class="category-card-header">
                                    <div>
                                        <p class="category-title">{{ $kategori->nama }}</p>
                                        <p class="section-meta">Kategori utama</p>
                                    </div>
                                    <div style="display:flex; gap:8px;">
                                        <button type="button" class="button button-secondary" onclick="document.getElementById('edit-kat-{{ $kategori->id }}').style.display='block'">Edit</button>
                                        <form method="POST" action="{{ route('kategori-arsip.destroy', $kategori->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="button button-danger" onclick="return confirm('Yakin hapus kategori ini?')">Hapus</button>
                                        </form>
                                    </div>
                                </div>

                                <div id="edit-kat-{{ $kategori->id }}" style="display:none; margin-top:12px; padding-top:12px; border-top:1px solid #e2e8f0;">
                                    <form method="POST" action="{{ route('kategori-arsip.update', $kategori->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="nama" class="form-control" value="{{ $kategori->nama }}" required>
                                        <div style="margin-top:10px; display:flex; gap:8px;">
                                            <button type="submit" class="button button-primary">Simpan</button>
                                            <button type="button" class="button button-secondary" onclick="document.getElementById('edit-kat-{{ $kategori->id }}').style.display='none'">Batal</button>
                                        </div>
                                    </form>
                                </div>

                                @if($kategori->children->isNotEmpty())
                                    <div class="sub-list">
                                        @foreach($kategori->children as $sub)
                                            <div class="sub-item">
                                                <div>
                                                    <p class="category-title">— {{ $sub->nama }}</p>
                                                    <p class="sub-note">Sub kategori</p>
                                                </div>
                                                <div style="display:flex; gap:8px;">
                                                    <button type="button" class="button button-secondary" onclick="document.getElementById('edit-sub-{{ $sub->id }}').style.display='block'">Edit</button>
                                                    <form method="POST" action="{{ route('kategori-arsip.destroy', $sub->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="button button-danger" onclick="return confirm('Yakin hapus sub kategori ini?')">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div id="edit-sub-{{ $sub->id }}" style="display:none; margin:-6px 0 12px; padding:12px; background:#f8fafc; border-radius:12px;">
                                                <form method="POST" action="{{ route('kategori-arsip.update', $sub->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="text" name="nama" class="form-control" value="{{ $sub->nama }}" required>
                                                    <div style="margin-top:10px; display:flex; gap:8px;">
                                                        <button type="submit" class="button button-primary">Simpan</button>
                                                        <button type="button" class="button button-secondary" onclick="document.getElementById('edit-sub-{{ $sub->id }}').style.display='none'">Batal</button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <aside class="form-card">
                <h3 class="section-title">Tambah Kategori Baru</h3>
                <p class="section-meta">Tambahkan kategori baru sesuai bidang yang tersedia.</p>

                <form method="POST" action="{{ route('kategori-arsip.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="nama" required class="form-control" />
                    </div>

                        <div>
                            <label class="form-label">Bidang</label>
                            <select name="bidang_id" id="bidang-select" required class="form-control" onchange="filterIndukKategori()">
                                <option value="">-- Pilih bidang --</option>
                                @foreach($semuaBidang as $b)
                                    <option value="{{ $b->id }}">{{ $b->nama_bidang }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Induk Kategori</label>
                            <select name="parent_id" id="induk-select" class="form-control">
                                <option value="">-- Kategori Utama --</option>
                                @foreach($bidangList as $b)
                                    @foreach($b->kategoriArsip as $kategori)
                                        <option value="{{ $kategori->id }}" data-bidang="{{ $b->id }}">
                                            {{ $kategori->nama }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>

                    <button type="submit" class="button button-primary">Simpan</button>
                </form>
            </aside>
        </div>
    </div>

    <script>
            function filterIndukKategori() {
                const bidangId = document.getElementById('bidang-select').value;
                const indukSelect = document.getElementById('induk-select');
                const options = indukSelect.querySelectorAll('option[data-bidang]');

                options.forEach(opt => {
                    opt.style.display = (opt.dataset.bidang === bidangId) ? '' : 'none';
                });

                indukSelect.value = '';
            }
    </script>
@endsection
