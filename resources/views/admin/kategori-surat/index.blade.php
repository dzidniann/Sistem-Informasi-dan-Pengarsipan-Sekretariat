@extends('admin.layout')

@section('content')
    <div class="page-container">
        <div class="card">
            <div class="page-header">
                <h2 class="page-title">Kelola Kategori Surat</h2>
                <p class="page-subtitle">Atur jenis kategori penomoran surat yang berlaku di sistem.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($kategoriList as $k)
                <tr>
                    <td><strong>{{ $k->kode_kategori }}</strong></td>
                    <td>{{ $k->nama_kategori }}</td>
                    <td>{{ $k->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    <td style="display:flex; justify-content:center; align-items:center; gap:8px;">
                        <button type="button" class="button button-secondary" onclick="document.getElementById('edit-ks-{{ $k->id_kategori }}').style.display='table-row'">Edit</button>
                        <form method="POST" action="{{ route('kategori-surat.toggle', $k->id_kategori) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="button button-secondary">{{ $k->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                    </td>
                </tr>
                <tr id="edit-ks-{{ $k->id_kategori }}" style="display:none;">
                    <td colspan="4">
                        <form method="POST" action="{{ route('kategori-surat.update', $k->id_kategori) }}" style="padding:14px 0;">
                            @csrf
                            @method('PUT')

                            <label class="form-label">Kode Kategori</label>
                            <input type="text" name="kode_kategori" class="form-control" value="{{ $k->kode_kategori }}" required>

                            <label class="form-label" style="margin-top:12px;">Nama Kategori</label>
                            <input type="text" name="nama_kategori" class="form-control" value="{{ $k->nama_kategori }}" required>

                            <div style="margin-top:14px; display:flex; gap:8px;">
                                <button type="submit" class="button button-primary">Simpan Perubahan</button>
                                <button type="button" class="button button-secondary" onclick="document.getElementById('edit-ks-{{ $k->id_kategori }}').style.display='none'">Batal</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>

        <div class="form-card">
            <h3 class="section-title">Tambah Kategori Baru</h3>
            <form method="POST" action="{{ route('kategori-surat.store') }}">
                @csrf
                <label class="form-label">Kode Kategori</label>
                <input type="text" name="kode_kategori" class="form-control" placeholder="Contoh: SK, SU, UM.01.01" required>

                <label class="form-label" style="margin-top:14px;">Nama Kategori</label>
                <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Surat Keputusan" required>

                <button type="submit" class="button button-primary" style="margin-top:18px;">Simpan</button>
            </form>
        </div>
    </div>
@endsection