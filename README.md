# Panduan Instalasi & Penggunaan Aplikasi Pembelajaran Bahasa Inggris
## SMP Swasta Nommensen (Berbasis Multimedia & Metode MDLC)

Selamat datang di repositori aplikasi pembelajaran digital Bahasa Inggris SMP Swasta Nommensen. Panduan ini disusun secara sistematis agar Anda atau klien dapat memasang, menjalankan, dan mengoperasikan aplikasi ini pada komputer lokal (localhost) dengan mudah dan lancar.

---

## 📋 Daftar Isi
1. [Prasyarat Sistem (Prerequisites)](#-1-prasyarat-sistem-prerequisites)
2. [Langkah-Langkah Instalasi & Setup Sistem](#-2-langkah-langkah-instalasi--setup-sistem)
   - [Langkah 1: Penempatan Folder Proyek](#langkah-1-penempatan-folder-proyek)
   - [Langkah 2: Menjalankan Web Server XAMPP](#langkah-2-menjalankan-web-server-xampp)
   - [Langkah 3: Membuat Database & Impor File SQL Master](#langkah-3-membuat-database--impor-file-sql-master)
   - [Langkah 4: Pengecekan File Konfigurasi (config.php)](#langkah-4-pengecekan-file-konfigurasi-configphp)
   - [Langkah 5: Menjalankan Aplikasi di Browser](#langkah-5-menjalankan-aplikasi-di-browser)
3. [Informasi Akun Pengguna (Kredensial Default)](#-3-informasi-akun-pengguna-kredensial-default)
   - [Portal Guru / Administrator](#-a-portal-guru--administrator)
   - [Portal Siswa (Multi-Auth NIS & NISN Rombel 7A/7B/7C)](#-b-portal-siswa-peserta-didik)
4. [Struktur dan Fitur-Fitur Unggulan Sistem](#-4-struktur-dan-fitur-fitur-unggulan-sistem)
5. [Peta Struktur Direktori Proyek](#-5-peta-struktur-direktori-proyek)
6. [Solusi Kendala Teknis (Troubleshooting)](#-6-solusi-kendala-teknis-troubleshooting)

---

## ⚙️ 1. Prasyarat Sistem (Prerequisites)

Sebelum menjalankan aplikasi, pastikan perangkat komputer sudah terpasang:
- **XAMPP** (dengan versi PHP 7.4 ke atas dan MySQL / MariaDB).
- **Web Browser Modern** (Google Chrome, Microsoft Edge, atau Mozilla Firefox).

---

## 🚀 2. Langkah-Langkah Instalasi & Setup Sistem

Ikuti 5 tahapan berikut untuk mengaktifkan sistem dari awal:

### Langkah 1: Penempatan Folder Proyek
Pastikan seluruh folder `web-edu-smpnomensen-main` berada di dalam direktori `htdocs` instalasi XAMPP Anda.
- **Lokasi di Windows**: `C:\xampp\htdocs\web-edu-smpnomensen-main`

---

### Langkah 2: Menjalankan Web Server XAMPP
1. Buka aplikasi **XAMPP Control Panel**.
2. Klik tombol **Start** pada modul **Apache**.
3. Klik tombol **Start** pada modul **MySQL**.
4. Pastikan kedua modul berubah warna menjadi **hijau**, menandakan server lokal telah aktif.

---

### Langkah 3: Membuat Database & Impor File SQL Master
Database telah disediakan dalam satu file utuh master: **`db_smp_nomensen_english.sql`**.

1. Buka browser (Chrome / Edge) dan kunjungi alamat phpMyAdmin:
   ```text
   http://localhost/phpmyadmin
   ```
2. Klik menu **Databases** (Basis Data Baru) pada panel atas.
3. Masukkan nama database persis seperti berikut:
   ```text
   db_smp_nomensen_english
   ```
   *(Pilih collation `utf8mb4_general_ci`, kemudian klik tombol **Create / Buat**)*.
4. Klik nama database `db_smp_nomensen_english` yang baru saja dibuat pada daftar di panel sebelah kiri.
5. Klik tab **Import** di bagian atas halaman.
6. Klik tombol **Choose File / Telusuri**, lalu pilih file **`db_smp_nomensen_english.sql`** yang berada di dalam folder proyek ini.
7. Gulir ke bagian paling bawah dan klik tombol **Import / Kirim**.
8. Tunggu beberapa saat hingga muncul notifikasi sukses berwarna hijau (*"Import has been successfully finished"*). Seluruh 8 tabel database beserta 20 bab materi kurikulum, 400 butir bank soal, 71 data akun siswa resmi (Rombel VII-A, VII-B, VII-C), dan **142 data riwayat pengerjaan kuis realistis (fokus Bab 1 & 2)** kini telah terpasang sempurna.

---

### Langkah 4: Pengecekan File Konfigurasi (`config.php`)
Secara default, file `config.php` telah disesuaikan dengan setelan standar XAMPP. Jika setelan XAMPP Anda standar, Anda tidak perlu mengubah apapun:
```php
<?php
$host = 'localhost';
$dbname = 'db_smp_nomensen_english';
$username = 'root'; // Username default XAMPP
$password = '';     // Password default XAMPP (kosong)
```

---

### Langkah 5: Menjalankan Aplikasi di Browser
Buka tab baru pada browser Anda, lalu ketik alamat berikut:
```text
http://localhost/web-edu-smpnomensen-main/
```
Aplikasi akan langsung menampilkan halaman depan (*Landing Page / Splash*) yang memuat logo resmi SMP Swasta Nommensen dan dua tombol akses: **Mulai Belajar (Siswa)** dan **Login Guru / Admin**.

---

## 🔑 3. Informasi Akun Pengguna (Kredensial Default)

Aplikasi ini memiliki dua portal utama dengan hak akses terpisah:

### 👨‍🏫 A. Portal Guru / Administrator & Kepala Sekolah
- **Tautan Login**: `http://localhost/web-edu-smpnomensen-main/admin/login.php`
- **Nama Guru / Kepala Sekolah**: Hedi Diana, S.Pd., Gr
- **NUPTK / NIP (Username)**: `8546774675230253`
- **Kata Sandi (Password)**: `guru123`
- **Profil Sekolah**: SMP Swasta Nommensen
- **Alamat Sekolah**: Jalan Kutacane-Medan Desa Lawe Desky Sabas , Kec. Babul Makmur, Kabupaten Aceh Tenggara, Aceh
- **Hak Akses**: Mengelola 20 bab kurikulum materi dengan visual editor Quill, mengunggah video percakapan & audio pelafalan, meracik butir bank soal kuis & batas waktu standar KKM 70, memantau rekapitulasi laporan nilai kelas (VII-A, VII-B, VII-C), mencetak raport evaluasi resmi format PDF/A4, serta mengelola data siswa (NIS & NISN).

### 👤 B. Portal Siswa (Peserta Didik)
- **Tautan Login**: `http://localhost/web-edu-smpnomensen-main/siswa/login.php`
- **Sistem Autentikasi Fleksibel (Multi-Auth)**: Siswa dapat login menggunakan **NIS** (5 digit) maupun **NISN** (10 digit).
- **Kata Sandi (Password Default Seluruh Siswa)**: `siswa123`
- **Hak Akses**: Menjelajahi Beranda Belajar, membaca modul 20 bab materi (Semester 1 & 2), memutar video animasi & audio pelafalan native speaker, mengerjakan kuis evaluasi anti-contek dengan palet soal dan timer, memantau posisi papan peringkat (leaderboard), melihat riwayat nilai & mencetak raport belajar mandiri (PDF/A4), serta mengganti password mandiri.

#### Contoh Akun Siswa Pengujian Berdasarkan Rombongan Belajar:

| Rombel | NIS | NISN | Nama Siswa | Kata Sandi | Status Database |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **VII-A** | `26001` | `0136442625` | ALENA FELICIA | `siswa123` | Memiliki Riwayat Nilai & Rapor |
| **VII-A** | `26002` | `0142940288` | ARMEN CANE PANGARIBUAN | `siswa123` | Memiliki Riwayat Nilai & Rapor |
| **VII-B** | `26029` | `3142757879` | Adelardo Giofrey Sigalingging | `siswa123` | Memiliki Riwayat Nilai & Rapor |
| **VII-B** | `26030` | `3148274646` | ALI ESER NATANIEL NAINGGOLAN | `siswa123` | Memiliki Riwayat Nilai & Rapor |
| **VII-C** | `26053` | `3122220006` | Amran Efraen S | `siswa123` | Memiliki Riwayat Nilai & Rapor |
| **VII-C** | `26054` | `3133766941` | Delvia Putri Lumbangaol | `siswa123` | Memiliki Riwayat Nilai & Rapor |

*(Tersedia total 71 akun siswa resmi: 28 siswa VII-A, 24 siswa VII-B, dan 19 siswa VII-C)*.

---

## 🌟 4. Struktur dan Fitur-Fitur Unggulan Sistem

1. **Penerapan Metode MDLC & 4 Pilar Media Terpadu**:
   - **Modul Teks Terstruktur**: Rangkuman kaidah tata bahasa, kosakata, dan dialog komunikatif per bab materi.
   - **Video Animasi Interaktif (`.mp4`)**: Visualisasi situasi percakapan dunia nyata untuk memahami ekspresi dan konteks sosial.
   - **Laboratorium Audio Pelafalan (`.mp3`)**: Latihan *listening* dan *pronunciation* penutur asli (*native speaker*) yang terintegrasi di modul.
   - **Kuis Evaluasi Berwaktu (KKM 70)**: Ujian interaktif dengan timer hitung mundur dan penentuan kelulusan standar KKM 70.

2. **Fokus Penuh Kelas 7 & Pemisahan 3 Rombel (VII-A, VII-B, VII-C)**:
   - Sistem difokuskan secara mendalam pada kurikulum Bahasa Inggris Kelas 7 (20 Bab: Semester 1 & 2).
   - Pemisahan data akademik rombongan belajar (7A, 7B, 7C) pada laporan nilai guru, filter manajemen siswa, dan rekapitulasi raport.

3. **Sistem Raport Evaluasi Belajar Siswa (Cetak PDF / A4 Resmi)**:
   - Terintegrasi pada portal Siswa (`siswa/cetak_raport.php`) dan Guru (`admin/cetak_raport.php`).
   - Format cetak standar dokumen resmi sekolah: memuat Kop Surat SMP Swasta Nommensen, identitas lengkap siswa (NIS & NISN), tabel capaian materi kuis, kalkulasi rata-rata skor, predikat ketuntasan (KKM 70), catatan perkembangan guru, serta 3 kolom tanda tangan legal (Kepala Sekolah, Guru Pengampu, dan Orang Tua/Wali).
   - Dilengkapi filter periode cetak: **Laporan Mingguan**, **Laporan Bulanan**, atau **Seluruh Periode Pembelajaran**.

4. **Papan Peringkat (Leaderboard) Kuis & Kartu Bintang Prestasi**:
   - Menampilkan peringkat Top 5 nilai kuis tertinggi di beranda siswa, landing page publik, dan portal guru.
   - Kartu *Highlight Bintang Prestasi* di portal guru untuk mengapresiasi siswa dengan capaian nilai tertinggi per rombel maupun kelas gabungan.

5. **Mesin Kuis Anti-Contek & Navigasi Palet Nomor Soal**:
   - Palet nomor soal interaktif dengan indikator status warna (soal yang sudah dijawab vs belum dijawab).
   - **Mode Anti-Contek**: Tanpa umpan balik instan benar/salah saat memilih opsi untuk menjaga kejujuran ujian.
   - Timer hitung mundur berbasis JavaScript dengan mekanisme *auto-submit* saat durasi habis.
   - Proteksi sesi via `localStorage`: Jawaban sementara tersimpan aman saat browser dimuat ulang secara tidak sengaja.
   - Penghapusan tombol ulangi kuis langsung di layar hasil untuk mencegah manipulasi nilai instan.

6. **Editor Visual WYSIWYG (Quill) & Pusat Unggah Media Langsung**:
   - Portal guru dilengkapi editor materi visual modern tanpa perlu menulis tag HTML manual, disertai template 1-klik (Modul Standar, Percakapan, Tabel Kosakata) dan fitur *Live Preview*.
   - Menu `admin/upload_media.php` memungkinkan guru mengunggah berkas audio dan video langsung ke server dengan pemutar media instan.

7. **Akses Cepat Modul Tematik**:
   - Halaman khusus untuk memperdalam aspek bahasa tertentu: Kosakata (`siswa/vocabulary.php`), Tata Bahasa (`siswa/grammar.php`), dan Percakapan (`siswa/conversation.php`).

8. **Keamanan & Integritas Rekayasa Data**:
   - Prepared Statements PDO di seluruh modul (kebal terhadap serangan *SQL Injection*).
   - Enkripsi kata sandi menggunakan hashing Bcrypt (`password_hash`).
   - Proteksi sesi aman dengan `session_regenerate_id(true)` dan pembersihan input via `htmlspecialchars()`.

---

## 📁 5. Peta Struktur Direktori Proyek

```text
web-edu-smpnomensen-main/
│
├── admin/                         # Portal Administrator & Guru Pengampu
│   ├── cetak_raport.php           # Cetak PDF Raport Resmi Siswa (Kop & TTD 3 Pihak)
│   ├── dashboard.php              # Dashboard metrik & pemantauan aktivitas real-time
│   ├── kelola_materi.php          # CRUD modul 20 bab dengan Visual Quill Editor
│   ├── kelola_siswa.php           # Manajemen data siswa (NIS, NISN, Rombel VII-A/B/C)
│   ├── kelola_soal.php            # Bank soal kuis pilihan ganda & KKM 70
│   ├── laporan_nilai.php          # Rekapitulasi nilai, highlight bintang prestasi & log kuis
│   ├── login.php                  # Halaman autentikasi NUPTK guru
│   ├── logout.php                 # Terminasi sesi aman guru
│   ├── pengaturan.php             # Profil & pembaruan kata sandi guru
│   └── upload_media.php           # Pusat unggah media audio (.mp3) & video (.mp4)
│
├── assets/                        # Sumber Daya Multimedia Statis
│   ├── audio/                     # Berkas audio pelafalan native speaker (.mp3)
│   ├── css/                       # Stylesheet kustom tema Navy (#1A3A5C) & Quill
│   ├── img/                       # Logo resmi SMP Swasta Nommensen
│   ├── js/                        # Pustaka skrip editor visual Quill
│   └── video/                     # Berkas video animasi percakapan (.mp4)
│
├── includes/                      # Komponen Bersama & Middleware Keamanan
│   ├── auth_admin.php             # Guard proteksi autentikasi guru
│   ├── auth_siswa.php             # Guard proteksi autentikasi siswa
│   ├── footer.php                 # Footer antarmuka terpadu
│   ├── header.php                 # Header Bootstrap 5.3.3 & Bootstrap Icons
│   └── sidebar.php                # Fixed navigation sidebar responsif
│
├── siswa/                         # Portal Belajar Peserta Didik
│   ├── cetak_raport.php           # Cetak PDF Raport Evaluasi Mandiri Siswa
│   ├── conversation.php           # Akses cepat modul percakapan kontekstual
│   ├── ganti_password.php         # Pembaruan kata sandi mandiri siswa
│   ├── grammar.php                # Akses cepat modul tata bahasa
│   ├── hasil_kuis.php             # Pengumuman skor akhir & ketuntasan KKM
│   ├── kuis.php                   # Katalog kuis evaluasi & penanda status
│   ├── kuis_kerjakan.php          # Mesin ujian berwaktu anti-contek & palet soal
│   ├── kuis_proses.php            # Evaluator skor kuis server-side yang aman
│   ├── login.php                  # Autentikasi multi-auth (NIS atau NISN)
│   ├── logout.php                 # Terminasi sesi aman siswa
│   ├── materi_detail.php          # Tampilan detail 4 pilar multimedia bab materi
│   ├── menu.php                   # Beranda siswa, statistik bento & leaderboard
│   ├── riwayat.php                # Histori pengerjaan kuis & grafik nilai
│   └── vocabulary.php             # Akses cepat modul penguasaan kosakata
│
├── config.php                     # Konfigurasi koneksi basis data PDO MySQL
├── db_smp_nomensen_english.sql    # Basis Data Master (8 Tabel, 20 Bab, 71 Akun, 142 Log Nilai Bab 1 & 2)
├── index.php                      # Landing page publik berlogo dengan Leaderboard
├── panduan_singkat_audio.md       # Panduan teknis aktivasi berkas audio pembelajaran
├── README.md                      # Panduan Instalasi & Penggunaan Klien
└── REPORT.md                      # Laporan Master & Dokumentasi Lengkap Proyek
```

---

## 🛠️ 6. Solusi Kendala Teknis (Troubleshooting)

| Gejala Kendala | Penyebab Umum | Solusi Cepat |
| :--- | :--- | :--- |
| **"Koneksi database gagal"** | Modul MySQL di XAMPP belum aktif atau database belum dibuat. | Buka XAMPP Control Panel, pastikan Apache & MySQL berstatus **Start** (hijau), dan pastikan database di phpMyAdmin telah dibuat dengan nama `db_smp_nomensen_english`. |
| **Data kuis / siswa kosong saat pertama kali dibuka** | Database baru dibuat namun belum mengimpor data SQL master. | Buka phpMyAdmin, klik database `db_smp_nomensen_english`, klik tab **Import**, pilih file **`db_smp_nomensen_english.sql`**, lalu klik **Kirim**. |
| **Tampilan CSS berantakan atau tidak terupdate** | Peramban web menyimpan cache CSS versi terdahulu. | Tekan kombinasi tombol **Ctrl + F5** (Hard Refresh) pada keyboard untuk memuat ulang stylesheet terbaru. |
| **Audio atau Video tidak bersuara / tidak berputar** | Berkas fisik media belum berada di direktori aset. | Pastikan file video berekstensi `.mp4` berada di folder `assets/video/` dan file audio berekstensi `.mp3` berada di folder `assets/audio/`. Baca selengkapnya di [`panduan_singkat_audio.md`](panduan_singkat_audio.md). |
| **Gagal login siswa** | Menggunakan format username yang keliru. | Siswa dapat login menggunakan **NIS** (contoh: `26001`) atau **NISN** (contoh: `0136442625`) dengan kata sandi default `siswa123`. |

---
*Dokumentasi ini disusun untuk mempermudah operasional, serah terima sistem, dan pengujian aplikasi.*
