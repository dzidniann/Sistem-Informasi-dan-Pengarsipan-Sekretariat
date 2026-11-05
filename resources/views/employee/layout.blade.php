<!DOCTYPE html>
<html lang="id">

    <head>
        @include('layouts.favicon')
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Portal Pegawai - Layanan Hukum & Perpustakaan</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            :root {
                color-scheme: light;
                font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                color: #0f172a;
                background: #f8fafc;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                background: #f8fafc;
                color: #0f172a;
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            button,
            input,
            select,
            textarea {
                font: inherit;
            }

            .app-shell {
                display: flex;
                min-height: 100vh;
            }

            /* Sidebar */
            .sidebar {
                width: 270px;
                min-width: 270px;
                background: #0f172a;
                color: #f8fafc;
                border-right: 3px solid #0284c7;
                transition: transform 0.25s ease, width 0.25s ease;
            }

            .sidebar-inner {
                padding: 26px 18px;
                display: flex;
                flex-direction: column;
                gap: 14px;
                min-height: 100vh;
            }

            .sidebar-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                padding-bottom: 16px;
                border-bottom: 1px solid #1e293b;
                margin-bottom: 6px;
            }

            .sidebar-brand img {
                height: 38px;
            }

            .brand-text h2 {
                margin: 0;
                font-size: 1.05rem;
                font-weight: 700;
                color: #f8fafc;
                letter-spacing: -0.01em;
            }

            .brand-text p {
                margin: 0;
                font-size: 0.75rem;
                color: #38bdf8;
                text-transform: uppercase;
                font-weight: 600;
                letter-spacing: 0.05em;
            }

            .sidebar-section-title {
                margin: 12px 10px 4px;
                font-size: 0.75rem;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #64748b;
                font-weight: 700;
            }

            /* Links */
            .nav-link {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 11px 14px;
                border: 1px solid transparent;
                border-radius: 14px;
                color: #cbd5e1;
                margin-bottom: 3px;
                font-size: 0.92rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .nav-link-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .nav-link:hover {
                background: #1e293b;
                border-color: #334155;
                color: #fff;
                transform: translateX(2px);
            }

            .nav-link.active {
                background: #0369a1;
                border-color: #38bdf8;
                color: #f0f9ff;
                font-weight: 600;
                box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
            }

            .sidebar-footer {
                margin-top: auto;
                padding-top: 14px;
                border-top: 1px solid #1e293b;
            }

            /* Main Content */
            .main-content {
                flex: 1;
                padding: 28px 30px;
            }

            .page-container {
                max-width: 1160px;
                margin: 0 auto;
                display: flex;
                flex-direction: column;
                gap: 24px;
            }

            .topbar {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 14px;
                align-items: center;
                padding-bottom: 18px;
                border-bottom: 1.5px solid #e2e8f0;
            }

            .page-header {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }

            .page-title {
                margin: 0;
                font-size: 26px;
                font-weight: 800;
                color: #0f172a;
                letter-spacing: -0.02em;
            }

            .page-subtitle {
                margin: 0;
                color: #64748b;
                font-size: 0.95rem;
            }

            .user-badge {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background: #e0f2fe;
                border: 1px solid #bae6fd;
                color: #0369a1;
                padding: 8px 14px;
                border-radius: 999px;
                font-size: 0.88rem;
                font-weight: 600;
            }

            /* Cards */
            .card {
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
                padding: 24px;
            }

            .card-header {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                gap: 12px;
                margin-bottom: 18px;
                padding-bottom: 12px;
                border-bottom: 1px solid #f1f5f9;
            }

            .card-title {
                margin: 0;
                font-size: 1.15rem;
                font-weight: 700;
                color: #0f172a;
            }

            .card-meta {
                margin: 0;
                color: #64748b;
                font-size: 0.88rem;
            }

            /* Tables */
            .table-wrapper {
                overflow-x: auto;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
                min-width: 650px;
            }

            .table th {
                padding: 13px 16px;
                text-align: left;
                background: #f8fafc;
                color: #475569;
                font-weight: 700;
                font-size: 0.82rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-bottom: 1px solid #e2e8f0;
            }

            .table td {
                padding: 14px 16px;
                text-align: left;
                border-bottom: 1px solid #f1f5f9;
                font-size: 0.92rem;
                color: #334155;
            }

            .table tbody tr:hover {
                background: #f8fafc;
            }

            /* Buttons */
            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border: none;
                border-radius: 999px;
                padding: 10px 18px;
                cursor: pointer;
                font-weight: 600;
                font-size: 0.9rem;
                transition: all 0.2s ease;
                text-decoration: none;
            }

            .btn-primary {
                background: #0284c7;
                color: #fff;
                box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
            }

            .btn-primary:hover {
                background: #0369a1;
                transform: translateY(-1px);
            }

            .btn-secondary {
                background: #f1f5f9;
                color: #334155;
                border: 1px solid #cbd5e1;
            }

            .btn-secondary:hover {
                background: #e2e8f0;
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 0.8rem;
                border-radius: 999px;
            }

            /* Status Badges */
            .badge {
                display: inline-flex;
                align-items: center;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.03em;
            }

            .badge-need-review {
                background: #fef3c7;
                color: #92400e;
                border: 1px solid #fcd34d;
            }

            .badge-active {
                background: #dcfce7;
                color: #166534;
                border: 1px solid #86efac;
            }

            .badge-info {
                background: #e0f2fe;
                color: #0369a1;
                border: 1px solid #7dd3fc;
            }

            .badge-draft {
                background: #f1f5f9;
                color: #475569;
                border: 1px solid #cbd5e1;
            }

            /* Forms */
            .form-group {
                display: flex;
                flex-direction: column;
                gap: 7px;
                margin-bottom: 16px;
            }

            .form-label {
                color: #334155;
                font-size: 0.9rem;
                font-weight: 600;
            }

            .form-control {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: 14px;
                padding: 11px 14px;
                background: #fff;
                color: #0f172a;
                outline: none;
                transition: border-color 0.2s, box-shadow 0.2s;
                font-size: 0.92rem;
            }

            .form-control:focus {
                border-color: #0284c7;
                box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
            }

            textarea.form-control {
                resize: vertical;
                min-height: 100px;
            }

            /* Alerts */
            .alert {
                border-radius: 14px;
                padding: 14px 18px;
                margin-bottom: 18px;
                font-size: 0.92rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            }

            .alert-success {
                background: #ecfdf5;
                border: 1px solid #a7f3d0;
                color: #065f46;
            }

            .alert-error {
                background: #fef2f2;
                border: 1px solid #fecaca;
                color: #991b1b;
            }

            /* Responsive Drawer */
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.5);
                z-index: 39;
            }

            @media (min-width: 981px) {
                .sidebar-toggle {
                    display: flex;
                }

                .app-shell.sidebar-collapsed .sidebar {
                    width: 0;
                    min-width: 0;
                    overflow: hidden;
                    border-right: none;
                }
            }

            @media (max-width: 980px) {
                .sidebar-toggle {
                    display: flex;
                }

                .sidebar {
                    position: fixed;
                    top: 0;
                    left: 0;
                    height: 100vh;
                    z-index: 40;
                    transform: translateX(-100%);
                }

                .app-shell.sidebar-open .sidebar {
                    transform: translateX(0);
                }

                .app-shell.sidebar-open .sidebar-overlay {
                    display: block;
                }

                .main-content {
                    padding-top: 76px;
                    padding-left: 18px;
                    padding-right: 18px;
                }
            }
        </style>
        @include('layouts.sidebar-controls-style')
        @include('layouts.responsive-style')
    </head>

    <body>
        <div class="app-shell" id="appShell">
            <div class="sidebar-overlay" onclick="closeSidebarMobile()"></div>

            <aside class="sidebar">
                <div class="sidebar-inner">
                    <button type="button" class="sidebar-close" onclick="closeSidebarMobile()"
                        aria-label="Tutup menu">Tutup <x-ui-icon name="close" width="16"
                            height="16" /></button>
                    <div class="sidebar-brand">
                        <img src="{{ asset('images/logo-icon-badge.png') }}" alt="Logo"
                            onerror="this.style.display='none'">
                        <div class="brand-text">
                            <h2>PORTAL PEGAWAI</h2>
                            <p>Layanan Hukum</p>
                        </div>
                    </div>

                    <div class="sidebar-section-title">Layanan Mandiri</div>
                    <a href="{{ route('employee.consultations.index') }}" class="nav-link {{ Request::routeIs('employee.consultations.index', 'employee.consultations.show') ? 'active' : '' }}">
                        <span class="nav-link-content">Konsultasi Saya</span>
                    </a>
                    <a href="{{ route('employee.consultations.create') }}" class="nav-link {{ Request::routeIs('employee.consultations.create') ? 'active' : '' }}">
                        <span class="nav-link-content">✍️ Ajukan Baru</span>
                    </a>

                    <div class="sidebar-section-title">Referensi & Regulasi</div>
                    <a href="{{ route('library.index') }}" class="nav-link {{ Request::routeIs('library.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Perpustakaan Digital</span>
                    </a>

                    <div class="sidebar-footer">
                        <div style="font-size:0.8rem; color:#94a3b8; margin-bottom:10px; padding:0 4px;">
                            Pegawai: <strong>{{ auth()->user()->name }}</strong>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-secondary"
                                style="width:100%; border-radius:12px; font-size:0.85rem; padding:9px;">
                                🚪 Keluar (Logout)
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <main class="main-content">
                <div class="sidebar-toolbar">
                    <button type="button" class="sidebar-toggle" onclick="toggleSidebar()"
                        aria-label="Buka atau tutup menu"><x-ui-icon name="menu" /></button>
                </div>
                <div class="page-container">
                    @if (session('success'))
                        <div class="alert alert-success">
                            <span>✅ {{ session('success') }}</span>
                            <button type="button" onclick="this.parentElement.remove()"
                                style="background:none;border:none;cursor:pointer;color:inherit;font-size:1.1rem;">×</button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-error">
                            <span>⚠️ {{ session('error') }}</span>
                            <button type="button" onclick="this.parentElement.remove()"
                                style="background:none;border:none;cursor:pointer;color:inherit;font-size:1.1rem;">×</button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        @include('layouts.sidebar-controls-script')
    </body>

</html>
