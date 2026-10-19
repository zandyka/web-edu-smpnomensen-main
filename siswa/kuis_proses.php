<?php
/**
 * File: siswa/kuis_proses.php
 * Deskripsi: Memproses jawaban kuis siswa, menghitung skor secara aman di sisi server,
 *            menyimpan hasil ke tb_hasil, dan mengarahkan ke halaman hasil kuis.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kuis.php");
    exit();
}

$id_kuis = isset($_POST['id_kuis']) ? intval($_POST['id_kuis']) : 0;
$id_siswa = $_SESSION['siswa_id'];
$elapsed_time_sec = isset($_POST['elapsed_time']) ? intval($_POST['elapsed_time']) : 0;

// Ambil jawaban dari POST
$student_answers = isset($_POST['answers']) ? json_decode($_POST['answers'], true) : [];

try {
    // Ambil semua soal untuk kuis ini dari DB untuk mencocokkan jawaban
    $stmt = $pdo->prepare("SELECT id_soal, jawaban_benar, tipe_soal FROM tb_soal WHERE id_kuis = :id_kuis ORDER BY id_soal ASC");
    $stmt->execute(['id_kuis' => $id_kuis]);
    $soal_list = $stmt->fetchAll();
    
    $total_soal = count($soal_list);
    if ($total_soal === 0) {
        header("Location: kuis.php");
        exit();
    }

    $jumlah_benar = 0;
    $jumlah_salah = 0;

    // Hitung jawaban benar & salah berdasarkan tipe soal
    foreach ($soal_list as $index => $soal) {
        $selected = isset($student_answers[$index]) ? trim($student_answers[$index]) : '';
        $correct = trim($soal['jawaban_benar']);
        $tipe = $soal['tipe_soal'] ?? 'pilihan_ganda';

        $is_correct = false;
        if ($tipe === 'susun_huruf') {
            // Hilangkan spasi dan simbol, bandingkan murni susunan huruf
            $clean_student = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $selected)));
            $accepted_list = explode('|', $correct);
            foreach ($accepted_list as $acc) {
                $clean_acc = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', trim($acc))));
                if ($clean_student === $clean_acc && $clean_student !== '') {
                    $is_correct = true;
                    break;
                }
            }
        } elseif ($tipe === 'isian' || $tipe === 'susun_kata') {
            // Normalisasi: kecilkan huruf, hilangkan tanda baca dan spasi ganda
            $clean_student = strtolower(trim(preg_replace('/[.,?!;:"\']/', '', preg_replace('/\s+/', ' ', $selected))));
            $accepted_list = explode('|', $correct);
            foreach ($accepted_list as $acc) {
                $clean_acc = strtolower(trim(preg_replace('/[.,?!;:"\']/', '', preg_replace('/\s+/', ' ', trim($acc)))));
                if ($clean_student === $clean_acc && $clean_student !== '') {
                    $is_correct = true;
                    break;
                }
            }
        } else {
            // Pilihan ganda atau True/False (A, B, C, D)
            if (strtoupper($selected) === strtoupper($correct) && $selected !== '') {
                $is_correct = true;
            }
        }

        if ($is_correct) {
            $jumlah_benar++;
        } else {
            $jumlah_salah++;
        }
    }

    // Hitung Skor (Skala 100)
    $skor = round(($jumlah_benar / $total_soal) * 100);

    // Simpan ke tb_hasil
    $stmt_insert = $pdo->prepare("INSERT INTO tb_hasil (id_siswa, id_kuis, skor, jumlah_benar, jumlah_salah) VALUES (:id_siswa, :id_kuis, :skor, :jumlah_benar, :jumlah_salah)");
    $stmt_insert->execute([
        'id_siswa' => $id_siswa,
        'id_kuis' => $id_kuis,
        'skor' => $skor,
        'jumlah_benar' => $jumlah_benar,
        'jumlah_salah' => $jumlah_salah
    ]);
    
    $id_hasil = $pdo->lastInsertId();

    // Simpan hasil ke session untuk ditampilkan di hasil_kuis.php
    $_SESSION['last_kuis_result'] = [
        'id_hasil' => $id_hasil,
        'id_kuis' => $id_kuis,
        'skor' => $skor,
        'jumlah_benar' => $jumlah_benar,
        'jumlah_salah' => $jumlah_salah,
        'elapsed_time' => $elapsed_time_sec
    ];

    header("Location: hasil_kuis.php");
    exit();

} catch (PDOException $e) {
    die("Gagal memproses hasil kuis: " . $e->getMessage());
}
