<?php
/**
 * File: siswa/riwayat.php
 * Deskripsi: Halaman Riwayat Nilai Kuis Siswa.
 *            Menampilkan kartu ringkasan (total kuis, rata-rata, nilai terbaik)
 *            dan tabel riwayat lengkap hasil pengerjaan kuis siswa secara real-time.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

$id_siswa = $_SESSION['siswa_id'];

// 1. Ambil data statistik ringkasan
try {
    $stmt_stats = $pdo->prepare("
        SELECT 
            COUNT(*) as total_kuis, 
            AVG(skor) as rata_rata, 
            MAX(skor) as nilai_terbaik 
        FROM tb_hasil 
        WHERE id_siswa = :id_siswa
    ");
    $stmt_stats->execute(['id_siswa' => $id_siswa]);
    $stats = $stmt_stats->fetch();
    
    $total_kuis = $stats['total_kuis'] ?? 0;
    $rata_rata = $stats['rata_rata'] !== null ? round($stats['rata_rata'], 1) : 0;
    $nilai_terbaik = $stats['nilai_terbaik'] ?? 0;
} catch (PDOException $e) {
    die("Gagal mengambil data statistik: " . $e->getMessage());
}

// 2. Ambil daftar riwayat lengkap hasil kuis
try {
    $stmt_history = $pdo->prepare("
        SELECT h.*, k.judul_kuis, k.kategori_materi 
        FROM tb_hasil h
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        WHERE h.id_siswa = :id_siswa
        ORDER BY h.waktu_selesai DESC
    ");
    $stmt_history->execute(['id_siswa' => $id_siswa]);
    $history = $stmt_history->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil riwayat kuis: " . $e->getMessage());
}

$page_title = 'Riwayat Nilai Pembelajaran';
$active_page = 'riwayat';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <div>
            <h1>Riwayat & Evaluasi Nilai</h1>
            <div class="siswa-header-school">
                <span>Catatan Hasil Kuis Bahasa Inggris</span>
                &bull;
                <span>SMP Swasta Nommensen</span>
            </div>
        </div>
        <div>
            <a href="kuis.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Kerjakan Kuis Lainnya
            </a>
        </div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        
        <!-- Grid Ringkasan Statistik Nilai (Bootstrap 5) -->
        <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
            <!-- Card 1: Total Kuis Selesai -->
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold">Total Kuis Selesai</span>
                        <div class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                            <i class="bi bi-file-earmark-check fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-dark"><?= $total_kuis ?> <span class="fs-6 text-muted fw-normal">Kali</span></div>
                    <div class="text-muted small mt-1">Sesi latihan kuis selesai</div>
                </div>
            </div>

            <!-- Card 2: Rata-Rata Nilai -->
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold">Rata-Rata Nilai</span>
                        <div class="badge bg-success-subtle text-success p-2 rounded-circle">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-dark"><?= $rata_rata ?></div>
                    <div class="text-muted small mt-1">KKM Kelulusan: 70 Poin</div>
                </div>
            </div>

            <!-- Card 3: Nilai Tertinggi -->
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold">Skor Terbaik</span>
                        <div class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-circle">
                            <i class="bi bi-trophy-fill fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-dark"><?= $nilai_terbaik ?></div>
                    <div class="text-muted small mt-1">Capaian skor maksimal</div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Lengkap (Bootstrap 5 Card & Table) -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <h3 class="h5 fw-bold text-dark mb-1">
                        Tabel Rekapitulasi Hasil Kuis
                    </h3>
                    <p class="text-secondary small mb-0">Daftar lengkap pengerjaan soal latihan mandiri yang tercatat pada sistem database.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary py-2 px-3 rounded-pill fw-semibold align-self-start align-self-md-center">
                    <?= count($history) ?> Catatan
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-3">Nama Kuis</th>
                            <th class="py-3 px-3">Kategori</th>
                            <th class="py-3 px-3 text-center">Skor</th>
                            <th class="py-3 px-3 text-center">Status Kelulusan</th>
                            <th class="py-3 px-3 text-end">Waktu Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="bi bi-journal-text fs-1 text-secondary d-block mb-2"></i>
                                    <strong class="d-block">Belum ada riwayat kuis</strong>
                                    <span class="small">Anda belum pernah menyelesaikan kuis. Silakan pilih salah satu kuis pada menu untuk mulai latihan!</span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($history as $row): ?>
                                <?php
                                $is_passed = $row['skor'] >= 70;
                                $formatted_date = date('d M Y &bull; H:i', strtotime($row['waktu_selesai']));
                                ?>
                                <tr>
                                    <td class="py-3 px-3 fw-bold text-dark">
                                        <?= htmlspecialchars($row['judul_kuis']) ?>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="badge bg-light text-secondary border">
                                            <?= htmlspecialchars($row['kategori_materi'] ?? 'Semester 1') ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-center fw-bold fs-5 <?= $is_passed ? 'text-success' : 'text-danger' ?>">
                                        <?= $row['skor'] ?>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <?php if ($is_passed): ?>
                                            <span class="badge bg-success-subtle text-success-emphasis py-2 px-3 rounded-pill fw-semibold">
                                                <i class="bi bi-check-circle me-1"></i>LULUS (KKM 70)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger py-2 px-3 rounded-pill fw-semibold">
                                                <i class="bi bi-x-circle me-1"></i>BELUM LULUS
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-3 text-end text-muted small text-nowrap">
                                        <?= $formatted_date ?> WIB
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

<?php require_once '../includes/footer.php'; ?>