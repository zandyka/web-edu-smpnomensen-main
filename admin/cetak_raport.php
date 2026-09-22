<?php
/**
 * File: admin/cetak_raport.php
 * Deskripsi: Halaman Cetak / Generate PDF Raport Evaluasi Belajar Siswa.
 *            Berstandar dokumen resmi sekolah lengkap dengan Kop Surat SMP Swasta Nommensen,
 *            identitas siswa, tabel capaian kuis, predikat, catatan guru, dan tanda tangan 3 pihak.
 */

require_once '../includes/auth_admin.php';
require_once '../config.php';

$id_siswa = isset($_GET['id_siswa']) ? intval($_GET['id_siswa']) : 0;
$tipe_periode = isset($_GET['tipe_periode']) ? $_GET['tipe_periode'] : 'semua';
$bulan = isset($_GET['bulan']) ? intval($_GET['bulan']) : intval(date('m'));
$tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));
$tgl_mulai = isset($_GET['tgl_mulai']) ? trim($_GET['tgl_mulai']) : '';
$tgl_selesai = isset($_GET['tgl_selesai']) ? trim($_GET['tgl_selesai']) : '';

if ($id_siswa <= 0) {
    die("ID Siswa tidak valid. Silakan kembali dan pilih siswa terlebih dahulu.");
}

// 1. Ambil data siswa
try {
    $stmt_siswa = $pdo->prepare("SELECT * FROM tb_siswa WHERE id_siswa = :id");
    $stmt_siswa->execute(['id' => $id_siswa]);
    $siswa = $stmt_siswa->fetch();
} catch (PDOException $e) {
    die("Gagal memuat data siswa: " . $e->getMessage());
}

if (!$siswa) {
    die("Data siswa tidak ditemukan di database.");
}

// 2. Susun Query Nilai Kuis berdasarkan filter periode
$sql_hasil = "
    SELECT h.*, k.judul_kuis, k.kategori_materi, k.nilai_lulus
    FROM tb_hasil h
    JOIN tb_kuis k ON h.id_kuis = k.id_kuis
    WHERE h.id_siswa = :id_siswa
";
$params = ['id_siswa' => $id_siswa];
$periode_label = "Seluruh Periode Pembelajaran";

$nama_bulan_arr = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

if ($tipe_periode === 'mingguan' && !empty($tgl_mulai) && !empty($tgl_selesai)) {
    $sql_hasil .= " AND DATE(h.waktu_selesai) BETWEEN :start_date AND :end_date";
    $params['start_date'] = $tgl_mulai;
    $params['end_date'] = $tgl_selesai;
    $periode_label = "Laporan Mingguan (" . date('d M Y', strtotime($tgl_mulai)) . " s.d. " . date('d M Y', strtotime($tgl_selesai)) . ")";
} elseif ($tipe_periode === 'bulanan' && $bulan > 0 && $tahun > 0) {
    $sql_hasil .= " AND MONTH(h.waktu_selesai) = :bulan AND YEAR(h.waktu_selesai) = :tahun";
    $params['bulan'] = $bulan;
    $params['tahun'] = $tahun;
    $nama_bln = $nama_bulan_arr[$bulan] ?? 'Bulan ' . $bulan;
    $periode_label = "Laporan Bulanan (" . $nama_bln . " " . $tahun . ")";
}

$sql_hasil .= " ORDER BY h.waktu_selesai ASC";

try {
    $stmt_hasil = $pdo->prepare($sql_hasil);
    $stmt_hasil->execute($params);
    $quiz_results = $stmt_hasil->fetchAll();
} catch (PDOException $e) {
    die("Gagal memuat data kuis: " . $e->getMessage());
}

// 3. Kalkulasi Statistik & Predikat
$total_kuis = count($quiz_results);
$total_skor = 0;
$skor_tertinggi = 0;
$skor_terendah = 100;
$total_tuntas = 0;
$total_benar = 0;
$total_salah = 0;

foreach ($quiz_results as $r) {
    $total_skor += $r['skor'];
    if ($r['skor'] > $skor_tertinggi) $skor_tertinggi = $r['skor'];
    if ($r['skor'] < $skor_terendah) $skor_terendah = $r['skor'];
    if ($r['skor'] >= 70) $total_tuntas++;
    $total_benar += $r['jumlah_benar'];
    $total_salah += $r['jumlah_salah'];
}

$rata_rata = $total_kuis > 0 ? round($total_skor / $total_kuis, 1) : 0;
if ($total_kuis === 0) $skor_terendah = 0;

// Penentuan Predikat & Deskripsi
if ($rata_rata >= 85) {
    $predikat = 'A';
    $status_kategori = 'Sangat Baik (Amat Memuaskan)';
    $deskripsi_evaluasi = 'Peserta didik menunjukkan penguasaan materi bahasa Inggris yang sangat prima, mandiri, dan konsisten di seluruh evaluasi bab.';
} elseif ($rata_rata >= 75) {
    $predikat = 'B';
    $status_kategori = 'Baik (Memuaskan)';
    $deskripsi_evaluasi = 'Peserta didik memahami konsep materi dengan baik dan berhasil melampaui KKM pada mayoritas kuis evaluasi.';
} elseif ($rata_rata >= 70) {
    $predikat = 'C';
    $status_kategori = 'Cukup (Tuntas KKM)';
    $deskripsi_evaluasi = 'Peserta didik telah mencapai standar KKM, namun disarankan untuk lebih memperdalam latihan vocabulary dan grammar.';
} else {
    $predikat = 'D';
    $status_kategori = 'Perlu Bimbingan (Belum Tuntas)';
    $deskripsi_evaluasi = 'Peserta didik memerlukan remedial dan pendampingan khusus pada materi dasar bahasa Inggris untuk mencapai standar KKM.';
}

$logo_path = '../assets/img/logo.png';
$has_logo = file_exists($logo_path);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport_<?= preg_replace('/[^A-Za-z0-9_-]/', '_', $siswa['nama_siswa']) ?>_<?= date('Ymd') ?></title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&family=Tinos:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Tinos', 'Times New Roman', serif;
            color: #111827;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            font-size: 13pt;
            line-height: 1.4;
        }

        /* Bilah Aksi di Layar (Tersembunyi saat Cetak) */
        .no-print-bar {
            background: #1e293b;
            color: #ffffff;
            padding: 0.85rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 9999;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            font-family: 'Inter', sans-serif;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 1.25rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-print {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-print:hover { background-color: #1d4ed8; }

        .btn-back {
            background-color: #475569;
            color: #ffffff;
        }
        .btn-back:hover { background-color: #334155; }

        /* Lembar Kertas Raport A4 */
        .paper-container {
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto 40px auto;
            background: #ffffff;
            padding: 20mm 20mm 20mm 20mm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        /* Kop Surat Resmi Sekolah */
        .kop-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 5px;
        }

        .kop-logo {
            width: 85px;
            height: 85px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .kop-text {
            flex: 1;
            text-align: center;
        }

        .kop-text h4 {
            margin: 0;
            font-size: 13pt;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .kop-text h2 {
            margin: 2px 0;
            font-size: 18pt;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #0f172a;
        }

        .kop-text p {
            margin: 0;
            font-size: 9.5pt;
            color: #334155;
            line-height: 1.3;
        }

        .kop-line-double {
            border-top: 3px solid #000000;
            border-bottom: 1px solid #000000;
            height: 4px;
            margin: 8px 0 16px 0;
        }

        /* Judul Dokumen */
        .doc-title {
            text-align: center;
            margin-bottom: 18px;
        }

        .doc-title h3 {
            margin: 0;
            font-size: 14pt;
            font-weight: 800;
            text-transform: uppercase;
            text-decoration: underline;
            letter-spacing: 0.5px;
        }

        .doc-title .doc-subtitle {
            margin-top: 3px;
            font-size: 11pt;
            font-weight: 700;
            color: #334155;
        }

        /* Tabel Identitas Siswa */
        .table-identity {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 11pt;
        }

        .table-identity td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .table-identity td.label {
            width: 22%;
            font-weight: 700;
            color: #1e293b;
        }

        .table-identity td.separator {
            width: 2%;
            text-align: center;
        }

        .table-identity td.val {
            width: 26%;
        }

        /* Tabel Nilai */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 10.5pt;
        }

        .table-data th, .table-data td {
            border: 1px solid #1e293b;
            padding: 6px 8px;
        }

        .table-data th {
            background-color: #f1f5f9 !important;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            font-size: 10pt;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }

        /* Badge Status */
        .badge-status-pass {
            font-weight: 700;
            color: #15803d;
        }
        .badge-status-fail {
            font-weight: 700;
            color: #b91c1c;
        }

        /* Tabel Ringkasan Rata-rata & Predikat */
        .summary-box {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 16px;
            font-size: 10.5pt;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td, .summary-table th {
            border: 1px solid #1e293b;
            padding: 6px 10px;
        }

        .summary-table th {
            background: #f8fafc !important;
            text-align: left;
            width: 45%;
        }

        .evaluation-note {
            border: 1px solid #1e293b;
            padding: 10px 12px;
            margin-bottom: 25px;
            font-size: 10pt;
            background: #fafafa;
        }

        /* Area Tanda Tangan */
        .signature-wrapper {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            page-break-inside: avoid;
            font-size: 11pt;
        }

        .sig-col {
            width: 30%;
            text-align: center;
        }

        .sig-space {
            height: 70px;
        }

        .sig-name {
            font-weight: 700;
            text-decoration: underline;
        }

        .sig-nip {
            font-size: 9.5pt;
            color: #334155;
            margin-top: 2px;
        }

        /* Aturan Cetak (Print) */
        @media print {
            body {
                background: #ffffff !important;
                font-size: 11pt !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .paper-container {
                margin: 0 !important;
                padding: 10mm 15mm !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm 10mm 10mm 10mm;
            }
        }
    </style>
</head>
<body>

    <!-- Bilah Aksi Atas (Hanya Tampil di Layar Monitor) -->
    <div class="no-print-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="bi bi-file-earmark-pdf-fill fs-4 text-warning"></i>
            <div>
                <strong>Pratinjau Raport Belajar Siswa</strong>
                <div style="font-size: 0.75rem; color: #cbd5e1;">Siap dicetak atau disimpan langsung sebagai dokumen PDF</div>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="laporan_nilai.php?tab=raport&id_siswa=<?= $id_siswa ?>" class="btn-action btn-back">
                <i class="bi bi-arrow-left"></i>Kembali
            </a>
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="bi bi-printer-fill"></i>Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Lembar Raport Fisik A4 -->
    <div class="paper-container">
        
        <!-- 1. KOP SURAT RESMI SEKOLAH -->
        <div class="kop-wrapper">
            <?php if ($has_logo): ?>
                <img src="<?= $logo_path ?>" alt="Logo Sekolah" class="kop-logo">
            <?php else: ?>
                <div style="width: 80px; height: 80px; border: 2px solid #000; display:flex; align-items:center; justify-content:center; font-weight:800;">SMP</div>
            <?php endif; ?>
            <div class="kop-text">
                <h4>YAYASAN UNIVERSITAS HKBP NOMMENSEN</h4>
                <h2>SMP SWASTA NOMMENSEN MEDAN</h2>
                <p>NSS: 204026001001 &bull; NPSN: 10210871 &bull; Status Akreditasi: A (Amat Baik)</p>
                <p>Alamat: Jl. Sutomo No. 1, Perintis, Kec. Medan Timur, Kota Medan, Sumatera Utara 20234</p>
                <p>Telepon: (061) 4522922 &bull; Surat Elektronik: smpnommensen@gmail.com</p>
            </div>
        </div>

        <div class="kop-line-double"></div>

        <!-- 2. JUDUL RAPORT -->
        <div class="doc-title">
            <h3>LEMBAR HASIL EVALUASI CAPAIAN BELAJAR SISWA</h3>
            <div class="doc-subtitle"><?= htmlspecialchars($periode_label) ?></div>
        </div>

        <!-- 3. IDENTITAS PESERTA DIDIK -->
        <table class="table-identity">
            <tr>
                <td class="label">Nama Peserta Didik</td>
                <td class="separator">:</td>
                <td class="val fw-bold"><?= htmlspecialchars($siswa['nama_siswa']) ?></td>
                <td class="label">Mata Pelajaran</td>
                <td class="separator">:</td>
                <td class="val">Bahasa Inggris (Kelas VII)</td>
            </tr>
            <tr>
                <td class="label">Nomor Induk Siswa (NIS)</td>
                <td class="separator">:</td>
                <td class="val"><?= htmlspecialchars($siswa['nis']) ?></td>
                <td class="label">Standar KKM</td>
                <td class="separator">:</td>
                <td class="val"><strong>70 Poin</strong></td>
            </tr>
            <tr>
                <td class="label">Kelas / Rombel</td>
                <td class="separator">:</td>
                <td class="val"><?= htmlspecialchars($siswa['kelas']) ?></td>
                <td class="label">Tahun Ajaran</td>
                <td class="separator">:</td>
                <td class="val"><?= date('Y') ?> / <?= date('Y') + 1 ?></td>
            </tr>
        </table>

        <!-- 4. TABEL CAPAIAN NILAI KUIS -->
        <table class="table-data">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 42%;">Materi Pembelajaran / Judul Kuis</th>
                    <th style="width: 18%;">Tanggal Ujian</th>
                    <th style="width: 10%;">Benar</th>
                    <th style="width: 10%;">Salah</th>
                    <th style="width: 14%;">Skor (0-100)</th>
                    <th style="width: 14%;">Status KKM</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quiz_results)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px; font-style: italic; color: #64748b;">
                            Belum ada aktivitas pengerjaan kuis pada periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($quiz_results as $i => $row): ?>
                        <?php 
                        $lulus = $row['skor'] >= 70;
                        ?>
                        <tr>
                            <td class="text-center"><?= $i + 1 ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['judul_kuis']) ?></strong>
                                <div style="font-size: 8.5pt; color: #475569;"><?= htmlspecialchars($row['kategori_materi']) ?></div>
                            </td>
                            <td class="text-center">
                                <?= date('d/m/Y - H:i', strtotime($row['waktu_selesai'])) ?>
                            </td>
                            <td class="text-center" style="color: #16a34a; font-weight:700;"><?= $row['jumlah_benar'] ?></td>
                            <td class="text-center" style="color: #dc2626; font-weight:700;"><?= $row['jumlah_salah'] ?></td>
                            <td class="text-center fw-bold" style="font-size: 11pt; color: <?= $lulus ? '#16a34a' : '#dc2626' ?>;">
                                <?= $row['skor'] ?>
                            </td>
                            <td class="text-center">
                                <?php if ($lulus): ?>
                                    <span class="badge-status-pass">TUNTAS</span>
                                <?php else: ?>
                                    <span class="badge-status-fail">REMIDIAL</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- 5. RANGKUMAN STATISTIK CAPAIAN -->
        <div class="summary-box">
            <table class="summary-table" style="width: 50%;">
                <tr>
                    <th>Total Kuis Selesai</th>
                    <td class="fw-bold"><?= $total_kuis ?> Kuis</td>
                </tr>
                <tr>
                    <th>Rata-Rata Nilai Kuis</th>
                    <td class="fw-bold" style="font-size: 11.5pt; color: #1e3a8a;"><?= $rata_rata ?> / 100</td>
                </tr>
                <tr>
                    <th>Nilai Tertinggi</th>
                    <td class="fw-bold" style="color: #16a34a;"><?= $skor_tertinggi ?> Poin</td>
                </tr>
            </table>

            <table class="summary-table" style="width: 50%;">
                <tr>
                    <th>Predikat Capaian</th>
                    <td class="fw-bold" style="font-size: 12pt; color: #1e3a8a;">
                        <?= $predikat ?> (<?= $status_kategori ?>)
                    </td>
                </tr>
                <tr>
                    <th>Ketuntasan Belajar</th>
                    <td class="fw-bold">
                        <?= $total_tuntas ?> dari <?= $total_kuis ?> Kuis Tuntas (<?= $total_kuis > 0 ? round(($total_tuntas / $total_kuis) * 100) : 0 ?>%)
                    </td>
                </tr>
                <tr>
                    <th>Total Akumulasi Jawaban</th>
                    <td>Benar: <strong><?= $total_benar ?></strong> &bull; Salah: <strong><?= $total_salah ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- 6. CATATAN PERKEMBANGAN BELAJAR -->
        <div class="evaluation-note">
            <strong>Catatan &amp; Evaluasi Guru Pengampu:</strong><br>
            <i>"<?= htmlspecialchars($deskripsi_evaluasi) ?>"</i>
        </div>

        <!-- 7. LEMBAR PENGESAHAN TANDA TANGAN (3 PIHAK) -->
        <div class="signature-wrapper">
            <div class="sig-col">
                <div>Mengetahui,</div>
                <div>Orang Tua / Wali Siswa</div>
                <div class="sig-space"></div>
                <div class="sig-name">( ............................................ )</div>
                <div class="sig-nip">Tanda Tangan &amp; Nama Terang</div>
            </div>

            <div class="sig-col">
                <div>Kota Medan, <?= date('d') ?> <?= $nama_bulan_arr[intval(date('m'))] ?> <?= date('Y') ?></div>
                <div>Guru Mata Pelajaran,</div>
                <div class="sig-space"></div>
                <div class="sig-name"><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Guru Bahasa Inggris') ?></div>
                <div class="sig-nip">NIP/NUPTK: <?= htmlspecialchars($_SESSION['admin_nip'] ?? '-') ?></div>
            </div>

            <div class="sig-col">
                <div>Mengetahui &amp; Mengesahkan,</div>
                <div>Kepala SMP Swasta Nommensen</div>
                <div class="sig-space"></div>
                <div class="sig-name">Drs. J. Siregar, M.Pd.</div>
                <div class="sig-nip">NIP: 19740512 200212 1 003</div>
            </div>
        </div>

    </div>

</body>
</html>