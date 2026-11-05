@extends('humas.layout')
@section('content')
    <div class="topbar">
        <div class="page-header">
            <h1 class="page-title">{{ $csr->nama_kegiatan }}</h1>
            <p class="page-subtitle">Detail Arsip CSR Humas</p>
        </div>
        <div class="humas-actions"><a class="btn btn-primary" href="{{ route('humas.csr.edit', $csr) }}">Edit Arsip</a><a
                class="btn btn-secondary" href="{{ route('humas.csr.index') }}">Kembali</a></div>
    </div>
    <div class="card">
        <dl class="humas-detail">
            @foreach (['pemohon' => 'Pemohon', 'nomor_surat' => 'Nomor Surat', 'nama_kegiatan' => 'Nama Kegiatan', 'keluar' => 'Keluar / Tindak Lanjut', 'nomor_voucher' => 'Nomor Voucher', 'keterangan' => 'Keterangan'] as $field => $label)
                <div>
                    <dt>{{ $label }}</dt>
                    <dd>{{ $csr->$field ?: '-' }}</dd>
                </div>
            @endforeach
            <div>
                <dt>Tanggal Proses</dt>
                <dd>{{ $csr->tanggal_proses->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt>Kategori</dt>
                <dd>{{ $csr->kategori->nama }}</dd>
            </div>
            <div>
                <dt>Status</dt>
                <dd>@include('humas.status')</dd>
            </div>
            <div>
                <dt>Luaran</dt>
                <dd>@include('humas.luaran')</dd>
            </div>
            <div>
                <dt>Nominal</dt>
                <dd>Rp{{ number_format($csr->nominal, 2, ',', '.') }}</dd>
            </div>
            <div>
                <dt>Dibuat Oleh</dt>
                <dd>{{ $csr->creator->name }}</dd>
            </div>
        </dl>
    </div>
    <div class="card">
        <h2 class="card-title">Lampiran Arsip</h2>
        <p class="humas-help">PDF dan gambar dapat dibuka langsung. DOCX ditampilkan sebagai preview teks.</p>
        <ul class="humas-files">
            @forelse($csr->attachments as $attachment)
                <li><strong>{{ ucfirst($attachment->jenis) }}: {{ $attachment->original_name }}</strong>
                    <div class="humas-actions" style="margin-top:10px"><a class="btn btn-secondary btn-sm"
                            href="{{ route('humas.csr.attachment', [$csr, $attachment, 'preview' => 1]) }}" target="_blank"
                            rel="noopener">Lihat Berkas</a><a class="btn btn-primary btn-sm"
                            href="{{ route('humas.csr.attachment', [$csr, $attachment, 'download' => 1]) }}">Unduh</a></div>
            </li>@empty<li>Tidak ada lampiran.</li>
            @endforelse
        </ul>
    </div>
    <div>
        <form method="POST" action="{{ route('humas.csr.destroy', $csr) }}"
            onsubmit="return confirm('Hapus arsip ini beserta seluruh lampirannya?')">@csrf @method('DELETE')<button
                class="btn btn-danger" type="submit">Hapus Arsip CSR</button></form>
    </div>
@endsection
