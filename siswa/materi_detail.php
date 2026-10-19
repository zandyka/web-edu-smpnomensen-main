<?php
/**
 * File: siswa/materi_detail.php
 * Deskripsi: Halaman Khusus Modul Teks & Bacaan Materi Pembelajaran (SMP Swasta Nommensen).
 *            Fokus pada teks bacaan terstruktur, penjelasan konsep, dan tautan langsung
 *            ke halaman terpisah untuk Video Pembelajaran, Audio Listening, dan Kuis Evaluasi.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

$id_materi = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_materi <= 0) {
    header("Location: menu.php");
    exit();
}

// Ambil data materi
$stmt = $pdo->prepare("SELECT * FROM tb_materi WHERE id_materi = :id LIMIT 1");
$stmt->execute(['id' => $id_materi]);
$materi = $stmt->fetch();

if (!$materi) {
    header("Location: menu.php");
    exit();
}

// Cek ketersediaan video materi
$stmt_v = $pdo->prepare("SELECT id_video FROM tb_video WHERE id_materi = :id LIMIT 1");
$stmt_v->execute(['id' => $id_materi]);
$has_video = (bool) $stmt_v->fetch();

// Cek ketersediaan audio materi
$stmt_a = $pdo->prepare("SELECT id_audio FROM tb_audio WHERE id_materi = :id LIMIT 1");
$stmt_a->execute(['id' => $id_materi]);
$has_audio = (bool) $stmt_a->fetch();

// Ambil kuis terkait materi ini
$stmt_k = $pdo->prepare("SELECT * FROM tb_kuis WHERE id_materi = :id LIMIT 1");
$stmt_k->execute(['id' => $id_materi]);
$kuis = $stmt_k->fetch();

// Cari Bab Sebelumnya dan Bab Selanjutnya berdasarkan urutan
$stmt_prev = $pdo->prepare("SELECT id_materi, urutan, judul_materi FROM tb_materi WHERE urutan < :urutan ORDER BY urutan DESC LIMIT 1");
$stmt_prev->execute(['urutan' => $materi['urutan']]);
$prev_materi = $stmt_prev->fetch();

$stmt_next = $pdo->prepare("SELECT id_materi, urutan, judul_materi FROM tb_materi WHERE urutan > :urutan ORDER BY urutan ASC LIMIT 1");
$stmt_next->execute(['urutan' => $materi['urutan']]);
$next_materi = $stmt_next->fetch();

$page_title = 'Bab ' . $materi['urutan'] . ': ' . $materi['judul_materi'];
$active_page = 'menu';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Modul Materi Pembelajaran</h1>
            <div class="small text-muted">Baca dan pelajari materi teks bahasa Inggris secara mendalam</div>
        </div>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="mx-auto" style="max-width: 920px;">
            
            <!-- Breadcrumb & Header Materi -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <a href="menu.php#daftar-materi" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Materi
                    </a>

                    <!-- Navigasi Pintas ke Fitur Terpisah Lainnya -->
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($has_video): ?>
                            <a href="video.php?id=<?= $materi['id_materi'] ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold d-flex align-items-center gap-1 shadow-xs">
                                <i class="bi bi-play-circle-fill"></i>
                                <span>Tonton Video Bab Ini</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($has_audio): ?>
                            <a href="audio.php?id=<?= $materi['id_materi'] ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold d-flex align-items-center gap-1 shadow-xs">
                                <i class="bi bi-headphones"></i>
                                <span>Dengar Audio Bab Ini</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($kuis): ?>
                            <a href="kuis_kerjakan.php?id_kuis=<?= $kuis['id_kuis'] ?>" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold text-dark d-flex align-items-center gap-1 shadow-xs">
                                <i class="bi bi-pencil-square"></i>
                                <span>Kerjakan Kuis</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary-subtle text-primary fw-bold py-2 px-3 rounded-pill">
                        BAB <?= sprintf("%02d", $materi['urutan']) ?>
                    </span>
                    <span class="badge <?= $materi['semester'] == 1 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info-emphasis' ?> py-2 px-3 rounded-pill fw-semibold">
                        Semester <?= htmlspecialchars($materi['semester']) ?>
                    </span>
                    <span class="badge bg-light text-muted border py-2 px-3 rounded-pill fw-semibold">
                        <i class="bi bi-journal-text me-1 text-primary"></i>Modul Bacaan
                    </span>
                </div>

                <h1 class="h3 fw-bold text-dark mb-2"><?= htmlspecialchars($materi['judul_materi']) ?></h1>
                
                <?php if (!empty($materi['ringkasan'])): ?>
                    <p class="text-secondary mb-0 leading-relaxed">
                        <?= htmlspecialchars($materi['ringkasan']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- MODUL TEKS & BACAAN GURU (Fokus Bacaan) -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-4">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="feature-icon-box bg-primary-subtle text-primary mb-0" style="width: 42px; height: 42px; font-size: 1.25rem;">
                            <i class="bi bi-book-half"></i>
                        </div>
                        <div>
                            <h2 class="h5 fw-bold text-dark mb-0">Isi Modul Pembelajaran</h2>
                            <small class="text-muted">Materi resmi mata pelajaran Bahasa Inggris Kelas 7</small>
                        </div>
                    </div>
                </div>

                <div class="text-dark leading-relaxed" style="font-size: 1.05rem;">
                    <?php if (!empty($materi['konten_teks'])): ?>
                        <div class="materi-html-content">
                            <?php 
                            $raw_text = trim($materi['konten_teks']);
                            // Jika teks polos tanpa tag HTML, ubah newline ke <p> & <br>
                            if (strip_tags($raw_text) === $raw_text && !str_contains($raw_text, '<')) {
                                echo '<p>' . nl2br(htmlspecialchars($raw_text)) . '</p>';
                            } else {
                                echo $raw_text;
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border border-dashed rounded-3 p-4 text-center text-muted mb-0">
                            <i class="bi bi-file-earmark-text fs-3 text-secondary d-block mb-1"></i>
                            Modul bacaan sedang dalam proses pengisian oleh guru pengampu.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- CARD AKTIVITAS PENDUKUNG (Video, Audio & Kuis Terpisah) -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h3 class="h6 fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-stars text-warning"></i>
                    <span>Aktivitas Pendukung Bab <?= $materi['urutan'] ?></span>
                </h3>

                <div class="row row-cols-1 row-cols-md-3 g-3">
                    
                    <!-- Kartu Video -->
                    <div class="col">
                        <div class="card h-100 rounded-4 border border-light-subtle bg-light p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-danger text-white rounded-pill p-1 px-2 small">
                                        <i class="bi bi-play-circle-fill me-1"></i>Video
                                    </span>
                                </div>
                                <h4 class="h6 fw-bold text-dark mb-1">Video Pembelajaran</h4>
                                <p class="small text-muted mb-3">Tonton video animasi situasi nyata untuk Bab <?= $materi['urutan'] ?>.</p>
                            </div>
                            <a href="video.php?id=<?= $materi['id_materi'] ?>" class="btn btn-outline-danger btn-sm rounded-3 fw-bold w-100 py-2">
                                <i class="bi bi-play-fill me-1"></i>Tonton Video
                            </a>
                        </div>
                    </div>

                    <!-- Kartu Audio -->
                    <div class="col">
                        <div class="card h-100 rounded-4 border border-light-subtle bg-light p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-success text-white rounded-pill p-1 px-2 small">
                                        <i class="bi bi-headphones me-1"></i>Audio
                                    </span>
                                </div>
                                <h4 class="h6 fw-bold text-dark mb-1">Audio Listening</h4>
                                <p class="small text-muted mb-3">Dengar pelafalan resmi dari penutur asli untuk Bab <?= $materi['urutan'] ?>.</p>
                            </div>
                            <a href="audio.php?id=<?= $materi['id_materi'] ?>" class="btn btn-outline-success btn-sm rounded-3 fw-bold w-100 py-2">
                                <i class="bi bi-soundwave me-1"></i>Dengar Audio
                            </a>
                        </div>
                    </div>

                    <!-- Kartu Kuis -->
                    <div class="col">
                        <div class="card h-100 rounded-4 border border-light-subtle bg-light p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-warning text-dark rounded-pill p-1 px-2 small fw-bold">
                                        <i class="bi bi-patch-question-fill me-1"></i>Kuis
                                    </span>
                                </div>
                                <h4 class="h6 fw-bold text-dark mb-1">Kuis Evaluasi</h4>
                                <p class="small text-muted mb-3">Uji pemahaman materi Anda dengan kuis interaktif Bab <?= $materi['urutan'] ?>.</p>
                            </div>
                            <?php if ($kuis): ?>
                                <a href="kuis_kerjakan.php?id_kuis=<?= $kuis['id_kuis'] ?>" class="btn btn-warning btn-sm rounded-3 fw-bold w-100 py-2 text-dark">
                                    <i class="bi bi-pencil-square me-1"></i>Mulai Kuis
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm rounded-3 fw-bold w-100 py-2" disabled>
                                    Kuis Disiapkan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Navigasi Bab Sebelumnya & Selanjutnya -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 pb-4">
                <?php if ($prev_materi): ?>
                    <a href="materi_detail.php?id=<?= $prev_materi['id_materi'] ?>" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i>Bab <?= $prev_materi['urutan'] ?>: <?= htmlspecialchars($prev_materi['judul_materi']) ?>
                    </a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>

                <?php if ($next_materi): ?>
                    <a href="materi_detail.php?id=<?= $next_materi['id_materi'] ?>" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold">
                        Bab <?= $next_materi['urutan'] ?>: <?= htmlspecialchars($next_materi['judul_materi']) ?><i class="bi bi-arrow-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php require_once '../includes/footer.php'; ?>