# Pengolahan upload sebelum penyimpanan

Diperbarui: 29 Oktober 2026.

Semua jalur upload menggunakan `NamedUploads` dan `UploadOptimizer`: validasi ukuran/jenis → proses salinan privat sementara → pilih hasil valid yang lebih kecil → simpan dengan nama `ID_nama-asli.ext`. Salinan sementara selalu dibersihkan, termasuk ketika penulisan gagal. Berkas operasional lama tidak diproses ulang otomatis.

## Batas upload

| Jenis | Batas sebelum kompresi |
|---|---|
| Dokumen/lampiran umum | 8 MB per file |
| Dokumen utama Hukum (PDF/DOC/DOCX) | 8 MB per file |
| Dokumen Tata Kelola (PDF saja) | 5 MB per file |
| Foto dokumentasi | 4 MB per foto |
| Seluruh lampiran CSR per penyimpanan | 14 MB |

Batas PHP dan Nginx tetap 20 MB per request untuk menampung multipart dan kombinasi lampiran. Batas per file di atas ditegakkan Laravel sebelum file diproses.

## Pengolahan berdasarkan format

- JPG: decode/encode ulang dengan GD, kualitas 90, progressive; dimensi dipertahankan dengan koreksi orientasi EXIF. EXIF diperlukan agar foto ponsel tidak berputar setelah metadata dibuang.
- PNG: decode/encode ulang dengan GD, kompresi level 9, alpha/transparansi dipertahankan.
- PDF: qpdf meregenerasi struktur/object stream dan mengompresi ulang stream dengan level 9. Tidak memakai rasterisasi, downsampling, atau konversi gambar ke JPEG. Hasil diperiksa dengan `qpdf --check`.
- DOCX/XLSX: ZIP package dibangun ulang menggunakan DEFLATE level 9; setiap isi part/XML tetap sama. Tidak melakukan konversi tata letak melalui Office/LibreOffice.
- Dokumen bertanda tangan: PDF dengan indikator ByteRange/field signature dan Office dengan `_xmlsignatures/` disimpan byte-identik. PDF terenkripsi juga dipertahankan karena tidak dapat diolah tanpa kredensial.
- DOC/XLS lama serta format gambar lain yang belum didukung disimpan utuh. Membuat ulang format biner lama memerlukan konversi yang berpotensi mengubah layout/formula; konversi itu tidak diterapkan.

Tidak dijanjikan setiap upload menjadi lebih kecil. Berkas yang sudah optimal, kandidat yang lebih besar, atau berkas yang tidak bisa dioptimalkan secara aman menggunakan file asli. Batas sebelum upload tetap berlaku untuk dokumen bertanda tangan dan file asli tersebut.

Untuk membatasi memori, pemrosesan GD hanya sampai 12 juta piksel; gambar lebih besar tetap disimpan utuh. ZIP dibatasi 5.000 entri, 16 MB per part, total 100 MB setelah dekompresi. Proses qpdf dibatasi waktu. Isi dokumen tidak dikirim ke layanan eksternal.

## Runtime

Dockerfile memasang qpdf; GD, EXIF, dan ZIP sudah tersedia pada image. Setelah menyalakan Docker Desktop, terapkan:

```sh
docker compose up -d --build app scheduler webserver
docker compose exec app php artisan optimize:clear
```

Tidak ada migrasi database baru. Rebuild tidak menghapus volume database.

Pada Windows lokal task ini memasang qpdf portabel resmi di `tools/qpdf/bin/qpdf.exe` dengan SHA-256 yang diperiksa dari rilis resminya. Folder binary diabaikan Git dan Docker. Config memilih binary lokal itu otomatis; lingkungan lain menggunakan `qpdf` dari PATH, atau dapat mengatur `UPLOAD_QPDF_BINARY` dengan path absolut. PHP lokal `C:/php/php.ini` telah diaktifkan EXIF-nya; proses PHP lama perlu dimulai ulang agar ekstensi baru terbaca.

PDF yang perlu dioptimalkan ditolak dengan pesan validasi jika qpdf belum tersedia, agar server tidak diam-diam mengklaim sudah memprosesnya. Pengecualian testing menggunakan `UPLOAD_REQUIRE_PDF_OPTIMIZER=false` secara default; tes integrasi PDF mengaktifkannya dan memakai binary sungguhan.

Docker runtime belum diuji dalam task ini karena Docker Engine tidak sedang berjalan. Pengujian lokal mencakup qpdf Windows sungguhan, keutuhan part DOCX, PNG lossless, JPEG/EXIF, dokumen bertanda tangan byte-identik, batas ukuran upload, dan regresi modul.

Panduan pilihan PDF: [dokumentasi resmi qpdf](https://qpdf.readthedocs.io/en/stable/cli.html#optimizing-file-size). Rilis Windows berasal dari [qpdf/qpdf](https://github.com/qpdf/qpdf/releases).
