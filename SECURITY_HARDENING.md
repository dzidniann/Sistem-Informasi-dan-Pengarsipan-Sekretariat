# Perbaikan keamanan — 5 November 2026

## Akun dan pengujian

- Registrasi publik ditutup secara default melalui `auth.allow_registration = false`. GET dan POST `/register` tidak terdaftar pada konfigurasi tersebut.
- Controller registrasi juga menolak pemanggilan ketika fitur ditutup. Jika kelak diaktifkan secara eksplisit, role `employee` dan bidang `Umum` wajib tersedia; tidak ada fallback ke role/bidang pertama. Akun baru selalu nonaktif, tidak langsung login, dan menunggu aktivasi Admin.
- Model User menyembunyikan password/remember token dari serialisasi. Pencatatan aktivitas baru tidak lagi mencatat hash password. Log lama tidak dihapus oleh revisi ini.
- PHPUnit memakai SQLite `:memory:` secara eksplisit agar pengujian tidak memakai database aplikasi.

## Berkas internal

Unggahan Arsip, Nomor Surat, Notulensi Sekretariat, Dokumen Hukum, dan lampiran konsultasi sekarang memakai disk `protected` di `storage/app/protected-uploads`. Modul Humas, Tata Kelola, kegiatan dan notulensi Hukum tetap memakai storage privatnya sendiri.

URL lama `/storage/{path}` dipertahankan melalui `ProtectedUploadController` dengan middleware `auth`, `aktif`, dan `nocache`. Akses ditentukan dari record database, bukan dari keberadaan file saja:

- Arsip: bidang sesuai hak akses pengguna; Super Admin dapat mengakses semua.
- Notulensi dan surat Sekretariat: role Sekretariat pada bidang terkait, atau Super Admin.
- Dokumen Hukum: Legal/Super Admin; pengguna internal lainnya hanya mendapat dokumen terpublikasi yang masih berlaku.
- Lampiran konsultasi/pesan: pemohon atau Legal/Super Admin. Kesamaan bidang tidak memberikan akses ke konsultasi orang lain.
- File tanpa record yang dapat diakses, path traversal, dan path di luar root privat ditolak. MIME aktif seperti HTML tidak dibuka inline.

Nginx meneruskan `/storage/` ke Laravel. Apache memakai rewrite khusus sebelum pengecekan file statis. Konfigurasi filesystem tidak lagi membuat tautan `public/storage`.

`php artisan files:protect-public --dry-run` memeriksa unggahan lama. Jalankan migrasi berikut setelah mencadangkan database, pada jendela pemeliharaan:

```sh
php artisan down
php artisan files:protect-public
php artisan up
php artisan optimize:clear
```

Jalankan perintah sebagai pengguna aplikasi/webserver pada Linux; contohnya `docker compose exec --user www-data app php artisan files:protect-public`. Pastikan pengguna tersebut dapat menulis `storage/app` dan folder backup privat. Jangan membuat ulang symlink `public/storage`. Jika masih ada symlink lama, lepaskan tautannya setelah migrasi selesai tanpa menghapus direktori target.

Command menyalin tiap file ke backup privat, memeriksa SHA-256, menyalin ke tujuan privat, memeriksa checksum lagi, baru menghapus salinan publik. Tujuan yang berisi data berbeda tidak ditimpa. Database path tidak berubah. Migrasi lokal telah memindahkan 21 file; checksum dan akses baca proses PHP-FPM Docker diperiksa. Backup database dan file berada di `storage/app/private-backups/`, bukan pada folder publik atau Git.

## Git dan riwayat konfigurasi lama

`.gitignore` menolak `.env.*`, kecuali dua file contoh, serta `.dist`, seluruh `tmp`, `.vs`, dan `.DS_Store`. Docker juga mengecualikan `.dist` dan backup privat.

`.env.local.backup` sudah tidak ada pada versi terbaru branch `ando`, tetapi ada pada commit awal GitHub. APP_KEY di dalamnya berbeda dari key lokal dan Docker yang diperiksa. Jika key lama masih dipakai di lingkungan lain, ganti secara terkoordinasi setelah menilai data terenkripsi yang bergantung pada key tersebut; jangan menjalankan `key:generate` sembarangan pada database berisi data terenkripsi.

`scripts/prepare_history_cleanup.py` membuat kandidat repo bare dalam `.dist`, bundle backup privat, dan rencana SHA. Script tidak melakukan push. Author, committer, pesan commit, serta source tree dipertahankan, kecuali penghapusan `.env.local.backup`; tanda tangan commit lama tidak dapat dipertahankan ketika hash berubah. Gunakan `--local-ando .dist/github-publish` untuk menyertakan commit keamanan lokal yang belum di-push.

Penulisan ulang `main` dan `ando` harus disetujui pemilik repo dan dikoordinasikan dengan collaborator. Push hanya kedua ref yang tercantum dalam rencana, secara atomic dengan `--force-with-lease` terhadap SHA lama yang sudah diverifikasi. Jangan memakai `git push --mirror` atau `--force` tanpa lease. Jika branch di GitHub berubah sejak persiapan, buat rencana baru; jangan menimpa perubahan teman.

Setelah penulisan ulang, collaborator perlu mengambil ulang riwayat/checkout bersih. Tag, PR tersembunyi, fork, atau cache GitHub dapat memerlukan penanganan pemilik repo/GitHub Support. Kandidat lokal bukan bukti bahwa riwayat GitHub sudah dibersihkan.

## Verifikasi dan batas cakupan

73 tes / 1037 assertions lulus pada SQLite di memori, mencakup akses file per role/bidang, publikasi/kedaluwarsa, konsultasi, path traversal, akun nonaktif, migrasi dan konflik file, registrasi, serta regresi modul yang sudah ada. HTTP Docker memverifikasi `/storage/...` mengarah ke login dan `/register` menghasilkan 404.

Revisi ini menyelesaikan temuan registrasi, akses upload langsung, dan pencegahan konfigurasi lokal ikut Git. Ini bukan sertifikasi keamanan menyeluruh: advisory dependency, XSS tampilan lama, dan temuan audit lain di luar cakupan masih perlu ditinjau sebelum production.
