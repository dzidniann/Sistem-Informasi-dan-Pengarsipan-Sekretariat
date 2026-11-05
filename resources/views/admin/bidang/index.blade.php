@extends('admin.layout')

@section('content')
    <div class="page-container">
        <div class="card card-sm">
            <div class="page-header">
                <h2 class="page-title">Kelola Bidang</h2>
                <p class="page-subtitle">Semua bidang ditampilkan di tabel berikut agar manajemen lebih cepat dan mudah.</p>
            </div>
        </div>

        @if (session('success') || session('error'))
            <div class="alert {{ session('success') ? 'alert-success' : 'alert-error' }}">
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Bidang</th>
                            <th style="text-align: center;">Akses Semua Bidang</th>
                            <th style="text-align: center;">Jumlah User</th>
                            <th style="text-align: center;">Jumlah Kategori</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bidang as $b)
                            <tr>
                                <td>{{ $b->nama_bidang }}</td>
                                <td style="text-align: center;">{{ $b->akses_semua_bidang ? 'Ya' : '-' }}</td>
                                <td style="text-align: center;">{{ $b->users_count }}</td>
                                <td style="text-align: center;">{{ $b->kategori_arsip_count }}</td>
                                <td style="display:flex; gap:8px;">
                                    <button type="button" class="button button-secondary"
                                        onclick="document.getElementById('edit-bidang-{{ $b->id }}').style.display='table-row'">Edit</button>
                                    <form method="POST" action="{{ route('bidang.destroy', $b->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button button-danger"
                                            onclick="return confirm('Yakin hapus bidang ini?')">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="edit-bidang-{{ $b->id }}" style="display:none;">
                                <td colspan="5">
                                    <form method="POST" action="{{ route('bidang.update', $b->id) }}"
                                        style="padding:14px 0;">
                                        @csrf
                                        @method('PUT')

                                        <label class="form-label">Nama Bidang</label>
                                        <input type="text" name="nama_bidang" class="form-control"
                                            value="{{ $b->nama_bidang }}" required>

                                        <label style="display:flex; align-items:center; gap:8px; margin-top:12px;">
                                            <input type="checkbox" name="akses_semua_bidang" value="1"
                                                {{ $b->akses_semua_bidang ? 'checked' : '' }}
                                                style="width:18px; height:18px; accent-color:#0f172a;">
                                            Izinkan staf bidang ini melihat arsip dari semua bidang
                                        </label>

                                        <div style="margin-top:14px; display:flex; gap:8px;">
                                            <button type="submit" class="button button-primary">Simpan Perubahan</button>
                                            <button type="button" class="button button-secondary"
                                                onclick="document.getElementById('edit-bidang-{{ $b->id }}').style.display='none'">Batal</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-card">
            <h3 class="section-title">Tambah Bidang Baru</h3>
            <p class="section-meta">Tambah bidang baru untuk memperluas akses staf dan menyusun kategori arsip.</p>

            <form method="POST" action="{{ route('bidang.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="form-label">Nama Bidang</label>
                    <input type="text" name="nama_bidang" required class="form-control" />
                </div>
                <div>
                    <label class="form-label">Pengaturan Akses</label>
                    <label class="inline-flex items-center gap-3 text-slate-700"
                        style="display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" name="akses_semua_bidang" value="1"
                            style="width:18px; height:18px; accent-color:#0f172a; appearance:auto; -webkit-appearance:auto; cursor:pointer;" />
                        Izinkan staf bidang ini melihat arsip dari semua bidang
                    </label>
                    <p class="section-meta">Panel/staf bidang tetap sama; hanya cakupan data arsip yang diperluas.</p>
                </div>
                <button type="submit" class="button button-primary">Simpan</button>
            </form>
        </div>
    </div>
@endsection
