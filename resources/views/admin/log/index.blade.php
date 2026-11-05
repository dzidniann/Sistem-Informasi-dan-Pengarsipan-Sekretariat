@extends('admin.layout')

@section('content')
    <div class="page-container">
        <div class="card card-sm">
            <div class="page-header">
                <h2 class="page-title">Log Aktivitas Pengguna</h2>
                <p class="page-subtitle">Riwayat aktivitas pengguna untuk audit dan pemantauan sistem.</p>
            </div>
        </div>

        <div class="table-card">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Data Terkait</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
                                <td>{{ $log->user->name }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->model_type }} #{{ $log->model_id }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Belum ada aktivitas tercatat</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div style="margin-top: 16px; display: flex; justify-content: center;">
                    {{ $logs->links('vendor.pagination.custom') }}
                </div>

            </div>
        </div>

        <div class="card card-sm" style="padding: 16px; text-align: right;">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
