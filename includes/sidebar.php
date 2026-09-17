<?php
/**
 * File: includes/sidebar.php
 * Deskripsi: Panel navigasi samping modern & interaktif untuk siswa (SMP Swasta Nommensen).
 */
$active_page = isset($active_page) ? $active_page : '';

// Ambil info profil siswa dari sesi
$nama_siswa = $_SESSION['siswa_nama'] ?? 'Siswa';
$nis_siswa = $_SESSION['siswa_nis'] ?? '-';
$kelas_siswa = $_SESSION['siswa_kelas'] ?? 'VII';

// Ekstrak inisial nama
$words = preg_split('/\s+/', trim($nama_siswa));
$initials = '';
foreach (array_slice($words, 0, 2) as $w) {
    if (!empty($w)) $initials .= strtoupper($w[0]);
}
if (empty($initials)) $initials = 'SW';
?>
<!-- Sidebar Navigasi Siswa Modern -->
<aside class="siswa-sidebar" id="siswaSidebar">
    
    <!-- Brand Header -->
    <div class="sidebar-brand-wrapper">
        <div class="sidebar-brand-inner" onclick="toggleSidebar()">
            <div class="sidebar-brand-icon">
                <i class="bi bi-mortarboard-fill"></i>
                <span class="online-indicator" title="Sesi Siswa Aktif"></span>
            </div>
            <div class="sidebar-brand-text">
                <h2 class="brand-title">Nommensen</h2>
                <span class="brand-subtitle">English Portal &bull; SMP</span>
            </div>
        </div>
        <!-- Ikon hamburger (mobile) -->
        <button type="button" class="mobile-hamburger" aria-label="Buka Menu Navigasi" id="hamburgerBtn" onclick="toggleSidebar()">
            <i class="bi bi-list fs-2"></i>
        </button>
    </div>

    <!-- Mini Widget Profil Siswa -->
    <div class="sidebar-user-card">
        <div class="user-avatar-circle">
            <?= htmlspecialchars($initials) ?>
        </div>
        <div class="user-details">
            <div class="user-name" title="<?= htmlspecialchars($nama_siswa) ?>">
                <?= htmlspecialchars($nama_siswa) ?>
            </div>
            <div class="user-meta">
                <span class="user-badge-class">Kelas <?= htmlspecialchars($kelas_siswa) ?></span>
                <span class="user-nis"><?= htmlspecialchars($nis_siswa) ?></span>
            </div>
        </div>
    </div>

    <!-- Navigasi Menu Terstruktur -->
    <nav class="sidebar-nav-container">
        
        <!-- Seksi 1: Pembelajaran -->
        <div class="sidebar-section-label">
            <span>AKADEMIK &amp; MATERI</span>
        </div>
        <ul class="siswa-sidebar-menu">
            <li>
                <a href="menu.php" class="<?= $active_page === 'menu' ? 'active' : '' ?>">
                    <div class="nav-icon-box">
                        <i class="bi bi-grid-fill"></i>
                    </div>
                    <span class="nav-text">Beranda Belajar</span>
                    <span class="nav-pill-badge badge-blue">Fitur &amp; Bab</span>
                </a>
            </li>
            <li>
                <a href="kuis.php" class="<?= $active_page === 'kuis' ? 'active' : '' ?>">
                    <div class="nav-icon-box">
                        <i class="bi bi-patch-question-fill"></i>
                    </div>
                    <span class="nav-text">Kuis &amp; Latihan</span>
                    <span class="nav-pill-badge badge-amber">Evaluasi</span>
                </a>
            </li>
            <li>
                <a href="riwayat.php" class="<?= $active_page === 'riwayat' ? 'active' : '' ?>">
                    <div class="nav-icon-box">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <span class="nav-text">Riwayat Nilai</span>
                    <span class="nav-pill-badge badge-green">Skor</span>
                </a>
            </li>
        </ul>

        <!-- Seksi 2: Akun -->
        <div class="sidebar-section-label">
            <span>AKUN &amp; SISTEM</span>
        </div>
        <ul class="siswa-sidebar-menu">
            <li>
                <a href="ganti_password.php" class="<?= $active_page === 'ganti_password' ? 'active' : '' ?>">
                    <div class="nav-icon-box">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <span class="nav-text">Ganti Password</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Footer Navigasi -->
    <div class="siswa-sidebar-footer">
        <a href="../index.php" class="btn-sidebar-nav btn-sidebar-home">
            <i class="bi bi-house-door-fill"></i>
            <span>Kembali ke Beranda</span>
        </a>
        <a href="logout.php" class="btn-sidebar-nav btn-sidebar-logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun?')">
            <i class="bi bi-box-arrow-left"></i>
            <span>Keluar Akun</span>
        </a>
    </div>

</aside>
<script>
function toggleSidebar() {
    if (window.innerWidth <= 768) {
        var sidebar = document.getElementById('siswaSidebar');
        sidebar.classList.toggle('sidebar-open');
    }
}
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        var sidebar = document.getElementById('siswaSidebar');
        sidebar.classList.remove('sidebar-open');
    }
});
</script>