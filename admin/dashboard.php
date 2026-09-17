<?php
/**
 * File: admin/dashboard.php
 * Deskripsi: Halaman Dashboard Utama Administrator / Panel Guru.
 *            Menyajikan statistik ringkas siswa, materi, kuis, rata-rata nilai,
 *            dan log aktivitas gabungan terbaru (login, pengerjaan kuis, tambah materi).
 */

require_once '../includes/auth_admin.php';
require_once '../config.php';

// 1. Menghitung Statistik Ringkas
try {
    $total_siswa = $pdo->query("SELECT COUNT(*) FROM tb_siswa")->fetchColumn();
    $total_materi = $pdo->query("SELECT COUNT(*) FROM tb_materi")->fetchColumn();
    $total_kuis = $pdo->query("SELECT COUNT(*) FROM tb_kuis")->fetchColumn();
    
    $avg_score_raw = $pdo->query("SELECT AVG(skor) FROM tb_hasil")->fetchColumn();
    $rata_rata_nilai = $avg_score_raw !== null ? round($avg_score_raw, 1) : 0;
} catch (PDOException $e) {
    die("Error database saat memuat statistik: " . $e->getMessage());
}

// 2. Fetch Log Aktivitas Terakhir
$activities = [];
try {
    $sql_union = "
        SELECT 'kuis' as tipe, h.waktu_selesai as tanggal, s.nama_siswa, s.kelas, k.judul_kuis as detail, h.skor
        FROM tb_hasil h
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        
        UNION ALL
        
        SELECT 'materi' as tipe, m.created_at as tanggal, '' as nama_siswa, '' as kelas, CONCAT(m.kategori, ' - ', m.judul_materi) as detail, 0 as skor
        FROM tb_materi m
        
        UNION ALL
        
        SELECT 'login' as tipe, s.last_login as tanggal, s.nama_siswa, s.kelas, 'Siswa Login' as detail, 0 as skor
        FROM tb_siswa s
        WHERE s.last_login IS NOT NULL
        
        ORDER BY tanggal DESC
        LIMIT 10
    ";
    
    $stmt_union = $pdo->query($sql_union);
    $activities = $stmt_union->fetchAll();
} catch (PDOException $e) {
    $activities = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrator - Panel Guru</title>
    
    <!-- Impor Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Memanggil Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Memanggil CSS Utama -->
    <link rel="stylesheet" href="../assets/css/style.css?v=4.3.0">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
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

        /* Sidebar Navigasi Abu-Abu (Sesuai Storyboard) */
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
            font-size: 1.1rem;
            font-weight: 800;
            color: #475569;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 0.75rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex: 1;
        }

        .sidebar-menu li {
            margin-bottom: 0.6rem;
        }

        .sidebar-menu a {
            color: #374151;
            text-decoration: none;
            padding: 0.7rem 1rem;
            border: 2px solid #9ca3af;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 700;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
            font-size: 0.88rem;
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
            padding: 0.7rem 1rem;
            border-radius: 4px;
            text-align: center;
            font-weight: 700;
            transition: background-color 0.2s;
            border: 2px solid #dc2626;
            margin-top: 1.25rem;
            display: block;
        }

        .btn-logout-sidebar:hover {
            background-color: #b91c1c;
        }

        /* Area Konten Utama */
        .main-content {
            flex-grow: 1;
            padding: 2.5rem 2.5rem;
            overflow-y: visible;
            min-width: 0;
        }

        /* Card Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            border: 2px solid #374151;
            border-radius: 4px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        .stat-card-header {
            background-color: #e5e7eb;
            border-bottom: 2px solid #374151;
            padding: 0.65rem 1rem;
            text-align: center;
            font-weight: 700;
            color: #374151;
            font-size: 0.95rem;
        }

        .stat-card-body {
            padding: 1.5rem;
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            color: #111827;
            font-family: 'Outfit', sans-serif;
        }

        /* Baris Aktivitas */
        .activity-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }

        .activity-row {
            display: flex;
            border: 2px solid #374151;
            border-radius: 4px;
            overflow: hidden;
        }

        .activity-row-gray {
            background-color: #e5e7eb;
        }

        .activity-row-white {
            background-color: #ffffff;
        }

        .activity-time {
            color: #374151;
            padding: 0.75rem 1rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-align: center;
            border-right: 2px solid #374151;
            min-width: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #cbd5e1;
        }

        .activity-text {
            padding: 0.75rem 1.25rem;
            font-weight: 500;
            color: #1f2937;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body class="admin-body">

    <!-- Header Atas (Sesuai Storyboard) -->
    <header class="top-header">
        Dashboard Administrator - Panel Guru
    </header>

    <div class="admin-layout">
        <!-- Sidebar Navigasi Kiri (PERSIS 7 Menu Sesuai Tugas) -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h3 class="d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-shield-check"></i>
                    <span>ADMIN NOMMENSEN</span>
                </h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
                    <li><a href="kelola_materi.php"><i class="bi bi-book-half"></i>Kelola Materi</a></li>
                    <li><a href="upload_media.php"><i class="bi bi-cloud-arrow-up-fill"></i>Upload Media</a></li>
                    <li><a href="kelola_soal.php"><i class="bi bi-pencil-square"></i>Kelola Soal</a></li>
                    <li><a href="laporan_nilai.php"><i class="bi bi-bar-chart-line-fill"></i>Laporan Nilai</a></li>
                    <li><a href="pengaturan.php"><i class="bi bi-gear-fill"></i>Pengaturan</a></li>
                    <li><a href="kelola_siswa.php"><i class="bi bi-people-fill"></i>Kelola Data Siswa</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="bi bi-box-arrow-left me-1"></i>Keluar (Logout)
            </a>
        </aside>

        <!-- Area Konten Utama Kanan -->
        <main class="main-content">
            <div class="mb-4">
                <h2 class="h4 fw-bold text-dark mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?>! 👋</h2>
                <p class="text-secondary small mb-0">Gunakan panel ini untuk mengelola siswa, mengunggah materi pembelajaran multimedia, dan mengevaluasi hasil nilai kuis.</p>
            </div>

            <!-- Grid Statistik Ringkas (Bootstrap 5) -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
                <!-- 1. Total Siswa -->
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small fw-bold">Total Siswa</span>
                            <div class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="bi bi-people-fill fs-5"></i></div>
                        </div>
                        <div class="fs-3 fw-bold text-dark"><?= $total_siswa ?> <span class="fs-6 text-muted fw-normal">Orang</span></div>
                    </div>
                </div>

                <!-- 2. Materi Aktif -->
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small fw-bold">Materi Aktif</span>
                            <div class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="bi bi-journal-bookmark-fill fs-5"></i></div>
                        </div>
                        <div class="fs-3 fw-bold text-dark"><?= $total_materi ?> <span class="fs-6 text-muted fw-normal">Bab</span></div>
                    </div>
                </div>

                <!-- 3. Kuis Tersedia -->
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small fw-bold">Kuis Tersedia</span>
                            <div class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-circle"><i class="bi bi-patch-question-fill fs-5"></i></div>
                        </div>
                        <div class="fs-3 fw-bold text-dark"><?= $total_kuis ?> <span class="fs-6 text-muted fw-normal">Set</span></div>
                    </div>
                </div>

                <!-- 4. Rata-rata Nilai -->
                <div class="col">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary small fw-bold">Rata-rata Nilai</span>
                            <div class="badge bg-info-subtle text-info-emphasis p-2 rounded-circle"><i class="bi bi-graph-up-arrow fs-5"></i></div>
                        </div>
                        <div class="fs-3 fw-bold text-dark"><?= $rata_rata_nilai ?></div>
                    </div>
                </div>
            </div>

            <!-- Aktivitas Terakhir -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                <h3 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">
                    <i class="bi bi-clock-history me-1 text-primary"></i>Log Aktivitas Terakhir
                </h3>

                <div class="activity-container">
                    <?php if (empty($activities)): ?>
                        <div class="text-center text-muted py-4 border border-dashed rounded-3">
                            Belum ada riwayat aktivitas di sistem.
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $idx => $act): ?>
                            <?php 
                            $time_str = date('d M - H:i', strtotime($act['tanggal']));
                            $row_bg_class = ($idx % 2 === 0) ? 'activity-row-gray' : 'activity-row-white';
                            
                            $text = "";
                            if ($act['tipe'] === 'kuis') {
                                $text = "Siswa <strong>" . htmlspecialchars($act['nama_siswa']) . "</strong> (Kelas " . htmlspecialchars($act['kelas']) . ") selesai mengerjakan <strong>" . htmlspecialchars($act['detail']) . "</strong> dengan skor <strong>" . $act['skor'] . "</strong>";
                            } elseif ($act['tipe'] === 'materi') {
                                $text = "Materi baru ditambahkan: <strong>" . htmlspecialchars($act['detail']) . "</strong>";
                            } elseif ($act['tipe'] === 'login') {
                                $text = "Siswa <strong>" . htmlspecialchars($act['nama_siswa']) . "</strong> (Kelas " . htmlspecialchars($act['kelas']) . ") login ke sistem";
                            }
                            ?>
                            <div class="activity-row <?= $row_bg_class ?>">
                                <div class="activity-time"><?= $time_str ?></div>
                                <div class="activity-text"><?= $text ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer Bawah -->
    <footer class="bottom-footer text-center py-3 text-white border-top">
        <small>&copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen</small>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>