<!DOCTYPE html>
<html lang="id">

    <head>
        @include('layouts.favicon')
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sub Bidang Hukum dan Tata Kelola - Sistem Kesekretariatan & Legal</title>
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
                width: 260px;
                min-width: 260px;
                background: #172033;
                color: #f8fafc;
                border-right: 3px solid #0f766e;
                transition: transform 0.25s ease, width 0.25s ease;
            }

            .sidebar-inner {
                padding: 28px 20px;
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
                border-bottom: 1px solid #416a9e;
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
                color: #9ed8d0;
                text-transform: uppercase;
                font-weight: 600;
                letter-spacing: 0.05em;
            }

            .sidebar-section-title {
                margin: 12px 10px 4px;
                font-size: 0.75rem;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #94a3b8;
                font-weight: 700;
            }

            /* Navigation Links */
            .nav-link {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 12px 16px;
                border: 1px solid transparent;
                border-radius: 16px;
                color: #d9f5f0;
                margin-bottom: 6px;
                font-size: 0.95rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }

            .nav-link-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .nav-link:hover {
                background: #19433f;
                border-color: #4da99f;
                color: #fff;
                transform: translateX(2px);
            }

            .nav-link.active {
                background: #1d625b;
                border-color: #7bd1c6;
                color: #fff;
                font-weight: 600;
            }

            .nav-badge {
                background: #ef4444;
                color: #fff;
                font-size: 0.72rem;
                padding: 2px 7px;
                border-radius: 999px;
                font-weight: 700;
            }

            .sidebar-footer {
                margin-top: auto;
                padding-top: 14px;
                border-top: 1px solid #416a9e;
            }

            /* Main Content */
            .main-content {
                flex: 1;
                min-width: 0;
                padding: 30px 26px;
            }

            .page-container {
                max-width: 1180px;
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
                border-bottom: 2px solid #8bcfc5;
            }

            .page-header {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .page-title {
                margin: 0;
                font-size: 28px;
                font-weight: 700;
                color: #0f172a;
            }

            .page-subtitle {
                margin: 0;
                color: #475569;
                font-size: 0.95rem;
                line-height: 1.6;
            }

            .user-badge {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background: #fff7e6;
                border: 1px solid #f1d58d;
                color: #92400e;
                padding: 10px 14px;
                border-radius: 999px;
                font-size: 0.88rem;
                font-weight: 600;
            }

            /* Cards */
            .card {
                background: #fff;
                border: 1px solid #9ed8d0;
                border-radius: 26px;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.05);
                padding: 26px;
                min-width: 0;
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
                min-width: 700px;
            }

            .table th {
                padding: 16px 18px;
                text-align: left;
                background: #f8fafc;
                color: #475569;
                font-weight: 700;
                font-size: 0.95rem;
                letter-spacing: 0.01em;
                border-bottom: 1px solid #e2e8f0;
            }

            .table td {
                padding: 16px 18px;
                text-align: left;
                border-bottom: 1px solid #d5e2f0;
                font-size: 0.95rem;
                color: #475569;
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
                padding: 12px 18px;
                cursor: pointer;
                font-weight: 600;
                font-size: 0.9rem;
                transition: all 0.2s ease;
                text-decoration: none;
            }

            .btn-primary {
                background: linear-gradient(135deg, #0f172a, #1e293b);
                color: #fff;
            }

            .btn-primary:hover {
                background: #1e293b;
                transform: translateY(-1px);
            }

            .btn-secondary {
                background: #e6f7f4;
                color: #115e59;
                border: 1px solid #9ed8d0;
            }

            .btn-secondary:hover {
                background: #d0eee7;
            }

            .btn-success {
                background: #10b981;
                color: #fff;
            }

            .btn-success:hover {
                background: #059669;
            }

            .btn-danger {
                background: #ef4444;
                color: #fff;
            }

            .btn-danger:hover {
                background: #dc2626;
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 0.8rem;
                border-radius: 999px;
            }

            .btn-logout {
                background: #fff7e6;
                color: #92400e;
                border: 1px solid #f1d58d;
            }

            .btn-logout:hover {
                background: #fef0cd;
            }

            .btn:focus-visible,
            .nav-link:focus-visible,
            .sidebar-toggle:focus-visible {
                outline: 3px solid #0f766e;
                outline-offset: 3px;
            }

            .legal-detail-grid {
                display: grid;
                grid-template-columns: minmax(0, 1.8fr) minmax(300px, 1fr);
                gap: 24px;
                align-items: start;
            }

            .legal-detail-grid>* {
                min-width: 0;
            }

            .legal-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-items: end;
            }

            .legal-filters>div {
                flex: 1 1 200px;
                min-width: 0;
            }

            .legal-filters>div:last-child {
                flex: 0 0 120px;
            }

            .legal-filters .form-label {
                display: block;
                margin-bottom: 10px;
            }

            .dashboard-table {
                min-width: 0;
                table-layout: auto;
            }

            .dashboard-table th,
            .dashboard-table td {
                padding: 14px 8px;
                overflow-wrap: break-word;
            }

            .dashboard-table .badge {
                max-width: none;
                white-space: nowrap;
            }

            .topbar>div {
                flex-wrap: wrap;
            }

            @media (max-width: 1200px) {
                .legal-detail-grid {
                    grid-template-columns: minmax(0, 1fr);
                }
            }

            @media (max-width: 640px) {
                .card {
                    padding: 18px;
                }

                .page-title {
                    font-size: 24px;
                }
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

            .badge-draft {
                background: #f1f5f9;
                color: #475569;
                border: 1px solid #cbd5e1;
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

            .badge-expiring {
                background: #ffedd5;
                color: #9a3412;
                border: 1px solid #fdba74;
            }

            .badge-expired {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #fca5a5;
            }

            .badge-published {
                background: #e6f7f4;
                color: #115e59;
                border: 1px solid #9ed8d0;
            }

            .badge-info {
                background: #e0f2fe;
                color: #0369a1;
                border: 1px solid #7dd3fc;
            }

            /* Form Inputs */
            .form-group {
                display: flex;
                flex-direction: column;
                gap: 7px;
                margin-bottom: 16px;
            }

            .form-label {
                color: #334155;
                font-size: 0.95rem;
                font-weight: 600;
            }

            .form-control {
                width: 100%;
                border: 1px solid #8bcfc5;
                border-radius: 18px;
                padding: 14px 15px;
                background: #fbfffe;
                color: #0f172a;
                outline: none;
                transition: border-color 0.2s, box-shadow 0.2s;
                font-size: 0.92rem;
            }

            .form-control:focus {
                border-color: #0f766e;
                box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.14);
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
            .sidebar-toolbar {
                display: flex;
                justify-content: flex-start;
                margin: 0 0 20px -14px;
            }

            .sidebar-toggle {
                display: flex;
                position: static;
                flex-shrink: 0;
                padding: 0;
                background: #0f172a;
                color: #fff;
                border: none;
                border-radius: 12px;
                width: 42px;
                height: 42px;
                font-size: 20px;
                cursor: pointer;
                align-items: center;
                justify-content: center;
                box-shadow: 0 10px 20px rgba(15, 23, 42, 0.2);
            }

            .sidebar-close {
                display: none;
            }

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
                .sidebar-toolbar {
                    margin-left: 0;
                }

                .sidebar-toggle {
                    display: flex;
                }

                .sidebar {
                    position: fixed;
                    top: 0;
                    left: 0;
                    height: 100vh;
                    overflow-y: auto;
                    z-index: 40;
                    transform: translateX(-100%);
                }

                .sidebar-close {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    align-self: flex-end;
                    border: 1px solid #334155;
                    border-radius: 10px;
                    background: #1e293b;
                    color: #fff;
                    padding: 6px 12px;
                    cursor: pointer;
                }

                .app-shell.sidebar-open .sidebar {
                    transform: translateX(0);
                }

                .app-shell.sidebar-open .sidebar-overlay {
                    display: block;
                }

                .main-content {
                    padding-top: 18px;
                    padding-left: 18px;
                    padding-right: 18px;
                }
            }
        </style>
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
                            <h2>SUB BIDANG HUKUM DAN TATA KELOLA</h2>
                            <p>Legal & Digital Archive</p>
                        </div>
                    </div>

                    <div class="sidebar-section-title">Menu Utama</div>
                    <a href="{{ route('legal.dashboard') }}" class="nav-link {{ Request::routeIs('legal.dashboard') ? 'active' : '' }}">
                        <span class="nav-link-content">Dashboard</span>
                    </a>
                    <a href="{{ route('legal.documents.index') }}" class="nav-link {{ Request::routeIs('legal.documents.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Dokumen Hukum</span>
                    </a>
                    <a href="{{ route('legal.governance-documents.index') }}" class="nav-link {{ Request::routeIs('legal.governance-documents.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Dokumen Tata Kelola</span>
                    </a>
                    <a href="{{ route('legal.consultations.index') }}" class="nav-link {{ Request::routeIs('legal.consultations.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Konsultasi Hukum</span>
                    </a>
                    <a href="{{ route('legal.calendar.index') }}" class="nav-link {{ Request::routeIs('legal.calendar.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Kalender Jadwal</span>
                    </a>
                    <a href="{{ route('legal.activities.index') }}" class="nav-link {{ Request::routeIs('legal.activities.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Kegiatan Bulanan</span>
                    </a>

                    <a href="{{ route('legal.notulensi.index') }}" class="nav-link {{ Request::routeIs('legal.notulensi.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Notulensi Legal</span>
                    </a>
                    <a href="{{ route('legal.reports.index') }}" class="nav-link {{ Request::routeIs('legal.reports.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Laporan Kegiatan Bulanan</span>
                    </a>


                    <div class="sidebar-section-title">Portal Terbuka</div>
                    <a href="{{ route('library.index') }}" class="nav-link {{ Request::routeIs('library.*') ? 'active' : '' }}">
                        <span class="nav-link-content">Perpustakaan Digital</span>
                    </a>

                    <div class="sidebar-footer">
                        <div style="font-size:0.8rem; color:#94a3b8; margin-bottom:10px; padding:0 4px;">
                            Login: <strong>{{ auth()->user()->name }}</strong>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-logout" style="width:100%;">
                                Keluar
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
                            <span>{{ session('success') }}</span>
                            <button type="button" onclick="this.parentElement.remove()"
                                style="background:none;border:none;cursor:pointer;color:inherit;font-size:1.1rem;">×</button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-error">
                            <span>{{ session('error') }}</span>
                            <button type="button" onclick="this.parentElement.remove()"
                                style="background:none;border:none;cursor:pointer;color:inherit;font-size:1.1rem;">×</button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-error">
                            <div>
                                <strong>Terdapat kesalahan:</strong>
                                <ul style="margin:6px 0 0; padding-left:20px;">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>

        @include('layouts.sidebar-controls-script')
        @stack('scripts')
    </body>

</html>
