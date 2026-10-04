# 🚀 Panduan Deploy Aplikasi ke Shared Hosting cPanel
## Aplikasi Pembelajaran Bahasa Inggris — SMP Swasta Nommensen

**Panduan ini dibuat untuk**: Klien atau siapa pun yang ingin mendeploy aplikasi ini ke shared hosting dengan cPanel.  
**Estimasi waktu**: 30–60 menit  
**Tingkat kesulitan**: ⭐⭐☆☆☆ (Mudah, cukup ikuti langkah demi langkah)

---

## ✅ Checklist Persiapan Sebelum Mulai

Sebelum memulai, pastikan kamu sudah punya:
- [ ] **Akses cPanel** dari provider hosting (email berisi link cPanel + username + password)
- [ ] **File proyek** lengkap: folder `web-edu-smpnomensen-main` (sudah di-download dari GitHub)
- [ ] **File database**: `db_smp_nomensen_english.sql` (ada di dalam folder proyek)
- [ ] **Software FTP** (opsional): [FileZilla](https://filezilla-project.org/) — GRATIS
- [ ] **Koneksi internet** yang stabil (untuk upload ~383 MB file)

---

## 📋 LANGKAH 1: Masuk ke cPanel

1. Buka browser, ketik link cPanel dari provider kamu.  
   Biasanya formatnya: `http://namadomainmu.com:2083` atau `https://namadomain.com/cpanel`
2. Login dengan **username** dan **password** yang diberikan provider hosting.
3. Kamu akan masuk ke dashboard cPanel.

---

## 📋 LANGKAH 2: Buat Database MySQL

> ⚠️ Di hosting, username MySQL **bukan** `root` dan password **bukan** kosong. Kamu harus membuat database baru.

### 2.1 Buat Database Baru
1. Di cPanel, cari menu **"MySQL Databases"** (biasanya ada di bagian **Database**).
2. Pada bagian **"Create New Database"**, ketik nama database:
   ```
   db_smp_nomensen_english
   ```
   *(Nama database di hosting biasanya jadi: `namauser_db_smp_nomensen_english`)*
3. Klik tombol **"Create Database"**.

### 2.2 Buat User MySQL
1. Scroll ke bawah, cari bagian **"MySQL Users"** → **"Add New User"**.
2. Isi:
   - **Username**: `smpnomensen` *(bebas, catat dengan baik!)*
   - **Password**: buat password yang kuat, contoh: `SmpNom3ns3n@2026!`
3. Klik **"Create User"**.

### 2.3 Hubungkan User ke Database
1. Cari bagian **"Add User to Database"**.
2. Pilih user yang baru dibuat → pilih database yang baru dibuat.
3. Klik **"Add"**.
4. Pada halaman berikutnya, centang **"ALL PRIVILEGES"** → klik **"Make Changes"**.

> 📝 **Catat informasi ini, akan digunakan di Langkah 4:**
> ```
> DB Host     : localhost
> DB Name     : namauser_db_smp_nomensen_english
> DB User     : namauser_smpnomensen
> DB Password : [password yang kamu buat]
> ```

---

## 📋 LANGKAH 3: Import Database

1. Di cPanel, cari menu **"phpMyAdmin"** (bagian Database).
2. Di panel kiri, klik nama database yang baru dibuat.
3. Klik tab **"Import"** (di bagian atas).
4. Klik **"Choose File"** → pilih file `db_smp_nomensen_english.sql` dari folder proyek.
5. Pastikan encoding: **UTF-8**.
6. Klik tombol **"Go"** / **"Import"**.
7. Tunggu hingga muncul pesan **"Import has been successfully finished"** ✅

> ⚠️ Jika muncul error "database already exists" pada baris `CREATE DATABASE`, itu normal — klik Skip atau abaikan, data tetap terimport dengan benar.

---

## 📋 LANGKAH 4: Edit File config.php

Sebelum upload file ke hosting, **wajib** mengubah konfigurasi database di file `config.php`.

### Cara Edit:
1. Buka folder proyek di komputer kamu.
2. Cari dan buka file `config.php` dengan **Notepad** atau **VS Code**.
3. Cari baris ini (sekitar baris 13–15):

```php
$host = 'localhost';
$username = 'root'; // Username bawaan XAMPP
$password = '';     // Password bawaan XAMPP (kosong)
```

4. **Ubah** menjadi (sesuai data dari Langkah 2):

```php
$host = 'localhost';
$username = 'namauser_smpnomensen';   // ← username MySQL hosting kamu
$password = 'SmpNom3ns3n@2026!';     // ← password MySQL hosting kamu
```

5. Lalu cari baris ini (sekitar baris 19–20):
```php
$candidate_dbs = [
    'db_smp_nomensen_english',
    'db_smp_nommensen_english'
];
```

6. **Ubah** menjadi (nama database lengkap dengan prefix namauser):
```php
$candidate_dbs = [
    'namauser_db_smp_nomensen_english',  // ← nama database lengkap hosting kamu
    'namauser_db_smp_nommensen_english'  // ← versi typo (cadangan)
];
```

7. **Simpan** file `config.php`.

> 💡 **Tips**: Nama database di hosting selalu ada prefix namauser cPanel + underscore.  
> Contoh: jika username cPanel kamu adalah `sekolah99`, maka database kamu adalah `sekolah99_db_smp_nomensen_english`.

---

## 📋 LANGKAH 5: Upload File ke Hosting

Ada 2 cara upload: **Via File Manager cPanel** (lebih mudah) atau **Via FTP** (lebih cepat untuk file besar).

### Cara A: Via File Manager cPanel (Direkomendasikan untuk pemula)

1. Di cPanel, klik **"File Manager"**.
2. Navigasi ke folder **`public_html`** (ini adalah root website kamu).
3. Jika website ini akan jadi website utama domain, upload langsung ke `public_html`.  
   Jika ingin akses via subfolder (misal: `domain.com/sekolah`), buat folder baru dulu.
4. Klik tombol **"Upload"** di bagian atas.
5. Upload **semua file dan folder** dari folder `web-edu-smpnomensen-main`:
   - Semua file `.php` (index.php, config.php, dll.)
   - Folder `assets/` (audio, video, img, css, js)
   - Folder `admin/`
   - Folder `siswa/`
   - Folder `includes/`
6. Tunggu proses upload selesai (file video ~362 MB butuh waktu cukup lama).

### Cara B: Via FTP dengan FileZilla (Lebih cepat untuk file besar)

1. Download dan install [FileZilla](https://filezilla-project.org/) di komputer.
2. Buka FileZilla → klik **"File"** → **"Site Manager"** → **"New Site"**.
3. Isi:
   - **Host**: `ftp.namadomainmu.com` atau IP hosting
   - **Port**: 21
   - **Protocol**: FTP atau SFTP
   - **Logon Type**: Normal
   - **User**: username cPanel kamu
   - **Password**: password cPanel kamu
4. Klik **"Connect"**.
5. Di panel kanan (server), navigasi ke `public_html`.
6. Di panel kiri (komputer kamu), navigasi ke folder `web-edu-smpnomensen-main`.
7. **Drag & Drop** semua isi folder ke `public_html`.
8. Tunggu upload selesai.

---

## 📋 LANGKAH 6: Verifikasi & Test

Setelah upload selesai, buka browser dan akses domain kamu:

```
http://namadomainmu.com/
```

### Checklist Pengujian:
- [ ] Halaman utama (`index.php`) terbuka dan tampil dengan benar
- [ ] Leaderboard muncul (artinya koneksi database berhasil)
- [ ] Login guru berhasil dengan NUPTK: `8546774675230253` dan password: `guru123`
- [ ] Login siswa berhasil dengan NIS salah satu siswa dan password: `siswa123`
- [ ] Halaman materi terbuka dan bisa diakses
- [ ] Video bisa diputar
- [ ] Audio bisa diputar
- [ ] Kuis bisa dikerjakan dan skor tersimpan

---

## 📋 LANGKAH 7: Aktifkan SSL (HTTPS)

Hosting ini menyediakan **Free SSL**. Aktifkan agar website lebih aman:

1. Di cPanel, cari menu **"SSL/TLS"** atau **"Let's Encrypt SSL"**.
2. Klik **"Install"** atau **"Issue"** untuk domain kamu.
3. Tunggu beberapa menit hingga SSL aktif.
4. Akses website via `https://namadomainmu.com/` untuk memastikan SSL berfungsi.

---

## ⚠️ Troubleshooting Masalah Umum

| Masalah | Kemungkinan Penyebab | Solusi |
|---|---|---|
| Halaman putih kosong | Error PHP | Aktifkan error display via cPanel → PHP Settings → `display_errors = On` (sementara) |
| "Database connection failed" | Kredensial DB salah | Cek ulang `config.php` — pastikan username, password, dan nama database sesuai |
| File gambar/audio tidak muncul | Path file salah atau permission | Cek permission folder `assets/` → ubah ke `755` via File Manager |
| Upload file audio/video gagal | Batas upload PHP terlalu kecil | Cek via cPanel → PHP Settings → `upload_max_filesize` dan `post_max_size` (minta provider ubah ke 50M) |
| "500 Internal Server Error" | Error pada `.htaccess` atau PHP | Cek error log via cPanel → Errors |

---

## 🔒 Keamanan Penting Setelah Deploy

1. **Hapus atau rename file `db_smp_nomensen_english.sql`** dari hosting — file ini tidak perlu ada di server dan bisa diunduh orang lain.
2. **Ubah password** guru dan admin default segera setelah deploy.
3. **Pastikan folder `config.php`** tidak bisa diakses langsung via browser.

---

## 📞 Informasi Kontak & Kredensial Default Aplikasi

| Akun | Username / NIP | Password |
|---|---|---|
| **Guru / Admin** | `8546774675230253` | `guru123` |
| **Semua Siswa** | NIS (26001–26071) atau NISN | `siswa123` |

> ⚠️ **Wajib ubah password default** setelah aplikasi berjalan di hosting!

---

*Panduan ini dibuat secara otomatis. Jika ada kendala teknis, hubungi pengembang aplikasi.*
