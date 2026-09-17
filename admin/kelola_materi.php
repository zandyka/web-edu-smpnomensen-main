<?php
/**
 * File: admin/kelola_materi.php
 * Deskripsi: Halaman Kelola 20 Materi SMP Kelas 7 untuk Guru/Administrator.
 *            Mendukung pembaruan modul teks materi, ringkasan, dan pemantauan multimedia.
 */

require_once '../includes/auth_admin.php';
require_once '../config.php';

$error_message = '';
$success_message = '';

// Ambil parameter filter semester
$filter_sem = isset($_GET['semester']) ? intval($_GET['semester']) : 0;

// Ambil parameter edit materi
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$materi_edit = null;

if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM tb_materi WHERE id_materi = :id LIMIT 1");
    $stmt->execute(['id' => $edit_id]);
    $materi_edit = $stmt->fetch();
}

// Proses Simpan / Update Modul Materi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_materi'])) {
    $id_materi = intval($_POST['id_materi'] ?? 0);
    $judul_materi = trim($_POST['judul_materi'] ?? '');
    $semester = intval($_POST['semester'] ?? 1);
    $urutan = intval($_POST['urutan'] ?? 1);
    $ringkasan = trim($_POST['ringkasan'] ?? '');
    $konten_teks = trim($_POST['konten_teks'] ?? '');

    if (empty($judul_materi)) {
        $error_message = "Judul materi tidak boleh kosong!";
    } elseif ($id_materi > 0) {
        try {
            $stmt = $pdo->prepare("
                UPDATE tb_materi 
                SET judul_materi = :judul, 
                    semester = :sem, 
                    urutan = :urut, 
                    ringkasan = :ringkas, 
                    konten_teks = :konten, 
                    kategori = :kat
                WHERE id_materi = :id
            ");
            $stmt->execute([
                'judul' => $judul_materi,
                'sem' => $semester,
                'urut' => $urutan,
                'ringkas' => $ringkasan,
                'konten' => $konten_teks,
                'kat' => 'Semester ' . $semester,
                'id' => $id_materi
            ]);
            $success_message = "Modul materi Bab " . $urutan . " berhasil diperbarui!";
            
            // Refresh data edit jika sedang aktif
            $stmt_ref = $pdo->prepare("SELECT * FROM tb_materi WHERE id_materi = :id LIMIT 1");
            $stmt_ref->execute(['id' => $id_materi]);
            $materi_edit = $stmt_ref->fetch();
        } catch (PDOException $e) {
            $error_message = "Gagal menyimpan materi: " . $e->getMessage();
        }
    }
}

// Query daftar 20 materi beserta relasi video dan kuis
$sql = "
    SELECT m.*, v.file_video, k.id_kuis, a.file_audio
    FROM tb_materi m
    LEFT JOIN tb_video v ON m.id_materi = v.id_materi
    LEFT JOIN tb_kuis k ON m.id_materi = k.id_materi
    LEFT JOIN tb_audio a ON m.id_materi = a.id_materi
";
if ($filter_sem === 1 || $filter_sem === 2) {
    $sql .= " WHERE m.semester = :sem";
}
$sql .= " ORDER BY m.urutan ASC";

$stmt = $pdo->prepare($sql);
if ($filter_sem === 1 || $filter_sem === 2) {
    $stmt->execute(['sem' => $filter_sem]);
} else {
    $stmt->execute();
}
$materi_list = $stmt->fetchAll();

// Total statistik
$total_materi = $pdo->query("SELECT COUNT(*) FROM tb_materi")->fetchColumn();
$total_s1 = $pdo->query("SELECT COUNT(*) FROM tb_materi WHERE semester = 1")->fetchColumn();
$total_s2 = $pdo->query("SELECT COUNT(*) FROM tb_materi WHERE semester = 2")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Materi - Guru SMP Swasta Nommensen</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
        <!-- Memanggil Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=4.3.0">
    <!-- Quill Rich Text Editor CSS -->
    <link rel="stylesheet" href="../assets/css/quill.snow.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; display: flex; flex-direction: column; min-height: 100vh; }
        .admin-layout { display: flex; flex-direction: row; flex: 1; width: 100%; }
        .sidebar { width: 250px; background-color: #e5e7eb; color: #1f2937; padding: 1.5rem 1.25rem; display: flex; flex-direction: column; border-right: 2px solid #cbd5e1; flex-shrink: 0; position: fixed; top: 0; left: 0; height: 100vh; overflow: hidden; z-index: 1000; }
        .sidebar-brand h3 { font-family: 'Outfit', sans-serif; font-size: 1.2rem; font-weight: 800; color: #475569; letter-spacing: 1.5px; margin-bottom: 2rem; text-align: center; text-transform: uppercase; border-bottom: 1px solid #cbd5e1; padding-bottom: 0.5rem; }
        .sidebar-menu { list-style: none; padding: 0; margin: 0; flex: 1; }
        .sidebar-menu li { margin-bottom: 0.85rem; }
        .sidebar-menu a { color: #374151; text-decoration: none; padding: 0.75rem 1rem; border: 2px solid #9ca3af; border-radius: 4px; display: block; font-weight: 700; text-align: center; background-color: #ffffff; transition: all 0.2s ease-in-out; font-size: 0.9rem; }
        .sidebar-menu a:hover { background-color: #f1f5f9; border-color: var(--accent-blue); color: var(--accent-blue); }
        .sidebar-menu a.active { color: #ffffff; background-color: #4b5563; border-color: #374151; }
        .btn-logout-sidebar { background-color: #ef4444; color: #ffffff; text-decoration: none; padding: 0.75rem 1rem; border-radius: 4px; text-align: center; font-weight: 700; transition: background-color 0.2s; border: 2px solid #dc2626; margin-top: 1rem; display: block; }
        .btn-logout-sidebar:hover { background-color: #b91c1c; }
        .admin-content { flex: 1; padding: 2.5rem 3rem; background-color: #ffffff; overflow-y: visible; min-width: 0; }
        .status-pill { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .status-pill.ready { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .status-pill.pending { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    </style>
</head>
<body class="admin-body">

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
                    <li><a href="kelola_materi.php" class="active">Kelola Materi</a></li>
                    <li><a href="upload_media.php">Upload Media</a></li>
                    <li><a href="kelola_soal.php">Kelola Soal</a></li>
                    <li><a href="laporan_nilai.php">Laporan Nilai</a></li>
                    <li><a href="pengaturan.php">Pengaturan</a></li>
                    <li><a href="kelola_siswa.php">Kelola Data Siswa</a></li>
                </ul>
            </div>
            <!-- Tombol Keluar Sesi -->
            <a href="logout.php" class="btn-logout-sidebar" onclick="return confirm('Apakah Anda yakin ingin keluar?')">Keluar (Logout)</a>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
            <div style="margin-bottom: 2rem;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">
                    Manajemen Kurikulum: 20 Materi SMP Kelas 7
                </h2>
                <p style="color: #64748b; font-size: 0.95rem;">
                    Kelola modul pembelajaran bacaan guru, pantau ketersediaan video animasi, audio listening, dan kuis evaluasi untuk masing-masing materi.
                </p>
            </div>

            <!-- Notifikasi -->
            <?php if (!empty($error_message)): ?>
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600;">
                    ⚠️ <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600;">
                    ✅ <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <!-- Form Edit Teks Modul Materi (Jika Guru Mengklik Edit) -->
            <?php if ($materi_edit): ?>
                <div class="form-card" style="border: 2px solid #3b82f6; background: #fafafa; margin-bottom: 2.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <h3 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin: 0;">
                            Edit Modul Teks: Bab <?= $materi_edit['urutan'] ?> - <?= htmlspecialchars($materi_edit['judul_materi']) ?>
                        </h3>
                        <a href="kelola_materi.php" class="btn-sm" style="background: #64748b; color: white; text-decoration: none;">
                            Tutup Form Edit
                        </a>
                    </div>

                    <form action="kelola_materi.php<?= $filter_sem ? '?semester='.$filter_sem : '' ?>" method="POST">
                        <input type="hidden" name="id_materi" value="<?= $materi_edit['id_materi'] ?>">
                        <input type="hidden" name="urutan" value="<?= $materi_edit['urutan'] ?>">

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-weight: 700; margin-bottom: 0.35rem; font-size: 0.88rem; color: #334155;">Judul Materi:</label>
                                <input type="text" name="judul_materi" class="form-control" value="<?= htmlspecialchars($materi_edit['judul_materi']) ?>" required>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 700; margin-bottom: 0.35rem; font-size: 0.88rem; color: #334155;">Semester:</label>
                                <select name="semester" class="form-control" required>
                                    <option value="1" <?= $materi_edit['semester'] == 1 ? 'selected' : '' ?>>Semester 1</option>
                                    <option value="2" <?= $materi_edit['semester'] == 2 ? 'selected' : '' ?>>Semester 2</option>
                                </select>
                            </div>
                        </div>

                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; font-weight: 700; margin-bottom: 0.35rem; font-size: 0.88rem; color: #334155;">Ringkasan / Tujuan Pembelajaran:</label>
                            <input type="text" name="ringkasan" class="form-control" value="<?= htmlspecialchars($materi_edit['ringkasan'] ?? '') ?>" placeholder="Tuliskan tujuan pokok bab ini...">
                        </div>

                        <div style="margin-bottom: 1.5rem;">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                <label style="display: block; font-weight: 700; font-size: 0.92rem; color: #1e293b; margin: 0;">
                                    <i class="bi bi-file-earmark-richtext-fill text-primary me-1"></i>Isi Modul Pembelajaran Materi:
                                </label>
                                
                                <!-- Toolbar Bantuan & Template Cepat Guru -->
                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    <span class="text-muted small me-1"><i class="bi bi-magic me-1"></i>Template Cepat:</span>
                                    <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2 rounded-2" onclick="insertTemplate('standar')" title="Format modul standar: Tujuan, Uraian, Contoh, Kosakata">
                                        <i class="bi bi-layout-text-window-reverse me-1"></i>Modul Standar
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm py-1 px-2 rounded-2" onclick="insertTemplate('dialog')" title="Format percakapan dialog dua tokoh (Speaker A & B)">
                                        <i class="bi bi-chat-dots-fill me-1"></i>Percakapan
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm py-1 px-2 rounded-2" onclick="insertTemplate('tabel')" title="Format tabel kosakata bahasa Inggris">
                                        <i class="bi bi-table me-1"></i>Tabel Kosakata
                                    </button>
                                    <button type="button" class="btn btn-outline-dark btn-sm py-1 px-2 rounded-2" onclick="toggleCodeMode()" id="btnToggleCode" title="Beralih ke tampilan kode HTML">
                                        <i class="bi bi-code-slash me-1"></i>Mode Kode
                                    </button>
                                </div>
                            </div>

                            <!-- Alert Edukasi Guru: Tidak Perlu HTML -->
                            <div class="alert alert-info py-2 px-3 mb-2 rounded-3 small d-flex align-items-center justify-content-between" style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;">
                                <div>
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    <strong>Editor Visual Mudah:</strong> Anda tidak perlu mengetik kode HTML sama sekali. Cukup ketik teks seperti di Microsoft Word dan gunakan tombol di atas (Tebal, Miring, Poin, atau Judul) untuk mempercantik modul.
                                </div>
                            </div>

                            <!-- Kontainer Quill Visual Editor (Tampilan Word) -->
                            <div id="quill-wrapper" style="background: #ffffff; border-radius: 8px; border: 1px solid #cbd5e1; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div id="quill-editor" style="min-height: 320px; font-family: 'Inter', sans-serif; font-size: 1rem; line-height: 1.7; color: #1e293b;">
                                    <?= $materi_edit['konten_teks'] ?? '' ?>
                                </div>
                            </div>

                            <!-- Textarea Asli (Hidden by default, untuk submit data form & mode kode) -->
                            <div id="html-code-wrapper" style="display: none; margin-top: 0.5rem;">
                                <textarea name="konten_teks" id="konten_teks" rows="14" class="form-control" style="font-family: monospace; font-size: 0.88rem; line-height: 1.5;"><?= htmlspecialchars($materi_edit['konten_teks'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                            <button type="submit" name="save_materi" class="btn-sm btn-success" style="padding: 0.75rem 1.5rem; font-size: 0.95rem; font-weight: 700;">
                                <i class="bi bi-check2-circle me-1"></i>Simpan Perubahan Modul
                            </button>
                            <button type="button" class="btn-sm btn-play" onclick="openLivePreview()" style="padding: 0.75rem 1.25rem; font-size: 0.95rem; background-color: #2563eb; color: #ffffff; border: none; cursor: pointer;">
                                <i class="bi bi-eye-fill me-1"></i>Pratinjau Tampilan Siswa
                            </button>
                            <a href="kelola_materi.php" class="btn-sm" style="background: #94a3b8; color: white; text-decoration: none; padding: 0.75rem 1.25rem; font-size: 0.95rem;">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Tab Filter Semester -->
            <div class="semester-tabs">
                <a href="kelola_materi.php" class="tab-btn <?= $filter_sem === 0 ? 'active' : '' ?>">
                    Semua Bab (<?= $total_materi ?>)
                </a>
                <a href="kelola_materi.php?semester=1" class="tab-btn <?= $filter_sem === 1 ? 'active' : '' ?>">
                    Semester 1 (<?= $total_s1 ?> Bab)
                </a>
                <a href="kelola_materi.php?semester=2" class="tab-btn <?= $filter_sem === 2 ? 'active' : '' ?>">
                    Semester 2 (<?= $total_s2 ?> Bab)
                </a>
            </div>

            <!-- Tabel 20 Materi -->
            <div class="table-container" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <table class="table-materi">
                    <thead>
                        <tr>
                            <th style="width: 8%; text-align: center;">Bab</th>
                            <th style="width: 12%; text-align: center;">Semester</th>
                            <th style="width: 32%;">Judul Materi Kurikulum</th>
                            <th style="width: 25%;">Kelengkapan Media</th>
                            <th style="width: 23%; text-align: center;">Aksi Guru</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materi_list as $m): ?>
                            <?php
                            $has_video = !empty($m['file_video']);
                            $has_audio = !empty($m['file_audio']);
                            $has_quiz = !empty($m['id_kuis']);
                            ?>
                            <tr>
                                <td style="text-align: center; font-weight: 800; color: #1e3a8a;">
                                    Bab <?= sprintf("%02d", $m['urutan']) ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-sem <?= $m['semester'] == 1 ? 'badge-sem-1' : 'badge-sem-2' ?>">
                                        Sem <?= $m['semester'] ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: #0f172a; font-size: 0.95rem;"><?= htmlspecialchars($m['judul_materi']) ?></strong>
                                    <?php if (!empty($m['ringkasan'])): ?>
                                        <br><small style="color: #64748b; font-size: 0.8rem;"><?= htmlspecialchars(substr($m['ringkasan'], 0, 75)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <div>
                                            <span class="status-pill <?= $has_video ? 'ready' : 'pending' ?>">
                                                🎬 Video: <?= $has_video ? 'Tersedia' : 'Belum Ada' ?>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="status-pill <?= $has_audio ? 'ready' : 'pending' ?>">
                                                🎙️ Audio: <?= $has_audio ? 'Tersedia' : 'Belum Ada' ?>
                                            </span>
                                        </div>
                                        <div>
                                            <span class="status-pill <?= $has_quiz ? 'ready' : 'pending' ?>">
                                                📝 Kuis: <?= $has_quiz ? 'Tersedia' : 'Belum Ada' ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 0.4rem; justify-content: center; flex-wrap: wrap;">
                                        <a href="kelola_materi.php?edit_id=<?= $m['id_materi'] ?><?= $filter_sem ? '&semester='.$filter_sem : '' ?>" class="btn-sm btn-edit">
                                            ✏️ Edit Teks
                                        </a>
                                        <a href="../siswa/materi_detail.php?id=<?= $m['id_materi'] ?>" target="_blank" class="btn-sm btn-play" title="Buka Pratinjau">
                                            👁️ Pratinjau
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <!-- Footer Bawah -->
    <footer class="bottom-footer">
        &copy; 2026 Aplikasi Pembelajaran Bahasa Inggris - SMP Swasta Nommensen
    </footer>

    <!-- Modal Live Preview Tampilan Siswa -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 shadow border-0 overflow-hidden">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="previewModalLabel">
                        <i class="bi bi-eye-fill me-2"></i>Simulasi Tampilan di Layar Siswa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-book-half fs-4 text-primary"></i>
                            <h5 class="fw-bold text-dark mb-0">2. Modul Teks &amp; Materi Pembelajaran</h5>
                        </div>
                        <div class="materi-html-content p-3 bg-light rounded-3 text-dark leading-relaxed" id="previewModalBody" style="font-size: 1rem; line-height: 1.8;">
                            <!-- Live content inserted dynamically -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Tutup Pratinjau</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Quill Rich Text Editor JS (Lokal dengan Fallback CDN) -->
    <script src="../assets/js/quill.js"></script>
    <script>
    if (typeof Quill === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"><\/script>');
    }
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editorContainer = document.getElementById('quill-editor');
        if (!editorContainer) return;

        // Inisialisasi Quill Editor
        const quill = new Quill('#quill-editor', {
            theme: 'snow',
            placeholder: 'Tuliskan modul teks pembelajaran di sini (seperti mengetik di Microsoft Word)...',
            modules: {
                toolbar: [
                    [{ 'header': [2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['blockquote'],
                    [{ 'align': [] }],
                    ['clean']
                ]
            }
        });

        let isCodeMode = false;

        // Sinkronisasi otomatis sebelum form disubmit
        const form = editorContainer.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                if (isCodeMode) {
                    return true;
                }
                document.getElementById('konten_teks').value = quill.root.innerHTML;
            });
        }

        // Toggle Mode Kode HTML / Visual Word
        window.toggleCodeMode = function() {
            const quillWrapper = document.getElementById('quill-wrapper');
            const htmlWrapper = document.getElementById('html-code-wrapper');
            const textarea = document.getElementById('konten_teks');
            const btnToggle = document.getElementById('btnToggleCode');

            if (!isCodeMode) {
                // Berpindah ke Mode Kode HTML
                textarea.value = quill.root.innerHTML;
                quillWrapper.style.display = 'none';
                htmlWrapper.style.display = 'block';
                btnToggle.innerHTML = '<i class="bi bi-pencil-square me-1"></i>Editor Visual (Word)';
                btnToggle.classList.remove('btn-outline-dark');
                btnToggle.classList.add('btn-dark');
                isCodeMode = true;
            } else {
                // Berpindah ke Mode Visual Editor
                quill.root.innerHTML = textarea.value;
                htmlWrapper.style.display = 'none';
                quillWrapper.style.display = 'block';
                btnToggle.innerHTML = '<i class="bi bi-code-slash me-1"></i>Mode Kode';
                btnToggle.classList.remove('btn-dark');
                btnToggle.classList.add('btn-outline-dark');
                isCodeMode = false;
            }
        };

        // Fungsi Sisipkan Template Cepat
        window.insertTemplate = function(type) {
            let content = '';
            if (type === 'standar') {
                content = `
<h3>1. Tujuan Pembelajaran</h3>
<p>Setelah mempelajari bab ini, siswa diharapkan mampu memahami konsep utama dan mempraktikkannya dengan percaya diri dalam komunikasi sehari-hari.</p>

<h3>2. Uraian Materi &amp; Penjelasan</h3>
<p>Tuliskan penjelasan materi secara ringkas, jelas, dan bertahap di bagian ini agar mudah dipahami siswa SMP...</p>
<ul>
    <li><b>Konsep Kunci 1:</b> Penjelasan detail poin pertama secara sederhana.</li>
    <li><b>Konsep Kunci 2:</b> Penjelasan detail poin kedua secara sederhana.</li>
</ul>

<h3>3. Contoh Kalimat</h3>
<p>Berikut adalah contoh penggunaan kalimat dalam kehidupan sehari-hari:</p>
<ul>
    <li><i>Good morning, everyone!</i> &mdash; Selamat pagi semuanya!</li>
    <li><i>How are you doing today?</i> &mdash; Bagaimana kabar kalian hari ini?</li>
</ul>

<h3>4. Kosakata Kunci (Vocabulary Focus)</h3>
<ul>
    <li><b>Greeting:</b> Salam / Sapaan</li>
    <li><b>Parting:</b> Pamitan / Perpisahan</li>
</ul>
                `.trim();
            } else if (type === 'dialog') {
                content = `
<h3>Contoh Percakapan (Dialogue Script)</h3>
<p>Perhatikan dan praktikkan dialog percakapan berikut bersama teman sebangkumu:</p>

<p><b>Tokoh A:</b> "Good afternoon! How can I help you today?"</p>
<p><i>(Selamat siang! Ada yang bisa saya bantu hari ini?)</i></p>

<p><b>Tokoh B:</b> "Good afternoon! I would like to borrow an English dictionary, please."</p>
<p><i>(Selamat siang! Saya ingin meminjam kamus bahasa Inggris.)</i></p>

<p><b>Tokoh A:</b> "Sure! Here is the dictionary. Please take good care of it."</p>
<p><i>(Tentu! Ini kamusnya. Tolong dijaga dengan baik ya.)</i></p>

<p><b>Tokoh B:</b> "Thank you very much!"</p>
<p><i>(Terima kasih banyak!)</i></p>
                `.trim();
            } else if (type === 'tabel') {
                content = `
<h3>Tabel Kosakata &amp; Contoh Penggunaan</h3>
<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
    <tr style="background-color: #f1f5f9; font-weight: bold;">
        <th style="width: 10%;">No</th>
        <th style="width: 30%;">Kata Bahasa Inggris</th>
        <th style="width: 30%;">Arti Bahasa Indonesia</th>
        <th style="width: 30%;">Contoh Kalimat</th>
    </tr>
    <tr>
        <td>1</td>
        <td><b>Library</b></td>
        <td>Perpustakaan</td>
        <td><i>We study in the library every Friday.</i></td>
    </tr>
    <tr>
        <td>2</td>
        <td><b>Dictionary</b></td>
        <td>Kamus</td>
        <td><i>Open your dictionary on page 20.</i></td>
    </tr>
    <tr>
        <td>3</td>
        <td><b>Teacher</b></td>
        <td>Guru</td>
        <td><i>Our teacher explains the lesson clearly.</i></td>
    </tr>
</table>
                `.trim();
            }

            if (confirm('Terapkan template ini ke editor? Konten di editor akan digantikan dengan struktur template yang rapi.')) {
                if (isCodeMode) {
                    document.getElementById('konten_teks').value = content;
                } else {
                    quill.root.innerHTML = content;
                }
            }
        };

        // Fungsi Buka Live Preview Modal
        window.openLivePreview = function() {
            let currentHtml = isCodeMode ? document.getElementById('konten_teks').value : quill.root.innerHTML;
            const modalBody = document.getElementById('previewModalBody');
            
            // Cek jika teks polos tanpa tag HTML
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = currentHtml;
            if (tempDiv.innerText.trim() === currentHtml.trim() && !currentHtml.includes('<')) {
                modalBody.innerHTML = '<p>' + currentHtml.replace(/\n/g, '<br>') + '</p>';
            } else {
                modalBody.innerHTML = currentHtml;
            }

            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            previewModal.show();
        };
    });
    </script>
</body>
</html>