<?php
/**
 * File: index.php
 * Deskripsi: Halaman Utama Aplikasi Pembelajaran Bahasa Inggris (SMP Swasta Nommensen).
 *            Menampilkan portal masuk siswa/guru dan Papan Peringkat (Leaderboard)
 *            nilai kuis tertinggi secara real-time.
 */

require_once 'config.php';

// Jalur ke file logo sekolah
$logo_path = 'assets/img/logo.png';
$has_logo = file_exists($logo_path);

// Query data perankingan: Siswa dengan nilai kuis tertinggi
$leaderboard = [];
try {
    $stmt_lead = $pdo->query("
        SELECT h.*, s.nama_siswa, s.nis, s.kelas, k.judul_kuis, k.kategori_materi
        FROM tb_hasil h
        JOIN tb_siswa s ON h.id_siswa = s.id_siswa
        JOIN tb_kuis k ON h.id_kuis = k.id_kuis
        ORDER BY h.skor DESC, h.jumlah_benar DESC, h.waktu_selesai ASC
        LIMIT 10
    ");
    $leaderboard = $stmt_lead->fetchAll();
} catch (PDOException $e) {
    $leaderboard = [];
}

// Hitung total kuis dikerjakan
$total_kuis_dikerjakan = 0;
try {
    $total_kuis_dikerjakan = $pdo->query("SELECT COUNT(*) FROM tb_hasil")->fetchColumn();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Memanggil CSS utama kustom -->
    <link rel="stylesheet" href="assets/css/style.css?v=4.5.0">

    <style>
        .leaderboard-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            background: #ffffff;
            overflow: hidden;
        }

        .podium-box {
            text-align: center;
            padding: 1.25rem 0.75rem;
            border-radius: 16px;
            position: relative;
            transition: transform 0.2s ease;
        }

        .podium-box:hover {
            transform: translateY(-3px);
        }

        .podium-1 {
            background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%);
            border: 2px solid #f59e0b;
        }

        .podium-2 {
            background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
            border: 2px solid #94a3b8;
        }

        .podium-3 {
            background: linear-gradient(180deg, #fff7ed 0%, #ffedd5 100%);
            border: 2px solid #f97316;
        }

        .rank-badge {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 800;
            font-size: 0.85rem;
        }

        .rank-1 { background-color: #f59e0b; color: #ffffff; }
        .rank-2 { background-color: #94a3b8; color: #ffffff; }
        .rank-3 { background-color: #d97706; color: #ffffff; }
        .rank-other { background-color: #f1f5f9; color: #475569; }

        .score-pill {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.05rem;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Header Atas (Sesuai Storyboard & Bootstrap 5) -->
    <header class="top-header text-center text-white py-3 shadow-sm">
        <span class="fs-5 fw-bold tracking-wide">Aplikasi Pembelajaran Bahasa Inggris &bull; SMP Swasta Nommensen</span>
    </header>

    <!-- Konten Utama Tengah -->
    <main class="container my-auto py-5">
        <div class="row g-4 align-items-stretch">
            
            <!-- Kolom Kiri: Kartu Portal Masuk (Login Siswa & Guru) -->
            <div class="col-12 col-lg-5 d-flex">
                <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 text-center bg-white w-100 d-flex flex-column justify-content-center">
                    
                    <!-- Logo Bulat -->
                    <div class="logo-circle mx-auto mb-3 shadow-sm" style="width: 100px; height: 100px;">
                        <?php if ($has_logo): ?>
                            <img src="<?= $logo_path ?>" alt="Logo SMP Swasta Nommensen" class="logo-img" style="max-height: 75px;">
                        <?php else: ?>
                            <span class="logo-text fw-bold">LOGO</span>
                        <?php endif; ?>
                    </div>

                    <!-- Judul & Selamat Datang -->
                    <h1 class="welcome-text fw-bold text-dark fs-4 mb-1">
                        Aplikasi Pembelajaran
                    </h1>
                    <p class="text-primary fw-bold fs-5 mb-1" style="font-family: 'Outfit', sans-serif;">
                        Bahasa Inggris Multimedia
                    </p>
                    <p class="school-text text-secondary fw-semibold small mb-4">SMP Swasta Nommensen</p>

                    <!-- Tombol Aksi Masuk -->
                    <div class="d-grid gap-3 col-12 mx-auto mb-3">
                        <a href="siswa/login.php" class="btn btn-primary btn-lg rounded-3 fw-bold py-3 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btn-siswa">
                            <i class="bi bi-mortarboard-fill fs-5"></i>
                            <span>Mulai Belajar (Siswa)</span>
                        </a>
                        <a href="admin/login.php" class="btn btn-outline-secondary btn-lg rounded-3 fw-semibold py-3 d-flex align-items-center justify-content-center gap-2" id="btn-guru">
                            <i class="bi bi-person-gear fs-5"></i>
                            <span>Login Guru / Admin</span>
                        </a>
                    </div>

                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>Kurikulum 20 Bab &bull; Laboratorium Audio & Video
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Papan Peringkat Kuis (Leaderboard Nilai Tertinggi) -->
            <div class="col-12 col-lg-7 d-flex">
                <div class="leaderboard-card w-100 p-4 d-flex flex-column">
                    
                    <!-- Header Leaderboard -->
                    <div class="d-flex justify-content-between align-items-center pb-3 mb-3 border-bottom">
                        <div>
                            <div class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-3 py-1 fw-bold mb-1">
                                <i class="bi bi-trophy-fill me-1 text-warning"></i>HALL OF FAME
                            </div>
                            <h2 class="h5 fw-bold text-dark mb-0">Papan Peringkat Kuis (Leaderboard)</h2>
                            <span class="small text-muted">Daftar siswa dengan capaian nilai tertinggi</span>
                        </div>
                        <div class="text-end d-none d-sm-block">
                            <span class="badge bg-primary text-white px-3 py-2 rounded-pill">
                                <?= $total_kuis_dikerjakan ?> Sesi Selesai
                            </span>
                        </div>
                    </div>

                    <?php if (empty($leaderboard)): ?>
                        <!-- Jika Belum Ada Siswa yang Mengerjakan Kuis -->
                        <div class="text-center py-5 my-auto text-muted">
                            <i class="bi bi-award fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                            <h3 class="h6 fw-bold text-dark">Belum Ada Riwayat Kuis</h3>
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
                        <div class="table-responsive flex-grow-1" style="max-height: 280px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 12%; text-align: center;">Rank</th>
                                        <th style="width: 40%;">Nama Siswa</th>
                                        <th style="width: 28%;">Kuis</th>
                                        <th style="width: 20%; text-align: center;">Skor</th>
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
                                                    NIS: <?= htmlspecialchars($row['nis']) ?> &bull; Kelas: <?= htmlspecialchars($row['kelas']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 160px;" title="<?= htmlspecialchars($row['judul_kuis']) ?>">
                                                    <span class="badge bg-light text-secondary border"><?= htmlspecialchars($row['kategori_materi']) ?></span>
                                                </div>
                                                <span class="text-muted" style="font-size: 0.72rem;">
                                                    <?= date('d M Y', strtotime($row['waktu_selesai'])) ?>
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
    <footer class="bottom-footer text-center py-3 text-white border-top">
        <small>&copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen</small>
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>