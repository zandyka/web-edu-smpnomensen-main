<?php
/**
 * File: index.php
 * Deskripsi: Halaman Utama Aplikasi Pembelajaran Bahasa Inggris (SMP Swasta Nommensen).
 *            Menampilkan portal masuk siswa (Kelas VII) dan guru/admin secara bersih dan terpusat.
 */

require_once 'config.php';

// Jalur ke file logo sekolah
$logo_path = 'assets/img/logo.png';
$has_logo = file_exists($logo_path);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi Pembelajaran Bahasa Inggris Berbasis Multimedia pada SMP Swasta Nommensen Menggunakan Metode MDLC.">
    <title>Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen</title>
    
    <!-- Memanggil Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Memanggil Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Memanggil CSS utama kustom -->
    <link rel="stylesheet" href="assets/css/style.css?v=4.6.0">

    <style>
        body.index-body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .welcome-card {
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="index-body">

    <!-- Navigasi Bar Atas Sederhana -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 px-3 flex-shrink-0 z-3 shadow-sm">
        <div class="container-fluid px-2 px-md-4 d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1 fs-6 fw-bold text-white">
                <i class="bi bi-mortarboard-fill me-2 text-warning"></i>SMP SWASTA NOMMENSEN &bull; PORTAL KELAS VII
            </span>
            <span class="text-white-50 small d-none d-md-inline">Media Instruksional Mandiri Berbasis Multimedia</span>
        </div>
    </nav>

    <!-- Kontainer Konten Utama -->
    <main class="flex-grow-1 d-flex flex-column justify-content-center py-4 py-lg-5">
        <div class="container my-auto">
            <div class="row justify-content-center">
                
                <!-- Sambutan & Akses Masuk Portal -->
                <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                    <div class="welcome-card card border-0 rounded-4 p-4 p-sm-5 text-center bg-white shadow-sm">
                        
                        <!-- Logo Bulat -->
                        <div class="logo-circle mx-auto mb-3 shadow-sm" style="width: 88px; height: 88px; border-radius: 50%; background: #ffffff; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; padding: 4px;">
                            <?php if ($has_logo): ?>
                                <img src="<?= $logo_path ?>" alt="Logo SMP Swasta Nommensen" class="logo-img" style="max-height: 64px; width: auto; object-fit: contain;">
                            <?php else: ?>
                                <span class="logo-text fw-bold text-primary">SMP</span>
                            <?php endif; ?>
                        </div>

                        <!-- Judul & Selamat Datang -->
                        <h1 class="welcome-text fw-bold text-dark fs-5 mb-1">
                            Aplikasi Pembelajaran
                        </h1>
                        <p class="text-primary fw-bold fs-5 mb-1" style="font-family: 'Outfit', sans-serif;">
                            Bahasa Inggris Multimedia (Kelas VII)
                        </p>
                        <p class="school-text text-secondary fw-semibold small mb-4">SMP Swasta Nommensen</p>

                        <!-- Tombol Aksi Masuk -->
                        <div class="d-grid gap-2.5 col-12 mx-auto mb-4">
                            <a href="siswa/login.php" class="btn btn-primary rounded-3 fw-bold py-2.5 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btn-siswa">
                                <i class="bi bi-mortarboard-fill fs-5"></i>
                                <span>Mulai Belajar (Siswa Kelas 7)</span>
                            </a>
                            <a href="admin/login.php" class="btn btn-outline-secondary rounded-3 fw-semibold py-2.5 d-flex align-items-center justify-content-center gap-2" id="btn-guru">
                                <i class="bi bi-person-gear fs-5"></i>
                                <span>Login Guru / Admin</span>
                            </a>
                        </div>

                        <div class="text-muted small border-top pt-3">
                            <div><i class="bi bi-layers me-1 text-primary"></i>Rombel VII-A, VII-B, VII-C &bull; Kurikulum 20 Bab</div>
                            <div class="mt-1" style="font-size: 0.75rem;"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Kec. Babul Makmur, Kab. Aceh Tenggara</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer Bawah -->
    <footer class="bottom-footer text-center py-2.5 text-white border-top flex-shrink-0">
        <small>&copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen</small>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>