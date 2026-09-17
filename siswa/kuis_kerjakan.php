<?php
/**
 * File: siswa/kuis_kerjakan.php
 * Deskripsi: Halaman Pengerjaan Kuis Siswa.
 *            Menampilkan soal satu per satu, indikator progres, hitung mundur (timer),
 *            opsi jawaban interaktif, feedback instan, dan penyimpanan sementara di localStorage.
 */

// Memroteksi halaman siswa agar wajib login
require_once '../includes/auth_siswa.php';

// Memanggil konfigurasi database
require_once '../config.php';

$id_kuis = isset($_GET['id_kuis']) ? intval($_GET['id_kuis']) : 0;
$id_siswa = $_SESSION['siswa_id'];

// Ambil data kuis
try {
    $stmt_quiz = $pdo->prepare("SELECT * FROM tb_kuis WHERE id_kuis = :id");
    $stmt_quiz->execute(['id' => $id_kuis]);
    $quiz = $stmt_quiz->fetch();
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}

if (!$quiz) {
    header("Location: kuis.php");
    exit();
}

// Ambil semua soal terkait kuis ini
try {
    $stmt_soal = $pdo->prepare("SELECT * FROM tb_soal WHERE id_kuis = :id ORDER BY id_soal ASC");
    $stmt_soal->execute(['id' => $id_kuis]);
    $questions = $stmt_soal->fetchAll();
} catch (PDOException $e) {
    die("Error database: " . $e->getMessage());
}

$total_soal = count($questions);
if ($total_soal === 0) {
    header("Location: kuis.php");
    exit();
}

$page_title = 'Pengerjaan Kuis';
$active_page = 'kuis';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header d-flex justify-content-between align-items-center">
        <h1 class="h4 fw-bold text-dark mb-0">Mengerjakan Kuis</h1>
        <div class="badge bg-danger-subtle text-danger fs-6 fw-bold py-2 px-3 rounded-pill border border-danger-subtle shadow-sm" id="timer-box">
            <i class="bi bi-clock me-1"></i>Sisa Waktu: --:--
        </div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="mx-auto" style="max-width: 800px;">
        
            <!-- Header Informasi Kuis -->
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-3 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill mb-1">
                            <?= htmlspecialchars($quiz['kategori_materi']) ?>
                        </span>
                        <h2 class="h5 fw-bold text-dark mb-0"><?= htmlspecialchars($quiz['judul_kuis']) ?></h2>
                    </div>
                    <div class="text-end">
                        <span id="question-progress" class="badge bg-primary-subtle text-primary fs-6 fw-bold py-2 px-3 rounded-pill">
                            Soal 1 dari <?= $total_soal ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Progress Bar Visual (Bootstrap 5) -->
            <div class="progress mb-4 rounded-pill shadow-xs" style="height: 10px;">
                <div id="progress-bar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= (1 / $total_soal) * 100 ?>%;" aria-valuenow="1" aria-valuemin="0" aria-valuemax="<?= $total_soal ?>"></div>
            </div>

            <!-- Card Soal Aktif -->
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-body p-4 p-md-5">
                    <h3 id="question-text" class="h5 fw-bold text-dark mb-4 leading-relaxed">
                        -- memuat soal --
                    </h3>

                    <!-- Pilihan Jawaban A, B, C, D -->
                    <div class="d-flex flex-column gap-3" id="options-container">
                        <!-- Dinamis terisi oleh JS -->
                    </div>

                    <!-- Feedback Instan Benar / Salah -->
                    <div id="feedback-box" class="mt-4 p-3 rounded-3 fw-bold small align-items-center gap-2" style="display: none;">
                        <!-- Dinamis terisi oleh JS -->
                    </div>
                </div>
            </div>

            <!-- Tombol Navigasi Bawah -->
            <div class="d-flex justify-content-end mb-4">
                <button id="next-btn" class="btn btn-primary btn-lg rounded-3 fw-bold px-4 py-2 shadow-sm" style="display: none;">
                    Berikutnya &rarr;
                </button>
            </div>

        </div>
    </div>

    <!-- Script Pengendali Kuis -->
    <script>
        // Array Soal dari PHP
        const questions = <?= json_encode($questions) ?>;
        const totalQuestions = questions.length;
        const quizId = <?= $id_kuis ?>;
        const siswaId = <?= $id_siswa ?>;
        const waktuKuisMin = <?= intval($quiz['waktu_pengerjaan']) ?>;
        
        // State Kuis
        let currentIndex = 0;
        let timerInterval = null;
        
        // Kunci Penyimpanan Sementara LocalStorage
        const storageKeyAnswers = `kuis_jawaban_${quizId}_${siswaId}`;
        const storageKeyTime = `kuis_sisa_waktu_${quizId}_${siswaId}`;

        // Inisialisasi Jawaban Sementara di LocalStorage
        let answers = JSON.parse(localStorage.getItem(storageKeyAnswers)) || {};

        // Inisialisasi Timer (Jika reload page, sisa waktu tetap berjalan dari yang disimpan di localStorage)
        let timeLeft = parseInt(localStorage.getItem(storageKeyTime)) ?? (waktuKuisMin * 60);
        if (isNaN(timeLeft) || timeLeft <= 0) {
            timeLeft = waktuKuisMin * 60;
        }

        // Tampilkan soal pertama saat dimuat
        document.addEventListener("DOMContentLoaded", function() {
            startTimer();
            displayQuestion();
        });

        // 1. Fungsi Jalankan Timer
        function startTimer() {
            updateTimerDisplay();
            timerInterval = setInterval(function() {
                timeLeft--;
                localStorage.setItem(storageKeyTime, timeLeft);
                updateTimerDisplay();

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    alert("Waktu pengerjaan kuis telah habis! Jawaban Anda akan otomatis dikirim.");
                    finishQuiz();
                }
            }, 1000);
        }

        // 2. Tampilkan Detik ke format MM:SS
        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const timerBox = document.getElementById("timer-box");
            
            // Format padding nol
            const displayMin = minutes < 10 ? '0' + minutes : minutes;
            const displaySec = seconds < 10 ? '0' + seconds : seconds;
            
            timerBox.innerText = `Sisa Waktu: ${displayMin}:${displaySec}`;

            // Peringatan jika sisa waktu di bawah 1 menit (warna merah berkedip)
            if (timeLeft < 60) {
                timerBox.style.backgroundColor = '#fecdd3';
                timerBox.style.color = '#be123c';
                timerBox.style.borderColor = '#e11d48';
            }
        }

        // 3. Tampilkan Soal Aktif
        function displayQuestion() {
            const currentQuestion = questions[currentIndex];
            
            // Update Teks Soal & Progres
            document.getElementById("question-text").innerText = `${currentIndex + 1}. ${currentQuestion.pertanyaan}`;
            document.getElementById("question-progress").innerText = `Soal ${currentIndex + 1} dari ${totalQuestions}`;
            
            // Update Progress Bar
            const progressPercent = ((currentIndex + 1) / totalQuestions) * 100;
            document.getElementById("progress-bar").style.width = progressPercent + '%';

            // Bersihkan Balon Pilihan lama & Feedback
            const optionsContainer = document.getElementById("options-container");
            optionsContainer.innerHTML = '';
            
            const feedbackBox = document.getElementById("feedback-box");
            feedbackBox.style.display = 'none';
            
            const nextBtn = document.getElementById("next-btn");
            nextBtn.style.display = 'none';

            // Array Opsi A, B, C, D
            const opts = [
                { key: 'A', text: currentQuestion.opsi_a },
                { key: 'B', text: currentQuestion.opsi_b },
                { key: 'C', text: currentQuestion.opsi_c },
                { key: 'D', text: currentQuestion.opsi_d }
            ];

            // Tampilkan pilihan sebagai tombol
            opts.forEach(function(opt) {
                const btn = document.createElement("button");
                btn.className = "opt-btn";
                btn.style.textAlign = "left";
                btn.style.background = "#ffffff";
                btn.style.border = "1.5px solid #cbd5e1";
                btn.style.borderRadius = "6px";
                btn.style.padding = "0.75rem 1.25rem";
                btn.style.fontSize = "0.95rem";
                btn.style.fontFamily = "inherit";
                btn.style.fontWeight = "600";
                btn.style.cursor = "pointer";
                btn.style.color = "#374151";
                btn.style.transition = "all 0.2s";
                
                btn.innerHTML = `<span style="color: var(--accent-blue); font-weight: 800; margin-right: 8px;">${opt.key}.</span> ${opt.text}`;
                
                // Tambahkan event click untuk menjawab
                btn.onclick = function() {
                    selectAnswer(opt.key, currentQuestion.jawaban_benar, btn);
                };

                optionsContainer.appendChild(btn);
            });

            // Periksa jika soal ini sebelumnya sudah dijawab (kasus reload atau navigasi kembali)
            if (answers[currentIndex] !== undefined) {
                restoreAnswer(answers[currentIndex], currentQuestion.jawaban_benar);
            }
        }

        // 4. Proses Memilih Jawaban & Umpan Balik Instan
        function selectAnswer(selectedKey, correctKey, clickedBtn) {
            // Simpan jawaban siswa secara lokal di array state & localStorage
            answers[currentIndex] = selectedKey;
            localStorage.setItem(storageKeyAnswers, JSON.stringify(answers));

            // Ambil semua tombol opsi
            const buttons = document.querySelectorAll(".opt-btn");
            
            // Matikan tombol-tombol agar tidak bisa diklik lagi untuk soal ini
            buttons.forEach(function(btn) {
                btn.disabled = true;
                btn.style.cursor = "default";
                
                // Cari opsi jawaban benar untuk disorot warna hijau
                const optLabel = btn.innerText.substring(0, 1);
                if (optLabel === correctKey) {
                    btn.style.borderColor = "#16a34a";
                    btn.style.backgroundColor = "#d1fae5";
                    btn.style.color = "#15803d";
                }
            });

            const feedbackBox = document.getElementById("feedback-box");
            feedbackBox.style.display = "flex";

            // Jika Jawaban BENAR
            if (selectedKey === correctKey) {
                clickedBtn.style.borderColor = "#16a34a";
                clickedBtn.style.backgroundColor = "#d1fae5";
                clickedBtn.style.color = "#15803d";
                
                feedbackBox.style.backgroundColor = "#d1fae5";
                feedbackBox.style.color = "#065f46";
                feedbackBox.style.border = "1px solid #10b981";
                feedbackBox.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Jawaban Benar! Kerja bagus.
                `;
            } else {
                // Jika Jawaban SALAH
                clickedBtn.style.borderColor = "#ef4444";
                clickedBtn.style.backgroundColor = "#fee2e2";
                clickedBtn.style.color = "#b91c1c";

                feedbackBox.style.backgroundColor = "#fee2e2";
                feedbackBox.style.color = "#991b1b";
                feedbackBox.style.border = "1px solid #f87171";
                feedbackBox.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Jawaban Salah. Jawaban yang benar adalah ${correctKey}.
                `;
            }

            // Tampilkan tombol "Berikutnya"
            const nextBtn = document.getElementById("next-btn");
            nextBtn.style.display = "block";
            
            // Ubah teks tombol jika sudah mencapai soal terakhir
            if (currentIndex === totalQuestions - 1) {
                nextBtn.innerText = "Selesai & Kumpulkan Kuis";
                nextBtn.style.backgroundColor = "#16a34a";
                nextBtn.style.borderColor = "#15803d";
            } else {
                nextBtn.innerText = "Berikutnya \u2192";
            }
        }

        // 5. Kembalikan State Jawaban Jika Siswa memuat ulang halaman
        function restoreAnswer(selectedKey, correctKey) {
            const buttons = document.querySelectorAll(".opt-btn");
            
            buttons.forEach(function(btn) {
                btn.disabled = true;
                btn.style.cursor = "default";
                const optLabel = btn.innerText.substring(0, 1);
                
                if (optLabel === correctKey) {
                    btn.style.borderColor = "#16a34a";
                    btn.style.backgroundColor = "#d1fae5";
                    btn.style.color = "#15803d";
                }
                
                if (optLabel === selectedKey && selectedKey !== correctKey) {
                    btn.style.borderColor = "#ef4444";
                    btn.style.backgroundColor = "#fee2e2";
                    btn.style.color = "#b91c1c";
                }
            });

            const feedbackBox = document.getElementById("feedback-box");
            feedbackBox.style.display = "flex";

            if (selectedKey === correctKey) {
                feedbackBox.style.backgroundColor = "#d1fae5";
                feedbackBox.style.color = "#065f46";
                feedbackBox.style.border = "1px solid #10b981";
                feedbackBox.innerHTML = "Jawaban Benar! Kerja bagus.";
            } else {
                feedbackBox.style.backgroundColor = "#fee2e2";
                feedbackBox.style.color = "#991b1b";
                feedbackBox.style.border = "1px solid #f87171";
                feedbackBox.innerHTML = `Jawaban Salah. Jawaban yang benar adalah ${correctKey}.`;
            }

            const nextBtn = document.getElementById("next-btn");
            nextBtn.style.display = "block";
            if (currentIndex === totalQuestions - 1) {
                nextBtn.innerText = "Selesai & Kumpulkan Kuis";
                nextBtn.style.backgroundColor = "#16a34a";
                nextBtn.style.borderColor = "#15803d";
            }
        }

        // 6. Tombol Aksi Navigasi Selanjutnya / Selesai
        document.getElementById("next-btn").addEventListener("click", function() {
            if (currentIndex === totalQuestions - 1) {
                // Selesai Kuis
                finishQuiz();
            } else {
                // Lanjut ke soal berikutnya
                currentIndex++;
                displayQuestion();
            }
        });

        // 7. Menyelesaikan Sesi Kuis, Submit Jawaban, & Pembersihan Cache
        function finishQuiz() {
            // Hentikan interval timer
            clearInterval(timerInterval);

            // Hitung waktu pengerjaan (dalam detik)
            const elapsed = (waktuKuisMin * 60) - timeLeft;

            // Buat form dinamis untuk submit ke kuis_proses.php secara aman
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'kuis_proses.php';

            const kuisInput = document.createElement('input');
            kuisInput.type = 'hidden';
            kuisInput.name = 'id_kuis';
            kuisInput.value = quizId;
            form.appendChild(kuisInput);

            const elapsedInput = document.createElement('input');
            elapsedInput.type = 'hidden';
            elapsedInput.name = 'elapsed_time';
            elapsedInput.value = elapsed;
            form.appendChild(elapsedInput);

            const answersInput = document.createElement('input');
            answersInput.type = 'hidden';
            answersInput.name = 'answers';
            answersInput.value = JSON.stringify(answers);
            form.appendChild(answersInput);

            document.body.appendChild(form);

            // Hapus cache pengerjaan kuis ini dari localStorage sebelum submit
            localStorage.removeItem(storageKeyAnswers);
            localStorage.removeItem(storageKeyTime);

            form.submit();
        }
    </script>

<?php
require_once '../includes/footer.php';
?>
