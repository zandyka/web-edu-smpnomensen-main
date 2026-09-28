<?php
/**
 * File: admin/laporan_nilai.php
 * Deskripsi: Halaman Laporan Hasil Nilai & Raport Evaluasi Siswa Kelas 7 (VII-A, VII-B, VII-C).
 *            Dilengkapi dengan:
 *            1. Pemisahan data per kelas: 7A, 7B, dan 7C
 *            2. Kartu Highlight Siswa dengan Nilai Tertinggi (Bintang Prestasi per kelas & overall)
 *            3. Tanggal & Jam pengerjaan kuis lengkap dan presisi
 *            4. Tab Log Seluruh Nilai & Tab Raport Siswa (Cetak PDF Mingguan / Bulanan)
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';

// Memanggil koneksi database
require_once '../config.php';

// Tab aktif (log atau raport)
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'raport' ? 'raport' : 'log';

// --- DATA FILTER TAB 1: LOG NILAI ---
$filter_kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
$filter_siswa = isset($_GET['id_siswa']) ? intval($_GET['id_siswa']) : 0;
$filter_kuis = isset($_GET['id_kuis']) ? intval($_GET['id_kuis']) : 0;
$filter_tanggal = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';

// Validasi filter kelas
if (!in_array($filter_kelas, ['VII-A', 'VII-B', 'VII-C'])) {
    $filter_kelas = '';
}

// Fetch daftar siswa untuk dropdown filter (hanya kelas 7, jika filter kelas aktif maka filter juga)
try {
    $sql_students_list = "SELECT id_siswa, nama_siswa, nis, nisn, kelas FROM tb_siswa WHERE kelas LIKE 'VII-%'";
    $p_st_list = [];
    if (!empty($filter_kelas)) {
        $sql_students_list .= " AND kelas = :kelas";
        $p_st_list['kelas'] = $filter_kelas;
    }
    $sql_students_list .= " ORDER BY kelas ASC, nama_siswa ASC";
    $stmt_st_list = $pdo->prepare($sql_students_list);
    $stmt_st_list->execute($p_st_list);
    $students_list = $stmt_st_list->fetchAll();
} catch (PDOException $e) {
    $students_list = [];
}

// Fetch daftar kuis untuk dropdown filter
try {
    $quizzes_list = $pdo->query("SELECT id_kuis, judul_kuis, kategori_materi FROM tb_kuis ORDER BY id_kuis ASC")->fetchAll();
} catch (PDOException $e) {
    $quizzes_list = [];
}

// Hitung total data log per kelas untuk badge tab
try {
    $count_log_all = $pdo->query("SELECT COUNT(*) FROM tb_hasil h JOIN tb_siswa s ON h.id_siswa = s.id_siswa WHERE s.kelas LIKE 'VII-%'")->fetchColumn();
    $count_log_7a = $pdo->query("SELECT COUNT(*) FROM tb_hasil h JOIN tb_siswa s ON h.id_siswa = s.id_siswa WHERE s.kelas = 'VII-A'")->fetchColumn();
    $count_log_7b = $pdo->query("SELECT COUNT(*) FROM tb_hasil h JOIN tb_siswa s ON h.id_siswa = s.id_siswa WHERE s.kelas = 'VII-B'")->fetchColumn();
    $count_log_7c = $pdo->query("SELECT COUNT(*) FROM tb_hasil h JOIN tb_siswa s ON h.id_siswa = s.id_siswa WHERE s.kelas = 'VII-C'")->fetchColumn();
} catch (PDOException $e) {
    $count_log_all = $count_log_7a = $count_log_7b = $count_log_7c = 0;
}

// ===================================================================
// SISWA DENGAN NILAI TERTINGGI (TOP SCORERS) PER KELAS 7A, 7B, 7C
// ===================================================================
$top_7a = null;
$top_7b = null;
$top_7c = null;
$top_overall = null;

try {
    // Top 7A
    $stmt_top_7a = $pdo->query("
        SELECT h.*, s.nama_siswa, s.nis, s.nisn, s.kelas, k.judul_kuis 
        FROM tb_hasil h 
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa 
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis 
        WHERE s.kelas = 'VII-A' 
        ORDER BY h.skor DESC, h.waktu_selesai DESC 
        LIMIT 1
    ");
    $top_7a = $stmt_top_7a->fetch();

    // Top 7B
    $stmt_top_7b = $pdo->query("
        SELECT h.*, s.nama_siswa, s.nis, s.nisn, s.kelas, k.judul_kuis 
        FROM tb_hasil h 
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa 
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis 
        WHERE s.kelas = 'VII-B' 
        ORDER BY h.skor DESC, h.waktu_selesai DESC 
        LIMIT 1
    ");
    $top_7b = $stmt_top_7b->fetch();

    // Top 7C
    $stmt_top_7c = $pdo->query("
        SELECT h.*, s.nama_siswa, s.nis, s.nisn, s.kelas, k.judul_kuis 
        FROM tb_hasil h 
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa 
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis 
        WHERE s.kelas = 'VII-C' 
        ORDER BY h.skor DESC, h.waktu_selesai DESC 
        LIMIT 1
    ");
    $top_7c = $stmt_top_7c->fetch();

    // Top Overall
    $stmt_top_all = $pdo->query("
        SELECT h.*, s.nama_siswa, s.nis, s.nisn, s.kelas, k.judul_kuis 
        FROM tb_hasil h 
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa 
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis 
        WHERE s.kelas LIKE 'VII-%' 
        ORDER BY h.skor DESC, h.waktu_selesai DESC 
        LIMIT 1
    ");
    $top_overall = $stmt_top_all->fetch();
} catch (PDOException $e) {
    // silent
}

// Query untuk Tab 1: Log Seluruh Nilai
try {
    $sql_results = "
        SELECT h.*, s.nama_siswa, s.nis, s.nisn, s.kelas, k.judul_kuis, k.kategori_materi
        FROM tb_hasil h
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        WHERE s.kelas LIKE 'VII-%'
    ";
    
    $params = [];
    if (!empty($filter_kelas)) {
        $sql_results .= " AND s.kelas = :kelas";
        $params['kelas'] = $filter_kelas;
    }
    if ($filter_siswa > 0) {
        $sql_results .= " AND h.id_siswa = :id_siswa";
        $params['id_siswa'] = $filter_siswa;
    }
    if ($filter_kuis > 0) {
        $sql_results .= " AND h.id_kuis = :id_kuis";
        $params['id_kuis'] = $filter_kuis;
    }
    if (!empty($filter_tanggal)) {
        $sql_results .= " AND DATE(h.waktu_selesai) = :tanggal";
        $params['tanggal'] = $filter_tanggal;
    }
    
    $sql_results .= " ORDER BY h.waktu_selesai DESC";
    
    $stmt = $pdo->prepare($sql_results);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error database saat memuat data laporan: " . $e->getMessage());
}

// --- DATA FILTER TAB 2: RAPORT SISWA ---
$raport_kelas = isset($_GET['raport_kelas']) ? trim($_GET['raport_kelas']) : '';
if (!in_array($raport_kelas, ['VII-A', 'VII-B', 'VII-C'])) {
    $raport_kelas = '';
}
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

// Fetch siswa khusus untuk tab raport
try {
    $sql_r_students = "SELECT id_siswa, nama_siswa, nis, nisn, kelas FROM tb_siswa WHERE kelas LIKE 'VII-%'";
    $p_r_st = [];
    if (!empty($raport_kelas)) {
        $sql_r_students .= " AND kelas = :kelas";
        $p_r_st['kelas'] = $raport_kelas;
    }
    $sql_r_students .= " ORDER BY kelas ASC, nama_siswa ASC";
    $stmt_r_st = $pdo->prepare($sql_r_students);
    $stmt_r_st->execute($p_r_st);
    $raport_students_list = $stmt_r_st->fetchAll();
} catch (PDOException $e) {
    $raport_students_list = [];
}

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
    <title>Laporan Nilai & Raport Siswa Kelas 7 - Nommensen Admin</title>
    
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

        .class-pill-btn {
            border-radius: 10px;
            padding: 0.55rem 1.15rem;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1.5px solid #cbd5e1;
            background: #ffffff;
            color: #475569;
        }

        .class-pill-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
        }

        .class-pill-btn.active {
            background-color: #2563eb;
            color: #ffffff;
            border-color: #1d4ed8;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        /* Top Scorer Card */
        .top-scorer-card {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
            position: relative;
        }

        .top-scorer-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.08);
        }

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
                        Laporan Nilai &amp; Raport Siswa Kelas 7
                    </h2>
                    <p style="color: #64748b; font-size: 0.95rem; margin-top: 0.35rem; margin-bottom: 0;">
                        Rekapitulasi nilai kuis dengan pemisahan <strong>Kelas 7A, 7B, 7C</strong>, siswa berprestasi tertinggi, serta waktu pengerjaan (tanggal &amp; jam lengkap).
                    </p>
                </div>
            </div>

            <!-- Nav Tabs Navigasi Antara Log Nilai & Raport Siswa -->
            <ul class="nav nav-tabs nav-tabs-custom" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $active_tab === 'log' ? 'active' : '' ?>" href="laporan_nilai.php?tab=log<?= !empty($filter_kelas) ? '&kelas=' . urlencode($filter_kelas) : '' ?>">
                        <i class="bi bi-table"></i>
                        Log Seluruh Nilai Kuis (Kelas 7)
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link <?= $active_tab === 'raport' ? 'active' : '' ?>" href="laporan_nilai.php?tab=raport<?= !empty($filter_kelas) ? '&raport_kelas=' . urlencode($filter_kelas) : '' ?>">
                        <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                        Raport Siswa (Cetak PDF Mingguan / Bulanan)
                    </a>
                </li>
            </ul>

            <?php if ($active_tab === 'log'): ?>
                <!-- ================= TAB 1: LOG SELURUH NILAI ================= -->

                <!-- SEKSI HIGHLIGHT: SISWA DENGAN NILAI TERTINGGI (TOP SCORERS PER KELAS 7A, 7B, 7C) -->
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold">
                            <i class="bi bi-trophy-fill me-1"></i> BINTANG PRESTASI
                        </span>
                        <h4 class="h5 fw-bold text-dark mb-0">Siswa Peraih Nilai Tertinggi per Rombel</h4>
                    </div>

                    <div class="row row-cols-1 row-cols-md-3 g-3">
                        <!-- Top Scorer 7A -->
                        <div class="col">
                            <div class="top-scorer-card p-3 shadow-sm h-100" style="border-top: 4px solid #f59e0b;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-3 py-1 rounded-pill">
                                        KELAS VII-A (7A)
                                    </span>
                                    <span class="fs-4">🥇</span>
                                </div>
                                <?php if ($top_7a): ?>
                                    <h5 class="fw-bold text-dark mb-1 fs-6 text-truncate" title="<?= htmlspecialchars($top_7a['nama_siswa']) ?>">
                                        <?= htmlspecialchars($top_7a['nama_siswa']) ?>
                                    </h5>
                                    <div class="text-muted small mb-2">
                                        NIS: <?= htmlspecialchars($top_7a['nis']) ?>
                                        <?php if (!empty($top_7a['nisn'])): ?>
                                            &bull; NISN: <?= htmlspecialchars($top_7a['nisn']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border mb-2">
                                        <span class="small text-secondary fw-semibold text-truncate me-2" title="<?= htmlspecialchars($top_7a['judul_kuis']) ?>">
                                            <?= htmlspecialchars($top_7a['judul_kuis']) ?>
                                        </span>
                                        <span class="badge bg-success fs-6 fw-bold px-2 py-1">
                                            Skor <?= $top_7a['skor'] ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-calendar-event me-1"></i><?= date('d M Y', strtotime($top_7a['waktu_selesai'])) ?></span>
                                        <span><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($top_7a['waktu_selesai'])) ?> WIB</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted small">
                                        <i class="bi bi-hourglass-split d-block fs-3 mb-1"></i>
                                        Belum ada nilai kuis di Kelas VII-A
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Top Scorer 7B -->
                        <div class="col">
                            <div class="top-scorer-card p-3 shadow-sm h-100" style="border-top: 4px solid #06b6d4;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-info-subtle text-info-emphasis fw-bold px-3 py-1 rounded-pill">
                                        KELAS VII-B (7B)
                                    </span>
                                    <span class="fs-4">🥈</span>
                                </div>
                                <?php if ($top_7b): ?>
                                    <h5 class="fw-bold text-dark mb-1 fs-6 text-truncate" title="<?= htmlspecialchars($top_7b['nama_siswa']) ?>">
                                        <?= htmlspecialchars($top_7b['nama_siswa']) ?>
                                    </h5>
                                    <div class="text-muted small mb-2">
                                        NIS: <?= htmlspecialchars($top_7b['nis']) ?>
                                        <?php if (!empty($top_7b['nisn'])): ?>
                                            &bull; NISN: <?= htmlspecialchars($top_7b['nisn']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border mb-2">
                                        <span class="small text-secondary fw-semibold text-truncate me-2" title="<?= htmlspecialchars($top_7b['judul_kuis']) ?>">
                                            <?= htmlspecialchars($top_7b['judul_kuis']) ?>
                                        </span>
                                        <span class="badge bg-success fs-6 fw-bold px-2 py-1">
                                            Skor <?= $top_7b['skor'] ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-calendar-event me-1"></i><?= date('d M Y', strtotime($top_7b['waktu_selesai'])) ?></span>
                                        <span><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($top_7b['waktu_selesai'])) ?> WIB</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted small">
                                        <i class="bi bi-hourglass-split d-block fs-3 mb-1"></i>
                                        Belum ada nilai kuis di Kelas VII-B
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Top Scorer 7C -->
                        <div class="col">
                            <div class="top-scorer-card p-3 shadow-sm h-100" style="border-top: 4px solid #10b981;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success-subtle text-success-emphasis fw-bold px-3 py-1 rounded-pill">
                                        KELAS VII-C (7C)
                                    </span>
                                    <span class="fs-4">🥉</span>
                                </div>
                                <?php if ($top_7c): ?>
                                    <h5 class="fw-bold text-dark mb-1 fs-6 text-truncate" title="<?= htmlspecialchars($top_7c['nama_siswa']) ?>">
                                        <?= htmlspecialchars($top_7c['nama_siswa']) ?>
                                    </h5>
                                    <div class="text-muted small mb-2">
                                        NIS: <?= htmlspecialchars($top_7c['nis']) ?>
                                        <?php if (!empty($top_7c['nisn'])): ?>
                                            &bull; NISN: <?= htmlspecialchars($top_7c['nisn']) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border mb-2">
                                        <span class="small text-secondary fw-semibold text-truncate me-2" title="<?= htmlspecialchars($top_7c['judul_kuis']) ?>">
                                            <?= htmlspecialchars($top_7c['judul_kuis']) ?>
                                        </span>
                                        <span class="badge bg-success fs-6 fw-bold px-2 py-1">
                                            Skor <?= $top_7c['skor'] ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-calendar-event me-1"></i><?= date('d M Y', strtotime($top_7c['waktu_selesai'])) ?></span>
                                        <span><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($top_7c['waktu_selesai'])) ?> WIB</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted small">
                                        <i class="bi bi-hourglass-split d-block fs-3 mb-1"></i>
                                        Belum ada nilai kuis di Kelas VII-C
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Pemisahan Kelas 7A, 7B, 7C -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="laporan_nilai.php?tab=log" class="class-pill-btn <?= empty($filter_kelas) ? 'active' : '' ?>">
                            <i class="bi bi-collection-fill"></i>
                            Semua Kelas 7 (<?= $count_log_all ?>)
                        </a>
                        <a href="laporan_nilai.php?tab=log&kelas=VII-A" class="class-pill-btn <?= $filter_kelas === 'VII-A' ? 'active' : '' ?>">
                            <i class="bi bi-award-fill text-warning"></i>
                            Kelas VII-A / 7A (<?= $count_log_7a ?>)
                        </a>
                        <a href="laporan_nilai.php?tab=log&kelas=VII-B" class="class-pill-btn <?= $filter_kelas === 'VII-B' ? 'active' : '' ?>">
                            <i class="bi bi-award-fill text-info"></i>
                            Kelas VII-B / 7B (<?= $count_log_7b ?>)
                        </a>
                        <a href="laporan_nilai.php?tab=log&kelas=VII-C" class="class-pill-btn <?= $filter_kelas === 'VII-C' ? 'active' : '' ?>">
                            <i class="bi bi-award-fill text-success"></i>
                            Kelas VII-C / 7C (<?= $count_log_7c ?>)
                        </a>
                    </div>
                </div>

                <!-- Form Filter Pencarian Log -->
                <div class="filter-card">
                    <form action="laporan_nilai.php" method="GET" class="row g-3 align-items-end">
                        <input type="hidden" name="tab" value="log">
                        <?php if (!empty($filter_kelas)): ?>
                            <input type="hidden" name="kelas" value="<?= htmlspecialchars($filter_kelas) ?>">
                        <?php endif; ?>
                        
                        <!-- Filter Siswa -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Filter Siswa:</label>
                            <select name="id_siswa" class="form-select">
                                <option value="0">-- Semua Siswa <?= !empty($filter_kelas) ? "($filter_kelas)" : 'Kelas 7' ?> --</option>
                                <?php foreach ($students_list as $student): ?>
                                    <option value="<?= $student['id_siswa'] ?>" <?= $filter_siswa === intval($student['id_siswa']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($student['nama_siswa']) ?> (<?= htmlspecialchars($student['kelas']) ?> - NIS: <?= htmlspecialchars($student['nis']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filter Kuis -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">Filter Kuis:</label>
                            <select name="id_kuis" class="form-select">
                                <option value="0">-- Semua Kuis --</option>
                                <?php foreach ($quizzes_list as $quiz): ?>
                                    <option value="<?= $quiz['id_kuis'] ?>" <?= $filter_kuis === intval($quiz['id_kuis']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($quiz['judul_kuis']) ?> (<?= htmlspecialchars($quiz['kategori_materi']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filter Tanggal Pengerjaan -->
                        <div class="col-md-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold text-secondary small mb-0">Tanggal Pengerjaan:</label>
                                <a href="laporan_nilai.php?tab=log<?= !empty($filter_kelas) ? '&kelas=' . urlencode($filter_kelas) : '' ?>&tanggal=<?= date('Y-m-d') ?>" class="badge bg-primary-subtle text-primary text-decoration-none small">
                                    Hari Ini
                                </a>
                            </div>
                            <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($filter_tanggal) ?>">
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold w-100 py-2">
                                <i class="bi bi-filter me-1"></i> Filter
                            </button>
                            <?php if ($filter_siswa > 0 || $filter_kuis > 0 || !empty($filter_tanggal)): ?>
                                <a href="laporan_nilai.php?tab=log<?= !empty($filter_kelas) ? '&kelas=' . urlencode($filter_kelas) : '' ?>" class="btn btn-outline-secondary py-2" title="Reset Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Info Header Rekap -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-secondary small">
                        Menampilkan <strong><?= count($results) ?></strong> rekaman data hasil kuis <?= !empty($filter_kelas) ? "pada <strong>Kelas $filter_kelas</strong>" : "seluruh Kelas 7" ?><?= !empty($filter_tanggal) ? " pada tanggal <strong>" . date('d M Y', strtotime($filter_tanggal)) . "</strong>" : "" ?>.
                    </div>
                </div>

                <!-- Tabel Daftar Laporan Nilai -->
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3 px-3 text-center" style="width: 50px;">No</th>
                                    <th class="py-3 px-3">Nama Siswa</th>
                                    <th class="py-3 px-3 text-center" style="width: 110px;">Kelas</th>
                                    <th class="py-3 px-3">Nama Kuis</th>
                                    <th class="py-3 px-3 text-center" style="width: 100px;">Skor</th>
                                    <th class="py-3 px-3 text-center" style="width: 140px;">Benar / Salah</th>
                                    <th class="py-3 px-3" style="width: 220px;">Tanggal &amp; Jam Pengerjaan</th>
                                    <th class="py-3 px-3 text-center" style="width: 110px;">Aksi</th>
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
                                    <?php $no = 1; foreach ($results as $row): ?>
                                        <?php 
                                        $is_passed = $row['skor'] >= 70;
                                        $score_color = $is_passed ? '#16a34a' : '#dc2626';
                                        
                                        $badge_k = 'bg-secondary';
                                        if ($row['kelas'] === 'VII-A') $badge_k = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                        elseif ($row['kelas'] === 'VII-B') $badge_k = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                        elseif ($row['kelas'] === 'VII-C') $badge_k = 'bg-success-subtle text-success-emphasis border border-success-subtle';
                                        ?>
                                        <tr>
                                            <td class="py-3 px-3 text-center text-secondary fw-semibold"><?= $no++ ?></td>
                                            <td class="py-3 px-3 fw-bold text-dark">
                                                <?= htmlspecialchars($row['nama_siswa']) ?>
                                                <div class="small text-muted fw-normal">
                                                    NIS: <?= htmlspecialchars($row['nis']) ?>
                                                    <?php if (!empty($row['nisn'])): ?>
                                                        &bull; NISN: <?= htmlspecialchars($row['nisn']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <span class="badge <?= $badge_k ?> px-2 py-1 rounded-pill fw-bold">
                                                    <?= htmlspecialchars($row['kelas']) ?>
                                                </span>
                                            </td>
                                            <td class="py-3 px-3 fw-semibold text-primary">
                                                <?= htmlspecialchars($row['judul_kuis']) ?>
                                                <div class="small text-muted fw-normal"><?= htmlspecialchars($row['kategori_materi']) ?></div>
                                            </td>
                                            <td class="py-3 px-3 text-center fw-bold fs-5" style="color: <?= $score_color ?>;">
                                                <?= $row['skor'] ?>
                                            </td>
                                            <td class="py-3 px-3 text-center small fw-semibold">
                                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i><?= $row['jumlah_benar'] ?></span> / 
                                                <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i><?= $row['jumlah_salah'] ?></span>
                                            </td>
                                            <!-- KOLOM TANGGAL & JAM LENGKAP -->
                                            <td class="py-3 px-3">
                                                <div class="fw-semibold text-dark">
                                                    <i class="bi bi-calendar-event text-primary me-1"></i>
                                                    <?= date('d M Y', strtotime($row['waktu_selesai'])) ?>
                                                </div>
                                                <div class="small text-muted">
                                                    <i class="bi bi-clock me-1 text-secondary"></i>
                                                    Pukul <?= date('H:i:s', strtotime($row['waktu_selesai'])) ?> WIB
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <a href="laporan_nilai.php?tab=raport&raport_kelas=<?= urlencode($row['kelas']) ?>&raport_siswa=<?= $row['id_siswa'] ?>" class="btn btn-sm btn-outline-primary" title="Buka Raport Siswa Ini">
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

                        <!-- Pilihan Kelas (7A, 7B, 7C) -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-dark small">Pilih Rombel / Kelas:</label>
                            <select name="raport_kelas" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Kelas 7</option>
                                <option value="VII-A" <?= $raport_kelas === 'VII-A' ? 'selected' : '' ?>>Kelas VII-A (7A)</option>
                                <option value="VII-B" <?= $raport_kelas === 'VII-B' ? 'selected' : '' ?>>Kelas VII-B (7B)</option>
                                <option value="VII-C" <?= $raport_kelas === 'VII-C' ? 'selected' : '' ?>>Kelas VII-C (7C)</option>
                            </select>
                        </div>

                        <!-- Pilihan Siswa -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Pilih Siswa <span class="text-danger">*</span>:</label>
                            <select name="raport_siswa" class="form-select" required>
                                <option value="">-- Pilih Nama Siswa (<?= count($raport_students_list) ?> Siswa) --</option>
                                <?php foreach ($raport_students_list as $st): ?>
                                    <option value="<?= $st['id_siswa'] ?>" <?= $raport_siswa_id === intval($st['id_siswa']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($st['nama_siswa']) ?> (<?= htmlspecialchars($st['kelas']) ?> - NIS: <?= htmlspecialchars($st['nis']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tipe Periode -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-dark small">Tipe Periode:</label>
                            <select name="raport_tipe" id="raportTipeSelect" class="form-select" onchange="togglePeriodeInputs()">
                                <option value="bulanan" <?= $raport_tipe === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                                <option value="mingguan" <?= $raport_tipe === 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
                                <option value="semua" <?= $raport_tipe === 'semua' ? 'selected' : '' ?>>Semua Waktu</option>
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
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary fw-bold w-100 py-2" title="Tampilkan Pratinjau">
                                <i class="bi bi-eye-fill"></i>
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
                                    NIS: <strong><?= htmlspecialchars($raport_siswa['nis']) ?></strong>
                                    <?php if (!empty($raport_siswa['nisn'])): ?>
                                        &bull; NISN: <strong><?= htmlspecialchars($raport_siswa['nisn']) ?></strong>
                                    <?php endif; ?>
                                    &bull; Kelas: <strong><?= htmlspecialchars($raport_siswa['kelas']) ?></strong>
                                    &bull; SMP Swasta Nommensen
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
                                            <th class="py-2 px-3 text-center" style="width: 90px;">KKM</th>
                                            <th class="py-2 px-3 text-center" style="width: 90px;">Skor</th>
                                            <th class="py-2 px-3 text-center" style="width: 100px;">Predikat</th>
                                            <th class="py-2 px-3 text-center" style="width: 120px;">Keterangan</th>
                                            <th class="py-2 px-3" style="width: 220px;">Tanggal &amp; Jam Pengerjaan</th>
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
                                                    <!-- TANGGAL & JAM PENGERJAAN LENGKAP -->
                                                    <td class="py-2 px-3">
                                                        <div class="fw-semibold text-dark">
                                                            <i class="bi bi-calendar-event text-primary me-1"></i>
                                                            <?= date('d M Y', strtotime($kr['waktu_selesai'])) ?>
                                                        </div>
                                                        <div class="small text-muted">
                                                            <i class="bi bi-clock me-1"></i>
                                                            Pukul <?= date('H:i:s', strtotime($kr['waktu_selesai'])) ?> WIB
                                                        </div>
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
                                    <strong>Panduan Cetak Raport Resmi:</strong> Klik tombol <strong>"Cetak Raport PDF"</strong> di atas untuk membuka lembar raport standar A4 yang dilengkapi Kop Surat resmi SMP Swasta Nommensen, identitas siswa, rincian tanggal &amp; jam ujian, serta tanda tangan guru &amp; kepala sekolah.
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
                            <h4 class="fw-bold text-dark">Pilih Kelas, Siswa &amp; Periode Raport</h4>
                            <p class="text-muted mx-auto" style="max-width: 500px;">
                                Silakan pilih kelas (7A, 7B, 7C), nama siswa, dan tipe periode (Mingguan / Bulanan / Semua Riwayat) pada formulir di atas untuk melihat pratinjau nilai dan mencetak raport PDF resmi.
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