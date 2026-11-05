# Status review dan kedaluwarsa dokumen Sub Bidang Hukum

Diperbarui: **30 Oktober 2026**. Aturan berikut sesuai `LegalDocument`, command `RefreshLegalDocumentStatuses`, dan jadwal di `app/Console/Kernel.php`.

## Aturan tanggal

Status dokumen yang sudah dinyatakan berlaku mengikuti `expiry_date`:

| Kondisi tanggal | Status |
|---|---|
| Tanpa tanggal kedaluwarsa, atau tersisa lebih dari 30 hari | `ACTIVE` |
| Tersisa 0–30 hari, termasuk tanggal kedaluwarsa | `EXPIRING_SOON` |
| Tanggal kedaluwarsa sudah lewat | `EXPIRED` |

Batasnya **30 hari kalender**, bukan selalu satu bulan kalender. Perhitungan memakai timezone aplikasi, saat ini `Asia/Makassar` (WITA). Dokumen tetap berlaku sampai akhir tanggal kedaluwarsanya. Jika `review_date` sudah ditetapkan, tanggal kedaluwarsa yang sudah lewat didahulukan sebagai EXPIRED. Selama belum kedaluwarsa, `review_date` yang jatuh hari ini atau sebelumnya menghasilkan NEED_REVIEW, termasuk untuk DRAFT. Tanpa `review_date`, DRAFT dan NEED_REVIEW tetap mengikuti workflow manual.

Model menghitung ulang ketika dokumen disimpan, tanggal diperpanjang/dihapus, atau tahap diubah menjadi Berlaku. EXPIRING_SOON/EXPIRED bukan pilihan status manual. Jika persetujuan Berlaku diberikan kepada dokumen yang tanggalnya sudah lewat, hasil tersimpan langsung EXPIRED.

Perintah berikut menyinkronkan dokumen tanpa perlu mengeditnya:

```powershell
php artisan legal:refresh-document-statuses
```

Command memproses batch 100 ID, transaksi dan row lock. Hanya status yang benar-benar berubah yang disimpan dan dicatat melalui Spatie Activity Log. Pengulangan pada kondisi yang sama tidak menggandakan perubahan/log. Pelaku sistem tidak dipalsukan sebagai akun pengguna dan perintah ini tidak menghapus dokumen/lampiran.

## Scheduler lokal dan server

Command dijadwalkan setiap **00.00 WITA**, dengan `withoutOverlapping()`. Definisi jadwal saja belum cukup; proses pemanggil Laravel scheduler harus berjalan.

Untuk pengembangan lokal, jalankan pada terminal terpisah dari `php artisan serve`:

```powershell
php artisan schedule:work
```

Jika mesin mati saat jadwal, jalankan command sinkronisasi sekali ketika aplikasi/server kembali aktif. `schedule:run` tidak otomatis memutar ulang semua jadwal yang terlewat.

Docker Compose menyediakan service `scheduler` dengan loop `schedule:run` setiap menit dan `restart: unless-stopped`. Setelah menyiapkan environment/volume dan koneksi database container, contoh pemeriksaannya:

```powershell
docker compose up -d --build scheduler
docker compose ps scheduler
docker compose exec scheduler php artisan schedule:list
docker compose exec scheduler php artisan legal:refresh-document-statuses
```

Di dalam jaringan Compose, host database adalah service `db` dan port database internal 3306; port host development yang dipublikasikan bukan port antarkontainer. Service `scheduler` memakai source dan konfigurasi aplikasi dari volume yang dipasang. Ubah konfigurasi lalu restart/rebuild sesuai perubahan deployment.

Loop saat ini membuang output `schedule:run` ke `/dev/null`; tidak adanya log scheduler bukan bukti command berhasil. Gunakan `schedule:list`, eksekusi manual, pemeriksaan status data, dan log Laravel untuk diagnosis. Runtime Docker belum diverifikasi dalam pembaruan dokumentasi ini.

Untuk server Linux tanpa Docker, pasang cron di akun layanan yang sesuai, memakai path absolut instalasi server:

```cron
* * * * * cd /path/aplikasi && php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/path/aplikasi` dan executable PHP sesuai server. Pastikan konfigurasi/cache timezone dan akses database cocok dengan proses web.

Jadwal aplikasi juga memuat `log:reset` setiap tanggal 1 pukul 01.00 untuk menghapus Activity Log lebih lama dari dua bulan. Menyalakan scheduler menjalankan seluruh jadwal yang jatuh tempo, bukan hanya status dokumen.

Periksa jadwal lokal dengan `php artisan schedule:list`. Fitur status otomatis tidak membutuhkan migration baru; migration penggantian nama bidang tanggal 20 Oktober terpisah dari aturan kedaluwarsa.

## Publikasi dan hasil verifikasi

Dokumen ACTIVE atau EXPIRING_SOON yang belum lewat tanggal kedaluwarsa boleh dipublikasikan. Daftar/detail Perpustakaan memeriksa tanggal aktual sehingga dokumen expired tersembunyi walaupun pembaruan status scheduler terlambat. Flag publikasi tetap disimpan. Mekanisme ini tidak melindungi URL berkas publik lama secara langsung; lihat [FILE_ACCESS.md](FILE_ACCESS.md) dan A01 dalam [audit](AUDIT_SISTEM_2026-10-16.md).

Pada **20 Oktober 2026**, suite terpilih lulus **29 test / 481 assertion**. `LegalDocumentExpiryTest` mencakup batas 31/30/1/0/-1 hari, pergantian hari WITA, perpanjangan/penghapusan tanggal, alur review, idempotensi command, Activity Log, respons perubahan status, Perpustakaan, dan jumlah publikasi dashboard. Suite lain mencakup regresi kategori, kegiatan/notulensi, laporan, rename bidang, konsultasi, dan akses log. Perintah lengkap ada di [LEGAL_MODULE.md](LEGAL_MODULE.md).

Keempat kelas mengisolasi database ke SQLite dalam memori. Hasil ini bukan uji runtime scheduler production, MySQL, atau Docker. Pemeriksaan command pada data lokal sebelumnya terpisah dari pengujian dan tidak membuktikan scheduler telah berjalan terus-menerus pada server lain.


## Jadwal review dan penyelesaian review

Kalender menampilkan semua dokumen yang memiliki `review_date` sesuai bulan/tahun yang dipilih, tanpa menunggu status NEED_REVIEW. Agenda 7 hari mendatang juga mencantumkan jadwal review dari hari ini sampai tujuh hari ke depan. Daftar Perlu Review terpisah tetap menampilkan dokumen yang memang berstatus NEED_REVIEW, termasuk yang belum diberi tanggal.

Perintah dan scheduler yang sama menyinkronkan tanggal review serta tanggal kedaluwarsa setiap pukul 00.00 WITA. Status NEED_REVIEW dipertahankan selama review belum diselesaikan; menggeser tanggal ke masa depan saja tidak otomatis menyatakan dokumen disetujui. Petugas dapat mengatur tanggal review berikutnya melalui Edit Dokumen, lalu mengubah tahap menjadi ACTIVE. Jika review_date masih hari ini atau masa lalu, ACTIVE akan dihitung kembali menjadi NEED_REVIEW.

Untuk localhost, `php artisan serve` tidak menjalankan scheduler. Gunakan `php artisan schedule:work` pada terminal terpisah. Sinkronisasi langsung dapat dilakukan dengan `php artisan legal:refresh-document-statuses`. Docker menggunakan service scheduler yang sudah ada, tanpa migrasi database baru.
