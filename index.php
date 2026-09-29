<?php
/**
 * File: index.php
 * Deskripsi: Halaman Utama Aplikasi Pembelajaran Bahasa Inggris (SMP Swasta Nommensen).
 *            Menampilkan portal masuk siswa/guru dan Papan Peringkat (Leaderboard)
 *            nilai kuis tertinggi secara real-time dengan pemisahan Kelas 7A, 7B, 7C
 *            serta tanggal & jam pengerjaan kuis.
 */

require_once 'config.php';

// Jalur ke file logo sekolah
$logo_path = 'assets/img/logo.png';
$has_logo = file_exists($logo_path);

// Filter per rombel / kelas 7
$filter_kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
if (!in_array($filter_kelas, ['VII-A', 'VII-B', 'VII-C'])) {
    $filter_kelas = '';
}

// Query data perankingan: Siswa dengan nilai kuis tertinggi
$leaderboard = [];
try {
    $sql_lead = "
        SELECT h.*, s.nama_siswa, s.nis, s.kelas, k.judul_kuis, k.kategori_materi
        FROM tb_hasil h
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        WHERE s.kelas LIKE 'VII-%'
    ";
    $p_lead = [];
    if (!empty($filter_kelas)) {
        $sql_lead .= " AND s.kelas = :kelas";
        $p_lead['kelas'] = $filter_kelas;
    }
    $sql_lead .= " ORDER BY h.skor DESC, h.jumlah_benar DESC, h.waktu_selesai ASC LIMIT 10";

    $stmt_lead = $pdo->prepare($sql_lead);
    $stmt_lead->execute($p_lead);
    $leaderboard = $stmt_lead->fetchAll();
} catch (PDOException $e) {
    $leaderboard = [];
}

// Hitung total kuis dikerjakan
$total_kuis_dikerjakan = 0;
try {
    $total_kuis_dikerjakan = $pdo->query("SELECT COUNT(*) FROM tb_hasil h JOIN tb_siswa s ON h.id_siswa = s.id_siswa WHERE s.kelas LIKE 'VII-%'")->fetchColumn();
} catch (PDOException $e) {
    $total_kuis_dikerjakan = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi Pembelajaran Bahasa Inggris Berbasis Multimedia pada SMP Swasta Nommensen Menggunakan Metode MDLC.">
    <title>Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen</title>
    
    <!-- Memanggil Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Memanggil Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Memanggil CSS utama kustom -->
    <link rel="stylesheet" href="assets/css/style.css?v=4.5.0">

    <style>
        body.index-body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .welcome-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .leaderboard-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        .podium-box {
            border-radius: 12px;
            padding: 0.65rem 0.5rem;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .podium-box:hover {
            transform: translateY(-2px);
        }

        .podium-1 {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
        }

        .podium-2 {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            border: 2px solid #94a3b8;
        }

        .podium-3 {
            background: linear-gradient(135deg, #ffedd5 0%, #fed7aa 100%);
            border: 2px solid #f97316;
        }

        .rank-badge {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.8rem;
        }

        .rank-1 { background-color: #fef08a; color: #854d0e; }
        .rank-2 { background-color: #e2e8f0; color: #334155; }
        .rank-3 { background-color: #ffedd5; color: #9a3412; }
        .rank-other { background-color: #f1f5f9; color: #64748b; }

        .leaderboard-scroll::-webkit-scrollbar {
            width: 6px;
        }
        .leaderboard-scroll::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .leaderboard-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .leaderboard-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="index-body">

    <!-- Navigasi Bar Atas Sederhana -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2 px-3 flex-shrink-0 z-3 shadow-sm">
        <div class="container-fluid px-2 px-md-4 d-flex justify-content-between align-items-center">
            <span class="navbar-brand mb-0 h1 fs-6 fw-bold text-white">
                <i class="bi bi-mortarboard-fill me-2 text-warning"></i>SMP SWASTA NOMMENSEN &bull; PORTAL KELAS VII
            </span>
            <span class="text-white-50 small d-none d-md-inline">Media Instruksional Mandiri Berbasis Multimedia</span>
        </div>
    </nav>

    <!-- Kontainer Konten Utama -->
    <main class="flex-grow-1 d-flex flex-column justify-content-center py-3 py-lg-4">
        <div class="container-xl my-auto">
            <div class="row g-3 g-lg-4 align-items-stretch justify-content-center">
                
                <!-- Kolom Kiri: Sambutan & Akses Masuk Portal -->
                <div class="col-12 col-lg-5 d-flex">
                    <div class="welcome-card card border-0 rounded-4 p-4 text-center bg-white w-100 d-flex flex-column justify-content-center">
                        
                        <!-- Logo Bulat -->
                        <div class="logo-circle mx-auto mb-2.5 shadow-sm" style="width: 80px; height: 80px; border-radius: 50%; background: #ffffff; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; padding: 4px;">
                            <?php if ($has_logo): ?>
                                <img src="<?= $logo_path ?>" alt="Logo SMP Swasta Nommensen" class="logo-img" style="max-height: 56px; width: auto; object-fit: contain;">
                            <?php else: ?>
                                <span class="logo-text fw-bold text-primary">SMP</span>
                            <?php endif; ?>
                        </div>

                        <!-- Judul & Selamat Datang -->
                        <h1 class="welcome-text fw-bold text-dark fs-5 mb-1">
                            Aplikasi Pembelajaran
                        </h1>
                        <p class="text-primary fw-bold fs-5 mb-1" style="font-family: 'Outfit', sans-serif;">
                            Bahasa Inggris Multimedia (Kelas VII)
                        </p>
                        <p class="school-text text-secondary fw-semibold small mb-3">SMP Swasta Nommensen</p>

                        <!-- Tombol Aksi Masuk -->
                        <div class="d-grid gap-2 col-12 mx-auto mb-3">
                            <a href="siswa/login.php" class="btn btn-primary rounded-3 fw-bold py-2.5 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btn-siswa">
                                <i class="bi bi-mortarboard-fill fs-5"></i>
                                <span>Mulai Belajar (Siswa Kelas 7)</span>
                            </a>
                            <a href="admin/login.php" class="btn btn-outline-secondary rounded-3 fw-semibold py-2 d-flex align-items-center justify-content-center gap-2" id="btn-guru">
                                <i class="bi bi-person-gear fs-5"></i>
                                <span>Login Guru / Admin</span>
                            </a>
                        </div>

                        <div class="text-muted small">
                            <div><i class="bi bi-layers me-1 text-primary"></i>Rombel VII-A, VII-B, VII-C &bull; Kurikulum 20 Bab</div>
                            <div class="mt-1" style="font-size: 0.75rem;"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Kec. Babul Makmur, Kab. Aceh Tenggara</div>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Papan Peringkat Kuis (Leaderboard Nilai Tertinggi) -->
                <div class="col-12 col-lg-7 d-flex">
                    <div class="leaderboard-card w-100 p-3 p-md-4 d-flex flex-column">
                    
                    <!-- Header Leaderboard -->
                    <div class="d-flex justify-content-between align-items-center pb-2 mb-2 border-bottom">
                        <div>
                            <div class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1 fw-bold mb-1">
                                <i class="bi bi-trophy-fill me-1 text-warning"></i>LEADERBOARD KELAS 7
                            </div>
                            <h2 class="h5 fw-bold text-dark mb-0">Papan Peringkat Nilai Tertinggi</h2>
                            <span class="small text-muted">Daftar siswa dengan capaian nilai kuis terbaik</span>
                        </div>
                        <div class="text-end d-none d-sm-block">
                            <span class="badge bg-primary text-white px-3 py-2 rounded-pill">
                                <?= $total_kuis_dikerjakan ?> Sesi Selesai
                            </span>
                        </div>
                    </div>

                    <!-- Filter Pemisahan Kelas 7A, 7B, 7C -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <a href="index.php" class="btn btn-sm <?= empty($filter_kelas) ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Semua Kelas 7
                        </a>
                        <a href="index.php?kelas=VII-A" class="btn btn-sm <?= $filter_kelas === 'VII-A' ? 'btn-warning text-dark' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Kelas VII-A (7A)
                        </a>
                        <a href="index.php?kelas=VII-B" class="btn btn-sm <?= $filter_kelas === 'VII-B' ? 'btn-info text-dark' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Kelas VII-B (7B)
                        </a>
                        <a href="index.php?kelas=VII-C" class="btn btn-sm <?= $filter_kelas === 'VII-C' ? 'btn-success text-white' : 'btn-outline-secondary' ?> rounded-pill px-3 fw-bold">
                            Kelas VII-C (7C)
                        </a>
                    </div>

                    <?php if (empty($leaderboard)): ?>
                        <!-- Jika Belum Ada Siswa yang Mengerjakan Kuis -->
                        <div class="text-center py-5 my-auto text-muted">
                            <i class="bi bi-award fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                            <h3 class="h6 fw-bold text-dark">Belum Ada Riwayat Kuis <?= !empty($filter_kelas) ? "di Kelas $filter_kelas" : "" ?></h3>
                            <p class="small text-muted mb-0">Jadilah siswa pertama yang mengerjakan kuis dan memimpin papan peringkat!</p>
                        </div>
                    <?php else: ?>

                        <!-- Podium Top 3 (Jika Data Tersedia >= 1) -->
                        <div class="row g-2 mb-3">
                            
                            <!-- Juara 2 (Perak) -->
                            <?php if (isset($leaderboard[1])): ?>
                                <div class="col-4">
                                    <div class="podium-box podium-2 h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="fs-4">🥈</div>
                                            <div class="fw-bold text-dark text-truncate small" title="<?= htmlspecialchars($leaderboard[1]['nama_siswa']) ?>">
                                                <?= htmlspecialchars($leaderboard[1]['nama_siswa']) ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                Kelas <?= htmlspecialchars($leaderboard[1]['kelas']) ?>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-secondary text-white fw-bold">
                                                <?= $leaderboard[1]['skor'] ?> Poin
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Juara 1 (Emas - Posisi Tengah Menonjol) -->
                            <?php if (isset($leaderboard[0])): ?>
                                <div class="col-4">
                                    <div class="podium-box podium-1 h-100 d-flex flex-column justify-content-between shadow-xs">
                                        <div>
                                            <div class="fs-3">🥇</div>
                                            <div class="fw-bold text-dark text-truncate" title="<?= htmlspecialchars($leaderboard[0]['nama_siswa']) ?>">
                                                <?= htmlspecialchars($leaderboard[0]['nama_siswa']) ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                Kelas <?= htmlspecialchars($leaderboard[0]['kelas']) ?>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-warning text-dark fw-bold px-3 py-2 fs-6">
                                                <?= $leaderboard[0]['skor'] ?> Poin
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Juara 3 (Perunggu) -->
                            <?php if (isset($leaderboard[2])): ?>
                                <div class="col-4">
                                    <div class="podium-box podium-3 h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="fs-4">🥉</div>
                                            <div class="fw-bold text-dark text-truncate small" title="<?= htmlspecialchars($leaderboard[2]['nama_siswa']) ?>">
                                                <?= htmlspecialchars($leaderboard[2]['nama_siswa']) ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                Kelas <?= htmlspecialchars($leaderboard[2]['kelas']) ?>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge bg-danger-subtle text-danger fw-bold">
                                                <?= $leaderboard[2]['skor'] ?> Poin
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>

                        <!-- Tabel Daftar Peringkat Lengkap -->
                        <div class="table-responsive flex-grow-1 leaderboard-scroll" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 10%; text-align: center;">Rank</th>
                                        <th style="width: 38%;">Nama Siswa</th>
                                        <th style="width: 27%;">Kuis &amp; Waktu</th>
                                        <th style="width: 25%; text-align: center;">Skor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($leaderboard as $idx => $row): ?>
                                        <?php 
                                        $rank = $idx + 1;
                                        $rank_class = $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : 'rank-other'));
                                        $medal_icon = $rank === 1 ? '🥇' : ($rank === 2 ? '🥈' : ($rank === 3 ? '🥉' : '#' . $rank));
                                        ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <span class="rank-badge <?= $rank_class ?>"><?= $medal_icon ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark text-truncate" style="max-width: 180px;">
                                                    <?= htmlspecialchars($row['nama_siswa']) ?>
                                                </div>
                                                <div class="text-muted" style="font-size: 0.75rem;">
                                                    NIS: <?= htmlspecialchars($row['nis']) ?> &bull; 
                                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['kelas']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate fw-semibold text-primary" style="max-width: 160px;" title="<?= htmlspecialchars($row['judul_kuis']) ?>">
                                                    <?= htmlspecialchars($row['judul_kuis']) ?>
                                                </div>
                                                <span class="text-muted" style="font-size: 0.72rem;">
                                                    <i class="bi bi-clock me-1"></i><?= date('d M Y - H:i', strtotime($row['waktu_selesai'])) ?> WIB
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <span class="badge <?= $row['skor'] >= 70 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' ?> fw-bold fs-6 px-2 py-1">
                                                    <?= $row['skor'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php endif; ?>

                </div>
            </div>

        </div>
    </main>

    <!-- Footer Bawah -->
    <footer class="bottom-footer text-center py-2.5 text-white border-top flex-shrink-0">
        <small>&copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen</small>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>