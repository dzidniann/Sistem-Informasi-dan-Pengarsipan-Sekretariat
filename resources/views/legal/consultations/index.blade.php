@extends('legal.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Layanan Pendampingan & Opini Hukum</p>
            <h1 class="page-title">Permohonan Konsultasi Hukum</h1>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card" style="padding:18px;">
        <form method="GET" action="{{ route('legal.consultations.index') }}" class="legal-filters">
            <div>
                <label class="form-label" style="font-size:0.82rem;">Cari Kata Kunci / Pemohon</label>
                <input type="text" name="cari" class="form-control" placeholder="Judul permasalahan, nama pegawai..."
                    value="{{ request('cari') }}">
            </div>

            <div>
                <label class="form-label" style="font-size:0.82rem;">Status Konsultasi</label>
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

    <!-- Consultations Table -->
    <div class="card" style="padding:0; overflow:hidden;">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Judul Permasalahan</th>
                        <th>Pemohon</th>
                        <th>Bidang / Unit</th>
                        <th>PIC Legal</th>
                        <th>Jadwal</th>
                        <th>Status</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($consultations as $index => $c)
                        <tr>
                            <td style="color:#94a3b8; font-weight:600;">
                                {{ $consultations->firstItem() + $index }}
                            </td>
                            <td>
                                <a href="{{ route('legal.consultations.show', $c) }}"
                                    style="font-weight:700; color:#1e293b; display:block; font-size:0.95rem;">
                                    {{ $c->title }}
                                </a>
                                <span style="font-size:0.78rem; color:#64748b;">
                                    Diajukan {{ $c->created_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            </td>
                            <td>
                                <strong style="color:#334155; font-size:0.88rem;">{{ $c->user->name ?? '-' }}</strong>
                                <div style="font-size:0.75rem; color:#64748b;">{{ $c->user->email ?? '-' }}</div>
                            </td>
                            <td>
                                <span
                                    style="font-size:0.85rem; color:#475569;">{{ $c->user->bidang->nama_bidang ?? 'Umum' }}</span>
                            </td>
                            <td>
                                @if ($c->assignedLegal)
                                    <span
                                        style="font-size:0.85rem; color:#115e59; font-weight:600;">{{ $c->assignedLegal->name }}</span>
                                @else
                                    <span style="font-size:0.78rem; color:#dc2626; font-style:italic;">Belum
                                        di-assign</span>
                                @endif
                            </td>
                            <td>
                                @if ($c->schedule)
                                    <div style="font-size:0.85rem; color:#0f172a; font-weight:600;">
                                        {{ \Carbon\Carbon::parse($c->schedule)->format('d/m/Y') }}
                                    </div>
                                    <div style="font-size:0.75rem; color:#64748b;">
                                        ⏰ {{ \Carbon\Carbon::parse($c->schedule)->format('H:i') }} WIB
                                    </div>
                                @else
                                    <span style="font-size:0.78rem; color:#94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($c->status === 'WAITING_REVIEW')
                                    <span class="badge badge-need-review">Menunggu</span>
                                @elseif($c->status === 'ACCEPTED')
                                    <span class="badge badge-info">Diterima</span>
                                @elseif($c->status === 'SCHEDULED')
                                    <span class="badge badge-published">Terjadwal</span>
                                @elseif($c->status === 'ONGOING')
                                    <span class="badge badge-active">Berjalan</span>
                                @elseif($c->status === 'FINISHED')
                                    <span class="badge badge-active">Selesai</span>
                                @else
                                    <span class="badge badge-draft">{{ $c->status }}</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('legal.consultations.show', $c) }}" class="btn btn-secondary btn-sm">
                                    Respon & Tangani
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:50px 20px; color:#94a3b8;">
                                <p style="font-size:1rem; margin:0; font-weight:600;">Belum ada permohonan konsultasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($consultations->hasPages())
            <div style="padding:16px 20px; border-top:1px solid #e2e8f0; background:#f8fafc;">
                {{ $consultations->links() }}
            </div>
        @endif
    </div>
@endsection
