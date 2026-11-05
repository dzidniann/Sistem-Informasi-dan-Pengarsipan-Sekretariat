# Preview dan download berkas

Diperbarui: **27 Oktober 2026**, berdasarkan source aplikasi. Jalur penyimpanan publik dan privat tetap berbeda; perubahan desain tidak memindahkan file lama.

## Lokasi penyimpanan

| Modul | Storage | Cara akses |
|---|---|---|
| Arsip, Nomor Surat, Notulensi Sekretariat | Disk `public`, `storage/app/public` | URL `/storage/...`; beberapa halaman juga menyediakan controller pembuka berkas |
| Dokumen Hukum, lampiran konsultasi dan pesan diskusi | Disk `public`, `storage/app/public` | URL `/storage/...`, termasuk tombol pada Perpustakaan |
| Kegiatan dan Notulensi Sub Bidang Hukum | Disk `local`, `storage/app/legal-private` | Endpoint lampiran yang memerlukan login dan role berwenang |
| PDF laporan kegiatan bulanan Hukum | Dibuat DomPDF dari database dan berkas lokal ketika diminta | Endpoint preview/download laporan, bukan URL upload `/storage` |
| Surat/proposal, bukti, foto CSR Humas | Disk `local`, `storage/app/humas-private` | Endpoint role Humas yang memeriksa bidang dan kepemilikan lampiran pada record |
| Excel/PDF rekap CSR Humas | Dibuat dari database mengikuti filter | `/humas/reports/excel` dan `/humas/reports/pdf`, memerlukan login Humas |

Dokumen Hukum utama (`legal_documents.file_path`) tidak sama dengan lampiran privat kegiatan/notulensi. Memilih dokumen Hukum pada sebuah kegiatan tidak memindahkan file dokumen itu ke storage privat.

Lampiran CSR menggunakan `/humas/csr/{csr}/attachments/{attachment}`. PDF/JPG/PNG dapat ditampilkan inline; `?download=1` memaksa unduhan. Word diunduh. `HumasPrivateFiles` memvalidasi prefix, traversal, `realpath`, keberadaan file, dan batas direktori privat. Detail batas unggahan dan transaksi file terdapat di [HUMAS_CSR_MODULE.md](HUMAS_CSR_MODULE.md).

## Penghubung storage publik

Untuk `php artisan serve` atau Apache, setelah checkout/setup baru jalankan dari root aplikasi:

```powershell
php artisan storage:link
```

Pada Windows, pastikan `public/storage` benar-benar menunjuk ke `storage/app/public`. Laravel dapat mencetak pesan sukses walaupun perintah Windows menampilkan `Access is denied`; periksa junction/link dan hak aksesnya. Jangan menyalin upload ke folder public secara manual untuk menggantikan penghubung.

Konfigurasi Nginx Docker di `docker/nginx/default.conf` menggunakan alias `/storage/` ke `/var/www/html/storage/app/public/`, sehingga tidak bergantung pada junction Windows. Setelah perubahan konfigurasi server:

```powershell
docker compose exec webserver nginx -t
docker compose exec webserver nginx -s reload
```

Reload hanya dilakukan setelah pemeriksaan konfigurasi berhasil. Volume aplikasi/webserver harus menunjuk ke data upload yang sama.

## Lampiran privat kegiatan/notulensi Hukum

Route yang tersedia:

- `legal.activities.attachment`: `/legal/activities/{id}/attachments/{field}`; field yang diizinkan `dokumen_pendukung` atau `foto_dokumentasi`.
- `legal.notulensi.attachment`: `/legal/notulensi/{id}/attachments/{field}`; field `file_path` atau `foto_path`.

Keduanya memakai `auth`, `aktif`, `nocache`, dan `LegalModuleAccess` untuk `legal-admin`/`super-admin`. Pengguna tidak mengirim path file bebas. Controller mengambil path dari record, lalu `LegalPrivateFiles::path()` memeriksa prefix, traversal, hasil `realpath`, keberadaan file, dan batas root privat.

Tanpa `download=1`, PDF/JPEG/PNG dikirim inline. Dengan `download=1`, file diunduh. Word selalu menjadi unduhan; modal preview menyediakan fallback. Respons menyertakan `X-Content-Type-Options: nosniff` dan `Cache-Control: private, no-store`.

Preview menggunakan dialog bersama di `resources/views/legal/notulensi/preview.blade.php`. Nama file asli yang disimpan terpisah dipakai untuk label/deteksi jenis; endpoint tetap memakai ID record dan field. `legal_notulensis` dan `notulensis` Sekretariat memiliki tabel/controller berbeda, sehingga ID yang sama tidak boleh dipertukarkan pada URL preview.

## PDF laporan bulanan

`legal.reports.monthly.preview` mengirim PDF inline; `legal.reports.monthly.pdf` mengirim unduhan. Keduanya menerima `bulan` dan `tahun`, serta mengambil kegiatan menurut `tanggal_kegiatan`.

PDF hanya memakai dokumentasi dari kegiatan, bukan file/foto notulensi. `LegalPrivateFiles::image()` menerima JPEG/PNG dari root privat. PNG memerlukan GD; jika GD tidak tersedia, thumbnail dilewati tetapi nama berkas tetap dicantumkan. GD CLI lokal terdeteksi aktif tanggal 20 Oktober 2026. Dockerfile memasang GD, tetapi proses PHP pada server perlu diverifikasi setelah build/restart.

## Jika berkas tidak ditemukan

1. Tentukan apakah URL berasal dari storage publik, endpoint privat, atau pembangkitan laporan.
2. Untuk URL publik, cocokkan nilai path di database dengan file di `storage/app/public`, lalu periksa link/alias dan volume server.
3. Untuk endpoint privat, cocokkan ID record, jenis modul, field yang diizinkan, dan keberadaan file di root `legal-private`. Path tidak sah atau file hilang memang menghasilkan 404.
4. Jika file fisik hilang, pulihkan backup atau unggah ulang melalui modul yang sesuai. Link storage tidak dapat memulihkan file yang hilang.
5. Untuk PDF laporan yang gagal dibuat, periksa parameter periode, log Laravel, hak baca berkas, dan konfigurasi PHP; masalahnya tidak selalu berupa link `/storage`.

## Batas keamanan dan verifikasi

URL file publik masih dapat dibuka langsung jika diketahui, tanpa mengikuti otorisasi metadata halaman. Menyembunyikan dokumen dari Perpustakaan atau mematikan flag publikasi tidak mencabut URL publiknya. Hal ini tetap tercatat sebagai **A01** pada [laporan audit](AUDIT_SISTEM_2026-10-16.md); pembaruan dokumentasi ini tidak menyelesaikannya.

Catatan pemeriksaan 16 Oktober 2026: junction lokal berhasil dibuat dan sampel PDF Arsip, Notulensi Sekretariat, Dokumen Hukum, Konsultasi, serta lampiran diskusi merespons `200 application/pdf`. Itu hasil pada tanggal tersebut, bukan pemeriksaan HTTP ulang tanggal 20 Oktober.

Pada 20 Oktober 2026, suite regresi terpilih lulus 29 test / 481 assertion, termasuk berkas privat dan PDF Legal; perintah dan cakupannya ada di [LEGAL_MODULE.md](LEGAL_MODULE.md). Runtime Docker/Nginx dan seluruh URL upload operasional tidak diuji ulang dalam pembaruan dokumentasi ini.


## Penamaan unggahan baru (29 Oktober 2026)

Semua jalur upload menggunakan `NamedUploads`. Nama fisik berkas adalah `ID-record_nama-asli.ext`, misalnya `125_Proposal_Bantuan.pdf`. Spasi/karakter khusus dirapikan agar aman untuk path. ID diperoleh setelah record dibuat dalam transaksi; lampiran CSR memakai ID lampirannya, pesan konsultasi memakai ID pesan, dan dokumen lain memakai primary key record masing-masing.

Berkas dengan nama sama pada record yang sama ditempatkan di direktori revisi terpisah, tanpa menambahkan nomor atau kode acak pada nama file. Sebelum penyimpanan, `UploadOptimizer` mengolah salinan sementara dan memilih hasil yang valid serta lebih kecil; dokumen bertanda tangan dipertahankan utuh. File lama dengan nama acak tetap dapat diakses melalui path database yang sudah ada; perubahan ini berlaku untuk upload baru. Nama asli yang ditampilkan pada metadata lampiran tetap dipertahankan.

Cakupan: Arsip, Nomor Surat, Notulensi Sekretariat, Dokumen Hukum, Kegiatan/Notulensi Hukum, lampiran permohonan dan diskusi konsultasi, serta semua lampiran CSR Humas. Tidak memerlukan migrasi database. Pengolahan PDF menambahkan dependency qpdf pada image Docker, sehingga image perlu dibangun ulang. Lihat [UPLOAD_PROCESSING.md](UPLOAD_PROCESSING.md).


## Lampiran Dokumen Tata Kelola

PDF Tata Kelola disimpan privat pada disk local di `legal-private/governance-documents`, dibatasi 5 MB, dengan MIME dan ekstensi PDF. Route `legal.governance-documents.file` memeriksa autentikasi, akun aktif, role legal-admin/super-admin, serta path kanonis di root privat. Respons default inline PDF; `?download=1` menghasilkan unduhan. Berkas baru mengikuti pipeline optimasi dan nama berawalan ID. Detail schema, route, dan pengujian ada di [LEGAL_MODULE.md](LEGAL_MODULE.md).
