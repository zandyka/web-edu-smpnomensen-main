<?php
/**
 * File: admin/laporan_nilai.php
 * Deskripsi: Halaman Laporan Hasil Nilai Siswa.
 *            Guru dapat memantau seluruh nilai kuis siswa, lengkap dengan filter
 *            pencarian berdasarkan siswa maupun berdasarkan kuis.
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';

// Memanggil koneksi database
require_once '../config.php';

// Menangkap parameter filter
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

// Menyusun SQL Query dinamis berdasarkan filter
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai Siswa - Nommensen Admin</title>
    
    <!-- Impor Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Memanggil CSS Utama -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <!-- Memanggil Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            flex-grow: 1;
            padding: 2.5rem 3rem;
            overflow-y: visible;
            min-width: 0;
        }

        .form-control {
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            outline: none;
            font-family: inherit;
        }

        .form-control:focus {
            border-color: var(--accent-blue);
        }

        /* Form Filter Inline */
        .filter-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: #475569;
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
            <div style="margin-bottom: 2rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.6rem; font-weight: 800; color: #111827;">Laporan Hasil Nilai Siswa</h2>
                <p style="color: #6b7280; font-size: 0.95rem; margin-top: 0.25rem;">Gunakan filter pencarian di bawah untuk menyaring nilai kuis per siswa atau per kategori kuis.</p>
            </div>

            <!-- Form Filter Pencarian -->
            <div class="filter-card">
                <form action="laporan_nilai.php" method="GET" class="filter-form">
                    
                    <!-- Filter Siswa -->
                    <div class="filter-group">
                        <label>Filter Berdasarkan Siswa:</label>
                        <select name="id_siswa" class="form-control">
                            <option value="0">-- Semua Siswa --</option>
                            <?php foreach ($students_list as $student): ?>
                                <option value="<?= $student['id_siswa'] ?>" <?= $filter_siswa === intval($student['id_siswa']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($student['nama_siswa']) ?> (NIS: <?= htmlspecialchars($student['nis']) ?> - Kelas: <?= htmlspecialchars($student['kelas']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Filter Kuis -->
                    <div class="filter-group">
                        <label>Filter Berdasarkan Kuis:</label>
                        <select name="id_kuis" class="form-control">
                            <option value="0">-- Semua Kuis --</option>
                            <?php foreach ($quizzes_list as $quiz): ?>
                                <option value="<?= $quiz['id_kuis'] ?>" <?= $filter_kuis === intval($quiz['id_kuis']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($quiz['judul_kuis']) ?> (<?= htmlspecialchars($quiz['kategori_materi']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tombol Filter -->
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn-sm btn-play" style="padding: 0.75rem 1.5rem; font-weight: 700; border: none; cursor: pointer;">
                            Filter Data
                        </button>
                        <?php if ($filter_siswa > 0 || $filter_kuis > 0): ?>
                            <a href="laporan_nilai.php" class="btn-sm" style="background-color: #6b7280; color: white; text-decoration: none; padding: 0.75rem 1.5rem; font-weight: 700; text-align: center; border-radius: 10px; border: 2px solid #4b5563;">
                                Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Tabel Daftar Laporan Nilai -->
            <div class="table-container" style="border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm); border-radius: 12px;">
                <table class="table-materi">
                    <thead>
                        <tr>
                            <th style="width: 25%; font-weight: 700;">Nama Siswa</th>
                            <th style="width: 10%; font-weight: 700; text-align: center;">NIS</th>
                            <th style="width: 10%; font-weight: 700; text-align: center;">Kelas</th>
                            <th style="width: 25%; font-weight: 700;">Nama Kuis</th>
                            <th style="width: 10%; font-weight: 700; text-align: center;">Skor</th>
                            <th style="width: 20%; font-weight: 700; text-align: center;">Detail Jawaban</th>
                            <th style="width: 20%; font-weight: 700; text-align: center;">Tanggal Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($results)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #6b7280; padding: 3rem 2rem;">
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
                                    <td style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($row['nama_siswa']) ?></td>
                                    <td style="text-align: center;"><?= htmlspecialchars($row['nis']) ?></td>
                                    <td style="text-align: center;"><?= htmlspecialchars($row['kelas']) ?></td>
                                    <td style="font-weight: 600; color: var(--accent-blue);"><?= htmlspecialchars($row['judul_kuis']) ?></td>
                                    <td style="text-align: center; font-weight: 800; font-size: 1.05rem; color: <?= $score_color ?>;">
                                        <?= $row['skor'] ?>
                                    </td>
                                    <td style="text-align: center; font-size: 0.85rem; color: #475569; font-weight: 600;">
                                        <span style="color: #16a34a;">Benar: <?= $row['jumlah_benar'] ?></span> / 
                                        <span style="color: #ef4444;">Salah: <?= $row['jumlah_salah'] ?></span>
                                    </td>
                                    <td style="text-align: center; font-size: 0.85rem; color: #6b7280;">
                                        <?= date('d M Y - H:i', strtotime($row['waktu_selesai'])) ?> WIB
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Footer Bawah (Sesuai Storyboard) -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>
    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
