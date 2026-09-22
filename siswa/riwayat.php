<?php
/**
 * File: siswa/riwayat.php
 * Deskripsi: Halaman Riwayat & Raport Evaluasi Siswa.
 *            Menampilkan filter periode (Semua, Mingguan, Bulanan),
 *            kartu capaian statistik & predikat belajar, tabel hasil kuis,
 *            serta tombol Cetak Raport Resmi (PDF) berstandar A4 sekolah.
 */

require_once '../includes/auth_siswa.php';
require_once '../config.php';

$id_siswa = $_SESSION['siswa_id'];

// Filter periode
$tipe_periode = isset($_GET['tipe_periode']) ? $_GET['tipe_periode'] : 'semua';
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : intval(date('m'));
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));
$tgl_mulai = isset($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : date('Y-m-d', strtotime('-7 days'));
$tgl_selesai = isset($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : date('Y-m-d');

$nama_bulan_arr = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Menyusun WHERE clause berdasarkan periode
$where_clause = " WHERE h.id_siswa = :id_siswa";
$params = ['id_siswa' => $id_siswa];
$periode_label = "Seluruh Riwayat Pembelajaran";

if ($tipe_periode === 'mingguan' && !empty($tgl_mulai) && !empty($tgl_selesai)) {
    $where_clause .= " AND DATE(h.waktu_selesai) BETWEEN :start_date AND :end_date";
    $params['start_date'] = $tgl_mulai;
    $params['end_date'] = $tgl_selesai;
    $periode_label = "Mingguan (" . date('d M Y', strtotime($tgl_mulai)) . " s.d. " . date('d M Y', strtotime($tgl_selesai)) . ")";
} elseif ($tipe_periode === 'bulanan' && $bulan > 0 && $tahun > 0) {
    $where_clause .= " AND MONTH(h.waktu_selesai) = :bulan AND YEAR(h.waktu_selesai) = :tahun";
    $params['bulan'] = $bulan;
    $params['tahun'] = $tahun;
    $periode_label = "Bulanan (" . ($nama_bulan_arr[$bulan] ?? $bulan) . " " . $tahun . ")";
}

// 1. Ambil data statistik ringkasan sesuai filter
try {
    $sql_stats = "
        SELECT 
            COUNT(*) as total_kuis, 
            AVG(h.skor) as rata_rata, 
            MAX(h.skor) as nilai_terbaik,
            SUM(CASE WHEN h.skor >= 70 THEN 1 ELSE 0 END) as total_lulus
        FROM tb_hasil h
        {$where_clause}
    ";
    $stmt_stats = $pdo->prepare($sql_stats);
    $stmt_stats->execute($params);
    $stats = $stmt_stats->fetch();
    
    $total_kuis = $stats['total_kuis'] ?? 0;
    $rata_rata = $stats['rata_rata'] !== null ? round($stats['rata_rata'], 1) : 0;
    $nilai_terbaik = $stats['nilai_terbaik'] ?? 0;
    $total_lulus = $stats['total_lulus'] ?? 0;

    // Predikat
    if ($total_kuis === 0) {
        $predikat = '-';
        $predikat_label = 'Belum Ada Data';
    } elseif ($rata_rata >= 85) {
        $predikat = 'A';
        $predikat_label = 'Sangat Baik';
    } elseif ($rata_rata >= 75) {
        $predikat = 'B';
        $predikat_label = 'Baik';
    } elseif ($rata_rata >= 70) {
        $predikat = 'C';
        $predikat_label = 'Cukup';
    } else {
        $predikat = 'D';
        $predikat_label = 'Perlu Bimbingan';
    }
} catch (PDOException $e) {
    die("Gagal mengambil data statistik: " . $e->getMessage());
}

// 2. Ambil daftar riwayat kuis sesuai filter
try {
    $sql_history = "
        SELECT h.*, k.judul_kuis, k.kategori_materi 
        FROM tb_hasil h
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        {$where_clause}
        ORDER BY h.waktu_selesai DESC
    ";
    $stmt_history = $pdo->prepare($sql_history);
    $stmt_history->execute($params);
    $history = $stmt_history->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil riwayat kuis: " . $e->getMessage());
}

// URL cetak raport PDF
$cetak_url = "cetak_raport.php?tipe_periode={$tipe_periode}&bulan={$bulan}&tahun={$tahun}&tgl_mulai={$tgl_mulai}&tgl_selesai={$tgl_selesai}";

$page_title = 'Riwayat & Raport Nilai';
$active_page = 'riwayat';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header">
        <div>
            <h1>Riwayat &amp; Raport Nilai</h1>
            <div class="siswa-header-school">
                <span>Catatan Hasil Belajar Bahasa Inggris</span>
                &bull;
                <span>SMP Swasta Nommensen</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $cetak_url ?>" target="_blank" class="btn btn-warning fw-bold d-inline-flex align-items-center gap-2 shadow-sm text-dark px-3 py-2" style="font-size: 0.9rem;">
                <i class="bi bi-printer-fill fs-6"></i>
                <span>Cetak Raport (PDF)</span>
            </a>
            <a href="kuis.php" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2" style="font-size: 0.9rem;">
                <i class="bi bi-play-circle-fill"></i>
                <span>Kerjakan Kuis</span>
            </a>
        </div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        
        <!-- Filter Periode Laporan / Raport -->
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
            <form action="riwayat.php" method="GET" class="row g-3 align-items-end" id="filterPeriodeForm">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark small">Pilih Periode Raport:</label>
                    <select name="tipe_periode" id="tipePeriodeSelect" class="form-select" onchange="toggleFilterInputs()">
                        <option value="semua" <?= $tipe_periode === 'semua' ? 'selected' : '' ?>>Semua Riwayat (Lengkap)</option>
                        <option value="bulanan" <?= $tipe_periode === 'bulanan' ? 'selected' : '' ?>>Bulanan (Satu Bulan)</option>
                        <option value="mingguan" <?= $tipe_periode === 'mingguan' ? 'selected' : '' ?>>Mingguan (Rentang Tanggal)</option>
                    </select>
                </div>

                <!-- Input Khusus Bulanan -->
                <div class="col-md-4" id="groupBulanan" style="<?= $tipe_periode === 'bulanan' ? '' : 'display:none;' ?>">
                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label fw-bold text-secondary small">Bulan:</label>
                            <select name="bulan" class="form-select">
                                <?php foreach ($nama_bulan_arr as $num => $nama): ?>
                                    <option value="<?= $num ?>" <?= $bulan === $num ? 'selected' : '' ?>><?= $nama ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label fw-bold text-secondary small">Tahun:</label>
                            <select name="tahun" class="form-select">
                                <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                                    <option value="<?= $y ?>" <?= $tahun === $y ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Input Khusus Mingguan -->
                <div class="col-md-4" id="groupMingguan" style="<?= $tipe_periode === 'mingguan' ? '' : 'display:none;' ?>">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Tanggal Mulai:</label>
                            <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-secondary small">Tanggal Selesai:</label>
                            <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tgl_selesai) ?>">
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary fw-bold w-100 py-2">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                </div>

                <div class="col-md-3 ms-auto text-md-end">
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                        <i class="bi bi-calendar-event me-1"></i> <?= htmlspecialchars($periode_label) ?>
                    </span>
                </div>
            </form>
        </div>

        <!-- Grid Ringkasan Statistik Nilai (Bootstrap 5) -->
        <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
            <!-- Card 1: Total Kuis Selesai -->
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold">Total Kuis</span>
                        <div class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                            <i class="bi bi-file-earmark-check fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-dark"><?= $total_kuis ?> <span class="fs-6 text-muted fw-normal">Sesi</span></div>
                    <div class="text-muted small mt-1">Latihan kuis diselesaikan</div>
                </div>
            </div>

            <!-- Card 2: Rata-Rata Nilai -->
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold">Rata-Rata Nilai</span>
                        <div class="badge bg-success-subtle text-success p-2 rounded-circle">
                            <i class="bi bi-graph-up-arrow fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold <?= $rata_rata >= 70 ? 'text-success' : 'text-danger' ?>"><?= $rata_rata ?></div>
                    <div class="text-muted small mt-1">Standar KKM: 70 Poin</div>
                </div>
            </div>

            <!-- Card 3: Nilai Tertinggi -->
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold">Skor Terbaik</span>
                        <div class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-circle">
                            <i class="bi bi-trophy-fill fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-dark"><?= $nilai_terbaik ?></div>
                    <div class="text-muted small mt-1">Capaian skor tertinggi</div>
                </div>
            </div>

            <!-- Card 4: Predikat Akademik -->
            <div class="col">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white h-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold">Predikat</span>
                        <div class="badge bg-info-subtle text-info p-2 rounded-circle">
                            <i class="bi bi-award-fill fs-5"></i>
                        </div>
                    </div>
                    <div class="fs-2 fw-bold text-dark"><?= $predikat ?></div>
                    <div class="text-muted small mt-1 text-truncate" title="<?= $predikat_label ?>"><?= $predikat_label ?></div>
                </div>
            </div>
        </div>

        <!-- Banner Unduh PDF Raport -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 mb-4" style="border-left: 5px solid #2563eb !important;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
                        Raport Belajar Resmi SMP Swasta Nommensen
                    </h5>
                    <p class="text-secondary small mb-0">
                        Cetak lembar raport resmi berformat A4 lengkap dengan Kop Sekolah, identitas siswa, nilai per kuis, serta tanda tangan guru &amp; kepala sekolah untuk periode terpilih (<strong><?= htmlspecialchars($periode_label) ?></strong>).
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="<?= $cetak_url ?>" target="_blank" class="btn btn-primary fw-bold px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                        <i class="bi bi-printer-fill fs-5"></i>
                        <span>Cetak / Simpan PDF</span>
                    </a>
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
                    <p class="text-secondary small mb-0">
                        Daftar lengkap pengerjaan soal latihan pada periode: <strong><?= htmlspecialchars($periode_label) ?></strong>.
                    </p>
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
                            <th class="py-3 px-3 text-center">Detail Jawaban</th>
                            <th class="py-3 px-3 text-center">Status Kelulusan</th>
                            <th class="py-3 px-3 text-end">Waktu Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-journal-text fs-1 text-secondary d-block mb-2"></i>
                                    <strong class="d-block">Belum ada riwayat kuis pada periode ini</strong>
                                    <span class="small">Tidak ada data pengerjaan kuis yang tercatat untuk filter ini. Silakan ubah filter atau kerjakan kuis baru.</span>
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
                                    <td class="py-3 px-3 text-center small fw-semibold">
                                        <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i><?= $row['jumlah_benar'] ?></span> / 
                                        <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i><?= $row['jumlah_salah'] ?></span>
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

    <script>
        function toggleFilterInputs() {
            var tipe = document.getElementById('tipePeriodeSelect').value;
            var groupBulanan = document.getElementById('groupBulanan');
            var groupMingguan = document.getElementById('groupMingguan');

            if (groupBulanan && groupMingguan) {
                if (tipe === 'bulanan') {
                    groupBulanan.style.display = 'block';
                    groupMingguan.style.display = 'none';
                } else if (tipe === 'mingguan') {
                    groupBulanan.style.display = 'none';
                    groupMingguan.style.display = 'block';
                } else {
                    groupBulanan.style.display = 'none';
                    groupMingguan.style.display = 'none';
                }
            }
        }
    </script>

<?php require_once '../includes/footer.php'; ?>