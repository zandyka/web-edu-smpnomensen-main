<?php
/**
 * File: admin/laporan_nilai.php
 * Deskripsi: Halaman Laporan Hasil Nilai & Raport Evaluasi Siswa.
 *            Dilengkapi dengan 2 Tab:
 *            1. Log Seluruh Nilai (Rekap lengkap kuis dengan filter per siswa/kuis)
 *            2. Raport Siswa (Filter Mingguan/Bulanan/Semua dengan pratinjau & generate PDF resmi)
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';

// Memanggil koneksi database
require_once '../config.php';

// Tab aktif (log atau raport)
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'raport' ? 'raport' : 'log';

// --- DATA FILTER TAB 1: LOG NILAI ---
$filter_siswa = isset($_GET['id_siswa']) ? intval($_GET['id_siswa']) : 0;
$filter_kuis = isset($_GET['id_kuis']) ? intval($_GET['id_kuis']) : 0;

// Fetch daftar siswa untuk dropdown filter
try {
    $students_list = $pdo->query("SELECT id_siswa, nama_siswa, nis, kelas FROM tb_siswa ORDER BY nama_siswa ASC")->fetchAll();
} catch (PDOException $e) {
    $students_list = [];
}

// Fetch daftar kuis untuk dropdown filter
try {
    $quizzes_list = $pdo->query("SELECT id_kuis, judul_kuis, kategori_materi FROM tb_kuis ORDER BY id_kuis ASC")->fetchAll();
} catch (PDOException $e) {
    $quizzes_list = [];
}

// Query untuk Tab 1: Log Seluruh Nilai
try {
    $sql_results = "
        SELECT h.*, s.nama_siswa, s.nis, s.kelas, k.judul_kuis, k.kategori_materi
        FROM tb_hasil h
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        WHERE 1=1
    ";
    
    $params = [];
    if ($filter_siswa > 0) {
        $sql_results .= " AND h.id_siswa = :id_siswa";
        $params['id_siswa'] = $filter_siswa;
    }
    if ($filter_kuis > 0) {
        $sql_results .= " AND h.id_kuis = :id_kuis";
        $params['id_kuis'] = $filter_kuis;
    }
    
    $sql_results .= " ORDER BY h.waktu_selesai DESC";
    
    $stmt = $pdo->prepare($sql_results);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error database saat memuat data laporan: " . $e->getMessage());
}

// --- DATA FILTER TAB 2: RAPORT SISWA ---
$raport_siswa_id = isset($_GET['raport_siswa']) ? intval($_GET['raport_siswa']) : 0;
$raport_tipe = isset($_GET['raport_tipe']) ? $_GET['raport_tipe'] : 'bulanan';
$raport_bulan = isset($_GET['raport_bulan']) ? intval($_GET['raport_bulan']) : intval(date('m'));
$raport_tahun = isset($_GET['raport_tahun']) ? intval($_GET['raport_tahun']) : intval(date('Y'));
$raport_tgl_mulai = isset($_GET['raport_tgl_mulai']) ? trim($_GET['raport_tgl_mulai']) : date('Y-m-d', strtotime('-7 days'));
$raport_tgl_selesai = isset($_GET['raport_tgl_selesai']) ? trim($_GET['raport_tgl_selesai']) : date('Y-m-d');

$nama_bulan_arr = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$raport_siswa = null;
$raport_results = [];
$raport_stats = [
    'total' => 0,
    'rata_rata' => 0,
    'tertinggi' => 0,
    'terendah' => 0,
    'lulus' => 0,
    'predikat' => '-',
    'predikat_label' => '-'
];
$raport_periode_text = '';

if ($raport_siswa_id > 0) {
    // Ambil info siswa
    $stmt_r_siswa = $pdo->prepare("SELECT * FROM tb_siswa WHERE id_siswa = :id");
    $stmt_r_siswa->execute(['id' => $raport_siswa_id]);
    $raport_siswa = $stmt_r_siswa->fetch();

    if ($raport_siswa) {
        $sql_r = "
            SELECT h.*, k.judul_kuis, k.kategori_materi
            FROM tb_hasil h
            JOIN tb_kuis k ON h.id_kuis = k.id_kuis
            WHERE h.id_siswa = :id_siswa
        ";
        $p_r = ['id_siswa' => $raport_siswa_id];

        if ($raport_tipe === 'mingguan' && !empty($raport_tgl_mulai) && !empty($raport_tgl_selesai)) {
            $sql_r .= " AND DATE(h.waktu_selesai) BETWEEN :start_date AND :end_date";
            $p_r['start_date'] = $raport_tgl_mulai;
            $p_r['end_date'] = $raport_tgl_selesai;
            $raport_periode_text = "Mingguan (" . date('d M Y', strtotime($raport_tgl_mulai)) . " s.d. " . date('d M Y', strtotime($raport_tgl_selesai)) . ")";
        } elseif ($raport_tipe === 'bulanan' && $raport_bulan > 0 && $raport_tahun > 0) {
            $sql_r .= " AND MONTH(h.waktu_selesai) = :bulan AND YEAR(h.waktu_selesai) = :tahun";
            $p_r['bulan'] = $raport_bulan;
            $p_r['tahun'] = $raport_tahun;
            $raport_periode_text = "Bulanan (" . ($nama_bulan_arr[$raport_bulan] ?? $raport_bulan) . " " . $raport_tahun . ")";
        } else {
            $raport_periode_text = "Seluruh Periode Pembelajaran";
        }

        $sql_r .= " ORDER BY h.waktu_selesai ASC";
        $stmt_r = $pdo->prepare($sql_r);
        $stmt_r->execute($p_r);
        $raport_results = $stmt_r->fetchAll();

        $raport_stats['total'] = count($raport_results);
        $total_score_sum = 0;
        $max_s = 0;
        $min_s = 100;
        $passed_count = 0;

        foreach ($raport_results as $row_r) {
            $total_score_sum += $row_r['skor'];
            if ($row_r['skor'] > $max_s) $max_s = $row_r['skor'];
            if ($row_r['skor'] < $min_s) $min_s = $row_r['skor'];
            if ($row_r['skor'] >= 70) $passed_count++;
        }

        if ($raport_stats['total'] > 0) {
            $raport_stats['rata_rata'] = round($total_score_sum / $raport_stats['total'], 1);
            $raport_stats['tertinggi'] = $max_s;
            $raport_stats['terendah'] = $min_s;
            $raport_stats['lulus'] = $passed_count;

            if ($raport_stats['rata_rata'] >= 85) {
                $raport_stats['predikat'] = 'A';
                $raport_stats['predikat_label'] = 'Sangat Baik (Amat Memuaskan)';
            } elseif ($raport_stats['rata_rata'] >= 75) {
                $raport_stats['predikat'] = 'B';
                $raport_stats['predikat_label'] = 'Baik (Memuaskan)';
            } elseif ($raport_stats['rata_rata'] >= 70) {
                $raport_stats['predikat'] = 'C';
                $raport_stats['predikat_label'] = 'Cukup (Tuntas KKM)';
            } else {
                $raport_stats['predikat'] = 'D';
                $raport_stats['predikat_label'] = 'Perlu Bimbingan (Belum Tuntas)';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai & Raport Siswa - Nommensen Admin</title>
    
    <!-- Impor Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Memanggil Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=4.3.0">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .admin-layout {
            display: flex;
            flex-direction: row;
            flex: 1;
            width: 100%;
        }

        /* Sidebar Navigasi Abu-Abu */
        .sidebar {
            width: 250px;
            background-color: #e5e7eb;
            color: #1f2937;
            padding: 1.5rem 1.25rem;
            display: flex;
            flex-direction: column;
            border-right: 2px solid #cbd5e1;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow: hidden;
            z-index: 1000;
        }

        .sidebar-brand h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            font-weight: 800;
            color: #475569;
            letter-spacing: 1.5px;
            margin-bottom: 2rem;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 0.5rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
        }

        .sidebar-menu li {
            margin-bottom: 0.85rem;
        }

        .sidebar-menu a {
            color: #374151;
            text-decoration: none;
            padding: 0.75rem 1rem;
            border: 2px solid #9ca3af;
            border-radius: 10px;
            display: block;
            font-weight: 700;
            text-align: center;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
            font-size: 0.9rem;
        }

        .sidebar-menu a:hover {
            background-color: #f1f5f9;
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .sidebar-menu a.active {
            color: #ffffff;
            background-color: #4b5563;
            border-color: #374151;
        }

        .btn-logout-sidebar {
            background-color: #ef4444;
            color: #ffffff;
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 700;
            transition: background-color 0.2s;
            border: 2px solid #dc2626;
            margin-top: 1rem;
            display: block;
        }

        .btn-logout-sidebar:hover {
            background-color: #b91c1c;
        }

        /* Area Konten Utama */
        .main-content {
            margin-left: 250px;
            flex-grow: 1;
            padding: 2.5rem 3rem;
            overflow-y: visible;
            min-width: 0;
        }

        /* Nav Pills Styling */
        .nav-tabs-custom {
            border-bottom: 2px solid #e2e8f0;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            color: #64748b;
            font-weight: 700;
            padding: 0.85rem 1.5rem;
            border-radius: 10px 10px 0 0;
            background: transparent;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
        }

        .nav-tabs-custom .nav-link:hover {
            color: #2563eb;
            background-color: #f1f5f9;
        }

        .nav-tabs-custom .nav-link.active {
            color: #2563eb;
            background-color: #ffffff;
            border-bottom: 3px solid #2563eb;
        }

        /* Form Filter Inline */
        .filter-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            border-radius: 14px;
            padding: 1.5rem;
            margin-bottom: 1.75rem;
        }

        .raport-preview-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .raport-preview-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: #ffffff;
            padding: 1.75rem 2rem;
        }

        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.25rem 1rem;
            text-align: center;
            height: 100%;
        }

        .kpi-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 0.35rem;
        }

        .kpi-value {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
        }
    </style>
</head>
<body class="admin-body">

    <!-- Header Atas -->
    <header class="top-header">
        Dashboard Administrator - Panel Guru
    </header>

    <div class="admin-layout">
        <!-- Sidebar Navigasi Kiri (7 Menu Resmi) -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h3>Admin Nommensen</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="kelola_materi.php">Kelola Materi</a></li>
                    <li><a href="upload_media.php">Upload Media</a></li>
                    <li><a href="kelola_soal.php">Kelola Soal</a></li>
                    <li><a href="laporan_nilai.php" class="active">Laporan Nilai</a></li>
                    <li><a href="pengaturan.php">Pengaturan</a></li>
                    <li><a href="kelola_siswa.php">Kelola Data Siswa</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Keluar (Logout)</a>
        </aside>

        <!-- Area Konten Utama Kanan -->
        <main class="main-content">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0;">
                        Laporan Nilai &amp; Raport Siswa
                    </h2>
                    <p style="color: #64748b; font-size: 0.95rem; margin-top: 0.35rem; margin-bottom: 0;">
                        Pantau seluruh riwayat pengerjaan kuis siswa atau cetak Raport Evaluasi Belajar resmi per siswa (Mingguan / Bulanan / Semua).
                    </p>
                </div>
            </div>

            <!-- Nav Tabs Navigasi Antara Log Nilai & Raport Siswa -->
            <ul class="nav nav-tabs nav-tabs-custom" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $active_tab === 'log' ? 'active' : '' ?>" href="laporan_nilai.php?tab=log">
                        <i class="bi bi-table"></i>
                        Log Seluruh Nilai Siswa
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $active_tab === 'raport' ? 'active' : '' ?>" href="laporan_nilai.php?tab=raport">
                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                        Raport Siswa (Cetak PDF Mingguan / Bulanan)
                    </a>
                </li>
            </ul>

            <?php if ($active_tab === 'log'): ?>
                <!-- ================= TAB 1: LOG SELURUH NILAI ================= -->
                
                <!-- Form Filter Pencarian Log -->
                <div class="filter-card">
                    <form action="laporan_nilai.php" method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="tab" value="log">
                        
                        <!-- Filter Siswa -->
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-secondary small">Filter Berdasarkan Siswa:</label>
                            <select name="id_siswa" class="form-select">
                                <option value="0">-- Semua Siswa --</option>
                                <?php foreach ($students_list as $student): ?>
                                    <option value="<?= $student['id_siswa'] ?>" <?= $filter_siswa === intval($student['id_siswa']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($student['nama_siswa']) ?> (NIS: <?= htmlspecialchars($student['nis']) ?> - Kelas: <?= htmlspecialchars($student['kelas']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filter Kuis -->
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-secondary small">Filter Berdasarkan Kuis:</label>
                            <select name="id_kuis" class="form-select">
                                <option value="0">-- Semua Kuis --</option>
                                <?php foreach ($quizzes_list as $quiz): ?>
                                    <option value="<?= $quiz['id_kuis'] ?>" <?= $filter_kuis === intval($quiz['id_kuis']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($quiz['judul_kuis']) ?> (<?= htmlspecialchars($quiz['kategori_materi']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold w-100 py-2">
                                <i class="bi bi-filter me-1"></i> Filter
                            </button>
                            <?php if ($filter_siswa > 0 || $filter_kuis > 0): ?>
                                <a href="laporan_nilai.php?tab=log" class="btn btn-outline-secondary py-2" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Info Header Rekap -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-secondary small">
                        Menampilkan <strong><?= count($results) ?></strong> rekaman data hasil kuis.
                    </div>
                </div>

                <!-- Tabel Daftar Laporan Nilai -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 px-3">Nama Siswa</th>
                                    <th class="py-3 px-3 text-center">NIS</th>
                                    <th class="py-3 px-3 text-center">Kelas</th>
                                    <th class="py-3 px-3">Nama Kuis</th>
                                    <th class="py-3 px-3 text-center">Skor</th>
                                    <th class="py-3 px-3 text-center">Detail Jawaban</th>
                                    <th class="py-3 px-3 text-end">Waktu Selesai</th>
                                    <th class="py-3 px-3 text-center">Aksi Raport</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($results)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                            Tidak ada data nilai kuis yang cocok dengan filter pencarian.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($results as $row): ?>
                                        <?php 
                                        $is_passed = $row['skor'] >= 70;
                                        $score_color = $is_passed ? '#16a34a' : '#dc2626';
                                        ?>
                                        <tr>
                                            <td class="py-3 px-3 fw-bold text-dark">
                                                <?= htmlspecialchars($row['nama_siswa']) ?>
                                            </td>
                                            <td class="py-3 px-3 text-center text-muted"><?= htmlspecialchars($row['nis']) ?></td>
                                            <td class="py-3 px-3 text-center">
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($row['kelas']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 fw-semibold text-primary">
                                                <?= htmlspecialchars($row['judul_kuis']) ?>
                                            </td>
                                            <td class="py-3 px-3 text-center fw-bold fs-5" style="color: <?= $score_color ?>;">
                                                <?= $row['skor'] ?>
                                            </td>
                                            <td class="py-3 px-3 text-center small fw-semibold">
                                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i><?= $row['jumlah_benar'] ?></span> / 
                                                <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i><?= $row['jumlah_salah'] ?></span>
                                            </td>
                                            <td class="py-3 px-3 text-end text-muted small">
                                                <?= date('d M Y - H:i', strtotime($row['waktu_selesai'])) ?> WIB
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <a href="laporan_nilai.php?tab=raport&raport_siswa=<?= $row['id_siswa'] ?>" class="btn btn-sm btn-outline-primary" title="Buka Raport Siswa Ini">
                                                    <i class="bi bi-file-earmark-person"></i> Raport
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php else: ?>
                <!-- ================= TAB 2: RAPORT SISWA (CETAK PDF) ================= -->

                <!-- Card Filter Pemilihan Raport -->
                <div class="filter-card">
                    <form action="laporan_nilai.php" method="GET" class="row g-3 align-items-end" id="raportFilterForm">
                        <input type="hidden" name="tab" value="raport">

                        <!-- Pilihan Siswa -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Pilih Siswa <span class="text-danger">*</span>:</label>
                            <select name="raport_siswa" class="form-select" required>
                                <option value="">-- Pilih Nama Siswa --</option>
                                <?php foreach ($students_list as $st): ?>
                                    <option value="<?= $st['id_siswa'] ?>" <?= $raport_siswa_id === intval($st['id_siswa']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st['nama_siswa']) ?> (NIS: <?= htmlspecialchars($st['nis']) ?> - Kelas: <?= htmlspecialchars($st['kelas']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tipe Periode -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-dark small">Tipe Periode Laporan:</label>
                            <select name="raport_tipe" id="raportTipeSelect" class="form-select" onchange="togglePeriodeInputs()">
                                <option value="bulanan" <?= $raport_tipe === 'bulanan' ? 'selected' : '' ?>>Bulanan (Satu Bulan Penuh)</option>
                                <option value="mingguan" <?= $raport_tipe === 'mingguan' ? 'selected' : '' ?>>Mingguan (Rentang Tanggal)</option>
                                <option value="semua" <?= $raport_tipe === 'semua' ? 'selected' : '' ?>>Seluruh Riwayat (Semua Kuis)</option>
                            </select>
                        </div>

                        <!-- Input Khusus Bulanan -->
                        <div class="col-md-3" id="groupBulanan" style="<?= $raport_tipe === 'bulanan' ? '' : 'display:none;' ?>">
                            <div class="row g-2">
                                <div class="col-7">
                                    <label class="form-label fw-bold text-secondary small">Bulan:</label>
                                    <select name="raport_bulan" class="form-select">
                                        <?php foreach ($nama_bulan_arr as $num => $nama): ?>
                                            <option value="<?= $num ?>" <?= $raport_bulan === $num ? 'selected' : '' ?>>
                                                <?= $nama ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label class="form-label fw-bold text-secondary small">Tahun:</label>
                                    <select name="raport_tahun" class="form-select">
                                        <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                                            <option value="<?= $y ?>" <?= $raport_tahun === $y ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Input Khusus Mingguan (Rentang Tanggal) -->
                        <div class="col-md-3" id="groupMingguan" style="<?= $raport_tipe === 'mingguan' ? '' : 'display:none;' ?>">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary small">Mulai:</label>
                                    <input type="date" name="raport_tgl_mulai" class="form-control" value="<?= htmlspecialchars($raport_tgl_mulai) ?>">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold text-secondary small">Sampai:</label>
                                    <input type="date" name="raport_tgl_selesai" class="form-control" value="<?= htmlspecialchars($raport_tgl_selesai) ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Tampilkan -->
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary fw-bold w-100 py-2">
                                <i class="bi bi-eye-fill me-1"></i> Tampilkan
                            </button>
                        </div>
                    </form>
                </div>

                <?php if ($raport_siswa_id > 0 && $raport_siswa): ?>
                    <!-- Kotak Pratinjau Raport & Tombol Cetak PDF -->
                    <div class="raport-preview-box">
                        <div class="raport-preview-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-white text-primary fw-bold px-3 py-1 rounded-pill">
                                        <?= strtoupper($raport_tipe) ?> REPORT
                                    </span>
                                    <span class="text-white-50 small">&bull;</span>
                                    <span class="text-light fw-medium small">Periode: <?= htmlspecialchars($raport_periode_text) ?></span>
                                </div>
                                <h3 class="h4 fw-bold text-white mb-1"><?= htmlspecialchars($raport_siswa['nama_siswa']) ?></h3>
                                <div class="text-light opacity-75 small">
                                    NIS: <strong><?= htmlspecialchars($raport_siswa['nis']) ?></strong> &bull; 
                                    NISN: <strong><?= htmlspecialchars($raport_siswa['nisn'] ?? '-') ?></strong> &bull; 
                                    Kelas: <strong><?= htmlspecialchars($raport_siswa['kelas']) ?></strong> &bull; 
                                    SMP Swasta Nommensen
                                </div>
                            </div>
                            <div>
                                <!-- Tombol Cetak PDF Berstandar A4 Sekolah -->
                                <?php
                                $print_url = "cetak_raport.php?id_siswa={$raport_siswa_id}&tipe_periode={$raport_tipe}&bulan={$raport_bulan}&tahun={$raport_tahun}&tgl_mulai={$raport_tgl_mulai}&tgl_selesai={$raport_tgl_selesai}";
                                ?>
                                <a href="<?= $print_url ?>" target="_blank" class="btn btn-warning btn-lg fw-bold px-4 py-2 shadow d-inline-flex align-items-center gap-2 text-dark">
                                    <i class="bi bi-printer-fill fs-5"></i>
                                    <span>Cetak Raport PDF</span>
                                </a>
                            </div>
                        </div>

                        <div class="p-4 bg-white">
                            <!-- Baris KPI Statistik -->
                            <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
                                <div class="col">
                                    <div class="kpi-card">
                                        <div class="kpi-title">Total Kuis Selesai</div>
                                        <div class="kpi-value text-primary"><?= $raport_stats['total'] ?></div>
                                        <div class="text-muted small mt-1">Sesi Ujian</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="kpi-card">
                                        <div class="kpi-title">Rata-Rata Nilai</div>
                                        <div class="kpi-value <?= $raport_stats['rata_rata'] >= 70 ? 'text-success' : 'text-danger' ?>">
                                            <?= $raport_stats['rata_rata'] ?>
                                        </div>
                                        <div class="text-muted small mt-1">KKM Kelulusan: 70</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="kpi-card">
                                        <div class="kpi-title">Ketuntasan KKM</div>
                                        <div class="kpi-value text-success">
                                            <?= $raport_stats['lulus'] ?> <span class="fs-6 text-muted fw-normal">/ <?= $raport_stats['total'] ?></span>
                                        </div>
                                        <div class="text-muted small mt-1"><?= $raport_stats['total'] > 0 ? round(($raport_stats['lulus'] / $raport_stats['total']) * 100) : 0 ?>% Tuntas</div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="kpi-card">
                                        <div class="kpi-title">Predikat Akhir</div>
                                        <div class="kpi-value text-dark"><?= $raport_stats['predikat'] ?></div>
                                        <div class="text-muted small mt-1 text-truncate" title="<?= $raport_stats['predikat_label'] ?>">
                                            <?= $raport_stats['predikat_label'] ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabel Rincian Nilai Kuis Siswa Pada Periode Ini -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold text-dark mb-0">Rincian Nilai Kuis (Periode: <?= htmlspecialchars($raport_periode_text) ?>)</h5>
                                <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                                    <?= count($raport_results) ?> Catatan
                                </span>
                            </div>

                            <div class="table-responsive border rounded-3 mb-3">
                                <table class="table table-striped table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-2 px-3 text-center" style="width: 50px;">No</th>
                                            <th class="py-2 px-3">Materi / Kuis</th>
                                            <th class="py-2 px-3 text-center" style="width: 100px;">KKM</th>
                                            <th class="py-2 px-3 text-center" style="width: 100px;">Skor</th>
                                            <th class="py-2 px-3 text-center" style="width: 120px;">Predikat</th>
                                            <th class="py-2 px-3 text-center" style="width: 150px;">Keterangan</th>
                                            <th class="py-2 px-3 text-end" style="width: 180px;">Waktu Pengerjaan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($raport_results)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    Tidak ada data kuis yang diselesaikan oleh siswa pada rentang periode ini.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $no = 1; foreach ($raport_results as $kr): ?>
                                                <?php
                                                $tuntas = $kr['skor'] >= 70;
                                                if ($kr['skor'] >= 85) $p = 'A';
                                                elseif ($kr['skor'] >= 75) $p = 'B';
                                                elseif ($kr['skor'] >= 70) $p = 'C';
                                                else $p = 'D';
                                                ?>
                                                <tr>
                                                    <td class="text-center fw-semibold text-secondary"><?= $no++ ?></td>
                                                    <td class="fw-bold text-dark">
                                                        <?= htmlspecialchars($kr['judul_kuis']) ?>
                                                        <div class="small text-muted fw-normal"><?= htmlspecialchars($kr['kategori_materi'] ?? '-') ?></div>
                                                    </td>
                                                    <td class="text-center text-secondary">70</td>
                                                    <td class="text-center fw-bold fs-6 <?= $tuntas ? 'text-success' : 'text-danger' ?>">
                                                        <?= $kr['skor'] ?>
                                                    </td>
                                                    <td class="text-center fw-bold"><?= $p ?></td>
                                                    <td class="text-center">
                                                        <?php if ($tuntas): ?>
                                                            <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">Tuntas</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">Remedial</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end text-muted small">
                                                        <?= date('d/m/Y H:i', strtotime($kr['waktu_selesai'])) ?> WIB
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Petunjuk Cetak PDF -->
                            <div class="alert alert-info d-flex align-items-center gap-3 mb-0" role="alert">
                                <i class="bi bi-info-circle-fill fs-3 text-primary flex-shrink-0"></i>
                                <div class="small">
                                    <strong>Panduan Generate PDF Raport:</strong> Klik tombol <strong>"Cetak Raport PDF"</strong> berwarna kuning di atas. Sistem akan membuka dokumen resmi A4 berstandar SMP Swasta Nommensen yang langsung mengaktifkan dialog print browser. Pada dialog cetak, pilih tujuan <em>"Save as PDF"</em> (Simpan sebagai PDF) untuk mengunduh raport.
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- State Belum Pilih Siswa -->
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                        <div class="py-4">
                            <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-person-lines-fill fs-1"></i>
                            </div>
                            <h4 class="fw-bold text-dark">Pilih Siswa &amp; Periode Raport</h4>
                            <p class="text-muted mx-auto" style="max-width: 500px;">
                                Silakan tentukan nama siswa dan jenis periode (Mingguan / Bulanan / Semua Riwayat) pada formulir di atas, lalu klik tombol <strong>Tampilkan</strong> untuk melihat pratinjau nilai dan mencetak raport PDF resmi.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </main>
    </div>

    <!-- Footer Bawah -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePeriodeInputs() {
            var tipe = document.getElementById('raportTipeSelect').value;
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
</body>
</html>