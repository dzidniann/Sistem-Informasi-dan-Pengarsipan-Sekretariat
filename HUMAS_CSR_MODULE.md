# Modul Pengarsipan CSR Humas

Diperbarui: **3 November 2026**.

## Mulai menggunakan

1. Login sebagai Admin. Di **Pengguna**, buat akun dengan role **humas** dan bidang **Humas**.
2. Sepuluh kategori CSR mengikuti template: **CSR/TJSLP, SPONSORSHIP, UNDANGAN, INFORMASI, IKLAN, PENGADAAN, SARANA/PRASARANA, BIASA, SURAT, NONE**. Master kategori tersebut disiapkan oleh `HumasCsrTemplateSeeder` pada bidang Humas. Form CSR hanya menawarkan daftar ini; kategori bidang lain tidak berubah.
3. Login menggunakan akun Humas, lalu buka **Input Arsip CSR**.
4. Isi data dan unggah surat/proposal. Setelah disimpan, arsip tersedia di **Riwayat Arsip CSR** untuk dilihat, diubah, atau dihapus.
5. Buka **Rekap / Laporan CSR**, pilih tahun/bulan dan filter lain, lalu unduh Excel atau PDF.

Role dan bidang Humas serta sepuluh kategori template telah dipasang pada database lokal dan Docker. Tidak dibuat akun maupun arsip contoh. Data spreadsheet lama tidak diimpor dan tidak disinkronkan otomatis.

## Data arsip

Format ekspor mengikuti 12 kolom spreadsheet referensi, dengan kelompok **Informasi Pemohon** dan **Informasi Permohonan**.

| Kolom ekspor | Pengisian |
|---|---|
| NO | Nomor urut otomatis pada hasil rekap |
| PEMOHON | Wajib, maksimal 255 karakter |
| NO SURAT | Opsional, teks; nol di depan tetap tersimpan |
| NAMA KEGIATAN | Wajib, maksimal 255 karakter |
| PROSES | Tanggal proses wajib; menentukan periode rekap, antara 1900–2100 |
| KELUAR | Teks bebas opsional, maksimal 5.000 karakter; diisi pengguna |
| NO VOUCHER | Opsional, teks; nol di depan tetap tersimpan |
| STATUS | DIBANTU, BELUM DIBANTU, DIBUATKAN, DIKETAHUI, DILAKSANAKAN, DIGANTI, DIPENDING, atau - |
| KATEGORI | Wajib; salah satu dari sepuluh kategori template, pada bidang pengguna |
| LUARAN | DANA, AIR MINERAL, BARANG, atau - |
| NOMINAL | Angka maksimal dua desimal; wajib lebih dari nol untuk luaran Dana; selain Dana boleh kosong dan disimpan sebagai nol |
| KETERANGAN | Teks bebas opsional, maksimal 10.000 karakter |

Kolom **Keluar** tidak mengubah Status secara otomatis. Nominal diisi tanpa `Rp` atau pemisah ribuan, misalnya `1000000` atau `1000000.25`.

## Lampiran dan akses

- Surat/proposal wajib saat membuat arsip: PDF, DOC, atau DOCX, maksimal 8 MB.
- Bukti bantuan/voucher opsional: PDF, DOC, DOCX, JPG, atau PNG, maksimal 8 MB.
- Foto dokumentasi: maksimal lima foto per penyimpanan, JPG/PNG, maksimal 4 MB per foto dan 6.000 × 6.000 piksel.
- Total unggahan per penyimpanan maksimal 14 MB. Pada edit, lampiran baru ditambahkan; lampiran lama dihapus hanya jika dicentang. Minimal satu lampiran harus tersisa.
- Lampiran disimpan pada disk `local`, di `storage/app/humas-private/{id}`. Akses melalui endpoint terautentikasi; tidak memakai URL publik `/storage`.
- Preview tersedia untuk PDF/JPG/PNG dan preview teks DOCX melalui tombol Lihat Berkas. Tata letak, tabel, dan gambar DOCX tidak direproduksi. DOC lama atau DOCX yang tidak dapat dibaca menampilkan pesan fallback; berkas asli tetap dapat diunduh lewat tombol Unduh. Record, kategori, dan lampiran dibatasi pada bidang pengguna, termasuk akses lewat URL langsung. Log Aktivitas Humas hanya menampilkan aktivitas pengguna sendiri.
- Penyimpanan metadata dan lampiran menggunakan transaksi database. Jika gagal, file unggahan baru dibersihkan; file lama baru dihapus setelah transaksi berhasil.

## Rekap dan ekspor

Dashboard menampilkan jumlah arsip, jumlah berstatus Dibantu, dan total nominal tahun berjalan. Riwayat dan rekap mendukung pencarian, tahun, bulan, kategori, status, serta luaran.

**Excel:** file `.xlsx` asli, satu sheet jika memilih bulan, atau 12 sheet Januari–Desember untuk seluruh tahun. Tata letak mengambil format tab FEBRUARI pada [spreadsheet rujukan](https://docs.google.com/spreadsheets/d/1DLJlC4uGpX_mIOmWKTnc4oXwFW6nzQcYC-LqW1ZWTjk/edit#gid=384698074): baris pertama kosong, nama bulan pada `A2:D2`, header putih Poppins, kelompok `A3:A4`, `B3:D3`, `E3:L3`, lebar kolom asli, tanggal `dd/mm/yy`, dan tampilan nominal rupiah tanpa desimal. Nilai nominal asli tetap bertipe angka dan menyimpan dua desimal bila diisi. Nomor surat/voucher tetap teks, termasuk nol di depan. Isian yang diawali `=` tetap disimpan sebagai teks.

Dropdown status, kategori, dan luaran tersedia pada kolom H, I, J sampai paling sedikit baris 131 dan meluas jika data lebih banyak. Warna status/luaran diterapkan langsung dan melalui conditional formatting agar mengikuti perubahan pilihan. Chip membulat Google Sheets tidak tersedia dalam XLSX standar; hasil memakai dropdown dan warna sel Excel. Judul tambahan, total `SUM`, autofilter, dan freeze header dari ekspor lama dihapus karena tidak ada pada tab rujukan. PDF tetap memiliki total nominal.

Format, header, dan tema yang disalin tersimpan di `resources/templates/humas-csr/`; tidak ada transaksi spreadsheet rujukan yang dimasukkan ke template aplikasi. Penggunaan aplikasi tidak memerlukan koneksi Google Sheets.

Nilai lama seperti DIPROSES, DITOLAK, JASA, atau LAINNYA tetap tersimpan dan dapat dibaca. Saat diedit, form meminta pemilihan nilai baru yang sesuai template; tidak ada perubahan status/luaran otomatis atau penghapusan arsip lama. Kategori lama di luar daftar template tidak dihapus, tetapi tidak menjadi pilihan baru pada form CSR.

**PDF:** tabel 12 kolom landscape A3, header berulang, total nominal, dan nomor halaman. Laporan tahunan memisahkan setiap bulan. Keluar/keterangan yang panjang diringkas di tabel dan dicantumkan lengkap pada bagian rincian setelah tabel agar tidak terpotong oleh batas halaman.

Kedua ekspor mengikuti filter yang dipilih dan periode **Tanggal Proses**, bukan tanggal pembuatan record. Total nominal pada dashboard dan PDF menjumlahkan arsip sesuai cakupan/filter; gunakan filter Status/Luaran jika membutuhkan total bantuan tertentu. Excel menampilkan nominal per arsip sesuai tata letak template tanpa baris total tambahan.

## Pemasangan di lingkungan lain

Cadangkan database sebelum menjalankan migrasi. Untuk project yang sudah berjalan, pasang perubahan dengan:

```sh
php artisan migrate --force
php artisan db:seed --class=HumasSeeder --force
php artisan optimize:clear
```

`HumasSeeder` idempoten, menambahkan role/bidang Humas yang belum ada, dan memanggil `HumasCsrTemplateSeeder`. Untuk memperbarui kategori pada instalasi lama, cukup jalankan `php artisan db:seed --class=HumasCsrTemplateSeeder --force`; tidak ada migrasi schema tambahan. Cadangan sebelum pemasangan disimpan secara privat di `storage/app/private-backups/`, dikecualikan dari Git dan build Docker. Jangan menjalankan `migrate:fresh` atau seluruh `DatabaseSeeder` pada database berisi data; seeder project lain dapat membuat akun sampel.

Ekspor Excel memerlukan ekstensi PHP **zip**; PDF memakai dependency DomPDF yang sudah ada. Dockerfile project sudah memasang `zip`. Konfigurasi unggahan PHP/Nginx harus mengizinkan request minimal 20 MB, dan `storage/app` harus dapat ditulis serta dipersistenkan/dicadangkan. Modul ini tidak memerlukan scheduler atau sinkronisasi Google Sheets.

## Peta kode dan pengujian

- Schema: `2026_10_27_090000_create_humas_csr_tables.php`, tabel `humas_csrs` dan `humas_csr_attachments`.
- Model: `HumasCsr`, `HumasCsrAttachment`; pencatatan perubahan memakai Spatie Activitylog.
- Controller: `HumasCsrController`, `HumasReportController`.
- Service: `HumasPrivateFiles`, `HumasCsrExcel`.
- View: `resources/views/humas/`; navigasi dipertahankan saat membuka konsultasi, perpustakaan, dan log.
- Route: `/humas/dashboard`, resource `/humas/csr`, lampiran `/humas/csr/{csr}/attachments/{attachment}`, serta `/humas/reports`, `/excel`, dan `/pdf` di bawah prefix laporan. Middleware: `auth`, `aktif`, `nocache`, `role:humas`.
- Admin tetap menggunakan pengelolaan pengguna/bidang/kategori yang ada. Kategori yang masih digunakan CSR tidak dapat dihapus.

Pengujian terpilih menggunakan SQLite dalam memori: **59 tes regresi lulus**, mencakup CRUD, validasi nominal/lampiran, isolasi bidang, rollback kegagalan file, ekspor Excel/PDF (termasuk serial tanggal Excel tahun 1900), serta regresi arsip Sekretariat, log, kategori dan kegiatan Hukum, dan kedaluwarsa dokumen. Pemeriksaan tambahan: struktur Excel dibuka dengan openpyxl; PDF dirender dengan Poppler; form desktop/mobile diperiksa menggunakan browser headless.

Perintah suite terisolasi:

```sh
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit tests/Feature/HumasCsrModuleTest.php tests/Feature/SecretariatArchiveAccessTest.php tests/Feature/ActivityLogAccessTest.php tests/Feature/LegalDocumentExpiryTest.php tests/Feature/LegalActivityModuleTest.php tests/Feature/LegalCategoryMasterTest.php
```

Jangan mengganti perintah tersebut dengan seluruh suite default tanpa memeriksa konfigurasi database pengujian; konfigurasi SQLite global project belum diaktifkan.


Pembaruan 29 Oktober 2026: seluruh lampiran melewati pipeline [pengolahan upload](UPLOAD_PROCESSING.md) sebelum disimpan dengan nama ID record. PDF memerlukan qpdf; Dockerfile sudah menambahkannya, dan image lama perlu dibangun ulang.

Pemeriksaan CSR terbaru: **12 tes, 239 assertions**, meliputi seluruh pilihan, validasi kategori, kompatibilitas data lama, typed cells, warna/dropdown Excel, 12 tab tahunan, rentang validasi lebih dari 131 baris, dan isolasi bidang. Hasil Excel dibandingkan secara visual dengan file rujukan; form diperiksa pada lima lebar layar.
