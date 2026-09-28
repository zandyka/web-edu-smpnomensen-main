<?php
/**
 * File: admin/kelola_siswa.php
 * Deskripsi: Halaman Pengelolaan Data Akun Siswa (CRUD) Khusus Kelas 7 (VII-A, VII-B, VII-C).
 *            Memungkinkan guru/admin menambah, mengubah (termasuk mengganti password dengan hash),
 *            dan menghapus data akun siswa dengan pemisahan per kelas 7A, 7B, dan 7C.
 */

// Memroteksi halaman ini agar hanya bisa diakses oleh guru yang sudah login
require_once '../includes/auth_admin.php';

// Memanggil koneksi database
require_once '../config.php';

$success_message = '';
$error_message = '';

// Filter kelas (VII-A, VII-B, VII-C)
$filter_kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';

// 1. PROSES TAMBAH / UPDATE AKUN SISWA (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_siswa'])) {
    $id_siswa = isset($_POST['id_siswa']) ? intval($_POST['id_siswa']) : 0;
    $nama_siswa = trim($_POST['nama_siswa'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $nis = trim($_POST['nis'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nama_siswa) || empty($kelas) || empty($nis)) {
        $error_message = "Nama, Kelas, dan NIS (Username) wajib diisi!";
    } elseif (!in_array($kelas, ['VII-A', 'VII-B', 'VII-C'])) {
        $error_message = "Kelas harus dipilih antara VII-A, VII-B, atau VII-C!";
    } else {
        try {
            // Cek keunikan NIS (kecuali jika mengedit dirinya sendiri)
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM tb_siswa WHERE nis = :nis AND id_siswa != :id");
            $stmt_check->execute(['nis' => $nis, 'id' => $id_siswa]);
            $is_exists = $stmt_check->fetchColumn();

            if ($is_exists > 0) {
                $error_message = "NIS (Username) '$nis' sudah terdaftar untuk siswa lain!";
            } else {
                if ($id_siswa > 0) {
                    // PROSES EDIT SISWA
                    if (!empty($password)) {
                        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_update = $pdo->prepare("
                            UPDATE tb_siswa 
                            SET nama_siswa = :nama, kelas = :kelas, nis = :nis, nisn = :nisn, password = :pass 
                            WHERE id_siswa = :id
                        ");
                        $stmt_update->execute([
                            'nama' => $nama_siswa,
                            'kelas' => $kelas,
                            'nis' => $nis,
                            'nisn' => !empty($nisn) ? $nisn : null,
                            'pass' => $password_hashed,
                            'id' => $id_siswa
                        ]);
                    } else {
                        $stmt_update = $pdo->prepare("
                            UPDATE tb_siswa 
                            SET nama_siswa = :nama, kelas = :kelas, nis = :nis, nisn = :nisn 
                            WHERE id_siswa = :id
                        ");
                        $stmt_update->execute([
                            'nama' => $nama_siswa,
                            'kelas' => $kelas,
                            'nis' => $nis,
                            'nisn' => !empty($nisn) ? $nisn : null,
                            'id' => $id_siswa
                        ]);
                    }
                    $success_message = "Data akun siswa berhasil diperbarui!";
                } else {
                    // PROSES TAMBAH SISWA BARU
                    if (empty($password)) {
                        $error_message = "Password wajib diisi untuk akun siswa baru!";
                    } else {
                        $password_hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt_insert = $pdo->prepare("
                            INSERT INTO tb_siswa (nama_siswa, kelas, nis, nisn, password) 
                            VALUES (:nama, :kelas, :nis, :nisn, :pass)
                        ");
                        $stmt_insert->execute([
                            'nama' => $nama_siswa,
                            'kelas' => $kelas,
                            'nis' => $nis,
                            'nisn' => !empty($nisn) ? $nisn : null,
                            'pass' => $password_hashed
                        ]);
                        $success_message = "Akun siswa baru kelas $kelas berhasil ditambahkan!";
                    }
                }

                if (empty($error_message)) {
                    $redir = "kelola_siswa.php?success=" . urlencode($success_message);
                    if (!empty($filter_kelas)) {
                        $redir .= "&kelas=" . urlencode($filter_kelas);
                    }
                    header("Location: " . $redir);
                    exit();
                }
            }
        } catch (PDOException $e) {
            $error_message = "Error database: " . $e->getMessage();
        }
    }
}

// 2. PROSES HAPUS AKUN SISWA (GET)
if (isset($_GET['delete_siswa_id'])) {
    $delete_id = intval($_GET['delete_siswa_id']);
    try {
        // Hapus hasil kuis siswa terlebih dahulu
        $stmt_del_h = $pdo->prepare("DELETE FROM tb_hasil WHERE id_siswa = :id");
        $stmt_del_h->execute(['id' => $delete_id]);

        $stmt_delete = $pdo->prepare("DELETE FROM tb_siswa WHERE id_siswa = :id");
        $stmt_delete->execute(['id' => $delete_id]);
        $success_message = "Akun siswa beserta riwayat nilainya berhasil dihapus!";
        
        $redir = "kelola_siswa.php?success=" . urlencode($success_message);
        if (!empty($filter_kelas)) {
            $redir .= "&kelas=" . urlencode($filter_kelas);
        }
        header("Location: " . $redir);
        exit();
    } catch (PDOException $e) {
        $error_message = "Gagal menghapus data siswa: " . $e->getMessage();
    }
}

// Menangkap parameter sukses redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// 3. READ DATA UNTUK FORM EDIT (GET)
$siswa_edit_data = null;
if (isset($_GET['edit_siswa_id'])) {
    $edit_id = intval($_GET['edit_siswa_id']);
    try {
        $stmt_get = $pdo->prepare("SELECT * FROM tb_siswa WHERE id_siswa = :id");
        $stmt_get->execute(['id' => $edit_id]);
        $siswa_edit_data = $stmt_get->fetch();
    } catch (PDOException $e) {
        $error_message = "Gagal memuat data edit: " . $e->getMessage();
    }
}

// 4. HITUNG JUMLAH SISWA PER KELAS 7A, 7B, 7C
try {
    $count_7a = $pdo->query("SELECT COUNT(*) FROM tb_siswa WHERE kelas = 'VII-A'")->fetchColumn();
    $count_7b = $pdo->query("SELECT COUNT(*) FROM tb_siswa WHERE kelas = 'VII-B'")->fetchColumn();
    $count_7c = $pdo->query("SELECT COUNT(*) FROM tb_siswa WHERE kelas = 'VII-C'")->fetchColumn();
    $count_all = $count_7a + $count_7b + $count_7c;
} catch (PDOException $e) {
    $count_7a = $count_7b = $count_7c = $count_all = 0;
}

// 5. READ DAFTAR SISWA SESUAI FILTER KELAS
try {
    $sql_students = "SELECT * FROM tb_siswa WHERE 1=1";
    $p_students = [];
    if (!empty($filter_kelas) && in_array($filter_kelas, ['VII-A', 'VII-B', 'VII-C'])) {
        $sql_students .= " AND kelas = :kelas";
        $p_students['kelas'] = $filter_kelas;
    }
    $sql_students .= " ORDER BY kelas ASC, nama_siswa ASC";
    $stmt_st = $pdo->prepare($sql_students);
    $stmt_st->execute($p_students);
    $students = $stmt_st->fetchAll();
} catch (PDOException $e) {
    die("Error database saat memuat daftar siswa: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Siswa Kelas 7 - Nommensen Admin</title>
    
    <!-- Impor Google Fonts -->
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

        .class-pill-btn {
            border-radius: 10px;
            padding: 0.6rem 1.25rem;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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
    </style>
</head>
<body class="admin-body">

    <!-- Header Atas -->
    <header class="top-header">
        Dashboard Administrator - Panel Guru
    </header>

    <div class="admin-layout">
        <!-- Sidebar Navigasi Kiri (7 Menu Sesuai Standar) -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h3>Admin Nommensen</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="kelola_materi.php">Kelola Materi</a></li>
                    <li><a href="upload_media.php">Upload Media</a></li>
                    <li><a href="kelola_soal.php">Kelola Soal</a></li>
                    <li><a href="laporan_nilai.php">Laporan Nilai</a></li>
                    <li><a href="pengaturan.php">Pengaturan</a></li>
                    <li><a href="kelola_siswa.php" class="active">Kelola Data Siswa</a></li>
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
                        Kelola Data Siswa Kelas 7
                    </h2>
                    <p style="color: #64748b; font-size: 0.95rem; margin-top: 0.35rem; margin-bottom: 0;">
                        Sistem difokuskan penuh untuk Kelas 7 dengan pemisahan rombongan belajar <strong>Kelas 7A, 7B, dan 7C</strong>.
                    </p>
                </div>
                <div>
                    <span class="badge bg-primary px-3 py-2 rounded-pill fs-6 fw-bold">
                        <i class="bi bi-people-fill me-1"></i> Total <?= $count_all ?> Siswa
                    </span>
                </div>
            </div>

            <!-- Pesan Umpan Balik Sukses/Error -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div><?= htmlspecialchars($error_message) ?></div>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success d-flex align-items-center gap-2 rounded-3 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div><?= htmlspecialchars($success_message) ?></div>
                </div>
            <?php endif; ?>

            <!-- Form Tambah / Edit Siswa -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex align-items-center gap-2 pb-3 mb-3 border-bottom">
                    <i class="bi bi-person-plus-fill text-primary fs-5"></i>
                    <h5 class="fw-bold text-dark mb-0">
                        <?= $siswa_edit_data ? 'Form Edit Akun Siswa' : 'Daftarkan Akun Siswa Baru (Kelas 7)' ?>
                    </h5>
                </div>

                <form action="kelola_siswa.php<?= !empty($filter_kelas) ? '?kelas=' . urlencode($filter_kelas) : '' ?>" method="POST">
                    <?php if ($siswa_edit_data): ?>
                        <input type="hidden" name="id_siswa" value="<?= $siswa_edit_data['id_siswa'] ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Nama Lengkap -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-secondary small">Nama Lengkap Siswa <span class="text-danger">*</span>:</label>
                            <input type="text" name="nama_siswa" class="form-control" placeholder="Contoh: Budi Santoso" required value="<?= htmlspecialchars($siswa_edit_data['nama_siswa'] ?? '') ?>">
                        </div>

                        <!-- Kelas (Dropdown 7A, 7B, 7C) -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">Pilih Rombel / Kelas <span class="text-danger">*</span>:</label>
                            <select name="kelas" class="form-select" required>
                                <option value="">-- Pilih Kelas --</option>
                                <option value="VII-A" <?= ($siswa_edit_data['kelas'] ?? ($filter_kelas === 'VII-A' ? 'VII-A' : '')) === 'VII-A' ? 'selected' : '' ?>>Kelas VII-A (7A)</option>
                                <option value="VII-B" <?= ($siswa_edit_data['kelas'] ?? ($filter_kelas === 'VII-B' ? 'VII-B' : '')) === 'VII-B' ? 'selected' : '' ?>>Kelas VII-B (7B)</option>
                                <option value="VII-C" <?= ($siswa_edit_data['kelas'] ?? ($filter_kelas === 'VII-C' ? 'VII-C' : '')) === 'VII-C' ? 'selected' : '' ?>>Kelas VII-C (7C)</option>
                            </select>
                        </div>

                        <!-- NIS (Username) -->
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-secondary small">NIS (Username) <span class="text-danger">*</span>:</label>
                            <input type="text" name="nis" class="form-control" placeholder="Contoh: 26001" required value="<?= htmlspecialchars($siswa_edit_data['nis'] ?? '') ?>">
                        </div>

                        <!-- NISN -->
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-secondary small">NISN (Opsional):</label>
                            <input type="text" name="nisn" class="form-control" placeholder="Contoh: 0136442625 (10 digit)" value="<?= htmlspecialchars($siswa_edit_data['nisn'] ?? '') ?>">
                        </div>

                        <!-- Password -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-secondary small">
                                Password: 
                                <?php if ($siswa_edit_data): ?>
                                    <span class="text-warning-emphasis fw-normal small">(Kosongkan jika tidak ingin mengubah password)</span>
                                <?php else: ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password akun..." <?= $siswa_edit_data ? '' : 'required' ?>>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="col-md-6 d-flex align-items-end gap-2">
                            <button type="submit" name="save_siswa" class="btn btn-primary fw-bold px-4 py-2">
                                <i class="bi bi-save me-1"></i>
                                <?= $siswa_edit_data ? 'Simpan Perubahan' : 'Daftarkan Akun Siswa' ?>
                            </button>
                            <?php if ($siswa_edit_data): ?>
                                <a href="kelola_siswa.php<?= !empty($filter_kelas) ? '?kelas=' . urlencode($filter_kelas) : '' ?>" class="btn btn-outline-secondary py-2">
                                    Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab Tombol Pemisahan Kelas 7A, 7B, dan 7C -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="kelola_siswa.php" class="class-pill-btn <?= empty($filter_kelas) ? 'active' : '' ?>">
                        <i class="bi bi-collection-fill"></i>
                        Semua Kelas 7 (<?= $count_all ?>)
                    </a>
                    <a href="kelola_siswa.php?kelas=VII-A" class="class-pill-btn <?= $filter_kelas === 'VII-A' ? 'active' : '' ?>">
                        <i class="bi bi-award-fill text-warning"></i>
                        Kelas VII-A / 7A (<?= $count_7a ?>)
                    </a>
                    <a href="kelola_siswa.php?kelas=VII-B" class="class-pill-btn <?= $filter_kelas === 'VII-B' ? 'active' : '' ?>">
                        <i class="bi bi-award-fill text-info"></i>
                        Kelas VII-B / 7B (<?= $count_7b ?>)
                    </a>
                    <a href="kelola_siswa.php?kelas=VII-C" class="class-pill-btn <?= $filter_kelas === 'VII-C' ? 'active' : '' ?>">
                        <i class="bi bi-award-fill text-success"></i>
                        Kelas VII-C / 7C (<?= $count_7c ?>)
                    </a>
                </div>

                <div class="text-secondary small">
                    Menampilkan <strong><?= count($students) ?></strong> siswa terdaftar
                </div>
            </div>

            <!-- Tabel Akun Siswa Terdaftar -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-3 text-center" style="width: 50px;">No</th>
                                <th class="py-3 px-3">Nama Siswa</th>
                                <th class="py-3 px-3 text-center" style="width: 140px;">Kelas</th>
                                <th class="py-3 px-3 text-center" style="width: 200px;">NIS (Username)</th>
                                <th class="py-3 px-3 text-center" style="width: 200px;">Login Terakhir</th>
                                <th class="py-3 px-3 text-center" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                                        Belum ada akun siswa pada kategori kelas ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($students as $student): ?>
                                    <?php
                                    $badge_class = 'bg-primary-subtle text-primary';
                                    if ($student['kelas'] === 'VII-A') $badge_class = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                                    elseif ($student['kelas'] === 'VII-B') $badge_class = 'bg-info-subtle text-info-emphasis border border-info-subtle';
                                    elseif ($student['kelas'] === 'VII-C') $badge_class = 'bg-success-subtle text-success-emphasis border border-success-subtle';
                                    ?>
                                    <tr>
                                        <td class="py-3 px-3 text-center text-secondary fw-semibold"><?= $no++ ?></td>
                                        <td class="py-3 px-3 fw-bold text-dark">
                                            <?= htmlspecialchars($student['nama_siswa']) ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="badge <?= $badge_class ?> px-3 py-2 rounded-pill fw-bold">
                                                <?= htmlspecialchars($student['kelas']) ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center font-monospace">
                                            <strong><?= htmlspecialchars($student['nis']) ?></strong>
                                            <?php if (!empty($student['nisn']) && $student['nisn'] !== $student['nis']): ?>
                                                <div class="text-muted small" style="font-family: inherit;">NISN: <?= htmlspecialchars($student['nisn']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center small text-muted">
                                            <?php if ($student['last_login']): ?>
                                                <i class="bi bi-clock-history me-1 text-primary"></i>
                                                <?= date('d M Y - H:i', strtotime($student['last_login'])) ?> WIB
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">Belum pernah login</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <div class="d-inline-flex gap-1">
                                                <a href="kelola_siswa.php?edit_siswa_id=<?= $student['id_siswa'] ?><?= !empty($filter_kelas) ? '&kelas=' . urlencode($filter_kelas) : '' ?>" class="btn btn-sm btn-outline-primary" title="Edit Akun">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="kelola_siswa.php?delete_siswa_id=<?= $student['id_siswa'] ?><?= !empty($filter_kelas) ? '&kelas=' . urlencode($filter_kelas) : '' ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus akun <?= htmlspecialchars(addslashes($student['nama_siswa'])) ?> beserta seluruh riwayat nilainya?')" title="Hapus Akun">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                                <a href="laporan_nilai.php?tab=raport&raport_siswa=<?= $student['id_siswa'] ?>" class="btn btn-sm btn-outline-success" title="Lihat Raport">
                                                    <i class="bi bi-file-earmark-person"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer Bawah -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>