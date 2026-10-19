<?php
/**
 * File: siswa/audio.php
 * Deskripsi: Halaman Khusus Laboratorium Audio Listening & Pelafalan (SMP Swasta Nommensen).
 *            Menampilkan pemutar audio aktif per bab, panduan teknik menyimak (listening),
 *            serta katalog 20 bab audio listening lengkap kurikulum kelas 7.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

// Filter semester & ID materi aktif
$filter_sem = isset($_GET['semester']) ? intval($_GET['semester']) : 0;
$active_materi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Ambil seluruh daftar audio kurikulum (20 Bab)
$sql_list = "
    SELECT a.id_audio, a.file_audio, a.keterangan, 
           m.id_materi, m.judul_materi, m.urutan, m.semester, m.ringkasan,
           k.id_kuis
    FROM tb_audio a
    JOIN tb_materi m ON a.id_materi = m.id_materi
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
$audio_list = $stmt_list->fetchAll();

// 2. Tentukan audio yang sedang aktif diputar
$active_audio = null;
if ($active_materi_id > 0) {
    foreach ($audio_list as $a) {
        if ($a['id_materi'] == $active_materi_id) {
            $active_audio = $a;
            break;
        }
    }
    // Jika tidak ditemukan di list terfilter, cari langsung di DB
    if (!$active_audio) {
        $stmt_act = $pdo->prepare("
            SELECT a.id_audio, a.file_audio, a.keterangan, 
                   m.id_materi, m.judul_materi, m.urutan, m.semester, m.ringkasan,
                   k.id_kuis
            FROM tb_audio a
            JOIN tb_materi m ON a.id_materi = m.id_materi
            LEFT JOIN tb_kuis k ON m.id_materi = k.id_materi
            WHERE m.id_materi = :id LIMIT 1
        ");
        $stmt_act->execute(['id' => $active_materi_id]);
        $active_audio = $stmt_act->fetch();
    }
}

// Jika belum ada yang aktif atau tidak valid, gunakan audio bab pertama dari list
if (!$active_audio && !empty($audio_list)) {
    $active_audio = $audio_list[0];
}

$page_title = 'Audio Listening - SMP Swasta Nommensen';
$active_page = 'audio';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Laboratorium Audio Listening</h1>
            <div class="small text-muted">Latih pelafalan (pronunciation) dan pemahaman menyimak bahasa Inggris resmi</div>
        </div>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="mx-auto" style="max-width: 1020px;">
            
            <?php if ($active_audio): ?>
                <?php 
                $audio_filename = $active_audio['file_audio'];
                $audio_path = '../assets/audio/' . $audio_filename;
                $audio_exists = !empty($audio_filename) && file_exists(__DIR__ . '/' . $audio_path);
                ?>

                <!-- 1. Pemutar Audio Aktif (Hero Audio Box) -->
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="card-header bg-light border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success text-white fw-bold px-3 py-1 rounded-pill shadow-xs">
                                <i class="bi bi-soundwave me-1"></i>SEDANG DIPUTAR
                            </span>
                            <span class="badge bg-primary text-white fw-bold px-3 py-1 rounded-pill">
                                BAB <?= sprintf("%02d", $active_audio['urutan']) ?>
                            </span>
                            <span class="badge bg-white text-secondary fw-semibold px-2 py-1 rounded-pill small border">
                                Semester <?= htmlspecialchars($active_audio['semester']) ?>
                            </span>
                        </div>
                        <div class="small text-muted fw-medium">
                            <i class="bi bi-headphones me-1 text-success"></i>Official Pronunciation Audio
                        </div>
                    </div>

                    <!-- Audio Player Box Body (High Contrast & Clear Typography) -->
                    <div class="p-4 p-md-5 text-center bg-white border-bottom">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle p-4 mb-3 shadow-xs" style="width: 80px; height: 80px;">
                            <i class="bi bi-headphones fs-1"></i>
                        </div>
                        
                        <h2 class="h4 fw-bold text-dark mb-2">
                            Bab <?= $active_audio['urutan'] ?>: <?= htmlspecialchars($active_audio['judul_materi']) ?>
                        </h2>
                        <div class="text-secondary small fw-medium mb-4">
                            <i class="bi bi-soundwave me-1 text-success"></i>
                            <?= htmlspecialchars($active_audio['keterangan'] ?? 'Latihan Pelafalan & Menyimak Bahasa Inggris') ?>
                        </div>

                        <!-- Audio Element Controls -->
                        <div class="mx-auto" style="max-width: 620px;">
                            <?php if ($audio_exists): ?>
                                <div class="p-3 bg-light rounded-4 border shadow-xs">
                                    <audio controls controlsList="nodownload" preload="metadata" class="w-100" id="mainAudioPlayer">
                                        <source src="<?= htmlspecialchars($audio_path) ?>" type="audio/mpeg">
                                        Browser Anda tidak mendukung pemutar audio HTML5.
                                    </audio>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light border border-dashed text-secondary rounded-4 p-3 small mb-0">
                                    <i class="bi bi-mic-mute fs-4 d-block mb-1 text-muted"></i>
                                    Berkas audio materi sedang disiapkan oleh guru pengampu.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Detail & Tips Listening -->
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 pb-3 border-bottom mb-3">
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Panduan Praktik Menyimak (Listening Tips):</h3>
                                <div class="text-secondary small">
                                    Dengarkan pelafalan penutur asli untuk melatih pendengaran dan artikulasi bicara Anda.
                                </div>
                            </div>
                            
                            <!-- Tombol Navigasi Cepat Antar Fitur untuk Bab Ini -->
                            <div class="d-flex flex-wrap gap-2">
                                <a href="materi_detail.php?id=<?= $active_audio['id_materi'] ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 shadow-xs" title="Buka Modul Teks Bab Ini">
                                    <i class="bi bi-book-half text-primary"></i>
                                    <span>Modul Teks</span>
                                </a>
                                <a href="video.php?id=<?= $active_audio['id_materi'] ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-2 fw-semibold d-flex align-items-center gap-1 shadow-xs" title="Tonton Video Bab Ini">
                                    <i class="bi bi-play-btn-fill text-danger"></i>
                                    <span>Video Animasi</span>
                                </a>
                                <?php if (!empty($active_audio['id_kuis'])): ?>
                                    <a href="kuis_kerjakan.php?id_kuis=<?= $active_audio['id_kuis'] ?>" class="btn btn-warning btn-sm rounded-pill px-3 py-2 fw-bold text-dark d-flex align-items-center gap-1 shadow-xs" title="Kerjakan Kuis Bab Ini">
                                        <i class="bi bi-pencil-square"></i>
                                        <span>Kuis Evaluasi</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- 3 Tips Belajar Efektif -->
                        <div class="row row-cols-1 row-cols-md-3 g-2">
                            <div class="col">
                                <div class="p-3 bg-light rounded-3 border-0 h-100">
                                    <div class="fw-bold text-dark small mb-1"><i class="bi bi-1-circle-fill text-success me-1"></i>1. Simak Penuh</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">Dengarkan seluruh audio sekali tanpa jeda untuk menangkap gambaran umum topik.</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-3 bg-light rounded-3 border-0 h-100">
                                    <div class="fw-bold text-dark small mb-1"><i class="bi bi-2-circle-fill text-success me-1"></i>2. Shadowing</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">Ulangi dan tirukan pelafalan kata-kata kunci mengikuti intonasi penutur.</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="p-3 bg-light rounded-3 border-0 h-100">
                                    <div class="fw-bold text-dark small mb-1"><i class="bi bi-3-circle-fill text-success me-1"></i>3. Evaluasi Kuis</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">Uji daya ingat dan pemahaman Anda lewat kuis evaluasi materi bab ini.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>


            <!-- 2. Katalog Seluruh Audio Pembelajaran (20 Bab Playlist) -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 pb-3 mb-3 border-bottom">
                    <div>
                        <div class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold mb-1">
                            <i class="bi bi-music-note-list me-1"></i>PLAYLIST LAB AUDIO
                        </div>
                        <h3 class="h6 fw-bold text-dark mb-0">Daftar Audio Listening (20 Bab)</h3>
                        <span class="small text-muted">Pilih bab untuk mendengarkan audio latihan pelafalannya</span>
                    </div>

                    <!-- Filter Semester -->
                    <div class="d-flex gap-1">
                        <a href="audio.php<?= $active_audio ? '?id=' . $active_audio['id_materi'] : '' ?>" class="btn btn-sm <?= $filter_sem === 0 ? 'btn-success text-white' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Semua
                        </a>
                        <a href="audio.php?semester=1<?= $active_audio ? '&id=' . $active_audio['id_materi'] : '' ?>" class="btn btn-sm <?= $filter_sem === 1 ? 'btn-success text-white' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Sem 1
                        </a>
                        <a href="audio.php?semester=2<?= $active_audio ? '&id=' . $active_audio['id_materi'] : '' ?>" class="btn btn-sm <?= $filter_sem === 2 ? 'btn-success text-white' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Sem 2
                        </a>
                    </div>
                </div>

                <!-- Grid Audio Playlist Items -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                    <?php foreach ($audio_list as $item): ?>
                        <?php 
                        $is_active = ($active_audio && $active_audio['id_materi'] == $item['id_materi']);
                        $item_file = $item['file_audio'];
                        $item_exists = !empty($item_file) && file_exists(__DIR__ . '/../assets/audio/' . $item_file);
                        ?>
                        <div class="col">
                            <div class="card h-100 rounded-4 border <?= $is_active ? 'border-2 border-success bg-white shadow-sm' : 'border-light-subtle bg-white hover-shadow' ?> p-3 d-flex flex-column justify-content-between transition-all">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge <?= $is_active ? 'bg-success text-white' : 'bg-primary-subtle text-primary' ?> fw-bold rounded-pill px-2 py-1 small">
                                            BAB <?= sprintf("%02d", $item['urutan']) ?>
                                        </span>
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 small">
                                            Sem <?= htmlspecialchars($item['semester']) ?>
                                        </span>
                                    </div>

                                    <h4 class="h6 fw-bold text-dark mb-2" style="min-height: 2.2rem;">
                                        <?= htmlspecialchars($item['judul_materi']) ?>
                                    </h4>

                                    <div class="small text-secondary mb-3 text-truncate">
                                        <i class="bi bi-volume-up me-1 text-success"></i>
                                        <?= htmlspecialchars($item['keterangan'] ?? 'Audio Listening Bab ' . $item['urutan']) ?>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <?php if ($is_active): ?>
                                        <button class="btn btn-success w-100 rounded-3 fw-bold py-2 small shadow-xs" disabled>
                                            <i class="bi bi-soundwave me-1"></i>Sedang Diputar
                                        </button>
                                    <?php else: ?>
                                        <a href="audio.php?id=<?= $item['id_materi'] ?><?= $filter_sem > 0 ? '&semester=' . $filter_sem : '' ?>" class="btn btn-outline-success w-100 rounded-3 fw-bold py-2 small shadow-xs d-flex align-items-center justify-content-center gap-1">
                                            <i class="bi bi-play-fill fs-5"></i>
                                            <span>Dengarkan Audio</span>
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
