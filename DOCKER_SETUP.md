# Menjalankan proyek dengan Docker

Setup 26 Oktober 2026: database Docker dimulai kosong, dengan 7 pengguna, 5 role, dan 7 bidang dari database lokal. Password akun dipertahankan. Data transaksi tidak diimpor. MariaDB XAMPP melaporkan kerusakan tabel sistem saat penyalinan; pengguna diambil dari salinan pemulihan di `tmp/mysql-recovery-copy`, bukan dengan memperbaiki database asli. Instance pemulihan dihentikan setelah penyalinan.

Jalankan perintah dari folder proyek dengan Docker Desktop (Linux containers) aktif.

```powershell
docker compose up -d
docker compose ps
```

- Aplikasi: http://localhost:8001
- phpMyAdmin: http://localhost:8081 (server `db`)
- MySQL dari Windows: `127.0.0.1:3308`

Konfigurasi container tersimpan di `.env.docker`, termasuk APP_KEY dan kredensial database. File ini diabaikan Git dan tidak dimasukkan ke build image. `.env` tetap digunakan untuk menjalankan aplikasi langsung di Windows. Login phpMyAdmin menggunakan DB_USERNAME dan DB_PASSWORD dari `.env.docker`.

Kode aplikasi dan berkas upload memakai folder proyek. Dependensi Composer, cache bootstrap, dan database menggunakan volume Docker terpisah. Data database Docker tidak otomatis tersinkron dengan database XAMPP.

Memeriksa PHP dan log:

```powershell
docker compose exec app php -v
docker compose exec app composer check-platform-reqs --no-dev
docker compose logs --tail=50 app webserver scheduler
```

Setelah mengubah dependensi atau Dockerfile:

```powershell
docker compose build --pull app scheduler
docker compose up -d
docker compose exec app composer install --no-dev --optimize-autoloader --no-interaction
docker compose exec app php artisan config:clear
```

Migrasi database Docker, bila diperlukan:

```powershell
docker compose exec app php artisan migrate --force
```

Menghentikan lingkungan tanpa menghapus data:

```powershell
docker compose stop
```

Jangan gunakan `docker compose down -v` jika ingin mempertahankan database: opsi `-v` menghapus volume data.
