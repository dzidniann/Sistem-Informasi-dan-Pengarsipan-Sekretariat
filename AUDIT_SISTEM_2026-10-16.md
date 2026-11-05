# Audit SSO PDAM — 16 Oktober 2026

## Tindak lanjut 5 November 2026

A01 ditangani melalui storage privat, otorisasi `/storage`, dan migrasi 21 file yang dicadangkan serta diverifikasi checksum. A02 ditangani dengan penutupan registrasi publik dan penghapusan fallback role/bidang pertama. Pencatatan password pada aktivitas baru dan serialisasi User diperbaiki untuk A03; data log lama tidak dihapus. A04 dicegah untuk unggahan Git baru melalui ignore rules; riwayat konfigurasi lama di GitHub masih memerlukan persetujuan penulisan ulang branch. A05 ditangani dengan database pengujian SQLite di memori. 73 tes / 1037 assertions lulus. Ini bukan penutupan seluruh temuan audit; lihat [SECURITY_HARDENING.md](SECURITY_HARDENING.md).

## Tindak lanjut 26 Oktober 2026 — cakupan arsip dan rekap

**A09 sudah diperbaiki:** endpoint `/dashboard/rekap-bulanan` sekarang memakai `auth`, `aktif`, dan `nocache`, memvalidasi bulan/tahun, serta menyaring data menurut hak akses akun. Bukti audit lama yang mengharapkan akses tamu dihapus dari `SystemAuditTest`; ekspektasi aman ditempatkan pada `tests/Feature/SecretariatArchiveAccessTest.php`.

Sekretariat sekarang dibatasi ke arsip bidang akun sendiri, meskipun flag lintas bidang pada data lama masih aktif. Pembatasan berlaku pada daftar, pencarian/pagination, detail langsung, kategori upload, rekap/dashboard, dan data arsip untuk laporan HTML/PDF. Pengaturan lintas bidang untuk role lain tetap mengikuti kebijakan sebelumnya.

Verifikasi 26 Oktober 2026: **34 test / 583 assertion lulus**, termasuk `SecretariatArchiveAccessTest` dan empat kelas regresi Legal/log yang sudah ada, semuanya memakai SQLite dalam memori. Pemeriksaan mencakup flag lintas bidang lama, pencarian/pagination, detail, upload lintas kategori, dashboard/rekap, data laporan HTML/PDF, serta penolakan tamu pada endpoint rekap.

Perubahan ini tidak memindahkan berkas publik lama, sehingga **A01 belum diselesaikan**. Jalur metadata yang terlindungi tidak mencabut URL `/storage` file publik yang telah diketahui. Status temuan lain tidak dianggap berubah oleh tindak lanjut ini.

## Pembaruan dokumentasi — 20 Oktober 2026

Dokumen ini tetap merupakan **laporan audit tanggal 16 Oktober**, dengan tindak lanjut bertanggal. Nomor baris dan hasil pemindaian dependency di bagian historis dapat berubah/menjadi usang; gunakan nama berkas dan konteks temuan sebagai rujukan. Angka advisory lama bukan hasil pemeriksaan dependency hari ini.

Status yang dapat dicatat dari perubahan dan regresi saat ini:

| Kelompok | Status per 20 Oktober 2026 |
|---|---|
| A11 — akses log aktivitas | Sudah diperbaiki; Super Admin melihat seluruh log, pengguna lain hanya causer akun sendiri. Regresi daftar/detail/pencarian/pagination tersedia. |
| A01 — URL berkas publik | Belum diselesaikan oleh perubahan UI. Dokumen Hukum, lampiran konsultasi, dan beberapa modul lama masih menggunakan disk publik. |
| A08 — middleware multi-role | Masih menjadi keterbatasan; middleware khusus kegiatan/notulensi/laporan tidak memperbaiki seluruh route Legal lama. |
| A12 — validasi PIC | Penyimpanan sekarang dapat memakai JSON tanpa reload, tetapi validasi server tetap `exists:users,id`; perubahan UI tidak menyelesaikan temuan role/akun aktif. |
| A02–A07, A09–A10, A13 | Tidak ditandai selesai dalam pembaruan ini. Belum dilakukan audit ulang menyeluruh dan belum ada bukti penyelesaian baru yang dicatat di sini. |

Perubahan setelah audit yang relevan untuk pemeliharaan:

- Nama bidang dan label berubah dari Divisi Hukum menjadi **Sub Bidang Hukum**. Migration `2026_10_20_090000_rename_legal_department.php` mengganti nama tanpa mengganti ID; relasi kategori/pengguna dipertahankan. Nama lama di badan audit adalah konteks historis.
- Status dokumen berlaku dihitung otomatis dari tanggal, disertai command harian. Perpustakaan menerima ACTIVE/EXPIRING_SOON yang terpublikasi dan belum kedaluwarsa. Pemeriksaan tanggal pada halaman tidak melindungi URL publik file itu sendiri.
- Tiga form status/PIC/jadwal konsultasi memakai penyimpanan tanpa reload, mempertahankan perubahan form lain, dan mengembalikan JSON dengan nilai tersimpan. Penyimpanan PIC/jadwal masih mempunyai efek status ACCEPTED/SCHEDULED.
- Desain Legal, sidebar lintas role, tombol hamburger, banner Perpustakaan, dan area scroll riwayat dokumen diperbarui. Perubahan visual ini bukan perbaikan temuan keamanan.
- Panduan implementasi aktif ada di [LEGAL_MODULE.md](LEGAL_MODULE.md), [project_analysis.md](project_analysis.md), [FILE_ACCESS.md](FILE_ACCESS.md), dan [DOCUMENT_EXPIRY_AUTOMATION.md](DOCUMENT_EXPIRY_AUTOMATION.md).

Verifikasi ulang **20 Oktober 2026: 29 test / 481 assertion lulus**, menggunakan `ActivityLogAccessTest`, `LegalDocumentExpiryTest`, `LegalActivityModuleTest`, dan `LegalCategoryMasterTest` dengan SQLite `:memory:`. Hasil ini meliputi regresi fitur dan A11, bukan kelulusan seluruh temuan audit. Test bukti `tests/Audit/SystemAuditTest.php`, pemindaian Composer/npm, MySQL, browser end-to-end, dan runtime Docker tidak dijalankan ulang dalam pembaruan dokumentasi ini.

## Tindak lanjut 19 Oktober 2026 — akses log aktivitas

Temuan A11 sudah diperbaiki. Super Admin dapat melihat seluruh log, sedangkan pengguna lainnya hanya dapat melihat log dengan causer akun sendiri; kesamaan role atau bidang tidak memberikan akses. Pembatasan berlaku pada daftar, pencarian, pagination, dan URL detail langsung. Lima bukti audit lama yang lain masih dipertahankan; bukti A11 dipindahkan menjadi test regresi aman di `tests/Feature/ActivityLogAccessTest.php`. Pengujian perubahan bersama regresi Legal: **27 test / 454 assertion lulus** menggunakan SQLite dalam memori. Bagian audit di bawah tetap merupakan catatan kondisi pada 16 Oktober, bukan status perbaikan terbaru.

## Kesimpulan

Sistem belum layak dinyatakan siap production. Alur Legal dan navigasi yang baru memiliki test regresi yang lulus, tetapi masih ada masalah akses berkas, pengelolaan akun, perlindungan rahasia, dan dependency yang perlu diselesaikan. Audit tidak menemukan atau menyimpulkan adanya kompromi server; laporan ini membahas kelemahan yang dapat diverifikasi pada kode dan lingkungan lokal.

Tidak ada perubahan kode aplikasi, kredensial, dependency, atau data database operasional selama audit. Penambahan hanya laporan ini dan test bukti audit yang menggunakan SQLite dalam memori.

## Cakupan dan bukti

- Pemeriksaan route, middleware, controller, model, migration, Blade, seeder, konfigurasi test, dan konfigurasi Docker/Nginx.
- Lint 144 berkas PHP: seluruhnya lulus.
- Kompilasi seluruh Blade: berhasil, lalu cache view dibersihkan.
- 26 test / 397 assertion lulus dalam satu eksekusi: **20 test regresi dan 6 test bukti audit**. Enam test bukti sengaja menegaskan perilaku bermasalah saat ini; kelulusannya BUKAN penilaian bahwa sistem aman.
- Test bukti ada di `tests/Audit/SystemAuditTest.php`, di luar direktori Feature/Unit agar tidak menjadi ekspektasi keamanan dalam suite biasa.
- `composer audit --locked --format=json`: exit 1, menemukan 13 entri advisory pada 5 paket; dua entri Laravel merujuk advisory yang sama. Empat paket merupakan dependency runtime, `symfony/yaml` merupakan dependency development.
- `npm audit --json --fetch-retries=0 --fetch-timeout=15000`: exit 1, 12 dependency ditandai: 9 high dan 3 moderate. Angka ini jumlah paket yang ditandai, bukan 12 celah independen atau bukti seluruhnya dapat dieksploitasi dari website.
- Test tidak menjalankan `migrate:fresh`, tidak memakai database aplikasi, dan tidak membuat akun pada database operasional.

## Temuan prioritas tinggi (P1)

### A01 — Berkas internal dapat diakses melalui URL publik tanpa otorisasi

Lokasi: `app/Http/Controllers/LegalDocumentController.php:82`, `ConsultationController.php:79` dan `:149`, `NotulensiController.php:50`, `ArsipController.php:63`, serta `docker/nginx/default.conf:15`.

Dokumen Hukum, termasuk draft, lampiran konsultasi/diskusi, Arsip, Nomor Surat, dan Notulensi Sekretariat memakai disk `public`. Junction `public/storage` serta alias Nginx melayani file tersebut langsung, tanpa melewati middleware login, role, bidang, atau kepemilikan. Pengguna yang memiliki URL dapat mengambil berkas meskipun halaman metadata menolak akses. Menonaktifkan publikasi dokumen di Perpustakaan tidak mencabut akses URL file yang sudah diketahui.

Ini berbeda dari lampiran kegiatan/notulensi Legal baru yang sudah memakai storage privat dan controller terotorisasi. Nama acak mengurangi kemungkinan menebak URL, tetapi bukan kontrol otorisasi.

Perbaikan: pindahkan file internal ke disk privat; sediakan endpoint preview/download per record dengan pemeriksaan akses yang sama dengan halaman detail. Endpoint Perpustakaan juga harus memeriksa status ACTIVE dan published sebelum mengirim file. Jangan hanya mencabut symlink karena itu memutus semua preview tanpa menyelesaikan alur aplikasi.

### A02 — Pendaftaran publik dapat menghasilkan super-admin ketika role employee tidak tersedia

Lokasi: `app/Http/Controllers/Auth/RegisteredUserController.php:39`, `routes/auth.php:15`, `database/seeders/RoleSeeder.php:17`.

Ketika role `employee` tidak ada, controller mengambil `Role::first()`. Seeder role dasar memasukkan `super-admin` sebagai role pertama. Dalam kondisi database tersebut, POST register dari tamu berhasil membuat akun super-admin aktif dan langsung login. Masalah direproduksi di SQLite. Temuan ini bersyarat; audit tidak menyatakan bahwa role employee hilang pada database operasional saat ini.

Perbaikan: gagal secara aman jika role minimum tidak tersedia; jangan menggunakan role pertama sebagai fallback. Putuskan apakah pendaftaran publik memang diperlukan untuk aplikasi internal. Jika tidak diperlukan, pengelolaan akun sebaiknya melalui admin.

### A03 — Hash password tercatat pada Activity Log dan tidak disembunyikan dari serialisasi User

Lokasi: `app/Models/User.php:20` dan `:32`, `resources/views/log-aktivitas/show.blade.php:112`.

Password ada pada fillable, sedangkan Spatie menggunakan `logFillable()`. Test membuktikan hash password muncul pada properties log saat user dibuat. View detail mencetak semua attributes. Model User juga tidak mempunyai daftar `$hidden`, dan test membuktikan `toArray()` menyertakan password. Otorisasi detail log masih berlaku; ini bukan bukti log dapat dibaca tamu, tetapi memperluas pihak dan sistem yang menerima hash password secara tidak perlu.

Perbaikan: kecualikan password dan token dari log, tambahkan hidden fields pada model, dan audit log yang sudah tersimpan. Pembersihan log sensitif perlu prosedur terencana agar jejak audit lain tetap tersedia.

### A04 — Backup konfigurasi berisi field rahasia masuk indeks Git dan build context Docker

Lokasi: `.env.local.backup`, `.dockerignore:9`, `Dockerfile:29`.

`git ls-files` menunjukkan `.env.local.backup` masuk indeks Git. Pemeriksaan nama field menunjukkan backup memiliki APP_KEY dan field kredensial; nilainya tidak dicetak dalam audit. `.dockerignore` hanya mengecualikan `.env` dan `.env.backup`, sehingga `.env.local.backup` tercakup oleh `COPY . .`. Folder upload operasional juga tidak dikecualikan dari build context.

Belum diperiksa apakah backup pernah di-push atau image pernah dipublikasikan. Perbaikan: keluarkan seluruh backup rahasia dan upload operasional dari Git/build context; jika sudah terdistribusi, rotasi kredensial terkait dengan prosedur yang mempertimbangkan data terenkripsi dan sesi aktif.

### A05 — Konfigurasi test bawaan berisiko menggunakan database aplikasi

Lokasi: `phpunit.xml:24`, `tests/Feature/Auth/RegistrationTest.php:11`, dan test Breeze lain yang memakai RefreshDatabase.

DB_CONNECTION dan DB_DATABASE khusus test masih dikomentari. APP_ENV=testing saja tidak mengganti database. Suite lama memakai RefreshDatabase, yang dapat menjalankan reset schema pada koneksi default ketika test database belum disiapkan. Karena itu audit tidak menjalankan seluruh suite default.

Perbaikan: koneksi SQLite dalam memori atau database khusus test harus dipaksa dan diperiksa sebelum test. Suite baru mengubah konfigurasi koneksinya sebelum menjalankan migration `up()` di memori; database aplikasi tidak disentuh.

### A06 — Dependency memiliki advisory keamanan yang perlu ditangani

Paket Composer yang ditandai pada lockfile:

| Paket | Versi | Lingkup |
|---|---|---|
| laravel/framework | 10.50.2 | Runtime |
| league/commonmark | 2.9.0 | Runtime |
| league/flysystem | 3.35.2 | Runtime |
| symfony/http-foundation | 6.4.43 | Runtime |
| symfony/yaml | 6.4.43 | Development |

Contoh sumber primer: [advisory Laravel untuk validasi email](https://github.com/laravel/framework/security/advisories/GHSA-5vg9-5847-vvmq) dan [advisory Symfony HeaderUtils](https://symfony.com/cve-2026-108082). Eksploitabilitas bergantung pada penggunaan fitur dan konfigurasi aplikasi; audit tidak mencoba payload DoS atau eksploit dependency.

Audit npm juga menandai dependency frontend/tooling, termasuk Axios, Vite, dan dependency Tailwind. Sebagian masalah berlaku pada adapter Node atau development server, bukan otomatis pada JavaScript browser. Semua paket langsung pada package.json diletakkan di devDependencies; itu tidak berarti seluruh kode paket tidak masuk bundle browser.

Perbaikan: rencanakan upgrade framework dan dependency dengan uji kompatibilitas. Jangan menjalankan `npm audit fix --force` atau upgrade major Composer tanpa memeriksa perubahan API, build, dan hasil test. Development server Vite tidak boleh dijadikan server production yang terbuka ke jaringan.

### A07 — Riwayat Nomor Surat memasukkan teks pengguna ke innerHTML

Lokasi: `resources/views/nomor-surat/index.blade.php:1252` dan `:1270`; pengisian `perihal` pada `NomorSuratController.php:115`.

Nilai perihal dari database diinterpolasi langsung ke `row.innerHTML`, dan juga dimasukkan ke string onclick pada baris 1228. Teks seperti markup HTML tidak diperlakukan sebagai teks biasa. Ini merupakan jalur stored XSS pada daftar riwayat yang dibuat melalui JavaScript. Penulisan perihal dibatasi role Sekretariat, tetapi konten bisa dieksekusi dalam sesi pengguna lain yang membuka riwayat yang sama. Audit ini memverifikasi jalur kode; belum menjalankan payload JavaScript melalui browser.

Perbaikan: gunakan DOM API dan `textContent`, serta `addEventListener` dengan data yang tidak dirangkai menjadi HTML/JavaScript. Escape Blade pada tabel awal tidak memperbaiki rendering ulang lewat JSON.

## Temuan fungsional dan integritas (P2)

### A08 — Role kedua pada middleware tidak diproses

Lokasi: `app/Http/Middleware/RoleMiddleware.php:10`, `routes/web.php:286`.

Laravel memberikan `role:legal-admin,super-admin` sebagai dua argumen. Middleware hanya menerima `$role`, sehingga `explode()` hanya memperoleh legal-admin. Test membuktikan super-admin mendapat 403 pada dashboard Legal lama, sedangkan laporan Legal baru berhasil karena memakai middleware khusus. Ini menimbulkan perilaku akses tidak konsisten, termasuk jika route baru ditambahkan mengikuti pola lama.

Perbaikan: terima seluruh argumen role secara variadic dan validasi role secara ketat; selaraskan pemeriksaan `hasRole('legal-admin')` pada controller jika super-admin memang dimaksudkan memiliki akses Legal.

### A09 — Endpoint rekap arsip dapat diakses tanpa login

Lokasi: `routes/web.php:210`, `app/Http/Controllers/DashboardController.php:64`.

Route hanya memperoleh middleware `web`, bukan auth/aktif/role. Test tamu memperoleh HTTP 200 dan struktur rekap. Controller mengembalikan agregat arsip dan nama bidang pada periode yang dipilih.

Perbaikan: masukkan ke grup akses yang sesuai dan validasi bulan/tahun. Batasi cakupan bidang jika endpoint hendak dipakai role yang tidak boleh melihat seluruh bidang.

### A10 — Route update Notulensi Sekretariat tidak memiliki method controller

Lokasi: `routes/web.php:177`, `app/Http/Controllers/NotulensiController.php:8`.

Resource route mendaftarkan PUT/PATCH update, tetapi controller tidak mempunyai method update. Test mereproduksi BadMethodCallException. Ini relevan ketika lampiran salah hendak diganti: modul lama tidak menyediakan implementasi penggantian melalui route tersebut.

Perbaikan: implementasikan update dengan validasi, otorisasi, dan penggantian file yang aman, atau hapus route jika edit memang tidak didukung. Menampilkan form edit saja tidak cukup.

### A11 — Daftar log admin tidak konsisten dengan detail log

Lokasi: `app/Http/Controllers/LogAktivitasController.php:20` dan `:66`.

Index mengecek nama role `admin`, sedangkan sistem menggunakan `super-admin`. Akibatnya daftar super-admin difilter berdasarkan role sendiri. Detail memakai super-admin dengan benar. Test membuktikan log staf tidak ada pada daftar admin walaupun admin dapat membuka detailnya.

Perbaikan: selaraskan identitas role dan kebijakan akses index/detail; tentukan apakah pengguna biasa boleh melihat seluruh log dengan role yang sama atau hanya aktivitas miliknya.

### A12 — PIC konsultasi dapat menunjuk akun non-Legal atau nonaktif

Lokasi: `app/Http/Controllers/ConsultationController.php:191`.

Validasi hanya memeriksa keberadaan users.id. Permintaan langsung dapat memilih akun staf/nonaktif, meskipun pilihan pada UI mungkin sudah difilter. Status kemudian berubah menjadi ACCEPTED.

Perbaikan: validasi server harus memastikan role Legal yang sesuai dan akun aktif. Jangan mengandalkan daftar dropdown.

### A13 — Penggantian lampiran Nomor Surat menghapus file lama sebelum penyimpanan berhasil

Lokasi: `app/Http/Controllers/NomorSuratController.php:184`.

File lama dihapus terlebih dahulu, baru upload pengganti disimpan dan database diperbarui. Jika upload atau update database gagal, referensi lama bisa menunjuk file yang sudah terhapus. Beberapa modul lama juga belum memakai transaksi untuk rangkaian upload, record, history, dan notifikasi. LegalActivityController sudah memiliki pola cleanup setelah transaksi yang lebih baik.

Perbaikan: simpan file baru terlebih dahulu, commit perubahan database, kemudian hapus file lama; hapus file baru jika transaksi gagal.

## Catatan deployment dan kelengkapan

- Docker Compose masih berisi kredensial default, publikasi port database, dan phpMyAdmin dengan akun root. Konfigurasi development ini tidak layak dipakai langsung di server publik. Audit tidak memeriksa firewall atau deployment production yang sebenarnya.
- Seeder Legal membuat akun sampel dengan password tetap. Jangan menjalankan seeder akun sampel tanpa pemisahan environment pada production. Audit tidak mencoba login ke akun operasional tersebut.
- APP_ENV=local dan APP_DEBUG=true ditemukan pada konfigurasi lokal; wajar untuk pengembangan, tetapi harus berbeda pada production. Nilai rahasia lain tidak dimasukkan ke laporan.
- Batas file dokumen 20 MB bertemu dengan post_max_size dan client_max_body_size 20M. Total multipart request juga mencakup overhead; file tepat di batas bisa ditolak lebih awal. Beri selisih pada batas request sambil mempertahankan validasi ukuran file.
- Data laporan bulanan sekarang memang diambil hanya dari legal_activities dan lampiran kegiatan. Notulensi tetap bisa membuat record kegiatan otomatis sesuai alur yang sudah ada. Jika maksud bisnisnya menghapus pembuatan kegiatan otomatis dari Notulensi, itu perlu keputusan terpisah; audit tidak mengubahnya.

## Bagian yang berhasil diverifikasi

- Pemisahan tabel notulensi Sekretariat dan Legal.
- Kegiatan/notulensi Legal menggunakan storage privat, endpoint role berwenang, validasi MIME/ukuran, dan penjagaan path.
- Rollback penyimpanan Legal membersihkan upload baru; penggantian berkas membersihkan upload lama sesudah penyimpanan berhasil.
- Kegiatan yang masih mempunyai notulensi ditolak ketika dihapus.
- Master kategori Divisi Hukum dari Kategori Arsip tersambung ke form Legal dan kategori bidang lain tidak muncul sebagai pilihan.
- Halaman laporan bulanan dan PDF tidak memuat lampiran notulensi; filter memakai tanggal kegiatan dan kegiatan tidak dihitung berganda karena jumlah notulensi.
- Navigasi role Perpustakaan dan Konsultasi, kontrol akses konsultasi milik pengguna lain, notifikasi pribadi, login redirect, dan pencabutan status CLOSED dalam cakupan test yang tersedia.

## Urutan tindak lanjut

1. Amankan registrasi, password/log, rahasia Git/Docker, dan konfigurasi database test.
2. Migrasikan file internal ke endpoint privat dengan tetap mendukung preview/download.
3. Perbaiki stored XSS dan proses upgrade dependency yang teruji.
4. Selaraskan middleware multi-role dan akses rekap/log, implementasikan update Notulensi Sekretariat, validasi PIC, serta perbaiki urutan penggantian lampiran.
5. Jalankan test MySQL, uji konkurensi, uji browser/mobile, backup/restore, serta validasi Docker/Nginx pada lingkungan deployment sebelum production.

## Batas audit

Belum dilakukan: penetrasi eksternal, pengujian beban/DoS, uji konkurensi MySQL, browser end-to-end, pemindaian image container, atau verifikasi backup/restore. Tidak ada klaim seluruh route aman hanya karena test terpilih lulus. Untuk menjalankan bukti audit secara terpisah:

```powershell
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit tests/Audit/SystemAuditTest.php
```

Jangan menjalankan suite default sebelum database test diisolasi. Test bukti harus diubah menjadi ekspektasi aman ketika temuan terkait sudah diperbaiki; hasilnya kemudian perlu dicatat ulang.
