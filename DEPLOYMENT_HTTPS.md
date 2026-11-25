# Cutover HTTPS SIMARTA: simarta.tirtahita.my.id

Domain ditentukan pengguna: simarta.tirtahita.my.id. Target server:10.0.100.237, port backend8500. Ini rencana konfigurasi, bukan bukti DNS/Proxy Host telah diterapkan.

## Status pemeriksaan

Pemeriksaan10November2026: SSH BatchMode akun mhs1 ditolak, sehingga server belum diubah otomatis. Domain awal simarta.thb.go.id tidak resolve. Domain final dari senior, simarta.tirtahita.my.id, berhasil diakses melalui HTTPS; /login mengembalikan200 dengan action/asetHTTPS. Namun HTTPS / mengembalikan302 ke http://simarta.tirtahita.my.id/login; HTTP /login masih200 dan cookie sesi belum Secure. Perlu Force SSL pada proxy, APP_URL finalHTTPS serta SESSION_SECURE_COOKIE=true, kemudian pengujian ulang. Tidak menguji login atau fitur terautentikasi karena tidak menggunakan kredensial pengguna.

## Nilai final yang dituju

- DNS internal kantor: A record simarta.tirtahita.my.id ->10.0.100.237, dibuat pengelola DNS.
- NPM Proxy Host: domain simarta.tirtahita.my.id, Forward Scheme http, Forward Hostname/IP10.0.100.237, Forward Port8500. Tidak menggunakan alamat IP container rootless atau localhost di container NPM.
- SSL: pilih sertifikat valid yang mencakup simarta.tirtahita.my.id dan dipercaya perangkat kantor; aktifkan Force SSL. Jangan meminta sertifikat publik tanpa verifikasi DNS dan metode challenge yang dapat digunakan; CA internal dapat memerlukan distribusi trust oleh IT.
- Environment aplikasi: APP_URL=https://simarta.tirtahita.my.id dan SESSION_SECURE_COOKIE=true setelah endpoint proxy/HTTPS siap. WEB_PORT=8500; WEB_BIND_IP=10.0.100.237 untuk jalur IP host yang disetujui pengguna. Jangan bind0.0.0.0 tanpa kebutuhan. Pertahankan APP_KEY, DB_PASSWORD, volume dan nama project yang sudah ada.
- NPM menimpa X-Forwarded-Proto dengan skema koneksi klien. Isi docker/nginx/trusted-proto.map hanya setelah peer NPM teramati dari log backend dan jalur tersebut dipastikan tidak dapat ditiru klien lain. Format: IP_PEER_YANG_DIVERIFIKASI $http_x_forwarded_proto;
- Jika rootless menyamarkan semua klien menjadi satu gateway, IP itu bukan identitas NPM yang eksklusif. Jangan mempercayainya secara otomatis; perlu pembatasan jalur backend atau mekanisme autentikasi proxy yang dirancang/diperiksa terpisah sebelum cutover HTTPS.

## Urutan penerapan manual di SSH yang sudah terautentikasi

1. Verifikasi DNS dari server dan laptop serta akses administrasi NPM. Pastikan hostname tidak dipakai aplikasi lain. Buat backup konfigurasi environment dan map dengan izin privat sebelum mengubahnya.
2. Dengan jalur backend/restriksi yang telah ditentukan, ubah hanya WEB_BIND_IP menjadi10.0.100.237. Validasi `dc config --quiet`; `dc up -d --no-deps --no-build --wait --wait-timeout 180 webserver` menerapkan bind baru. Tahap ini membuat backend tersedia pada interface LAN tersebut: lakukan terkoordinasi, bukan mengekspos tanpa pembatasan yang disepakati.
3. Buat/pilih Proxy Host dan sertifikat SIMARTA di NPM, tanpa mengubah Proxy Host kantor lain. Uji NPM dapat menjangkau backend. Verifikasi peer dari log webserver dan sanitasi header sebelum mengisi whitelist. Berkas map dipasang bind: gunakan editor yang mempertahankan inode atau recreate webserver setelah menggantinya; nginx reload saja tidak selalu melihat file hasil atomic replace.
4. Setelah forwarding terpercaya dan TLS tersedia, ubah APP_URL/SESSION_SECURE_COOKIE sesuai nilai final. `dc config --quiet`, lalu `dc up -d --no-build --wait --wait-timeout 180 app scheduler webserver` dan `dc restart webserver` untuk membaca ulang DNS upstream bila app direcreate. Entry point menghasilkan ulang config cache dari environment baru. Tidak perlu migration, seeder, penggantian key atau build image baru untuk perubahan environment/map.
5. Uji `https://simarta.tirtahita.my.id/login` tanpa bypass verifikasi TLS: status200, form/action/redirect tetap HTTPS, cookie Secure, login/CSRF, upload/preview/download serta PDF. Request backend dengan header palsu tidak boleh dipercaya. Akses dokumen anonim tetap ditolak.

## Rollback konfigurasi

Jika cutover gagal, pulihkan backup environment/map, gunakan kembali WEB_BIND_IP=127.0.0.1, APP_URL=http://127.0.0.1:8500 dan SESSION_SECURE_COOKIE=false untuk commissioning lewat SSH tunnel. Recreate app/scheduler/webserver dari image/tag yang sama dan cek health. Jangan mengganti key/password, menghapus volume, atau mematikan daemon sistem. Koordinasikan penonaktifan/perbaikan Proxy Host SIMARTA saja dengan admin NPM.

## Yang masih memerlukan akses/informasi

Resolver/DNS internal, sertifikat yang tersedia, akses administrasi NPM dan identitas/jalur peer proxy. Konfigurasi ini belum dapat diterapkan dari sesi Codex karena autentikasi SSH tidak tersedia. Jangan mengirim password SSH/NPM, private key atau isi environment melalui chat.
