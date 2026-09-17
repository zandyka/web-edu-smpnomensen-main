<?php
/**
 * File: index.php
 * Deskripsi: Halaman Splash/Intro aplikasi pembelajaran Bahasa Inggris.
 *            Disesuaikan persis dengan rancangan storyboard proposal.
 */

// Jalur ke file logo sekolah (bisa diganti dengan ekstensi lain seperti .jpg atau .svg jika diperlukan)
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Memanggil CSS utama kustom -->
    <link rel="stylesheet" href="assets/css/style.css?v=4.4.0">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Header Atas (Sesuai Storyboard & Bootstrap 5) -->
    <header class="top-header text-center text-white py-3 shadow-sm">
        <span class="fs-5 fw-bold tracking-wide">Aplikasi Pembelajaran Bahasa Inggris</span>
    </header>

    <!-- Konten Utama Tengah (Bootstrap 5 Card Layout) -->
    <main class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-10 col-md-8 col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 text-center bg-white">
                    <!-- Logo Bulat -->
                    <div class="logo-circle mx-auto mb-4 shadow-sm">
                        <?php if ($has_logo): ?>
                            <img src="<?= $logo_path ?>" alt="Logo SMP Swasta Nommensen" class="logo-img">
                        <?php else: ?>
                            <span class="logo-text fw-bold">LOGO</span>
                        <?php endif; ?>
                    </div>

                    <!-- Judul & Selamat Datang -->
                    <h1 class="welcome-text fw-bold text-dark fs-3 mb-2">
                        Selamat Datang di Aplikasi Pembelajaran<br>
                        Bahasa Inggris Berbasis Multimedia
                    </h1>
                    
                    <!-- Nama Sekolah -->
                    <p class="school-text text-secondary fw-semibold fs-5 mb-4">SMP Swasta Nommensen</p>

                    <!-- Tombol Aksi Tumpuk Vertikal -->
                    <div class="d-grid gap-3 col-12 col-md-10 mx-auto">
                        <a href="siswa/login.php" class="btn btn-primary btn-lg rounded-3 fw-bold py-3 shadow-sm" id="btn-siswa">
                            <i class="bi bi-mortarboard-fill me-2"></i>Mulai Belajar (Siswa)
                        </a>
                        <a href="admin/login.php" class="btn btn-outline-secondary btn-lg rounded-3 fw-semibold py-3" id="btn-guru">
                            <i class="bi bi-person-gear me-2"></i>Login Guru / Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Bawah -->
    <footer class="bottom-footer text-center py-3 text-white border-top">
        <small>&copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen</small>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>