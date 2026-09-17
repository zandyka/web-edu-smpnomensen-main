<?php
/**
 * File: siswa/ganti_password.php
 * Deskripsi: Halaman mandiri bagi siswa untuk mengganti kata sandi akunnya.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = trim($_POST['old_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Semua kolom kata sandi wajib diisi!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Kata sandi baru minimal harus 6 karakter!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Konfirmasi kata sandi baru tidak cocok!";
    } else {
        try {
            // Ambil data siswa saat ini
            $stmt = $pdo->prepare("SELECT password FROM tb_siswa WHERE id_siswa = :id LIMIT 1");
            $stmt->execute(['id' => $_SESSION['siswa_id']]);
            $current_user = $stmt->fetch();

            if ($current_user && password_verify($old_password, $current_user['password'])) {
                // Hash password baru
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE tb_siswa SET password = :pass WHERE id_siswa = :id");
                $update_stmt->execute([
                    'pass' => $new_hash,
                    'id' => $_SESSION['siswa_id']
                ]);

                $success_message = "Kata sandi berhasil diperbarui! Silakan gunakan kata sandi baru untuk login berikutnya.";
            } else {
                $error_message = "Kata sandi saat ini (lama) yang Anda masukkan salah!";
            }
        } catch (PDOException $e) {
            $error_message = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}

$page_title = 'Ganti Kata Sandi';
$active_page = 'ganti_password';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <h1>Pengaturan Akun Siswa</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-shield-lock-fill fs-3 text-primary"></i>
                        <h2 class="h5 fw-bold text-dark mb-0">Ganti Kata Sandi (Password)</h2>
                    </div>

                    <!-- Informasi Akun Siswa -->
                    <div class="card bg-light border-0 rounded-3 p-3 mb-4 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Nama Siswa:</span>
                            <strong class="text-dark"><?= htmlspecialchars($_SESSION['siswa_nama']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">NIS / Kelas:</span>
                            <span class="text-dark fw-semibold font-monospace"><?= htmlspecialchars($_SESSION['siswa_nis']) ?> (<?= htmlspecialchars($_SESSION['siswa_kelas']) ?>)</span>
                        </div>
                    </div>

                    <!-- Alert Error -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 px-3 rounded-3 border-0 shadow-sm mb-3 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                            <div><strong>Peringatan: </strong> <?= htmlspecialchars($error_message) ?></div>
                        </div>
                    <?php endif; ?>

                    <!-- Alert Sukses -->
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success d-flex align-items-center gap-2 py-2 px-3 rounded-3 border-0 shadow-sm mb-3 small" role="alert">
                            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                            <div><strong>Berhasil: </strong> <?= htmlspecialchars($success_message) ?></div>
                        </div>
                    <?php endif; ?>

                    <form action="ganti_password.php" method="POST">
                        <div class="mb-3">
                            <label for="old_password" class="form-label small fw-semibold text-secondary">
                                Kata Sandi Saat Ini:
                            </label>
                            <input type="password" id="old_password" name="old_password" class="form-control rounded-3 py-2" placeholder="Masukkan password lama..." required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label small fw-semibold text-secondary">
                                Kata Sandi Baru:
                            </label>
                            <input type="password" id="new_password" name="new_password" class="form-control rounded-3 py-2" placeholder="Minimal 6 karakter..." required minlength="6">
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label small fw-semibold text-secondary">
                                Ulangi Kata Sandi Baru:
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control rounded-3 py-2" placeholder="Ketik ulang password baru..." required minlength="6">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary rounded-3 fw-bold py-2 px-4 flex-grow-1 shadow-sm">
                                <i class="bi bi-save me-1"></i>Simpan Password Baru
                            </button>
                            <a href="menu.php" class="btn btn-outline-secondary rounded-3 py-2 px-3 fw-semibold">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php require_once '../includes/footer.php'; ?>