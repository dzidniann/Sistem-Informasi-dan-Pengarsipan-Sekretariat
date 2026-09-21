@extends('admin.layout')

@section('content')
    <div class="page-container">

        <div class="card" style="background: linear-gradient(135deg, #0f172a 0%, #2a5298 100%); color: white; border-radius: 12px; padding: 30px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 20px;">

                <div style="width: 70px; height: 70px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 10px; flex-shrink: 0;">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="Logo"
                        style="width: 100%; height: auto;"
                    >
                </div>

                <div>
                    <h2 style="margin: 0 0 8px; font-size: 24px;">
                        Halo, Selamat Datang Kembali, {{ auth()->user()->name }}! 👋
                    </h2>

                    <p style="margin: 0; font-size: 15px; opacity: 0.9;">
                        Ringkasan data dan akses cepat ke pengaturan sistem.
                    </p>
                </div>

            </div>
        </div>


        <div class="stat-grid">

            <div class="card">
                <p class="section-meta" style="margin:0 0 6px;">
                    Total Pengguna
                </p>

                <p style="margin:0; font-size:28px; font-weight:700; color:#0f172a;">
                    {{ $totalPengguna }}
                </p>
            </div>

            <div class="card">
                <p class="section-meta" style="margin:0 0 6px;">
                    Bidang
                </p>

                <p style="margin:0; font-size:28px; font-weight:700; color:#0f172a;">
                    {{ $totalBidang }}
                </p>
            </div>

            <div class="card">
                <p class="section-meta" style="margin:0 0 6px;">
                    Kategori Arsip
                </p>

                <p style="margin:0; font-size:28px; font-weight:700; color:#0f172a;">
                    {{ $totalKategori }}
                </p>
            </div>

            <div class="card">
                <p class="section-meta" style="margin:0 0 6px;">
                    Arsip Tersimpan
                </p>

                <p style="margin:0; font-size:28px; font-weight:700; color:#0f172a;">
                    {{ $totalArsip }}
                </p>
            </div>

        </div>


        <div>

            <h3 class="section-title" style="margin-bottom:12px;">
                Kelola Master Data
            </h3>

            <div class="stat-grid">

                <a href="{{ route('kategori-surat.index') }}"
                   class="category-card"
                   style="display:block;">

                    <p class="category-title" style="margin:0 0 4px;">
                        Kategori Surat
                    </p>

                    <p class="section-meta" style="margin:0;">
                        Format nomor & jenis surat
                    </p>

                </a>


                <a href="{{ route('bidang.index') }}"
                   class="category-card"
                   style="display:block;">

                    <p class="category-title" style="margin:0 0 4px;">
                        Bidang
                    </p>

                    <p class="section-meta" style="margin:0;">
                        Kelola daftar divisi
                    </p>

                </a>


                <a href="{{ route('kategori-arsip.index') }}"
                   class="category-card"
                   style="display:block;">

                    <p class="category-title" style="margin:0 0 4px;">
                        Kategori Arsip
                    </p>

                    <p class="section-meta" style="margin:0;">
                        Folder per bidang
                    </p>

                </a>


                <a href="{{ route('role.index') }}"
                   class="category-card"
                   style="display:block;">

                    <p class="category-title" style="margin:0 0 4px;">
                        Role
                    </p>

                    <p class="section-meta" style="margin:0;">
                        Lihat hak akses
                    </p>

                </a>


                <a href="{{ route('pengguna.index') }}"
                   class="category-card"
                   style="display:block;">

                    <p class="category-title" style="margin:0 0 4px;">
                        Pengguna
                    </p>

                    <p class="section-meta" style="margin:0;">
                        Akun, role, dan bidang
                    </p>
                </a>

                <a href="{{ route('log-aktivitas.index') }}"
                    class="category-card"
                    style="display:block;">

                    <p class="category-title" style="margin:0 0 4px;">
                        Log Aktivitas
                    </p>

                    <p class="section-meta" style="margin:0;">
                        Riwayat aktivitas pengguna
                    </p>
                </a>

            </div>
        </div>


        <div class="table-card">

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">

                <h3 class="section-title" style="margin:0;">
                    Aktivitas Terbaru
                </h3>

                <a
                    href="{{ route('log-aktivitas.index') }}"
                    class="button button-secondary"
                    style="padding:8px 14px; font-size:0.85rem;"
                >
                    Lihat semua
                </a>

            </div>


            @forelse($aktivitasTerbaru as $log)

                <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #d8c38f; font-size:0.95rem;">

                    <span>

                        <strong>
                            {{ $log->causer->name ?? 'Pengguna' }}
                        </strong>

                        melakukan

                        <span style="color:#475569;">
                            {{ ucfirst($log->description) }}
                        </span>

                        @if($log->subject_type)
                            pada
                            <strong>
                                {{ class_basename($log->subject_type) }}
                            </strong>
                        @endif

                    </span>

                    <span style="color:#94a3b8; font-size:0.85rem;">
                        {{ $log->created_at->diffForHumans() }}
                    </span>

                </div>

            @empty

                <p class="section-meta">
                    Belum ada aktivitas tercatat.
                </p>

            @endforelse

        </div>

    </div>
@endsection