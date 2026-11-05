@extends('sekretariat.layout')

@section('content')
    <div class="page-container">

        <div class="card">
            <div class="page-header">
                <h2 class="page-title">Perilisan Nomor Surat</h2>
                <p class="page-subtitle">Rilis nomor surat resmi berdasarkan kategori yang berlaku.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert" style="background:#fff7e6; border-color:#f1d58d; color:#92400e;">{{ session('warning') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-card">
            <h3 class="section-title">Rilis Nomor Baru</h3>

            <form method="POST" action="{{ route('nomor-surat.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_kategori" id="input-id-kategori">

                <label class="form-label">Kategori Surat</label>
                <button type="button" class="button button-secondary" style="width:100%; margin-bottom:8px;"
                    onclick="bukaModalKategori()">
                    🔍 Pilih Kategori Surat
                </button>
                <p class="section-meta" id="kategori-terpilih-label">Belum ada kategori dipilih</p>

                <div id="area-preview" class="hidden"
                    style="margin-top:20px; padding:15px; background:#f8fafc; border-radius:12px; border:1px solid #e2e8f0;">
                    <label class="form-label">Nomor Surat</label>
                    <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                        <input type="text" name="nomor_urut" id="input-nomor-urut" class="form-control"
                            style="max-width:110px; font-weight:700; text-align:center;" required>
                        <span>/</span>
                        <span id="teks-kode-kategori" style="font-weight:600; color:#334155;"></span>
                        <span>/</span>
                        <select name="bulan" id="select-bulan" class="form-control" style="width:auto;">
                            @foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'] as $i => $romawi)
                                <option value="{{ $i + 1 }}">{{ $romawi }}</option>
                            @endforeach
                        </select>
                        <span>/</span>
                        <select name="tahun" id="select-tahun" class="form-control" style="width:auto;"
                            onchange="gantiTahun()">
                            @foreach (range(now()->year - 1, now()->year + 1) as $y)
                                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>
                                    {{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="section-meta" id="info-nomor-terakhir" style="margin-top:8px;"></p>

                    <div style="margin-top:16px;">
                        <label class="form-label">Tanggal Surat</label>
                        <input type="date" name="tanggal_surat" class="form-control">
                    </div>
                    <div style="margin-top:14px;">
                        <label class="form-label">Perihal</label>
                        <input type="text" name="perihal" class="form-control"
                            placeholder="Contoh: Undangan Rapat Koordinasi">
                    </div>
                    <div style="margin-top:14px;">
                        <label class="form-label">Tujuan</label>
                        <input type="text" name="tujuan" class="form-control"
                            placeholder="Contoh: Kepala Bidang Kepegawaian">
                    </div>
                    <div style="margin-top:14px;">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>
                    <div style="margin-top:14px;">
                        <label class="form-label">Upload File Surat</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>

                    <button type="submit" class="button button-primary" style="margin-top:18px;">Rilis Nomor</button>
                </div>
            </form>
        </div>

        <div class="table-card">
            <div
                style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px;">
                <div>
                    <h3 class="section-title" style="margin:0;">Riwayat Perilisan Nomor</h3>
                    <p class="section-meta" id="label-filter-riwayat" style="margin:4px 0 0;">Menampilkan semua kategori</p>
                </div>
                <button type="button" class="button button-secondary" onclick="tampilkanSemuaRiwayat()">Tampilkan
                    Semua</button>
            </div>

            <div style="display:flex; gap:8px; margin-bottom:14px;">
                <input type="text" id="input-cari-nomor" class="form-control" placeholder="Cari nomor surat"
                    onkeyup="if(event.key==='Enter') cariNomor()">
                <button type="button" class="button button-primary button-search" onclick="cariNomor()">Cari</button>
            </div>

            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Dirilis Oleh</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-riwayat">
                        @forelse($riwayat as $r)
                            <tr>
                                <td><a href="{{ route('nomor-surat.show', $r) }}"
                                        style="font-weight:700; color:#0f172a;">{{ $r->full_nomor }}</a></td>
                                <td>{{ $r->perihal ?? '-' }}</td>
                                <td>{{ $r->kategori->nama_kategori }}</td>
                                <td>
                                    @if ($r->is_backdate)
                                        <span class="category-label" style="background:#fef3c7; color:#92400e;">Sisipan /
                                            TBH</span>
                                    @else
                                        <span class="category-label"
                                            style="background:#ecfdf5; color:#166534;">Normal</span>
                                    @endif
                                </td>
                                <td>{{ $r->perilis->name ?? '-' }}</td>
                                <td>{{ $r->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; color:#94a3b8;">Belum ada nomor surat yang
                                    dirilis</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px; display:flex; justify-content:center;" id="area-pagination">
                {{ $riwayat->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

    <div id="modal-kategori" class="modal-overlay hidden">
        <div class="modal-box">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 class="section-title" style="margin:0;">Pilih Kategori Surat</h3>
                <button type="button" onclick="tutupModalKategori()"
                    style="background:none; border:none; font-size:1.4rem; cursor:pointer; color:#64748b;">&times;</button>
            </div>
            <input type="text" id="filter-kategori" class="form-control" placeholder="Cari kategori..."
                style="margin-bottom:14px;" onkeyup="filterKategori()">
            <div id="daftar-kategori-modal"
                style="max-height:360px; overflow-y:auto; display:flex; flex-direction:column; gap:8px;">
                <p class="section-meta">Memuat kategori...</p>
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .modal-overlay.hidden {
            display: none;
        }

        .modal-box {
            background: #fff;
            border-radius: 24px;
            padding: 26px;
            width: 100%;
            max-width: 480px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.25);
        }

        .kategori-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 16px;
            cursor: pointer;
        }

        .kategori-item:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }
    </style>

    <script>
        let semuaKategori = [];
        const daftarRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        function bukaModalKategori() {
            document.getElementById('modal-kategori').classList.remove('hidden');
            if (semuaKategori.length === 0) muatKategori();
        }

        function tutupModalKategori() {
            document.getElementById('modal-kategori').classList.add('hidden');
        }
        async function muatKategori() {
            const res = await fetch("{{ route('nomor-surat.kategori-aktif') }}");
            semuaKategori = await res.json();
            renderKategori(semuaKategori);
        }

        function renderKategori(list) {
            const wrap = document.getElementById('daftar-kategori-modal');
            wrap.innerHTML = '';
            if (list.length === 0) {
                wrap.innerHTML = '<p class="section-meta">Tidak ada kategori ditemukan</p>';
                return;
            }
            list.forEach(k => {
                const item = document.createElement('div');
                item.className = 'kategori-item';
                item.onclick = () => pilihKategori(k.id_kategori);
                item.innerHTML =
                    `<div><p style="margin:0;font-weight:700;color:#0f172a;">${k.nama_kategori}</p><p style="margin:2px 0 0;color:#64748b;font-size:0.85rem;">Kode: ${k.kode_kategori}</p></div><span style="color:#94a3b8;">&rsaquo;</span>`;
                wrap.appendChild(item);
            });
        }

        function filterKategori() {
            const kata = document.getElementById('filter-kategori').value.toLowerCase();
            renderKategori(semuaKategori.filter(k => k.nama_kategori.toLowerCase().includes(kata) || k.kode_kategori
                .toLowerCase().includes(kata)));
        }
        async function pilihKategori(idKategori) {
            const res = await fetch(`{{ route('nomor-surat.nomor-berikutnya') }}?id_kategori=${idKategori}`);
            const data = await res.json();

            document.getElementById('input-id-kategori').value = data.id_kategori;
            document.getElementById('kategori-terpilih-label').textContent =
                `Kategori dipilih: ${data.nama_kategori} (${data.kode_kategori})`;
            document.getElementById('input-nomor-urut').value = data.nomor_selanjutnya;
            document.getElementById('teks-kode-kategori').textContent = data.kode_kategori;
            document.getElementById('select-bulan').value = new Date().getMonth() + 1;
            document.getElementById('select-tahun').value = data.tahun;
            document.getElementById('info-nomor-terakhir').textContent =
                `Nomor disarankan: ${data.nomor_selanjutnya}. Bisa diedit manual.`;
            document.getElementById('area-preview').classList.remove('hidden');

            tutupModalKategori();
            muatRiwayat(idKategori, data.nama_kategori);
        }
        async function gantiTahun() {
            const idKategori = document.getElementById('input-id-kategori').value;
            const tahun = document.getElementById('select-tahun').value;
            if (!idKategori) return;
            const res = await fetch(
                `{{ route('nomor-surat.nomor-berikutnya') }}?id_kategori=${idKategori}&tahun=${tahun}`);
            const data = await res.json();
            document.getElementById('input-nomor-urut').value = data.nomor_selanjutnya;
            document.getElementById('info-nomor-terakhir').textContent =
                `Nomor untuk tahun ${tahun}: ${data.nomor_selanjutnya}.`;
        }
        async function muatRiwayat(idKategori, namaKategori) {
            const res = await fetch(`{{ route('nomor-surat.riwayat-json') }}?id_kategori=${idKategori}`);
            renderTabelRiwayat(await res.json());
            document.getElementById('label-filter-riwayat').textContent =
                `Menampilkan riwayat kategori: ${namaKategori}`;
            document.getElementById('area-pagination').style.display = 'none';
        }
        async function tampilkanSemuaRiwayat() {
            const res = await fetch(`{{ route('nomor-surat.riwayat-json') }}`);
            renderTabelRiwayat(await res.json());
            document.getElementById('label-filter-riwayat').textContent = 'Menampilkan semua kategori';
            document.getElementById('area-pagination').style.display = 'block';
        }
        async function cariNomor() {
            const kata = document.getElementById('input-cari-nomor').value.trim();
            if (!kata) {
                tampilkanSemuaRiwayat();
                return;
            }
            const res = await fetch(`{{ route('nomor-surat.riwayat-json') }}?cari=${encodeURIComponent(kata)}`);
            renderTabelRiwayat(await res.json());
            document.getElementById('label-filter-riwayat').textContent = `Hasil pencarian: "${kata}"`;
            document.getElementById('area-pagination').style.display = 'none';
        }

        function renderTabelRiwayat(data) {
            const tbody = document.getElementById('tbody-riwayat');
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#94a3b8;">Tidak ada data</td></tr>';
                return;
            }
            const baseUrl = "{{ url('/nomor-surat') }}";
            data.forEach(r => {
                const badge = r.is_backdate ?
                    '<span class="category-label" style="background:#fef3c7;color:#92400e;">Sisipan / TBH</span>' :
                    '<span class="category-label" style="background:#ecfdf5;color:#166534;">Normal</span>';
                const row = document.createElement('tr');
                row.innerHTML =
                    `<td><a href="${baseUrl}/${r.id_surat}" style="font-weight:700;color:#0f172a;">${r.full_nomor}</a></td><td>${r.perihal ?? '-'}</td><td>${r.kategori}</td><td>${badge}</td><td>${r.perilis}</td><td>${r.waktu}</td>`;
                tbody.appendChild(row);
            });
        }
    </script>
@endsection
