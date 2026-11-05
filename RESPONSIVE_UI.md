# Tampilan responsif — 30 Oktober 2026

Aturan responsif berlaku pada layout Admin, Sekretariat, Staf Bidang, Sub Bidang Hukum dan Tata Kelola, Humas, serta Portal Pegawai. Perpustakaan dan portal konsultasi mengikuti layout role pengguna. Template PDF tetap memakai tata letak cetaknya sendiri.

## Komponen bersama

- `resources/views/layouts/responsive-style.blade.php`: ukuran konten, form, tabel, drawer, kalender, dan modal preview. Diikutkan setelah stylesheet layout masing-masing role.
- `resources/views/layouts/sidebar-controls-script.blade.php`: perilaku sidebar seluruh role, atribut aksesibilitas, fokus keyboard, penutupan melalui Escape/overlay, dan pengaturan ulang saat breakpoint berubah.
- Sidebar menjadi drawer pada lebar maksimal 980 px. Tingginya memakai `100dvh` dengan fallback `100vh`, dapat digulir, dan memperhitungkan safe area bawah.
- Pada desktop, sidebar memakai `position: sticky` dengan tinggi satu viewport agar tetap di tempat saat konten halaman digulir. Menu yang panjang memiliki scroll sendiri. Perilaku scroll, lipat sidebar, dan drawer ponsel telah diperiksa pada seluruh enam role melalui 24 pemeriksaan browser.
- Pada lebar maksimal 640 px, form/panel disusun vertikal, jarak dan judul diperkecil, serta kontrol form menggunakan font minimal 16 px untuk mencegah zoom otomatis saat mengetik pada iPhone.
- Tabel banyak kolom digulir di dalam area tabel. Tabel informasi dua kolom pada detail arsip/surat berubah menjadi pasangan label dan nilai vertikal.
- Kalender tetap tujuh kolom. Trek grid boleh menyusut dan agenda panjang dipotong secara visual; tautan detail dan daftar agenda tetap tersedia.
- Layout login juga disesuaikan untuk layar sempit.

Saat menambah halaman, gunakan `minmax(0, 1fr)` atau `minmax(min(100%, 280px), 1fr)` pada grid agar ukuran minimum tidak memaksa halaman melebar. Gunakan `.table-wrapper` untuk tabel banyak kolom dan `.detail-table` untuk tabel pasangan label/nilai. Hindari memberikan `flex-wrap` pada panel vertikal yang dibatasi tinggi karena isinya dapat berpindah menjadi kolom tambahan.

## Verifikasi

- 58 halaman dirender dari route aplikasi dengan data uji di SQLite `:memory:`; database operasional tidak diubah.
- 522 pemeriksaan browser lulus: 58 halaman pada lebar 280, 320, 360, 390, 430, 768, 844, 1024, dan 1440 px. Pemeriksaan mencakup lebar halaman, elemen yang melebar, ukuran font input, dan perilaku drawer.
- 27 pemeriksaan tambahan pada emulasi ponsel lulus, mencakup layar sentuh, portrait/landscape, fokus keyboard, Escape/overlay, perubahan breakpoint, dan batas modal preview.
- 56 tes regresi terkait lulus dengan 898 assertion.
- Cache Blade lokal dan Docker telah dibersihkan. Tidak ada perubahan dependensi atau migrasi database untuk revisi desain ini.

Pengujian dilakukan dengan browser berbasis Chromium/Edge dan emulasi perangkat. Ini bukan pengujian fisik setiap model ponsel atau setiap versi Safari/Android.

## Proporsi kartu dan teks

Daftar Dokumen Hukum dan kedua ringkasan dashboard Hukum menggunakan `.record-table` dengan `data-label` pada setiap sel. Pada layar maksimal 640 px, baris berubah menjadi kartu berlabel. Daftar dokumen memakai dua kolom informasi, lalu satu kolom pada layar di bawah 360 px. Informasi, tautan, dan aksi tetap berasal dari data yang sama.

Pada desktop, tabel dokumen mempunyai lebar minimum 1080 px dan ruang minimum untuk judul, kategori, departemen/PIC, tanggal, dan aksi. Ringkasan dashboard memakai pembagian kolom otomatis. Header, badge, versi, tanggal, dan tombol tidak dipecah per huruf. Judul panjang ditampilkan maksimal tiga baris dengan teks lengkap tetap berada di DOM dan atribut `title`.

Tambahan 20 pemeriksaan visual lulus pada kedua halaman di 10 lebar layar (280–1920 px), termasuk contoh data seperti screenshot dan judul tanpa spasi yang sangat panjang. Pada viewport desktop 1440 px, tabel dokumen berukuran 1126 px dan tabel ringkasan 500 px, sesuai area kontennya.
