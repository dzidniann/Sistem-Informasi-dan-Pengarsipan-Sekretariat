@extends(
    match (auth()->user()->role->nama) {
        'sekretariat' => 'sekretariat.layout',
        'humas' => 'humas.layout',
        default => 'staf.layout'
    }
)

@section('content')
    <div class="page-header">
        <h2 class="page-title">
            {{ $cakupanSendiri ? 'Arsip Sekretariat' : 'Upload & Arsip Berkas' }}
        </h2>

        <p class="page-subtitle">
            {{ $cakupanSendiri
                ? 'Menampilkan arsip milik bidang Sekretariat dan Pengarsipan saja.'
                : 'Kelola dokumen dan cari arsip dengan cepat dari satu tampilan.' }}
        </p>
    </div>


    @if (session('success'))
        <p style="color: #16a34a; font-weight: 600;">
            {{ session('success') }}
        </p>
    @endif

    <div class="card">

        <h3 style="
        margin-top: 0;
        margin-bottom: 16px;
        font-size: 1.1rem;
        color: #0f172a;">
            Upload Berkas
        </h3>

        <form method="POST" action="{{ route('arsip.store') }}" enctype="multipart/form-data">

            @csrf

            <label class="form-label">Kategori</label>

            <select name="kategori_id" required class="form-control">
                <option value="">
                    -- Pilih kategori --
                </option>

                @foreach ($kategoriList as $k)
                    <option value="{{ $k->id }}">
                        {{ $k->nama }}
                    </option>
                @endforeach
            </select>


            <label class="form-label">Judul File</label>

            <input type="text" name="judul_file" required class="form-control">
            <label class="form-label">
                Tanggal
            </label>

            <input type="date" name="tanggal" required class="form-control">


            <label class="form-label">Berkas</label>

            <input type="file" name="file" required class="form-control">


            <button type="submit" class="button button-primary" style="margin-top: 16px;">Simpan</button>
        </form>
    </div>

    <div class="card" id="daftar-arsip">
        <div class="topbar" style="
            padding: 0;
            align-items: flex-end;
            gap: 16px;">
            <div>
                <h3 class="page-title" style="
                font-size: 1.1rem;
                margin: 0;">Daftar Arsip
                </h3>

                <p class="page-subtitle" style="
                margin: 6px 0 0;">
                    {{ $cakupanSendiri ? 'Arsip bidang sendiri ditampilkan di sini.' : 'Arsip sesuai hak akses ditampilkan di sini.' }}
                </p>
            </div>

            <form method="GET" action="{{ route('arsip.index') }}" class="search-group">

                <input type="hidden" name="cakupan" value="{{ request('cakupan') }}">

                <input type="text" name="cari" placeholder="Cari semua data arsip..." value="{{ request('cari') }}"
                    class="form-control">

                <button type="submit" class="button button-primary button-search">Cari
                </button>
            </form>
        </div>

        <div class="table-card">

            <div class="table-wrapper">

                <table class="table">

                    <thead>
                        <tr>
                            <th style="text-align: left;">
                                Judul File
                            </th>

                            <th style="text-align: center;">
                                Kategori
                            </th>

                            @if (auth()->user()->bisaAksesSemuaBidang() && !$cakupanSendiri)
                                <th style="text-align: center;">
                                    Bidang
                                </th>
                            @endif

                            <th style="text-align: center;">
                                Tanggal Dokumen
                            </th>

                            <th style="text-align: center;">
                                Berkas
                            </th>

                        </tr>
                    </thead>


                    <tbody>

                        @forelse($arsip as $a)
                            <tr>
                                {{-- JUDUL --}}
                                <td style="text-align: left;">

                                    <a href="{{ route('arsip.show', $a->id) }}" class="button button-secondary"
                                        style="
                                        padding: 8px 12px;
                                        display: inline-block;
                                        background: #f8fafc;
                                        color: #0f172a;
                                    ">
                                        {{ $a->judul_file }}
                                    </a>

                                </td>


                                {{-- KATEGORI --}}

                                <td style="text-align: center;">

                                    {{ $a->kategori->nama ?? '-' }}

                                </td>


                                {{-- BIDANG --}}

                                @if (auth()->user()->bisaAksesSemuaBidang() && !$cakupanSendiri)
                                    <td style="text-align: center;">

                                        {{ $a->bidang->nama_bidang ?? '-' }}

                                    </td>
                                @endif


                                {{-- TANGGAL --}}

                                <td style="text-align: center;">

                                    {{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}

                                </td>


                                {{-- BERKAS --}}

                                <td style="text-align: center;">

                                    @if ($a->file_path)
                                        {{-- Mengirimkan URL dan Judul File (dinamis) ke JS --}}
                                        <a href="javascript:void(0)"
                                            onclick="previewBerkas('{{ asset('storage/' . $a->file_path) }}', '{{ addslashes($a->judul_file) }}')"
                                            title="Lihat Berkas"
                                            style="color: #475569; display: inline-flex; justify-content: center; align-items: center; background: transparent; border: none; padding: 0; transition: color 0.2s ease;"
                                            onmouseover="this.style.color='#2563eb'"
                                            onmouseout="this.style.color='#475569'">

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

                                <td colspan="{{ auth()->user()->bisaAksesSemuaBidang() && !$cakupanSendiri ? 5 : 4 }}"
                                    style="
                                    text-align:center;
                                    color:#64748b;
                                    padding: 20px;
                                ">
                                    Tidak ada arsip yang ditemukan.
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- PAGINATION --}}

        <div
            style="
            margin-top: 16px;
            display: flex;
            justify-content: center;
        ">
            {{ $arsip->links('vendor.pagination.custom') }}
        </div>

    </div>


    {{-- =====================================================
     MODAL PREVIEW BERKAS (UI BARU)
===================================================== --}}
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

            {{-- HEADER MODAL --}}
            <div
                style="
                display:flex;
                align-items:center;
                justify-content:space-between;
                padding:16px 24px;
                border-bottom:1px solid #e2e8f0;
                background: #ffffff;
            ">

                <h3
                    style="
                    margin:0; 
                    color:#0f172a; 
                    font-size:1.1rem; 
                    font-weight: 500;
                ">
                    Preview Berkas
                </h3>

                <div style="display: flex; align-items: center; gap: 16px;">

                    {{-- Pintasan Download di Header --}}
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

                    {{-- Tombol Close Bergaya Pill --}}
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


    <script>

// Menambahkan parameter namaFile dengan default value
function previewBerkas(url, namaFile = 'Unduhan Berkas')
{
    const modal = document.getElementById('modal-preview-berkas');
    const content = document.getElementById('preview-berkas-content');
    const headerDownload = document.getElementById('modal-header-download');


    if (!modal || !content) {
        console.error('Modal preview berkas tidak ditemukan.');
        return;
    }

    modal.style.display = 'flex';

    // Tampilkan loading
    content.innerHTML = `
        <div style="text-align: center; color: #64748b; padding: 40px;">
            Memuat preview...
        </div>
    `;

    // Ambil ekstensi file
    const cleanUrl = url.split('?')[0];
    const extension = cleanUrl.split('.').pop().toLowerCase();

    // Gabungkan nama kustom dengan ekstensi asli file
    const namaFileLengkap = `${namaFile}.${extension}`;

    // Pasang link download & nama dinamis di header
    if(headerDownload) {
        headerDownload.href = url;
        headerDownload.download = namaFileLengkap; 

        // Sembunyikan ikon download header jika PDF
        if (extension === 'pdf') {
            headerDownload.style.display = 'none';
        } else {
            headerDownload.style.display = 'flex';
        }
    }


    /* =======================================
       GAMBAR
    ======================================= */
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(extension)) {
        content.innerHTML = `
            <img
                src="${url}"
                alt="Preview Berkas"
                style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);"
                onerror="
                    document.getElementById('preview-berkas-content').innerHTML = \`
                        <div style='text-align:center; color:#64748b; padding:40px;'>
                            Gambar tidak dapat ditampilkan.
                        </div>
                    \`;
                "
            >
        `;
        return;
    }

    /* =======================================
       PDF
    ======================================= */
    if (extension === 'pdf') {
        content.innerHTML = `
            <iframe
                src="${url}"
                title="Preview PDF"
                style="width: 100%; height: 100%; border: none; background: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);"
            ></iframe>
        `;
        return;
    }

    /* =======================================
       OFFICE 
    ======================================= */
    if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension)) {
        content.innerHTML = `
            <div style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
                <div style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 40px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.025); max-width: 450px; width: 100%; text-align: center;">
                    
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

                    <a href="${url}" download="${namaFileLengkap}" style="background-color: #0f172a; color: #ffffff; border-radius: 9999px; padding: 10px 24px; font-weight: 500; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);" onmouseover="this.style.backgroundColor='#1e293b'" onmouseout="this.style.backgroundColor='#0f172a'">
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
        return;
    }

    /* =======================================
       FORMAT LAIN
    ======================================= */
    content.innerHTML = `
        <div style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
            <div style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 40px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.025); max-width: 450px; width: 100%; text-align: center;">
                
                <div style="width: 56px; height: 56px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                    </svg>
                </div>

                <h3 style="color: #0f172a; margin: 0 0 12px 0; font-size: 1.15rem; font-weight: 600;">
                    File tidak dapat dipreview
                </h3>

                <p style="color: #64748b; margin: 0 0 24px 0; font-size: 0.95rem; line-height: 1.6;">
                    Format file <strong>.${extension}</strong> belum didukung untuk ditampilkan langsung. Silakan download file untuk membukanya.
                </p>

                <a href="${url}" download="${namaFileLengkap}" style="background-color: #0f172a; color: #ffffff; border-radius: 9999px; padding: 10px 24px; font-weight: 500; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s ease; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);" onmouseover="this.style.backgroundColor='#1e293b'" onmouseout="this.style.backgroundColor='#0f172a'">
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



function tutupPreviewBerkas()
{
    const modal = document.getElementById('modal-preview-berkas');
    const content = document.getElementById('preview-berkas-content');

    if (modal) {
        modal.style.display = 'none';
    }

    if (content) {
        content.innerHTML = '';
    }
}


document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        tutupPreviewBerkas();
    }
});


document.getElementById('modal-preview-berkas')?.addEventListener('click', function(event) {
    if (event.target === this) {
        tutupPreviewBerkas();
    }
});

</script>
@endsection
