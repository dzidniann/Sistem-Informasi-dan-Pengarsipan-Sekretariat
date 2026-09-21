<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Sekretariat & Pengarsipan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { color-scheme: light; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #f8fafc; color: #0f172a; }
        a { text-decoration: none; color: inherit; }

        .nav { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; }
        .nav-brand { font-weight: 700; font-size: 1.1rem; }
        .nav-brand span { color: #64748b; font-weight: 500; }

        .button { display: inline-flex; align-items: center; justify-content: center; border: none; border-radius: 999px; padding: 13px 24px; cursor: pointer; font-weight: 700; letter-spacing: 0.01em; text-decoration: none; transition: transform 0.18s ease; }
        .button:hover { transform: translateY(-1px); }
        .button-primary { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; box-shadow: 0 14px 28px rgba(15, 23, 42, 0.15); }
        .button-secondary { background: #fff; color: #0f172a; border: 1px solid #8bcfc5; }

        .hero { max-width: 780px; margin: 60px auto 80px; padding: 0 24px; text-align: center; }
        .hero-eyebrow { display: inline-block; padding: 6px 16px; border: 1px solid #f1d58d; border-radius: 999px; background: #fff7e6; color: #92400e; font-size: 0.8rem; letter-spacing: 0.06em; text-transform: uppercase; margin-bottom: 20px; }
        .hero h1 { font-size: 2.6rem; font-weight: 800; margin: 0 0 16px; line-height: 1.2; }
        .hero p { color: #475569; font-size: 1.1rem; line-height: 1.7; margin: 0 0 32px; }

        .features { max-width: 1100px; margin: 0 auto 80px; padding: 0 24px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .feature-card { background: #fff; border: 1px solid #b8d6f5; border-top: 3px solid #2563eb; border-radius: 24px; padding: 28px; box-shadow: 0 18px 45px rgba(15, 23, 42, 0.04); }
        .feature-icon { width: 44px; height: 44px; border: 2px solid #9ed8d0; border-radius: 14px; background: #172033; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-bottom: 16px; }
        .feature-card h3 { margin: 0 0 8px; font-size: 1.05rem; }
        .feature-card p { margin: 0; color: #64748b; font-size: 0.92rem; line-height: 1.6; }

        .footer { text-align: center; padding: 30px 24px; color: #94a3b8; font-size: 0.85rem; border-top: 2px solid #d8c38f; }

        @media (max-width: 780px) {
            .features { grid-template-columns: 1fr; }
            .hero h1 { font-size: 2rem; }
        }
    </style>
</head>
<body>

    <nav class="nav">
        <div class="nav-brand" style="display:flex; align-items:center; gap:10px;">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 38px;">
            <span>Sekretariat Hub</span>
        </div>
        <a href="{{ route('login') }}" class="button button-primary">Masuk</a>
    </nav>

    <section class="hero">
        <span class="hero-eyebrow">Sekretariat & Pengarsipan</span>
        <h1>Kelola surat, arsip, dan notulensi dalam satu sistem terpusat</h1>
        <p>
            Sistem Informasi Administrasi dan Pengarsipan Kesekretariatan membantu
            setiap bidang menyimpan berkas dengan rapi, mencatat notulensi rapat,
            dan menyusun laporan bulanan secara otomatis.
        </p>
        <a href="{{ route('login') }}" class="button button-primary">Masuk ke Sistem</a>
    </section>

    <section class="features">
        <div class="feature-card">
            <div class="feature-icon">🗂️</div>
            <h3>Kearsipan per Bidang</h3>
            <p>Setiap bidang punya folder arsip sendiri, dengan kategori yang diatur terpusat oleh Super Admin.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📂</div>
            <h3>Hak Akses Berjenjang</h3>
            <p>Super Admin, Sekretariat, dan Staf Bidang memiliki cakupan akses yang berbeda sesuai kebutuhan.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">👩🏻‍💻</div>
            <h3>Notulensi Rapat</h3>
            <p>Catat hasil rapat lengkap dengan foto dokumentasi dan file notulensi, tersimpan rapi dan mudah dicari.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📑</div>
            <h3>Laporan Otomatis</h3>
            <p>Rekapitulasi arsip bulanan tersusun otomatis per bidang dan kategori, siap diekspor ke PDF.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🕒</div>
            <h3>Log Aktivitas</h3>
            <p>Setiap perubahan data tercatat otomatis, memudahkan audit dan pelacakan riwayat.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔍</div>
            <h3>Pencarian Cepat</h3>
            <p>Temukan arsip dan notulensi dengan cepat lewat pencarian judul, kapan pun dibutuhkan.</p>
        </div>
    </section>

    <footer class="footer">
        &copy; {{ date('Y') }} Sistem Informasi Sekretariat & Pengarsipan
    </footer>

</body>
</html>