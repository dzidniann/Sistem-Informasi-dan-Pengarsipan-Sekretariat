@extends('admin.layout')

@section('content')
    <div class="page-container">
        <div class="card card-sm">
            <div class="page-header">
                <h2 class="page-title">Kelola Role</h2>
                <p class="page-subtitle">Daftar role bersifat tetap dan hanya dapat dilihat oleh Super Admin.</p>
            </div>
        </div>

        <div class="table-card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Role</th>
                            <th>Jumlah Pengguna</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $r)
                            <tr>
                                <td>{{ $r->nama }}</td>
                                <td>{{ $r->users_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
