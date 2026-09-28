<?php
/**
 * File: siswa/kuis.php
 * Deskripsi: Halaman Daftar Kuis Evaluasi Pembelajaran Bahasa Inggris SMP Kelas 7.
 *            Dilengkapi penanda tanggal pengerjaan kuis hari ini, status riwayat pengerjaan,
 *            dan filter semester.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

$id_siswa = $_SESSION['siswa_id'];
$filter_sem = isset($_GET['semester']) ? intval($_GET['semester']) : 0;
$filter_status = isset($_GET['status_pengerjaan']) ? trim($_GET['status_pengerjaan']) : '';

// 1. Ambil data kuis
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

// 2. Ambil riwayat pengerjaan kuis milik siswa ini
$my_quiz_map = [];
try {
    $stmt_my = $pdo->prepare("
        SELECT id_hasil, id_kuis, skor, waktu_selesai,
               (DATE(waktu_selesai) = CURRENT_DATE()) as is_today
        FROM tb_hasil
        WHERE id_siswa = :id_siswa
        ORDER BY waktu_selesai DESC
    ");
    $stmt_my->execute(['id_siswa' => $id_siswa]);
    $my_attempts = $stmt_my->fetchAll();

    foreach ($my_attempts as $att) {
        $qid = $att['id_kuis'];
        if (!isset($my_quiz_map[$qid])) {
            $my_quiz_map[$qid] = $att;
        }
    }
} catch (PDOException $e) {
    $my_quiz_map = [];
}

// Filter berdasarkan status pengerjaan jika dipilih
if ($filter_status === 'today') {
    $quizzes = array_filter($quizzes, function($q) use ($my_quiz_map) {
        return isset($my_quiz_map[$q['id_kuis']]) && !empty($my_quiz_map[$q['id_kuis']]['is_today']);
    });
} elseif ($filter_status === 'undone') {
    $quizzes = array_filter($quizzes, function($q) use ($my_quiz_map) {
        return !isset($my_quiz_map[$q['id_kuis']]);
    });
}

// Format tanggal hari ini dalam Bahasa Indonesia
$hari_arr = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$bulan_arr = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

$hari_ini = $hari_arr[date('l')] ?? date('l');
$tanggal_indo = $hari_ini . ', ' . date('d') . ' ' . ($bulan_arr[intval(date('m'))] ?? date('F')) . ' ' . date('Y');

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
        
        <!-- Banner Agenda Pengerjaan Kuis Hari Ini -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 mb-4" style="border-left: 5px solid #2563eb !important;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary px-3 py-1 rounded-pill fw-bold">
                            <i class="bi bi-calendar-check-fill me-1"></i> AGENDA HARI INI
                        </span>
                        <span class="text-secondary small">&bull;</span>
                        <span class="text-dark fw-bold small"><?= $tanggal_indo ?></span>
                    </div>
                    <h2 class="h5 fw-bold text-dark mb-1">Jadwal Pengerjaan Kuis Mandiri Siswa</h2>
                    <p class="text-secondary small mb-0">
                        Kerjakan kuis latihan bab di bawah ini hari ini. Hasil pengerjaan kuis Anda hari ini otomatis tercatat dengan tanggal &amp; jam lengkap ke dalam Raport Digital Anda.
                    </p>
                </div>
                <div class="flex-shrink-0 text-md-end">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-3 small">
                        <i class="bi bi-clock-history me-1 text-primary"></i> Waktu Sekarang: <strong><?= date('H:i') ?> WIB</strong>
                    </span>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'completed'): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 py-3 px-4 rounded-4 shadow-sm border-0 mb-4" role="alert">
                <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                <div>
                    <strong>Kuis Selesai!</strong> Jawaban Anda berhasil dikirim dan tersimpan dengan timestamp tanggal &amp; jam hari ini.
                </div>
            </div>
        <?php endif; ?>

        <!-- Filter Tab (Semester & Status Hari Ini) -->
        <div class="d-flex overflow-auto pb-2 mb-4">
            <ul class="nav nav-pills gap-2 flex-nowrap flex-md-wrap">
                <li class="nav-item">
                    <a href="kuis.php" class="nav-link rounded-pill px-3 py-2 fw-semibold <?= $filter_sem === 0 && empty($filter_status) ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                        Semua Kuis
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kuis.php?status_pengerjaan=today" class="nav-link rounded-pill px-3 py-2 fw-semibold <?= $filter_status === 'today' ? 'active bg-success text-white' : 'bg-white text-secondary shadow-sm' ?>">
                        <i class="bi bi-check-circle-fill me-1"></i> Dikerjakan Hari Ini
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kuis.php?status_pengerjaan=undone" class="nav-link rounded-pill px-3 py-2 fw-semibold <?= $filter_status === 'undone' ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                        Belum Dikerjakan
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kuis.php?semester=1" class="nav-link rounded-pill px-3 py-2 fw-semibold <?= $filter_sem === 1 ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                        Semester 1 (Bab 1 - 9)
                    </a>
                </li>
                <li class="nav-item">
                    <a href="kuis.php?semester=2" class="nav-link rounded-pill px-3 py-2 fw-semibold <?= $filter_sem === 2 ? 'active' : 'bg-white text-secondary shadow-sm' ?>">
                        Semester 2 (Bab 10 - 20)
                    </a>
                </li>
            </ul>
        </div>

        <?php if (empty($quizzes)): ?>
            <div class="alert alert-light border border-dashed rounded-4 p-5 text-center text-muted shadow-sm bg-white">
                <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary"></i>
                Tidak ada kuis yang cocok dengan filter yang dipilih.
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php foreach ($quizzes as $quiz): ?>
                    <?php
                    // Hitung jumlah butir soal asli
                    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM tb_soal WHERE id_kuis = :id");
                    $stmt_count->execute(['id' => $quiz['id_kuis']]);
                    $total_soal = $stmt_count->fetchColumn();

                    $my_attempt = $my_quiz_map[$quiz['id_kuis']] ?? null;
                    $is_done_today = $my_attempt && !empty($my_attempt['is_today']);
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
                                
                                <!-- INDIKATOR TANGGAL PENGERJAAN KUIS -->
                                <?php if ($my_attempt): ?>
                                    <?php if ($is_done_today): ?>
                                        <div class="alert alert-success py-2 px-3 mb-3 rounded-3 small fw-bold d-flex align-items-center justify-content-between border-0 shadow-xs">
                                            <span>
                                                <i class="bi bi-check-circle-fill me-1 text-success"></i>
                                                Dikerjakan Hari Ini (<?= date('H:i', strtotime($my_attempt['waktu_selesai'])) ?> WIB)
                                            </span>
                                            <span class="badge bg-success">Skor: <?= $my_attempt['skor'] ?></span>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-light border py-2 px-3 mb-3 rounded-3 small text-muted d-flex align-items-center justify-content-between">
                                            <span>
                                                <i class="bi bi-clock-history me-1 text-primary"></i>
                                                Dikerjakan: <?= date('d M Y', strtotime($my_attempt['waktu_selesai'])) ?>
                                            </span>
                                            <span class="badge bg-secondary">Skor: <?= $my_attempt['skor'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="badge bg-light text-secondary border py-2 px-3 mb-3 rounded-3 small fw-semibold text-start d-block">
                                        <i class="bi bi-circle me-1 text-primary"></i> Belum Dikerjakan &bull; Yuk kerjakan hari ini!
                                    </div>
                                <?php endif; ?>

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
                                <a href="kuis_kerjakan.php?id_kuis=<?= $quiz['id_kuis'] ?>" class="btn <?= $is_done_today ? 'btn-outline-primary' : 'btn-primary' ?> w-100 rounded-3 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-play-fill fs-5"></i>
                                    <span><?= $my_attempt ? 'Kerjakan Ulang Hari Ini' : 'Kerjakan Kuis Sekarang' ?></span>
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