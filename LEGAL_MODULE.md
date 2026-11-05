# Panduan modul Sub Bidang Hukum

Diperbarui: **20 Oktober 2026**. Dokumen ini menjelaskan implementasi sekarang, menggantikan panduan awal yang masih menyertakan statistik laporan dan lampiran notulensi dalam PDF bulanan.

## Cakupan dan berkas utama

| Modul | Controller | View utama |
|---|---|---|
| Dashboard | `LegalDashboardController` | `resources/views/legal/dashboard.blade.php` |
| Dokumen dan riwayat | `LegalDocumentController` | `resources/views/legal/documents/` |
| Konsultasi dan diskusi | `ConsultationController` | `resources/views/legal/consultations/`, `resources/views/employee/consultations/` |
| Kalender | `LegalCalendarController` | `resources/views/legal/calendar/index.blade.php` |
| Notulensi | `LegalNotulensiController`, turunan `LegalActivityController` | `resources/views/legal/notulensi/` |
| Kegiatan bulanan | `LegalActivityController` | `resources/views/legal/activities/` |
| Laporan kegiatan bulanan | `LegalMonthlyReportController` | `resources/views/legal/reports/{monthly,monthly-pdf,documentation}.blade.php` |
| Perpustakaan | `LibraryController` | `resources/views/library/` |

Controller berada di `app/Http/Controllers/`. `LegalReportController` dan view `legal/reports/index.blade.php` lama sudah dihapus. View `legal/records/` masih dipakai sebagai sumber data/template internal oleh controller dasar; antarmuka Notulensi yang ditampilkan memakai view khusus `legal/notulensi/`.

## Nama bidang dan master kategori

Nama yang ditampilkan sekarang adalah **Sub Bidang Hukum**, termasuk sidebar, halaman, dan header PDF. Nama role teknis tetap `legal-admin`; prefix URL tetap `/legal`.

Migration `2026_10_20_090000_rename_legal_department.php` mengganti nama lama `Divisi Hukum` pada tabel `bidangs` tanpa mengganti ID. Relasi pengguna dan kategori tetap menggunakan ID yang sama. Model `KategoriArsip` dan scope `LegalCategory::masterHukum()` menerima kedua nama untuk kompatibilitas data lama.

Admin mengisi kategori melalui **Kategori Arsip → Sub Bidang Hukum**. Tidak ada modul master kategori dokumen Hukum terpisah.

- Tambah/edit `KategoriArsip` menyinkronkan `LegalCategory` melalui `legal_categories.kategori_arsip_id`, yang unik dan nullable.
- Form dokumen Hukum hanya menerima kategori dari master bidang Hukum. Kategori bidang lain tidak ditawarkan maupun diterima pada validasi pembuatan dokumen.
- Penghapusan kategori yang masih dipakai dokumen Hukum ditolak.
- Seeder tidak membuat enam kategori bawaan. Migration penghubung master menghapus default yang masih cocok nama/deskripsinya dan belum dipakai; kategori historis yang dipakai atau disunting dipertahankan, tetapi kategori tanpa master tidak ditawarkan untuk dokumen baru.

## Dokumen, status, dan publikasi

Pembaruan 26 Oktober 2026: seluruh isian pada form **Unggah Dokumen Hukum Baru** wajib diisi: nama, kategori, departemen/bidang terkait, PIC, tanggal mulai berlaku, tanggal kedaluwarsa, jadwal evaluasi/review, berkas, dan deskripsi. Ketentuan diterapkan melalui atribut HTML `required` dan validasi `store()` di server. Tanggal kedaluwarsa tidak boleh sebelum tanggal mulai berlaku. Perubahan ini khusus pembuatan/unggah dokumen baru; data lama dan aturan edit tidak diubah.

Dokumen baru berstatus `DRAFT`. Tahap manual adalah `DRAFT`, `NEED_REVIEW`, atau Berlaku (`ACTIVE` sebagai permintaan persetujuan). Untuk dokumen yang sudah dinyatakan berlaku, model menghitung hasil akhirnya dari `expiry_date`:

| Tanggal kedaluwarsa | Status |
|---|---|
| Kosong atau lebih dari 30 hari lagi | `ACTIVE` |
| Hari ini sampai 30 hari lagi | `EXPIRING_SOON` |
| Sudah lewat tanggalnya | `EXPIRED` |

Tanggal menggunakan timezone aplikasi `Asia/Makassar`. Jadwal harian dan pemeriksaan saat penyimpanan dijelaskan di [DOCUMENT_EXPIRY_AUTOMATION.md](DOCUMENT_EXPIRY_AUTOMATION.md).

Perpustakaan hanya menampilkan dokumen dengan `is_published = true`, berstatus `ACTIVE` atau `EXPIRING_SOON`, dan belum melewati tanggal kedaluwarsa. Daftar dan detail memeriksa tanggal aktual sehingga dokumen yang sudah kedaluwarsa tetap tersembunyi jika scheduler terlambat. Perpustakaan memerlukan login; istilah portal terbuka berarti akses internal lintas role, bukan akses tamu internet.

Riwayat versi/aktivitas dokumen berada di kartu dengan area scroll vertikal maksimal 360 px. Judul kartu tetap di luar area scroll. Teks panjang membungkus agar tidak memperlebar kartu.

## Konsultasi: tiga form tanpa reload

Status yang tersedia: `WAITING_REVIEW`, `ACCEPTED`, `SCHEDULED`, `ONGOING`, dan `FINISHED`. `CLOSED` sudah digabung ke `FINISHED` melalui migration, dan tidak diterima lagi pada pemilihan status.

Di halaman detail Legal terdapat tiga form: status, penugasan PIC, dan jadwal pertemuan.

- Ketiga tombol abu-abu/nonaktif selama nilainya sama dengan data tersimpan atau nilainya tidak valid. Tombol menjadi navy/aktif setelah perubahan valid.
- JavaScript pada `legal/consultations/show.blade.php` mengirim `FormData` melalui `fetch`, termasuk token CSRF dan `_method=PATCH`, dengan `Accept: application/json`.
- `ConsultationController::actionResponse()` mengembalikan pesan serta nilai tersimpan `status`, `assigned_legal`, dan `schedule`. Permintaan form biasa tetap mendapat redirect dengan pesan session.
- Menyimpan satu form tidak me-reload halaman atau menghapus perubahan pada dua form lain. Nilai pembanding diperbarui setelah berhasil; jika gagal, input dipertahankan dan pesan tampil pada form terkait.
- Permintaan simpan diantrekan di browser. Nilai yang masih diedit saat permintaan berjalan tetap dipertahankan.
- Penugasan PIC otomatis menetapkan status `ACCEPTED`. Menyimpan jadwal otomatis menetapkan `SCHEDULED`. Badge status di atas halaman mengikuti respons server; pilihan status yang belum disimpan tidak ditimpa.
- Jadwal wajib setelah waktu sekarang. Validasi PIC server saat ini masih hanya `exists:users,id`; perbaikan validasi role/akun aktif tercatat sebagai A12 dalam laporan audit.

Legal Admin melihat seluruh konsultasi. Pemohon lain hanya melihat/mengirim pesan pada konsultasi sendiri. Penyimpanan pesan diskusi masih memakai alur form/redirect biasa; perubahan tanpa reload hanya berlaku pada tiga form administratif di atas.

## Notulensi dan kegiatan bulanan

Data Notulensi Legal berada di `legal_notulensis`, terpisah dari `notulensis` milik Sekretariat. Data kegiatan berada di `legal_activities`.

Halaman daftar Notulensi dan Kegiatan menggabungkan form tambah dengan riwayat, pencarian, filter periode, pagination, detail, edit, dan preview lampiran. Desain mengikuti Sekretariat.

| Record | Isian utama | Lampiran |
|---|---|---|
| Kegiatan | `tanggal_kegiatan`, `uraian_kegiatan`, opsional dokumen Hukum dan `keterangan` | `dokumen_pendukung`, `foto_dokumentasi` |
| Notulensi | `judul_rapat`, `tanggal_rapat`, `notulis`, opsional ringkasan/dokumen Hukum; pilih kegiatan atau buat otomatis | `file_path`, `foto_path` |

Satu kegiatan dapat memiliki banyak notulensi. Pembuatan kegiatan otomatis dari notulensi menggunakan ulang record dengan pembuat, tanggal, judul, dan dokumen Hukum yang sama. Proses memakai transaksi dan row lock pembuat. Kegiatan yang masih mempunyai notulensi tidak dapat dihapus; pindahkan atau hapus notulensinya dahulu.

Upload baru disimpan terlebih dahulu. File lama baru dihapus setelah transaksi penyimpanan berhasil; file baru dibersihkan jika transaksi gagal. Menghapus notulensi tidak menghapus kegiatan atau dokumen Hukum terkait. Perubahan dicatat melalui Spatie Activity Log.

Validasi lampiran: PDF/DOC/DOCX maksimal 10 MB; foto JPG/JPEG/PNG maksimal 5 MB dan dimensi maksimal 6000 × 6000. Endpoint lampiran dan batas akses dijelaskan di [FILE_ACCESS.md](FILE_ACCESS.md).

## Laporan kegiatan bulanan dan PDF

Menu **Laporan Kegiatan Bulanan** hanya mengambil `LegalActivity` menurut `tanggal_kegiatan`, diurutkan tanggal lalu ID. Rentang periode adalah awal bulan terpilih sampai sebelum awal bulan berikutnya. Satu record kegiatan menjadi satu baris laporan.

`legal.reports.index` dan `legal.reports.monthly` menampilkan halaman yang sama. Halaman statistik Laporan & Rekap lama sudah tidak digunakan.

Sumber kolom dokumentasi hanya relasi dokumen Hukum pada kegiatan, `dokumen_pendukung`, dan `foto_dokumentasi` milik kegiatan. PDF tidak memuat judul, file, atau foto notulensi. Relasi notulensi tetap ada untuk operasional, dan notulensi masih dapat membuat kegiatan otomatis; record kegiatan hasil proses itu dapat masuk laporan sesuai tanggalnya.

PDF memakai DomPDF, A4 landscape, header **SUB BIDANG HUKUM PDAM**, logo JPEG lokal, periode, jumlah kegiatan, tabel, foto kegiatan, waktu pembuatan, serta nomor halaman. File pendukung dicantumkan sebagai nama berkas, bukan digabung sebagai halaman PDF lampiran. `isRemoteEnabled` dan `isPhpEnabled` dinonaktifkan.

Tanda tangan hanya tampil jika `legal.signatory_name` diisi. Konfigurasi berada di `config/legal.php`, melalui `LEGAL_SIGNATORY_NAME` dan `LEGAL_SIGNATORY_TITLE` pada environment deployment.

GD dibutuhkan untuk thumbnail PNG dalam PDF. Tanpa GD, nama PNG tetap tercantum dan laporan tetap dibuat; thumbnail PNG dilewati. JPEG dan logo JPEG lokal tidak memerlukan GD. Pada pemeriksaan PHP CLI lokal tanggal 20 Oktober 2026, GD sudah aktif. Dockerfile juga memasang GD; runtime server/container harus diperiksa sendiri setelah build.

## Navigasi dan tampilan

- `/` langsung mengarahkan ke login; landing page lama tidak ditampilkan.
- Sidebar Admin berfokus pada administrasi, tanpa pintasan modul Hukum atau Perpustakaan.
- Perpustakaan dan portal konsultasi pemohon memakai layout sesuai role; pengguna Sekretariat/Staf tidak kehilangan sidebar asal ketika masuk portal tersebut.
- Layout Hukum memakai sidebar navy, aksen teal, kartu membulat, form seragam, serta tombol utama gelap. Tombol tambah dokumen berada di samping judul pada dashboard dan daftar dokumen.
- Kontrol hamburger Admin/Sekretariat/Staf/Pegawai memakai `resources/views/layouts/sidebar-controls-style.blade.php`. Layout Hukum mempunyai aturan setara di layoutnya sendiri. Tombol 42 × 42 px berada dekat sidebar dan tidak mengikuti pemusatan konten; mobile memakai drawer dan tombol Tutup.
- Menu Konsultasi/Perpustakaan tidak memakai emoji. Banner pencarian Perpustakaan tetap ada dengan latar navy; judul/subjudul tambahan di atas banner sudah dihapus.
- Pembaruan 26 Oktober 2026: halaman Notifikasi, menu sidebar, controller halaman, serta route daftar/tandai dibaca sudah dihapus. Model, tabel, dan pencatatan notifikasi pada proses dokumen/konsultasi tetap dipertahankan; perubahan ini hanya menghapus antarmuka notifikasi.

## Akses dan batas implementasi

Route Legal memakai `auth`, `aktif`, dan `nocache`. Route kegiatan, notulensi, dan laporan mengganti middleware role lama dengan `LegalModuleAccess`, yang menerima `legal-admin` dan `super-admin`.

Route Legal lainnya masih memakai `role:legal-admin,super-admin`. `RoleMiddleware` belum menerima parameter role secara variadic sehingga akses role kedua belum bekerja sebagaimana maksud deklarasi route. Jangan menyimpulkan Super Admin dapat memakai seluruh modul Legal hanya dari daftar route; lihat A08 dalam [laporan audit](AUDIT_SISTEM_2026-10-16.md).

Pembaruan akses arsip 26 Oktober 2026: Sekretariat hanya melihat arsip bidang sendiri, termasuk daftar/pencarian/detail, statistik dashboard, rekap, dan data arsip laporan. Menu Arsip Semua Bidang dihapus. Perubahan ini tidak membatasi Perpustakaan Digital yang memang melayani dokumen Hukum terpublikasi lintas role.

Log Aktivitas: Super Admin melihat seluruh log; pengguna lainnya hanya log milik akunnya sendiri. Pembatasan mencakup pencarian, pagination, dan detail langsung. Log sistem tanpa causer tidak diberikan kepada pengguna biasa.

Lampiran kegiatan/notulensi Legal privat. Dokumen dan beberapa modul lama masih menggunakan URL storage publik; pembatasan metadata Perpustakaan tidak mencabut akses URL file yang telah diketahui. Lihat [panduan berkas](FILE_ACCESS.md) dan temuan A01 audit.

## Migration dan penerapan

Migration terkait perubahan modul:

| File di `database/migrations/` | Fungsi |
|---|---|
| `2026_10_08_100001` sampai `100006` | Kategori, dokumen, riwayat, konsultasi, diskusi, notifikasi |
| `2026_10_14_100001_create_legal_activity_minutes_tables.php` | Tabel kegiatan/notulensi Legal dan relasinya |
| `2026_10_14_150000_connect_legal_categories_to_archive_master.php` | Master kategori dan pembersihan default tidak terpakai |
| `2026_10_15_100000_merge_closed_consultations_into_finished.php` | Penggabungan status CLOSED ke FINISHED |
| `2026_10_20_090000_rename_legal_department.php` | Nama Sub Bidang Hukum, ID tetap |

Migration penggantian nama sudah dijalankan pada database lokal tanggal 20 Oktober 2026. Catatan ini tidak menyatakan migration sudah berjalan pada server lain. Untuk deployment lain yang databasenya sudah dikonfigurasi dan dibackup:

```powershell
php artisan migrate
php artisan optimize:clear
php artisan files:protect-public --dry-run
```

Jangan memakai `migrate:fresh` untuk memperbarui database yang berisi data. `LegalSeeder` mengisi role/bidang dan akun sampel; jangan menjadikannya langkah pembaruan production tanpa memisahkan akun sampel. Tidak ada dependency baru khusus perubahan antarmuka terbaru.

## Route penting

| Nama route | Metode dan URL |
|---|---|
| `legal.dashboard` | GET `/legal/dashboard` |
| `legal.documents.*` | Resource `/legal/documents` |
| `legal.documents.update-status` | PUT/PATCH `/legal/documents/{document}/status` |
| `legal.documents.publish`, `.unpublish` | POST `/legal/documents/{document}/publish` atau `/unpublish` |
| `legal.consultations.index`, `.show` | GET `/legal/consultations`, `/legal/consultations/{consultation}` |
| `legal.consultations.update-status`, `.assign`, `.schedule` | PUT/PATCH `/legal/consultations/{consultation}/status`, `/assign`, `/schedule` |
| `legal.consultations.message` | POST `/legal/consultations/{consultation}/message` |
| `legal.activities.*`, `legal.notulensi.*` | Resource `/legal/activities`, `/legal/notulensi` |
| `legal.activities.attachment`, `legal.notulensi.attachment` | GET `/legal/activities/{id}/attachments/{field}`, `/legal/notulensi/{id}/attachments/{field}` |
| `legal.reports.index`, `.monthly` | GET `/legal/reports`, `/legal/reports/monthly` |
| `legal.reports.monthly.preview`, `.pdf` | GET `/legal/reports/monthly/preview`, `/legal/reports/monthly/pdf` |
| `library.index`, `.show` | GET `/library`, `/library/{document}` |
| `employee.consultations.*` | Daftar, tambah, detail dan diskusi di `/employee/consultations` |

Resource mencakup index/create/store/show/edit/update/destroy. Daftar kegiatan/notulensi menerima `cari`, `bulan`, `tahun`; laporan menerima `bulan`, `tahun`; endpoint lampiran privat menerima `download=1`.

## Verifikasi terakhir

Tanggal **20 Oktober 2026**, perintah berikut lulus **29 test / 481 assertion**:

```powershell
php -d extension=pdo_sqlite vendor/phpunit/phpunit/phpunit tests/Feature/ActivityLogAccessTest.php tests/Feature/LegalDocumentExpiryTest.php tests/Feature/LegalActivityModuleTest.php tests/Feature/LegalCategoryMasterTest.php
```

Keempat kelas mengisolasi database ke SQLite `:memory:` di setup masing-masing. Cakupan mencakup CRUD dan lampiran privat, pemisahan data Sekretariat, laporan kegiatan saja, kategori master, rename bidang, respons JSON konsultasi dan validasi/aksesnya, tanggal kedaluwarsa, publikasi, sidebar sesuai role, dan isolasi log.

Pemeriksaan JavaScript terpisah pada perubahan konsultasi memverifikasi perubahan form lain tetap ada, sinkronisasi hasil simpan, retry saat gagal, dan antrean simpan. Itu pemeriksaan logika dengan DOM tiruan, bukan pengujian browser end-to-end.

Hasil di atas bukan pengujian seluruh suite, audit ulang keamanan, uji MySQL/konkurensi nyata, atau verifikasi runtime Docker. `phpunit.xml` masih menonaktifkan konfigurasi SQLite default; jangan menjalankan suite bawaan yang memakai `RefreshDatabase` terhadap database operasional. Pada sandbox Windows, PHPUnit dapat memerlukan eksekusi di luar sandbox karena pembacaan bootstrap vendor ditolak.


## Kalender review dokumen — 30 Oktober 2026

Kalender jadwal menampilkan konsultasi dan dokumen berstatus `NEED_REVIEW`. Dokumen ditempatkan berdasarkan `review_date`, sebagai agenda seharian berwarna kuning, dengan tautan ke detail dokumen. Perubahan status keluar dari NEED_REVIEW otomatis menghilangkan agenda review pada pembacaan berikutnya.

Panel konsultasi 7 hari mendatang tetap tersedia. Panel Dokumen Perlu Review menampilkan hingga 20 dokumen yang belum selesai direview, termasuk yang lewat tanggal dan yang tanggalnya belum diisi; dokumen tanpa tanggal tidak ditempatkan pada hari kalender yang dibuat-buat. Jika lebih dari 20, tersedia tautan ke daftar dokumen dengan filter NEED_REVIEW. Endpoint JSON kalender menyertakan `type`, `url`, dan `allDay`; ID agenda dokumen memakai prefix `document_` untuk membedakannya dari ID konsultasi.


Revisi kalender 30 Oktober 2026: agenda kini diambil dari semua `review_date`, termasuk dokumen yang belum berstatus NEED_REVIEW. Tanggal yang sudah tiba otomatis menghasilkan NEED_REVIEW lewat penyimpanan model dan command terjadwal; kedaluwarsa didahulukan sebagai EXPIRED. Agenda 7 hari ke depan juga menampilkan jadwal review. Untuk menyelesaikan review, atur tanggal berikutnya lalu pilih ACTIVE. Lihat [aturan otomatis](DOCUMENT_EXPIRY_AUTOMATION.md).


## Dokumen Tata Kelola — 30 Oktober 2026

Modul terpisah di menu Sub Bidang Hukum dan Tata Kelola, setelah Dokumen Hukum. Form hanya memuat **Judul File**, **Tanggal**, dan **Upload File**. Semua wajib saat unggah baru; penggantian file opsional saat edit. Validasi PDF menurut MIME dan ekstensi, maksimal **5 MB**, tanggal 1900–2100, judul maksimal 255 karakter.

Daftar mendukung pencarian judul, filter tanggal, dan pagination 25 record. Detail menyediakan preview PDF privat, unduh, edit, dan hapus. Akses mengikuti LegalModuleAccess (legal-admin/super-admin), dengan middleware autentikasi dan akun aktif. Modul tidak menambah kategori, workflow status, jadwal review, atau publikasi perpustakaan.

Schema: `legal_governance_documents`, migration `2026_10_30_110000_create_legal_governance_documents_table.php`. Controller/model: `LegalGovernanceDocumentController`, `LegalGovernanceDocument`. Route: resource `/legal/governance-documents` serta `/legal/governance-documents/{governanceDocument}/file` (tambahkan `?download=1` untuk unduh).

File disimpan pada disk local di `legal-private/governance-documents` melalui pipeline NamedUploads/UploadOptimizer: nama berawalan ID, optimasi PDF, dan pengecualian dokumen bertanda tangan mengikuti kebijakan upload yang ada. Penggantian file menggunakan transaksi; file lama baru dihapus setelah metadata tersimpan. Log perubahan memakai Spatie Activitylog.

Judul tab, identitas sidebar, dan heading dashboard menjadi **Sub Bidang Hukum dan Tata Kelola**. Nama master bidang database tetap memakai nilai yang sudah ada agar relasi/kategori tetap konsisten.

Migrasi tambahan sudah dipasang di database lokal dan Docker pada task ini, setelah keduanya dicadangkan. Tidak dibuat data contoh. Untuk deployment lain jalankan `php artisan migrate --force`, kemudian bersihkan cache route/view. Tidak perlu rebuild image untuk modul ini.

Verifikasi: **56 tes / 898 assertions lulus**, termasuk CRUD Tata Kelola, preview/download, penolakan non-PDF, batas tepat 5 MB dan di atasnya, otorisasi, validasi path privat, rollback kegagalan penggantian file, serta regresi modul lama. Form desktop 1440 px dan mobile 390 px diperiksa: tepat tiga field dan tidak ada horizontal overflow.

Pembaruan keamanan 5 November 2026: registrasi publik ditutup dan unggahan lama dipindahkan ke storage privat dengan pemeriksaan akses pada URL berkas. Jangan membuat symlink `public/storage`. Lihat [SECURITY_HARDENING.md](SECURITY_HARDENING.md).
