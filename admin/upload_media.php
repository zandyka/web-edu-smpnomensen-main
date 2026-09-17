<?php
/**
 * File: admin/upload_media.php
 * Deskripsi: Halaman Upload & Manajemen Media Pembelajaran (Audio & Video) untuk Panel Guru Admin.
 *            Memungkinkan guru mengunggah media langsung ke sistem dan database secara otomatis
 *            tanpa perlu membuat unit materi terlebih dahulu di menu Kelola Materi.
 */

// Memroteksi halaman admin
require_once '../includes/auth_admin.php';
require_once '../config.php';

$error_message = '';
$success_message = '';

// Folder penyimpanan berkas media fisik
$audio_upload_dir = '../assets/audio/';
$video_upload_dir = '../assets/video/';

// Pastikan folder penyimpanan media ada di server
if (!is_dir($audio_upload_dir)) {
    mkdir($audio_upload_dir, 0777, true);
}
if (!is_dir($video_upload_dir)) {
    mkdir($video_upload_dir, 0777, true);
}

// ---------------------------------------------------------
// 1. PROSES UPLOAD MEDIA LANGSUNG (POST: upload_media)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_media'])) {
    $media_type = trim($_POST['media_type'] ?? 'audio');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $id_materi_input = isset($_POST['id_materi']) && $_POST['id_materi'] !== '' ? intval($_POST['id_materi']) : null;

    if (empty($keterangan)) {
        $error_message = "Judul atau keterangan media wajib diisi!";
    } else {
        if ($media_type === 'audio') {
            // Validasi File Audio
            if (!isset($_FILES['file_media']) || $_FILES['file_media']['error'] !== UPLOAD_ERR_OK) {
                $error_message = "Silakan pilih berkas file audio yang valid untuk diunggah!";
            } else {
                $file_tmp = $_FILES['file_media']['tmp_name'];
                $orig_name = $_FILES['file_media']['name'];
                $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                $allowed_audio_exts = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];

                if (!in_array($ext, $allowed_audio_exts)) {
                    $error_message = "Format audio tidak didukung! Format yang diperbolehkan: mp3, wav, ogg, m4a, aac.";
                } else {
                    $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($orig_name, PATHINFO_FILENAME));
                    $safe_basename = substr($safe_basename, 0, 30);
                    $new_file_name = 'audio_' . time() . '_' . $safe_basename . '.' . $ext;

                    if (move_uploaded_file($file_tmp, $audio_upload_dir . $new_file_name)) {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO tb_audio (id_materi, file_audio, keterangan) VALUES (:id_materi, :file, :ket)");
                            $stmt->bindValue(':id_materi', $id_materi_input, $id_materi_input !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                            $stmt->bindValue(':file', $new_file_name, PDO::PARAM_STR);
                            $stmt->bindValue(':ket', $keterangan, PDO::PARAM_STR);
                            $stmt->execute();

                            $success_message = "Berkas audio (" . htmlspecialchars($orig_name) . ") berhasil diunggah dan otomatis tersimpan ke database!";
                            header("Location: upload_media.php?success=" . urlencode($success_message));
                            exit();
                        } catch (PDOException $e) {
                            @unlink($audio_upload_dir . $new_file_name);
                            $error_message = "Gagal menyimpan data ke database: " . $e->getMessage();
                        }
                    } else {
                        $error_message = "Gagal memindahkan berkas audio ke server. Pastikan folder assets/audio dapat ditulis.";
                    }
                }
            }
        } elseif ($media_type === 'video') {
            // Validasi File Video
            if (!isset($_FILES['file_media']) || $_FILES['file_media']['error'] !== UPLOAD_ERR_OK) {
                $error_message = "Silakan pilih berkas file video yang valid untuk diunggah!";
            } else {
                $file_tmp = $_FILES['file_media']['tmp_name'];
                $orig_name = $_FILES['file_media']['name'];
                $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                $allowed_video_exts = ['mp4', 'webm', 'ogg', 'mkv'];

                if (!in_array($ext, $allowed_video_exts)) {
                    $error_message = "Format video tidak didukung! Format yang diperbolehkan: mp4, webm, ogg, mkv.";
                } else {
                    $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($orig_name, PATHINFO_FILENAME));
                    $safe_basename = substr($safe_basename, 0, 30);
                    $new_file_name = 'video_' . time() . '_' . $safe_basename . '.' . $ext;

                    if (move_uploaded_file($file_tmp, $video_upload_dir . $new_file_name)) {
                        try {
                            $stmt = $pdo->prepare("INSERT INTO tb_video (id_materi, file_video, keterangan) VALUES (:id_materi, :file, :ket)");
                            $stmt->bindValue(':id_materi', $id_materi_input, $id_materi_input !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                            $stmt->bindValue(':file', $new_file_name, PDO::PARAM_STR);
                            $stmt->bindValue(':ket', $keterangan, PDO::PARAM_STR);
                            $stmt->execute();

                            $success_message = "Berkas video (" . htmlspecialchars($orig_name) . ") berhasil diunggah dan otomatis tersimpan ke database!";
                            header("Location: upload_media.php?success=" . urlencode($success_message));
                            exit();
                        } catch (PDOException $e) {
                            @unlink($video_upload_dir . $new_file_name);
                            $error_message = "Gagal menyimpan data ke database: " . $e->getMessage();
                        }
                    } else {
                        $error_message = "Gagal memindahkan berkas video ke server. Pastikan folder assets/video dapat ditulis.";
                    }
                }
            }
        }
    }
}

// ---------------------------------------------------------
// 2. PROSES UPDATE DATA / GANTI FILE MEDIA (POST: update_media)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_media'])) {
    $edit_type = trim($_POST['edit_type'] ?? '');
    $edit_id = intval($_POST['edit_id'] ?? 0);
    $keterangan = trim($_POST['keterangan'] ?? '');
    $id_materi_input = isset($_POST['id_materi']) && $_POST['id_materi'] !== '' ? intval($_POST['id_materi']) : null;

    if ($edit_id <= 0 || empty($keterangan)) {
        $error_message = "Data perubahan media tidak lengkap!";
    } else {
        if ($edit_type === 'audio') {
            try {
                // Cek apakah ada penggantian file audio baru
                if (isset($_FILES['file_media_edit']) && $_FILES['file_media_edit']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['file_media_edit']['tmp_name'];
                    $orig_name = $_FILES['file_media_edit']['name'];
                    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                    $allowed_audio_exts = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];

                    if (!in_array($ext, $allowed_audio_exts)) {
                        $error_message = "Format audio baru tidak valid! (Hanya mp3, wav, ogg, m4a, aac)";
                    } else {
                        // Ambil file lama untuk dihapus
                        $stmt_old = $pdo->prepare("SELECT file_audio FROM tb_audio WHERE id_audio = :id");
                        $stmt_old->execute(['id' => $edit_id]);
                        $old_file = $stmt_old->fetchColumn();
                        if ($old_file && file_exists($audio_upload_dir . $old_file)) {
                            @unlink($audio_upload_dir . $old_file);
                        }

                        $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($orig_name, PATHINFO_FILENAME));
                        $safe_basename = substr($safe_basename, 0, 30);
                        $new_file_name = 'audio_' . time() . '_' . $safe_basename . '.' . $ext;

                        if (move_uploaded_file($file_tmp, $audio_upload_dir . $new_file_name)) {
                            $stmt = $pdo->prepare("UPDATE tb_audio SET id_materi = :id_materi, file_audio = :file, keterangan = :ket WHERE id_audio = :id");
                            $stmt->bindValue(':id_materi', $id_materi_input, $id_materi_input !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                            $stmt->bindValue(':file', $new_file_name, PDO::PARAM_STR);
                            $stmt->bindValue(':ket', $keterangan, PDO::PARAM_STR);
                            $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
                            $stmt->execute();
                            $success_message = "Media audio berhasil diperbarui beserta berkas baru!";
                        } else {
                            $error_message = "Gagal memindahkan berkas audio baru ke server.";
                        }
                    }
                } else {
                    // Update keterangan & kaitan materi saja
                    $stmt = $pdo->prepare("UPDATE tb_audio SET id_materi = :id_materi, keterangan = :ket WHERE id_audio = :id");
                    $stmt->bindValue(':id_materi', $id_materi_input, $id_materi_input !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':ket', $keterangan, PDO::PARAM_STR);
                    $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $success_message = "Data media audio berhasil diperbarui!";
                }

                if (empty($error_message)) {
                    header("Location: upload_media.php?success=" . urlencode($success_message));
                    exit();
                }
            } catch (PDOException $e) {
                $error_message = "Error database: " . $e->getMessage();
            }
        } elseif ($edit_type === 'video') {
            try {
                // Cek apakah ada penggantian file video baru
                if (isset($_FILES['file_media_edit']) && $_FILES['file_media_edit']['error'] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['file_media_edit']['tmp_name'];
                    $orig_name = $_FILES['file_media_edit']['name'];
                    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                    $allowed_video_exts = ['mp4', 'webm', 'ogg', 'mkv'];

                    if (!in_array($ext, $allowed_video_exts)) {
                        $error_message = "Format video baru tidak valid! (Hanya mp4, webm, ogg, mkv)";
                    } else {
                        // Ambil file lama untuk dihapus
                        $stmt_old = $pdo->prepare("SELECT file_video FROM tb_video WHERE id_video = :id");
                        $stmt_old->execute(['id' => $edit_id]);
                        $old_file = $stmt_old->fetchColumn();
                        if ($old_file && file_exists($video_upload_dir . $old_file)) {
                            @unlink($video_upload_dir . $old_file);
                        }

                        $safe_basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($orig_name, PATHINFO_FILENAME));
                        $safe_basename = substr($safe_basename, 0, 30);
                        $new_file_name = 'video_' . time() . '_' . $safe_basename . '.' . $ext;

                        if (move_uploaded_file($file_tmp, $video_upload_dir . $new_file_name)) {
                            $stmt = $pdo->prepare("UPDATE tb_video SET id_materi = :id_materi, file_video = :file, keterangan = :ket WHERE id_video = :id");
                            $stmt->bindValue(':id_materi', $id_materi_input, $id_materi_input !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                            $stmt->bindValue(':file', $new_file_name, PDO::PARAM_STR);
                            $stmt->bindValue(':ket', $keterangan, PDO::PARAM_STR);
                            $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
                            $stmt->execute();
                            $success_message = "Media video berhasil diperbarui beserta berkas baru!";
                        } else {
                            $error_message = "Gagal memindahkan berkas video baru ke server.";
                        }
                    }
                } else {
                    // Update keterangan & kaitan materi saja
                    $stmt = $pdo->prepare("UPDATE tb_video SET id_materi = :id_materi, keterangan = :ket WHERE id_video = :id");
                    $stmt->bindValue(':id_materi', $id_materi_input, $id_materi_input !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':ket', $keterangan, PDO::PARAM_STR);
                    $stmt->bindValue(':id', $edit_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $success_message = "Data media video berhasil diperbarui!";
                }

                if (empty($error_message)) {
                    header("Location: upload_media.php?success=" . urlencode($success_message));
                    exit();
                }
            } catch (PDOException $e) {
                $error_message = "Error database: " . $e->getMessage();
            }
        }
    }
}

// ---------------------------------------------------------
// 3. PROSES HAPUS MEDIA (GET: delete_audio / delete_video)
// ---------------------------------------------------------
if (isset($_GET['delete_audio'])) {
    $id_audio = intval($_GET['delete_audio']);
    try {
        $stmt_f = $pdo->prepare("SELECT file_audio FROM tb_audio WHERE id_audio = :id");
        $stmt_f->execute(['id' => $id_audio]);
        $f = $stmt_f->fetchColumn();
        if ($f && file_exists($audio_upload_dir . $f)) {
            @unlink($audio_upload_dir . $f);
        }

        $stmt = $pdo->prepare("DELETE FROM tb_audio WHERE id_audio = :id");
        $stmt->execute(['id' => $id_audio]);
        $success_message = "Media audio dan berkas fisik berhasil dihapus!";
        header("Location: upload_media.php?success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Gagal menghapus audio: " . $e->getMessage();
    }
}

if (isset($_GET['delete_video'])) {
    $id_video = intval($_GET['delete_video']);
    try {
        $stmt_f = $pdo->prepare("SELECT file_video FROM tb_video WHERE id_video = :id");
        $stmt_f->execute(['id' => $id_video]);
        $f = $stmt_f->fetchColumn();
        if ($f && file_exists($video_upload_dir . $f)) {
            @unlink($video_upload_dir . $f);
        }

        $stmt = $pdo->prepare("DELETE FROM tb_video WHERE id_video = :id");
        $stmt->execute(['id' => $id_video]);
        $success_message = "Media video dan berkas fisik berhasil dihapus!";
        header("Location: upload_media.php?success=" . urlencode($success_message));
        exit();
    } catch (PDOException $e) {
        $error_message = "Gagal menghapus video: " . $e->getMessage();
    }
}

// Tangkap pesan sukses via URL redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// ---------------------------------------------------------
// 4. FETCH SEMUA DATA MATERI & MEDIA UNTUK DITAMPILKAN
// ---------------------------------------------------------
try {
    // A. Daftar seluruh 20 Bab materi
    $stmt_mat = $pdo->query("SELECT id_materi, urutan, judul_materi, semester, kategori FROM tb_materi ORDER BY urutan ASC");
    $daftar_materi = $stmt_mat->fetchAll();

    // B. Daftar seluruh berkas Audio
    $stmt_aud = $pdo->query("
        SELECT a.*, m.judul_materi, m.urutan, m.semester 
        FROM tb_audio a 
        LEFT JOIN tb_materi m ON a.id_materi = m.id_materi 
        ORDER BY a.id_audio DESC
    ");
    $semua_audio = $stmt_aud->fetchAll();

    // C. Daftar seluruh berkas Video
    $stmt_vid = $pdo->query("
        SELECT v.*, m.judul_materi, m.urutan, m.semester 
        FROM tb_video v 
        LEFT JOIN tb_materi m ON v.id_materi = m.id_materi 
        ORDER BY v.id_video DESC
    ");
    $semua_video = $stmt_vid->fetchAll();

    // D. Hitung Metrik Statistik
    $total_audio = count($semua_audio);
    $total_video = count($semua_video);
    $total_media = $total_audio + $total_video;

    // Bab yang sudah memiliki setidaknya 1 media
    $stmt_linked = $pdo->query("
        SELECT COUNT(DISTINCT id_materi) FROM (
            SELECT id_materi FROM tb_audio WHERE id_materi IS NOT NULL
            UNION
            SELECT id_materi FROM tb_video WHERE id_materi IS NOT NULL
        ) AS linked_babs
    ");
    $total_bab_terkoneksi = $stmt_linked->fetchColumn() ?: 0;

} catch (PDOException $e) {
    die("Gagal memuat data media dari database: " . $e->getMessage());
}

// Pengecekan jika sedang mode Edit media tertentu
$edit_item = null;
$edit_type = '';
if (isset($_GET['edit_audio'])) {
    $edit_id = intval($_GET['edit_audio']);
    $stmt = $pdo->prepare("SELECT * FROM tb_audio WHERE id_audio = :id LIMIT 1");
    $stmt->execute(['id' => $edit_id]);
    $edit_item = $stmt->fetch();
    $edit_type = 'audio';
} elseif (isset($_GET['edit_video'])) {
    $edit_id = intval($_GET['edit_video']);
    $stmt = $pdo->prepare("SELECT * FROM tb_video WHERE id_video = :id LIMIT 1");
    $stmt->execute(['id' => $edit_id]);
    $edit_item = $stmt->fetch();
    $edit_type = 'video';
}

// Pengecekan jika ada id_materi dari parameter URL (misal klik "Unggah untuk Bab ini")
$selected_materi_preset = isset($_GET['id_materi']) ? intval($_GET['id_materi']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload & Kelola Media - Guru Nommensen</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSS Utama Admin -->
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

        /* Sidebar Navigasi Admin Konsisten */
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
            overflow-y: auto;
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
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
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
            border-radius: 8px;
            text-align: center;
            font-weight: 700;
            transition: background-color 0.2s;
            border: 2px solid #dc2626;
            margin-top: 1rem;
            display: block;
        }

        .btn-logout-sidebar:hover {
            background-color: #b91c1c;
            color: #ffffff;
        }

        /* Area Konten Utama */
        .admin-content {
            margin-left: 250px;
            flex: 1;
            padding: 2.25rem 2.5rem;
            background-color: #f8fafc;
            min-width: 0;
        }

        @media (max-width: 991.98px) {
            .sidebar { position: static; width: 100%; height: auto; }
            .admin-content { margin-left: 0; padding: 1.5rem; }
            .admin-layout { flex-direction: column; }
        }

        /* Stat Badges / Bento Cards */
        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
        }
        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        /* Form Card */
        .form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.75rem 2rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 2rem;
        }

        /* Custom Toggle Type */
        .type-toggle-btn {
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-weight: 700;
            padding: 0.65rem 1.25rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .type-toggle-btn.active {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.25);
        }

        /* Table Styling */
        .custom-table-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .custom-table {
            width: 100%;
            margin-bottom: 0;
            vertical-align: middle;
        }
        .custom-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0.9rem 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .custom-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
            color: #1e293b;
        }
        .custom-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .media-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .media-badge.audio {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }
        .media-badge.video {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .materi-badge {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .materi-badge.none {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body class="admin-body">

    <!-- Header Atas -->
    <header class="top-header">
        Dashboard Administrator - Panel Guru SMP Swasta Nommensen
    </header>

    <div class="admin-layout">
        <!-- Sidebar Navigasi Admin (Persis 7 Menu Standar) -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h3>Admin Nommensen</h3>
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php"><i class="bi bi-speedometer2"></i>Dashboard</a></li>
                    <li><a href="kelola_materi.php"><i class="bi bi-book"></i>Kelola Materi</a></li>
                    <li><a href="upload_media.php" class="active"><i class="bi bi-cloud-arrow-up-fill"></i>Upload Media</a></li>
                    <li><a href="kelola_soal.php"><i class="bi bi-patch-question"></i>Kelola Soal</a></li>
                    <li><a href="laporan_nilai.php"><i class="bi bi-bar-chart-line"></i>Laporan Nilai</a></li>
                    <li><a href="pengaturan.php"><i class="bi bi-gear"></i>Pengaturan</a></li>
                    <li><a href="kelola_siswa.php"><i class="bi bi-people"></i>Kelola Data Siswa</a></li>
                </ul>
            </div>
            <a href="logout.php" class="btn-logout-sidebar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="bi bi-box-arrow-left me-1"></i>Keluar (Logout)
            </a>
        </aside>

        <!-- Area Konten Utama -->
        <main class="admin-content">
            
            <!-- Breadcrumb & Header Title -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>Upload & Kelola Media Pembelajaran
                    </h1>
                    <p class="text-secondary mb-0 small">
                        Unggah berkas audio pelafalan atau video percakapan secara langsung. Media otomatis tersimpan ke server dan database tanpa perlu membuat unit baru terlebih dahulu di menu Kelola Materi.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="#form-upload" class="btn btn-primary btn-sm fw-bold px-3 py-2 rounded-3 shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i>Unggah Media Baru
                    </a>
                    <a href="#daftar-media" class="btn btn-outline-secondary btn-sm fw-semibold px-3 py-2 rounded-3">
                        <i class="bi bi-collection-play me-1"></i>Lihat Galeri
                    </a>
                </div>
            </div>

            <!-- Pesan Alert (Notifikasi Error & Sukses) -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <strong>Gagal!</strong> <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                    <strong>Berhasil!</strong> <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Ringkasan Statistik Media (4 Bento Cards) -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary small fw-bold text-uppercase mb-1">Total Media</div>
                            <div class="h3 fw-bold mb-0 text-dark"><?= $total_media ?></div>
                            <div class="text-muted small mt-1">Audio &amp; Video tersimpan</div>
                        </div>
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-folder-fill"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary small fw-bold text-uppercase mb-1">File Audio</div>
                            <div class="h3 fw-bold mb-0 text-primary"><?= $total_audio ?></div>
                            <div class="text-muted small mt-1">Pelafalan &amp; Listening</div>
                        </div>
                        <div class="stat-icon bg-info-subtle text-info">
                            <i class="bi bi-music-note-beamed"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary small fw-bold text-uppercase mb-1">File Video</div>
                            <div class="h3 fw-bold mb-0 text-danger"><?= $total_video ?></div>
                            <div class="text-muted small mt-1">Percakapan &amp; Animasi</div>
                        </div>
                        <div class="stat-icon bg-danger-subtle text-danger">
                            <i class="bi bi-camera-reels-fill"></i>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="stat-card d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-secondary small fw-bold text-uppercase mb-1">Bab Terhubung</div>
                            <div class="h3 fw-bold mb-0 text-success"><?= $total_bab_terkoneksi ?> <span class="fs-6 fw-normal text-muted">/ 20</span></div>
                            <div class="text-muted small mt-1">Bab memiliki media</div>
                        </div>
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =========================================================
                 SECTION A: FORM EDIT MEDIA (JIKA SEDANG MENGEDIT)
                 ========================================================= -->
            <?php if ($edit_item): ?>
                <div class="form-card border-warning" style="background: #fffdf5; border-width: 2px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h5 fw-bold text-dark mb-0">
                            <i class="bi bi-pencil-square text-warning me-2"></i>Edit Media: <?= $edit_type === 'audio' ? 'File Audio' : 'File Video' ?>
                        </h2>
                        <a href="upload_media.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bi bi-x-lg me-1"></i>Batal Edit
                        </a>
                    </div>
                    <form action="upload_media.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="edit_type" value="<?= htmlspecialchars($edit_type) ?>">
                        <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_type === 'audio' ? $edit_item['id_audio'] : $edit_item['id_video']) ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-secondary">Judul / Keterangan Media:</label>
                                <input type="text" name="keterangan" class="form-control" required value="<?= htmlspecialchars($edit_item['keterangan'] ?? '') ?>">
                                <div class="form-text small">Keterangan materi, konteks video, atau kosakata (format: Word | Arti).</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-secondary">Hubungkan ke Bab Materi (Opsional):</label>
                                <select name="id_materi" class="form-select">
                                    <option value="">— Media Mandiri / Bebas (Tanpa Terhubung ke Bab) —</option>
                                    <optgroup label="Semester 1">
                                        <?php foreach ($daftar_materi as $mat): ?>
                                            <?php if ($mat['semester'] == 1): ?>
                                                <option value="<?= $mat['id_materi'] ?>" <?= $edit_item['id_materi'] == $mat['id_materi'] ? 'selected' : '' ?>>
                                                    Bab <?= $mat['urutan'] ?>: <?= htmlspecialchars($mat['judul_materi']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Semester 2">
                                        <?php foreach ($daftar_materi as $mat): ?>
                                            <?php if ($mat['semester'] == 2): ?>
                                                <option value="<?= $mat['id_materi'] ?>" <?= $edit_item['id_materi'] == $mat['id_materi'] ? 'selected' : '' ?>>
                                                    Bab <?= $mat['urutan'] ?>: <?= htmlspecialchars($mat['judul_materi']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold small text-secondary">
                                    Ganti Berkas Fisik: 
                                    <span class="text-muted fw-normal">(Biarkan kosong jika tidak ingin mengganti berkas saat ini)</span>
                                </label>
                                <input type="file" name="file_media_edit" class="form-control" accept="<?= $edit_type === 'audio' ? 'audio/*' : 'video/*' ?>">
                                <div class="form-text small">
                                    Berkas saat ini: 
                                    <strong><?= htmlspecialchars($edit_type === 'audio' ? $edit_item['file_audio'] : $edit_item['file_video']) ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" name="update_media" class="btn btn-warning fw-bold px-4 rounded-3 text-dark">
                                <i class="bi bi-check2-circle me-1"></i>Simpan Perubahan
                            </button>
                            <a href="upload_media.php" class="btn btn-light border px-4 rounded-3">Batal</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- =========================================================
                 SECTION B: FORM UNGGAH MEDIA LANGSUNG (DIRECT UPLOAD)
                 ========================================================= -->
            <div class="form-card" id="form-upload">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-plus-circle-fill text-primary me-2"></i>Form Unggah Media Langsung
                        </h2>
                        <span class="text-muted small">
                            Pilih file dan upload langsung. Media otomatis tersimpan ke server dan database.
                        </span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-lightning-charge-fill me-1"></i>Upload Cepat Langsung Aktif
                    </span>
                </div>

                <form action="upload_media.php" method="POST" enctype="multipart/form-data" id="mainUploadForm">
                    <!-- 1. Pilihan Tipe Media (Audio / Video) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary d-block">1. Pilih Tipe Media:</label>
                        <div class="d-flex flex-wrap gap-2">
                            <label class="type-toggle-btn active" id="btnTypeAudio" onclick="selectMediaType('audio')">
                                <input type="radio" name="media_type" value="audio" checked style="display: none;">
                                <i class="bi bi-music-note-beamed fs-5"></i>
                                <span>Audio Pelafalan / Listening (MP3, WAV, M4A)</span>
                            </label>
                            <label class="type-toggle-btn" id="btnTypeVideo" onclick="selectMediaType('video')">
                                <input type="radio" name="media_type" value="video" style="display: none;">
                                <i class="bi bi-camera-reels-fill fs-5"></i>
                                <span>Video Percakapan / Pembelajaran (MP4, WEBM)</span>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Grid Form Input -->
                    <div class="row g-3 mb-3">
                        <!-- Pilih File Media -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary" id="labelFileMedia">
                                2. Pilih Berkas Audio: <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="file_media" id="inputFileMedia" class="form-control" accept="audio/*" required>
                            <div class="form-text small" id="hintFileMedia">
                                Format: <strong>.mp3, .wav, .ogg, .m4a</strong> (Disarankan &le; 25 MB).
                            </div>
                        </div>

                        <!-- Hubungkan ke Bab Materi (Opsional) -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">
                                3. Hubungkan ke Bab Materi: <span class="badge bg-secondary-subtle text-secondary ms-1">Opsional</span>
                            </label>
                            <select name="id_materi" class="form-select" id="selectBabMateri">
                                <option value="" <?= $selected_materi_preset === 0 ? 'selected' : '' ?>>
                                    — Media Mandiri / Bebas (Tanpa Terkait Bab Tertentu) —
                                </option>
                                <optgroup label="Semester 1">
                                    <?php foreach ($daftar_materi as $mat): ?>
                                        <?php if ($mat['semester'] == 1): ?>
                                            <option value="<?= $mat['id_materi'] ?>" <?= $selected_materi_preset === intval($mat['id_materi']) ? 'selected' : '' ?>>
                                                Bab <?= $mat['urutan'] ?>: <?= htmlspecialchars($mat['judul_materi']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                                <optgroup label="Semester 2">
                                    <?php foreach ($daftar_materi as $mat): ?>
                                        <?php if ($mat['semester'] == 2): ?>
                                            <option value="<?= $mat['id_materi'] ?>" <?= $selected_materi_preset === intval($mat['id_materi']) ? 'selected' : '' ?>>
                                                Bab <?= $mat['urutan'] ?>: <?= htmlspecialchars($mat['judul_materi']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </optgroup>
                            </select>
                            <div class="form-text small">
                                Jika dihubungkan ke Bab, media akan langsung muncul di materi belajar siswa pada Bab tersebut.
                            </div>
                        </div>

                        <!-- Judul / Keterangan Media -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-secondary">
                                4. Judul / Keterangan Media: <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="keterangan" id="inputKeterangan" class="form-control" placeholder="Contoh: Audio Pelafalan Bab 1 - Expression of Greeting" required>
                            <div class="form-text small">
                                Deskripsikan isi materi media. Untuk kosakata (vocabulary), Anda dapat menggunakan format <code>Kata Bahasa Inggris | Arti Bahasa Indonesia</code> (contoh: <code>Library | Perpustakaan</code>).
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Aksi Upload -->
                    <div class="d-flex align-items-center gap-3 pt-2">
                        <button type="submit" name="upload_media" class="btn btn-primary fw-bold px-4 py-2 rounded-3 shadow-sm">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i>Unggah &amp; Simpan ke Database
                        </button>
                        <button type="reset" class="btn btn-light border px-3 py-2 rounded-3" onclick="resetUploadForm()">
                            Reset Form
                        </button>
                    </div>
                </form>
            </div>

            <!-- =========================================================
                 SECTION C: GALERI & DAFTAR MEDIA TERUNGGAH (MEDIA LIBRARY)
                 ========================================================= -->
            <div class="custom-table-card" id="daftar-media">
                <div class="p-3 bg-white border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-collection-play-fill text-primary me-2"></i>Daftar Seluruh Media Tersimpan di Database
                        </h2>
                        <span class="text-muted small">
                            Semua berkas audio dan video yang telah diunggah guru ke database sistem.
                        </span>
                    </div>

                    <!-- Navigasi Tab Filter Media -->
                    <ul class="nav nav-pills gap-1" id="mediaTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active py-1 px-3 fw-bold small rounded-pill" id="tab-all" data-bs-toggle="pill" data-bs-target="#panel-all" type="button">
                                Semua (<?= $total_media ?>)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3 fw-bold small rounded-pill text-primary" id="tab-audio" data-bs-toggle="pill" data-bs-target="#panel-audio" type="button">
                                <i class="bi bi-music-note-beamed me-1"></i>Audio (<?= $total_audio ?>)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link py-1 px-3 fw-bold small rounded-pill text-danger" id="tab-video" data-bs-toggle="pill" data-bs-target="#panel-video" type="button">
                                <i class="bi bi-camera-reels me-1"></i>Video (<?= $total_video ?>)
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Konten Tab Media -->
                <div class="tab-content" id="mediaTabsContent">
                    
                    <!-- 1. TAB SEMUA MEDIA -->
                    <div class="tab-pane fade show active" id="panel-all" role="tabpanel">
                        <?php 
                        // Gabungkan data audio dan video untuk tampilan semua
                        $all_media = [];
                        foreach ($semua_audio as $a) {
                            $all_media[] = [
                                'type' => 'audio',
                                'id' => $a['id_audio'],
                                'id_materi' => $a['id_materi'],
                                'file' => $a['file_audio'],
                                'keterangan' => $a['keterangan'],
                                'judul_materi' => $a['judul_materi'] ?? null,
                                'urutan' => $a['urutan'] ?? null
                            ];
                        }
                        foreach ($semua_video as $v) {
                            $all_media[] = [
                                'type' => 'video',
                                'id' => $v['id_video'],
                                'id_materi' => $v['id_materi'],
                                'file' => $v['file_video'],
                                'keterangan' => $v['keterangan'],
                                'judul_materi' => $v['judul_materi'] ?? null,
                                'urutan' => $v['urutan'] ?? null
                            ];
                        }
                        ?>

                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 12%;">Tipe</th>
                                        <th style="width: 28%;">Judul / Keterangan</th>
                                        <th style="width: 20%;">Bab Terkait</th>
                                        <th style="width: 23%;">Pemutar Media</th>
                                        <th style="width: 12%; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($all_media)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="bi bi-folder2-open fs-1 d-block mb-2 text-secondary"></i>
                                                Belum ada berkas media tersimpan di database. Silakan gunakan form di atas untuk mengunggah media langsung.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($all_media as $item): ?>
                                            <tr>
                                                <td class="text-muted fw-bold"><?= $no++ ?></td>
                                                <td>
                                                    <?php if ($item['type'] === 'audio'): ?>
                                                        <span class="media-badge audio">
                                                            <i class="bi bi-music-note-beamed"></i>Audio
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="media-badge video">
                                                            <i class="bi bi-camera-reels-fill"></i>Video
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($item['keterangan']) ?></div>
                                                    <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                                                        <i class="bi bi-file-earmark me-1"></i><?= htmlspecialchars($item['file']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($item['id_materi']) && !empty($item['judul_materi'])): ?>
                                                        <span class="materi-badge">
                                                            <i class="bi bi-book-half me-1"></i>Bab <?= $item['urutan'] ?>: <?= htmlspecialchars($item['judul_materi']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="materi-badge none">
                                                            <i class="bi bi-dash-circle me-1"></i>Media Mandiri
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($item['type'] === 'audio'): ?>
                                                        <audio src="../assets/audio/<?= htmlspecialchars($item['file']) ?>" controls preload="none" style="height: 36px; max-width: 220px;"></audio>
                                                    <?php else: ?>
                                                        <video src="../assets/video/<?= htmlspecialchars($item['file']) ?>" controls preload="none" style="height: 60px; max-width: 140px; border-radius: 8px; background: #000;"></video>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align: center;">
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($item['type'] === 'audio'): ?>
                                                            <a href="upload_media.php?edit_audio=<?= $item['id'] ?>" class="btn btn-outline-warning" title="Edit Keterangan">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </a>
                                                            <a href="upload_media.php?delete_audio=<?= $item['id'] ?>" class="btn btn-outline-danger" title="Hapus Berkas & Data" onclick="return confirm('Apakah Anda yakin ingin menghapus berkas audio ini dari database dan server?')">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="upload_media.php?edit_video=<?= $item['id'] ?>" class="btn btn-outline-warning" title="Edit Keterangan">
                                                                <i class="bi bi-pencil-fill"></i>
                                                            </a>
                                                            <a href="upload_media.php?delete_video=<?= $item['id'] ?>" class="btn btn-outline-danger" title="Hapus Berkas & Data" onclick="return confirm('Apakah Anda yakin ingin menghapus berkas video ini dari database dan server?')">
                                                                <i class="bi bi-trash-fill"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. TAB AUDIO KHUSUS -->
                    <div class="tab-pane fade" id="panel-audio" role="tabpanel">
                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 35%;">Judul / Keterangan Audio</th>
                                        <th style="width: 25%;">Bab Terkait</th>
                                        <th style="width: 23%;">Pemutar Audio</th>
                                        <th style="width: 12%; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($semua_audio)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                Belum ada file audio tersimpan. Unggah berkas audio baru lewat form di atas.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($semua_audio as $a): ?>
                                            <tr>
                                                <td class="text-muted fw-bold"><?= $no++ ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($a['keterangan']) ?></div>
                                                    <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                                                        <?= htmlspecialchars($a['file_audio']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($a['id_materi']) && !empty($a['judul_materi'])): ?>
                                                        <span class="materi-badge">
                                                            Bab <?= $a['urutan'] ?>: <?= htmlspecialchars($a['judul_materi']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="materi-badge none">Media Mandiri</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <audio src="../assets/audio/<?= htmlspecialchars($a['file_audio']) ?>" controls preload="none" style="height: 36px; max-width: 220px;"></audio>
                                                </td>
                                                <td style="text-align: center;">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="upload_media.php?edit_audio=<?= $a['id_audio'] ?>" class="btn btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>
                                                        <a href="upload_media.php?delete_audio=<?= $a['id_audio'] ?>" class="btn btn-outline-danger" title="Hapus" onclick="return confirm('Hapus audio ini?')">
                                                            <i class="bi bi-trash-fill"></i>
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

                    <!-- 3. TAB VIDEO KHUSUS -->
                    <div class="tab-pane fade" id="panel-video" role="tabpanel">
                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 35%;">Judul / Keterangan Video</th>
                                        <th style="width: 25%;">Bab Terkait</th>
                                        <th style="width: 23%;">Pemutar Video</th>
                                        <th style="width: 12%; text-align: center;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($semua_video)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                Belum ada file video tersimpan. Unggah berkas video baru lewat form di atas.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($semua_video as $v): ?>
                                            <tr>
                                                <td class="text-muted fw-bold"><?= $no++ ?></td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?= htmlspecialchars($v['keterangan']) ?></div>
                                                    <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                                                        <?= htmlspecialchars($v['file_video']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if (!empty($v['id_materi']) && !empty($v['judul_materi'])): ?>
                                                        <span class="materi-badge">
                                                            Bab <?= $v['urutan'] ?>: <?= htmlspecialchars($v['judul_materi']) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="materi-badge none">Media Mandiri</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <video src="../assets/video/<?= htmlspecialchars($v['file_video']) ?>" controls preload="none" style="height: 60px; max-width: 140px; border-radius: 8px; background: #000;"></video>
                                                </td>
                                                <td style="text-align: center;">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="upload_media.php?edit_video=<?= $v['id_video'] ?>" class="btn btn-outline-warning" title="Edit">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>
                                                        <a href="upload_media.php?delete_video=<?= $v['id_video'] ?>" class="btn btn-outline-danger" title="Hapus" onclick="return confirm('Hapus video ini?')">
                                                            <i class="bi bi-trash-fill"></i>
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

                </div>
            </div>

            <!-- =========================================================
                 SECTION D: KATALOG KETERSEDIAAN MEDIA 20 BAB KURIKULUM
                 ========================================================= -->
            <div class="custom-table-card">
                <div class="p-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-journal-bookmark-fill text-success me-2"></i>Status Ketersediaan Media 20 Bab Materi Kurikulum
                        </h2>
                        <span class="text-muted small">
                            Memantau status kelengkapan audio listening dan video animasi pembelajaran di setiap Bab materi.
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">Bab</th>
                                <th style="width: 32%;">Judul Bab Materi</th>
                                <th style="width: 12%;">Semester</th>
                                <th style="width: 18%;">Status Audio</th>
                                <th style="width: 18%;">Status Video</th>
                                <th style="width: 12%; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Indexing media berdasarkan id_materi untuk pencarian cepat
                            $audio_by_materi = [];
                            foreach ($semua_audio as $a) {
                                if (!empty($a['id_materi'])) {
                                    $audio_by_materi[$a['id_materi']][] = $a;
                                }
                            }

                            $video_by_materi = [];
                            foreach ($semua_video as $v) {
                                if (!empty($v['id_materi'])) {
                                    $video_by_materi[$v['id_materi']][] = $v;
                                }
                            }

                            foreach ($daftar_materi as $m): 
                                $has_audio = isset($audio_by_materi[$m['id_materi']]) && count($audio_by_materi[$m['id_materi']]) > 0;
                                $has_video = isset($video_by_materi[$m['id_materi']]) && count($video_by_materi[$m['id_materi']]) > 0;
                            ?>
                                <tr>
                                    <td class="fw-bold text-primary">Bab <?= sprintf("%02d", $m['urutan']) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($m['judul_materi']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $m['semester'] == 1 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info-emphasis' ?> rounded-pill px-2 py-1">
                                            Semester <?= htmlspecialchars($m['semester']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($has_audio): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-check2 me-1"></i><?= count($audio_by_materi[$m['id_materi']]) ?> Audio
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                                Belum Ada
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($has_video): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                <i class="bi bi-check2 me-1"></i><?= count($video_by_materi[$m['id_materi']]) ?> Video
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">
                                                Belum Ada
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="upload_media.php?id_materi=<?= $m['id_materi'] ?>#form-upload" class="btn btn-sm btn-outline-primary fw-semibold rounded-3" title="Unggah media untuk Bab ini">
                                            <i class="bi bi-plus-lg me-1"></i>Unggah
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- Footer Bawah -->
    <footer class="bottom-footer text-center py-3 border-top bg-white text-secondary small">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Fungsi interaktif memilih tipe media pada form
        function selectMediaType(type) {
            const btnAudio = document.getElementById('btnTypeAudio');
            const btnVideo = document.getElementById('btnTypeVideo');
            const labelFile = document.getElementById('labelFileMedia');
            const inputFile = document.getElementById('inputFileMedia');
            const hintFile = document.getElementById('hintFileMedia');
            const inputKet = document.getElementById('inputKeterangan');

            if (type === 'audio') {
                btnAudio.classList.add('active');
                btnVideo.classList.remove('active');
                btnAudio.querySelector('input').checked = true;
                labelFile.innerHTML = '2. Pilih Berkas Audio: <span class="text-danger">*</span>';
                inputFile.accept = 'audio/*';
                hintFile.innerHTML = 'Format: <strong>.mp3, .wav, .ogg, .m4a</strong> (Disarankan &le; 25 MB).';
                if (!inputKet.value || inputKet.value.includes('Video')) {
                    inputKet.placeholder = 'Contoh: Audio Pelafalan Bab 1 - Expression of Greeting';
                }
            } else {
                btnVideo.classList.add('active');
                btnAudio.classList.remove('active');
                btnVideo.querySelector('input').checked = true;
                labelFile.innerHTML = '2. Pilih Berkas Video: <span class="text-danger">*</span>';
                inputFile.accept = 'video/*';
                hintFile.innerHTML = 'Format: <strong>.mp4, .webm, .ogg</strong> (Disarankan &le; 100 MB).';
                if (!inputKet.value || inputKet.value.includes('Audio')) {
                    inputKet.placeholder = 'Contoh: Video Dialog Percakapan Bab 1 - Greeting & Parting';
                }
            }
        }

        function resetUploadForm() {
            setTimeout(() => {
                selectMediaType('audio');
            }, 50);
        }
    </script>
</body>
</html>
