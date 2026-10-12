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

// 4. Query Leaderboard Siswa dengan nilai kuis tertinggi dan filter kelas
$filter_kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
if (!in_array($filter_kelas, ['VII-A', 'VII-B', 'VII-C'])) {
    $filter_kelas = '';
}

$leaderboard = [];
try {
    $sql_lead = "
        SELECT h.*, s.nama_siswa, s.nis, s.kelas, k.judul_kuis, k.kategori_materi
        FROM tb_hasil h
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        WHERE s.kelas LIKE 'VII-%'
    ";
    $p_lead = [];
    if (!empty($filter_kelas)) {
        $sql_lead .= " AND s.kelas = :kelas";
        $p_lead['kelas'] = $filter_kelas;
    }
    $sql_lead .= " ORDER BY h.skor DESC, h.jumlah_benar DESC, h.waktu_selesai ASC LIMIT 10";

    $stmt_lead = $pdo->prepare($sql_lead);
    $stmt_lead->execute($p_lead);
    $leaderboard = $stmt_lead->fetchAll();
} catch (PDOException $e) {
    $leaderboard = [];
}

// Hitung total kuis dikerjakan
$total_kuis_dikerjakan = 0;
try {
    $sql_count = "SELECT COUNT(*) FROM tb_hasil h JOIN tb_siswa s ON h.id_siswa = s.id_siswa WHERE s.kelas LIKE 'VII-%'";
    if (!empty($filter_kelas)) {
        $sql_count .= " AND s.kelas = " . $pdo->quote($filter_kelas);
    }
    $total_kuis_dikerjakan = $pdo->query($sql_count)->fetchColumn();
} catch (PDOException $e) {
    $total_kuis_dikerjakan = 0;
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

        <!-- 2.1 Widget Papan Peringkat (Leaderboard) Siswa -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden" id="leaderboard">
            <div class="card-body p-3 p-md-4">
                
                <!-- Header Leaderboard -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 pb-3 mb-3 border-bottom">
                    <div>
                        <div class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1 fw-bold mb-1">
                            <i class="bi bi-trophy-fill me-1 text-warning"></i>LEADERBOARD KELAS 7
                        </div>
                        <h2 class="h5 fw-bold text-dark mb-0">Papan Peringkat Nilai Tertinggi</h2>
                        <span class="small text-muted">Daftar siswa dengan capaian nilai kuis terbaik di SMP Swasta Nommensen</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white px-3 py-2 rounded-pill">
                            <?= $total_kuis_dikerjakan ?> Sesi Selesai
                        </span>
                        <a href="riwayat.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                            <i class="bi bi-clock-history me-1"></i>Nilai Saya
                        </a>
                    </div>
                </div>

                <!-- Filter Pemisahan Rombel: Semua, VII-A, VII-B, VII-C -->
                <?php
                $sem_param = ($filter_sem === 1 || $filter_sem === 2) ? '&semester=' . $filter_sem : '';
                ?>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="menu.php?kelas=<?= $sem_param ?>#leaderboard" class="btn btn-sm <?= empty($filter_kelas) ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                        Semua Kelas 7
                    </a>
                    <a href="menu.php?kelas=VII-A<?= $sem_param ?>#leaderboard" class="btn btn-sm <?= $filter_kelas === 'VII-A' ? 'btn-warning text-dark' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                        Kelas VII-A (7A)
                    </a>
                    <a href="menu.php?kelas=VII-B<?= $sem_param ?>#leaderboard" class="btn btn-sm <?= $filter_kelas === 'VII-B' ? 'btn-info text-dark' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                        Kelas VII-B (7B)
                    </a>
                    <a href="menu.php?kelas=VII-C<?= $sem_param ?>#leaderboard" class="btn btn-sm <?= $filter_kelas === 'VII-C' ? 'btn-success text-white' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                        Kelas VII-C (7C)
                    </a>
                </div>

                <?php if (empty($leaderboard)): ?>
                    <!-- Jika Belum Ada Siswa yang Mengerjakan Kuis -->
                    <div class="text-center py-5 my-auto text-muted">
                        <i class="bi bi-award fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                        <h3 class="h6 fw-bold text-dark">Belum Ada Riwayat Kuis <?= !empty($filter_kelas) ? "di Kelas $filter_kelas" : "" ?></h3>
                        <p class="small text-muted mb-0">Jadilah siswa pertama yang mengerjakan kuis dan memimpin papan peringkat!</p>
                    </div>
                <?php else: ?>

                    <!-- Podium Top 3 -->
                    <div class="row g-2 mb-3">
                        
                        <!-- Juara 2 (Perak) -->
                        <?php if (isset($leaderboard[1])): ?>
                            <?php $is_me_2 = ($leaderboard[1]['id_siswa'] == $id_siswa); ?>
                            <div class="col-4">
                                <div class="podium-box podium-2 h-100 d-flex flex-column justify-content-between <?= $is_me_2 ? 'border-primary shadow-sm' : '' ?>">
                                    <div>
                                        <div class="fs-4">🥈</div>
                                        <div class="fw-bold text-dark text-truncate small" title="<?= htmlspecialchars($leaderboard[1]['nama_siswa']) ?>">
                                            <?= htmlspecialchars($leaderboard[1]['nama_siswa']) ?>
                                            <?php if ($is_me_2): ?><span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Anda</span><?php endif; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            Kelas <?= htmlspecialchars($leaderboard[1]['kelas']) ?>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-secondary text-white fw-bold">
                                            <?= $leaderboard[1]['skor'] ?> Poin
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Juara 1 (Emas - Posisi Tengah Menonjol) -->
                        <?php if (isset($leaderboard[0])): ?>
                            <?php $is_me_1 = ($leaderboard[0]['id_siswa'] == $id_siswa); ?>
                            <div class="col-4">
                                <div class="podium-box podium-1 h-100 d-flex flex-column justify-content-between shadow-xs <?= $is_me_1 ? 'border-primary shadow-sm' : '' ?>">
                                    <div>
                                        <div class="fs-3">🥇</div>
                                        <div class="fw-bold text-dark text-truncate" title="<?= htmlspecialchars($leaderboard[0]['nama_siswa']) ?>">
                                            <?= htmlspecialchars($leaderboard[0]['nama_siswa']) ?>
                                            <?php if ($is_me_1): ?><span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Anda</span><?php endif; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.8rem;">
                                            Kelas <?= htmlspecialchars($leaderboard[0]['kelas']) ?>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-warning text-dark fw-bold px-3 py-2 fs-6">
                                            <?= $leaderboard[0]['skor'] ?> Poin
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Juara 3 (Perunggu) -->
                        <?php if (isset($leaderboard[2])): ?>
                            <?php $is_me_3 = ($leaderboard[2]['id_siswa'] == $id_siswa); ?>
                            <div class="col-4">
                                <div class="podium-box podium-3 h-100 d-flex flex-column justify-content-between <?= $is_me_3 ? 'border-primary shadow-sm' : '' ?>">
                                    <div>
                                        <div class="fs-4">🥉</div>
                                        <div class="fw-bold text-dark text-truncate small" title="<?= htmlspecialchars($leaderboard[2]['nama_siswa']) ?>">
                                            <?= htmlspecialchars($leaderboard[2]['nama_siswa']) ?>
                                            <?php if ($is_me_3): ?><span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Anda</span><?php endif; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            Kelas <?= htmlspecialchars($leaderboard[2]['kelas']) ?>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <span class="badge bg-danger-subtle text-danger fw-bold">
                                            <?= $leaderboard[2]['skor'] ?> Poin
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Tabel Daftar Peringkat Lengkap -->
                    <div class="table-responsive leaderboard-scroll" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 10%; text-align: center;">Rank</th>
                                    <th style="width: 38%;">Nama Siswa</th>
                                    <th style="width: 27%;">Kuis &amp; Waktu</th>
                                    <th style="width: 25%; text-align: center;">Skor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leaderboard as $idx => $row): ?>
                                    <?php 
                                    $rank = $idx + 1;
                                    $rank_class = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-other'));
                                    $medal_icon = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#' . $rank));
                                    $is_me = ($row['id_siswa'] == $id_siswa);
                                    ?>
                                    <tr class="<?= $is_me ? 'table-primary-subtle fw-bold' : '' ?>">
                                        <td style="text-align: center;">
                                            <span class="rank-badge <?= $rank_class ?>"><?= $medal_icon ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 220px;">
                                                <?= htmlspecialchars($row['nama_siswa']) ?>
                                                <?php if ($is_me): ?>
                                                    <span class="badge bg-primary ms-1" style="font-size: 0.7rem;">Anda</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                NIS: <?= htmlspecialchars($row['nis']) ?> &bull; 
                                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['kelas']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate fw-semibold text-primary" style="max-width: 220px;" title="<?= htmlspecialchars($row['judul_kuis']) ?>">
                                                <?= htmlspecialchars($row['judul_kuis']) ?>
                                            </div>
                                            <span class="text-muted" style="font-size: 0.72rem;">
                                                <i class="bi bi-clock me-1"></i><?= date('d M Y - H:i', strtotime($row['waktu_selesai'])) ?> WIB
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge <?= $row['skor'] >= 70 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> fw-bold fs-6 px-3 py-1 rounded-pill">
                                                <?= $row['skor'] ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

            </div>
        </div>


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
