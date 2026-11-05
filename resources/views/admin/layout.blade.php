<!DOCTYPE html>
<html lang="id">

    <head>
        @include('layouts.favicon')
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Super Admin - Sistem Kesekretariatan</title>
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
            select {
                font: inherit;
            }

            .app-shell {
                display: flex;
                min-height: 100vh;
            }

            .sidebar {
                width: 260px;
                min-width: 260px;
                background: #172033;
                color: #f8fafc;
                border-right: 3px solid #2563eb;
            }

            .sidebar-inner {
                padding: 28px 20px;
                display: flex;
                flex-direction: column;
                gap: 18px;
            }

            .sidebar-title {
                margin: 0 0 12px;
                font-size: 0.95rem;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #94a3b8;
            }

            .nav-link {
                display: block;
                width: 100%;
                padding: 12px 16px;
                border: 1px solid transparent;
                border-radius: 16px;
                color: #dbeafe;
                margin-bottom: 6px;
                transition: background 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
            }

            .nav-link:hover {
                background: #203455;
                border-color: #416a9e;
                transform: translateX(2px);
            }

            .nav-link.active {
                background: #244a82;
                border-color: #5b9bf2;
                color: #fff;
            }

            .sidebar-footer {
                margin-top: auto;
                padding-top: 12px;
                border-top: 1px solid #416a9e;
            }

            .main-content {
                flex: 1;
                padding: 30px 26px;
            }

            .page-container {
                max-width: 1180px;
                margin: 0 auto;
                display: flex;
                flex-direction: column;
                gap: 24px;
            }

            .page-header {
                display: flex;
                flex-direction: column;
                gap: 6px;
                margin-bottom: 12px;
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
                line-height: 1.6;
            }

            .topbar {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 12px;
                align-items: center;
                padding-bottom: 18px;
                border-bottom: 2px solid #d8c38f;
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
                font-size: 0.95rem;
            }

            .card {
                background: #fff;
                border: 1px solid #b8d6f5;
                border-radius: 26px;
                box-shadow: 0 18px 45px rgba(15, 23, 42, 0.05);
                padding: 26px;
            }

            .card-sm {
                padding: 20px;
            }

            .grid-2 {
                display: grid;
                grid-template-columns: minmax(0, 1.6fr) minmax(280px, 1fr);
                gap: 24px;
            }

            .section-title {
                margin: 0 0 8px;
                font-size: 1.15rem;
                font-weight: 700;
                color: #0f172a;
            }

            .section-meta {
                margin: 0;
                color: #64748b;
                font-size: 0.95rem;
            }

            .alert {
                border-radius: 22px;
                padding: 16px 18px;
                border: 1px solid transparent;
                font-size: 0.95rem;
            }

            .alert-success {
                background: #ecfdf5;
                border-color: #bbf7d0;
                color: #166534;
            }

            .alert-error {
                background: #f8d7da;
                border-color: #f5c2c7;
                color: #842029;
            }

            .category-list {
                display: grid;
                gap: 18px;
            }

            .category-card {
                background: #f8fbff;
                border: 1px solid #9ed8d0;
                border-radius: 22px;
                padding: 22px;
            }

            .category-card-header {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 14px;
                align-items: center;
                margin-bottom: 16px;
            }

            .category-title {
                margin: 0;
                font-size: 1.1rem;
                font-weight: 700;
                color: #0f172a;
            }

            .category-label {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 8px 12px;
                border-radius: 999px;
                background: #e2e8f0;
                color: #334155;
                font-size: 0.8rem;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .sub-list {
                border-top: 2px solid #b7e3dc;
                padding-top: 18px;
                margin-top: 18px;
                display: grid;
                gap: 12px;
            }

            .sub-item {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 14px;
                align-items: center;
                padding: 14px 0;
            }

            .sub-item p {
                margin: 0;
                color: #334155;
            }

            .sub-note {
                margin: 0;
                color: #64748b;
                font-size: 0.9rem;
            }

            .form-card {
                background: #fff;
                border: 1px solid #9ed8d0;
                border-radius: 22px;
                padding: 24px;
            }

            .table-card {
                background: #fff;
                border: 1px solid #b8d6f5;
                border-radius: 22px;
                padding: 24px;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
                min-width: 680px;
            }

            .table th,
            .table td {
                padding: 16px 18px;
                text-align: left;
                border-bottom: 1px solid #d5e2f0;
            }

            .table th {
                color: #0f172a;
                font-weight: 700;
                font-size: 0.95rem;
                letter-spacing: 0.01em;
            }

            .table td {
                color: #475569;
                font-size: 0.95rem;
            }

            .table tbody tr:hover {
                background: #f8fafc;
            }

            .table td .button {
                padding: 10px 16px;
                border-radius: 999px;
            }

            .hidden {
                display: none;
            }

            .form-label {
                display: block;
                margin-bottom: 10px;
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
                transition: border-color 0.2s ease, box-shadow 0.2s ease;
            }

            .form-control:focus {
                border-color: #0f766e;
                box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.14);
            }

            .button {
                border: none;
                border-radius: 999px;
                padding: 12px 18px;
                cursor: pointer;
                font-weight: 600;
                transition: transform 0.2s ease, background 0.2s ease;
            }

            .button-primary {
                background: #0f172a;
                color: #fff;
            }

            .button-primary:hover {
                background: #1e293b;
                transform: translateY(-1px);
            }

            .button-danger {
                background: #dc2626;
                color: #fff;
            }

            .button-danger:hover {
                background: #b91c1c;
                transform: translateY(-1px);
            }

            .button-secondary {
                background: #fff7e6;
                color: #92400e;
                border: 1px solid #f1d58d;
            }

            .middle-section {
                display: flex;
                flex-direction: column;
                gap: 16px;
            }

            .stat-grid {
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            }

            @media (max-width: 900px) {
                .stat-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (max-width: 560px) {
                .stat-grid {
                    grid-template-columns: 1fr;
                }
            }

            .category-card {
                background: #f8fafc;
                border: 1px solid #9ed8d0;
                border-radius: 22px;
                padding: 22px;
                transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
            }

            .category-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
                border-color: #0f766e;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.5);
                z-index: 39;
            }

            .sidebar {
                transition: transform 0.25s ease, width 0.25s ease;
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
                    padding-top: 78px;
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
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom: 8px;">
                        <img src="{{ asset('images/logo-icon-badge.png') }}" alt="Logo" style="height: 40px;">
                        <p class="sidebar-title" style="margin:0;">Admin Navigation</p>
                    </div>
                    <a href="{{ route('dashboard.admin') }}" class="nav-link {{ Request::routeIs('dashboard.admin') ? 'active' : '' }}">Dashboard</a>
                    <a href="{{ route('bidang.index') }}" class="nav-link {{ Request::routeIs('bidang.*') ? 'active' : '' }}">Bidang</a>
                    <a href="{{ route('kategori-arsip.index') }}" class="nav-link {{ Request::routeIs('kategori-arsip.*') ? 'active' : '' }}">Kategori Arsip</a>
                    <a href="{{ route('kategori-surat.index') }}" class="nav-link {{ Request::routeIs('kategori-surat.*') ? 'active' : '' }}">Kategori Surat</a>
                    <a href="{{ route('role.index') }}" class="nav-link {{ Request::routeIs('role.*') ? 'active' : '' }}">Role</a>
                    <a href="{{ route('pengguna.index') }}" class="nav-link {{ Request::routeIs('pengguna.*') ? 'active' : '' }}">Pengguna</a>
                    <a href="{{ route('log-aktivitas.index') }}" class="nav-link {{ Request::routeIs('log-aktivitas.*') ? 'active' : '' }}">Log Aktivitas</a>

                    <div class="sidebar-footer">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="button button-secondary" style="width:100%;">Logout</button>
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
                    <div class="topbar">
                        <div class="page-header">
                            <p class="page-subtitle">Super Admin</p>
                            <h1 class="page-title">{{ auth()->user()->name }}</h1>
                        </div>
                        <div class="user-badge">{{ auth()->user()->email ?? 'admin@example.com' }}</div>
                    </div>

                    @yield('content')
                </div>
            </main>
        </div>

        @include('layouts.sidebar-controls-script')
    </body>

</html>
