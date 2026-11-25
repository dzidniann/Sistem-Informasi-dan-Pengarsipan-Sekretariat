# Pembaruan keamanan dependency SIMARTA

Tanggal pemeriksaan: 9 November 2026 (WITA). Perubahan lokal; tidak deploy ke kantor atau menjalankan migration database aplikasi.

## Alasan dan cakupan

Audit awal dependency production melaporkan 19 advisory pada 7 paket. Dry-run update dalam Laravel10 ditolak Composer karena seluruh pilihan Laravel10 yang tersedia masih terkena advisory. Pemeriksaan metadata advisory menunjukkan versi perbaikan memerlukan Laravel12; keamanan Composer tidak dinonaktifkan dan tidak ada advisory yang di-ignore.

Constraint aplikasi dinaikkan ke PHP^8.2 dan Laravel^12.69. Composer platform PHP8.2.35 memastikan pemilihan dependency sesuai baseline runtime Docker, bukan hanya PHP8.3 laptop. Resolusi memilih Laravel12.71.0, Sanctum4.3.3, Carbon3.15.1, CommonMark2.10.7, Flysystem3.36.0 dan komponen Symfony7.4. Paket development Breeze/Collision/PHPUnit mengikuti kompatibilitas framework. Detail otoritatif seluruh versi ada di composer.lock.

Struktur Kernel/providers/routes aplikasi dipertahankan. Konfigurasi Sanctum mengganti nama key middleware CSRF ke validate_csrf_token dan tetap memakai middleware aplikasi. Migration personal_access_tokens termasuk expires_at sudah tersedia: tidak publish migration duplikat dan tidak mengubah schema.

## Pengoperasian

Setiap clone harus `composer install` dari lock terbaru menggunakan PHP8.2 atau lebih baru; jangan `composer update` di production. Docker production memerlukan build ulang image app/scheduler. Volume vendor_data Docker development lama tidak otomatis ikut berubah hanya karena source di-bind mount. Ikuti instruksi pembaruan lokal yang diuji pada catatan hasil akhir di bawah; jangan menganggap container yang masih memakai dependency lama sudah diperbarui.

Tidak mengubah APP_KEY, konfigurasi database, atau file dokumen. Cadangan manifest/lock sebelum perubahan disimpan lokal di tmp/security-dependencies (di-ignore), bukan sumber rollback production resmi.

Docker development lokal sudah diselaraskan dan dinyalakan kembali dengan rangkaian berikut (bukan perintah untuk server kantor):

```powershell
docker compose stop app scheduler
docker compose run --rm --no-deps -e COMPOSER_ALLOW_SUPERUSER=1 app composer install --no-dev --no-scripts --no-interaction --prefer-dist
docker compose run --rm --no-deps app php artisan package:discover --no-ansi
docker compose run --rm --no-deps app php artisan config:clear
docker compose run --rm --no-deps app php artisan view:clear
docker compose up -d app scheduler
docker compose restart webserver
```

Nginx perlu membaca ulang DNS upstream setelah app direcreate. Pada percobaan pertama smoke test mendapat502 karena alamat upstream lama; restart webserver menyelesaikannya. Tidak melakukan migration, seed, penghapusan volume, perubahan key atau reset database.

## Hasil validasi

- `composer audit --locked --no-dev` dan `composer audit --locked` akhir: **0 advisory / 0 abandoned package**; tidak ada advisory ignore atau penonaktifan security blocking. Selain19 advisory production, Symfony YAML pada tooling development juga diperbarui dari6.4.43 ke7.4.23 untuk menutup2 advisory tambahan.
- `composer validate --no-check-publish`: valid. Composer install/discovery paket berhasil pada PHP8.3.36 Windows.
- Docker lokal pada PHP8.2.35 melaporkan Laravel12.71.0; `composer check-platform-reqs --no-dev` seluruhnya lulus. App/scheduler/Nginx berjalan kembali. HTTP login200, register404, URL storage tanpa autentikasi302 (redirect login). Ini smoke test lokal, bukan bukti deployment kantor.
- Regresi Feature/Unit penuh: **99 tes, 1097 assertion lulus**, PHPUnit11.5.57, SQLite :memory:. JUnit/log lokal di tmp/security-dependencies. Opt-in tests/Audit berisi pembuktian cacat historis dan bukan bagian suite regresi.
- Carbon3 memperketat setter bulan: input bulan/tahun laporan sekretariat dan dashboard dinormalisasi menjadi integer, mempertahankan hasil untuk input numerik dari request.
- Factory pengguna dilengkapi role employee (non-admin) dan bidang Umum sesuai foreign key wajib. Tes login memeriksa redirect role tersebut. Tes reset password publik diperbarui untuk memverifikasi endpoint memang nonaktif dan tidak dapat mengganti password, mengikuti route yang telah dinonaktifkan sebelum upgrade; tidak mengaktifkan fitur reset password publik.
- Struktur aplikasi, migration dan data arsip tidak diubah. Upgrade tidak memerlukan migration baru dalam source proyek ini. Tetap review migrate:status/--pretend saat deployment; jangan migrate:fresh.
- Follow-up10November: build runtime/web dan smoke test production lokal selesai, termasuk recreate serta backup–restore. Advisory npm yang ditemukan saat build juga ditangani (Vite7/plugin2); Composer/npm audit akhir bersih. Regresi bertambah menjadi100tes/1098assertion. Lihat PRODUCTION_IMAGE_TESTS.md untuk bukti dan batas hasil; rootless Ubuntu kantor dan scan OS/image tetap terpisah. Semua hasil audit berlaku pada saat pemeriksaan, bukan jaminan bebas kerentanan masa depan.

Panduan acuan: [Laravel11 upgrade](https://laravel.com/docs/11.x/upgrade), [Laravel12 upgrade](https://laravel.com/docs/12.x/upgrade), serta vendor/laravel/sanctum/UPGRADE.md untuk konfigurasi Sanctum4.
