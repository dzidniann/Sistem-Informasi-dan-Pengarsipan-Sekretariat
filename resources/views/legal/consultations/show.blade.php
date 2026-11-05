@extends('legal.layout')

@section('content')
    <style>
        .consultation-save:disabled,
        .consultation-save:disabled:hover {
            background: #e2e8f0;
            color: #64748b;
            box-shadow: none;
            cursor: not-allowed;
            transform: none;
        }
    </style>
    <div class="topbar">
        <div class="page-header">
            <p class="page-subtitle"><a href="{{ route('legal.consultations.index') }}" style="color:#0f766e;">← Kembali ke
                    Daftar Konsultasi</a></p>
            <h1 class="page-title">{{ $consultation->title }}</h1>
        </div>
        <div id="consultation-status-badge">
            @if ($consultation->status === 'WAITING_REVIEW')
                <span class="badge badge-need-review" style="font-size:0.85rem; padding:6px 14px;">MENUNGGU REVIEW</span>
            @elseif($consultation->status === 'ACCEPTED')
                <span class="badge badge-info" style="font-size:0.85rem; padding:6px 14px;">DITERIMA</span>
            @elseif($consultation->status === 'SCHEDULED')
                <span class="badge badge-published" style="font-size:0.85rem; padding:6px 14px;">TERJADWAL</span>
            @elseif($consultation->status === 'ONGOING')
                <span class="badge badge-active" style="font-size:0.85rem; padding:6px 14px;">BERJALAN</span>
            @elseif($consultation->status === 'FINISHED')
                <span class="badge badge-active" style="font-size:0.85rem; padding:6px 14px;">SELESAI</span>
            @else
                <span class="badge badge-draft" style="font-size:0.85rem; padding:6px 14px;">DITUTUP</span>
            @endif
        </div>
    </div>

    <div class="legal-detail-grid">
        <!-- Left Column: Details & Discussion Thread -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Issue Card -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Permasalahan yang Diajukan</h3>
                        <p class="card-meta">Diajukan oleh <strong>{{ $consultation->user->name ?? 'Pegawai' }}</strong>
                            ({{ $consultation->user->bidang->nama_bidang ?? 'Umum' }}) •
                            {{ $consultation->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
                <div
                    style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px; font-size:0.95rem; line-height:1.7; color:#334155; white-space:pre-line;">
                    {{ $consultation->description }}
                </div>

                @if ($consultation->attachment)
                    <div
                        style="margin-top:16px; padding-top:14px; border-top:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:0.88rem; font-weight:600; color:#334155;">Berkas Lampiran Awal</span>
                        </div>
                        <a href="{{ asset('storage/' . $consultation->attachment) }}" target="_blank"
                            class="btn btn-secondary btn-sm">
                            Buka Lampiran
                        </a>
                    </div>
                @endif
            </div>

            <!-- Discussion Thread (Messages) -->
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">Ruang Diskusi & Catatan Hukum</h3>
                        <p class="card-meta">Komunikasi langsung antara Pemohon dan Sub Bidang Hukum</p>
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
                                <strong>{{ $msg->user->name ?? 'User' }}</strong>
                                <span>• {{ $msg->created_at->translatedFormat('d M H:i') }}</span>
                            </div>
                            <div
                                style="max-width:85%; background:{{ $isMe ? '#e6f7f4' : '#f1f5f9' }}; border:1px solid {{ $isMe ? '#9ed8d0' : '#e2e8f0' }}; border-radius:16px; padding:12px 16px; font-size:0.92rem; color:#0f172a; line-height:1.6;">
                                <div style="white-space:pre-line;">{{ $msg->message }}</div>
                                @if ($msg->attachment)
                                    <div
                                        style="margin-top:8px; padding-top:6px; border-top:1px dashed {{ $isMe ? '#a5b4fc' : '#cbd5e1' }};">
                                        <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank"
                                            style="font-size:0.8rem; color:#0f766e; text-decoration:underline;">
                                            Buka Berkas Lampiran
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center; padding:30px 10px; color:#94a3b8;">
                            <p style="margin:0; font-size:0.88rem;">Belum ada tanggapan atau pesan dalam diskusi ini.
                                Berikan respon pertama di bawah.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Reply Form -->
                <form method="POST" action="{{ route('legal.consultations.message', $consultation) }}"
                    enctype="multipart/form-data" style="border-top:1px solid #e2e8f0; padding-top:16px;">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" style="font-size:0.85rem;">Kirim Balasan / Catatan Hukum</label>
                        <textarea name="message" class="form-control" rows="3"
                            placeholder="Tulis analisis hukum, advis, saran, atau pertanyaan tindak lanjut..." required></textarea>
                    </div>
                    <div
                        style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; margin-top:10px;">
                        <div>
                            <input type="file" name="attachment" style="font-size:0.82rem; color:#64748b;"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" style="padding:10px 20px;">
                            Kirim Balasan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Administrative Actions -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <!-- Status Management -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom:12px;">Status Konsultasi</h3>
                <form method="POST" action="{{ route('legal.consultations.update-status', $consultation) }}"
                    data-change-form>
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <select name="status" class="form-control" style="font-weight:600;">
                            <option value="WAITING_REVIEW"
                                {{ $consultation->status === 'WAITING_REVIEW' ? 'selected' : '' }}>WAITING REVIEW
                                (Menunggu)</option>
                            <option value="ACCEPTED" {{ $consultation->status === 'ACCEPTED' ? 'selected' : '' }}>ACCEPTED
                                (Diterima)</option>
                            <option value="SCHEDULED" {{ $consultation->status === 'SCHEDULED' ? 'selected' : '' }}>
                                SCHEDULED (Terjadwal)</option>
                            <option value="ONGOING" {{ $consultation->status === 'ONGOING' ? 'selected' : '' }}>ONGOING
                                (Sedang Berjalan)</option>
                            <option value="FINISHED" {{ $consultation->status === 'FINISHED' ? 'selected' : '' }}>FINISHED
                                (Selesai)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary consultation-save"
                        style="width:100%; border-radius:12px;">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- PIC Assignment -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom:6px;">Penugasan PIC Legal</h3>
                <p style="font-size:0.82rem; color:#64748b; margin-top:0;">Tugaskan staf Sub Bidang Hukum untuk menangani
                    permohonan ini.</p>

                @php
                    $legalAdmins = \App\Models\User::whereHas(
                        'role',
                        fn($q) => $q->where('nama', 'legal-admin')
                    )->get();
                @endphp

                <form method="POST" action="{{ route('legal.consultations.assign', $consultation) }}" data-change-form>
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <select name="assigned_legal" class="form-control" required>
                            <option value="">Pilih Petugas Legal</option>
                            @foreach ($legalAdmins as $adm)
                                <option value="{{ $adm->id }}"
                                    {{ $consultation->assigned_legal == $adm->id ? 'selected' : '' }}>
                                    {{ $adm->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary consultation-save"
                        style="width:100%; border-radius:12px;">
                        Assign Petugas
                    </button>
                </form>
            </div>

            <!-- Schedule Meeting -->
            <div class="card">
                <h3 class="card-title" style="margin-bottom:6px;">Penjadwalan Pertemuan</h3>
                <p style="font-size:0.82rem; color:#64748b; margin-top:0;">Jadwalkan audiensi/rapat pembahasan langsung.
                </p>

                <form method="POST" action="{{ route('legal.consultations.schedule', $consultation) }}"
                    data-change-form>
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <input type="datetime-local" name="schedule" class="form-control"
                            value="{{ $consultation->schedule ? \Carbon\Carbon::parse($consultation->schedule)->format('Y-m-d\TH:i') : '' }}"
                            required>
                    </div>
                    <button type="submit" class="btn btn-primary consultation-save"
                        style="width:100%; border-radius:12px;">
                        Simpan Jadwal Pertemuan
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const actionStates = [];
        let saveQueue = Promise.resolve();
        const statusLabels = {
            WAITING_REVIEW: ['MENUNGGU REVIEW', 'badge-need-review'],
            ACCEPTED: ['DITERIMA', 'badge-info'],
            SCHEDULED: ['TERJADWAL', 'badge-published'],
            ONGOING: ['BERJALAN', 'badge-active'],
            FINISHED: ['SELESAI', 'badge-active'],
        };

        document.querySelectorAll('[data-change-form]').forEach((form) => {
            const field = form.querySelector('select, input[type="datetime-local"]');
            const button = form.querySelector('.consultation-save');
            const state = {
                field,
                button,
                busy: false,
                originalValue: field.tagName === 'SELECT' ?
                    Array.from(field.options).find((option) => option.defaultSelected)?.value ?? field.options[
                        0].value :
                    field.defaultValue,
            };
            actionStates.push(state);
            const feedback = document.createElement('p');
            feedback.setAttribute('role', 'status');
            feedback.setAttribute('aria-live', 'polite');
            feedback.style.cssText = 'margin:10px 0 0;font-size:.85rem;overflow-wrap:anywhere;';
            feedback.hidden = true;
            form.append(feedback);
            const buttonLabel = button.textContent.trim();
            state.update = () => {
                button.disabled = state.busy || field.value === state.originalValue || !field.validity.valid;
                button.setAttribute('aria-busy', String(state.busy));
            };
            field.addEventListener('input', state.update);
            field.addEventListener('change', state.update);
            form.addEventListener('reset', () => queueMicrotask(state.update));
            window.addEventListener('pageshow', state.update);
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                state.update();
                if (button.disabled || !form.reportValidity()) return;
                const body = new FormData(form);
                const submittedValue = field.value;
                state.busy = true;
                button.textContent = 'Menyimpan…';
                feedback.hidden = true;
                state.update();
                saveQueue = saveQueue.then(async () => {
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body,
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                        });
                        const data = await response.json().catch(() => null);
                        if (!response.ok || !data?.consultation) {
                            const errors = data?.errors ? Object.values(data.errors).flat()
                                .join(' ') : null;
                            throw new Error(errors || (response.status === 419 || response
                                .status === 401 ?
                                'Sesi berakhir. Buka ulang halaman untuk masuk kembali.' :
                                'Perubahan belum tersimpan. Silakan coba lagi.'));
                        }
                        actionStates.forEach((other) => {
                            const savedValue = String(data.consultation[other.field
                                .name] ?? '');
                            const unchanged = other.field.value === other.originalValue;
                            if (unchanged || (other === state && other.field.value ===
                                    submittedValue)) {
                                other.field.value = savedValue;
                            }
                            other.originalValue = savedValue;
                            other.update();
                        });
                        const badge = document.createElement('span');
                        const [label, badgeClass] = statusLabels[data.consultation.status];
                        badge.className = 'badge ' + badgeClass;
                        badge.style.cssText = 'font-size:.85rem;padding:6px 14px;';
                        badge.textContent = label;
                        document.getElementById('consultation-status-badge').replaceChildren(
                            badge);
                        feedback.textContent = data.message;
                        feedback.style.color = '#0f766e';
                    } catch (error) {
                        feedback.textContent = error instanceof TypeError ?
                            'Koneksi terputus. Periksa koneksi dan coba simpan lagi.' : error
                            .message;
                        feedback.style.color = '#b91c1c';
                    } finally {
                        feedback.hidden = false;
                        state.busy = false;
                        button.textContent = buttonLabel;
                        state.update();
                    }
                });
            });
            state.update();
        });
    </script>
@endpush
