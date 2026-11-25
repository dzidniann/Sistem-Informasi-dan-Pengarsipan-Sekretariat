# Pengujian image production lokal — 10 November 2026 (WITA)

Pengujian memakai Docker Desktop Linux/amd64 pada laptop. Tidak mengakses SSH/server kantor, tidak mengubah NPM/firewall, dan tidak memigrasi database development. Port kantor8500 tidak dibuka; port percobaan18500/18501 hanya bind127.0.0.1. Project QA bernama simarta-smoke-20261110 dan simarta-smoke-20261110-restore, dengan database simarta_smoke serta named volume terpisah. Semua akun/dokumen adalah fixture sintetis.

## Build dan pemeriksaan image

- Dockerfile.production target runtime dan web berhasil dibangun. PHP8.2.35, Composer2.8.6, Node22.14.0, Nginx1.28.0-alpine; MySQL8.0.42 diuji sebagai service database.
- Tag lokal simarta-app:production-check-20261110: sha256:e9e30b86f1498c0299de90bfc032bb8f3da4664e03ae6b7f77571546485d345f.
- Tag lokal simarta-web:production-check-20261110: sha256:df0d01059a878ee050dad728d0c698b40331b54bb3ee47a8783b618e67b1536b.
- Runtime berjalan sebagai www-data; Nginx sebagai nginx pada8080; keduanya Linux/amd64. Image tidak berisi .env/.env.docker, public/hot, public/storage atau file unggahan laptop. public/build/manifest.json tersedia. Source code tidak di-bind mount ke app production.
- Migration berhasil pada database MySQL BARU simarta_smoke. Percobaan awal dilakukan sebelum health database selesai dan ditolak connection refused; diulang setelah healthy tanpa reset/destructive migration, lalu seluruh migration berhasil. Ini bukan review/persetujuan migration data kantor.

## Perbaikan yang ditemukan saat validasi

1. AppServiceProvider tidak lagi forceScheme HTTPS hanya berdasarkan APP_ENV. URL mengikuti request dan proxy terpercaya, sementara APP_URL dipakai untuk URL di konteks CLI. Ini memungkinkan commissioning backend HTTP; HTTPS melalui NPM tetap wajib dikonfigurasi dan diuji sebelum go-live.
2. Default fastcgi_params meneruskan REMOTE_ADDR klien, sehingga whitelist DNS service webserver di Laravel tidak cocok. Image Nginx sekarang membuat salinan parameter tanpa REMOTE_ADDR, lalu mengisi identitas peer dengan $server_addr. IP klien yang telah disanitasi diteruskan via X-Forwarded-For; HTTPS hanya diambil dari whitelist peer NPM eksplisit. Tidak mempercayai X-Forwarded-Host.
3. npm ci build awal melaporkan7 advisory npm. Vite diperbarui ke7.3.8, laravel-vite-plugin ke2.1.0, patch dependency lain mengikuti constraint; tanpa audit fix --force. Node minimum sekarang ^20.19.0 atau >=22.12.0. Lockfile final dan npm ci melaporkan0 vulnerability. Tidak mengubah desain atau alur fitur.

## Smoke test HTTP dan scheduler

Script guarded di tests/Deployment/production_smoke.py hanya menerima localhost, port non-development/non-kantor dan project simarta-smoke-*. Fixture dibuat hanya jika database bernama simarta_smoke.

- Login/dashboard untuk super-admin, sekretariat, staf-bidang, legal-admin, humas, employee: lulus.
- Registrasi publik404, /storage tanpa login302, .env tidak terbuka: lulus.
- Upload PDF tata kelola, nama file diawaliID, optimasi server, preview inline, download attachment dan penolakan user tanpa akses: lulus.
- Upload notulensi sekretariat dan foto PNG, proses GD, preview PDF/foto terotorisasi: lulus.
- Kegiatan bulanan, preview dan download PDF laporan: lulus.
- Ekspor CSR Excel/PDF: lulus.
- legal:refresh-document-statuses diuji dengan tanggal fixture: EXPIRED, EXPIRING_SOON dan NEED_REVIEW sesuai harapan. schedule:list dan heartbeat scheduler tersedia. Tidak mengklaim sudah menunggu eksekusi midnight nyata.

## Proxy

tests/Deployment/production_proxy.py memakai override map sementara khusus QA, bukan mengedit map production. Header HTTPS palsu pada default whitelist kosong tidak mengubah form action HTTP. Whitelist peer QA yang tepat menghasilkan form action HTTPS, sementara spoof X-Forwarded-Host diabaikan. Setelah tes, override dihapus dari container dan whitelist kosong dipulihkan. Ini validasi forwarding lokal, bukan validasi sertifikat/hostname/topologi NPM kantor.

## Penyimpanan dan restore

- Recreate app/scheduler/webserver dengan named volume yang sama: row dokumen tetap ada dan SHA256 file cocok.
- Seluruh penulis dihentikan sebelum mysqldump dan arsip storage dibuat; backup dummy berhasil.
- Restore ke project simarta-smoke-20261110-restore dengan database/volume BARU: app, database, Nginx dan scheduler healthy; row dokumen serta SHA256 file cocok dengan sumber. Login HTTP pada port18501 memberi200.
- Setelah drill, container dan network kedua project QA dihapus melalui compose down TANPA -v; seluruh volume QA tetap ada. Tidak menjalankan prune atau menghapus volume.
- Aplikasi development ssopdam pada port8001 tetap memberi login HTTP200. Server kantor tidak diakses.

tests/Deployment/production_persistence.py membatasi operasi pada project simarta-smoke-*, memakai data dummy, dan tidak menghapus volume.

## Hasil akhir

Regresi PHP final: **100 tes / 1098 assertion lulus**. Audit Composer terkini10November:0 advisory. Audit npm lock final dan npm ci:0 vulnerability. Smoke test HTTP diulang pada image final dan mencakup CSS/JS build yang tersaji tanpa Vite dev server. Hasil ini tidak mencakup scan vulnerability paket OS/base image atau uji beban.

## Reproduksi

Gunakan environment QA yang dibuat sendiri dengan secret acak, APP_ENV_FILE menunjuk file tersebut, DB_DATABASE=simarta_smoke, COMPOSE_PROJECT_NAME berawalan simarta-smoke-, WEB_BIND_IP=127.0.0.1, port18500, APP_URL=http://127.0.0.1:18500 dan SESSION_SECURE_COOKIE=false. Jangan pakai environment/database kantor. Secret/data QA lokal berada di tmp/production-smoke-20261110 dan tidak di-commit atau dimasukkan image.

Setelah build, DB healthy dan migration QA selesai, jalankan script dengan Python3 dan Docker CLI:

```text
python tests/Deployment/production_smoke.py ABSOLUTE_QA_ENV http://127.0.0.1:18500 simarta-smoke-20261110
python tests/Deployment/production_proxy.py ABSOLUTE_QA_ENV
python tests/Deployment/production_persistence.py ABSOLUTE_QA_ENV
```

Raw log QA berada di tmp/production-smoke-20261110 serta tmp/security-dependencies. Jangan membagikan file environment, secret atau backup meskipun berisi data uji.

## Batas hasil

Rootless Ubuntu kantor, subnet/port resmi, jalur NPM, domain/sertifikat, kapasitas dan scan vulnerability OS/image belum divalidasi. Audit Composer/npm bersih bukan jaminan seluruh risiko sistem hilang. Persetujuan dan blocker IT pada DEPLOYMENT.md tetap berlaku; jangan menyatakan aplikasi kantor sudah deployed.
