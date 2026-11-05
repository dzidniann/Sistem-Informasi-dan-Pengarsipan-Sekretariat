@extends('sekretariat.layout')

@section('content')
    <div class="page-container">

        <a href="{{ route('nomor-surat.index') }}" style="color:#64748b; font-size:0.9rem;">&larr; Kembali ke daftar
            riwayat</a>

        <div class="card">
            <div class="page-header">
                <h2 class="page-title">{{ $surat->full_nomor }}</h2>
                <p class="page-subtitle">
                    Kategori: {{ $surat->kategori->nama_kategori }}
                    @if ($surat->is_backdate)
                        <span class="category-label" style="background:#fef3c7; color:#92400e; margin-left:8px;">Sisipan /
                            THB</span>
                    @endif
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-error">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="form-card">
            <h3 class="section-title">Detail & Edit Surat</h3>

            <form method="POST" action="{{ route('nomor-surat.update', $surat) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <label class="form-label">Tanggal Surat</label>
                <input type="date" name="tanggal_surat" class="form-control"
                    value="{{ $surat->tanggal_surat?->format('Y-m-d') }}">

                <label class="form-label" style="margin-top:14px;">Perihal</label>
                <input type="text" name="perihal" class="form-control" value="{{ $surat->perihal }}">

                <label class="form-label" style="margin-top:14px;">Tujuan</label>
                <input type="text" name="tujuan" class="form-control" value="{{ $surat->tujuan }}">

                <label class="form-label" style="margin-top:14px;">Catatan Tambahan</label>
                <textarea name="catatan" class="form-control" rows="3">{{ $surat->catatan }}</textarea>

                <label class="form-label" style="margin-top:14px;">Berkas Surat</label>
                @if ($surat->file_path)
                    <p class="section-meta" style="margin-bottom:8px;">
                        File saat ini: <a href="{{ Storage::url($surat->file_path) }}" target="_blank">Lihat berkas</a>
                    </p>
                @else
                    <p class="section-meta" style="margin-bottom:8px;">Belum ada berkas diunggah.</p>
                @endif
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <p class="section-meta" style="margin-top:6px;">Kosongkan jika tidak ingin mengganti berkas.</p>

                <button type="submit" class="button button-primary" style="margin-top:18px;">Simpan Perubahan</button>
            </form>
        </div>

        <div class="table-card">
            <h3 class="section-title" style="margin-bottom:10px;">Informasi Rilis</h3>
            <div class="table-wrapper">
                <table class="table detail-table" style="min-width:auto;">
                    <tr>
                        <td style="width:200px;"><strong>Dirilis oleh</strong></td>
                        <td>{{ $surat->perilis->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Waktu rilis</strong></td>
                        <td>{{ $surat->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Terakhir diubah</strong></td>
                        <td>{{ $surat->updated_at->format('d M Y, H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
