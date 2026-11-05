@extends('employee.consultations.layout')

@section('consultation-content')
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle"><a href="{{ route('employee.consultations.index') }}" style="color:#0284c7;">← Kembali ke
                    Konsultasi Saya</a></p>
            <h1 class="page-title">{{ $consultation->title }}</h1>
        </div>
        <div>
            @if ($consultation->status === 'WAITING_REVIEW')
                <span class="badge badge-need-review" style="font-size:0.85rem; padding:6px 14px;">MENUNGGU REVIEW</span>
            @elseif($consultation->status === 'ACCEPTED')
                <span class="badge badge-info" style="font-size:0.85rem; padding:6px 14px;">DITERIMA</span>
            @elseif($consultation->status === 'SCHEDULED')
                <span class="badge badge-active" style="font-size:0.85rem; padding:6px 14px;">TERJADWAL</span>
            @elseif($consultation->status === 'ONGOING')
                <span class="badge badge-active" style="font-size:0.85rem; padding:6px 14px;">SEDANG BERJALAN</span>
            @elseif($consultation->status === 'FINISHED')
                <span class="badge badge-active" style="font-size:0.85rem; padding:6px 14px;">SELESAI</span>
            @else
                <span class="badge badge-draft" style="font-size:0.85rem; padding:6px 14px;">DITUTUP</span>
            @endif
        </div>
    </div>

    <div class="consultation-detail-grid" style="display:grid; gap:24px; align-items:start;">
        <!-- Left Column: Problem Details & Chat -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Issue Card -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom:12px;">Uraian Permasalahan</h3>
                <div
                    style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px; font-size:0.95rem; line-height:1.7; color:#334155; white-space:pre-line;">
                    {{ $consultation->description }}
                </div>

                @if ($consultation->attachment)
                    <div
                        style="margin-top:16px; padding-top:14px; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:1.2rem;">📎</span>
                            <span style="font-size:0.88rem; font-weight:600; color:#334155;">Berkas Lampiran
                                Pengajuan</span>
                        </div>
                        <a href="{{ asset('storage/' . $consultation->attachment) }}" target="_blank"
                            class="btn btn-secondary btn-sm">
                            ⬇️ Unduh Berkas
                        </a>
                    </div>
                @endif
            </div>

            <!-- Discussion Thread -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Diskusi dengan Tim Sub Bidang Hukum</h3>
                        <p class="card-meta">Ajukan pertanyaan tambahan atau kirimkan berkas pelengkap</p>
                    </div>
                </div>

                <!-- Messages List -->
                <div
                    style="display:flex; flex-direction:column; gap:16px; max-height:480px; overflow-y:auto; padding-right:8px; margin-bottom:20px;">
                    @forelse($consultation->messages as $msg)
                        @php
                            $isMe = $msg->user_id === auth()->id();
                        @endphp
                        <div
                            style="display:flex; flex-direction:column; align-items:{{ $isMe ? 'flex-end' : 'flex-start' }};">
                            <div
                                style="display:flex; align-items:center; gap:8px; margin-bottom:4px; font-size:0.75rem; color:#64748b;">
                                <strong>{{ $isMe ? 'Anda' : $msg->user->name ?? 'Tim Legal' }}</strong>
                                <span>• {{ $msg->created_at->translatedFormat('d M H:i') }}</span>
                            </div>
                            <div
                                style="max-width:85%; background:{{ $isMe ? '#e0f2fe' : '#f1f5f9' }}; border:1px solid {{ $isMe ? '#bae6fd' : '#e2e8f0' }}; border-radius:16px; padding:12px 16px; font-size:0.92rem; color:#0f172a; line-height:1.6;">
                                <div style="white-space:pre-line;">{{ $msg->message }}</div>
                                @if ($msg->attachment)
                                    <div
                                        style="margin-top:8px; padding-top:6px; border-top:1px dashed {{ $isMe ? '#7dd3fc' : '#cbd5e1' }};">
                                        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank"
                                            style="font-size:0.8rem; color:#0284c7; text-decoration:underline;">
                                            📎 Buka Berkas Lampiran
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding:30px 10px; color:#94a3b8;">
                            <p style="margin:0; font-size:0.88rem;">Permohonan Anda telah kami terima. Tim Sub Bidang Hukum
                                akan segera menelaah dan memberikan respon di sini.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Reply Form -->
                <form method="POST" action="{{ route('employee.consultations.message', $consultation) }}"
                    enctype="multipart/form-data" style="border-top:1px solid #e2e8f0; padding-top:16px;">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" style="font-size:0.85rem;">Tulis Pesan / Tanggapan</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="Tuliskan respon atau pertanyaan lanjutan..."
                            required></textarea>
                    </div>
                    <div
                        style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-top:10px;">
                        <div>
                            <input type="file" name="attachment" style="font-size:0.82rem; color:#64748b;"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" style="padding:10px 20px;">
                            ✉️ Kirim Pesan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Status & Assistance Info -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Schedule Info Card -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom:14px;">Informasi Layanan</h3>

                <div style="margin-bottom:14px;">
                    <label style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Petugas Sub
                        Bidang Hukum</label>
                    @if ($consultation->assignedLegal)
                        <p style="margin:4px 0 0; font-weight:700; color:#0f172a; font-size:0.95rem;">
                            ⚖️ {{ $consultation->assignedLegal->name }}
                        </p>
                        <span style="font-size:0.8rem; color:#64748b;">{{ $consultation->assignedLegal->email }}</span>
                    @else
                        <p style="margin:4px 0 0; font-style:italic; color:#94a3b8; font-size:0.88rem;">
                            Sedang dalam proses penunjukan PIC
                        </p>
                    @endif
                </div>

                <div style="margin-bottom:14px; border-top:1px solid #f1f5f9; padding-top:12px;">
                    <label style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Jadwal
                        Pertemuan / Audiensi</label>
                    @if ($consultation->schedule)
                        <div
                            style="margin-top:4px; padding:10px 12px; background:#e0f2fe; border-radius:10px; border:1px solid #bae6fd;">
                            <strong style="color:#0369a1; font-size:0.95rem; display:block;">
                                📅 {{ \Carbon\Carbon::parse($consultation->schedule)->translatedFormat('l, d F Y') }}
                            </strong>
                            <span style="font-size:0.85rem; color:#0c4a6e;">
                                ⏰ Pukul {{ \Carbon\Carbon::parse($consultation->schedule)->format('H:i') }} WIB
                            </span>
                        </div>
                    @else
                        <p style="margin:4px 0 0; color:#64748b; font-size:0.88rem;">
                            Belum ada jadwal tatap muka/audiensi yang ditentukan.
                        </p>
                    @endif
                </div>

                <div style="border-top:1px solid #f1f5f9; padding-top:12px;">
                    <label style="font-size:0.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Tanggal
                        Permohonan</label>
                    <p style="margin:4px 0 0; color:#0f172a; font-size:0.88rem;">
                        {{ $consultation->created_at->translatedFormat('d F Y, H:i') }} WIB
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
