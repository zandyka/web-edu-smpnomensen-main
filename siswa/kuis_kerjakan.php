<?php
/**
 * File: siswa/kuis_kerjakan.php
 * Deskripsi: Halaman Pengerjaan Kuis Siswa (Mode Ujian Anti-Contek).
 *            Menampilkan soal satu per satu, palet nomor soal (1-20), indikator pilihan netral,
 *            hitung mundur (timer), tanpa menampilkan hasil benar/salah secara instan agar tidak dicontek,
 *            serta menyimpan progres di localStorage hingga selesai disubmit.
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

// Ambil butir soal TANPA menyertakan jawaban_benar ke frontend untuk keamanan penuh (anti-inspect devtools)
try {
    $stmt_soal = $pdo->prepare("SELECT id_soal, id_kuis, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d FROM tb_soal WHERE id_kuis = :id ORDER BY id_soal ASC");
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

$page_title = 'Pengerjaan Kuis: ' . $quiz['judul_kuis'];
$active_page = 'kuis';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<style>
/* Styling Tombol Pilihan Jawaban (Anti-Contek / Netral) */
.opt-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    font-size: 0.95rem;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.opt-card:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    transform: translateY(-1px);
}

/* Opsi Aktif Terpilih (Biru Elegan Netral - TIDAK membocorkan Benar/Salah) */
.opt-card.selected {
    border-color: #2563eb !important;
    background-color: #eff6ff !important;
    color: #1e40af !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
}

.opt-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #475569;
    font-weight: 800;
    margin-right: 12px;
    flex-shrink: 0;
    transition: all 0.2s ease;
}

.opt-card.selected .opt-badge {
    background: #2563eb;
    color: #ffffff;
}

/* Palet Nomor Soal (CBT Grid) */
.palette-btn {
    width: 38px;
    height: 38px;
    padding: 0;
    font-size: 0.85rem;
    font-weight: 700;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    cursor: pointer;
}

.palette-btn.unanswered {
    background-color: #f1f5f9;
    color: #64748b;
    border: 1px solid #cbd5e1;
}

.palette-btn.answered {
    background-color: #2563eb;
    color: #ffffff;
    border: 1px solid #1d4ed8;
}

.palette-btn.current {
    box-shadow: 0 0 0 3px #fbbf24 !important;
    font-weight: 800;
}
</style>

<!-- Area Konten Utama Siswa -->
<main class="siswa-main">
    <header class="siswa-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h4 fw-bold text-dark mb-0">Evaluasi Mandiri</h1>
            <div class="small text-muted">Jawablah pertanyaan dengan cermat dan teliti</div>
        </div>
        <div class="badge bg-danger-subtle text-danger fs-6 fw-bold py-2 px-3 rounded-pill border border-danger-subtle shadow-sm d-flex align-items-center gap-1" id="timer-box">
            <i class="bi bi-clock-history"></i>
            <span>Sisa Waktu: --:--</span>
        </div>
    </header>

    <div class="siswa-content container-fluid px-3 px-md-4 py-4">
        <div class="mx-auto" style="max-width: 920px;">
        
            <!-- Header Informasi Kuis -->
            <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-3 bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill mb-1">
                            <?= htmlspecialchars($quiz['kategori_materi']) ?>
                        </span>
                        <h2 class="h5 fw-bold text-dark mb-0"><?= htmlspecialchars($quiz['judul_kuis']) ?></h2>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span id="answered-counter" class="badge bg-info-subtle text-info-emphasis fs-6 fw-bold py-2 px-3 rounded-pill">
                            0 dari <?= $total_soal ?> Terjawab
                        </span>
                        <span id="question-progress" class="badge bg-primary-subtle text-primary fs-6 fw-bold py-2 px-3 rounded-pill">
                            Soal 1 dari <?= $total_soal ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Palet Nomor Soal (Daftar Nomor Soal) -->
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-secondary text-uppercase tracking-wider">
                            <i class="bi bi-grid-3x3-gap-fill me-1 text-primary"></i>Daftar Nomor Soal
                        </span>
                        <span class="badge bg-light text-muted border small">
                            <span class="badge bg-primary p-1 me-1" style="font-size: 8px;"> </span> Terisi
                            <span class="badge bg-secondary p-1 ms-2 me-1" style="font-size: 8px;"> </span> Belum
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-2" id="palette-container">
                        <!-- Terisi otomatis oleh JS -->
                    </div>
                </div>
            </div>

            <!-- Progress Bar Visual -->
            <div class="progress mb-4 rounded-pill shadow-xs" style="height: 8px;">
                <div id="progress-bar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= (1 / $total_soal) * 100 ?>%;" aria-valuenow="1" aria-valuemin="0" aria-valuemax="<?= $total_soal ?>"></div>
            </div>

            <!-- Card Soal Aktif -->
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center text-muted small mb-3 pb-2 border-bottom">
                        <span id="current-badge-indicator" class="fw-bold text-primary">PERTANYAAN 1</span>
                        <span class="fst-italic"><i class="bi bi-info-circle me-1"></i>Pilih salah satu jawaban (A, B, C, atau D)</span>
                    </div>

                    <h3 id="question-text" class="h5 fw-bold text-dark mb-4 leading-relaxed">
                        -- Memuat pertanyaan... --
                    </h3>

                    <!-- Pilihan Jawaban A, B, C, D (Tanpa Bocoran Warna Benar/Salah) -->
                    <div class="d-flex flex-column gap-3" id="options-container">
                        <!-- Terisi otomatis oleh JS -->
                    </div>
                </div>
            </div>

            <!-- Tombol Navigasi Bawah (Sebelumnya / Berikutnya / Selesai) -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button id="prev-btn" class="btn btn-outline-secondary btn-lg rounded-3 fw-bold px-4 py-2 shadow-sm" style="display: none;">
                    &larr; Sebelumnya
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button id="next-btn" class="btn btn-primary btn-lg rounded-3 fw-bold px-4 py-2 shadow-sm">
                        Berikutnya &rarr;
                    </button>
                </div>
            </div>

        </div>
    </div>

    <!-- Script Pengendali Kuis (Aman & Anti-Contek) -->
    <script>
        // Array Soal dari PHP (Aman: TIDAK mengandung jawaban_benar)
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

        // Inisialisasi Timer
        let timeLeft = parseInt(localStorage.getItem(storageKeyTime));
        if (isNaN(timeLeft) || timeLeft <= 0) {
            timeLeft = waktuKuisMin * 60;
        }

        // Jalankan saat dokumen siap
        document.addEventListener("DOMContentLoaded", function() {
            startTimer();
            buildPalette();
            displayQuestion();
        });

        // 1. Jalankan Timer
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

        // 2. Format Tampilan Waktu (MM:SS)
        function updateTimerDisplay() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            const timerBox = document.getElementById("timer-box");
            
            const displayMin = minutes < 10 ? '0' + minutes : minutes;
            const displaySec = seconds < 10 ? '0' + seconds : seconds;
            
            timerBox.innerHTML = `<i class="bi bi-clock-history"></i><span>Sisa Waktu: ${displayMin}:${displaySec}</span>`;

            if (timeLeft < 180) { // Di bawah 3 menit
                timerBox.className = "badge bg-danger text-white fs-6 fw-bold py-2 px-3 rounded-pill shadow-sm d-flex align-items-center gap-1";
            }
        }

        // 3. Bangun Palet Nomor Soal (1 s.d. totalQuestions)
        function buildPalette() {
            const paletteContainer = document.getElementById("palette-container");
            paletteContainer.innerHTML = '';

            for (let i = 0; i < totalQuestions; i++) {
                const btn = document.createElement("button");
                btn.type = "button";
                btn.className = "palette-btn unanswered";
                btn.id = `palette-item-${i}`;
                btn.innerText = (i + 1);
                btn.title = `Pindah ke Soal ${i + 1}`;

                if (answers[i] !== undefined) {
                    btn.className = "palette-btn answered";
                }

                if (i === currentIndex) {
                    btn.classList.add("current");
                }

                btn.onclick = function() {
                    jumpToQuestion(i);
                };

                paletteContainer.appendChild(btn);
            }
            updateAnsweredCount();
        }

        // 4. Perbarui Palet Soal & Penghitung Terjawab
        function updatePalette() {
            for (let i = 0; i < totalQuestions; i++) {
                const btn = document.getElementById(`palette-item-${i}`);
                if (!btn) continue;

                btn.className = "palette-btn";
                if (answers[i] !== undefined) {
                    btn.classList.add("answered");
                } else {
                    btn.classList.add("unanswered");
                }

                if (i === currentIndex) {
                    btn.classList.add("current");
                }
            }
            updateAnsweredCount();
        }

        function updateAnsweredCount() {
            const answeredCount = Object.keys(answers).length;
            const counter = document.getElementById("answered-counter");
            if (counter) {
                counter.innerText = `${answeredCount} dari ${totalQuestions} Terjawab`;
            }
        }

        // 5. Pindah Langsung ke Nomor Soal Tertentu
        function jumpToQuestion(index) {
            if (index >= 0 && index < totalQuestions) {
                currentIndex = index;
                displayQuestion();
            }
        }

        // 6. Tampilkan Soal Aktif (Tanpa Warna Benar/Salah)
        function displayQuestion() {
            const currentQuestion = questions[currentIndex];
            
            // Perbarui Teks & Label Progres
            document.getElementById("current-badge-indicator").innerText = `PERTANYAAN ${currentIndex + 1} DARI ${totalQuestions}`;
            document.getElementById("question-text").innerText = `${currentIndex + 1}. ${currentQuestion.pertanyaan}`;
            document.getElementById("question-progress").innerText = `Soal ${currentIndex + 1} dari ${totalQuestions}`;
            
            // Progress Bar
            const progressPercent = ((currentIndex + 1) / totalQuestions) * 100;
            document.getElementById("progress-bar").style.width = progressPercent + '%';

            // Bersihkan Wadah Opsi
            const optionsContainer = document.getElementById("options-container");
            optionsContainer.innerHTML = '';

            // Opsi Jawaban A, B, C, D
            const opts = [
                { key: 'A', text: currentQuestion.opsi_a },
                { key: 'B', text: currentQuestion.opsi_b },
                { key: 'C', text: currentQuestion.opsi_c },
                { key: 'D', text: currentQuestion.opsi_d }
            ];

            const currentAnswer = answers[currentIndex];

            opts.forEach(function(opt) {
                const card = document.createElement("div");
                card.className = "opt-card";
                card.id = `opt-${opt.key}`;

                if (currentAnswer === opt.key) {
                    card.classList.add("selected");
                }

                card.innerHTML = `
                    <div class="d-flex align-items-center">
                        <span class="opt-badge">${opt.key}</span>
                        <span>${opt.text}</span>
                    </div>
                    <div class="opt-check">
                        ${currentAnswer === opt.key ? '<i class="bi bi-check-circle-fill text-primary fs-5"></i>' : '<i class="bi bi-circle text-muted opacity-50 fs-5"></i>'}
                    </div>
                `;

                // Event memilih jawaban (Dapat diubah-ubah secara fleksibel oleh siswa)
                card.onclick = function() {
                    selectAnswer(opt.key);
                };

                optionsContainer.appendChild(card);
            });

            // Atur Navigasi Tombol
            const prevBtn = document.getElementById("prev-btn");
            if (currentIndex === 0) {
                prevBtn.style.display = "none";
            } else {
                prevBtn.style.display = "block";
            }

            const nextBtn = document.getElementById("next-btn");
            if (currentIndex === totalQuestions - 1) {
                nextBtn.innerText = "Selesai & Kumpulkan Kuis";
                nextBtn.className = "btn btn-success btn-lg rounded-3 fw-bold px-4 py-2 shadow-sm";
            } else {
                nextBtn.innerText = "Berikutnya \u2192";
                nextBtn.className = "btn btn-primary btn-lg rounded-3 fw-bold px-4 py-2 shadow-sm";
            }

            updatePalette();
        }

        // 7. Pilih Jawaban Siswa (Netral, Tanpa Umpan Balik Benar/Salah)
        function selectAnswer(selectedKey) {
            answers[currentIndex] = selectedKey;
            localStorage.setItem(storageKeyAnswers, JSON.stringify(answers));

            // Perbarui gaya opsi terpilih
            const options = document.querySelectorAll(".opt-card");
            options.forEach(function(el) {
                el.classList.remove("selected");
                const checkIcon = el.querySelector(".opt-check");
                if (checkIcon) {
                    checkIcon.innerHTML = '<i class="bi bi-circle text-muted opacity-50 fs-5"></i>';
                }
            });

            const selectedCard = document.getElementById(`opt-${selectedKey}`);
            if (selectedCard) {
                selectedCard.classList.add("selected");
                const checkIcon = selectedCard.querySelector(".opt-check");
                if (checkIcon) {
                    checkIcon.innerHTML = '<i class="bi bi-check-circle-fill text-primary fs-5"></i>';
                }
            }

            updatePalette();
        }

        // 8. Event Listener Tombol Navigasi
        document.getElementById("prev-btn").addEventListener("click", function() {
            if (currentIndex > 0) {
                currentIndex--;
                displayQuestion();
            }
        });

        document.getElementById("next-btn").addEventListener("click", function() {
            if (currentIndex === totalQuestions - 1) {
                // Konfirmasi Sebelum Menyelesaikan
                const answeredCount = Object.keys(answers).length;
                if (answeredCount < totalQuestions) {
                    const unanswered = totalQuestions - answeredCount;
                    const confirmSubmit = confirm(`Perhatian: Masih ada ${unanswered} soal yang belum Anda jawab!\n\nApakah Anda yakin ingin mengumpulkan kuis sekarang?`);
                    if (!confirmSubmit) {
                        return;
                    }
                } else {
                    const confirmSubmit = confirm("Apakah Anda yakin ingin menyelesaikan dan mengumpulkan kuis ini?");
                    if (!confirmSubmit) {
                        return;
                    }
                }
                finishQuiz();
            } else {
                currentIndex++;
                displayQuestion();
            }
        });

        // 9. Menyelesaikan Sesi Kuis, Kirim ke kuis_proses.php
        function finishQuiz() {
            clearInterval(timerInterval);

            const elapsed = (waktuKuisMin * 60) - timeLeft;

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
            elapsedInput.value = elapsed > 0 ? elapsed : 0;
            form.appendChild(elapsedInput);

            const answersInput = document.createElement('input');
            answersInput.type = 'hidden';
            answersInput.name = 'answers';
            answersInput.value = JSON.stringify(answers);
            form.appendChild(answersInput);

            document.body.appendChild(form);

            // Bersihkan localStorage untuk kuis ini
            localStorage.removeItem(storageKeyAnswers);
            localStorage.removeItem(storageKeyTime);

            form.submit();
        }
    </script>

<?php
require_once '../includes/footer.php';
?>