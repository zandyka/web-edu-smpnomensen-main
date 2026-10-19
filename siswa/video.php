<?php
/**
 * File: siswa/video.php
 * Deskripsi: Halaman Khusus Video Pembelajaran Animasi (SMP Swasta Nommensen).
 *            Menampilkan pemutar video aktif per bab, catatan video, serta katalog 20 bab video berurutan.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

// Filter semester & ID materi aktif
$filter_sem = isset($_GET['semester']) ? intval($_GET['semester']) : 0;
$active_materi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Ambil seluruh daftar video kurikulum (20 Bab)
$sql_list = "
    SELECT v.id_video, v.file_video, v.keterangan, 
           m.id_materi, m.judul_materi, m.urutan, m.semester, m.ringkasan,
           k.id_kuis
    FROM tb_video v
    JOIN tb_materi m ON v.id_materi = m.id_materi
    LEFT JOIN tb_kuis k ON m.id_materi = k.id_materi
";
if ($filter_sem === 1 || $filter_sem === 2) {
    $sql_list .= " WHERE m.semester = :sem";
}
$sql_list .= " ORDER BY m.urutan ASC";

$stmt_list = $pdo->prepare($sql_list);
if ($filter_sem === 1 || $filter_sem === 2) {
    $stmt_list->execute(['sem' => $filter_sem]);
} else {
    $stmt_list->execute();
}
$video_list = $stmt_list->fetchAll();

// 2. Tentukan video yang sedang aktif diputar
$active_video = null;
if ($active_materi_id > 0) {
    foreach ($video_list as $v) {
        if ($v['id_materi'] == $active_materi_id) {
            $active_video = $v;
            break;
        }
    }
    // Jika tidak ditemukan di list terfilter, cari langsung di DB
    if (!$active_video) {
        $stmt_act = $pdo->prepare("
            SELECT v.id_video, v.file_video, v.keterangan, 
                   m.id_materi, m.judul_materi, m.urutan, m.semester, m.ringkasan,
                   k.id_kuis
            FROM tb_video v
            JOIN tb_materi m ON v.id_materi = m.id_materi
            LEFT JOIN tb_kuis k ON m.id_materi = k.id_materi
            WHERE m.id_materi = :id LIMIT 1
        ");
        $stmt_act->execute(['id' => $active_materi_id]);
        $active_video = $stmt_act->fetch();
    }
}

// Jika belum ada yang aktif atau tidak valid, gunakan video bab pertama dari list
if (!$active_video && !empty($video_list)) {
    $active_video = $video_list[0];
}

$page_title = 'Video Pembelajaran - SMP Swasta Nommensen';
$active_page = 'video';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Video Pembelajaran Animasi</h1>
            <div class="small text-muted">Tonton simulasi dan percakapan kontekstual dalam situasi nyata</div>
        </div>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="mx-auto" style="max-width: 1020px;">
            
            <?php if ($active_video): ?>
                <?php 
                $video_filename = $active_video['file_video'];
                $video_path = '../assets/video/' . $video_filename;
                $video_exists = !empty($video_filename) && file_exists(__DIR__ . '/' . $video_path);
                ?>

                <!-- 1. Pemutar Video Aktif (Hero Cinema Box) -->
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-dark text-white p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger text-white fw-bold px-3 py-1 rounded-pill">
                                <i class="bi bi-play-circle-fill me-1"></i>SEDANG DIPUTAR
                            </span>
                            <span class="badge bg-primary text-white fw-bold px-3 py-1 rounded-pill">
                                BAB <?= sprintf("%02d", $active_video['urutan']) ?>
                            </span>
                            <span class="badge bg-secondary text-white fw-semibold px-2 py-1 rounded-pill small">
                                Semester <?= htmlspecialchars($active_video['semester']) ?>
                            </span>
                        </div>
                        <div class="small text-white-50">
                            <i class="bi bi-camera-reels me-1"></i>Video Animasi Percakapan
                        </div>
                    </div>

                    <!-- Video Container -->
                    <div class="bg-black text-center">
                        <?php if ($video_exists): ?>
                            <div class="ratio ratio-16x9">
                                <video controls controlsList="nodownload" preload="metadata" class="w-100 h-100" id="mainVideoPlayer">
                                    <source src="<?= htmlspecialchars($video_path) ?>" type="video/mp4">
                                    Browser Anda tidak mendukung pemutar video HTML5.
                                </video>
                            </div>
                        <?php else: ?>
                            <div class="p-5 text-white-50">
                                <i class="bi bi-camera-video-off fs-1 text-secondary mb-2 d-block"></i>
                                <h4 class="h6 text-white">Berkas Video Sedang Disiapkan</h4>
                                <p class="small text-secondary mb-0">Berkas video sedang diunggah oleh guru pengampu mata pelajaran.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Informasi Detail Video Aktif -->
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 pb-3 border-bottom mb-3">
                            <div>
                                <h2 class="h5 fw-bold text-dark mb-1">
                                    Bab <?= $active_video['urutan'] ?>: <?= htmlspecialchars($active_video['judul_materi']) ?>
                                </h2>
                                <div class="text-secondary small">
                                    <?= htmlspecialchars($active_video['ringkasan'] ?? 'Pelajari ekspresi, pelafalan, dan kosakata melalui video animasi ini.') ?>
                                </div>
                            </div>
                            
                            <!-- Tombol Navigasi Cepat Antar Fitur untuk Bab Ini -->
                            <div class="d-flex flex-wrap gap-2">
                                <a href="materi_detail.php?id=<?= $active_video['id_materi'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 shadow-xs" title="Buka Modul Teks Bab Ini">
                                    <i class="bi bi-book-half text-primary"></i>
                                    <span>Modul Teks</span>
                                </a>
                                <a href="audio.php?id=<?= $active_video['id_materi'] ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 shadow-xs" title="Dengarkan Audio Bab Ini">
                                    <i class="bi bi-headphones text-success"></i>
                                    <span>Audio Listening</span>
                                </a>
                                <?php if (!empty($active_video['id_kuis'])): ?>
                                    <a href="kuis_kerjakan.php?id_kuis=<?= $active_video['id_kuis'] ?>" class="btn btn-warning btn-sm rounded-pill px-3 py-2 fw-bold text-dark d-flex align-items-center gap-1 shadow-xs" title="Kerjakan Kuis Bab Ini">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Kuis Evaluasi</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (!empty($active_video['keterangan'])): ?>
                            <div class="alert alert-light border rounded-3 p-3 mb-0 small text-secondary d-flex align-items-start gap-2">
                                <i class="bi bi-info-circle-fill text-primary fs-5 mt-n1"></i>
                                <div>
                                    <strong class="text-dark">Petunjuk &amp; Catatan Video:</strong><br>
                                    <?= htmlspecialchars($active_video['keterangan']) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php endif; ?>


            <!-- 2. Katalog Seluruh Video Pembelajaran (20 Bab Playlist) -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 pb-3 mb-3 border-bottom">
                    <div>
                        <div class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-bold mb-1">
                            <i class="bi bi-collection-play-fill me-1"></i>PLAYLIST LENGKAP
                        </div>
                        <h3 class="h6 fw-bold text-dark mb-0">Daftar Video Pembelajaran (20 Bab)</h3>
                        <span class="small text-muted">Pilih bab untuk menonton video animasi pembelajarannya</span>
                    </div>

                    <!-- Filter Semester -->
                    <div class="d-flex gap-1">
                        <a href="video.php<?= $active_video ? '?id=' . $active_video['id_materi'] : '' ?>" class="btn btn-sm <?= $filter_sem === 0 ? 'btn-danger text-white' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Semua
                        </a>
                        <a href="video.php?semester=1<?= $active_video ? '&id=' . $active_video['id_materi'] : '' ?>" class="btn btn-sm <?= $filter_sem === 1 ? 'btn-danger text-white' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Sem 1
                        </a>
                        <a href="video.php?semester=2<?= $active_video ? '&id=' . $active_video['id_materi'] : '' ?>" class="btn btn-sm <?= $filter_sem === 2 ? 'btn-danger text-white' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Sem 2
                        </a>
                    </div>
                </div>

                <!-- Grid Video Playlist Items -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                    <?php foreach ($video_list as $item): ?>
                        <?php 
                        $is_playing = ($active_video && $active_video['id_materi'] == $item['id_materi']);
                        $item_file = $item['file_video'];
                        $item_exists = !empty($item_file) && file_exists(__DIR__ . '/../assets/video/' . $item_file);
                        ?>
                        <div class="col">
                            <div class="card h-100 rounded-4 border <?= $is_playing ? 'border-2 border-danger bg-danger-subtle shadow-sm' : 'border-light-subtle bg-white hover-shadow' ?> p-3 d-flex flex-column justify-content-between transition-all">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge <?= $is_playing ? 'bg-danger text-white' : 'bg-primary-subtle text-primary' ?> fw-bold rounded-pill px-2 py-1 small">
                                            BAB <?= sprintf("%02d", $item['urutan']) ?>
                                        </span>
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small">
                                            Sem <?= htmlspecialchars($item['semester']) ?>
                                        </span>
                                    </div>

                                    <h4 class="h6 fw-bold text-dark mb-2" style="min-height: 2.2rem;">
                                        <?= htmlspecialchars($item['judul_materi']) ?>
                                    </h4>

                                    <div class="small text-muted mb-3 text-truncate">
                                        <i class="bi bi-file-earmark-play me-1 text-danger"></i>
                                        <?= htmlspecialchars($item['keterangan'] ?? 'Video Animasi Bab ' . $item['urutan']) ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <?php if ($is_playing): ?>
                                        <button class="btn btn-danger w-100 rounded-3 fw-bold py-2 small shadow-xs" disabled>
                                            <i class="bi bi-play-circle-fill me-1"></i>Sedang Ditonton
                                        </button>
                                    <?php else: ?>
                                        <a href="video.php?id=<?= $item['id_materi'] ?><?= $filter_sem > 0 ? '&semester=' . $filter_sem : '' ?>" class="btn btn-outline-danger w-100 rounded-3 fw-bold py-2 small shadow-xs d-flex align-items-center justify-content-center gap-1">
                                            <i class="bi bi-play-fill fs-5"></i>
                                            <span>Tonton Video</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

        </div>
    </div>

<?php require_once '../includes/footer.php'; ?>
