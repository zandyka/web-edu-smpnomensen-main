<?php
/**
 * File: siswa/login.php
 * Deskripsi: Halaman login untuk Siswa.
 *            Memvalidasi input NIS dan Password menggunakan database.
 *            Password diverifikasi dengan aman menggunakan password_verify().
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['siswa_logged_in']) && $_SESSION['siswa_logged_in'] === true) {
    header("Location: menu.php");
    exit();
}

require_once '../config.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis = trim($_POST['nis'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nis) || empty($password)) {
        $error_message = "Username (NIS) dan Password wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM tb_siswa WHERE nis = :nis OR nisn = :nis LIMIT 1");
            $stmt->execute(['nis' => $nis]);
            $siswa = $stmt->fetch();

            if ($siswa && password_verify($password, $siswa['password'])) {
                session_regenerate_id(true);

                $stmt_ll = $pdo->prepare("UPDATE tb_siswa SET last_login = NOW() WHERE id_siswa = :id");
                $stmt_ll->execute(['id' => $siswa['id_siswa']]);

                $_SESSION['siswa_logged_in'] = true;
                $_SESSION['siswa_id'] = $siswa['id_siswa'];
                $_SESSION['siswa_nis'] = $siswa['nis'];
                $_SESSION['siswa_nama'] = $siswa['nama_siswa'];
                $_SESSION['siswa_kelas'] = $siswa['kelas'];

                header("Location: menu.php");
                exit();
            } else {
                $error_message = "Username (NIS) atau Password salah!";
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi masalah sistem. Silakan coba beberapa saat lagi.";
        }
    }
}

if (isset($_GET['error']) && $_GET['error'] === 'session_expired') {
    $error_message = "Silakan login terlebih dahulu untuk mengakses pembelajaran.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Siswa - SMP Swasta Nommensen</title>
    
    <!-- Memanggil Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Memanggil Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Memanggil CSS utama kustom -->
    <link rel="stylesheet" href="../assets/css/style.css?v=4.3.0">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Header Atas -->
    <header class="top-header text-center text-white py-3 shadow-sm">
        <span class="fs-5 fw-bold">Aplikasi Pembelajaran Bahasa Inggris</span>
    </header>

    <!-- Konten Utama Tengah (Bootstrap 5) -->
    <main class="container my-auto py-5">
        <div class="row justify-content-center">
            <div class="col-11 col-sm-9 col-md-7 col-lg-5 col-xl-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                    <div class="card-header bg-primary text-white text-center py-3 border-0">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-person-circle fs-4"></i>
                            <span class="fs-5 fw-bold">LOGIN SISWA</span>
                        </div>
                    </div>
                    
                    <div class="card-body p-4 p-md-4">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 mb-4 rounded-3 border-0 shadow-sm" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                                <div><?= htmlspecialchars($error_message) ?></div>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="nis" class="form-label fw-semibold text-secondary small">NIS / NISN</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person-badge"></i></span>
                                    <input type="text" id="nis" name="nis" class="form-control border-start-0 ps-0" placeholder="Masukkan NIS Anda" value="<?= htmlspecialchars($_POST['nis'] ?? '') ?>" required autofocus>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold text-secondary small">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" id="password" name="password" class="form-control border-start-0 ps-0" placeholder="Masukkan Password Anda" required>
                                </div>
                            </div>

                            <div class="d-grid mb-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold py-2 shadow-sm">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="../index.php" class="text-decoration-none text-muted fw-semibold small">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda
                    </a>
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