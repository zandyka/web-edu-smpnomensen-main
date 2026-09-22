<?php
/**
 * File: siswa/menu.php
 * Deskripsi: Halaman Beranda Utama & Dashboard Pembelajaran Siswa (SMP Swasta Nommensen).
 *            Menyajikan antarmuka bersih, modern, dan ringkas: ringkasan profil, bento statistik,
 *            katalog 6 fitur unggulan, serta katalog 20 bab materi kurikulum.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

$id_siswa = $_SESSION['siswa_id'] ?? 0;
$filter_sem = isset($_GET['semester']) ? intval($_GET['semester']) : 0;

// 1. Query seluruh materi beserta info video dan kuis
$sql = "
    SELECT m.*, v.file_video, k.id_kuis
    FROM tb_materi m
    LEFT JOIN tb_video v ON m.id_materi = v.id_materi
    LEFT JOIN tb_kuis k ON m.id_materi = k.id_materi
";
if ($filter_sem === 1 || $filter_sem === 2) {
    $sql .= " WHERE m.semester = :sem";
}
$sql .= " ORDER BY m.urutan ASC";

$stmt = $pdo->prepare($sql);
if ($filter_sem === 1 || $filter_sem === 2) {
    $stmt->execute(['sem' => $filter_sem]);
} else {
    $stmt->execute();
}
$materi_list = $stmt->fetchAll();

// 2. Hitung statistik kurikulum
$total_all = $pdo->query("SELECT COUNT(*) FROM tb_materi")->fetchColumn();
$total_sem1 = $pdo->query("SELECT COUNT(*) FROM tb_materi WHERE semester = 1")->fetchColumn();
$total_sem2 = $pdo->query("SELECT COUNT(*) FROM tb_materi WHERE semester = 2")->fetchColumn();

// 3. Hitung statistik capaian belajar siswa aktif
$stmt_stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_kuis_selesai,
        AVG(skor) as rata_rata_skor,
        MAX(skor) as skor_tertinggi
    FROM tb_hasil
    WHERE id_siswa = :id_siswa
");
$stmt_stats->execute(['id_siswa' => $id_siswa]);
$student_stats = $stmt_stats->fetch();

$total_kuis_selesai = $student_stats['total_kuis_selesai'] ?? 0;
$rata_rata_skor = $student_stats['rata_rata_skor'] !== null ? round($student_stats['rata_rata_skor'], 1) : 0;

// 4. Query Leaderboard Top 5 Siswa
try {
    $stmt_lead = $pdo->query("
        SELECT h.*, s.nama_siswa, s.nis, s.kelas, k.judul_kuis
        FROM tb_hasil h
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        ORDER BY h.skor DESC, h.jumlah_benar DESC, h.waktu_selesai ASC
        LIMIT 5
    ");
    $top_leaderboard = $stmt_lead->fetchAll();
} catch (PDOException $e) {
    $top_leaderboard = [];
}

$page_title = 'Beranda Belajar Siswa';
$active_page = 'menu';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    
    <!-- Header Atas Bersih -->
    <header class="siswa-header">
        <h1>Beranda Siswa</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        
        <!-- 1. Hero Sambutan Ringkas & Elegan -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <h2 class="h4 fw-bold text-white mb-1">
                            Halo, <?= htmlspecialchars($_SESSION['siswa_nama']) ?>! 👋
                        </h2>
                        <div class="text-white-50 small">
                            Kelas <?= htmlspecialchars($_SESSION['siswa_kelas']) ?> &bull; NIS: <?= htmlspecialchars($_SESSION['siswa_nis']) ?>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#daftar-materi" class="btn btn-light text-primary fw-bold rounded-3 px-3 py-2 small shadow-sm">
                            <i class="bi bi-book me-1"></i>Materi
                        </a>
                        <a href="kuis.php" class="btn btn-outline-light fw-bold rounded-3 px-3 py-2 small">
                            <i class="bi bi-pencil-square me-1"></i>Kuis
                        </a>
                        <a href="riwayat.php" class="btn btn-outline-light fw-bold rounded-3 px-3 py-2 small">
                            <i class="bi bi-trophy me-1"></i>Nilai
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Ringkasan Metrik Singkat (4 Bento Tiles) -->
        <div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
            <div class="col">
                <div class="card stat-bento-card h-100 bg-white rounded-4 shadow-sm p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-primary-subtle text-primary">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-dark mb-0"><?= $total_all ?> Bab</div>
                            <small class="text-muted">Materi</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card stat-bento-card h-100 bg-white rounded-4 shadow-sm p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-danger-subtle text-danger">
                            <i class="bi bi-collection-play-fill"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-dark mb-0">4 Pilar</div>
                            <small class="text-muted">Multimedia</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card stat-bento-card h-100 bg-white rounded-4 shadow-sm p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-success-subtle text-success">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-dark mb-0"><?= $total_kuis_selesai ?> Sesi</div>
                            <small class="text-muted">Kuis Selesai</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card stat-bento-card h-100 bg-white rounded-4 shadow-sm p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-icon-wrapper bg-warning-subtle text-warning-emphasis">
                            <i class="bi bi-trophy-fill"></i>
                        </div>
                        <div>
                            <div class="fs-5 fw-bold text-dark mb-0"><?= $rata_rata_skor ?></div>
                            <small class="text-muted">Rata-Rata Nilai</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($top_leaderboard)): ?>
        <!-- 2.1 Widget Papan Peringkat (Leaderboard) Siswa -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-circle">
                        <i class="bi bi-trophy-fill fs-6 text-warning"></i>
                    </span>
                    <div>
                        <h3 class="h6 fw-bold text-dark mb-0">Papan Peringkat Kuis (Top 5 Siswa)</h3>
                        <small class="text-muted">Nilai evaluasi kuis tertinggi di SMP Swasta Nommensen</small>
                    </div>
                </div>
                <a href="riwayat.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                    Lihat Nilai Saya
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%; text-align: center;">Peringkat</th>
                                <th style="width: 35%;">Nama Siswa</th>
                                <th style="width: 35%;">Kuis yang Dikerjakan</th>
                                <th style="width: 20%; text-align: center;">Skor Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_leaderboard as $idx => $lead): ?>
                                <?php 
                                $pos = $idx + 1;
                                $medal = $pos === 1 ? '🥇 Juara 1' : ($pos === 2 ? '🥈 Juara 2' : ($pos === 3 ? '🥉 Juara 3' : '#' . $pos));
                                $is_me = ($lead['id_siswa'] == $id_siswa);
                                ?>
                                <tr class="<?= $is_me ? 'table-primary-subtle fw-bold' : '' ?>">
                                    <td style="text-align: center;">
                                        <span class="badge <?= $pos === 1 ? 'bg-warning text-dark' : ($pos === 2 ? 'bg-secondary text-white' : ($pos === 3 ? 'bg-danger-subtle text-danger' : 'bg-light text-secondary border')) ?> rounded-pill px-2 py-1">
                                            <?= $medal ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            <?= htmlspecialchars($lead['nama_siswa']) ?>
                                            <?php if ($is_me): ?>
                                                <span class="badge bg-primary ms-1" style="font-size: 0.7rem;">Anda</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            Kelas <?= htmlspecialchars($lead['kelas']) ?> &bull; NIS: <?= htmlspecialchars($lead['nis']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($lead['judul_kuis']) ?>">
                                            <?= htmlspecialchars($lead['judul_kuis']) ?>
                                        </div>
                                        <small class="text-muted"><?= date('d M Y - H:i', strtotime($lead['waktu_selesai'])) ?> WIB</small>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge <?= $lead['skor'] >= 70 ? 'bg-success text-white' : 'bg-primary text-white' ?> fs-6 px-3 py-1 rounded-pill">
                                            <?= $lead['skor'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- 3. Fitur Utama Pembelajaran (Katalog Ringkas & Padat) -->
        <div class="mb-4">
            <h2 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                <span>Fitur Utama</span>
            </h2>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
                
                <!-- Fitur 1: Modul Materi -->
                <div class="col">
                    <a href="#daftar-materi" class="card feature-card h-100 bg-white rounded-4 shadow-sm p-3 text-decoration-none d-flex flex-row align-items-center gap-3">
                        <div class="feature-icon-box bg-primary-subtle text-primary mb-0">
                            <i class="bi bi-journal-richtext"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark small mb-1">Modul Materi</div>
                            <div class="text-muted small text-truncate">20 bab bacaan terstruktur</div>
                        </div>
                        <i class="bi bi-chevron-right text-secondary small"></i>
                    </a>
                </div>

                <!-- Fitur 2: Video Animasi -->
                <div class="col">
                    <a href="#daftar-materi" class="card feature-card h-100 bg-white rounded-4 shadow-sm p-3 text-decoration-none d-flex flex-row align-items-center gap-3">
                        <div class="feature-icon-box bg-danger-subtle text-danger mb-0">
                            <i class="bi bi-play-btn-fill"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark small mb-1">Video Pembelajaran</div>
                            <div class="text-muted small text-truncate">Animasi percakapan situasi nyata</div>
                        </div>
                        <i class="bi bi-chevron-right text-secondary small"></i>
                    </a>
                </div>

                <!-- Fitur 3: Laboratorium Audio -->
                <div class="col">
                    <a href="#daftar-materi" class="card feature-card h-100 bg-white rounded-4 shadow-sm p-3 text-decoration-none d-flex flex-row align-items-center gap-3">
                        <div class="feature-icon-box bg-success-subtle text-success mb-0">
                            <i class="bi bi-headphones"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark small mb-1">Audio Listening</div>
                            <div class="text-muted small text-truncate">Pelafalan penutur asli</div>
                        </div>
                        <i class="bi bi-chevron-right text-secondary small"></i>
                    </a>
                </div>

                <!-- Fitur 4: Kuis Evaluasi -->
                <div class="col">
                    <a href="kuis.php" class="card feature-card h-100 bg-white rounded-4 shadow-sm p-3 text-decoration-none d-flex flex-row align-items-center gap-3">
                        <div class="feature-icon-box bg-warning-subtle text-warning-emphasis mb-0">
                            <i class="bi bi-patch-question-fill"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark small mb-1">Kuis Evaluasi</div>
                            <div class="text-muted small text-truncate">Latihan interaktif KKM 70</div>
                        </div>
                        <i class="bi bi-chevron-right text-secondary small"></i>
                    </a>
                </div>

                <!-- Fitur 5: Riwayat Nilai -->
                <div class="col">
                    <a href="riwayat.php" class="card feature-card h-100 bg-white rounded-4 shadow-sm p-3 text-decoration-none d-flex flex-row align-items-center gap-3">
                        <div class="feature-icon-box bg-info-subtle text-info-emphasis mb-0">
                            <i class="bi bi-bar-chart-line-fill"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark small mb-1">Riwayat Nilai</div>
                            <div class="text-muted small text-truncate">Rekapitulasi skor belajar</div>
                        </div>
                        <i class="bi bi-chevron-right text-secondary small"></i>
                    </a>
                </div>

                <!-- Fitur 6: Ganti Password -->
                <div class="col">
                    <a href="ganti_password.php" class="card feature-card h-100 bg-white rounded-4 shadow-sm p-3 text-decoration-none d-flex flex-row align-items-center gap-3">
                        <div class="feature-icon-box bg-secondary-subtle text-secondary-emphasis mb-0">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-bold text-dark small mb-1">Ganti Password</div>
                            <div class="text-muted small text-truncate">Keamanan akun siswa</div>
                        </div>
                        <i class="bi bi-chevron-right text-secondary small"></i>
                    </a>
                </div>

            </div>
        </div>

        <!-- 4. Katalog Materi Kurikulum (#daftar-materi) -->
        <div class="mb-4 pt-2" id="daftar-materi">
            
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <h2 class="h6 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-journal-bookmark-fill text-primary"></i>
                    <span>Daftar Materi (<?= $total_all ?>)</span>
                </h2>

                <!-- Filter Tab Semester -->
                <ul class="nav nav-pills gap-1">
                    <li class="nav-item">
                        <a href="menu.php#daftar-materi" class="nav-link rounded-pill px-3 py-1 small fw-semibold <?= $filter_sem === 0 ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                            Semua
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="menu.php?semester=1#daftar-materi" class="nav-link rounded-pill px-3 py-1 small fw-semibold <?= $filter_sem === 1 ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                            Sem 1
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="menu.php?semester=2#daftar-materi" class="nav-link rounded-pill px-3 py-1 small fw-semibold <?= $filter_sem === 2 ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                            Sem 2
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Grid 20 Bab Materi Bersih & Kompak -->
            <?php if (empty($materi_list)): ?>
                <div class="alert alert-light border border-dashed rounded-4 p-4 text-center text-muted shadow-sm">
                    Belum ada materi untuk semester ini.
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                    <?php foreach ($materi_list as $m): ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-4 bg-white p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary-subtle text-primary fw-bold rounded-pill px-2 py-1 small">
                                            BAB <?= sprintf("%02d", $m['urutan']) ?>
                                        </span>
                                        <span class="badge <?= $m['semester'] == 1 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info-emphasis' ?> rounded-pill px-2 py-1 small">
                                            Sem <?= htmlspecialchars($m['semester']) ?>
                                        </span>
                                    </div>

                                    <h3 class="h6 fw-bold text-dark mb-2" style="min-height: 2.2rem;">
                                        <?= htmlspecialchars($m['judul_materi']) ?>
                                    </h3>

                                    <!-- Indikator 4 Media Ikon Ringkas -->
                                    <div class="d-flex align-items-center gap-3 text-secondary small mb-3">
                                        <span title="Tersedia Video"><i class="bi bi-play-circle-fill text-danger me-1"></i>Video</span>
                                        <span title="Tersedia Modul"><i class="bi bi-book-half text-primary me-1"></i>Modul</span>
                                        <span title="Tersedia Audio"><i class="bi bi-volume-up-fill text-success me-1"></i>Audio</span>
                                        <span title="Tersedia Kuis"><i class="bi bi-patch-question-fill text-warning me-1"></i>Kuis</span>
                                    </div>
                                </div>

                                <a href="materi_detail.php?id=<?= $m['id_materi'] ?>" class="btn btn-primary w-100 rounded-3 fw-bold py-2 small shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <span>Buka Bab</span>
                                    <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

    </div>

<?php require_once '../includes/footer.php'; ?>
