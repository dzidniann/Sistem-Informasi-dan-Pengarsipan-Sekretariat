@extends('staf.layout')

@section('content')
    <div class="page-container">

        <!-- Banner Ucapan Selamat Datang -->
        <div class="card"
            style="background: linear-gradient(135deg, #0f172a 0%, #2a5298 100%); color: white; border-radius: 12px; padding: 30px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <!-- Logo Aplikasi -->
                <div
                    style="width: 70px; height: 70px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 10px; flex-shrink: 0;">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 100%; height: auto;">
                </div>
                <div>
                    <h2 style="margin: 0 0 8px; font-size: 24px;">Halo, Selamat Datang Kembali, {{ auth()->user()->name }}!
                        👋</h2>
                    <p style="margin: 0; font-size: 15px; opacity: 0.9;">Semoga harimu menyenangkan.Berikut adalah fitur
                        Sekretariatan yang dapat anda akses dan fungsikan.</p>
                </div>
            </div>
        </div>

        <div>
            <h3 class="section-title" style="margin-bottom:12px;">Akses Cepat</h3>
            <div class="stat-grid" style="grid-template-columns: repeat(2, 1fr);">
                <a href="{{ route('arsip.index') }}" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Upload Berkas</p>
                    <p class="section-meta" style="margin:0;">Unggah dan kelola arsip bidang</p>
                </a>
                <a href="{{ route('arsip.index') }}#daftar-arsip" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Arsip Bidang</p>
                    <p class="section-meta" style="margin:0;">Telusuri arsip bidang
                        {{ auth()->user()->bidang->nama_bidang }}</p>
                </a>

                <a href="{{ route('log-aktivitas.index') }}" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Log Aktivitas</p>
                    <p class="section-meta" style="margin:0;">Riwayat aktivitas pengguna</p>
                </a>

            </div>
        </div>

    </div>
@endsection
