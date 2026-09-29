# LAPORAN MASTER & PANDUAN KOMPREHENSIF SISTEM
## Aplikasi Pembelajaran Bahasa Inggris Berbasis Multimedia (SMP Swasta Nommensen)
### *Panduan Teknis, Arsitektur Sistem, Basis Data, dan Manual Operasional Proyek*

---

> **Pesan Pengantar dari Instruktur/Pembimbing:**
> *"Selamat datang di dokumentasi resmi sistem pembelajaran digital Bahasa Inggris SMP Swasta Nommensen. Laporan ini dirancang khusus sebagai panduan induk satu pintu (one-stop master report) agar siapapun yang membaca—baik Anda, rekan pengembang, guru pengajar, maupun tim penguji—dapat memahami seluruh rancang bangun aplikasi ini dengan mudah, mendalam, dan terstruktur. Pelajari setiap bab secara bertahap layaknya kita sedang berdiskusi di ruang kelas komputasi."*

---

## DAFTAR ISI LAPORAN

1. [BAGIAN 1: Landasan Konseptual, Metodologi MDLC & Arsitektur Kurikulum](#bagian-1-landasan-konseptual-metodologi-mdlc--arsitektur-kurikulum)
2. [BAGIAN 2: Panduan Instalasi & Deployment Lokal (XAMPP & 1-Klik Setup)](#bagian-2-panduan-instalasi--deployment-lokal-xampp--1-klik-setup)
3. [BAGIAN 3: Direktori Kredensial Akun Pengguna (Guru & Siswa Rombel VII-A, VII-B, VII-C)](#bagian-3-direktori-kredensial-akun-pengguna-guru--siswa-rombel-vii-a-vii-b-vii-c)
4. [BAGIAN 4: Arsitektur Rekayasa Perangkat Lunak, Keamanan & Mekanisme Internal](#bagian-4-arsitektur-rekayasa-perangkat-lunak-keamanan--mekanisme-internal)
5. [BAGIAN 5: Dokumentasi Lengkap Skema Basis Data (8 Tabel Aktual, Relasi 3NF & 482 Data Riwayat)](#bagian-5-dokumentasi-lengkap-skema-basis-data-8-tabel-aktual-relasi-3nf--482-data-riwayat)
6. [BAGIAN 6: Eksplorasi Komprehensif Seluruh Fitur-Fitur Sistem](#bagian-6-eksplorasi-komprehensif-seluruh-fitur-fitur-sistem)
   - [6.1 Fitur-Fitur Utama Portal Siswa (Peserta Didik)](#61-fitur-fitur-utama-portal-siswa-peserta-didik)
   - [6.2 Fitur-Fitur Utama Portal Guru / Administrator](#62-fitur-fitur-utama-portal-guru--administrator)
   - [6.3 Fitur-Fitur Publik & Landing Page Terpadu](#63-fitur-fitur-publik--landing-page-terpadu)
7. [BAGIAN 7: Struktur Direktori Proyek Bersih (Clean Repository & Peta Berkas Lengkap)](#bagian-7-struktur-direktori-proyek-bersih-clean-repository--peta-berkas-lengkap)
8. [BAGIAN 8: Panduan Presentasi, Skenario Simulasi Pengujian, & Solusi Kendala (Troubleshooting)](#bagian-8-panduan-presentasi-skenario-simulasi-pengujian--solusi-kendala-troubleshooting)

---

## BAGIAN 1: LANDASAN KONSEPTUAL, METODOLOGI MDLC & ARSITEKTUR KURIKULUM

Sebagai seorang pendidik dan pengembang perangkat lunak, sistem ini tidak dirancang hanya sekadar aplikasi web biasa, melainkan media instruksional interaktif yang dibangun menggunakan metodologi ilmiah **Multimedia Development Life Cycle (MDLC)** menurut Luther-Sutopo, yang meliputi 6 tahapan terstruktur:
1. **Concept**: Menetapkan kebutuhan pembelajaran Bahasa Inggris jenjang Kelas VII SMP Swasta Nommensen yang terbagi ke dalam 3 rombongan belajar (VII-A, VII-B, VII-C) dengan target penguasaan kosa kata, tata bahasa, dan percakapan kontekstual.
2. **Design**: Merancang *storyboard* antarmuka, diagram relasi database (ERD 3NF), sistem navigasi *fixed sidebar*, alur ujian berwaktu anti-contek, papan peringkat (*leaderboard*), dan format dokumen cetak rapor resmi A4/PDF.
3. **Material Collecting**: Menghimpun 20 unit silabus materi kurikulum kelas VII, berkas audio pelafalan format `.mp3` dari penutur asli (*native speaker*), video animasi situasi format `.mp4`, serta 400 butir soal pilihan ganda berstandar KKM 70.
4. **Assembly**: Mengintegrasikan seluruh komponen frontend (Bootstrap 5.3.3, Bootstrap Icons, Visual Quill WYSIWYG) dengan backend PHP native berorientasi PDO MySQL.
5. **Testing**: Menguji fungsionalitas menggunakan metode *Black-Box Testing* untuk memverifikasi akurasi kalkulasi skor, ketahanan sesi ujian, konsistensi hak akses, dan kepatuhan format cetak dokumen resmi sekolah.
6. **Distribution**: Mengemas sistem ke dalam lingkungan server lokal (*localhost*) berbasis XAMPP dengan basis data terpadu yang memuat 482 log aktivitas riil sehingga sistem siap digunakan secara langsung.

### 1.1 Pendekatan 4 Pilar Media Terintegrasi
Setiap bab materi pembelajaran menyajikan 4 pilar multimedia yang saling menguatkan proses pemerolehan bahasa (*language acquisition*):
- **Pilar 1: Modul Teks Terstruktur** — Memuat rangkuman materi ringkas, kaidah tata bahasa (*grammar*), daftar kosakata tematik (*vocabulary*), dan contoh kalimat komunikatif.
- **Pilar 2: Video Animasi Kontekstual** — Menghadirkan visualisasi percakapan situasional dunia nyata (`.mp4`) agar siswa memahami gestur, intonasi, dan konteks sosial.
- **Pilar 3: Laboratorium Audio (Listening & Pronunciation)** — Menyajikan pelafalan kata per kata dan dialog oleh *native speaker* (`.mp3`) guna melatih kepekaan telinga (*listening*) dan akurasi pelafalan berbicara (*speaking*).
- **Pilar 4: Kuis Evaluasi Interaktif (KKM 70)** — Evaluasi pemahaman mandiri dengan sistem hitung mundur (*timer*), palet navigasi nomor soal, mode anti-contek, dan penentuan kelulusan standar KKM 70.

### 1.2 Cakupan Silabus 20 Bab Kurikulum Terstruktur (Fokus Kelas VII)
Sistem memuat 20 bab materi komprehensif yang dirancang khusus untuk jenjang SMP Kelas VII:
- **Semester 1 (Bab 1 s.d. 10)**:
  1. *Greetings & Partings*
  2. *Expression of Thanking & Apologizing*
  3. *Vocabularies: Family Tree*
  4. *Introduction to Verb (Action Verbs)*
  5. *Introduction to Pronoun (Subject & Object)*
  6. *Numbers (Cardinal & Ordinal)*
  7. *Introduction to Date (Days, Months, Years)*
  8. *Introduction to Time (Telling Time)*
  9. *Preposition of Time (In, On, At)*
  10. *Vocabularies: Tools, Creatures, and Places*
- **Semester 2 (Bab 11 s.d. 20)**:
  11. *Introduction to Noun (Countable & Uncountable)*
  12. *How Many vs How Much (Quantifiers)*
  13. *Articles (A, An, The)*
  14. *Preposition of Place (In, On, Under, Beside, dll.)*
  15. *Aspects of Descriptive Text (Identification & Description)*
  16. *Expression of Asking and Describing*
  17. *Introduction to Adjective (Describing Attributes)*
  18. *Introduction to Simple Present Tense (Daily Routines & Facts)*
  19. *Contextual Meaning of Songs & Moral Values*
  20. *Expression of Liking and Dislike (Preferences)*

---

## BAGIAN 2: PANDUAN INSTALASI & DEPLOYMENT LOKAL (XAMPP & 1-KLIK SETUP)

Untuk menjalankan proyek ini di komputer lokal laboratorium atau penguji, ikuti 5 langkah instalasi terstruktur berikut:

### Langkah 1: Penempatan Folder Proyek
Pastikan folder proyek diletakkan di dalam direktori root server web Apache Anda:
- **Lokasi standar Windows (XAMPP)**: `C:\xampp\htdocs\web-edu-smpnomensen-main`

### Langkah 2: Mengaktifkan Layanan Server Lokal
1. Buka aplikasi **XAMPP Control Panel**.
2. Klik tombol **Start** pada modul **Apache** dan **MySQL**.
3. Pastikan indikator keduanya berubah warna menjadi **hijau** (Port 80/443 untuk Apache dan Port 3306 untuk MySQL aktif).

### Langkah 3: Menyiapkan Basis Data Master (1-Klik Setup)
1. Buka peramban web (Chrome / Edge / Firefox) lalu akses: `http://localhost/phpmyadmin`.
2. Klik menu **Databases** (Basis Data Baru), masukkan nama database:
   ```text
   db_smp_nomensen_english
   ```
   *(Pilih collation `utf8mb4_general_ci`, lalu klik tombol **Create / Buat**)*.
3. Klik nama database `db_smp_nomensen_english` pada daftar di sebelah kiri.
4. Klik tab **Import** pada bar navigasi atas.
5. Klik tombol **Choose File / Telusuri**, pilih berkas **`db_smp_nomensen_english.sql`** dari folder proyek.
6. Gulir ke bagian paling bawah dan klik tombol **Import / Kirim**.
7. Tunggu beberapa detik hingga muncul notifikasi sukses berwarna hijau (*"Import has been successfully finished"*). Seluruh 8 tabel database, 20 materi bab kurikulum, 400 butir bank soal, 71 data akun siswa resmi (Rombel VII-A, VII-B, VII-C), dan **482 data riwayat pengerjaan kuis realistis** telah terpasang utuh.

### Langkah 4: Verifikasi Konfigurasi Koneksi (`config.php`)
Buka file `config.php`. Konfigurasi telah disesuaikan secara default dengan setelan standar XAMPP:
```php
<?php
$host = 'localhost';
$dbname = 'db_smp_nomensen_english';
$username = 'root'; // Username default XAMPP
$password = '';     // Password default XAMPP (kosong)
```

### Langkah 5: Menjalankan Aplikasi di Peramban Web
Buka tab baru browser dan ketik alamat:
```text
http://localhost/web-edu-smpnomensen-main/
```
Sistem akan menampilkan Landing Page berlogo resmi SMP Swasta Nommensen lengkap dengan papan peringkat kuis terkini dan tombol akses langsung kedua portal.

---

## BAGIAN 3: DIREKTORI KREDENSIAL AKUN PENGGUNA (GURU & SISWA ROMBEL VII-A, VII-B, VII-C)

Sistem telah dilengkapi dengan akun resmi terdaftar yang siap digunakan untuk presentasi, pengujian fungsionalitas, maupun operasional harian:

### 3.1 Akun Guru / Tenaga Pendidik Resmi
- **Nama Guru**: Hedi Diana, S.Pd., Gr
- **Identitas (NUPTK / NIP)**: `8546774675230253` *(Guru Bahasa Inggris bersertifikat pendidik)*
- **Kata Sandi (Password)**: `guru123`
- **Tautan Login Guru**: `http://localhost/web-edu-smpnomensen-main/admin/login.php`
- **Hak Akses & Wewenang**: Mengelola naskah 20 bab materi via visual WYSIWYG editor, mengunggah dan menautkan file media audio/video langsung, mengelola 400 butir bank soal, memantau rapor nilai kelas 7A/7B/7C, mengunduh/mencetak dokumen resmi raport siswa (A4/PDF), serta mengelola akun siswa (NIS & NISN).

### 3.2 Akun Siswa (Dukungan Multi-Auth: NIS & NISN)
- **Tautan Login Siswa**: `http://localhost/web-edu-smpnomensen-main/siswa/login.php`
- **Fleksibilitas Login (Multi-Auth)**: Formulir login siswa mengenali **NIS (5 digit)** maupun **NISN (10 digit)** secara otomatis tanpa konfigurasi manual.
- **Kata Sandi Bawaan (Default Password Seluruh Siswa)**: `siswa123`
- **Distribusi Rombongan Belajar (Rombel)**: Terdapat total **71 siswa resmi** yang terbagi ke dalam:
  - **Kelas VII-A**: 28 Siswa (NIS `26001` s.d. `26028`)
  - **Kelas VII-B**: 24 Siswa (NIS `26029` s.d. `26052`)
  - **Kelas VII-C**: 19 Siswa (NIS `26053` s.d. `26071`)

#### Tabel Contoh Akun Siswa Siap Uji (Per Rombel):

| Rombel | NIS | NISN | Nama Siswa | Kata Sandi | Status Riwayat & Rapor |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **VII-A** | `26001` | `0136442625` | ALENA FELICIA | `siswa123` | Aktif (Memiliki Riwayat & Rapor) |
| **VII-A** | `26002` | `0142940288` | ARMEN CANE PANGARIBUAN | `siswa123` | Aktif (Memiliki Riwayat & Rapor) |
| **VII-A** | `26005` | `0139232373` | Bintang Cecilia Panjaitan | `siswa123` | Aktif (Memiliki Riwayat & Rapor) |
| **VII-B** | `26029` | `3142757879` | Adelardo Giofrey Sigalingging | `siswa123` | Aktif (Memiliki Riwayat & Rapor) |
| **VII-B** | `26030` | `3148274646` | ALI ESER NATANIEL NAINGGOLAN | `siswa123` | Aktif (Memiliki Riwayat & Rapor) |
| **VII-C** | `26053` | `3122220006` | Amran Efraen S | `siswa123` | Aktif (Memiliki Riwayat & Rapor) |
| **VII-C** | `26054` | `3133766941` | Delvia Putri Lumbangaol | `siswa123` | Aktif (Memiliki Riwayat & Rapor) |

---

## BAGIAN 4: ARSITEKTUR REKAYASA PERANGKAT LUNAK & CARA KERJA DI BALIK LAYAR

Mari kita bedah anatomi teknis dari sistem ini. Sebagai seorang insinyur perangkat lunak, keindahan sebuah aplikasi terletak pada kerapian struktur internalnya.

```text
               +-------------------------------------------+
               |     KLIEN (Browser Siswa & Guru)          |
               +-------------------------------------------+
                                     |
                          [HTTP/HTTPS Request]
                                     |
                                     v
               +-------------------------------------------+
               |           APACHE WEB SERVER               |
               +-------------------------------------------+
                                     |
                         [Session & Auth Gatekeeper]
                         - auth_siswa.php / auth_admin.php
                                     |
                                     v
               +-------------------------------------------+
               |           KONTROLER LOGIKA (PHP)          |
               | - Siswa Engine: menu, kuis, riwayat       |
               | - Guru Engine: materi, media, bank soal   |
               | - UI Presentasi: Bootstrap 5.3.3 + Icons  |
               +-------------------------------------------+
                                     |
                         [PDO Prepared Statements]
                                     |
                                     v
               +-------------------------------------------+
               |          DATABASE SERVER (MySQL)          |
               |          db_smp_nomensen_english          |
               +-------------------------------------------+
```

### 4.1 Pertahanan Keamanan Berlapis (Security Layer)
1. **Pencegahan Serangan SQL Injection**:
   Seluruh interaksi data ke basis data wajib menggunakan teknologi **PDO Prepared Statements** dengan *named parameter binding*. Tidak ada input pengguna mentah yang digabungkan secara langsung ke string SQL.
2. **Pencegahan Serangan XSS (Cross-Site Scripting)**:
   Seluruh keluaran dinamis ke layar browser disaring menggunakan fungsi proteksi `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`.
3. **Pengamanan Kredensial Kata Sandi (Bcrypt Hashing)**:
   Kata sandi tidak pernah disimpan dalam format teks polos (*plaintext*). Sistem menggunakan fungsi mutakhir `password_hash($pass, PASSWORD_DEFAULT)` dengan algoritma Bcrypt yang aman dari serangan *rainbow table*.
4. **Session Hijacking Guard & Role-Based Access Control**:
   Saat pengguna berhasil login, sistem segera mengeksekusi `session_regenerate_id(true)` untuk membatalkan token sesi lama dan menerbitkan token baru. Setiap halaman dipagari oleh middleware autentikasi ketat (`auth_siswa.php` dan `auth_admin.php`) untuk mencegah eskalasi hak akses antar role.

### 4.2 Arsitektur Antarmuka: Layout Fixed Sidebar & Zero Overlap
Salah satu penyempurnaan utama pada sistem ini adalah tata letak navigasi samping (*sidebar*):
- **Sidebar Siswa (`.siswa-sidebar`)**: Berposisi tetap permanen (`position: fixed; top: 0; left: 0; width: 285px; height: 100vh; overflow: hidden; z-index: 1000;`).
- **Konten Utama Siswa (`.siswa-main`)**: Diberikan offset presisi (`margin-left: 285px;`) sehingga saat pengguna menggulir (*scroll*) konten pelajaran, sidebar tetap terkunci rapi di sebelah kiri tanpa ada penumpukan elemen (*zero overlap*) dan tanpa adanya bilah gulir ganda (*zero dual scrollbar*).
- **Sidebar Guru (`.sidebar`)**: Menerapkan arsitektur identik (`width: 250px; position: fixed;`) dengan offset konten admin (`margin-left: 250px;`).

### 4.3 Mesin Kuis Interaktif Berwaktu & Mode Anti-Contek (Anti-Cheating Engine)
- **Palet Navigasi Nomor Soal**: Menyajikan daftar tombol nomor soal 1-20 secara interaktif. Tombol berubah warna secara dinamis saat soal telah dijawab, memudahkan siswa memantau progres tanpa tersesat.
- **Timer Hitung Mundur Real-time**: Waktu pengerjaan kuis (20 menit) dihitung mundur via JavaScript. Apabila waktu habis, formulir kuis akan terkumpul secara otomatis (*auto-submit*).
- **Mode Anti-Contek**: Antarmuka pengerjaan sengaja meniadakan umpan balik instan (benar/salah) saat siswa mengklik opsi jawaban, mencegah siswa menghafal pola tebakan atau saling menyontek di laboratorium komputer.
- **Proteksi Ketahanan Sesi Kuis (`localStorage`)**: Data pilihan sementara dan sisa waktu pengerjaan dicadangkan secara lokal di browser. Apabila halaman tidak sengaja di-*refresh* atau komputer mengalami mati daya sejenak, status pengerjaan siswa segera dipulihkan utuh.
- **Kalkulasi Nilai Ketat di Sisi Server (`server-side validation`)**: Skor akhir tidak dihitung di sisi peramban klien guna menangkal manipulasi nilai via *Inspect Element / DevTools*. Skrip `kuis_proses.php` secara independen mengambil kunci jawaban resmi dari database `tb_soal`, mencocokkan input POST siswa, lalu menyimpan rekapitulasi ke `tb_hasil`.
- **Integritas Skor Ujian**: Tombol "Ulangi Kuis" ditiadakan dari layar hasil kuis (`hasil_kuis.php`) untuk menjaga kemurnian evaluasi belajar dan mencegah manipulasi nilai instan berulang-ulang.

### 4.4 Engine Pencetakan Dokumen Resmi Raport (CSS Print Media & Standar A4 Sekolah)
Sistem dilengkapi modul cetak dokumen raport resmi (`admin/cetak_raport.php` dan `siswa/cetak_raport.php`) yang dirancang tanpa ketergantungan pustaka biner berat pihak ketiga:
- **Kop Surat Resmi Sekolah**: Dilengkapi logo resmi SMP Swasta Nommensen, alamat lengkap, dan garis ganda standar tata naskah dinas.
- **Aturan Cetak Presisi (`@media print`)**: Mengatur ukuran kertas standar **A4**, menyembunyikan elemen navigasi tombol cetak pada lembar fisik, serta menggunakan aturan `page-break-inside: avoid` pada tabel dan kolom tanda tangan agar dokumen tidak terpotong canggung antar halaman.
- **Legalitas Dokumen**: Memuat 3 kolom tanda tangan resmi (Kepala Sekolah, Guru Pengampu Bahasa Inggris, dan Orang Tua/Wali Siswa) beserta tanggal titimangsa dinas.

### 4.5 Dual-Identity Authentication Engine
Sistem login peserta didik (`siswa/login.php`) dirancang dengan mekanisme pencarian ganda (*dual-identifier lookup*):
```php
$stmt = $pdo->prepare("SELECT * FROM tb_siswa WHERE nis = :nis OR nisn = :nis LIMIT 1");
$stmt->execute(['nis' => $nis]);
```
Mekanisme ini memungkinkan siswa memasukkan **NIS (Nomor Induk Siswa lokal, 5 digit)** ataupun **NISN (Nomor Induk Siswa Nasional, 10 digit)** tanpa memerlukan pemilihan menu terpisah.

---

## BAGIAN 5: DOKUMENTASI LENGKAP SKEMA BASIS DATA (8 TABEL AKTUAL, RELASI 3NF & 482 DATA RIWAYAT)

Database `db_smp_nomensen_english` dirancang menggunakan bentuk normal ketiga (3NF) guna menjamin konsistensi data, integritas referensial, dan mencegah anomali data.

```text
+----------------+          +-----------------+          +------------------+
|    tb_guru     | 1      * |    tb_materi    | 1      * |     tb_audio     |
+----------------+<--------^+----------------+<--------^+------------------+
| id_guru (PK)   |          | id_materi (PK)  |          | id_audio (PK)    |
| nip            |          | kategori        |          | id_materi (FK)   |
| nama_guru      |          | semester        |          | file_audio       |
| password       |          | urutan          |          | keterangan       |
| last_login     |          | judul_materi    |          +------------------+
| created_at     |          | ringkasan       |
+-------+--------+          | konten_teks     |          +------------------+
        |                   | created_at      | 1      * |     tb_video     |
        |                   | id_guru (FK)    +---------^+------------------+
        |                   +--------+--------+          | id_video (PK)    |
        |                            |                   | id_materi (FK)   |
        |                            | 1                 | file_video       |
        | 1                          |                   | keterangan       |
        |                            v *                 +------------------+
        |                   +-----------------+
        +------------------^+    tb_kuis      | 1      * +------------------+
        | *                 +-----------------+<--------^+     tb_soal      |
        |                   | id_kuis (PK)    |          +------------------+
        |                   | judul_kuis      |          | id_soal (PK)     |
        |                   | kategori_materi |          | id_kuis (FK)     |
        |                   | waktu_pengerjaan|          | pertanyaan       |
        |                   | id_materi (FK)  |          | opsi_a, b, c, d  |
        |                   | nilai_lulus(70) |          | jawaban_benar    |
        |                   | id_guru (FK)    |          +------------------+
        |                   +--------+--------+
        |                            ^ 1
+-------+--------+                   |
|    tb_siswa    | 1                 | *
+----------------+<------------------+
| id_siswa (PK)  |          +-----------------+
| nis            | 1      * |    tb_hasil     |
| nisn           +---------^+-----------------+
| nama_siswa     |          | id_hasil (PK)   |
| password       |          | id_siswa (FK)   |
| kelas          |          | id_kuis (FK)    |
| last_login     |          | skor            |
| created_at     |          | jumlah_benar    |
+----------------+          | jumlah_salah    |
                            | waktu_selesai   |
                            +-----------------+
```

### 5.1 Tabel `tb_siswa` (Pengguna Peserta Didik)
Menyimpan kredensial dan identitas akademik 71 siswa resmi kelas VII (VII-A, VII-B, VII-C):
- `id_siswa` (INT, Primary Key, Auto Increment)
- `nis` (VARCHAR(20), Unique, Not Null) — Nomor Induk Siswa lokal (contoh: `26001` s.d. `26071`)
- `nisn` (VARCHAR(20), Unique, Nullable) — Nomor Induk Siswa Nasional (contoh: `0136442625`)
- `nama_siswa` (VARCHAR(100), Not Null) — Nama lengkap peserta didik resmi
- `password` (VARCHAR(255), Not Null) — Hash Bcrypt kata sandi (`siswa123`)
- `kelas` (VARCHAR(50), Not Null) — Rombongan belajar (`VII-A`, `VII-B`, atau `VII-C`)
- `last_login` (DATETIME, Nullable) — Jejak waktu login terakhir siswa
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 5.2 Tabel `tb_guru` (Pengguna Pendidik / Administrator)
Menyimpan identitas guru pengampu mata pelajaran Bahasa Inggris:
- `id_guru` (INT, Primary Key, Auto Increment)
- `nip` (VARCHAR(20), Unique, Not Null) — NUPTK/NIP resmi guru (`8546774675230253`)
- `nama_guru` (VARCHAR(100), Not Null) — Hedi Diana, S.Pd., Gr
- `password` (VARCHAR(255), Not Null) — Hash Bcrypt kata sandi (`guru123`)
- `last_login` (DATETIME, Nullable) — Jejak waktu login terakhir guru
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 5.3 Tabel `tb_materi` (Silabus 20 Bab Kurikulum)
Menyimpan 20 unit modul pembelajaran bahasa Inggris kelas VII:
- `id_materi` (INT, Primary Key, Auto Increment)
- `kategori` (VARCHAR(50), Not Null) — Topik kurikulum (contoh: *Conversation, Grammar, Vocabulary*)
- `semester` (TINYINT(1), Not Null, Default 1) — Nilai 1 (Semester 1: Bab 1-10) atau 2 (Semester 2: Bab 11-20)
- `urutan` (INT, Not Null, Default 1) — Urutan bab pembelajaran (1 s.d. 20)
- `judul_materi` (VARCHAR(150), Not Null) — Judul bab resmi materi
- `ringkasan` (TEXT, Nullable) — Intisari kompetensi materi
- `konten_teks` (TEXT, Nullable) — Naskah penjelasan lengkap (format HTML dari editor Quill)
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)
- `id_guru` (INT, Foreign Key ke `tb_guru.id_guru` ON DELETE SET NULL)

### 5.4 Tabel `tb_audio` (Laboratorium Suara / Listening & Pronunciation)
Menyimpan rekaman suara penutur asli untuk melatih listening:
- `id_audio` (INT, Primary Key, Auto Increment)
- `id_materi` (INT, Foreign Key ke `tb_materi.id_materi` ON DELETE CASCADE)
- `file_audio` (VARCHAR(255), Not Null) — Berkas audio `.mp3` di direktori `assets/audio/`
- `keterangan` (VARCHAR(255), Nullable) — Deskripsi topik latihan audio

### 5.5 Tabel `tb_video` (Media Visual Gerak / Situational Conversation)
Menyimpan berkas video animasi interaktif per bab:
- `id_video` (INT, Primary Key, Auto Increment)
- `id_materi` (INT, Foreign Key ke `tb_materi.id_materi` ON DELETE CASCADE)
- `file_video` (VARCHAR(255), Not Null) — Berkas video `.mp4` di direktori `assets/video/`
- `keterangan` (VARCHAR(255), Nullable) — Konteks percakapan situasional video

### 5.6 Tabel `tb_kuis` (Sesi Evaluasi Pembelajaran)
Menyimpan 20 paket evaluasi belajar berbasis batas waktu dan KKM:
- `id_kuis` (INT, Primary Key, Auto Increment) — Memuat 20 paket tes untuk Bab 1 s.d. Bab 20
- `judul_kuis` (VARCHAR(150), Not Null) — Judul paket kuis
- `kategori_materi` (VARCHAR(50), Not Null) — Klasifikasi topik kuis
- `waktu_pengerjaan` (INT, Not Null) — Batas durasi pengerjaan (standar 20 menit)
- `id_materi` (INT, Foreign Key ke `tb_materi.id_materi` ON DELETE CASCADE)
- `nilai_lulus` (INT, Default 70) — Standar Kriteria Ketuntasan Minimal (KKM)
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)
- `id_guru` (INT, Foreign Key ke `tb_guru.id_guru` ON DELETE SET NULL)

### 5.7 Tabel `tb_soal` (Bank Butir Pertanyaan Pilihan Ganda)
Menyimpan **400 butir soal pilihan ganda** (tepat 20 soal per kuis evaluasi Bab 1 s.d. 20):
- `id_soal` (INT, Primary Key, Auto Increment)
- `id_kuis` (INT, Foreign Key ke `tb_kuis.id_kuis` ON DELETE CASCADE)
- `pertanyaan` (TEXT, Not Null) — Naskah pertanyaan soal
- `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d` (VARCHAR(255), Not Null) — 4 opsi alternatif
- `jawaban_benar` (ENUM('A','B','C','D'), Not Null) — Kunci jawaban resmi

### 5.8 Tabel `tb_hasil` (Rekapitulasi Nilai & 482 Data Riwayat Realistis)
Merekam jejak performa belajar dan hasil evaluasi peserta didik. Tabel ini telah dimuat dengan **482 baris data riwayat pengerjaan kuis realistis** dari seluruh rombel VII-A, VII-B, dan VII-C:
- `id_hasil` (INT, Primary Key, Auto Increment)
- `id_siswa` (INT, Foreign Key ke `tb_siswa.id_siswa` ON DELETE CASCADE)
- `id_kuis` (INT, Foreign Key ke `tb_kuis.id_kuis` ON DELETE CASCADE)
- `skor` (INT, Not Null) — Skor akhir evaluasi skala 0-100
- `jumlah_benar` (INT, Not Null) — Jumlah jawaban benar
- `jumlah_salah` (INT, Not Null) — Jumlah jawaban salah
- `waktu_selesai` (TIMESTAMP, Default CURRENT_TIMESTAMP) — Waktu pengerjaan lengkap dengan tanggal dan jam presisi (`Y-m-d H:i:s`) yang menggerakkan analitik laporan, papan peringkat (*leaderboard*), dan raport evaluasi.

---

## BAGIAN 6: EKSPLORASI KOMPREHENSIF SELURUH FITUR-FITUR SISTEM

Aplikasi pembelajaran ini mengintegrasikan seluruh instrumen pedagogis kurikulum bahasa Inggris dengan kenyamanan rekayasa web modern. Berikut adalah inventarisasi dan penjelasan mendalam mengenai seluruh fitur yang tersedia dalam sistem:

### 6.1 Fitur-Fitur Utama Portal Siswa (Peserta Didik)

#### 1. Autentikasi Fleksibel Multi-Identifier (NIS & NISN)
- **Lokasi Berkas**: `siswa/login.php`
- **Mekanisme**: Formulir login dilengkapi kecerdasan backend yang secara otomatis memeriksa masukan pengguna terhadap kolom `nis` (5 digit) maupun `nisn` (10 digit) melalui klausa SQL `WHERE nis = :nis OR nisn = :nis`. Hal ini mencegah kegagalan login akibat kebingungan siswa antara nomor induk lokal sekolah dan nomor induk nasional.
- **Keamanan**: Menggunakan `password_verify()` terhadap enkripsi Bcrypt, dipadukan dengan peremajaan sesi `session_regenerate_id(true)` serta pembatasan akses berbasis peran (*role guard*).

#### 2. Beranda Belajar Interaktif & 4 Bento Metrik Kemajuan Siswa
- **Lokasi Berkas**: `siswa/menu.php`
- **Mekanisme**: Menyajikan *Hero Greeting Banner* personal yang menampilkan nama, kelas rombel, dan NIS siswa. Dilengkapi 4 kartu ringkasan visual (*Bento Grid*):
  1. *Total Materi Aktif*: Menampilkan ketersediaan 20 bab materi kurikulum.
  2. *4 Pilar Multimedia*: Indikator kesiapan modul multimedia teks, video animasi, audio, dan kuis.
  3. *Total Kuis Selesai*: Rekapitulasi jumlah sesi evaluasi yang telah diselesaikan siswa.
  4. *Akumulasi Rata-Rata Nilai*: Nilai rata-rata seluruh kuis yang telah dikerjakan secara real-time.
- **Navigasi Cepat**: Tombol jalan pintas ke katalog materi, kuis, riwayat nilai, serta filter semester (Semester 1 & Semester 2).

#### 3. Papan Peringkat (Leaderboard) Top 5 Nilai Kuis & Lencana Prestasi
- **Lokasi Berkas**: `siswa/menu.php` & `index.php`
- **Mekanisme**: Menampilkan 5 peserta didik dengan skor kuis tertinggi di tingkat sekolah. Menggunakan penyortiran gabungan: skor tertinggi, jumlah jawaban benar terbanyak, dan kecepatan waktu penyelesaian.
- **Dukungan Visual**: Dilengkapi lencana medali juara (🥇 Juara 1, 🥈 Juara 2, 🥉 Juara 3) serta penanda khusus *badge* **"Anda"** dengan latar biru cerah apabila akun siswa yang sedang login berhasil menembus jajaran Top 5.

#### 4. Modul Pembelajaran 4 Pilar Multimedia Terintegrasi
- **Lokasi Berkas**: `siswa/materi_detail.php`
- **Mekanisme**: Menggabungkan 4 moda belajar dalam 1 antarmuka tanpa perlu berpindah jendela:
  1. *Modul Teks Bacaan*: Naskah materi komprehensif berformat tipografi bersih dengan tabel kosakata dan dialog.
  2. *Video Animasi Pembelajaran*: Pemutar video HTML5 responsif yang memutar visualisasi percakapan situasional dunia nyata format `.mp4`.
  3. *Laboratorium Audio Listening & Pronunciation*: Pemutar audio interaktif berlatar rekaman penutur asli (*native speaker*) `.mp3` dengan slider durasi dan pengatur volume.
  4. *Kuis Evaluasi Interaktif*: Tombol langsung menuju ujian bab terkait dengan standar KKM 70.

#### 5. Akses Cepat Modul Tematik (Vocabulary, Grammar, Conversation)
- **Lokasi Berkas**: `siswa/vocabulary.php`, `siswa/grammar.php`, `siswa/conversation.php`
- **Mekanisme**: Filter navigasi tematik yang mengelompokkan 20 bab kurikulum ke dalam 3 rumpun kompetensi bahasa:
  - *Vocabulary*: Fokus penguasaan perbendaharaan kata (Family, Numbers, Animals, School Tools, Nouns).
  - *Grammar*: Fokus kaidah tata bahasa (Action Verbs, Pronouns, Prepositions, Quantifiers, Simple Present Tense).
  - *Conversation*: Fokus dialog praktis sehari-hari (Greetings, Partings, Thanking, Apologizing, Asking & Describing).

#### 6. Mesin Ujian Berwaktu & Mode Anti-Contek (Anti-Cheating Quiz Engine)
- **Lokasi Berkas**: `siswa/kuis.php`, `siswa/kuis_kerjakan.php`, `siswa/kuis_proses.php`, `siswa/hasil_kuis.php`
- **Fitur Sub-Sistem Ujian**:
  - *Palet Nomor Soal Interaktif*: Kotak kisi nomor soal 1-20 di samping lembar ujian. Tombol berubah status warna secara otomatis saat soal telah dijawab, memudahkan navigasi lompat soal.
  - *Timer Hitung Mundur Real-Time*: Batas waktu pengerjaan 20 menit dihitung mundur presisi. Jika waktu habis, sistem otomatis melakukan *auto-submit* ke server.
  - *Mode Anti-Contek (No Instant Feedback)*: Siswa tidak diberikan indikator benar/salah secara langsung saat mengklik opsi, mencegah tebakan berulang dan menjaga keheningan ujian laboratorium.
  - *Proteksi Sesi LocalStorage*: Jawaban tersimpan di browser klien secara berkala; jika browser tertutup atau listrik padam, siswa dapat melanjutkan tanpa kehilangan progres.
  - *Evaluasi Skor Ketat Server-Side*: Koreksi jawaban dilakukan secara terisolasi pada backend `kuis_proses.php` dengan mencocokkan kunci database, mencegah manipulasi inspect element.
  - *Penghapusan Tombol Ulangi Kuis*: Menghilangkan tombol *retake* instan di halaman pengumuman nilai agar hasil evaluasi murni dan tercatat permanen ke database sekolah.

#### 7. Riwayat Belajar & Analisis Progres Nilai Multi-Periode
- **Lokasi Berkas**: `siswa/riwayat.php`
- **Mekanisme**: Menampilkan kartu pencapaian statistik: Total Kuis Selesai, Nilai Rata-Rata, Nilai Terbaik, dan Rasio Ketuntasan KKM.
- **Filter Fleksibel**: Siswa dapat menyaring riwayat berdasarkan:
  - *Pengerjaan Hari Ini* (melihat kuis yang baru saja dikerjakan).
  - *Laporan Mingguan* (rentang tanggal fleksibel).
  - *Laporan Bulanan* (pilihan bulan dan tahun).
  - *Seluruh Periode Pembelajaran*.

#### 8. Sistem Cetak Raport Belajar Siswa Mandiri (Dokumen Resmi PDF / A4)
- **Lokasi Berkas**: `siswa/cetak_raport.php`
- **Mekanisme**: Siswa dapat mengunduh atau mencetak secara mandiri lembar raport evaluasi belajar berstandar resmi:
  - Menggunakan format layout cetak presisi A4 berbasis stylesheet `@media print`.
  - Memuat Kop Surat Resmi SMP Swasta Nommensen lengkap dengan logo, alamat, dan nomor telepon sekolah.
  - Memuat identitas resmi peserta didik (Nama, NIS, NISN, Rombel Kelas, Tanggal Cetak).
  - Menyajikan tabel komprehensif capaian nilai bab materi, jumlah benar/salah, waktu pengerjaan, dan status kelulusan (KKM 70).
  - Menghitung nilai rata-rata kumulatif dan menetapkan predikat belajar (Sangat Baik / Baik / Cukup / Perlu Bimbingan).
  - Memuat 3 kolom tanda tangan resmi: Kepala Sekolah, Guru Pengampu, dan Orang Tua/Wali Murid.

#### 9. Pengaturan Akun & Pembaruan Kata Sandi Mandiri
- **Lokasi Berkas**: `siswa/ganti_password.php`
- **Mekanisme**: Memberikan hak mandiri kepada siswa untuk memperbarui kata sandi dengan validasi kata sandi lama, konfirmasi kata sandi baru minimal 6 karakter, dan enkripsi Bcrypt instan ke `tb_siswa`.

---

### 6.2 Fitur-Fitur Utama Portal Guru / Administrator

#### 1. Dashboard Monitoring Real-Time & Kartu Statistik Kelas 7
- **Lokasi Berkas**: `admin/dashboard.php`
- **Mekanisme**: Pusat kendali pengawasan proses belajar mengajar. Menampilkan 4 metrik utama: Total Siswa Terdaftar (71 siswa), Total Bab Kurikulum (20 Bab), Bank Soal Aktif (400 Butir), dan Rekap Sesi Kuis Selesai (482 Log).
- **Distribusi Rombel**: Memperlihatkan rincian sebaran siswa aktif per rombongan belajar (Kelas VII-A: 28 siswa, VII-B: 24 siswa, VII-C: 19 siswa).
- **Tabel Aktivitas Terkini**: Menampilkan log pengerjaan kuis siswa paling mutakhir secara *live*.

#### 2. Manajemen 20 Bab Kurikulum dengan Visual WYSIWYG Editor (Quill)
- **Lokasi Berkas**: `admin/kelola_materi.php`
- **Mekanisme**: Guru dapat menyusun dan memperbarui modul pembelajaran tanpa perlu memahami bahasa HTML:
  - Menggunakan pustaka teruji **Quill Visual Editor** (ala Microsoft Word) dengan bilah format teks lengkap (Bold, Italic, Underline, Bullet Lists, Header, Table).
  - Dilengkapi fitur **Template 1-Klik**: Template Modul Standar, Template Percakapan Interaktif, dan Template Tabel Kosakata Tematik.
  - Dilengkapi fitur **Live Preview Siswa**: Memungkinkan guru melihat pratinjau tampilan yang persis akan dilihat oleh siswa sebelum disimpan ke basis data.

#### 3. Pusat Unggah Media Langsung (Direct Media Upload)
- **Lokasi Berkas**: `admin/upload_media.php`
- **Mekanisme**: Modul mandiri yang memungkinkan guru mengunggah berkas rekaman suara (`.mp3`, `.wav`, `.ogg`, `.m4a`) dan video percakapan (`.mp4`, `.webm`) secara langsung ke sistem dan direktori penyimpanan server (`assets/audio/` dan `assets/video/`) tanpa harus membuka formulir materi terlebih dahulu.
- **Player Preview**: Dilengkapi pemutar instan untuk mendengarkan audio atau memutar video sebelum ditautkan ke bab kurikulum.

#### 4. Bank Soal Kuis Komprehensif & KKM 70
- **Lokasi Berkas**: `admin/kelola_soal.php`
- **Mekanisme**: Pengelolaan 400 butir soal pilihan ganda (20 soal per bab):
  - Mengatur batas waktu durasi pengerjaan (standar 20 menit).
  - Menetapkan ambang batas kelulusan Kriteria Ketuntasan Minimal (KKM 70).
  - CRUD butir soal: input teks pertanyaan, opsi jawaban A, B, C, D, dan penetapan kunci jawaban resmi.

#### 5. Rekapitulasi Laporan Nilai Multi-Dimensi & Tab Rombel
- **Lokasi Berkas**: `admin/laporan_nilai.php`
- **Mekanisme**: Sistem laporan nilai canggih yang dilengkapi pemisahan tabulasi rombongan belajar:
  - *Tab Semua Kelas (Kelas 7)*: Menampilkan rekapitulasi nilai 482 log kuis seluruh angkatan.
  - *Tab Rombel VII-A, VII-B, dan VII-C*: Memfilter data khusus per kelas lengkap dengan lencana jumlah data.
  - *Filter Lanjutan*: Penyaringan data berdasarkan nama siswa tertentu, bab kuis tertentu, dan tanggal pengerjaan spesifik.

#### 6. Kartu Bintang Prestasi (Highlight Siswa Nilai Tertinggi)
- **Lokasi Berkas**: `admin/laporan_nilai.php`
- **Mekanisme**: Algoritma cerdas yang mendeteksi dan menyorot profil siswa peraih nilai tertinggi (*Top Achievers*) secara visual dengan kartu emas bertanda bintang/trofi. Menampilkan nama siswa, NIS, judul kuis, skor sempurna (100), dan waktu pencapaian untuk memudahkan guru memberikan apresiasi akademik.

#### 7. Presisi Tanggal & Jam Pengerjaan Kuis Siswa
- **Lokasi Berkas**: `admin/laporan_nilai.php`
- **Mekanisme**: Setiap baris log nilai menampilkan tanggal pengerjaan dalam format bahasa Indonesia formal beserta jam, menit, dan detik yang presisi (contoh: `28 Sep 2026 - 14:16:11 WIB`). Memudahkan audit waktu pengerjaan dan verifikasi presensi saat ujian laboratorium berlangsung.

#### 8. Manajemen Raport Resmi Sekolah oleh Guru
- **Lokasi Berkas**: `admin/laporan_nilai.php` & `admin/cetak_raport.php`
- **Mekanisme**: Guru memiliki wewenang penuh untuk meninjau dan mencetak raport evaluasi belajar siswa manapun dari menu *Tab Raport Siswa*:
  - Memilih siswa dari kelas VII-A, VII-B, atau VII-C.
  - Menentukan periode laporan (Mingguan, Bulanan, atau Keseluruhan).
  - Menghasilkan dokumen cetak resmi PDF/A4 dengan Kop Surat SMP Swasta Nommensen, kalkulasi predikat KKM, catatan evaluasi guru, dan 3 kolom tanda tangan legal.

#### 9. Manajemen Data Siswa Terpadu (CRUD & Rombel VII)
- **Lokasi Berkas**: `admin/kelola_siswa.php`
- **Mekanisme**: Mengelola 71 data siswa resmi sekolah:
  - Pemisahan kolom NIS lokal (5 digit) dan NISN nasional (10 digit) dengan validasi keunikan ketat untuk mencegah duplikasi data.
  - Penempatan kelas siswa ke rombongan belajar resmi: VII-A, VII-B, atau VII-C.
  - Fitur Reset Kata Sandi instan: Mengembalikan kata sandi siswa ke default (`siswa123`) dalam satu klik apabila siswa lupa kata sandi.

#### 10. Pengaturan Keamanan Akun Pendidik
- **Lokasi Berkas**: `admin/pengaturan.php`
- **Mekanisme**: Pembaruan profil nama guru, identitas NUPTK, serta pembaruan kata sandi akun pendidik dengan validasi kata sandi lama dan hashing Bcrypt mutakhir.

---

### 6.3 Fitur-Fitur Publik & Landing Page Terpadu (`index.php`)

1. **Identitas Institusi Resmi**: Menampilkan logo resmi SMP Swasta Nommensen dalam resolusi tinggi, nama sekolah, dan deskripsi metode pembelajaran MDLC.
2. **Papan Peringkat (Leaderboard) Publik**: Memperlihatkan 5 besar siswa berprestasi di beranda awal sebagai bentuk motivasi belajar kompetitif sehat.
3. **Katalog Interaktif 20 Bab Kurikulum**: Calon siswa maupun guru dapat meninjau silabus materi Semester 1 dan 2 beserta rincian 4 pilar media yang disediakan pada masing-masing bab.
4. **Pintu Masuk Terpadu (Single Gateway)**: Dua tombol akses jelas dan elegan untuk mengarahkan pengguna ke portal masing-masing: tombol biru **"Mulai Belajar (Siswa)"** dan tombol outline **"Login Guru / Admin"**.

---

## BAGIAN 7: STRUKTUR DIREKTORI PROYEK BERSIH (CLEAN REPOSITORY & PETA BERKAS LENGKAP)

Proyek ini telah melalui tahap refaktorisasi dan pembersihan menyeluruh. Seluruh berkas pengujian dan berkas temporer yang tidak terpakai telah dibersihkan sehingga repositori berada dalam kondisi bersih (*clean repository*) dan siap produksi:

```text
web-edu-smpnomensen-main/
│
├── admin/                         # Portal Administrator Guru & Pengampu
│   ├── cetak_raport.php           # Cetak PDF Raport Resmi Siswa (Kop & TTD 3 Pihak)
│   ├── dashboard.php              # Dashboard metrik & pemantauan aktivitas real-time
│   ├── kelola_materi.php          # CRUD modul 20 bab dengan Visual Quill WYSIWYG
│   ├── kelola_siswa.php           # Manajemen data siswa (NIS, NISN, Rombel VII-A/B/C)
│   ├── kelola_soal.php            # Bank soal kuis pilihan ganda & pengaturan KKM 70
│   ├── laporan_nilai.php          # Rekapitulasi nilai, highlight bintang prestasi & log kuis
│   ├── login.php                  # Halaman autentikasi NUPTK guru
│   ├── logout.php                 # Terminasi sesi aman guru
│   ├── pengaturan.php             # Profil & pembaruan kata sandi guru
│   └── upload_media.php           # Pusat unggah media audio (.mp3) & video (.mp4)
│
├── assets/                        # Sumber Daya Multimedia Statis
│   ├── audio/                     # Berkas audio pelafalan native speaker (.mp3)
│   │   ├── audio bab 1.mp3        # Berkas audio pelafalan Bab 1
│   │   └── audio_bab_01.mp3       # Berkas audio pelafalan terstandarisasi
│   ├── css/
│   │   ├── quill.snow.css         # Tema visual editor Quill
│   │   └── style.css              # Stylesheet kustom tema Navy (#1A3A5C) v4.4.0
│   ├── img/
│   │   └── logo.png               # Logo resmi SMP Swasta Nommensen (resolusi tinggi)
│   ├── js/
│   │   └── quill.js               # Pustaka core editor visual Quill
│   └── video/                     # Berkas animasi video percakapan 20 bab (.mp4)
│       ├── Video 01_Greetings & Partings.mp4
│       └── ... (Video 02 s.d. Video 20)
│
├── includes/                      # Modul Bersama & Middleware Arsitektur
│   ├── auth_admin.php             # Guard middleware autentikasi guru
│   ├── auth_siswa.php             # Guard middleware autentikasi siswa
│   ├── footer.php                 # Komponen footer terpadu
│   ├── header.php                 # Header Bootstrap 5.3.3 & Bootstrap Icons
│   └── sidebar.php                # Fixed navigation sidebar siswa responsif
│
├── siswa/                         # Portal Belajar Peserta Didik
│   ├── cetak_raport.php           # Cetak PDF Raport Evaluasi Mandiri Siswa
│   ├── conversation.php           # Akses cepat modul percakapan kontekstual
│   ├── ganti_password.php         # Pembaruan kata sandi mandiri siswa
│   ├── grammar.php                # Akses cepat modul tata bahasa
│   ├── hasil_kuis.php             # Layar pengumuman skor akhir & ketuntasan KKM
│   ├── index.php                  # Redirector aman ke login siswa
│   ├── kuis.php                   # Katalog kuis evaluasi & penanda status
│   ├── kuis_kerjakan.php          # Mesin ujian berwaktu anti-contek & palet soal
│   ├── kuis_proses.php            # Evaluator skor server-side yang aman
│   ├── login.php                  # Halaman autentikasi multi-auth (NIS atau NISN)
│   ├── logout.php                 # Terminasi sesi aman siswa
│   ├── materi_detail.php          # Detail 4 pilar per bab materi
│   ├── menu.php                   # Beranda belajar, statistik bento & leaderboard
│   ├── riwayat.php                # Histori pengerjaan kuis & grafik nilai
│   └── vocabulary.php             # Akses cepat modul penguasaan kosakata
│
├── config.php                     # Konfigurasi koneksi database PDO MySQL
├── db_smp_nomensen_english.sql    # File Database SQL Master Lengkap (8 Tabel + 142 Log Nilai Bab 1 & 2)
├── index.php                      # Halaman intro / landing page utama berlogo
├── panduan_singkat_audio.md       # Panduan teknis aktivasi audio pembelajaran
├── README.md                      # Panduan Instalasi & Penggunaan Klien
└── REPORT.md                      # Laporan Master & Dokumentasi Lengkap Proyek
```

---

## BAGIAN 8: PANDUAN PRESENTASI, SKENARIO SIMULASI PENGUJIAN, & SOLUSI KENDALA (TROUBLESHOOTING)

Saat Anda atau rekan Anda mempresentasikan aplikasi ini di hadapan penguji, klien, atau pihak sekolah, ikuti skenario demonstrasi ideal berikut:

### 8.1 Skenario Demonstrasi Ideal (Demo Flow)
1. **Tampilan Pembuka**: Buka `http://localhost/web-edu-smpnomensen-main/index.php`. Soroti bahwa logo resmi sekolah telah tampil proporsional di tengah kartu sambutan dengan visual modern Bootstrap 5 dan papan peringkat (*leaderboard*) Top 5 siswa berprestasi.
2. **Simulasi Siswa**:
   - Masuk menggunakan NIS `26001` (atau NISN `0136442625`) / Password `siswa123`.
   - Tunjukkan **Beranda Belajar** yang informatif: kartu statistik ringkas bento, 6 akses fitur utama, papan peringkat leaderboard dengan penanda lencana "Anda", dan katalog kurikulum 20 bab materi.
   - Buka salah satu bab materi (misal Bab 1), putar audio pelafalan untuk mendemonstrasikan pilar audio penutur asli, dan putar video untuk pilar percakapan.
   - Buka kuis dan tunjukkan cara kerja palet nomor soal, timer hitung mundur, dan mode anti-contek tanpa feedback instan. Selesaikan kuis dan tunjukkan skor evaluasi otomatis dengan standar kelulusan KKM 70.
   - Tunjukkan halaman **Riwayat Nilai** yang secara otomatis mencatat hasil kuis tersebut dengan tanggal & jam presisi.
   - Klik tombol **"Cetak Raport Resmi (PDF)"** untuk memperlihatkan dokumen cetak A4 resmi lengkap dengan Kop Surat SMP Swasta Nommensen, tabel nilai, predikat belajar, dan 3 kolom tanda tangan legal.
3. **Simulasi Guru/Admin**:
   - Masuk ke portal guru menggunakan NUPTK `8546774675230253` / Password `guru123`.
   - Perlihatkan **Dashboard** dengan sebaran siswa per rombel VII-A, VII-B, dan VII-C.
   - Buka menu **Laporan Nilai** untuk memperlihatkan tabulasi rombel (7A, 7B, 7C), kartu emas **Bintang Prestasi** siswa peraih nilai tertinggi, serta log pengerjaan kuis real-time.
   - Buka **Tab Raport Siswa**, pilih salah satu siswa dan demonstrasikan pencetakan raport resmi oleh guru.
   - Tunjukkan kemudahan mengunggah media audio/video langsung di menu **Upload Media**, menyusun naskah di **Kelola Materi** dengan editor visual Quill, serta mengelola data siswa di **Kelola Siswa**.

### 8.2 Solusi Cepat Kendala Teknis (Troubleshooting)

| Gejala Kendala | Penyebab Umum | Solusi Cepat |
| :--- | :--- | :--- |
| **Pesan error *"Koneksi database gagal"*** | Layanan MySQL pada XAMPP belum dijalankan atau database belum dibuat. | Buka XAMPP Control Panel, klik **Start** pada MySQL. Pastikan database bernama `db_smp_nomensen_english` sudah dibuat di phpMyAdmin dan diimpor file `db_smp_nomensen_english.sql`. |
| **Data kuis / siswa kosong saat pertama kali dibuka** | Database baru dibuat namun belum mengimpor data SQL master. | Buka phpMyAdmin, klik database `db_smp_nomensen_english`, klik tab **Import**, pilih berkas **`db_smp_nomensen_english.sql`**, lalu klik **Kirim**. |
| **Tampilan CSS berantakan / tidak update** | Cache web browser masih menyimpan CSS versi lama. | Tekan kombinasi tombol **Ctrl + F5** (Hard Refresh) pada browser Anda untuk memuat versi CSS terbaru `v=4.4.0`. |
| **Audio atau Video tidak dapat diputar** | Berkas fisik media belum berada di folder aset server. | Pastikan berkas media berekstensi `.mp3` diletakkan di `assets/audio/` dan berkas `.mp4` diletakkan di `assets/video/`. Baca rincian panduannya di [`panduan_singkat_audio.md`](panduan_singkat_audio.md). |
| **Gagal login siswa** | Menggunakan format identitas yang salah. | Siswa dapat menggunakan **NIS** (misal: `26001`) ataupun **NISN** (misal: `0136442625`) dengan kata sandi default `siswa123`. |

---

*Laporan Master Proyek disusun dan diverifikasi secara profesional untuk memastikan keandalan sistem pembelajaran Bahasa Inggris SMP Swasta Nommensen.*
