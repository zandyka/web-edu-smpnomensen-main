# Panduan Instalasi & Penggunaan Aplikasi Pembelajaran Bahasa Inggris
## SMP Swasta Nommensen (Berbasis Multimedia & Metode MDLC)

Selamat datang di repositori aplikasi pembelajaran digital Bahasa Inggris SMP Swasta Nommensen. Panduan ini disusun secara sistematis agar Anda atau klien dapat memasang, menjalankan, dan mengoperasikan aplikasi ini pada komputer lokal (localhost) dengan mudah dan lancar.

---

## 📋 Daftar Isi
1. [Prasyarat Sistem (Prerequisites)](#-1-prasyarat-sistem-prerequisites)
2. [Langkah-Langkah Instalasi & Setup Sistem](#-2-langkah-langkah-instalasi--setup-sistem)
   - [Langkah 1: Penempatan Folder Proyek](#langkah-1-penempatan-folder-proyek)
   - [Langkah 2: Menjalankan Web Server XAMPP](#langkah-2-menjalankan-web-server-xampp)
   - [Langkah 3: Membuat Database & Impor File SQL](#langkah-3-membuat-database--impor-file-sql)
   - [Langkah 4: Pengecekan File Konfigurasi (config.php)](#langkah-4-pengecekan-file-konfigurasi-configphp)
   - [Langkah 5: Menjalankan Aplikasi di Browser](#langkah-5-menjalankan-aplikasi-di-browser)
3. [Informasi Akun Pengguna (Kredensial Default)](#-3-informasi-akun-pengguna-kredensial-default)
4. [Struktur dan Fitur Unggulan Sistem](#-4-struktur-dan-fitur-unggulan-sistem)
5. [Solusi Kendala Teknis (Troubleshooting)](#-5-solusi-kendala-teknis-troubleshooting)

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

### Langkah 3: Membuat Database & Impor File SQL
Database telah disediakan dalam satu file utuh: **`db_smp_nomensen_english.sql`**.

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
8. Tunggu beberapa saat hingga muncul notifikasi sukses berwarna hijau (*"Import has been successfully finished"*). Seluruh 8 tabel database beserta 20 bab materi kurikulum dan akun pengguna kini telah terpasang.

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

### 👨‍🏫 A. Portal Guru / Administrator
- **Tautan Login**: `http://localhost/web-edu-smpnomensen-main/admin/login.php`
- **Nama Guru**: Hedi Diana, S.Pd., Gr
- **NUPTK / NIP (Username)**: `8546774675230253`
- **Kata Sandi (Password)**: `guru123`
- **Hak Akses**: Mengelola 20 bab kurikulum materi, mengunggah video percakapan & audio pelafalan, meracik butir soal kuis & batas waktu, memantau rekapitulasi laporan nilai kelas, dan mengelola data siswa.

### 👤 B. Portal Siswa (Peserta Didik)
- **Tautan Login**: `http://localhost/web-edu-smpnomensen-main/siswa/login.php`
- **Nama Siswa**: Fernando Michael
- **Kelas**: VII-A
- **NIS (Username)**: `26001`
- **Kata Sandi (Password)**: `siswa123`
- **Hak Akses**: Menjelajahi Beranda Belajar, membaca modul 20 bab materi (Semester 1 & 2), memutar video animasi & audio pelafalan native speaker, mengerjakan kuis evaluasi berwaktu standar KKM 70, melihat riwayat nilai & status kelulusan, dan mengganti password mandiri.

---

## 🌟 4. Struktur dan Fitur Unggulan Sistem

1. **Penerapan Metode MDLC & 4 Pilar Media Terpadu**:
   - **Modul Teks**: Rangkuman kurikulum tematik terstruktur per bab.
   - **Video Animasi**: Visualisasi percakapan situasional dunia nyata (`.mp4`).
   - **Audio Pelafalan**: Latihan listening & pronunciation penutur asli (`.mp3`).
   - **Kuis Evaluasi**: Ujian interaktif dengan timer hitung mundur dan penilaian instan standar KKM 70.
2. **Katalog 20 Bab Materi Kelas VII**:
   - Semester 1: Bab 1 s.d. 10.
   - Semester 2: Bab 11 s.d. 20.
3. **Desain Antarmuka Modern & Responsif**:
   - Menggunakan Bootstrap 5.3.3 dan Bootstrap Icons.
   - Arsitektur **Fixed Sidebar** yang terkunci kokoh di sisi kiri saat konten digulir (*zero overlap* dan tanpa scrollbar ganda).
4. **Keamanan & Integritas Data**:
   - Prepared Statements PDO (aman dari ancaman *SQL Injection*).
   - Enkripsi kata sandi menggunakan hashing Bcrypt (`password_hash`).
   - Pembersihan otomatis file fisik media server saat data dihapus.

---

## 🛠️ 5. Solusi Kendala Teknis (Troubleshooting)

| Kendala | Penyebab Umum | Solusi Cepat |
| :--- | :--- | :--- |
| **"Koneksi database gagal"** | Modul MySQL di XAMPP belum aktif atau nama database salah. | Pastikan modul MySQL di XAMPP Control Panel sudah berstatus **Start** (hijau), dan pastikan database di phpMyAdmin dinamai `db_smp_nomensen_english`. |
| **Halaman "Not Found 404"** | Nama folder di `htdocs` tidak cocok dengan URL. | Pastikan nama folder proyek adalah `web-edu-smpnomensen-main` dan diakses melalui `http://localhost/web-edu-smpnomensen-main/`. |
| **Tampilan CSS tampak berantakan / cache lama** | Browser menyimpan file cache CSS lama. | Tekan tombol **Ctrl + F5** (Hard Refresh) pada keyboard untuk memuat ulang CSS versi terbaru. |
| **Video atau Audio tidak terdengar / tidak berputar** | Berkas fisik media belum berada di foldernya. | Pastikan file video berada di `assets/video/` dan file audio berada di `assets/audio/`. |

---
*Dokumentasi ini disusun untuk mempermudah serah terima proyek dan operasional aplikasi.*
