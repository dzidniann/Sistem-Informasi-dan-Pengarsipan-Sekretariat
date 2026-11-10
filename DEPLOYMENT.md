# SIMARTA: deployment Ubuntu dengan Docker Rootless

Status: konfigurasi persiapan, bukan bukti deployment kantor berhasil. Tidak ada akses SSH atau perubahan server kantor dilakukan. Compose development tetap terpisah. Jalankan seluruh perintah production dengan `-f compose.production.yml`; jangan gabungkan dengan docker-compose.yml.

## Pemeriksaan server yang dilaporkan pengguna (9 November 2026)

Host thb730xd / 10.0.100.237, akun mhs1 menggunakan Docker context rootless. Port 8001/8500/8501/9001 tidak terlihat LISTEN pada pemeriksaan tersebut. Pengguna kemudian memilih **WEB_PORT=8500** untuk template production. Pilihan pengguna ini bukan bukti persetujuan admin IT; ketersediaan port dan kebijakan kantor tetap harus diperiksa sebelum start. Nilai dapat diganti lewat environment.

HTTP loopback port 80 dan 81 memberi 200/OpenResty: ini menunjukkan listener, bukan bukti akses administrasi NPM atau Proxy Host telah tersedia. HTTPS loopback gagal `tlsv1 unrecognized name`: belum membuktikan sertifikat rusak, karena hostname/SNI aplikasi yang benar belum diketahui. thb730xd yang resolve 127.0.1.1 bukan hostname aplikasi yang telah disetujui. Jangan menebak domain atau melewati verifikasi TLS untuk go-live.

Default bridge rootless terlihat 172.17.0.0/16; routing server mencakup 172.17.0.0/16 sampai 172.28.0.0/16. Compose sekarang tidak menetapkan IPAM/subnet/IP statis. Alokasi otomatis tetap HARUS dibandingkan dengan routing LAN/VPN dan jaringan daemon lain sebelum container aplikasi dijalankan.

/home/mhs1 dimiliki mhs1; /mnt/thb_storage dimiliki root. Belum ada lokasi/jadwal backup yang disetujui, crontab atau systemd user timer. Kepemilikan home tidak otomatis menjadikannya tujuan backup yang disetujui. Tidak membuat timer, direktori storage kantor, atau permission host secara otomatis.

## Audit dan keputusan

- Framework diperbarui ke Laravel12.71.0 untuk menutup advisory yang tidak tersedia patch-nya di Laravel10; PHP requirement ^8.2, platform resolusi Composer8.2.35 sesuai baseline Docker. Composer.lock dan package-lock.json dipakai, `composer install --no-dev` serta `npm ci && npm run build`. Detail upgrade/regresi ada di DEPENDENCY_SECURITY_UPDATE.md.
- Frontend Vite7.3.8/plugin Laravel2.1.0/Tailwind/Alpine; seluruh layout menggunakan @vite. Build membutuhkan Node^20.19.0 atau >=22.12.0 (image build memakai22.14.0). Image production mengandung public/build; npm dev server tidak diperlukan. Advisory npm yang ditemukan saat build telah ditangani; lihat PRODUCTION_IMAGE_TESTS.md.
- GD/EXIF, PDO MySQL, mbstring, bcmath, intl, zip, curl, pcntl, opcache dan qpdf tersedia dalam Dockerfile.production. Optimasi file dan pembentukan PDF tetap di aplikasi; format/batas validasi tidak diubah.
- Dokumen disimpan dalam storage/app/protected-uploads; sebagian modul memakai storage/app/legal-private. Karena itu SELURUH storage dipersistenkan, bukan hanya folder public. public/storage tidak dibuat. /storage tetap melewati Laravel dan authorization. Nginx hanya berisi public assets dan tidak memiliki volume upload.
- MySQL 8 memakai named volume, user aplikasi non-root, tanpa published port; root password berupa file secret terpisah. Kredensial Laravel dan MySQL berasal dari DB_DATABASE/DB_USERNAME/DB_PASSWORD yang sama. Mengubah env tidak merotasi password database yang telah diinisialisasi.
- Registrasi publik tertutup, akses dokumen berdasarkan role/pemilik/bidang. Tidak mengubah business logic. TrustedProxy yang sebelumnya wildcard sekarang hanya menerima alamat dari config/proxies.php; default tidak mempercayai proxy.
- Scheduler: legal:refresh-document-statuses harian pukul 00:00 WITA, log:reset tanggal 1 pukul 01:00. Jalankan SATU scheduler; jangan tambah cron host atau scale scheduler. log:reset memang menghapus riwayat lama: minta kebijakan retensi IT sebelum go-live.
- Queue default sync; tidak ditemukan job ShouldQueue/dispatch aplikasi yang membutuhkan worker terpisah. Jangan ubah QUEUE_CONNECTION sebelum menyiapkan worker dan pengujian.
- Tidak ditemukan pemanggilan API eksternal wajib pada service aplikasi yang diperiksa. SMTP/S3 opsional belum dikonfigurasi. MAIL_MAILER=log hanya untuk commissioning: isi email dapat masuk log. Konfirmasi SMTP dan retensi sebelum memakai fitur email nyata.
- APP_DEBUG=false, log Laravel stderr dan rotasi log container 10MB x3 per service. Monitor ukuran storage/framework, dokumen, backup dan Docker Root Dir; rotasi Docker bukan backup.
- Production tidak bind mount source/vendor/bootstrap cache. Cache konfigurasi dibuat saat startup tiap container, bukan saat build. Session dan file cache ada di storage bersama, sehingga lock scheduler dan sesi tidak hilang ketika app direcreate.
- Compose menggunakan project terpisah tanpa container_name, host network, socket Docker, privileged, phpMyAdmin atau database port. Bridge khusus bukan `internal:true`: egress masih diperlukan bila SMTP diaktifkan; aturan egress dibahas bersama IT.
- PHP berjalan sebagai www-data, Nginx sebagai nginx pada port internal 8080. Named volume storage baru diinisialisasi dari direktori image milik www-data. Tidak memakai chmod 777 atau chown host ke UID container secara sembarangan pada rootless.

## BLOCKER / PERLU KONFIRMASI ADMIN IT

1. Verifikasi `uname -m` = x86_64; image build laptop bukan bukti arsitektur server.
2. Port >=1024 dan bind address yang diizinkan. WEB_PORT=8500 dipilih pengguna; cek ulang listener dan kebijakan IT sebelum start. WEB_BIND_IP tetap 127.0.0.1, belum mengekspos aplikasi ke LAN.
3. Hostname, DNS internal, TLS certificate dan admin Nginx Proxy Manager (NPM).
4. NPM berjalan dalam container atau proses host? Bisa menjangkau loopback host atau harus interface LAN? `127.0.0.1` di container NPM adalah container itu sendiri, bukan server host. IT harus menentukan upstream, jaringan serta pembatasan akses backend.
5. Validasi subnet bridge hasil alokasi otomatis terhadap seluruh route host/VPN dan network Docker yang bisa diperiksa. Akun rootless tidak dapat melihat seluruh network daemon sistem: minta IT melengkapi inventaris. Jika bentrok, jangan mulai aplikasi; minta alokasi/pool yang disetujui IT tanpa mengubah daemon sendiri.
6. Rootless dapat menyamarkan IP sumber. Jangan mempercayai alamat gateway bersama yang dapat dipakai klien lain. IT harus memastikan backend hanya dapat dicapai NPM sebelum mengizinkan forwarded HTTPS. Jika tidak bisa membedakan peer, jangan go-live sampai jalur proxy dibatasi/diisolasi.
7. Kebijakan patch/security: Laravel12.71.0 telah lolos audit Composer dan regresi lokal (lihat DEPENDENCY_SECURITY_UPDATE.md). Jalankan audit/scan ulang dan tinjau dukungan framework/PHP/image menjelang go-live; hasil ini bukan jaminan semua kerentanan aplikasi/OS sudah terselesaikan. Build production dan smoke test rootless tetap wajib.
8. Tentukan sumber data awal, akun admin unik, SMTP, retensi audit log, jadwal/tujuan backup terenkripsi, RPO/RTO dan penanggung jawab restore. Jangan memakai kredensial demo seeder.
9. Versi image contoh merupakan baseline kompatibilitas untuk diuji, bukan klaim patch terbaru. IT harus menyetujui image hasil scan dan digest immutable untuk semua base image/MySQL. APT masih menggunakan repository Debian yang berubah: reproduksi byte-identik memerlukan snapshot package mirror; simpan image hasil release dengan digest untuk rollback.

## 1. Laptop Windows / PowerShell

Edit proyek utama C:\Magang\SSO PDAM. .dist/github-publish adalah salinan publishing lama; bila dipakai, sinkronkan file deployment baru secara eksplisit sebelum commit. Jangan menganggap push salinan lama mencakup perubahan ini.

```powershell
cd "C:\Magang\SSO PDAM"
git status --short
# Review file yang dipilih; jangan git add seluruh folder tanpa pemeriksaan.
git check-ignore .env.production .secrets/mysql-root-password
# .env.production.example harus TIDAK di-ignore.
ssh mhs1@10.0.100.237
```

Commit/push sendiri ke branch yang disepakati. Jangan kirim .env laptop, database, dokumen internal, .dist, tmp atau backup dalam Git/image. Untuk transfer file terbatas gunakan SCP hanya source/config yang telah direview, bukan seluruh folder kerja. Jangan salin data produksi kantor ke laptop.

## 2. SSH Ubuntu: pemeriksaan tanpa sudo

```bash
id
uname -m
docker context show
docker --context rootless info
systemctl --user is-active docker
loginctl show-user mhs1 -p Linger
ss -ltn
df -h "$HOME" /mnt/thb_storage
mkdir -p /home/mhs1/simarta
cd /home/mhs1/simarta
git clone --branch ando https://github.com/dzidniann/Sistem-Informasi-dan-Pengarsipan-Sekretariat.git source
cd source
git checkout <COMMIT_RELEASE_YANG_SUDAH_DIREVIEW>
```

Ganti placeholder, jangan tempel literal. Pastikan tidak menimpa direktori yang sudah ada. Semua operasi di bawah memakai rootless secara eksplisit; tidak menggunakan /var/run/docker.sock. Jangan ubah daemon sistem, firewall, service kantor atau NPM sendiri.

## 3. Environment dan secret di server

```bash
umask 077
cp .env.production.example .env.production
mkdir -p .secrets
openssl rand -base64 48 > .secrets/mysql-root-password
chmod 700 .secrets
chmod 600 .env.production
# Compose secret memakai bind mount; proses mysql non-root harus bisa membaca file.
# Direktori induk 700 mencegah user host lain menelusuri file ini.
chmod 644 .secrets/mysql-root-password
nano .env.production
```

Isi DB_PASSWORD acak unik yang disimpan secara aman, APP_URL, WEB_PORT dan bind IP sesuai keputusan IT. TRUSTED_PROXY_HOSTS=webserver hanya me-resolve DNS service Nginx pada jaringan Compose saat request; tidak mempercayai seluruh subnet. TRUSTED_PROXIES tetap kosong kecuali ada kebutuhan alamat spesifik yang direview. Tidak ada APP_NETWORK_SUBNET atau APP_NGINX_IP yang perlu ditebak. Isi APP_KEY untuk instalasi BARU langsung di editor dari hasil generator yang tidak dicatat dalam chat/log. Jika memindahkan database terenkripsi yang ada, pertahankan APP_KEY asal secara aman: jangan key:generate ulang. Hindari terminal sharing/history secret. Jangan pernah `cat .env.production` atau `docker compose config` tanpa --quiet ke laporan bersama.

APP_ENV=production, APP_DEBUG=false; untuk HTTPS SESSION_SECURE_COOKIE=true. Tes HTTP sementara memerlukan false, kemudian kembalikan true sebelum go-live. Jangan ubah APP_URL HTTP menjadi HTTPS hanya dengan forceScheme; konfigurasi trusted proxy harus benar.

```bash
dc() { docker --context rootless compose --env-file .env.production -f compose.production.yml "$@"; }
dc config --quiet
dc build --pull app webserver
```

Sebelum release, pin PHP_IMAGE/NODE_IMAGE/COMPOSER_IMAGE/NGINX_IMAGE/MYSQL_IMAGE ke tag@sha256 yang disetujui, ambil digest dari registry terpercaya, scan image dan simpan hasilnya. Gunakan RELEASE_TAG SHA commit unik, jangan overwrite image release sebelumnya. Tidak ada npm atau Composer install di host Ubuntu.

## 4. Database, data awal, dan migration

Sebelum `up`, setelah port/bind disetujui, buat HANYA network project agar alokasinya dapat diperiksa (langkah manual di server, bukan dijalankan Codex):

```bash
dc create --no-deps webserver
docker --context rootless network ls --filter label=com.docker.compose.project=simarta-office
docker --context rootless network inspect simarta-office_private --format '{{json .IPAM.Config}}'
ip -4 route show table all
ip -6 route show table all
```

Ganti nama project pada filter/nama network jika COMPOSE_PROJECT_NAME diubah. `create` membuat container Nginx berstatus stopped beserta network dan dapat membuat resource project; tidak memulai listener. Jangan `start/up` jika alokasi tumpang tindih atau belum dicek IT. Jika Docker gagal mengalokasikan pool, laporkan dan minta alokasi resmi; jangan edit rootless daemon/default-address-pools sendiri. Membuat network tidak mengubah layanan kantor lain, tetapi tetap lakukan hanya pada jadwal dan project yang disepakati.

```bash
dc up -d db
dc ps
dc run --rm --no-deps app php artisan migrate:status
dc run --rm --no-deps app php artisan migrate --pretend --force
```

`migrate:status` dapat gagal pada database baru tanpa tabel migrations; ini bukan alasan reset. Review seluruh SQL/migration sebelum menjalankan. Untuk database berisi data, lakukan backup sesuai bagian 7 TERLEBIH DAHULU. `--pretend` membantu review, bukan jaminan migration bebas efek eksternal. Setelah review dan persetujuan:

```bash
dc run --rm --no-deps app php artisan migrate --force
```

Jangan migrate:fresh, migrate:refresh, db:wipe atau menjalankan DatabaseSeeder penuh. Seeder tersebut berisi UserSeeder/LegalSeeder/demo: tentukan provisioning master dan akun admin dengan pemilik aplikasi. Jika belum ada akun admin aman, itu blocker go-live. Import data yang disetujui harus sekaligus mencakup DB dan seluruh storage serta kunci yang cocok, bukan DB saja. Transfer data uji laptop ke server hanya bila disetujui; data kantor tetap di kantor.

Untuk unggahan legacy: `dc run --rm --no-deps app php artisan files:protect-public --dry-run`, review lalu jalankan tanpa --dry-run setelah backup. Tidak menjalankan storage:link.

## 5. Jalankan dan uji

```bash
dc up -d app webserver scheduler
dc ps
dc exec -T webserver nginx -t
dc exec -T app php artisan schedule:list
dc logs --tail=80 app webserver scheduler
# Gunakan port backend yang benar-benar disetujui, bukan placeholder ini:
curl -I http://127.0.0.1:<PORT_DISETUJUI>/login
```

Health app memeriksa FPM, SELECT 1 dan direktori upload writable; web memeriksa halaman login; scheduler memeriksa heartbeat <300 detik. Heartbeat menunjukkan loop hidup, bukan semua job sukses: lihat exit/error log dan validasi perubahan status legal harian. Healthcheck tidak otomatis me-restart container unhealthy: restart policy hanya menangani proses exit/daemon restart. Siapkan pemantauan status oleh operator.

Sebelum go-live uji dengan data uji: login tiap role, sesi/CSRF setelah HTTPS, upload PDF/DOC/DOCX/gambar pada modul relevan, nama file ber-ID, optimasi qpdf/GD, preview/download, penolakan akses file user/bidang lain, /storage tanpa login, notulensi, PDF laporan bulanan dan CSR Excel. Batas aplikasi: dokumen umum/hukum 8MB, tata kelola PDF 5MB, foto 4MB, batch CSR 14MB; PHP file20MB/post25MB dan Nginx25MB hanya plafon transport, tidak melonggarkan validasi aplikasi. NPM juga harus menerima body minimal25MB dan timeout >120s, oleh IT. Uji recreate container lalu file/sesi/DB masih tersedia.

## 6. Nginx Proxy Manager (hanya admin IT)

- Forward Scheme: http, Forward Hostname/IP: alamat backend yang dapat dicapai NPM menurut topologi IT, Forward Port: WEB_PORT yang disetujui. Jangan gunakan app:9000 atau port database.
- Skema kandidat: NPM -> `http://10.0.100.237:<PORT_DISETUJUI>` -> Nginx aplikasi8080 -> PHP-FPM. Bila IT menyetujui port8500 dan bind LAN, set WEB_BIND_IP=10.0.100.237 dan WEB_PORT=8500 secara manual. Kandidat ini belum izin deploy. Default template tetap loopback untuk mencegah exposure LAN tak sengaja; NPM container tidak dapat memakai 127.0.0.1 host begitu saja. IT harus memastikan routing dan akses backend hanya dari proxy yang dimaksud, tanpa membuka 0.0.0.0 atau mengubah firewall sendiri.
- NPM mempertahankan Host asli, menimpa X-Forwarded-Proto dengan skema koneksi klien, dan mengatur TLS/hostname.
- Pada Nginx aplikasi, `docker/nginx/trusted-proto.map` hanya ditambah IP peer NPM yang terverifikasi dari access log. Jangan menambah `default`, `*`, seluruh LAN, atau gateway rootless yang dapat ditiru. trusted-proxies.conf sengaja tidak melakukan real_ip rewriting agar pemeriksaan peer tetap berdasarkan socket asli. Alamat klien audit pada rootless bisa merupakan gateway: jangan klaim IP asli sebelum diuji.
- Laravel hanya percaya alamat IPv4 yang di-resolve dari service DNS `webserver` pada network project. Alamat diperiksa ulang per request agar recreate Nginx tidak memerlukan IP statis. Ini berbeda dari peer NPM yang dilihat Nginx. DNS service tidak ditemukan berarti tidak ada alamat tambahan yang dipercaya. Jangan memasukkan nama host dari input pengguna atau menghubungkan container tak terkait ke jaringan project. Host forwarding tidak dipercaya dari header X-Forwarded-Host.
- Setelah edit map: `dc exec -T webserver nginx -t`, kemudian `dc exec -T webserver nginx -s reload`. Uji URL form/action dan redirect tetap HTTPS, cookie Secure, tidak mixed content; request langsung dengan header palsu tidak boleh mendapat trust. Bila peer rootless tidak bisa diisolasi, stop dan koordinasikan IT.

## 7. Backup konsisten (SSH Ubuntu saja)

Jadwalkan maintenance dengan pengguna. Backup menyimpan dokumen sensitif; gunakan lokasi berizin, terenkripsi saat disalin ke storage backup kantor. DB dan storage harus merupakan satu titik konsisten. Contoh berikut menghentikan seluruh penulis; jangan melanjutkan service jika backup gagal tanpa investigasi.

```bash
umask 077
# Set SIMARTA_BACKUP_ROOT pada .env.production ke path absolut yang disetujui IT.
# Ambil hanya variabel ini; jangan source seluruh .env sebagai script shell.
SIMARTA_BACKUP_ROOT="$(dc run --rm --no-deps --entrypoint printenv app SIMARTA_BACKUP_ROOT)"
: "${SIMARTA_BACKUP_ROOT:?BLOCKER: lokasi backup belum disetujui/diisi}"
case "$SIMARTA_BACKUP_ROOT" in /*) ;; *) echo 'Backup root harus path absolut'; exit 1;; esac
test -d "$SIMARTA_BACKUP_ROOT" && test -w "$SIMARTA_BACKUP_ROOT" || exit 1
backup_dir="$SIMARTA_BACKUP_ROOT/$(date +%Y%m%d-%H%M%S)"
mkdir -p "$backup_dir"
dc stop webserver scheduler app
dc exec -T db sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysqldump --single-transaction --no-tablespaces --set-gtid-purged=OFF -u"$MYSQL_USER" "$MYSQL_DATABASE"' > "$backup_dir/database.sql"
# Periksa exit code dump; jika gagal, STOP dan jangan gunakan dump parsial.
dc run --rm --no-deps --entrypoint tar app -czf - -C /var/www/html/storage . > "$backup_dir/storage.tar.gz"
test -s "$backup_dir/database.sql" && gzip -t "$backup_dir/storage.tar.gz"
cp .env.production "$backup_dir/environment.private"
cp .secrets/mysql-root-password "$backup_dir/mysql-root.private"
git rev-parse HEAD > "$backup_dir/commit.txt"
sha256sum "$backup_dir/database.sql" "$backup_dir/storage.tar.gz" > "$backup_dir/SHA256SUMS"
dc up -d app webserver scheduler
```

Jalankan langkah per langkah dan periksa setiap exit code, atau gunakan shell `set -e` dalam script backup terkontrol. Backup environment/root secret tidak boleh masuk Git/image/chat. Simpan image release/digest dan konfigurasi proxy juga; lakukan restore drill di project/volume BARU sebelum mengandalkan backup. Jangan down -v, volume rm atau docker system prune.

SIMARTA_BACKUP_ROOT bukan volume upload/database, tidak di-mount ke web/app, dan tidak otomatis membuat backup. Direktori induk harus lebih dulu disediakan dengan izin privat setelah persetujuan IT. Jangan gunakan /mnt/thb_storage tanpa direktori dan hak akses yang disediakan IT; jangan chown/chmod mount tersebut sendiri. Jika tujuan disetujui di home mhs1, tentukan kapasitas, salinan di media/server berbeda dan retensi, karena backup di disk/host yang sama tidak melindungi kegagalan host. Belum ada penjadwalan otomatis: setelah restore drill, pilih user timer/crontab dengan IT, lock agar job tidak bertumpuk, alert kegagalan, dan uji reboot/linger. Jangan menganggap restart container sama dengan backup.

## 8. Restore dan rollback

Restore adalah tindakan operasional dengan persetujuan karena menimpa data. Uji dahulu dalam project rootless lain, env/port/subnet baru, tanpa menghubungkan NPM kantor. Buat database kosong baru, volume storage baru, env dan key yang cocok dengan backup. Cek checksum, `gzip -t`, dan daftar isi tar sebelum extract. Jangan menggunakan volume live untuk latihan.

```bash
# Hanya pada project restore terisolasi yang SUDAH diverifikasi:
dc up -d db
dc exec -T db sh -c 'MYSQL_PWD="$MYSQL_PASSWORD" exec mysql -u"$MYSQL_USER" "$MYSQL_DATABASE"' < /PATH_BACKUP/database.sql
dc run --rm --no-deps --entrypoint tar app -xzf - -C /var/www/html/storage < /PATH_BACKUP/storage.tar.gz
dc up -d app webserver scheduler
```

Archive storage dibuat oleh user www-data; extract sebagai user sama ke volume kosong, tidak pakai sudo/chmod777. Uji hak baca/tulis file dan seluruh skenario login/download. Cadangan memuat cache lama: sebelum start release restore, `dc run --rm --no-deps app php artisan view:clear`. Jangan cache:clear di production tanpa memahami lock scheduler.

Update: backup, catat commit/image/env lama, stop webserver/scheduler/app, checkout commit baru, set RELEASE_TAG baru, build app/webserver, review migrate --pretend lalu migrate --force setelah disetujui, clear compiled views (`dc run --rm --no-deps app php artisan view:clear`), up dan smoke test. Build gagal: jangan migrasi. Jangan build ulang release lama dengan tag yang sama.

Setelah recreate app, pastikan Nginx membaca ulang alamat upstream: `dc restart webserver`, lalu cek health dan HTTP. `up -d` saja tidak selalu me-recreate webserver bila image/config-nya tidak berubah, sementara alamat IP app mungkin berubah.

Rollback kode: kembalikan commit, RELEASE_TAG dan konfigurasi pasangan, gunakan image release lama yang tersimpan lalu up. Ini hanya aman jika schema kompatibel. Jangan otomatis migrate:rollback. Jika schema tidak kompatibel, perlu restore DB+storage+key dari backup konsisten saat maintenance; perubahan sejak backup akan hilang sesuai RPO yang disetujui. Jangan hapus volume live untuk rollback coba-coba.

## 9. Troubleshooting dan batas verifikasi

- 502: cek app health, FPM9000 dan jaringan. 500: log app tanpa menyalin secret, koneksi DB, APP_KEY, storage permission. 419/redirect HTTP: APP_URL, Secure cookie, Host/proto NPM dan whitelist peer. 413: NPM/Nginx/PHP plafon transport; 422: validasi aplikasi tetap berlaku.
- Permission denied rootless: cek UID mapping dan named volume, jangan chown seluruh /home atau chmod777. Storage lama dengan owner salah membutuhkan prosedur migrasi terencana, bukan perbaikan sudo spontan.
- Port bentrok: hentikan hanya project SIMARTA bila perlu, minta port lain kepada IT; jangan stop container kantor.
- Jangan membagikan output inspect/env/config yang berisi kredensial. Log error tidak boleh dipublikasikan tanpa sanitasi.
- Hasil validasi lokal dicatat dalam DEPLOYMENT_VALIDATION.md. Keberhasilan Docker Desktop tidak membuktikan rootless Ubuntu, NPM, TLS, reboot/linger, backup terjadwal atau kapasitas kantor telah teruji.

Referensi: [Docker Rootless](https://docs.docker.com/engine/security/rootless/), [port publishing](https://docs.docker.com/engine/network/port-publishing/), [Laravel trusted proxies](https://laravel.com/docs/10.x/requests#configuring-trusted-proxies), [Laravel deployment](https://laravel.com/docs/10.x/deployment).
