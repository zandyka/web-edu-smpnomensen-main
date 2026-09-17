<?php
/**
 * File: siswa/materi_detail.php
 * Deskripsi: Halaman pembelajaran terpadu 4 pilar (Video Animasi, Modul Teks Guru, Audio Listening, dan Kuis).
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

// Ambil video materi
$stmt_v = $pdo->prepare("SELECT * FROM tb_video WHERE id_materi = :id LIMIT 1");
$stmt_v->execute(['id' => $id_materi]);
$video = $stmt_v->fetch();

// Ambil audio materi
$stmt_a = $pdo->prepare("SELECT * FROM tb_audio WHERE id_materi = :id LIMIT 1");
$stmt_a->execute(['id' => $id_materi]);
$audio = $stmt_a->fetch();

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
    <header class="siswa-header">
        <h1>Pembelajaran SMP Kelas 7</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="mx-auto" style="max-width: 900px;">
            
            <!-- Breadcrumb & Header Materi -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="mb-3">
                    <a href="menu.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar Materi
                    </a>
                </div>

                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary-subtle text-primary fw-bold py-2 px-3 rounded-pill">
                        BAB <?= sprintf("%02d", $materi['urutan']) ?>
                    </span>
                    <span class="badge <?= $materi['semester'] == 1 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info-emphasis' ?> py-2 px-3 rounded-pill fw-semibold">
                        Semester <?= htmlspecialchars($materi['semester']) ?>
                    </span>
                </div>

                <h1 class="h3 fw-bold text-dark mb-2"><?= htmlspecialchars($materi['judul_materi']) ?></h1>
                
                <?php if (!empty($materi['ringkasan'])): ?>
                    <p class="text-secondary mb-0 leading-relaxed">
                        <?= htmlspecialchars($materi['ringkasan']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- PILAR 1: VIDEO PEMBELAJARAN ANIMASI -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-play-btn-fill fs-3 text-danger"></i>
                    <h2 class="h5 fw-bold text-dark mb-0">1. Video Pembelajaran Animasi</h2>
                </div>

                <?php 
                $video_path = '';
                $video_exists = false;
                if ($video && !empty($video['file_video'])) {
                    $video_path = '../assets/video/' . $video['file_video'];
                    $video_exists = file_exists(__DIR__ . '/' . $video_path);
                }
                ?>

                <?php if ($video_exists): ?>
                    <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm bg-dark">
                        <video controls controlsList="nodownload" preload="metadata" class="w-100 h-100">
                            <source src="<?= htmlspecialchars($video_path) ?>" type="video/mp4">
                            Browser Anda tidak mendukung pemutar video HTML5.
                        </video>
                    </div>
                    <?php if (!empty($video['keterangan'])): ?>
                        <div class="alert alert-light border-0 py-2 px-3 mt-3 mb-0 small text-secondary">
                            <i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($video['keterangan']) ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-light border border-dashed rounded-3 p-4 text-center text-muted mb-0">
                        <i class="bi bi-camera-video-off fs-3 text-secondary d-block mb-1"></i>
                        Berkas video materi sedang disiapkan oleh guru pengampu.
                    </div>
                <?php endif; ?>
            </div>

            <!-- PILAR 2: TEKS MATERI & MODUL BACAAN GURU -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-book-half fs-3 text-primary"></i>
                    <h2 class="h5 fw-bold text-dark mb-0">2. Modul Teks &amp; Materi Pembelajaran</h2>
                </div>

                <div class="p-4 bg-light rounded-4 border-0 text-dark">
                    <?php if (!empty($materi['konten_teks'])): ?>
                        <div class="materi-html-content">
                            <?php 
                            $raw_text = trim($materi['konten_teks']);
                            // Jika guru menulis teks biasa tanpa tag HTML sama sekali, ubah baris baru secara otomatis
                            if (strip_tags($raw_text) === $raw_text && !str_contains($raw_text, '<')) {
                                echo '<p>' . nl2br(htmlspecialchars($raw_text)) . '</p>';
                            } else {
                                echo $raw_text;
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted fst-italic mb-0">Modul bacaan sedang dalam proses pengisian oleh guru.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PILAR 3: AUDIO PELAFALAN & LISTENING -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-volume-up-fill fs-3 text-success"></i>
                    <h2 class="h5 fw-bold text-dark mb-0">3. Audio Pelafalan &amp; Latihan Listening</h2>
                </div>

                <?php 
                $audio_path = '';
                $audio_exists = false;
                if ($audio && !empty($audio['file_audio'])) {
                    $audio_path = '../assets/audio/' . $audio['file_audio'];
                    $audio_exists = file_exists(__DIR__ . '/' . $audio_path);
                }
                ?>

                <div class="card bg-light border-0 rounded-3 p-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="fw-bold text-dark">
                                <?= htmlspecialchars($audio['keterangan'] ?? 'Latihan Pelafalan (Pronunciation Audio)') ?>
                            </div>
                            <div class="small text-muted">
                                Dengarkan rekaman audio pelafalan resmi untuk melatih kemampuan menyimak (listening).
                            </div>
                        </div>

                        <?php if ($audio_exists): ?>
                            <audio controls controlsList="nodownload" class="flex-shrink-0">
                                <source src="<?= htmlspecialchars($audio_path) ?>" type="audio/mpeg">
                                Browser Anda tidak mendukung pemutar audio.
                            </audio>
                        <?php else: ?>
                            <span class="badge bg-white text-muted border py-2 px-3 small">
                                <i class="bi bi-mic-mute me-1"></i>Audio disiapkan guru
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- PILAR 4: KUIS EVALUASI PEMAHAMAN -->
            <?php if ($kuis): ?>
                <div class="card border-0 shadow-sm rounded-4 bg-gradient text-white p-4 mb-4" style="background-color: var(--accent-blue);">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold mb-2">
                                PILAR 4: EVALUASI PEMBELAJARAN
                            </span>
                            <h3 class="h5 fw-bold mb-1"><?= htmlspecialchars($kuis['judul_kuis']) ?></h3>
                            <p class="small text-white-50 mb-0">
                                Uji pemahaman materi Anda dengan kuis interaktif (Waktu: <?= htmlspecialchars($kuis['waktu_pengerjaan']) ?> Menit, KKM: <?= htmlspecialchars($kuis['nilai_lulus'] ?? 70) ?> Poin).
                            </p>
                        </div>

                        <a href="kuis_kerjakan.php?id_kuis=<?= $kuis['id_kuis'] ?>" class="btn btn-warning btn-lg rounded-3 fw-bold py-2 px-4 shadow-sm text-dark flex-shrink-0 d-flex align-items-center gap-2">
                            <i class="bi bi-play-fill fs-4"></i>
                            <span>Mulai Kuis</span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

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