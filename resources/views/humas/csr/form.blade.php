@extends('humas.layout')

@section('content')
    <div class="topbar">
        <div class="page-header">
            <h1 class="page-title">{{ $csr ? 'Edit Arsip CSR' : 'Input Arsip CSR' }}</h1>
            <p class="page-subtitle">Isian bertanda * wajib diisi. Rekap menggunakan tanggal proses sebagai periode.</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('humas.csr.index') }}">Kembali ke Riwayat</a>
    </div>

    <div class="card">
        @if ($categories->isEmpty())
            <p class="alert alert-error">
                Belum ada kategori bidang Humas. Minta Admin menambahkan melalui Kategori Arsip → Humas.
            </p>
        @endif

        <form
            method="POST"
            action="{{ $csr ? route('humas.csr.update', $csr) : route('humas.csr.store') }}"
            enctype="multipart/form-data"
        >
            @csrf
            @if ($csr)
                @method('PUT')
            @endif

            <div class="humas-grid">
                @foreach ([
                    'pemohon' => 'Pemohon',
                    'nomor_surat' => 'Nomor Surat',
                    'nama_kegiatan' => 'Nama Kegiatan',
                    'nomor_voucher' => 'Nomor Voucher',
                ] as $field => $label)
                    <div class="form-group">
                        <label class="form-label" for="{{ $field }}">
                            {{ $label }} {{ in_array($field, ['pemohon', 'nama_kegiatan']) ? '*' : '' }}
                        </label>
                        <input
                            class="form-control"
                            id="{{ $field }}"
                            name="{{ $field }}"
                            maxlength="255"
                            value="{{ old($field, $csr?->$field) }}"
                            @required(in_array($field, ['pemohon', 'nama_kegiatan']))
                        >
                    </div>
                @endforeach

                <div class="form-group">
                    <label class="form-label" for="tanggal_proses">Tanggal Proses *</label>
                    <input
                        type="date"
                        class="form-control"
                        id="tanggal_proses"
                        name="tanggal_proses"
                        value="{{ old('tanggal_proses', $csr?->tanggal_proses?->format('Y-m-d') ?? today()->toDateString()) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="kategori_id">Kategori *</label>
                    <select class="form-control" id="kategori_id" name="kategori_id" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('kategori_id', $csr?->kategori_id) == $category->id || (!session()->hasOldInput('kategori_id') && $csr?->kategori?->nama === $category->nama))>{{ $category->nama }}</option>
                        @endforeach
                    </select>
                    <small class="humas-help">Kategori mengikuti 10 pilihan pada template rekap CSR.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status *</label>
                    <select class="form-control" id="status" name="status" required>
                        @if (!array_key_exists(old('status', $csr?->status ?? 'TIDAK_ADA'), \App\Models\HumasCsr::STATUSES))
                            <option value="" selected disabled>Pilih status sesuai template (nilai lama: {{ $csr?->status_label }})</option>
                        @endif
                        @foreach (\App\Models\HumasCsr::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $csr?->status ?? 'TIDAK_ADA') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="luaran">Luaran *</label>
                    <select class="form-control" id="luaran" name="luaran" required>
                        @if (!array_key_exists(old('luaran', $csr?->luaran ?? 'DANA'), \App\Models\HumasCsr::LUARAN))
                            <option value="" selected disabled>Pilih luaran sesuai template (nilai lama: {{ $csr?->luaran_label }})</option>
                        @endif
                        @foreach (\App\Models\HumasCsr::LUARAN as $value => $label)
                            <option value="{{ $value }}" @selected(old('luaran', $csr?->luaran ?? 'DANA') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="nominal">Nominal (Rp) <span id="nominal-required">*</span></label>
                    <input
                        class="form-control"
                        type="number"
                        min="0"
                        max="999999999999.99"
                        step="0.01"
                        id="nominal"
                        name="nominal"
                        value="{{ old('nominal', $csr?->nominal) }}"
                        placeholder="Contoh: 1000000"
                    >
                    <small class="humas-help">
                        Isi angka tanpa Rp atau pemisah ribuan. Wajib lebih dari nol untuk luaran Dana.
                    </small>
                </div>

                <div class="form-group humas-full">
                    <label class="form-label" for="keluar">Keluar / Tindak Lanjut</label>
                    <textarea
                        class="form-control"
                        id="keluar"
                        name="keluar"
                        rows="3"
                        maxlength="5000"
                        placeholder="Contoh: DILAKSANAKAN (DIHADIRI DIRUT), BANTUAN DISERAHKAN, atau -"
                    >{{ old('keluar', $csr?->keluar) }}</textarea>
                    <small class="humas-help">Teks bebas; tidak mengubah Status secara otomatis.</small>
                </div>

                <div class="form-group humas-full">
                    <label class="form-label" for="keterangan">Keterangan</label>
                    <textarea
                        class="form-control"
                        id="keterangan"
                        name="keterangan"
                        rows="3"
                        maxlength="10000"
                    >{{ old('keterangan', $csr?->keterangan) }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="lampiran_surat">Surat / Proposal {{ $csr ? '(tambahan)' : '*' }}</label>
                    <input
                        class="form-control"
                        id="lampiran_surat"
                        type="file"
                        name="lampiran_surat"
                        accept=".pdf,.doc,.docx"
                        @required(!$csr)
                    >
                    <small class="humas-help">PDF, DOC, DOCX; maksimal 8 MB.</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="bukti_bantuan">Bukti Bantuan / Voucher</label>
                    <input
                        class="form-control"
                        id="bukti_bantuan"
                        type="file"
                        name="bukti_bantuan"
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    >
                    <small class="humas-help">PDF, Word, JPG, PNG; maksimal 8 MB.</small>
                </div>

                <div class="form-group humas-full">
                    <label class="form-label" for="foto">Foto Dokumentasi</label>
                    <input
                        class="form-control"
                        id="foto"
                        type="file"
                        name="foto[]"
                        accept=".jpg,.jpeg,.png"
                        multiple
                    >
                    <small class="humas-help">
                        Maksimal 5 foto per simpan, 4 MB/foto, 6000 × 6000 piksel.
                        Total seluruh lampiran per simpan maksimal 14 MB.
                    </small>
                </div>
            </div>

            @if ($csr && $csr->attachments->isNotEmpty())
                <h2 class="card-title">Lampiran Tersimpan</h2>
                <p class="humas-help">
                    Centang untuk menghapus lampiran saat menyimpan. Minimal satu lampiran harus tetap tersedia.
                </p>
                <ul class="humas-files">
                    @foreach ($csr->attachments as $attachment)
                        <li>
                            <label>
                                <input
                                    type="checkbox"
                                    name="hapus_lampiran[]"
                                    value="{{ $attachment->id }}"
                                    @checked(in_array($attachment->id, old('hapus_lampiran', [])))
                                > Hapus {{ $attachment->original_name }}
                            </label>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="humas-actions" style="margin-top:20px">
                <button class="btn btn-primary" type="submit">Simpan Arsip CSR</button>
                <a class="btn btn-secondary" href="{{ route('humas.csr.index') }}">Batal</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const luaran = document.getElementById('luaran'),
                nominal = document.getElementById('nominal');
            const update = () => {
                const dana = luaran.value === 'DANA';
                nominal.required = dana;
                nominal.min = dana ? '0.01' : '0';
                document.getElementById('nominal-required').hidden = !dana;
            };
            luaran.addEventListener('change', update);
            update();
        })();
    </script>
@endpush
