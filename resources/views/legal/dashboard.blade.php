@extends('legal.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Pusat Kendali Dokumen dan Layanan</p>
            <div style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
                <h1 class="page-title">Sub Bidang Hukum dan Tata Kelola</h1>
                <a href="{{ route('legal.documents.create') }}" class="btn btn-primary">+ Dokumen Baru</a>
            </div>
        </div>
        <div style="display:flex; gap:10px; align-items:center;">
            <span class="user-badge">{{ auth()->user()->role->nama ?? 'Legal Admin' }}</span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap:16px;">
        <div class="card" style="padding:18px; border-left:4px solid #0f766e;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <p style="margin:0; font-size:0.82rem; color:#64748b; font-weight:600; text-transform:uppercase;">Total
                        Dokumen</p>
                    <h3 style="margin:6px 0 0; font-size:1.8rem; font-weight:800; color:#0f172a;">{{ $totalDocuments }}</h3>
                </div>
            </div>
            <div style="margin-top:12px; font-size:0.8rem; color:#64748b; display:flex; gap:12px;">
                <span>Aktif: <strong>{{ $totalActive }}</strong></span>
                <span>Draft: <strong>{{ $totalDraft }}</strong></span>
            </div>
        </div>

        <div class="card" style="padding:18px; border-left:4px solid #f59e0b;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <p style="margin:0; font-size:0.82rem; color:#64748b; font-weight:600; text-transform:uppercase;">Perlu
                        Review</p>
                    <h3 style="margin:6px 0 0; font-size:1.8rem; font-weight:800; color:#b45309;">{{ $totalNeedReview }}
                    </h3>
                </div>
            </div>
            <div style="margin-top:12px; font-size:0.8rem; color:#64748b;">
                <span>Akan kadaluarsa: <strong>{{ $totalExpiringSoon }}</strong> dokumen</span>
            </div>
        </div>

        <div class="card" style="padding:18px; border-left:4px solid #ef4444;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <p style="margin:0; font-size:0.82rem; color:#64748b; font-weight:600; text-transform:uppercase;">
                        Dokumen Expired</p>
                    <h3 style="margin:6px 0 0; font-size:1.8rem; font-weight:800; color:#dc2626;">{{ $totalExpired }}</h3>
                </div>
            </div>
            <div style="margin-top:12px; font-size:0.8rem; color:#64748b;">
                <span>Perlu pembaharuan segera</span>
            </div>
        </div>

        <div class="card" style="padding:18px; border-left:4px solid #06b6d4;">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <p style="margin:0; font-size:0.82rem; color:#64748b; font-weight:600; text-transform:uppercase;">
                        Konsultasi Hukum</p>
                    <h3 style="margin:6px 0 0; font-size:1.8rem; font-weight:800; color:#0e7490;">{{ $totalConsultations }}
                    </h3>
                </div>
            </div>
            <div style="margin-top:12px; font-size:0.8rem; color:#64748b; display:flex; gap:12px;">
                <span>Menunggu: <strong>{{ $totalWaiting }}</strong></span>
                <span>Terjadwal: <strong>{{ $totalScheduled }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Main Section: Upcoming Schedules & Quick Actions -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 360px), 1fr)); gap:20px;">
        <!-- Upcoming Schedules -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Jadwal Konsultasi Mendatang</h3>
                    <p class="card-meta">Konsultasi yang telah diagendakan dengan pemohon</p>
                </div>
                <a href="{{ route('legal.calendar.index') }}" class="btn btn-secondary btn-sm">Lihat Kalender →</a>
            </div>
            @if ($upcomingSchedules->isEmpty())
                <div style="text-align:center; padding:30px 10px; color:#94a3b8;">
                    <p style="margin:0; font-size:0.9rem;">Tidak ada jadwal konsultasi dalam waktu dekat.</p>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @foreach ($upcomingSchedules as $sched)
                        <div
                            style="padding:12px 14px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong
                                    style="color:#0f172a; font-size:0.92rem; display:block;">{{ $sched->title }}</strong>
                                <span style="font-size:0.8rem; color:#64748b;">Pemohon: {{ $sched->user->name }} •
                                    {{ \Carbon\Carbon::parse($sched->schedule)->translatedFormat('d M Y, H:i') }}
                                    WIB</span>
                            </div>
                            <a href="{{ route('legal.consultations.show', $sched) }}" class="btn btn-secondary btn-sm"
                                style="padding:5px 10px;">Detail</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Digital Library Quick Glance -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Perpustakaan Digital Publik</h3>
                    <p class="card-meta">{{ $totalPublished }} dokumen telah dipublikasikan</p>
                </div>
                <a href="{{ route('library.index') }}" class="btn btn-secondary btn-sm">Buka Perpustakaan →</a>
            </div>
            <p style="color:#475569; font-size:0.92rem; line-height:1.6; margin-top:0;">
                Dokumen hukum berstatus <strong>ACTIVE</strong> atau <strong>EXPIRING_SOON</strong> yang belum kedaluwarsa
                dapat dipublikasikan agar bisa diakses oleh seluruh pegawai melalui perpustakaan digital.
            </p>
            <div style="display:flex; gap:10px; margin-top:16px;">
                <a href="{{ route('legal.documents.index', ['status' => 'ACTIVE']) }}" class="btn btn-primary btn-sm">
                    Kelola Publikasi Dokumen
                </a>
            </div>
        </div>
    </div>

    <!-- Tables: Recent Documents & Recent Consultations -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(min(100%, 480px), 1fr)); gap:20px;">
        <!-- Recent Documents -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Dokumen Terbaru</h3>
                    <p class="card-meta">5 dokumen terakhir yang diunggah</p>
                </div>
                <a href="{{ route('legal.documents.index') }}" class="btn btn-secondary btn-sm">Semua Dokumen</a>
            </div>
            <div class="table-wrapper">
                <table class="table dashboard-table record-table">
                    <thead>
                        <tr>
                            <th>Dokumen</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDocuments as $doc)
                            <tr>
                                <td data-label="Dokumen" class="cell-primary">
                                    <strong title="{{ $doc->name }}"
                                        style="color:#0f172a; font-size:0.9rem;">{{ $doc->name }}</strong>
                                    <div style="font-size:0.75rem; color:#94a3b8;">v{{ $doc->version }} •
                                        {{ $doc->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td data-label="Kategori"><span
                                        style="font-size:0.85rem; color:#475569;">{{ $doc->category->name ?? '-' }}</span>
                                </td>
                                <td data-label="Status">
                                    @if ($doc->status === 'ACTIVE')
                                        <span class="badge badge-active">Active</span>
                                    @elseif($doc->status === 'NEED_REVIEW')
                                        <span class="badge badge-need-review">Review</span>
                                    @elseif($doc->status === 'EXPIRING_SOON')
                                        <span class="badge badge-expiring">Expiring</span>
                                    @elseif($doc->status === 'EXPIRED')
                                        <span class="badge badge-expired">Expired</span>
                                    @else
                                        <span class="badge badge-draft">Draft</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="cell-actions">
                                    <a href="{{ route('legal.documents.show', $doc) }}" class="btn btn-secondary btn-sm"
                                        style="padding:4px 8px; font-size:0.75rem;">Lihat</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="cell-empty" colspan="4" style="text-align:center; color:#94a3b8;">Belum ada dokumen yang
                                    diunggah.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Consultations -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Permohonan Konsultasi Baru</h3>
                    <p class="card-meta">Konsultasi hukum dari para pegawai</p>
                </div>
                <a href="{{ route('legal.consultations.index') }}" class="btn btn-secondary btn-sm">Semua Konsultasi</a>
            </div>
            <div class="table-wrapper">
                <table class="table dashboard-table record-table">
                    <thead>
                        <tr>
                            <th>Permasalahan</th>
                            <th>Pemohon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentConsultations as $c)
                            <tr>
                                <td data-label="Permasalahan" class="cell-primary">
                                    <strong title="{{ $c->title }}"
                                        style="color:#0f172a; font-size:0.9rem;">{{ $c->title }}</strong>
                                    <div style="font-size:0.75rem; color:#94a3b8;">{{ $c->created_at->diffForHumans() }}
                                    </div>
                                </td>
                                <td data-label="Pemohon"><span style="font-size:0.85rem; color:#475569;">{{ $c->user->name ?? '-' }}</span></td>
                                <td data-label="Status">
                                    @if ($c->status === 'WAITING_REVIEW')
                                        <span class="badge badge-need-review">Menunggu</span>
                                    @elseif($c->status === 'SCHEDULED')
                                        <span class="badge badge-info">Terjadwal</span>
                                    @elseif($c->status === 'ONGOING')
                                        <span class="badge badge-active">Berjalan</span>
                                    @elseif($c->status === 'FINISHED')
                                        <span class="badge badge-draft">Selesai</span>
                                    @else
                                        <span class="badge badge-draft">{{ $c->status }}</span>
                                    @endif
                                </td>
                                <td data-label="Aksi" class="cell-actions">
                                    <a href="{{ route('legal.consultations.show', $c) }}" class="btn btn-secondary btn-sm"
                                        style="padding:4px 8px; font-size:0.75rem;">Respon</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="cell-empty" colspan="4" style="text-align:center; color:#94a3b8;">Belum ada konsultasi yang
                                    diajukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
