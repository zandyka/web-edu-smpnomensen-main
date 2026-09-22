# LAPORAN UJI KELAYAKAN SISTEM & PANDUAN SIDANG AKHIR
## RANCANG BANGUN APLIKASI PEMBELAJARAN BAHASA INGGRIS BERBASIS MULTIMEDIA DENGAN METODE MDLC
### Studi Kasus: Siswa Kelas VII SMP Swasta Nommensen

---

**INFORMASI DOKUMEN LAPORAN:**
- **Penyusun / Mahasiswa** : [Nama Klien / Mahasiswa] (NIM: [Nomor Induk Mahasiswa])
- **Program Studi / Jurusan** : [Teknik Informatika / Sistem Informasi / Ilmu Komputer]
- **Instansi Mitra Pengujian** : SMP Swasta Nommensen
- **Guru Pengampu Mitra** : Hedi Diana, S.Pd., Gr (NUPTK: 8546774675230253)
- **Tujuan Dokumen** : Naskah Rujukan Pengujian Program, Buku Petunjuk Operasional Sidang, dan Lembar Skrip Tanya Jawab Ujian Akhir (Maksimal ±5 Halaman A4).

---

## BAB 1. IDENTITAS & SPESIFIKASI TEKNIS SISTEM

### 1.1 Spesifikasi Perangkat Lunak & Lingkungan Pengembangan
Aplikasi ini dikembangkan sebagai media instruksional mandiri berbasis web lokal (*intranet/localhost*) yang ringan, stabil, dan dapat beroperasi penuh pada laboratorium komputer sekolah tanpa ketergantungan koneksi internet publik.

**Tabel 1.1 Spesifikasi Teknis Perangkat Lunak & Basis Data**
| Komponen Arsitektur | Teknologi / Standar yang Digunakan | Keterangan Fungsional |
|---|---|---|
| **Bahasa Pemrograman** | PHP versi 7.4+ (Native / Murni) | Logika backend terstruktur tanpa *overhead* framework berat. |
| **Pustaka Antarmuka** | Bootstrap 5.3.3 & Bootstrap Icons | Desain responsif, modern, dan tata letak *Fixed Sidebar*. |
| **Sistem Basis Data** | MySQL / MariaDB (Engine InnoDB) | Terdiri dari 8 tabel relasional yang memenuhi kaidah normalisasi 3NF. |
| **Koneksi Basis Data** | PDO (PHP Data Objects) | Menggunakan *Prepared Statements* penuh untuk proteksi injeksi SQL. |
| **Web Server Lokal** | Apache HTTP Server (via XAMPP) | Port standar HTTP 80 / SSL 443. |
| **Metode Rekayasa** | MDLC (Luther-Sutopo) | 6 siklus pengembangan perangkat lunak berbasis multimedia. |
| **Metode Pengujian** | Black-Box Testing | Uji kesesuaian fungsionalitas antarmuka dan validasi keluaran sistem. |
| **Alamat URL Lokal** | `http://localhost/web-edu-smpnomensen-main/` | Akses instan dari peramban (Chrome/Edge/Firefox). |

### 1.2 Hak Akses & Kredensial Uji Sistem
Sistem memisahkan ruang kerja ke dalam dua portal terisolasi dengan otorisasi berbasis sesi server:

**Tabel 1.2 Kredensial Pengujian Pengguna Bawaan**
| Peran (Role) | Identitas Pengguna | Kata Sandi | Halaman Akses Masuk | Hak Akses Utama |
|---|---|---|---|---|
| **Guru / Admin** | `8546774675230253` (NUPTK) | `guru123` | `/admin/login.php` | Mengelola 20 bab materi, editor teks WYSIWYG, bank 400 soal, unggah audio/video, dan memantau rapor nilai siswa. |
| **Siswa Didik** | `26001` (NIS Kelas VII-A) | `siswa123` | `/siswa/login.php` | Mengakses modul 4 pilar media (teks, audio native, video animasi, kuis), mengerjakan evaluasi berwaktu KKM 70, dan melihat riwayat rapor. |

---

## BAB 2. LATAR BELAKANG, RUMUSAN MASALAH & TUJUAN

### 2.1 Latar Belakang Masalah
Berdasarkan hasil studi pendahuluan di SMP Swasta Nommensen, proses belajar-mengajar Bahasa Inggris Kelas VII menghadapi 4 kendala utama:
1. **Keterbatasan Media Teks Konvensional:** Buku cetak tidak mampu memperdengarkan intonasi dan pelafalan kata (*pronunciation*) penutur asli (*native speaker*), sehingga siswa ragu melafalkan kosakata.
2. **Ketiadaan Evaluasi Formatif Seketika:** Latihan manual membutuhkan waktu koreksi berhari-hari oleh guru, sehingga siswa terlambat mengetahui konsep yang belum dipahami.
3. **Kendala Pemantauan Kemajuan Siswa:** Guru tidak memiliki rekapitulasi data digital yang mencatat ketuntasan belajar siswa secara individual per materi pokok.
4. **Ketiadaan Platform Terintegrasi:** Bahan ajar audio, video, bacaan, dan tes evaluasi terserak di berbagai media yang tidak terpusat.

### 2.2 Tujuan Pengembangan Proyek
Proyek tugas akhir ini bertujuan untuk:
1. Membangun aplikasi pembelajaran digital berbasis web lokal yang memuat **20 bab kurikulum resmi Bahasa Inggris Kelas VII** (Semester 1 & 2).
2. Menerapkan konsep **4 Pilar Media Terpadu** (Modul Teks, Video Animasi Kontekstual, Audio Penutur Asli, dan Kuis Interaktif) dalam satu kesatuan antarmuka.
3. Merancang mesin kuis evaluasi otomatis dengan **penghitung waktu mundur (*timer*)** dan batas kelulusan **KKM 70**.
4. Menyediakan **Portal Manajemen Pendidik** yang ramah pengguna awam (dilengkapi teks editor visual gaya MS Word dan unggah berkas instan).
5. Menerapkan standar keamanan web meliputi sanitasi input PDO Prepared Statements, enkripsi kata sandi Bcrypt, dan isolasi token sesi.

---

## BAB 3. METODOLOGI PENGEMBANGAN SISTEM (MDLC)

Proyek ini dibangun mengikuti metodologi **Multimedia Development Life Cycle (MDLC)** menurut Luther (1994) yang disempurnakan oleh Sutopo (2003). Metodologi ini dipilih karena secara khusus mengakomodasi siklus pengumpulan dan orkestrasi aset media digital (*Material Collecting*), yang tidak dimiliki oleh metodologi perangkat lunak umum seperti Waterfall atau Agile.

```
  [1. Concept] ──> [2. Design] ──> [3. Material Collecting]
                                             │
  [6. Distribution] <── [5. Testing] <── [4. Assembly]
```

**Tabel 3.1 Penerapan 6 Siklus MDLC pada Aplikasi**
| Tahapan Siklus | Aktivitas Rekayasa yang Dilakukan | Luaran (*Output*) Nyata |
|---|---|---|
| **1. Concept (Konsep)** | Menentukan sasaran pengguna (siswa SMP & guru), analisis silabus kelas VII, dan perumusan platform web lokal tanpa kuota internet. | Dokumen spesifikasi kebutuhan & arsitektur media 4 pilar. |
| **2. Design (Perancangan)** | Merancang diagram relasi tabel (ERD 3NF), tata letak *Fixed Sidebar*, alur pengerjaan kuis, serta palet warna identitas institusi (*Navy Blue #1A3A5C*). | Storyboard UI/UX, diagram alir data (DFD), dan skema basis data 8 tabel. |
| **3. Material Collecting** | Menghimpun 20 silabus modul naskah bab, berkas rekaman suara penutur asli (.mp3), video simulasi percakapan (.mp4), dan 400 butir soal kuis pilihan ganda. | Aset digital terstruktur di folder `assets/audio/`, `assets/video/`, dan berkas SQL. |
| **4. Assembly (Perakitan)** | Mengembangkan kode program PHP Native, antarmuka Bootstrap 5.3.3, integrasi basis data PDO, serta logika JavaScript untuk pengatur waktu kuis. | 23 berkas modul PHP fungsional (9 file admin, 14 file portal siswa). |
| **5. Testing (Pengujian)** | Melakukan pengujian fungsionalitas *Black-Box Testing* pada setiap tautan, formulir input, sistem login, serta ketahanan kalkulasi kuis. | Seluruh skenario uji berstatus valid, nol galat logika, dan bebas celah keamanan injeksi. |
| **6. Distribution (Distribusi)** | Mengemas direktori aplikasi ke dalam lingkungan XAMPP laboratorium sekolah, menyiapkan skrip basis data 1-klik, dan menyusun buku panduan. | Berkas master proyek siap pakai beserta `db_smp_nomensen_english.sql`. |

---

## BAB 4. CARA KERJA, ALUR & SISTEMATIKA SISTEM

### 4.1 Arsitektur Tiga Tingkat (*3-Tier Architecture*)
Sistem beroperasi menggunakan arsitektur web standar industri yang memisahkan tanggung jawab antarmuka, pemrosesan logika, dan penyimpanan data:
1. **Lapisan Penyajian (*Presentation Layer*):** Dibangun dengan HTML5 semantik, CSS3 murni, tata letak *Fixed Sidebar* anti-tumpang tindih, serta komponen Bootstrap 5.3.3 yang menyajikan tampilan bersih di peramban pengguna.
2. **Lapisan Logika Bisnis (*Logic Layer*):** Ditangani oleh skrip PHP 7.4+ Native. Berfungsi memvalidasi sesi aktif (`auth_siswa.php` / `auth_admin.php`), memproses kunci jawaban, dan mengelola alur CRUD data.
3. **Lapisan Data (*Data Layer*):** Basis data relasional MySQL/MariaDB yang mengelola integritas data akademik dan relasi foreign key, berdampingan dengan direktori penyimpanan fisik berkas multimedia di server.

---

### 4.2 Sistematika Alur Operasional Siswa (Belajar Mandiri & Ujian)

Alur belajar siswa dirancang bertahap agar siswa memperoleh pemahaman konsep secara terarah sebelum diuji kemampuannya:

```
[ Landing Page: index.php ]
            │
            ▼
[ Login Siswa: Masukkan NIS & Password ] ──> [ Verifikasi Hash Bcrypt & Buat Sesi ]
            │
            ▼
[ Dashboard Belajar: Menu 20 Bab Materi, 4 Kartu Metrik, Filter Semester ]
            │
            ▼
[ Halaman Modul 4 Pilar Media: Teks Modul + Audio Native + Video Dialog ]
            │
            ▼
[ Mulai Kuis Evaluasi: Timer Countdown Aktif + Soal Acak + Backup LocalStorage ]
            │
            ▼
[ Auto-Submit / Submit Manual ] ──> [ Evaluasi Server-Side kuis_proses.php ]
            │
            ▼
[ Rapor Hasil Instan: Nilai Angka, Status KKM 70, Benar/Salah & Rekap Riwayat ]
```

#### Tahapan Langkah Fungsional Siswa:
1. **Akses Masuk & Validasi Keamanan:** Siswa membuka beranda, memilih tombol siswa, dan memasukkan NIS serta kata sandi. Skrip `login.php` memeriksa keabsahan NIS via PDO dan mencocokkan kata sandi via `password_verify()`. Jika lolos, ID sesi baru diterbitkan secara aman melalui `session_regenerate_id(true)`.

```text
+-------------------------------------------------------------------------------+
| [ TEMPAT SCREENSHOT 1: HALAMAN UTAMA & FORM LOGIN SISWA ]                     |
| Keterangan Gambar 4.1:                                                        |
| - Tangkapan layar halaman depan (index.php) berlogo SMP Swasta Nommensen.     |
| - Formulir autentikasi login siswa (siswa/login.php) dengan input NIS & Sandi.|
+-------------------------------------------------------------------------------+
```

2. **Beranda Belajar Dinamis (`menu.php`):** Siswa disambut ringkasan personalisasi (nama dan kelas), 4 panel statistik (jumlah bab siap pelajari, total kuis tuntas, dan akumulasi rata-rata nilai), menu jalan pintas (*Vocabulary, Grammar, Conversation*), serta katalog 20 bab materi yang dapat disaring per semester.

```text
+-------------------------------------------------------------------------------+
| [ TEMPAT SCREENSHOT 2: BERANDA BELAJAR & KATALOG 20 BAB MATERI SISWA ]        |
| Keterangan Gambar 4.2:                                                        |
| - Tampilan kartu ringkasan progres belajar siswa di menu.php.                 |
| - Antarmuka navigasi katalog bab dengan filter pemilih Semester 1 dan 2.      |
+-------------------------------------------------------------------------------+
```

3. **Eksplorasi Modul 4 Pilar Media (`materi_detail.php`):** Pada bab yang dipilih, siswa disajikan konten multimedia lengkap:
   - **Pilar 1 (Teks):** Ringkasan kaidah tata bahasa dan dialog bacaan terstruktur.
   - **Pilar 2 (Audio Pelafalan):** Pemutar HTML5 yang memperdengarkan lafal penutur asli per kosakata penting.
   - **Pilar 3 (Video Kontekstual):** Pemutar video percakapan animasi situasi nyata.
   - **Pilar 4 (Kuis Pemahaman):** Tombol interaktif untuk melangkah ke sesi tes.

```text
+-------------------------------------------------------------------------------+
| [ TEMPAT SCREENSHOT 3: MODUL PEMBELAJARAN TERPADU 4 PILAR MEDIA ]             |
| Keterangan Gambar 4.3:                                                        |
| - Tampilan modul bab memperlihatkan pembaca teks materi kurikulum.            |
| - Elemen audio pemutar suara native speaker dan pemutar video percakapan.     |
+-------------------------------------------------------------------------------+
```

4. **Pengerjaan Kuis Interaktif Berwaktu (`kuis_kerjakan.php`):**
   - Siswa mengerjakan 20 soal pilihan ganda acak di bawah pantauan *timer hitung mundur* JavaScript.
   - **Mekanisme Ketahanan Sesi (*Fail-Safe LocalStorage*):** Setiap jawaban opsi yang dipilih serta sisa detik waktu disimpan berkala di penyimpanan lokal peramban (*localStorage*). Apabila peramban tidak sengaja tertutup atau komputer ter-refresh, waktu dan status jawaban tidak akan terulang dari awal.
   - **Penyerahan Otomatis (*Auto-Submit*):** Jika durasi waktu habis, sistem otomatis mengirim lembar jawaban ke server tanpa intervensi siswa.

5. **Kalkulasi Nilai Sisi Server (*Server-Side Validation* di `kuis_proses.php`):**
   - Demi mencegah manipulasi skor via *Inspect Element* peramban, penilaian kuis **100% diproses di server**.
   - Jawaban siswa dicocokkan dengan kunci asli di tabel `tb_soal`. Rumus perolehan: Skor = (Jumlah Benar / 20) * 100.
   - Hasil disimpan ke tabel `tb_hasil`. Apabila skor >= 70, siswa dinyatakan **TUNTAS (Memenuhi KKM)**.

```text
+-------------------------------------------------------------------------------+
| [ TEMPAT SCREENSHOT 4: LEMBAR UJIAN KUIS BERWAKTU & RAPOR KELULUSAN KKM 70 ]  |
| Keterangan Gambar 4.4:                                                        |
| - Tampilan lembar pengerjaan kuis dengan indikator timer hitung mundur aktif. |
| - Layar hasil_kuis.php yang menyajikan skor angka, status kelulusan KKM 70,   |
|   serta rincian rekapitulasi riwayat nilai di riwayat.php.                    |
+-------------------------------------------------------------------------------+
```

---

### 4.3 Sistematika Alur Manajemen Guru (Pengelolaan Konten & Nilai)

Portal pengajar dirancang untuk memudahkan operasional guru tanpa menuntut keahlian teknis pemrograman:

```
[ Login Guru: Masukkan NUPTK & Sandi ] ──> [ Dashboard Guru: Statistik Real-Time ]
                                                      │
         ┌──────────────────┬─────────────────────────┴──────────────┬──────────────────┐
         ▼                  ▼                                        ▼                  ▼
[ Kelola 20 Bab ]   [ Unggah Media ]                         [ Kelola Bank Soal ] [ Rapor Nilai ]
• Editor WYSIWYG     • Audio (.mp3)                           • Paket Kuis & KKM   • Real-Time
• Template 1-Klik    • Video (.mp4)                           • Opsi A-D & Kunci   • Rekap Kelas
• Live Preview       • Auto-Unlink File saat data dihapus     • 400 Butir Soal     • Filter Bab
```

#### Tahapan Langkah Fungsional Guru:
1. **Dasbor Metrik (`admin/dashboard.php`):** Menampilkan pemantauan langsung jumlah siswa terdaftar, total bab aktif, volume bank butir soal, serta log riwayat akses siswa terbaru.
2. **Kelola Modul Materi (`admin/kelola_materi.php`):** Guru menyusun materi ajar dengan bantuan **Editor Visual WYSIWYG** (fitur format teks tebal, miring, daftar poin, dan tabel layaknya Microsoft Word) tanpa perlu mengetik kode HTML manual. Dilengkapi fitur *Live Preview* untuk melihat sudut pandang siswa.
3. **Manajemen Berkas Media (`admin/upload_media.php`):** Guru dapat mengunggah rekaman suara (.mp3, .wav) dan video (.mp4) dengan verifikasi format otomatis. **Otomasi Pembersihan:** Saat data media dihapus melalui sistem, fungsi `unlink()` di PHP akan menghapus berkas fisik di folder server secara permanen guna mencegah penumpukan sampah memori penyimpanan.
4. **Bank Soal & Kuis (`admin/kelola_soal.php`):** Pengaturan durasi batas pengerjaan kuis, penetapan ambang KKM, serta peracikan 4 pilihan jawaban dan penentuan kunci jawaban pasti.
5. **Rekapitulasi Nilai Kelas (`admin/laporan_nilai.php`):** Guru memantau skor siswa yang masuk secara seketika (*real-time*), lengkap dengan filter pencarian per bab evaluasi.

```text
+-------------------------------------------------------------------------------+
| [ TEMPAT SCREENSHOT 5: DASHBOARD GURU & EDITOR MATERI WYSIWYG ]               |
| Keterangan Gambar 4.5:                                                        |
| - Tampilan dasbor utama admin/dashboard.php dengan ringkasan data kelas.      |
| - Form penyunting materi di kelola_materi.php menggunakan antarmuka WYSIWYG.  |
+-------------------------------------------------------------------------------+
```

```text
+-------------------------------------------------------------------------------+
| [ TEMPAT SCREENSHOT 6: MODUL UPLOAD MEDIA & REKAPITULASI RAPOR NILAI GURU ]   |
| Keterangan Gambar 4.6:                                                        |
| - Antarmuka upload_media.php untuk pengelolaan berkas audio & video bab.      |
| - Tabel rekap nilai ujian siswa di laporan_nilai.php dengan fitur filter bab. |
+-------------------------------------------------------------------------------+
```

---

### 4.4 Skema Relasi Basis Data (Normalisasi 3NF)

Basis data `db_smp_nomensen_english` disusun atas 8 tabel yang saling berelasi erat untuk menjamin konsistensi data dan meniadakan duplikasi:

```
[ tb_guru ] (1) ────< (N) [ tb_materi ] (1) ────< (N) [ tb_audio ]
                               │         └────< (N) [ tb_video ]
                               │
                               └───────────< (1) [ tb_kuis ] (1) ────< (N) [ tb_soal ]
                                                       │
[ tb_siswa ] (1) ───────────< (N) [ tb_hasil ] >───────┘
```

**Tabel 4.1 Ringkasan 8 Tabel Basis Data Sistem**
| Nama Tabel | Peran Entitas | Kunci Utama (PK) | Kunci Tamu (FK) & Sifat Penghapusan |
|---|---|---|---|
| `tb_guru` | Data akun autentikasi guru/admin | `id_guru` | - |
| `tb_siswa` | Data akun dan identitas kelas siswa | `id_siswa` | - |
| `tb_materi` | Modul ajar 20 bab kurikulum | `id_materi` | `id_guru` (`ON DELETE SET NULL`) |
| `tb_audio` | Direktori berkas suara pelafalan | `id_audio` | `id_materi` (`ON DELETE CASCADE`) |
| `tb_video` | Direktori berkas animasi percakapan | `id_video` | `id_materi` (`ON DELETE CASCADE`) |
| `tb_kuis` | Paket evaluasi ujian & standar KKM | `id_kuis` | `id_materi` (`ON DELETE CASCADE`), `id_guru` |
| `tb_soal` | Bank 400 butir soal & kunci jawaban | `id_soal` | `id_kuis` (`ON DELETE CASCADE`) |
| `tb_hasil` | Rekam jejak nilai dan skor siswa | `id_hasil` | `id_siswa` & `id_kuis` (`ON DELETE CASCADE`) |

---

## BAB 5. IMPLEMENTASI KEAMANAN SISTEM PERANGKAT LUNAK

**Tabel 5.1 Matriks Penanganan Keamanan Aplikasi**
| Potensi Ancaman | Vektor Serangan | Mekanisme Proteksi yang Diterapkan di Kode |
|---|---|---|
| **SQL Injection** | Memasukkan sintaks injeksi manipulatif (misal: `' OR '1'='1`) pada kolom formulir login atau kuis. | **PDO Prepared Statements penuh** dengan *parameter binding* (`bindParam()`). Basis data mengompilasi cetak biru kueri terlebih dahulu sehingga seluruh input murni diperlakukan sebagai nilai string data pasif. |
| **Cross-Site Scripting (XSS)** | Menyisipkan skrip JavaScript berbahaya pada modul bacaan atau nama siswa untuk membajak data. | Menggunakan fungsi filter **`htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`** pada seluruh variabel dinamis yang dicetak ke peramban. |
| **Kebocoran Kata Sandi** | Pencurian data dari dalam basis data oleh pihak luar (*database dump*). | Menerapkan algoritma mutakhir **Bcrypt via `password_hash()`**. Kata sandi tersimpan dalam deret acak satu arah sepanjang 60 karakter dengan *salt unik*, aman dari serangan *rainbow table*. |
| **Pembajakan Sesi (*Session Fixation*)** | Eksploitasi token sesi peramban yang sama sebelum dan setelah proses masuk (*login*). | Mengeksekusi rutin **`session_regenerate_id(true)`** saat proses verifikasi login sukses guna menghanguskan ID sesi lama dan menerbitkan token otentikasi baru. |
| **Akses Ilegal Tanpa Login** | Menembak URL modul dalam secara langsung (misal: langsung membuka `/admin/kelola_materi.php`). | **Middleware Guard Interseptor** (`auth_admin.php` dan `auth_siswa.php`) yang memverifikasi eksistensi variabel sesi sebelum merender komponen halaman. |

---

## BAB 6. SKRIP PERCAKAPAN SIMULASI UJI SIDANG AKHIR

> **Petunjuk bagi Mahasiswa:** Skrip ini adalah rangkuman dialog tanya jawab taktis. Pahami esensi argumen teknisnya agar Anda dapat menjawab penguji secara lugas, tenang, dan ilmiah.

### 6.1 Sesi Pembukaan & Latar Belakang
- **Penguji:** *"Silakan perkenalkan diri Anda secara singkat dan jelaskan urgensi mengapa Anda mengangkat judul tugas akhir ini!"*
- **Mahasiswa:** *"Terima kasih Yang Terhormat Bapak/Ibu Dewan Penguji. Nama saya [Nama Mahasiswa], NIM [NIM]. Judul tugas akhir saya adalah: **'Rancang Bangun Aplikasi Pembelajaran Bahasa Inggris Berbasis Multimedia Menggunakan Metode MDLC pada SMP Swasta Nommensen'**.*
  *Penelitian ini dilatarbelakangi kendala nyata di SMP Swasta Nommensen, di mana pembelajaran Bahasa Inggris kelas VII masih mengandalkan buku teks cetak pasif tanpa panduan audio pelafalan otentik, minim visualisasi situasi percakapan, serta ketiadaan evaluasi seketika. Sistem ini hadir memadukan teks modul, audio native speaker, video kontekstual, dan kuis otomatis berwaktu dalam satu platform terpadu yang dapat diakses mandiri di laboratorium komputer sekolah tanpa internet."*

### 6.2 Sesi Pembuktian Metode MDLC
- **Penguji:** *"Mengapa Anda memilih metodologi MDLC ketimbang metode umum seperti Waterfall atau Agile?"*
- **Mahasiswa:** *"Izin menjawab Bapak/Ibu. Aplikasi ini pada hakikatnya adalah perangkat ajar berbasis multimedia terintegrasi. Metodologi **MDLC Luther-Sutopo** memiliki tahapan khusus yang bernama **Material Collecting** — yaitu siklus terdedikasi untuk mengumpulkan, memproduksi, dan memvalidasi aset multimedia seperti berkas audio pelafalan, video animasi kontekstual, dan 400 butir bank soal kurikulum.*
  *Tahap pengumpulan bahan media ini tidak diwadahi secara eksplisit dalam Waterfall maupun Agile yang berorientasi murni pada rekayasa logika kode software bisnis. Oleh karena itu, MDLC adalah instrumen metodologi ilmiah yang paling presisi untuk proyek ini."*

### 6.3 Sesi Pendalaman Arsitektur & Logika Kuis
- **Penguji:** *"Bagaimana sistem Anda menjamin integritas kejujuran pengerjaan kuis siswa dari kecurangan?"*
- **Mahasiswa:** *"Kami menerapkan pertahanan dua sisi (*client* dan *server*):*
  *1. Di sisi browser klien, JavaScript menjalankan timer hitung mundur yang otomatis men-submit kuis jika durasi habis, didukung penyimpanan sementara di **localStorage** agar siswa tidak dapat mencurangi waktu dengan sengaja me-refresh halaman.*
  *2. Penentu skor kuis **100% diproses di sisi server (`kuis_proses.php`)**. Kunci jawaban tidak pernah disematkan di kode browser siswa, melainkan dicocokkan langsung dari database via PHP. Sehingga meskipun siswa menggunakan fitur Inspect Element browser, mereka tidak akan menemukan kunci jawaban maupun memanipulasi nilai."*

### 6.4 Sesi Pembuktian Basis Data & Foreign Key
- **Penguji:** *"Mengapa pada tabel `tb_materi` terhadap `tb_guru` Anda menggunakan `SET NULL`, tetapi pada `tb_materi` terhadap `tb_audio` Anda gunakan `CASCADE`?"*
- **Mahasiswa:** *"Perbedaan ini didasarkan pada analisis integritas dependensi data:*
  *- Opsi **`CASCADE`** diterapkan pada berkas turunan bab (audio, video, soal kuis) karena jika suatu bab materi dihapus, maka aset audio dan soal terkait bab tersebut tidak lagi memiliki relevansi logis, sehingga wajib ikut terhapus secara bersih agar tidak menjadi data sampah.*
  *- Sebaliknya, opsi **`SET NULL`** diterapkan pada relasi guru pembuat materi. Jika akun guru tersebut dimutasi atau dihapus dari sistem, 20 modul kurikulum dan paket kuis yang telah disusun tidak boleh ikut terhapus, melainkan tetap dipertahankan di database sekolah untuk dikelola oleh guru pengganti."*

### 6.5 Sesi Pertanggungjawaban Pilihan Teknologi (PHP Native vs Framework)
- **Penguji:** *"Mengapa memilih PHP Native dan bukan framework seperti Laravel?"*
- **Mahasiswa:** *"Pertama, dari aspek **lingkungan target deployment**: Aplikasi ini didistribusikan untuk laboratorium komputer sekolah menggunakan server lokal XAMPP standar. PHP Native dapat langsung dieksekusi tanpa memerlukan konfigurasi dependensi Composer, Node.js, atau instalasi paket eksternal yang rumit bagi pihak sekolah.*
  *Kedua, dari sisi **performa**: Sistem ini memiliki skala kompleksitas menengah. Pemanfaatan PHP Native dengan lapisan PDO dan Bootstrap 5.3.3 menghasilkan kecepatan rendering yang sangat instan dan konsumsi memori yang sangat hemat bagi komputer laboratorium."*

### 6.6 Sesi Evaluasi Diri & Penutup
- **Penguji:** *"Apakah sistem ini memiliki kekurangan? Apa rencana pengembangan Anda ke depan?"*
- **Mahasiswa:** *"Sistem saat ini masih beroperasi pada jaringan lokal intranet sekolah dan antarmukanya dioptimasi khusus untuk layar monitor komputer desktop laboratorium. Untuk pengembangan di masa mendatang, sistem dapat dikembangkan secara daring (*cloud hosting*), ditambahkan modul analitik grafis pencapaian per kompetensi dasar, serta diintegrasikan dengan fitur gamifikasi lencana belajar.*
  *Terima kasih atas segala saran berharga dari Dewan Penguji untuk penyempurnaan karya ini. Wassalamu'alaikum Warahmatullahi Wabarakatuh."*

---

*Laporan Persiapan Uji Sidang Akhir — SMP Swasta Nommensen | Disusun Rapi & Siap Disalin ke Format Dokumen Microsoft Word (.docx)*