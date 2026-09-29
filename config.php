<?php
/**
 * File: config.php
 * Deskripsi: Menghubungkan aplikasi web ke database MySQL menggunakan PDO.
 * 
 * Fitur Ketahanan Koneksi:
 * 1. Mendukung deteksi otomatis nama database baik dengan 1 'm' (db_smp_nomensen_english) 
 *    maupun 2 'm' (db_smp_nommensen_english) untuk mencegah kesalahan ketik pengguna.
 * 2. Mampu membuat database secara otomatis jika belum dibuat di MySQL server.
 * 3. Menampilkan pesan panduan visual yang informatif jika layanan MySQL XAMPP belum aktif.
 */

$host = 'localhost';
$username = 'root'; // Username bawaan XAMPP
$password = '';     // Password bawaan XAMPP (kosong)

// Daftar nama database yang didukung (mencegah galat typo ejaan nama sekolah)
$candidate_dbs = [
    'db_smp_nomensen_english',
    'db_smp_nommensen_english'
];

$pdo = null;
$dbname = 'db_smp_nomensen_english';

// 1. Coba koneksi ke kandidat database yang mungkin sudah dibuat oleh klien
foreach ($candidate_dbs as $candidate) {
    try {
        $test_pdo = new PDO("mysql:host=$host;dbname=$candidate;charset=utf8mb4", $username, $password);
        $test_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $test_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        $pdo = $test_pdo;
        $dbname = $candidate;
        break;
    } catch (PDOException $e) {
        // Jika database belum ada (error 1049), lanjutkan mencoba kandidat lain
        if ($e->getCode() != 1049) {
            // Jika error koneksi server (misal MySQL mati), tangani di bawah
            break;
        }
    }
}

// 2. Jika database belum ditemukan sama sekali, hubungkan ke server dan buat otomatis
if (!$pdo) {
    try {
        // Koneksi langsung ke server MySQL tanpa memilih nama database
        $server_pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
        $server_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Buat database default
        $server_pdo->exec("CREATE DATABASE IF NOT EXISTS `db_smp_nomensen_english` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Hubungkan kembali ke database yang baru dibuat
        $pdo = new PDO("mysql:host=$host;dbname=db_smp_nomensen_english;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $dbname = 'db_smp_nomensen_english';
    } catch (PDOException $e) {
        // Tampilan informasi ramah pengguna jika MySQL XAMPP belum dijalankan
        die("
        <!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Koneksi Database Terputus</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
        </head>
        <body class='bg-light d-flex align-items-center justify-content-center min-vh-100 p-3'>
            <div class='card border-0 shadow-sm rounded-4 p-4 p-md-5 max-w-500 bg-white' style='max-width: 550px;'>
                <div class='text-center mb-3'>
                    <div class='badge bg-danger-subtle text-danger p-3 rounded-circle fs-3 mb-2'>⚠️</div>
                    <h4 class='fw-bold text-dark mb-1'>Layanan MySQL Belum Berjalan</h4>
                    <p class='text-muted small'>Sistem tidak dapat terhubung ke server database lokal.</p>
                </div>
                <div class='alert alert-warning small mb-4'>
                    <strong>Penyebab:</strong> Modul <strong>MySQL</strong> pada <strong>XAMPP Control Panel</strong> kemungkinan belum diklik tombol <strong>Start</strong>.
                </div>
                <h6 class='fw-bold text-dark small mb-2'>Langkah Penyelesaian:</h6>
                <ol class='small text-secondary ps-3 mb-4'>
                    <li>Buka aplikasi <strong>XAMPP Control Panel</strong> di laptop/komputer Anda.</li>
                    <li>Pada baris <strong>MySQL</strong>, klik tombol <strong>Start</strong> hingga berwarna hijau.</li>
                    <li>Muat ulang (refresh) halaman ini kembali.</li>
                </ol>
                <div class='text-center'>
                    <button onclick='window.location.reload()' class='btn btn-primary px-4 rounded-pill fw-bold'>
                        Coba Refresh Halaman
                    </button>
                </div>
            </div>
        </body>
        </html>
        ");
    }
}
?>
