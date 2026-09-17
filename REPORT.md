# LAPORAN MASTER & PANDUAN KOMPREHENSIF SISTEM
## Aplikasi Pembelajaran Bahasa Inggris Berbasis Multimedia (SMP Swasta Nommensen)
### *Panduan Teknis, Arsitektur Sistem, Basis Data, dan Manual Operasional Proyek*

---

> **Pesan Pengantar dari Instruktur/Pembimbing:**
> *"Selamat datang di dokumentasi resmi sistem pembelajaran digital Bahasa Inggris SMP Swasta Nommensen. Laporan ini dirancang khusus sebagai panduan induk satu pintu (one-stop master report) agar siapapun yang membaca—baik Anda, rekan pengembang, guru pengajar, maupun tim penguji—dapat memahami seluruh rancang bangun aplikasi ini dengan mudah, mendalam, dan terstruktur. Pelajari setiap bab secara bertahap layaknya kita sedang berdiskusi di ruang kelas komputasi."*

---

## DAFTAR ISI LAPORAN

1. [BAGIAN 1: Landasan Konseptual & Arsitektur Kurikulum](#bagian-1-landasan-konseptual--arsitektur-kurikulum)
2. [BAGIAN 2: Panduan Instalasi & Deployment Lokal (XAMPP)](#bagian-2-panduan-instalasi--deployment-lokal-xampp)
3. [BAGIAN 3: Direktori Kredensial Akun Pengguna](#bagian-3-direktori-kredensial-akun-pengguna)
4. [BAGIAN 4: Arsitektur Rekayasa Perangkat Lunak & Cara Kerja Di Balik Layar](#bagian-4-arsitektur-rekayasa-perangkat-lunak--cara-kerja-di-balik-layar)
5. [BAGIAN 5: Dokumentasi Lengkap Skema Basis Data (8 Tabel Aktual)](#bagian-5-dokumentasi-lengkap-skema-basis-data-8-tabel-aktual)
6. [BAGIAN 6: Manual Operasional Pengguna (Portal Siswa & Guru)](#bagian-6-manual-operasional-pengguna-portal-siswa--guru)
7. [BAGIAN 7: Struktur Direktori Proyek Bersih (Clean Repository)](#bagian-7-struktur-direktori-proyek-bersih-clean-repository)
8. [BAGIAN 8: Panduan Presentasi, Simulasi Pengujian, & Solusi Kendala (Troubleshooting)](#bagian-8-panduan-presentasi-simulasi-pengujian--solusi-kendala-troubleshooting)

---

## BAGIAN 1: LANDASAN KONSEPTUAL & ARSITEKTUR KURIKULUM

Sebagai seorang pendidik dan pengembang perangkat lunak, sistem ini tidak dirancang hanya sekadar aplikasi web biasa, melainkan media instruksional interaktif yang dibangun menggunakan metodologi ilmiah **Multimedia Development Life Cycle (MDLC)** menurut Luther-Sutopo, yang meliputi 6 tahapan terstruktur:
1. **Concept**: Menetapkan kebutuhan pembelajaran Bahasa Inggris kelas VII SMP Swasta Nommensen yang memerlukan visualisasi dan audio otentik.
2. **Design**: Merancang storyboard antarmuka, diagram relasi database (ERD), alur interaksi kuis, dan layout fixed sidebar responsive.
3. **Material Collecting**: Mengumpulkan modul naskah kurikulum 20 bab, file audio pelafalan format `.mp3`, dan video animasi kontekstual format `.mp4`.
4. **Assembly**: Mengintegrasikan seluruh komponen frontend (Bootstrap 5.3.3, CSS kustom) dengan backend PHP native (PDO) dan MySQL.
5. **Testing**: Menguji fungsionalitas menggunakan metode *Black-Box Testing* untuk memastikan nol galat logika dan nol kebocoran sesi.
6. **Distribution**: Mengemas sistem ke dalam lingkungan web server lokal terintegrasi yang siap digunakan di laboratorium komputer sekolah.

### 1.1 Pendekatan 4 Pilar Media Terintegrasi
Setiap bab materi pembelajaran menyajikan 4 pilar multimedia yang saling menguatkan proses pemerolehan bahasa (*language acquisition*):
- **Pilar 1: Modul Teks Terstruktur** — Memuat rangkuman materi ringkas, kaidah tata bahasa, dan contoh kalimat komunikatif.
- **Pilar 2: Video Animasi Kontekstual** — Menghadirkan visualisasi percakapan situasional dunia nyata agar siswa memahami gestur, intonasi, dan konteks sosial.
- **Pilar 3: Laboratorium Audio (Listening & Pronunciation)** — Menyajikan pelafalan kata per kata dan dialog oleh *native speaker* guna melatih kepekaan telinga (*listening*) dan akurasi berbicara (*speaking*).
- **Pilar 4: Kuis Evaluasi Interaktif (KKM 70)** — Evaluasi pemahaman mandiri dengan sistem hitung mundur (*timer*), koreksi instan, dan penentuan kelulusan standar KKM 70.

### 1.2 Cakupan Silabus 20 Bab Kurikulum Terstruktur
Sistem memuat 20 bab materi komprehensif untuk jenjang SMP Kelas VII:
- **Semester 1 (Bab 1 s.d. 10)**: *Greetings & Introductions, Alphabet & Numbers, Things in the Classroom, Family Members, Telling Time, Days & Months, Animals Around Us, Describing People, Daily Activities, My School Environment.*
- **Semester 2 (Bab 11 s.d. 20)**: *Public Places & Directions, Food & Drinks, Shopping & Prices, Hobbies & Free Time, Weather & Seasons, Asking & Giving Permission, Past Activities, Comparative Adjectives, Short Functional Texts, English Songs & Poetry Appreciation.*

---

## BAGIAN 2: PANDUAN INSTALASI & DEPLOYMENT LOKAL (XAMPP)

Untuk menjalankan proyek ini di komputer Anda atau komputer klien, ikuti 4 langkah mudah berikut layaknya instruksi laboratorium:

### Langkah 1: Penempatan Folder Proyek
Pastikan folder proyek diletakkan di dalam direktori root server web Apache Anda:
- **Lokasi standar Windows (XAMPP)**: `C:\xampp\htdocs\web-edu-smpnomensen-main` atau `C:\xampp\htdocs\Projek-web-antis`.

### Langkah 2: Mengaktifkan Layanan Server Lokal
1. Buka aplikasi **XAMPP Control Panel**.
2. Klik tombol **Start** pada modul **Apache** dan **MySQL**.
3. Pastikan indikator keduanya berwarna hijau dan port (80/443 untuk Apache, 3306 untuk MySQL) aktif normal.

### Langkah 3: Menyiapkan Basis Data (1-Klik Setup)
1. Buka browser (Chrome / Edge) lalu akses: `http://localhost/phpmyadmin`.
2. Klik menu **Databases** (Basis Data Baru), ketikkan nama database:
   ```text
   db_smp_nomensen_english
   ```
   *(Pilih collation `utf8mb4_general_ci`, lalu klik tombol **Create**)*.
3. Klik nama database `db_smp_nomensen_english` pada panel sebelah kiri.
4. Klik tab **Import** di bagian atas, pilih file **`db_smp_nomensen_english.sql`**, lalu klik **Import / Kirim**.
5. Seluruh tabel (8 tabel) dan data kurikulum 20 bab materi beserta akun pengguna akan langsung terpasang sempurna.

### Langkah 4: Verifikasi Konfigurasi Koneksi (`config.php`)
Buka file `config.php` di teks editor Anda. Secara default, konfigurasinya sudah disesuaikan dengan standar XAMPP:
```php
<?php
$host = 'localhost';
$dbname = 'db_smp_nomensen_english';
$username = 'root'; // Username default XAMPP
$password = '';     // Password default XAMPP (kosong)
```

---

## BAGIAN 3: DIREKTORI KREDENSIAL AKUN PENGGUNA

Sistem telah dilengkapi dengan akun resmi yang siap digunakan untuk presentasi, pengujian fungsionalitas, maupun operasional harian:

### 3.1 Akun Guru / Tenaga Pendidik Resmi
- **Nama Guru**: Hedi Diana, S.Pd., Gr
- **Identitas (NUPTK / NIP)**: `8546774675230253` *(Guru Non-PNS bersertifikat pendidik)*
- **Kata Sandi (Password)**: `guru123`
- **Tautan Login Guru**: `http://localhost/web-edu-smpnomensen-main/admin/login.php`
- **Hak Akses**: Mengelola 20 bab materi, mengunggah file media audio & video, meracik butir soal kuis, memantau rapor nilai kelas, dan menambah/mengedit data akun siswa.

### 3.2 Akun Siswa (Pengujian Belajar)
- **Nama Siswa**: Fernando Michael
- **Identitas (NIS)**: `26001`
- **Kelas**: VII-A
- **Kata Sandi (Password)**: `siswa123`
- **Tautan Login Siswa**: `http://localhost/web-edu-smpnomensen-main/siswa/login.php`
- **Hak Akses**: Menjelajahi Beranda Belajar, mengakses modul 20 bab materi, memutar audio & video pembelajaran, mengerjakan kuis berwaktu, melihat riwayat nilai & status kelulusan (KKM 70), dan mengganti kata sandi mandiri.

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
4. **Session Hijacking Guard**:
   Saat pengguna berhasil login, sistem segera mengeksekusi `session_regenerate_id(true)` untuk membatalkan token sesi lama dan menerbitkan token baru.

### 4.2 Arsitektur Antarmuka: Layout Fixed Sidebar & Zero Overlap
Salah satu penyempurnaan utama pada sistem ini adalah tata letak navigasi samping (*sidebar*):
- **Sidebar Siswa (`.siswa-sidebar`)**: Berposisi tetap permanen (`position: fixed; top: 0; left: 0; width: 285px; height: 100vh; overflow: hidden; z-index: 1000;`).
- **Konten Utama Siswa (`.siswa-main`)**: Diberikan offset presisi (`margin-left: 285px;`) sehingga saat pengguna menggulir (*scroll*) konten pelajaran, sidebar tetap terkunci rapi di sebelah kiri tanpa ada penumpukan elemen (*zero overlap*) dan tanpa adanya bilah gulir ganda (*zero dual scrollbar*).
- **Sidebar Guru (`.sidebar`)**: Menerapkan arsitektur identik (`width: 250px; position: fixed;`) dengan offset konten admin (`margin-left: 250px;`).

### 4.3 Mesin Kuis Interaktif Berwaktu (Client-Side & Server-Side State)
- **Timer Hitung Mundur Real-time**: Waktu pengerjaan kuis dihitung mundur via JavaScript. Apabila waktu habis, formulir kuis akan terkumpul secara otomatis (*auto-submit*).
- **Proteksi Ketahanan Sesi Kuis**: Data jawaban sementara dan sisa waktu pengerjaan disimpan ke dalam `localStorage` browser. Apabila laptop siswa tidak sengaja me-refresh halaman atau daya padam sesaat, status kuis dapat dipulihkan secara instan.
- **Kalkulasi Nilai di Server**: Skor akhir tidak dihitung di sisi browser klien (untuk mencegah kecurangan/manipulasi inspect element), melainkan dievaluasi ulang oleh skrip backend `kuis_proses.php` yang mencocokkan kunci jawaban resmi dari database.

---

## BAGIAN 5: DOKUMENTASI LENGKAP SKEMA BASIS DATA (8 TABEL AKTUAL)

Database `db_smp_nomensen_english` dirancang menggunakan bentuk normal ketiga (3NF) guna menjamin konsistensi data dan menghindari anomali peremajaan data.

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
Menyimpan kredensial dan identitas akademik siswa SMP.
- `id_siswa` (INT, Primary Key, Auto Increment)
- `nis` (VARCHAR(20), Unique, Not Null) — Nomor Induk Siswa untuk login
- `nisn` (VARCHAR(20), Unique, Nullable) — Nomor Induk Siswa Nasional
- `nama_siswa` (VARCHAR(100), Not Null) — Nama lengkap peserta didik
- `password` (VARCHAR(255), Not Null) — Hash Bcrypt kata sandi
- `kelas` (VARCHAR(50), Not Null) — Rombongan belajar (contoh: VII-A)
- `last_login` (DATETIME, Nullable) — Waktu login terakhir
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 5.2 Tabel `tb_guru` (Pengguna Pendidik / Administrator)
Menyimpan identitas guru pengampu mata pelajaran Bahasa Inggris.
- `id_guru` (INT, Primary Key, Auto Increment)
- `nip` (VARCHAR(20), Unique, Not Null) — NIP / NUPTK resmi guru untuk login
- `nama_guru` (VARCHAR(100), Not Null) — Nama lengkap guru beserta gelar
- `password` (VARCHAR(255), Not Null) — Hash Bcrypt kata sandi
- `last_login` (DATETIME, Nullable) — Waktu login terakhir guru
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)

### 5.3 Tabel `tb_materi` (Silabus 20 Bab Kurikulum)
Menyimpan unit pembelajaran bahasa Inggris yang terbagi ke dalam 2 semester.
- `id_materi` (INT, Primary Key, Auto Increment)
- `kategori` (VARCHAR(50), Not Null) — Kategori topik pembelajaran
- `semester` (TINYINT(1), Not Null, Default 1) — Nilai 1 (Semester 1) atau 2 (Semester 2)
- `urutan` (INT, Not Null, Default 1) — Urutan bab pembelajaran (1 s.d. 20)
- `judul_materi` (VARCHAR(150), Not Null) — Nama judul bab materi
- `ringkasan` (TEXT, Nullable) — Ringkasan singkat topik pembelajaran
- `konten_teks` (TEXT, Nullable) — Isi lengkap modul bacaan guru
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)
- `id_guru` (INT, Foreign Key ke `tb_guru.id_guru` ON DELETE SET NULL)

### 5.4 Tabel `tb_audio` (Media Suara / Pronunciation)
Menyimpan berkas rekaman suara penutur asli untuk latihan menyimak dan pelafalan.
- `id_audio` (INT, Primary Key, Auto Increment)
- `id_materi` (INT, Foreign Key ke `tb_materi.id_materi` ON DELETE CASCADE)
- `file_audio` (VARCHAR(255), Not Null) — Nama berkas audio `.mp3` di `assets/audio/`
- `keterangan` (VARCHAR(255), Nullable) — Deskripsi percakapan/kosakata audio

### 5.5 Tabel `tb_video` (Media Visual Gerak / Conversation)
Menyimpan berkas video animasi interaktif per bab materi.
- `id_video` (INT, Primary Key, Auto Increment)
- `id_materi` (INT, Foreign Key ke `tb_materi.id_materi` ON DELETE CASCADE)
- `file_video` (VARCHAR(255), Not Null) — Nama berkas video `.mp4` di `assets/video/`
- `keterangan` (VARCHAR(255), Nullable) — Konteks percakapan situasional video

### 5.6 Tabel `tb_kuis` (Sesi Evaluasi Pembelajaran)
Menyimpan paket evaluasi belajar berbasis batas waktu dan KKM.
- `id_kuis` (INT, Primary Key, Auto Increment) — Memuat 20 paket kuis evaluasi untuk Bab 1 s.d. Bab 20
- `judul_kuis` (VARCHAR(150), Not Null) — Judul tes/kuis evaluasi
- `kategori_materi` (VARCHAR(50), Not Null) — Pengelompokan jenis kuis
- `waktu_pengerjaan` (INT, Not Null) — Batas durasi waktu (ditetapkan 20 menit untuk 20 butir soal)
- `id_materi` (INT, Foreign Key ke `tb_materi.id_materi` ON DELETE CASCADE)
- `nilai_lulus` (INT, Default 70) — Standar Kriteria Ketuntasan Minimal (KKM)
- `created_at` (TIMESTAMP, Default CURRENT_TIMESTAMP)
- `id_guru` (INT, Foreign Key ke `tb_guru.id_guru` ON DELETE SET NULL)

### 5.7 Tabel `tb_soal` (Bank Butir Pertanyaan)
Menyimpan butir pertanyaan pilihan ganda dengan 4 opsi alternatif. Berisi total **400 butir soal** (tepat 20 soal per kuis evaluasi Bab 1 s.d. 20).
- `id_soal` (INT, Primary Key, Auto Increment)
- `id_kuis` (INT, Foreign Key ke `tb_kuis.id_kuis` ON DELETE CASCADE)
- `pertanyaan` (TEXT, Not Null) — Naskah pertanyaan soal
- `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d` (VARCHAR(255), Not Null) — Opsi pilihan jawaban
- `jawaban_benar` (ENUM('A','B','C','D'), Not Null) — Kunci jawaban resmi

### 5.8 Tabel `tb_hasil` (Rapor Histori Nilai Siswa)
Merekam jejak performa belajar dan hasil latihan setiap peserta didik.
- `id_hasil` (INT, Primary Key, Auto Increment)
- `id_siswa` (INT, Foreign Key ke `tb_siswa.id_siswa` ON DELETE CASCADE)
- `id_kuis` (INT, Foreign Key ke `tb_kuis.id_kuis` ON DELETE CASCADE)
- `skor` (INT, Not Null) — Skor akhir evaluasi skala 0-100
- `jumlah_benar` (INT, Not Null) — Jumlah jawaban benar
- `jumlah_salah` (INT, Not Null) — Jumlah jawaban salah
- `waktu_selesai` (TIMESTAMP, Default CURRENT_TIMESTAMP) — Waktu kuis diselesaikan

---

## BAGIAN 6: MANUAL OPERASIONAL PENGGUNA (PORTAL SISWA & GURU)

Berikut panduan langkah demi langkah cara mengoperasikan sistem dari sudut pandang pengguna:

### 6.1 Panduan Siswa: Alur Belajar Mandiri
1. **Membuka Halaman Utama (`index.php`)**:
   Siswa melihat logo resmi SMP Swasta Nommensen yang terbingkai elegan, kemudian menekan tombol biru **"Mulai Belajar (Siswa)"**.
2. **Login Siswa (`siswa/login.php`)**:
   Masukkan NIS (`26001`) dan Password (`siswa123`).
3. **Eksplorasi Beranda Belajar (`siswa/menu.php`)**:
   - Siswa disambut oleh banner nama dan kelas.
   - Melihat 4 metrik belajar: Total Materi (20 Bab), Media 4 Pilar, Total Kuis Selesai, dan Akumulasi Rata-Rata Nilai.
   - Memilih 6 jalan pintas fitur utama atau memfilter katalog materi berdasarkan Semester 1 atau Semester 2.
4. **Mempelajari Bab Materi (`siswa/materi_detail.php`)**:
   - Membaca modul teks materi bahasa Inggris.
   - Memutar audio pronunciation penutur asli untuk melatih pendengaran.
   - Menonton video animasi situasi percakapan.
5. **Mengerjakan Kuis Evaluasi (`siswa/kuis_kerjakan.php`)**:
   - Menekan tombol **"Kerjakan Kuis Sekarang"**.
   - Mengerjakan soal pilihan ganda di bawah pengawasan timer hitung mundur.
   - Menyerahkan kuis dan langsung menerima pengumuman hasil: nilai angka, predikat Tuntas/Belum Tuntas (KKM 70), serta pesan motivasi.
6. **Melihat Rekapitulasi Rapor (`siswa/riwayat.php`)**:
   Siswa dapat mengecek grafik performa dan seluruh histori nilai yang pernah dicapai.

### 6.2 Panduan Guru: Manajemen Pembelajaran Digital
1. **Login Administrator (`admin/login.php`)**:
   Masukkan NUPTK (`8546774675230253`) dan Password (`guru123`).
2. **Dashboard Guru (`admin/dashboard.php`)**:
   Memantau statistik total siswa, bab materi aktif, bank soal, dan log aktivitas real-time.
3. **Kelola 20 Bab Materi (`admin/kelola_materi.php`)**:
   Guru dapat memperbarui naskah penjelasan modul pembelajaran dengan **Editor Visual WYSIWYG (ala Microsoft Word)** yang ramah pengguna non-teknis tanpa perlu menulis kode HTML, dilengkapi template materi 1-klik (Modul Standar, Dialog, Tabel Kosakata) dan fitur Pratinjau Tampilan Siswa secara langsung.
4. **Unggah Media Pembelajaran (`admin/upload_media.php`)**:
   Guru dapat mengunggah file rekaman audio (`.mp3`, `.wav`, `.ogg`, `.m4a`) dan video materi (`.mp4`, `.webm`) secara langsung ke sistem & database tanpa perlu membuat unit materi terlebih dahulu di menu Kelola Materi, dilengkapi pemutar media instan dan kaitan fleksibel ke Bab kurikulum.
5. **Penyusunan Kuis & Soal (`admin/kelola_soal.php`)**:
   Guru dapat membuat paket kuis, menentukan batas durasi pengerjaan, menetapkan KKM, serta mengisi butir soal pilihan ganda lengkap dengan kunci jawabannya.
6. **Rekapitulasi Laporan Nilai (`admin/laporan_nilai.php`)**:
   Guru dapat melihat seluruh daftar nilai siswa, memfilter berdasarkan bab kuis tertentu, dan mengunduh data laporan untuk arsip sekolah.
7. **Kelola Data Siswa (`admin/kelola_siswa.php`)**:
   Menambahkan siswa baru atau mereset kata sandi siswa yang lupa kata sandi.

---

## BAGIAN 7: STRUKTUR DIREKTORI PROYEK BERSIH (CLEAN REPOSITORY)

Proyek ini telah melalui tahap refaktorisasi dan pembersihan komprehensif. Seluruh file pengujian (*testing script*), file draf (*scratch html*), dan file SQL potongan yang tidak terpakai telah dibersihkan sehingga direktori proyek rapi dan siap produksi:

```text
web-edu-smpnomensen-main/
│
├── admin/                         # Portal Administrator Guru
│   ├── dashboard.php              # Dashboard metrik & pemantauan aktivitas
│   ├── kelola_materi.php          # CRUD modul kurikulum 20 bab
│   ├── kelola_siswa.php           # Manajemen data akademik siswa
│   ├── kelola_soal.php            # Bank soal kuis & pengaturan KKM 70
│   ├── laporan_nilai.php          # Rekapitulasi nilai & progres kelas
│   ├── login.php                  # Halaman autentikasi NUPTK guru
│   ├── logout.php                 # Terminasi sesi aman guru
│   ├── pengaturan.php             # Profil & pembaruan kata sandi guru
│   └── upload_media.php           # Pengelola berkas audio (.mp3) & video (.mp4)
│
├── assets/                        # Sumber Daya Multimedia Statis
│   ├── audio/                     # Berkas rekaman pelafalan native speaker (.mp3)
│   ├── css/
│   │   └── style.css              # Stylesheet kustom tema Navy (#1A3A5C) v4.4.0
│   ├── img/
│   │   └── logo.png               # Berkas logo resmi sekolah (resolusi tinggi)
│   └── video/                     # Berkas animasi video percakapan (.mp4)
│
├── includes/                      # Modul Bersama & Middleware Arsitektur
│   ├── auth_admin.php             # Guard middleware autentikasi guru
│   ├── auth_siswa.php             # Guard middleware autentikasi siswa
│   ├── footer.php                 # Komponen footer terpadu
│   ├── header.php                 # Header Bootstrap 5.3.3 & Bootstrap Icons
│   └── sidebar.php                # Fixed navigation sidebar siswa
│
├── siswa/                         # Portal Belajar Siswa
│   ├── ganti_password.php         # Pembaruan kata sandi mandiri
│   ├── hasil_kuis.php             # Layar rapor skor evaluasi kuis
│   ├── kuis.php                   # Katalog sesi kuis aktif
│   ├── kuis_kerjakan.php          # Mesin ujian berwaktu interaktif
│   ├── kuis_proses.php            # Evaluator skor server-side
│   ├── login.php                  # Halaman autentikasi NIS siswa
│   ├── logout.php                 # Terminasi sesi aman siswa
│   ├── materi_detail.php          # Detail 4 pilar per bab materi
│   ├── menu.php                   # Beranda belajar & katalog 20 bab
│   └── riwayat.php                # Histori pengerjaan kuis siswa
│
├── config.php                     # Konfigurasi koneksi database PDO
├── db_smp_nomensen_english.sql    # File Database SQL Master Lengkap (Skema + 20 Bab + Akun)
├── index.php                      # Halaman intro / landing page utama berlogo
├── README.md                      # Panduan Instalasi & Penggunaan Klien
└── REPORT.md                      # Laporan Master & Dokumentasi Lengkap Proyek
```

---

## BAGIAN 8: PANDUAN PRESENTASI, SIMULASI PENGUJIAN, & SOLUSI KENDALA (TROUBLESHOOTING)

Saat Anda atau rekan Anda mempresentasikan aplikasi ini di hadapan penguji, klien, atau pihak sekolah, ikuti skenario demonstrasi ideal berikut:

### 8.1 Skenario Demonstrasi Ideal (Demo Flow)
1. **Tampilan Pembuka**: Buka `http://localhost/web-edu-smpnomensen-main/index.php`. Soroti bahwa logo resmi sekolah telah tampil proporsional di tengah kartu sambutan dengan visual modern Bootstrap 5.
2. **Simulasi Siswa**:
   - Masuk menggunakan NIS `26001` / Password `siswa123`.
   - Tunjukkan **Beranda Belajar** yang informatif: kartu statistik ringkas, 6 akses fitur utama, dan katalog kurikulum 20 bab materi yang rapi tanpa teks meluap.
   - Buka salah satu bab materi (misal Bab 1), putar audio pelafalan untuk mendemonstrasikan pilar audio, dan putar video untuk pilar percakapan.
   - Buka kuis dan tunjukkan cara kerja timer hitung mundur. Selesaikan kuis dan tunjukkan skor evaluasi otomatis dengan standar kelulusan KKM 70.
   - Tunjukkan halaman **Riwayat Nilai** yang secara otomatis mencatat hasil kuis tersebut.
3. **Simulasi Guru/Admin**:
   - Masuk ke portal guru menggunakan NUPTK `8546774675230253` / Password `guru123`.
   - Perlihatkan **Fixed Sidebar** yang terkunci kokoh di sisi kiri saat konten digulir.
   - Buka menu **Laporan Nilai** untuk memperlihatkan bahwa skor yang baru saja dikerjakan siswa telah masuk secara real-time ke rekapan guru.
   - Tunjukkan kemudahan mengunggah media audio/video dan menambah butir soal kuis baru.

### 8.2 Solusi Cepat Kendala Teknis (Troubleshooting)

| Gejala Kendala | Penyebab Umum | Solusi Cepat |
| :--- | :--- | :--- |
| **Pesan error *"Koneksi database gagal"*** | Layanan MySQL pada XAMPP belum dijalankan atau database belum dibuat. | Buka XAMPP Control Panel, klik **Start** pada MySQL. Pastikan database bernama `db_smp_nomensen_english` sudah dibuat di phpMyAdmin dan diimpor file `db_smp_nomensen_english.sql`. |
| **Tampilan CSS berantakan / tidak update** | Cache web browser masih menyimpan CSS versi lama. | Tekan kombinasi tombol **Ctrl + F5** (Hard Refresh) pada browser Anda untuk memuat versi CSS terbaru `v=4.4.0`. |
| **Audio atau Video tidak dapat diputar** | Berkas fisik media belum berada di folder aset server. | Pastikan berkas media berekstensi `.mp3` diletakkan di `assets/audio/` dan berkas `.mp4` diletakkan di `assets/video/`. |
| **Gagal login guru atau siswa** | Salah memasukkan kombinasi NUPTK/NIS atau kata sandi. | Gunakan akun pengujian bawaan yang tercantum di Bagian 3 laporan ini: Siswa (`26001` / `siswa123`) atau Guru (`8546774675230253` / `guru123`). |

---

*Laporan Master Proyek disusun dan diverifikasi secara profesional untuk memastikan keandalan sistem pembelajaran Bahasa Inggris SMP Swasta Nommensen.*
