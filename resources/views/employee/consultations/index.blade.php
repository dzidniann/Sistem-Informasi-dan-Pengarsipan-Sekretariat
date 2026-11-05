@extends('employee.consultations.layout')

@section('consultation-content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle">Pendampingan Hukum Internal Perusahaan</p>
            <h1 class="page-title">Konsultasi Hukum Saya</h1>
        </div>
        <a href="{{ route('employee.consultations.create') }}" class="btn btn-primary">
            + Ajukan Konsultasi Baru
        </a>
    </div>

    <!-- Info Banner -->
    <div class="card" style="background:#f0f9ff; border:1px solid #bae6fd; padding:18px;">
        <div style="display:flex; align-items:flex-start; gap:12px;">
            <span style="font-size:1.6rem;">💡</span>
            <div>
                <strong style="color:#0369a1; font-size:0.95rem;">Layanan Konsultasi & Advis Hukum Pegawai</strong>
                <p style="margin:4px 0 0; font-size:0.85rem; color:#0c4a6e; line-height:1.5;">
                    Anda dapat mengajukan permohonan kajian hukum terkait perjanjian kerja, sengketa operasional, telaah
                    peraturan, atau permasalahan kedinasan lainnya. Tim Sub Bidang Hukum akan memberikan advis dan
                    menjadwalkan audiensi.
                </p>
            </div>
        </div>
    </div>

    <!-- Consultations Table -->
    <div class="card" style="padding:0; overflow:hidden;">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>Judul Permasalahan</th>
                        <th>Tanggal Diajukan</th>
                        <th>Petugas Legal (PIC)</th>
                        <th>Jadwal Pertemuan</th>
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
                                <a href="{{ route('employee.consultations.show', $c) }}"
                                    style="font-weight:700; color:#1e293b; display:block; font-size:0.95rem;">
                                    {{ $c->title }}
                                </a>
                                <span style="font-size:0.78rem; color:#64748b;">
                                    {{ $c->messages->count() }} tanggapan dalam diskusi
                                </span>
                            </td>
                            <td>
                                <span style="font-size:0.85rem; color:#475569;">
                                    {{ $c->created_at->translatedFormat('d M Y') }}
                                </span>
                            </td>
                            <td>
                                @if ($c->assignedLegal)
                                    <strong style="font-size:0.85rem; color:#0369a1;">⚖️
                                        {{ $c->assignedLegal->name }}</strong>
                                @else
                                    <span style="font-size:0.8rem; color:#94a3b8; font-style:italic;">Menunggu
                                        penugasan</span>
                                @endif
                            </td>
                            <td>
                                @if ($c->schedule)
                                    <div style="font-size:0.85rem; color:#0f172a; font-weight:600;">
                                        📅 {{ \Carbon\Carbon::parse($c->schedule)->format('d/m/Y') }}
                                    </div>
                                    <div style="font-size:0.75rem; color:#0284c7;">
                                        ⏰ {{ \Carbon\Carbon::parse($c->schedule)->format('H:i') }} WIB
                                    </div>
                                @else
                                    <span style="font-size:0.78rem; color:#94a3b8;">Belum diagendakan</span>
                                @endif
                            </td>
                            <td>
                                @if ($c->status === 'WAITING_REVIEW')
                                    <span class="badge badge-need-review">Menunggu Review</span>
                                @elseif($c->status === 'ACCEPTED')
                                    <span class="badge badge-info">Diterima</span>
                                @elseif($c->status === 'SCHEDULED')
                                    <span class="badge badge-active">Terjadwal</span>
                                @elseif($c->status === 'ONGOING')
                                    <span class="badge badge-active">Sedang Berjalan</span>
                                @elseif($c->status === 'FINISHED')
                                    <span class="badge badge-active">Selesai</span>
                                @else
                                    <span class="badge badge-draft">Ditutup</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('employee.consultations.show', $c) }}" class="btn btn-secondary btn-sm">
                                    Buka Diskusi →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:50px 20px; color:#94a3b8;">
                                <p style="font-size:2.5rem; margin:0 0 10px;">📋</p>
                                <p style="font-size:1rem; margin:0; font-weight:600;">Anda belum pernah mengajukan
                                    konsultasi hukum.</p>
                                <p style="font-size:0.85rem; margin:6px 0 16px;">Ajukan permohonan pertama Anda untuk
                                    mendapatkan advis dari Sub Bidang Hukum.</p>
                                <a href="{{ route('employee.consultations.create') }}" class="btn btn-primary btn-sm">
                                    + Ajukan Konsultasi Sekarang
                                </a>
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
