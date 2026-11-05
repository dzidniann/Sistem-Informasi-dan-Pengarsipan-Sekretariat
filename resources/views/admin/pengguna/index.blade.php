@extends('admin.layout')

@section('content')
    <div class="page-container">
        <div class="card card-sm">
            <div class="page-header">
                <h2 class="page-title">Kelola Pengguna</h2>
                <p class="page-subtitle">Atur pengguna, role, bidang, dan status aktif/nonaktif dengan antarmuka yang
                    terstruktur.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="table-card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Bidang</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $u)
                            <tr>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->role->nama }}</td>
                                <td>{{ $u->bidang->nama_bidang }}</td>
                                <td>{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td>
                                    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                                        <button type="button" class="button button-secondary"
                                            onclick="document.getElementById('edit-{{ $u->id }}').style.display='block'">Edit</button>

                                        <form method="POST" action="{{ route('pengguna.toggle', $u->id) }}"
                                            style="display:inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="button button-danger">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <tr id="edit-{{ $u->id }}" style="display:none">
                                <td colspan="6">
                                    <div class="form-card">
                                        <form method="POST" action="{{ route('pengguna.update', $u->id) }}">
                                            @csrf
                                            @method('PUT')

                                            <label class="form-label">Nama</label>
                                            <input type="text" name="name" value="{{ $u->name }}" required
                                                class="form-control">

                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" value="{{ $u->email }}" required
                                                class="form-control">

                                            <label class="form-label">Role</label>
                                            <select name="role_id" required class="form-control">
                                                @foreach ($roles as $r)
                                                    <option value="{{ $r->id }}"
                                                        {{ $u->role_id == $r->id ? 'selected' : '' }}>
                                                        {{ $r->nama }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <label class="form-label">Bidang</label>
                                            <select name="bidang_id" required class="form-control">
                                                @foreach ($bidangList as $b)
                                                    <option value="{{ $b->id }}"
                                                        {{ $u->bidang_id == $b->id ? 'selected' : '' }}>
                                                        {{ $b->nama_bidang }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <div style="display:flex; flex-wrap:wrap; gap:10px; margin-top: 16px;">
                                                <button type="submit" class="button button-primary">Simpan
                                                    Perubahan</button>
                                                <button type="button" class="button button-secondary"
                                                    onclick="document.getElementById('edit-{{ $u->id }}').style.display='none'">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-card">
            <h3 class="section-title">Tambah Pengguna Baru</h3>
            <p class="section-meta">Buat akun pengguna baru dan atur role serta bidangnya.</p>

            <form method="POST" action="{{ route('pengguna.store') }}" style="margin-top: 16px;">
                @csrf

                <label class="form-label">Nama</label>
                <input type="text" name="name" required class="form-control">

                <label class="form-label">Email</label>
                <input type="email" name="email" required class="form-control">

                <label class="form-label">Password</label>
                <input type="password" name="password" required class="form-control">

                <label class="form-label">Role</label>
                <select name="role_id" required class="form-control">
                    <option value="">-- Pilih role --</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->nama }}</option>
                    @endforeach
                </select>

                <label class="form-label">Bidang</label>
                <select name="bidang_id" required class="form-control">
                    <option value="">-- Pilih bidang --</option>
                    @foreach ($bidangList as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_bidang }}</option>
                    @endforeach
                </select>

                <button type="submit" class="button button-primary" style="margin-top: 16px;">Simpan</button>
            </form>
        </div>
    </div>
@endsection
