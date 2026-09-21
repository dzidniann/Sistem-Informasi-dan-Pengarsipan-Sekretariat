@extends('sekretariat.layout')

@section('content')
    <div class="page-container">

        <!-- Banner Ucapan Selamat Datang -->
        <div class="card" style="background: linear-gradient(135deg, #0f172a 0%, #2a5298 100%); color: white; border-radius: 12px; padding: 30px; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <!-- Logo Aplikasi -->
                <div style="width: 70px; height: 70px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 10px; flex-shrink: 0;">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 100%; height: auto;">
                </div>
                <div>
                    <h2 style="margin: 0 0 8px; font-size: 24px;">Halo, Selamat Datang Kembali, {{ auth()->user()->name }}! 👋</h2>
                    <p style="margin: 0; font-size: 15px; opacity: 0.9;">Semoga harimu menyenangkan. Berikut adalah ringkasan data dan akses cepat ke fitur kesekretariatan hari ini.</p>
                </div>
            </div>
        </div>

        <div>
            <h3 class="section-title" style="margin-bottom:12px;">Akses Cepat</h3>
            <div class="stat-grid">
                
                <a href="{{ route('arsip.index') }}" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Upload Berkas</p>
                    <p class="section-meta" style="margin:0;">Unggah dan kelola arsip</p>
                </a>
                
                <a href="{{ route('nomor-surat.index') }}" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Rilis Nomor Surat</p>
                    <p class="section-meta" style="margin:0;">Ambil nomor surat resmi</p>
                </a>

                <a href="{{ route('arsip.index', ['cakupan' => 'sendiri']) }}#daftar-arsip" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Arsip Sekretariat</p>
                    <p class="section-meta" style="margin:0;">Arsip milik sekretariat</p>
                </a>
                
                <a href="{{ route('notulensi.index') }}" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Notulensi</p>
                    <p class="section-meta" style="margin:0;">Catat hasil rapat</p>
                </a>
                
                <a href="{{ route('laporan.index') }}" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Laporan Bulanan</p>
                    <p class="section-meta" style="margin:0;">Rekap dan ekspor PDF</p>
                </a>

                 <a href="{{ route('log-aktivitas.index') }}" class="category-card" style="display:block;">
                    <p class="category-title" style="margin:0 0 4px;">Log Aktivitas</p>
                    <p class="section-meta" style="margin:0;">Riwayat aktivitas pengguna</p>
                </a>
            </div>
        </div>

        <div class="table-card">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:14px; margin-bottom:16px;">
                <div>
                    <h3 class="section-title" style="margin:0;">Rekap Arsip Bulanan</h3>
                    <p class="section-meta" id="periode-label" style="margin:4px 0 0;">Memuat...</p>
                </div>
                <div style="dis play:flex; gap:8px;">
                    <select id="pilih-bulan" class="form-control" style="width:auto; text-align: center;">
                        @foreach(range(1,12) as $i)
                            <option value="{{ $i }}" {{ $i == now()->month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                    <select id="pilih-tahun" class="form-control" style="width:auto; text-align: center;">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="text-align: center;">Bidang</th>
                            <th style="text-align: center;" >Jumlah Arsip</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-rekap">
                        <tr><td colspan="2">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="chart-card">
            <h3 class="section-title" style="margin:0 0 16px;">Arsip 6 Bulan Terakhir</h3>
            <canvas id="chartArsip"></canvas>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        async function muatRekapBulanan() {
            const bulan = document.getElementById('pilih-bulan').value;
            const tahun = document.getElementById('pilih-tahun').value;

            const response = await fetch(`{{ route('dashboard.rekap-bulanan') }}?bulan=${bulan}&tahun=${tahun}`);
            const data = await response.json();

            document.getElementById('periode-label').textContent =
                `Total ${data.totalKeseluruhan} arsip pada ${data.namaBulan} ${data.tahun}`;

            const tbody = document.getElementById('tbody-rekap');
            tbody.innerHTML = '';

            if (data.rekap.length === 0) {
                tbody.innerHTML = '<tr><td colspan="2" style="text-align:center; color:#94a3b8;">Tidak ada arsip pada bulan ini</td></tr>';
                return;
            }

            data.rekap.forEach(r => {
                const row = document.createElement('tr');
                row.innerHTML = `<td style="text-align: center;">${r.bidang}</td><td style="text-align: center;">${r.total}</td>`;
                tbody.appendChild(row);
            });
        }

        document.getElementById('pilih-bulan').addEventListener('change', muatRekapBulanan);
        document.getElementById('pilih-tahun').addEventListener('change', muatRekapBulanan);

        muatRekapBulanan();

        new Chart(document.getElementById('chartArsip'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($bulanData)->pluck('label')) !!},
                datasets: [{
                    label: 'Jumlah Arsip',
                    data: {!! json_encode(collect($bulanData)->pluck('total')) !!},
                    backgroundColor: '#4A90D9'
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
@endsection