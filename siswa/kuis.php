<?php
/**
 * File: siswa/kuis.php
 * Deskripsi: Halaman Daftar Kuis Evaluasi Pembelajaran Bahasa Inggris SMP Kelas 7.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

$filter_sem = isset($_GET['semester']) ? intval($_GET['semester']) : 0;

$sql = "
    SELECT k.*, m.urutan, m.semester, m.judul_materi
    FROM tb_kuis k
    LEFT JOIN tb_materi m ON k.id_materi = m.id_materi
";
if ($filter_sem === 1 || $filter_sem === 2) {
    $sql .= " WHERE m.semester = :sem";
}
$sql .= " ORDER BY IFNULL(m.urutan, k.id_kuis) ASC";

try {
    $stmt = $pdo->prepare($sql);
    if ($filter_sem === 1 || $filter_sem === 2) {
        $stmt->execute(['sem' => $filter_sem]);
    } else {
        $stmt->execute();
    }
    $quizzes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data kuis: " . $e->getMessage());
}

$page_title = 'Kuis & Latihan Soal';
$active_page = 'kuis';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <h1>Kuis Evaluasi Bahasa Inggris</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="mb-4">
            <h2 class="h4 fw-bold text-dark mb-1">
                Daftar Kuis Pembelajaran (Bab 1 - 20)
            </h2>
            <p class="text-secondary small mb-0">
                Uji pemahaman materi Anda dengan mengerjakan kuis interaktif per bab. Skor dan pembahasan akan langsung muncul setelah Anda selesai.
            </p>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'completed'): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 py-3 px-4 rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <span class="fw-semibold">Selamat! Anda telah menyelesaikan kuis dengan sukses.</span>
            </div>
        <?php endif; ?>

        <!-- Filter Tab Semester (Bootstrap Nav Pills) -->
        <div class="d-flex overflow-auto pb-2 mb-4">
            <ul class="nav nav-pills gap-2 flex-nowrap flex-md-wrap">
                <li class="nav-item">
                    <a href="kuis.php" class="nav-link rounded-pill px-3 py-2 fw-semibold <?= $filter_sem === 0 ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                        Semua Kuis
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kuis.php?semester=1" class="nav-link rounded-pill px-3 py-2 fw-semibold <?= $filter_sem === 1 ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                        Semester 1 (Kuis Bab 1 - 9)
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kuis.php?semester=2" class="nav-link rounded-pill px-3 py-2 fw-semibold <?= $filter_sem === 2 ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                        Semester 2 (Kuis Bab 10 - 20)
                    </a>
                </li>
            </ul>
        </div>

        <?php if (empty($quizzes)): ?>
            <div class="alert alert-light border border-dashed rounded-4 p-5 text-center text-muted shadow-sm">
                <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary"></i>
                Belum ada kuis yang tersedia untuk filter semester ini.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php foreach ($quizzes as $quiz): ?>
                    <?php
                    // Hitung jumlah butir soal asli
                    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM tb_soal WHERE id_kuis = :id");
                    $stmt_count->execute(['id' => $quiz['id_kuis']]);
                    $total_soal = $stmt_count->fetchColumn();
                    ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden d-flex flex-column bg-white">
                            
                            <!-- Header Kartu Kuis -->
                            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <?php if ($quiz['urutan']): ?>
                                        <span class="badge bg-primary-subtle text-primary fw-bold py-2 px-3 rounded-pill">
                                            BAB <?= sprintf("%02d", $quiz['urutan']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($quiz['semester']): ?>
                                        <span class="badge <?= $quiz['semester'] == 1 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info-emphasis' ?> py-2 px-3 rounded-pill fw-semibold">
                                            Semester <?= htmlspecialchars($quiz['semester']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="card-title h6 fw-bold text-dark mt-2 mb-0" style="min-height: 2.5rem;">
                                    <?= htmlspecialchars($quiz['judul_kuis']) ?>
                                </h3>
                            </div>

                            <!-- Body Kartu Kuis -->
                            <div class="card-body px-4 py-3 flex-grow-1">
                                <div class="list-group list-group-flush border-top border-bottom py-2 small">
                                    <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                        <span class="text-muted"><i class="bi bi-clock me-1"></i>Durasi Waktu:</span>
                                        <strong class="text-dark"><?= htmlspecialchars($quiz['waktu_pengerjaan']) ?> Menit</strong>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                        <span class="text-muted"><i class="bi bi-question-circle me-1"></i>Jumlah Soal:</span>
                                        <strong class="text-dark"><?= htmlspecialchars($total_soal) ?> Butir PG</strong>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                        <span class="text-muted"><i class="bi bi-award me-1"></i>KKM Kelulusan:</span>
                                        <strong class="text-success"><?= htmlspecialchars($quiz['nilai_lulus'] ?? 70) ?> Poin</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Aksi -->
                            <div class="card-footer bg-white border-0 pt-0 pb-4 px-4 d-flex flex-column gap-2">
                                <a href="kuis_kerjakan.php?id_kuis=<?= $quiz['id_kuis'] ?>" class="btn btn-primary w-100 rounded-3 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-play-fill fs-5"></i>
                                    <span>Kerjakan Kuis</span>
                                </a>

                                <?php if (!empty($quiz['id_materi'])): ?>
                                    <a href="materi_detail.php?id=<?= $quiz['id_materi'] ?>" class="btn btn-link text-decoration-none text-secondary small py-0 text-center">
                                        <i class="bi bi-arrow-left me-1"></i>Buka Modul &amp; Video Bab Ini
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

<?php require_once '../includes/footer.php'; ?>