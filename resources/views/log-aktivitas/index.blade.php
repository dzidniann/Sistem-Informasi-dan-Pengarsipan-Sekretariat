@extends($layout)

@section('content')
    <div class="page-container">
        <div class="card">
            <div class="page-header">
                <h2 class="page-title">Log Aktivitas</h2>
                <p class="page-subtitle">
                    Riwayat aktivitas seluruh pengguna.
                </p>
            </div>
        </div>

        <div class="table-card">
            <form method="GET" action="{{ route('log-aktivitas.index') }}" class="search-group" style="margin-bottom:14px;">
                <input
                    type="text"
                    name="cari"
                    placeholder="Cari aksi, jenis data, atau nama pengguna"
                    value="{{ request('cari') }}"
                    class="form-control"
                >

                <button type="submit" class="button button-primary button-search">
                    Cari
                </button>
            </form>

            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Jenis Data</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($logs as $log)
                            <tr
                                style="cursor:pointer;"
                                onclick="window.location='{{ route('log-aktivitas.show', $log) }}'"
                            >
                                <td>
                                    {{ $log->created_at->format('d M Y, H:i') }}
                                </td>

                                <td>
                                    {{ $log->causer->name ?? '-' }}
                                </td>

                                <td>
                                    {{ ucfirst($log->description) }}
                                </td>

                                <td>
                                    {{ class_basename($log->subject_type) ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="4"
                                    style="text-align:center; color:#94a3b8;"
                                >
                                    Belum ada aktivitas tercatat
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px; display:flex; justify-content:center;">
                {{ $logs->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
@endsection