@extends(
    match (auth()->user()->role->nama) {
        'sekretariat' => 'sekretariat.layout',
        'humas' => 'humas.layout',
        default => 'staf.layout'
    }
)

@section('content')
    <div class="page-header">
        <h2 class="page-title">{{ $arsip->judul_file }}</h2>
        <p class="page-subtitle">Detail arsip dan informasi dokumen.</p>
    </div>

    <div class="card">
        <div style="margin-bottom: 20px;">
            <div class="table-wrapper" style="overflow: visible;">
                <table class="table detail-table" style="min-width: auto; border: none;">
                    <tbody>
                        <tr>
                            <th style="width: 200px; border-bottom: none;">Kategori</th>
                            <td style="border-bottom: none;">{{ $arsip->kategori->nama }}</td>
                        </tr>
                        <tr>
                            <th style="border-bottom: none;">Bidang</th>
                            <td style="border-bottom: none;">{{ $arsip->bidang->nama_bidang }}</td>
                        </tr>
                        <tr>
                            <th style="border-bottom: none;">Tanggal Dokumen</th>
                            <td style="border-bottom: none;">{{ \Carbon\Carbon::parse($arsip->tanggal)->format('d M Y') }}
                            </td>
                        </tr>
                        <tr>
                            <th style="border-bottom: none;">Diunggah oleh</th>
                            <td style="border-bottom: none;">{{ $arsip->uploader->name }}</td>
                        </tr>
                        <tr>
                            <th style="border-bottom: none;">Waktu Upload</th>
                            <td style="border-bottom: none;">{{ $arsip->created_at->format('d M Y, H:i') }} WIB</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display:flex; flex-wrap:wrap; gap: 12px; align-items:center;">
            <a href="{{ Storage::url($arsip->file_path) }}" target="_blank" class="button button-primary">Buka / Unduh
                Berkas</a>
            <a href="{{ route('arsip.index') }}" class="button button-secondary">Kembali ke daftar arsip</a>
        </div>
    </div>
@endsection
