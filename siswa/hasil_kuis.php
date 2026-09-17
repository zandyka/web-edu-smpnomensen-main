<?php
/**
 * File: siswa/hasil_kuis.php
 * Deskripsi: Halaman Hasil Evaluasi Kuis Siswa.
 *            Menampilkan skor akhir, detail jawaban, sisa waktu pengerjaan,
 *            pesan motivasi dinamis, dan tombol navigasi aksi.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

// Cek jika session hasil kuis tidak ada, kembalikan ke daftar kuis
if (!isset($_SESSION['last_kuis_result'])) {
    header("Location: kuis.php");
    exit();
}

$result = $_SESSION['last_kuis_result'];
$id_kuis = $result['id_kuis'];
$skor = $result['skor'];
$jumlah_benar = $result['jumlah_benar'];
$jumlah_salah = $result['jumlah_salah'];
$elapsed_time_sec = $result['elapsed_time'];

// Ambil info nama kuis
try {
    $stmt = $pdo->prepare("SELECT judul_kuis FROM tb_kuis WHERE id_kuis = :id");
    $stmt->execute(['id' => $id_kuis]);
    $judul_kuis = $stmt->fetchColumn();
} catch (PDOException $e) {
    $judul_kuis = 'Kuis';
}

// Tentukan pesan motivasi dan warna berdasarkan skor
if ($skor >= 85) {
    $motivation_title = "Luar Biasa! Hebat Sekali!";
    $motivation_text = "Pertahankan prestasi hebatmu! Kamu telah menguasai materi pembelajaran bahasa Inggris ini dengan sangat baik.";
    $accent_color = "#16a34a"; // Hijau sukses
    $bg_motivation = "#d1fae5";
    $border_motivation = "#10b981";
} elseif ($skor >= 70) {
    $motivation_title = "Kerja Bagus!";
    $motivation_text = "Kamu sudah memahami materi ini dengan cukup baik. Ayo terus belajar untuk mendapatkan nilai sempurna!";
    $accent_color = "#2563eb"; // Biru info
    $bg_motivation = "#dbeafe";
    $border_motivation = "#3b82f6";
} else {
    $motivation_title = "Jangan Menyerah!";
    $motivation_text = "Nilaimu masih di bawah KKM. Jangan berkecil hati, ayo pelajari lagi materinya lalu ulangi mengerjakan kuis ini.";
    $accent_color = "#d97706"; // Amber/Kuning peringatan
    $bg_motivation = "#fef3c7";
    $border_motivation = "#f59e0b";
}

// Konversi detik pengerjaan ke format menit & detik
$minutes = floor($elapsed_time_sec / 60);
$seconds = $elapsed_time_sec % 60;
$time_string = "";
if ($minutes > 0) {
    $time_string .= $minutes . " Menit ";
}
$time_string .= $seconds . " Detik";

$page_title = 'Hasil Kuis';
$active_page = 'kuis';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';

// Hapus hasil kuis dari session setelah dibaca agar tidak bisa di-refresh/diakses langsung nanti
unset($_SESSION['last_kuis_result']);
?>

<!-- Helper Javascript untuk mendefinisikan Math.floor di PHP -->
<?php
// PHP helper untuk menit karena Math::floor di atas salah ketik
function format_elapsed_time($sec) {
    $m = floor($sec / 60);
    $s = $sec % 60;
    return ($m > 0 ? $m . " Menit " : "") . $s . " Detik";
}
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header d-flex justify-content-between align-items-center">
        <h1 class="h4 fw-bold text-dark mb-0">Hasil Evaluasi Kuis</h1>
        <div class="siswa-header-school">SMP Swasta Nommensen</div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="mx-auto" style="max-width: 650px;">
            
            <!-- Hasil Skor Besar (Bootstrap 5 Card) -->
            <div class="card border-0 shadow-sm rounded-4 text-center mb-4 bg-white overflow-hidden">
                <div class="card-header bg-light py-3 border-0 fw-bold text-secondary">
                    <?= htmlspecialchars($judul_kuis) ?>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <span class="text-uppercase tracking-wider fw-bold text-muted small">Skor Akhir Anda</span>
                    <div class="display-1 fw-bold my-2" style="color: <?= $accent_color ?>;">
                        <?= $skor ?>
                    </div>

                    <!-- Pesan Motivasi Dinamis -->
                    <div class="alert border-0 rounded-4 text-start p-3 p-md-4 mt-4 shadow-xs" style="background-color: <?= $bg_motivation ?>; color: <?= $accent_color ?>;">
                        <h4 class="h6 fw-bold mb-1"><i class="bi bi-star-fill me-1"></i><?= $motivation_title ?></h4>
                        <p class="small text-dark mb-0 leading-relaxed"><?= $motivation_text ?></p>
                    </div>
                </div>
            </div>

            <!-- Detail Statistik Pengerjaan -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h3 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">Rincian Hasil Pengerjaan</h3>
                
                <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted"><i class="bi bi-check-circle-fill text-success me-2"></i>Jawaban Benar:</span>
                    <strong class="text-success"><?= $jumlah_benar ?> Soal</strong>
                </div>
                
                <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted"><i class="bi bi-x-circle-fill text-danger me-2"></i>Jawaban Salah:</span>
                    <strong class="text-danger"><?= $jumlah_salah ?> Soal</strong>
                </div>

                <div class="d-flex justify-content-between py-2 small">
                    <span class="text-muted"><i class="bi bi-clock-history me-2"></i>Waktu Pengerjaan:</span>
                    <strong class="text-dark"><?= format_elapsed_time($elapsed_time_sec) ?></strong>
                </div>
            </div>

            <!-- Tombol Tindakan Navigasi -->
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <a href="kuis_kerjakan.php?id_kuis=<?= $id_kuis ?>" class="btn btn-outline-secondary btn-lg w-100 rounded-3 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-arrow-clockwise"></i>
                        <span>Ulangi Kuis</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="menu.php" class="btn btn-primary btn-lg w-100 rounded-3 fw-bold py-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-grid"></i>
                        <span>Menu Materi</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

<?php require_once '../includes/footer.php'; ?>
