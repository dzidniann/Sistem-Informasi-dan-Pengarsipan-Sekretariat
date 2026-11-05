@extends('sekretariat.layout')

@section('content')

    <div class="page-header">

        <h2 class="page-title">
            Perilisan Nomor Surat
        </h2>

        <p class="page-subtitle">
            Rilis nomor surat resmi berdasarkan kategori yang berlaku.
        </p>

    </div>


    @if (session('success'))
        <div style="color:#16a34a; font-weight:600;">
            {{ session('success') }}
        </div>
    @endif


    @if (session('warning'))
        <div class="alert"
            style="
            background:#fff7e6;
            border-color:#f1d58d;
            color:#92400e;
        ">
            {{ session('warning') }}
        </div>
    @endif


    @if ($errors->any())
        <div class="alert alert-error">

            <ul style="margin:0; padding-left:18px;">

                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach

            </ul>

        </div>
    @endif


    <div class="form-card">

        <h3>
            Rilis Nomor Surat
        </h3>


        <form method="POST" action="{{ route('nomor-surat.store') }}" enctype="multipart/form-data">

            @csrf

            <input type="hidden" name="id_kategori" id="input-id-kategori">


            {{-- KATEGORI --}}

            <button type="button" class="button button-secondary" style="width:100%; margin-bottom:8px;"
                onclick="bukaModalKategori()">
                🔍 Pilih Kategori Surat
            </button>

            <p class="section-meta" id="kategori-terpilih-label">
                Belum ada kategori dipilih
            </p>



            {{-- AREA NOMOR --}}

            <div id="area-preview" class="hidden"
                style="
                margin-top:20px;
                padding:15px;
                background:#f8fafc;
                border-radius:12px;
                border:1px solid #e2e8f0;
            ">

                <label class="form-label">
                    Nomor Surat
                </label>


                <div
                    style="
                    display:flex;
                    align-items:center;
                    gap:6px;
                    flex-wrap:wrap;
                ">

                    <input type="text" name="nomor_urut" id="input-nomor-urut" class="form-control"
                        style="
                        max-width:110px;
                        font-weight:700;
                        text-align:center;
                    "
                        required>

                    <span>/</span>

                    <span id="teks-kode-kategori"
                        style="
                        font-weight:600;
                        color:#334155;
                    "></span>

                    <span>/</span>

                    <select name="bulan" id="select-bulan" class="form-control" style="width:auto;">

                        @foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'] as $i => $romawi)
                            <option value="{{ $i + 1 }}">
                                {{ $romawi }}
                            </option>
                        @endforeach

                    </select>

                    <span>/</span>

                    <select name="tahun" id="select-tahun" class="form-control" style="width:auto;"
                        onchange="gantiTahun()">

                        @foreach (range(now()->year - 1, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach

                    </select>

                </div>


                <p class="section-meta" id="info-nomor-terakhir" style="margin-top:8px;"></p>



                {{-- TANGGAL --}}

                <div style="margin-top:16px;">

                    <label class="form-label">
                        Tanggal Surat
                    </label>

                    <input type="date" name="tanggal_surat" class="form-control">

                </div>



                {{-- PERIHAL --}}

                <div style="margin-top:14px;">

                    <label class="form-label">
                        Perihal
                    </label>

                    <input type="text" name="perihal" class="form-control"
                        placeholder="Contoh: Undangan Rapat Koordinasi">

                </div>



                {{-- TUJUAN --}}

                <div style="margin-top:14px;">

                    <label class="form-label">
                        Tujuan
                    </label>

                    <input type="text" name="tujuan" class="form-control"
                        placeholder="Contoh: Kepala Bidang Kepegawaian">

                </div>



                {{-- CATATAN --}}

                <div style="margin-top:14px;">

                    <label class="form-label">
                        Catatan Tambahan
                    </label>

                    <textarea name="catatan" class="form-control" rows="3"></textarea>

                </div>



                {{-- FILE --}}

                <div style="margin-top:14px;">

                    <label class="form-label">
                        Upload File Surat
                    </label>

                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

                </div>



                <button type="submit" class="button button-primary" style="margin-top:18px;">
                    Rilis Nomor
                </button>

            </div>

        </form>

    </div>


    <div class="table-card">

        <div
            style="
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:10px;
            margin-bottom:14px;
        ">

            <div>

                <h3 class="section-title" style="margin:0;">
                    Riwayat Perilisan Nomor
                </h3>

                <p class="section-meta" id="label-filter-riwayat" style="margin:4px 0 0;">
                    Menampilkan semua kategori
                </p>

            </div>


            <button type="button" class="button button-secondary" onclick="tampilkanSemuaRiwayat()">
                Tampilkan Semua
            </button>

        </div>



        {{-- SEARCH --}}

        <div style="
            display:flex;
            gap:8px;
            margin-bottom:14px;
        ">

            <input type="text" id="input-cari-nomor" class="form-control"
                placeholder="Cari nomor, perihal, tujuan, atau catatan..." onkeyup="if(event.key === 'Enter') cariNomor()">

            <button type="button" class="button button-primary button-search" onclick="cariNomor()">
                Cari
            </button>

        </div>



        {{-- TABLE --}}

        <div class="table-wrapper">

            <table class="table">

                <thead>

                    <tr>

                        <th>
                            Nomor Surat
                        </th>

                        <th style="text-align: center;">
                            Perihal
                        </th>

                        <th>
                            Kategori
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Dirilis Oleh
                        </th>

                        <th>
                            Waktu
                        </th>

                        <th style="text-align: center;">
                            Berkas
                        </th>

                    </tr>

                </thead>


                <tbody id="tbody-riwayat">

                    @forelse($riwayat as $r)
                        <tr>

                            <td>

                                <a href="{{ route('nomor-surat.show', $r) }}"
                                    style="
                                    font-weight:700;
                                    color:#0f172a;
                                ">
                                    {{ $r->full_nomor }}
                                </a>

                            </td>


                            <td style="text-align: center;">
                                {{ $r->perihal ?? '-' }}
                            </td>


                            <td>
                                {{ $r->kategori->nama_kategori ?? '-' }}
                            </td>


                            <td>

                                @if ($r->is_backdate)
                                    <span class="category-label"
                                        style="
                                        background:#fef3c7;
                                        color:#92400e;
                                    ">
                                        Sisipan / THB
                                    </span>
                                @else
                                    <span class="category-label"
                                        style="
                                        background:#ecfdf5;
                                        color:#166534;
                                    ">
                                        Normal
                                    </span>
                                @endif

                            </td>


                            <td>
                                {{ $r->perilis->name ?? '-' }}
                            </td>


                            <td>
                                {{ $r->created_at->format('d M Y, H:i') }}
                            </td>


                            {{-- BERKAS --}}

                            <td style="text-align: center;">

                                @if ($r->file_path)
                                    <a href="javascript:void(0)"
                                        onclick="previewBerkas('{{ asset('storage/' . $r->file_path) }}', '{{ addslashes(str_replace('/', '-', $r->full_nomor) . ' - ' . $r->perihal) }}')"
                                        title="Lihat Berkas"
                                        style="color: #475569; display: inline-flex; justify-content: center; align-items: center; background: transparent; border: none; padding: 0; transition: color 0.2s ease;"
                                        onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#475569'">

                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                @else
                                    <span style="color:#94a3b8; font-size: 0.9rem;">
                                        Tidak ada
                                    </span>
                                @endif

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="7"
                                style="
                                text-align:center;
                                color:#94a3b8;
                            ">
                                Belum ada nomor surat yang dirilis
                            </td>

                        </tr>
                    @endforelse

                </tbody>

            </table>

        </div>



        {{-- PAGINATION --}}

        <div style="
            margin-top:16px;
            display:flex;
            justify-content:center;
        "
            id="area-pagination">

            {{ $riwayat->links('vendor.pagination.custom') }}

        </div>

    </div>



    <div id="modal-kategori" class="modal-overlay hidden">

        <div class="modal-box">

            <div
                style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                margin-bottom:16px;
            ">

                <h3 class="section-title" style="margin:0;">
                    Pilih Kategori Surat
                </h3>


                <button type="button" onclick="tutupModalKategori()"
                    style="
                    background:none;
                    border:none;
                    font-size:1.4rem;
                    cursor:pointer;
                    color:#64748b;
                ">
                    &times;
                </button>

            </div>


            <input type="text" id="filter-kategori" class="form-control" placeholder="Cari kategori..."
                style="margin-bottom:14px;" onkeyup="filterKategori()">


            <div id="daftar-kategori-modal"
                style="
                max-height:360px;
                overflow-y:auto;
                display:flex;
                flex-direction:column;
                gap:8px;
            ">

                <p class="section-meta">
                    Memuat kategori...
                </p>

            </div>

        </div>

    </div>


    <div id="modal-preview-berkas"
        style="
        position:fixed;
        inset:0;
        z-index:9999;
        background:rgba(15,23,42,.65);
        display:none;
        align-items:center;
        justify-content:center;
        padding:20px;
        box-sizing:border-box;
    ">

        <div
            style="
            width:95%;
            max-width:1000px;
            height:90vh;
            background:white;
            border-radius:14px;
            overflow:hidden;
            box-shadow:0 25px 60px rgba(0,0,0,.25);
            display: flex;
            flex-direction: column;
        ">
            {{-- HEADER --}}

            <div
                style="
                display:flex;
                align-items:center;
                justify-content:space-between;
                padding:16px 24px;
                border-bottom:1px solid #e2e8f0;
                background: #ffffff;
            ">

                <h3 style="margin:0; color:#0f172a; font-size:1.1rem; font-weight: 500;">
                    Preview Berkas
                </h3>

                <div style="display: flex; align-items: center; gap: 16px;">
                    <!-- Pintasan Download di Header -->
                    <a id="modal-header-download" href="#" download title="Download Berkas"
                        style="color: #64748b; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: color 0.2s;"
                        onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </a>

                    <button type="button" onclick="tutupPreviewBerkas()"
                        style="
                        background: #ffffff; 
                        border: 1px solid #f1f5f9; 
                        box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
                        color: #475569; 
                        width: 48px; 
                        height: 32px; 
                        border-radius: 9999px; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        cursor: pointer; 
                        transition: all 0.2s ease;
                    "
                        onmouseover="this.style.background='#f8fafc'; this.style.color='#0f172a'"
                        onmouseout="this.style.background='#ffffff'; this.style.color='#475569'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                            stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>


            {{-- ISI PREVIEW --}}

            <div id="preview-berkas-content"
                style="
                flex: 1;
                overflow:auto;
                background:#f8fafc;
                display:flex;
                align-items:center;
                justify-content:center;
                padding:20px;
                box-sizing:border-box;
            ">
            </div>
        </div>
    </div>

    <style>
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
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
            box-shadow: 0 30px 60px rgba(0, 0, 0, .25);
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

        const daftarRomawi = [
            'I',
            'II',
            'III',
            'IV',
            'V',
            'VI',
            'VII',
            'VIII',
            'IX',
            'X',
            'XI',
            'XII'
        ];


        function bukaModalKategori() {
            document
                .getElementById('modal-kategori')
                .classList
                .remove('hidden');

            if (semuaKategori.length === 0) {

                muatKategori();

            }
        }


        function tutupModalKategori() {
            document
                .getElementById('modal-kategori')
                .classList
                .add('hidden');
        }


        async function muatKategori() {
            const res = await fetch(
                "{{ route('nomor-surat.kategori-aktif') }}"
            );

            semuaKategori = await res.json();

            renderKategori(semuaKategori);
        }


        function renderKategori(list) {
            const wrap =
                document.getElementById(
                    'daftar-kategori-modal'
                );

            wrap.innerHTML = '';


            if (list.length === 0) {

                wrap.innerHTML =
                    '<p class="section-meta">Tidak ada kategori ditemukan</p>';

                return;
            }


            list.forEach(k => {

                const item =
                    document.createElement('div');

                item.className =
                    'kategori-item';

                item.onclick = () =>
                    pilihKategori(k.id_kategori);

                item.innerHTML = `
            <div>

                <p style="
                    margin:0;
                    font-weight:700;
                    color:#0f172a;
                ">
                    ${k.nama_kategori}
                </p>

                <p style="
                    margin:2px 0 0;
                    color:#64748b;
                    font-size:.85rem;
                ">
                    Kode: ${k.kode_kategori}
                </p>

            </div>

            <span style="color:#94a3b8;">
                &rsaquo;
            </span>
        `;

                wrap.appendChild(item);

            });
        }


        function filterKategori() {
            const kata =
                document
                .getElementById('filter-kategori')
                .value
                .toLowerCase();

            renderKategori(
                semuaKategori.filter(k =>
                    k.nama_kategori
                    .toLowerCase()
                    .includes(kata) ||
                    k.kode_kategori
                    .toLowerCase()
                    .includes(kata)
                )
            );
        }


        async function pilihKategori(idKategori) {
            const res = await fetch(
                `{{ route('nomor-surat.nomor-berikutnya') }}?id_kategori=${idKategori}`
            );

            const data = await res.json();


            document.getElementById(
                'input-id-kategori'
            ).value = data.id_kategori;


            document.getElementById(
                    'kategori-terpilih-label'
                ).textContent =
                `Kategori dipilih: ${data.nama_kategori} (${data.kode_kategori})`;


            document.getElementById(
                    'input-nomor-urut'
                ).value =
                data.nomor_selanjutnya;


            document.getElementById(
                    'teks-kode-kategori'
                ).textContent =
                data.kode_kategori;


            document.getElementById(
                    'select-bulan'
                ).value =
                new Date().getMonth() + 1;


            document.getElementById(
                    'select-tahun'
                ).value =
                data.tahun;


            document.getElementById(
                    'info-nomor-terakhir'
                ).textContent =
                `Nomor disarankan: ${data.nomor_selanjutnya}. Bisa diedit manual.`;


            document
                .getElementById('area-preview')
                .classList
                .remove('hidden');


            tutupModalKategori();

            muatRiwayat(
                idKategori,
                data.nama_kategori
            );
        }


        async function gantiTahun() {
            const idKategori =
                document.getElementById(
                    'input-id-kategori'
                ).value;

            const tahun =
                document.getElementById(
                    'select-tahun'
                ).value;


            if (!idKategori) return;


            const res = await fetch(
                `{{ route('nomor-surat.nomor-berikutnya') }}?id_kategori=${idKategori}&tahun=${tahun}`
            );

            const data = await res.json();


            document.getElementById(
                    'input-nomor-urut'
                ).value =
                data.nomor_selanjutnya;


            document.getElementById(
                    'info-nomor-terakhir'
                ).textContent =
                `Nomor untuk tahun ${tahun}: ${data.nomor_selanjutnya}.`;
        }



        async function muatRiwayat(
            idKategori,
            namaKategori
        ) {
            const res = await fetch(
                `{{ route('nomor-surat.riwayat-json') }}?id_kategori=${idKategori}`
            );

            const data = await res.json();

            renderTabelRiwayat(data);


            document.getElementById(
                    'label-filter-riwayat'
                ).textContent =
                `Menampilkan riwayat kategori: ${namaKategori}`;


            document.getElementById(
                'area-pagination'
            ).style.display = 'none';
        }



        async function tampilkanSemuaRiwayat() {
            const res = await fetch(
                `{{ route('nomor-surat.riwayat-json') }}`
            );

            const data = await res.json();

            renderTabelRiwayat(data);


            document.getElementById(
                    'label-filter-riwayat'
                ).textContent =
                'Menampilkan semua kategori';


            document.getElementById(
                'area-pagination'
            ).style.display = 'block';
        }


        async function cariNomor() {
            const kata =
                document
                .getElementById('input-cari-nomor')
                .value
                .trim();


            if (!kata) {

                tampilkanSemuaRiwayat();

                return;
            }


            const res = await fetch(
                `{{ route('nomor-surat.riwayat-json') }}?cari=${encodeURIComponent(kata)}`
            );

            const data = await res.json();

            renderTabelRiwayat(data);


            document.getElementById(
                    'label-filter-riwayat'
                ).textContent =
                `Hasil pencarian: "${kata}"`;


            document.getElementById(
                'area-pagination'
            ).style.display = 'none';
        }


        function renderTabelRiwayat(data) {
            const tbody =
                document.getElementById(
                    'tbody-riwayat'
                );

            tbody.innerHTML = '';


            if (data.length === 0) {

                tbody.innerHTML = `
            <tr>

                <td
                    colspan="8"
                    style="
                        text-align:center;
                        color:#94a3b8;
                    "
                >
                    Tidak ada data
                </td>

            </tr>
        `;

                return;
            }


            const baseUrl =
                "{{ url('/nomor-surat') }}";


            data.forEach(r => {

                const badge =
                    r.is_backdate

                    ?
                    `
                    <span
                        class="category-label"
                        style="
                            background:#fef3c7;
                            color:#92400e;
                        "
                    >
                        Sisipan / THB
                    </span>
                `

                    :
                    `
                    <span
                        class="category-label"
                        style="
                            background:#ecfdf5;
                            color:#166534;
                        "
                    >
                        Normal
                    </span>
                `;

                const berkas =
                    r.file_path

                    ?
                    `
                    <a href="javascript:void(0)"
                       onclick="previewBerkas('/storage/${r.file_path}', '${r.full_nomor.replace(/\\//g, '-')} - ${r.perihal || 'Surat'}')"
                       title="Lihat Berkas"
                       style="color: #475569; display: inline-flex; justify-content: center; align-items: center; background: transparent; border: none; padding: 0; transition: color 0.2s ease;"
                       onmouseover="this.style.color='#2563eb'" 
                       onmouseout="this.style.color='#475569'">
                       
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </a>
                `

                    :
                    `
                    <span style="color:#94a3b8; font-size: 0.9rem;">
                        Tidak ada
                    </span>
                `;


                const row =
                    document.createElement('tr');


                row.innerHTML = `

            <td>

                <a
                    href="${baseUrl}/${r.id_surat}"
                    style="
                        font-weight:700;
                        color:#0f172a;
                    "
                >
                    ${r.full_nomor}
                </a>

            </td>


            <td>
                ${r.perihal ?? '-'}
            </td>


            <td>
                ${r.kategori ?? '-'}
            </td>


            <td>
                ${badge}
            </td>


            <td>
                ${r.perilis ?? '-'}
            </td>


            <td style="text-align: right; vertical-align: middle;">
                ${berkas}
            </td>

        `;


                tbody.appendChild(row);

            });
        }



        function previewBerkas(url, namaFile = 'Unduhan Berkas') {
            const modal = document.getElementById('modal-preview-berkas');
            const content = document.getElementById('preview-berkas-content');
            const headerDownload = document.getElementById('modal-header-download');

            if (!modal || !content) return;

            const extension = url.split('?')[0].split('.').pop().toLowerCase();

            const namaFileLengkap = `${namaFile}.${extension}`;

            // Pasang link download di header
            headerDownload.href = url;
            headerDownload.download = namaFileLengkap;

            // Tampilkan modal
            modal.style.display = 'flex';

            content.innerHTML = `
        <div style="color:#64748b; text-align:center; padding:40px;">
            Memuat preview...
        </div>
    `;

            if (extension === 'pdf') {
                headerDownload.style.display = 'none'; // Sembunyi
            } else {
                headerDownload.style.display = 'flex'; // Muncul untuk gambar/lainnya
            }

            /* ==============================
               GAMBAR
            ============================== */
            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(extension)) {
                content.innerHTML = `
            <img src="${url}" alt="Preview" style="max-width:100%; max-height:100%; object-fit:contain; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        `;
                return;
            }

            /* ==============================
               PDF
            ============================== */
            if (extension === 'pdf') {
                content.innerHTML = `
            <iframe src="${url}" title="Preview PDF" style="width:100%; height:100%; border:none; background:white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);"></iframe>
        `;
                return;
            }

            /* ==============================
               OFFICE / TIDAK DIDUKUNG (DESAIN CARD)
            ============================== */
            content.innerHTML = `
        <div style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
            <div style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 40px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.025); max-width: 450px; width: 100%; text-align: center;">
                
                <!-- Ikon Dokumen -->
                <div style="width: 56px; height: 56px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>

                <h3 style="color: #0f172a; margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 600;">
                    Preview belum tersedia
                </h3>

                <p style="color: #64748b; margin: 0 0 24px 0; font-size: 0.95rem; line-height: 1.6;">
                    File <strong>.${extension}</strong> tidak dapat ditampilkan langsung oleh browser. Silakan download file untuk membukanya.
                </p>

                <!-- Tombol Unduh -->
                <a href="${url}" download style="background-color: #0f172a; color: #ffffff; border-radius: 9999px; padding: 10px 24px; font-weight: 500; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);" onmouseover="this.style.backgroundColor='#1e293b'" onmouseout="this.style.backgroundColor='#0f172a'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Unduh File
                </a>

            </div>
        </div>
    `;
        }


        function tutupPreviewBerkas() {
            const modal = document.getElementById('modal-preview-berkas');
            const content = document.getElementById('preview-berkas-content');

            if (modal) modal.style.display = 'none';
            if (content) content.innerHTML = '';
        }

        // Tutup dengan ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') tutupPreviewBerkas();
        });

        // Tutup saat area gelap diklik
        document.getElementById('modal-preview-berkas')?.addEventListener('click', function(event) {
            if (event.target === this) tutupPreviewBerkas();
        });
    </script>

@endsection
