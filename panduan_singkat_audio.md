# Panduan Singkat: Mengaktifkan & Memperbarui Audio Pembelajaran

Dokumen ini adalah panduan praktis dan ringkas bagi guru / administrator / penguji untuk memastikan **Audio Pelafalan & Latihan Listening (MP3)** terbaca dan dapat langsung diputar pada aplikasi.

---

## 📌 Mengapa Audio Belum Muncul di Web?

Aplikasi membaca berkas audio berdasarkan **2 hal yang saling terhubung**:
1. **Berkas Fisik:** Berkas MP3 wajib berada di dalam folder `assets/audio/` (misal: `assets/audio/audio bab 1.mp3`).
2. **Data di Database (`tb_audio`):** Nama berkas audio tersebut harus tercatat di tabel `tb_audio` pada database MySQL.

> Jika database di phpMyAdmin komputer klien masih menggunakan versi lama (belum di-update), web akan menampilkan status:  
> `<span style="color:#64748b; font-weight:600;">[ 🔇 Audio disiapkan guru ]</span>`  
> dan pemutar audio tidak akan muncul.

---

## 🚀 3 Pilihan Cara Mengaktifkan Audio (Pilih Salah Satu)

---

### CARA 1 (Paling Cepat - 30 Detik) ⚡
#### Eksekusi Perintah SQL di phpMyAdmin *(Direkomendasikan)*

Jika database sudah terpasang dan Anda hanya ingin langsung memunculkan audio Bab 1 tanpa repot:

1. Buka peramban (browser) dan akses: **`http://localhost/phpmyadmin`**
2. Pada panel sebelah kiri, klik nama database: **`db_smp_nomensen_english`**
3. Klik tab **SQL** pada menu navigasi bagian atas.
4. Salin (*copy*) perintah di bawah ini dan tempel (*paste*) ke kotak teks SQL:

```sql
-- Memastikan audio Bab 1 terdaftar dan langsung terbaca oleh web
INSERT INTO tb_audio (id_audio, id_materi, file_audio, keterangan)
VALUES (1, 1, 'audio bab 1.mp3', 'Audio Pelafalan & Listening Bab 1: Expression of Greeting & Parting')
ON DUPLICATE KEY UPDATE 
    file_audio = 'audio bab 1.mp3',
    keterangan = 'Audio Pelafalan & Listening Bab 1: Expression of Greeting & Parting';
```

5. Klik tombol **Kirim / Go** di pojok kanan bawah.
6. Buka kembali halaman materi Bab 1 di portal siswa (`siswa/materi_detail.php?id=1`), lalu **Refresh (F5)**.
7. **Selesai!** Pemutar audio resmi langsung muncul dan siap diputar.

---

### CARA 2 (Paling Lengkap & Bersih) 📦
#### Re-Import File `db_smp_nomensen_english.sql` Terbaru

Gunakan cara ini jika Anda ingin menyelaraskan **seluruh data master terbaru**, mencakup:
- Seluruh 20 tautan audio bab materi.
- 71 akun siswa Kelas 7 dengan NIS baru (`26001` - `26071`) dan NISN terpisah.
- 482 data riwayat kuis realistis dan perankingan leaderboard.

**Langkah-langkah:**
1. Buka **`http://localhost/phpmyadmin`**.
2. Klik database **`db_smp_nomensen_english`** di sebelah kiri.
3. Klik tab **Import** di bagian atas.
4. Pada kolom *File to import*, klik tombol **Choose File / Telusuri**.
5. Pilih berkas: **`db_smp_nomensen_english.sql`** yang ada di folder utama proyek Anda.
6. Gulir ke bawah lalu klik tombol **Import / Kirim**.
7. Tunggu beberapa detik hingga muncul notifikasi sukses berwarna hijau (*"Import has been successfully finished"*).
8. **Selesai!** Seluruh audio, data siswa, dan nilai otomatis sinkron 100%.

---

### CARA 3 (Paling Mudah Tanpa Sentuh Database) 🌐
#### Unggah Langsung via Menu Admin / Guru di Web

Jika Anda tidak ingin membuka phpMyAdmin sama sekali, Anda dapat mengunggahnya langsung dari antarmuka web:

1. Masuk ke portal guru: **`http://localhost/web-edu-smpnomensen-main/admin/login.php`**
   - **NUPTK / Username:** `8546774675230253`
   - **Password:** `guru123`
2. Pada menu navigasi sebelah kiri, klik **Unggah Media (Audio & Video)**.
3. Pada kartu formulir:
   - Pilih jenis media: **Audio Pelafalan / Listening (MP3, WAV, M4A)**.
   - Pilih berkas audio dari komputer Anda: pilih file `audio bab 1.mp3`.
   - Pilih bab materi: **Bab 1: Expression of Greeting & Parting**.
   - Keterangan: *Audio Pelafalan Bab 1*.
4. Klik tombol **Simpan & Unggah Media**.
5. **Selesai!** Sistem secara otomatis akan memindahkan file ke folder `assets/audio/` dan mendaftarkannya ke database.

---

## 🔍 Checklist Pengecekan Berkas Fisik Audio

Pastikan berkas fisik audio telah berada pada lokasi folder berikut:

```text
c:\xampp\htdocs\[folder-projek]\
 ├── assets\
 │    └── audio\
 │         ├── audio bab 1.mp3    <-- Wajib ada di sini
 │         └── audio_bab_01.mp3
 ├── db_smp_nomensen_english.sql
 └── ...
```

> **Catatan Penting:**  
> Jika berkas `audio bab 1.mp3` masih berada di luar folder (hanya di folder utama dan belum masuk ke dalam `assets/audio/`), silakan salin (*copy*) berkas tersebut ke dalam folder:  
> `assets/audio/audio bab 1.mp3`

---

## ❓ Kendala & Solusi Cepat (Troubleshooting)

| Gejala Masalah | Penyebab | Solusi |
|---|---|---|
| Muncul tulisan abu-abu *"Audio disiapkan guru"* | Data audio belum tercatat di database atau file fisik belum di folder `assets/audio/`. | Jalankan **CARA 1** (query SQL singkat di atas) dan pastikan file ada di `assets/audio/`. |
| Pemutar audio muncul, tetapi ketika ditekan tombol Play suara tidak berbunyi | Format berkas rusak atau volume browser/laptop mute. | Pastikan file berformat `.mp3` valid dan periksa volume perangkat Anda. |
| Tombol Play berputar terus (*loading*) | Nama file di database berbeda spasi/huruf besar-kecil dengan nama file asli di folder. | Pastikan namanya sama persis: `audio bab 1.mp3`. |

---
*Dokumen ini disusun untuk memudahkan tim teknis, mahasiswa, dan guru mitra SMP Swasta Nommensen.*
