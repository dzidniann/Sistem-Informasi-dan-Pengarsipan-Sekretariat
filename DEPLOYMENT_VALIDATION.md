# Validasi persiapan production — 9–10 November 2026 (WITA)

**Hasil terbaru10November:** build runtime/web lengkap berhasil pada Docker Desktop;100tes/1098assertion, smoke HTTP enamrole, upload/PDF/GD/CSR, proxy whitelist, recreate dan backup–restore ke volume baru lulus. Composer/npm audit final bersih. Lihat PRODUCTION_IMAGE_TESTS.md. Catatan build belum selesai di bawah adalah riwayat9November, bukan status terkini. Rootless dan NPM kantor belum diuji.

Belum ada deployment ke server kantor; tidak ada SSH, migration, perubahan database/volume atau NPM kantor. Volume dependency Docker development di laptop kemudian diperbarui dan container lokal dinyalakan ulang; data arsip/database tidak dimigrasi atau di-reset.

Pembaruan dependency berikutnya: Laravel12.71.0 dengan PHP requirement^8.2. Audit Composer production DAN development akhir bersih (0 advisory/abandoned); regresi penuh99tes/1097assertion lulus. Temuan19 advisory di bagian audit awal di bawah adalah kondisi sebelum pembaruan. Lihat DEPENDENCY_SECURITY_UPDATE.md untuk cakupan dan instruksi update. Build production lengkap/rootless kantor tetap belum terverifikasi.

## Revisi setelah laporan lanjutan server

Pembaruan berikutnya atas instruksi pengguna: template sekarang memakai WEB_PORT=8500, tetap dapat di-override. Rendering Compose diverifikasi menghasilkan 127.0.0.1:8500 -> container8080. Tidak membuka listener atau menjalankan container; persetujuan IT dan ketersediaan aktual belum diverifikasi. Catatan port kosong di bawah merupakan hasil pengujian versi sebelumnya.

- Menghapus seluruh IPAM/IP statis dari Compose; alokasi bridge otomatis dan inspeksi hasilnya wajib sebelum start. Tidak membuat network lokal/server selama validasi ini.
- TRUSTED_PROXY_HOSTS=webserver me-resolve hanya service Nginx proyek per request; tidak mempercayai wildcard/subnet. Pada revisi awal, port8500 masih kandidat dan WEB_PORT template kosong; lihat pembaruan pilihan pengguna di atas.
- SIMARTA_BACKUP_ROOT kosong, tujuan backup wajib disetujui, absolut, sudah ada dan writable sebelum langkah backup; tidak ada penjadwalan atau bind mount backup otomatis.
- PHPUnit proxy + protected uploads terbaru: **15 tes, 70 assertion lulus**, termasuk DNS proxy lokal dan penolakan header palsu.
- Compose config dengan override port18500 dan19001: lolos, database/app/scheduler tidak memiliki published port, tidak ada fixed IPAM/subnet/IP. Compose menormalisasi ipam menjadi objek kosong; objek itu bukan alokasi subnet statis. Port kosong ditolak. Ini pemeriksaan rendering konfigurasi, bukan membuka/listen pada port tersebut.
- PHP lint middleware/config proxy lulus. Dockerfile.production tidak memerlukan perubahan tambahan untuk jaringan otomatis; image production tetap belum lulus build lengkap sebagaimana catatan sebelumnya.

## Hasil yang benar-benar dijalankan

- `docker compose --env-file .env.production.example -f compose.production.yml config --quiet`: lulus dengan override sementara non-rahasia APP_ENV_FILE, DB_PASSWORD dan WEB_PORT untuk validasi saja. Tidak membuat .env.production nyata atau memilih port kantor.
- PHP lint TrustProxies, config/proxies.php, docker/production/health.php: lulus.
- PHPUnit TrustedProxyConfigurationTest + ProtectedUploadAccessTest: **14 tes / 69 assertion lulus**, SQLite sementara. Mencakup header HTTPS/Host palsu, proxy whitelist, otorisasi file, file privat dan migrasi upload. Bukan bukti semua fitur aplikasi/infra lulus.
- `nginx -t` memakai image lokal nginx:stable-alpine sebagai user nginx, upstream stub host app, konfigurasi production dan map proxy: lulus. Percobaan pertama menemukan permission uwsgi_temp; lokasi uwsgi/scgi dipindahkan ke /tmp dan tes ulang lulus.
- `sh -n docker/production/entrypoint.sh` melalui shell Linux container: lulus.
- Git ignore: .env.production dan .secrets/mysql-root-password terabaikan; .env.production.example tidak terabaikan.
- `composer audit --locked --no-dev`: **gagal keamanan, 19 advisory pada 7 paket**. Laravel/framework (5), league/commonmark (6), league/flysystem (1), symfony/http-foundation (4), symfony/http-kernel (1), symfony/mime (1), symfony/routing (1). Paket tidak diperbarui. Tinjau applicability dan versi perbaikan, rencanakan patch/upgrade disertai regresi sebelum go-live. Jumlah advisory dapat berubah ketika database advisory diperbarui.
- Build `docker build -f Dockerfile.production --target runtime -t simarta-production-check:local .` dicoba pada Docker Desktop. Registry metadata berhasil diambil, context sekitar 542KB; unduhan layer berjalan sangat lambat. Dihentikan setelah sekitar 11 menit saat masih mengunduh base image. **Build lengkap belum terverifikasi dan image production belum dinyatakan berhasil.** Tidak ada perubahan container development yang berjalan.

## Wajib diselesaikan sebelum go-live

Ulang build runtime DAN web dari file final, scan/pin image yang disetujui IT, lalu jalankan smoke test dengan DB/volume data uji terpisah. Uji startup non-root, isi volume baru, healthcheck, restart/recreate, scheduler, restore drill dan seluruh fitur penting. Sintaks Nginx dengan image lokal bukan tes runtime target nginx:1.28.0-alpine. Rootless Ubuntu, arsitektur x86_64, port, subnet, sumber IP NPM, TLS, reboot/linger dan backup terjadwal belum diuji. Lihat DEPLOYMENT.md untuk prosedur dan blocker IT.
