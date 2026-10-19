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
    $stmt_soal = $pdo->prepare("SELECT id_soal, id_kuis, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, tipe_soal FROM tb_soal WHERE id_kuis = :id ORDER BY id_soal ASC");
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

/* Styling Variasi Soal (Susun Kata & Isian) */
.word-chip {
    display: inline-flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #0284c7;
    color: #0369a1;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 0.5rem 0.9rem;
    border-radius: 9999px;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.word-chip:hover {
    background: #e0f2fe;
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.word-chip.selected {
    background: #0284c7;
    color: #ffffff;
    border-color: #0369a1;
}

.word-chip-target {
    background: #2563eb;
    color: #ffffff;
    border: 1.5px solid #1d4ed8;
}

.word-chip-target:hover {
    background: #dc2626;
    border-color: #b91c1c;
    color: #ffffff;
}

.sentence-target-box {
    background-color: #f8fafc;
    border: 2px dashed #94a3b8;
    border-radius: 14px;
    min-height: 70px;
    padding: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    transition: border-color 0.2s ease;
}

.sentence-target-box.active {
    border-color: #2563eb;
    background-color: #eff6ff;
}

/* Styling Variasi Susun Huruf (Letter Scramble / Spelling) */
.letter-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 50px;
    background: #ffffff;
    border: 2px solid #7c3aed;
    color: #6d28d9;
    font-weight: 800;
    font-size: 1.3rem;
    border-radius: 12px;
    cursor: pointer;
    user-select: none;
    transition: all 0.15s ease;
    box-shadow: 0 2px 4px rgba(124, 58, 237, 0.12);
}

.letter-chip:hover {
    background: #ede9fe;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(124, 58, 237, 0.25);
}

.letter-chip-target {
    background: #7c3aed;
    color: #ffffff;
    border: 2px solid #6d28d9;
}

.letter-chip-target:hover {
    background: #ef4444;
    border-color: #dc2626;
    color: #ffffff;
}

.letter-target-box {
    background-color: #f8fafc;
    border: 2px dashed #a78bfa;
    border-radius: 14px;
    min-height: 75px;
    padding: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.letter-target-box.active {
    border-color: #7c3aed;
    background-color: #f5f3ff;
}

/* Styling Khusus Benar / Salah (True/False Cards) */
.tf-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tf-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.tf-card-true:hover {
    border-color: #10b981;
    background-color: #f0fdf4;
}

.tf-card-false:hover {
    border-color: #ef4444;
    background-color: #fef2f2;
}

.tf-card-true.selected {
    border-color: #10b981 !important;
    background-color: #ecfdf5 !important;
    color: #065f46 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25) !important;
}

.tf-card-false.selected {
    border-color: #ef4444 !important;
    background-color: #fff1f2 !important;
    color: #9f1239 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25) !important;
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

            <!-- Tanggal & Jam Pengerjaan Kuis Aktif -->
            <div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between py-2 px-3 rounded-4 mb-3 border-0 shadow-xs">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-check-fill fs-5 text-primary"></i>
                    <div>
                        <span class="fw-bold">Tanggal Pengerjaan Kuis:</span> Hari ini, <?= date('d M Y') ?>
                        <span class="text-secondary small ms-2">&bull; Mulai Pukul <?= date('H:i') ?> WIB</span>
                    </div>
                </div>
                <div class="small fw-semibold text-primary">
                    <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($_SESSION['siswa_nama'] ?? 'Siswa') ?> (<?= htmlspecialchars($_SESSION['siswa_kelas'] ?? 'VII') ?>)
                </div>
            </div>
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
                        <span id="type-badge-indicator" class="badge bg-primary-subtle text-primary fw-bold py-1 px-3 rounded-pill">
                            <i class="bi bi-ui-radios me-1"></i>PILIHAN GANDA
                        </span>
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

        // State untuk susun kata per soal
        window.susunTokensState = window.susunTokensState || {};

        // 6. Tampilkan Soal Aktif Sesuai Tipe
        function displayQuestion() {
            const currentQuestion = questions[currentIndex];
            const qType = currentQuestion.tipe_soal || 'pilihan_ganda';
            
            // Perbarui Teks & Label Progres
            document.getElementById("current-badge-indicator").innerText = `PERTANYAAN ${currentIndex + 1} DARI ${totalQuestions}`;
            document.getElementById("question-text").innerText = `${currentIndex + 1}. ${currentQuestion.pertanyaan}`;
            document.getElementById("question-progress").innerText = `Soal ${currentIndex + 1} dari ${totalQuestions}`;
            
            // Progress Bar
            const progressPercent = ((currentIndex + 1) / totalQuestions) * 100;
            document.getElementById("progress-bar").style.width = progressPercent + '%';

            // Badge Tipe Soal
            const typeBadge = document.getElementById("type-badge-indicator");
            typeBadge.removeAttribute("style");
            if (qType === 'isian') {
                typeBadge.className = "badge bg-warning-subtle text-warning-emphasis fw-bold py-1 px-3 rounded-pill";
                typeBadge.innerHTML = '<i class="bi bi-pencil-square me-1"></i>ISIAN SINGKAT / ESSAY';
            } else if (qType === 'susun_kata') {
                typeBadge.className = "badge bg-success-subtle text-success-emphasis fw-bold py-1 px-3 rounded-pill";
                typeBadge.innerHTML = '<i class="bi bi-puzzle me-1"></i>SUSUN KATA (WORD ARRANGE)';
            } else if (qType === 'susun_huruf') {
                typeBadge.className = "badge text-white fw-bold py-1 px-3 rounded-pill";
                typeBadge.style.backgroundColor = "#7c3aed";
                typeBadge.innerHTML = '<i class="bi bi-spellcheck me-1"></i>SUSUN HURUF (SPELLING PUZZLE)';
            } else if (qType === 'true_false') {
                typeBadge.className = "badge bg-info-subtle text-info-emphasis fw-bold py-1 px-3 rounded-pill";
                typeBadge.innerHTML = '<i class="bi bi-check2-circle me-1"></i>BENAR / SALAH (TRUE / FALSE)';
            } else {
                typeBadge.className = "badge bg-primary-subtle text-primary fw-bold py-1 px-3 rounded-pill";
                typeBadge.innerHTML = '<i class="bi bi-ui-radios me-1"></i>PILIHAN GANDA';
            }

            // Bersihkan Wadah Opsi
            const optionsContainer = document.getElementById("options-container");
            optionsContainer.innerHTML = '';

            const currentAnswer = answers[currentIndex];

            if (qType === 'true_false') {
                renderTrueFalse(currentQuestion, optionsContainer);
            } else if (qType === 'pilihan_ganda') {
                const opts = [
                    { key: 'A', text: currentQuestion.opsi_a },
                    { key: 'B', text: currentQuestion.opsi_b },
                    { key: 'C', text: currentQuestion.opsi_c },
                    { key: 'D', text: currentQuestion.opsi_d }
                ].filter(o => o.text !== null && o.text !== undefined && o.text !== '');

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

                    card.onclick = function() {
                        selectOption(opt.key);
                    };

                    optionsContainer.appendChild(card);
                });
            } else if (qType === 'susun_huruf') {
                renderSusunHuruf(currentQuestion, optionsContainer);
            } else if (qType === 'susun_kata') {
                renderSusunKata(currentQuestion, optionsContainer);
            } else if (qType === 'isian') {
                const isianWrapper = document.createElement("div");
                isianWrapper.className = "p-3 p-md-4 bg-light rounded-4 border";

                let hintHtml = '';
                if (currentQuestion.opsi_a && currentQuestion.opsi_a.trim() !== '') {
                    hintHtml = `
                        <div class="alert alert-primary-subtle border border-primary-subtle small text-primary-emphasis mb-3 rounded-3 d-flex align-items-center gap-2">
                            <i class="bi bi-lightbulb-fill text-primary fs-5"></i>
                            <div><strong>Petunjuk:</strong> ${currentQuestion.opsi_a}</div>
                        </div>
                    `;
                }

                isianWrapper.innerHTML = `
                    ${hintHtml}
                    <label class="form-label fw-bold text-dark mb-2">
                        <i class="bi bi-keyboard me-1 text-primary"></i>Ketik Jawaban Anda di Bawah Ini:
                    </label>
                    <input type="text" id="isian-input-${currentIndex}" class="form-control form-control-lg fw-semibold py-3 px-3 rounded-3 border-2 shadow-xs" placeholder="Ketik kata / frasa bahasa Inggris di sini..." value="${currentAnswer ? String(currentAnswer).replace(/"/g, '&quot;') : ''}" autocomplete="off">
                    <div class="d-flex justify-content-between align-items-center mt-2 small text-muted">
                        <span><i class="bi bi-info-circle me-1"></i>Jawaban tidak sensitif huruf besar/kecil (case-insensitive).</span>
                        <span id="isian-status" class="${currentAnswer ? 'text-success fw-bold' : 'text-secondary'}">
                            ${currentAnswer ? '<i class="bi bi-check2 me-1"></i>Tersimpan otomatis' : 'Belum diisi'}
                        </span>
                    </div>
                `;

                optionsContainer.appendChild(isianWrapper);

                const inputEl = document.getElementById(`isian-input-${currentIndex}`);
                inputEl.addEventListener('input', function() {
                    const val = this.value;
                    const statusEl = document.getElementById("isian-status");
                    if (val.trim() !== '') {
                        answers[currentIndex] = val.trim();
                        if (statusEl) statusEl.innerHTML = '<i class="bi bi-check2 me-1"></i>Tersimpan otomatis';
                        if (statusEl) statusEl.className = 'text-success fw-bold';
                    } else {
                        delete answers[currentIndex];
                        if (statusEl) statusEl.innerText = 'Belum diisi';
                        if (statusEl) statusEl.className = 'text-secondary';
                    }
                    localStorage.setItem(storageKeyAnswers, JSON.stringify(answers));
                    updatePalette();
                });
            }

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

        // 7. Render Interaktif Benar / Salah (True/False)
        function renderTrueFalse(currentQuestion, container) {
            const currentAnswer = answers[currentIndex];
            const optA = currentQuestion.opsi_a || 'Pernyataan ini Benar';
            const optB = currentQuestion.opsi_b || 'Pernyataan ini Salah';

            const tfWrapper = document.createElement("div");
            tfWrapper.className = "row g-3 py-2";

            // True Card (Hijau)
            const colTrue = document.createElement("div");
            colTrue.className = "col-12 col-md-6";
            colTrue.innerHTML = `
                <div class="tf-card tf-card-true ${currentAnswer === 'A' ? 'selected' : ''}" id="opt-A">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 48px; height: 48px; flex-shrink: 0;">
                            <i class="bi bi-check-lg fs-3"></i>
                        </div>
                        <div>
                            <div class="h5 fw-bold mb-0 text-success">TRUE (Benar)</div>
                            <small class="text-secondary">${optA}</small>
                        </div>
                    </div>
                    <div class="opt-check">
                        ${currentAnswer === 'A' ? '<i class="bi bi-check-circle-fill text-success fs-3"></i>' : '<i class="bi bi-circle text-muted fs-3 opacity-50"></i>'}
                    </div>
                </div>
            `;
            colTrue.querySelector(".tf-card").onclick = function() {
                selectOption('A');
            };

            // False Card (Merah)
            const colFalse = document.createElement("div");
            colFalse.className = "col-12 col-md-6";
            colFalse.innerHTML = `
                <div class="tf-card tf-card-false ${currentAnswer === 'B' ? 'selected' : ''}" id="opt-B">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center shadow-xs" style="width: 48px; height: 48px; flex-shrink: 0;">
                            <i class="bi bi-x-lg fs-3"></i>
                        </div>
                        <div>
                            <div class="h5 fw-bold mb-0 text-danger">FALSE (Salah)</div>
                            <small class="text-secondary">${optB}</small>
                        </div>
                    </div>
                    <div class="opt-check">
                        ${currentAnswer === 'B' ? '<i class="bi bi-x-circle-fill text-danger fs-3"></i>' : '<i class="bi bi-circle text-muted fs-3 opacity-50"></i>'}
                    </div>
                </div>
            `;
            colFalse.querySelector(".tf-card").onclick = function() {
                selectOption('B');
            };

            tfWrapper.appendChild(colTrue);
            tfWrapper.appendChild(colFalse);
            container.appendChild(tfWrapper);
        }

        // State untuk susun huruf per soal
        window.susunHurufState = window.susunHurufState || {};

        // 8. Render Interaktif Susun Huruf (Letter Scramble / Spelling)
        function renderSusunHuruf(currentQuestion, container) {
            // Ambil huruf dari opsi_a
            let rawLetters = [];
            if (currentQuestion.opsi_a) {
                if (currentQuestion.opsi_a.includes('/')) {
                    rawLetters = currentQuestion.opsi_a.split('/').map(s => s.trim().toUpperCase()).filter(s => s.length > 0);
                } else if (currentQuestion.opsi_a.includes('-')) {
                    rawLetters = currentQuestion.opsi_a.split('-').map(s => s.trim().toUpperCase()).filter(s => s.length > 0);
                } else if (currentQuestion.opsi_a.includes(' ')) {
                    rawLetters = currentQuestion.opsi_a.split(/\s+/).map(s => s.trim().toUpperCase()).filter(s => s.length > 0);
                } else {
                    rawLetters = currentQuestion.opsi_a.trim().toUpperCase().split('');
                }
            }

            const allTokens = rawLetters.map((char, idx) => ({ id: idx, text: char }));

            // Tentukan huruf yang sudah terpilih
            if (!window.susunHurufState[currentIndex]) {
                const savedWord = answers[currentIndex];
                if (savedWord) {
                    const savedChars = savedWord.split('');
                    const chosen = [];
                    const remaining = [...allTokens];
                    savedChars.forEach(c => {
                        const matchIdx = remaining.findIndex(t => t.text.toLowerCase() === c.toLowerCase());
                        if (matchIdx !== -1) {
                            chosen.push(remaining[matchIdx]);
                            remaining.splice(matchIdx, 1);
                        }
                    });
                    window.susunHurufState[currentIndex] = chosen;
                } else {
                    window.susunHurufState[currentIndex] = [];
                }
            }

            const chosenTokens = window.susunHurufState[currentIndex];
            const chosenIds = chosenTokens.map(t => t.id);
            const availableTokens = allTokens.filter(t => !chosenIds.includes(t.id));

            const wrapper = document.createElement("div");
            wrapper.className = "p-3 p-md-4 rounded-4 border";
            wrapper.style.backgroundColor = "#faf5ff";

            wrapper.innerHTML = `
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-dark mb-0">
                            <i class="bi bi-spellcheck me-1" style="color: #7c3aed;"></i>Kotak Ejaan Kata Anda:
                        </label>
                        <button type="button" id="btn-reset-huruf" class="btn btn-sm btn-outline-danger py-1 px-3 rounded-pill fw-semibold">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Huruf
                        </button>
                    </div>
                    <div id="letter-target-box" class="letter-target-box ${chosenTokens.length > 0 ? 'active' : ''}">
                    </div>
                </div>

                <div>
                    <label class="form-label fw-bold text-secondary small mb-2">
                        <i class="bi bi-hand-index-thumb me-1" style="color: #7c3aed;"></i>Ubin Huruf Tersedia (Klik untuk menyusun kata):
                    </label>
                    <div id="letters-pool-box" class="d-flex flex-wrap gap-2 p-3 bg-white rounded-3 border justify-content-center">
                    </div>
                </div>
            `;

            container.appendChild(wrapper);

            const targetBox = document.getElementById("letter-target-box");
            const poolBox = document.getElementById("letters-pool-box");

            if (chosenTokens.length === 0) {
                targetBox.innerHTML = '<span class="text-muted small fst-italic w-100 text-center py-2"><i class="bi bi-arrow-down-circle me-1"></i>Klik ubin huruf di bawah ini untuk mengeja kata bahasa Inggris...</span>';
            } else {
                chosenTokens.forEach((token, index) => {
                    const chip = document.createElement("span");
                    chip.className = "letter-chip letter-chip-target";
                    chip.title = "Klik untuk membatalkan huruf ini";
                    chip.innerText = token.text;
                    chip.onclick = function() {
                        chosenTokens.splice(index, 1);
                        syncSusunHurufAnswer(chosenTokens);
                        displayQuestion();
                    };
                    targetBox.appendChild(chip);
                });
            }

            if (availableTokens.length === 0) {
                poolBox.innerHTML = '<span class="text-success small fw-semibold w-100 text-center py-2"><i class="bi bi-check2-all me-1"></i>Semua huruf telah tersusun ke dalam kotak ejaan!</span>';
            } else {
                availableTokens.forEach(token => {
                    const chip = document.createElement("span");
                    chip.className = "letter-chip";
                    chip.title = "Klik untuk memasukkan huruf ini";
                    chip.innerText = token.text;
                    chip.onclick = function() {
                        chosenTokens.push(token);
                        syncSusunHurufAnswer(chosenTokens);
                        displayQuestion();
                    };
                    poolBox.appendChild(chip);
                });
            }

            document.getElementById("btn-reset-huruf").onclick = function() {
                window.susunHurufState[currentIndex] = [];
                syncSusunHurufAnswer([]);
                displayQuestion();
            };
        }

        function syncSusunHurufAnswer(chosenTokens) {
            if (chosenTokens.length > 0) {
                answers[currentIndex] = chosenTokens.map(t => t.text).join('');
            } else {
                delete answers[currentIndex];
            }
            localStorage.setItem(storageKeyAnswers, JSON.stringify(answers));
            updatePalette();
        }

        // 9. Render Interaktif Susun Kata
        function renderSusunKata(currentQuestion, container) {
            // Ambil token dari opsi_a
            let rawTokens = [];
            if (currentQuestion.opsi_a) {
                if (currentQuestion.opsi_a.includes('/')) {
                    rawTokens = currentQuestion.opsi_a.split('/').map(s => s.trim()).filter(s => s.length > 0);
                } else {
                    rawTokens = currentQuestion.opsi_a.trim().split(/\s+/).filter(s => s.length > 0);
                }
            }

            // Inisialisasi token objects [{id: 0, text: '...'}]
            const allTokens = rawTokens.map((text, idx) => ({ id: idx, text: text }));

            // Tentukan token yang sudah terpilih
            if (!window.susunTokensState[currentIndex]) {
                const savedSentence = answers[currentIndex];
                if (savedSentence) {
                    const savedWords = savedSentence.split(/\s+/);
                    const chosen = [];
                    const remaining = [...allTokens];
                    savedWords.forEach(w => {
                        const matchIdx = remaining.findIndex(t => t.text.toLowerCase() === w.toLowerCase());
                        if (matchIdx !== -1) {
                            chosen.push(remaining[matchIdx]);
                            remaining.splice(matchIdx, 1);
                        }
                    });
                    window.susunTokensState[currentIndex] = chosen;
                } else {
                    window.susunTokensState[currentIndex] = [];
                }
            }

            const chosenTokens = window.susunTokensState[currentIndex];
            const chosenIds = chosenTokens.map(t => t.id);
            const availableTokens = allTokens.filter(t => !chosenIds.includes(t.id));

            const wrapper = document.createElement("div");
            wrapper.className = "p-3 p-md-4 bg-light rounded-4 border";

            wrapper.innerHTML = `
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label fw-bold text-dark mb-0">
                            <i class="bi bi-chat-quote-fill me-1 text-primary"></i>Susunan Kalimat Anda:
                        </label>
                        <button type="button" id="btn-reset-susun" class="btn btn-sm btn-outline-danger py-1 px-3 rounded-pill fw-semibold">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Susunan
                        </button>
                    </div>
                    <div id="sentence-target-box" class="sentence-target-box ${chosenTokens.length > 0 ? 'active' : ''}">
                    </div>
                </div>

                <div>
                    <label class="form-label fw-bold text-secondary small mb-2">
                        <i class="bi bi-hand-index-thumb me-1 text-primary"></i>Pilihan Kata (Klik untuk memasukkan ke kalimat):
                    </label>
                    <div id="tokens-pool-box" class="d-flex flex-wrap gap-2 p-3 bg-white rounded-3 border">
                    </div>
                </div>
            `;

            container.appendChild(wrapper);

            const sentenceBox = document.getElementById("sentence-target-box");
            const poolBox = document.getElementById("tokens-pool-box");

            // Render token dalam kotak kalimat
            if (chosenTokens.length === 0) {
                sentenceBox.innerHTML = '<span class="text-muted small fst-italic w-100 text-center py-2"><i class="bi bi-arrow-down-circle me-1"></i>Klik kata-kata di bawah ini untuk menyusun kalimat...</span>';
            } else {
                chosenTokens.forEach((token, index) => {
                    const chip = document.createElement("span");
                    chip.className = "word-chip word-chip-target";
                    chip.title = "Klik untuk membatalkan/mengembalikan kata ini";
                    chip.innerHTML = `${token.text} <i class="bi bi-x-circle ms-1 opacity-75"></i>`;
                    chip.onclick = function() {
                        chosenTokens.splice(index, 1);
                        syncSusunKataAnswer(chosenTokens);
                        displayQuestion();
                    };
                    sentenceBox.appendChild(chip);
                });
            }

            // Render token dalam pool yang belum dipilih
            if (availableTokens.length === 0) {
                poolBox.innerHTML = '<span class="text-success small fw-semibold w-100 text-center py-1"><i class="bi bi-check2-all me-1"></i>Semua kata telah dimasukkan ke dalam kalimat!</span>';
            } else {
                availableTokens.forEach(token => {
                    const chip = document.createElement("span");
                    chip.className = "word-chip";
                    chip.title = "Klik untuk menambahkan ke susunan kalimat";
                    chip.innerHTML = `<i class="bi bi-plus me-1 text-primary"></i>${token.text}`;
                    chip.onclick = function() {
                        chosenTokens.push(token);
                        syncSusunKataAnswer(chosenTokens);
                        displayQuestion();
                    };
                    poolBox.appendChild(chip);
                });
            }

            // Tombol Reset
            document.getElementById("btn-reset-susun").onclick = function() {
                window.susunTokensState[currentIndex] = [];
                syncSusunKataAnswer([]);
                displayQuestion();
            };
        }

        function syncSusunKataAnswer(chosenTokens) {
            if (chosenTokens.length > 0) {
                answers[currentIndex] = chosenTokens.map(t => t.text).join(' ');
            } else {
                delete answers[currentIndex];
            }
            localStorage.setItem(storageKeyAnswers, JSON.stringify(answers));
            updatePalette();
        }

        // 10. Pilih Jawaban Pilihan Ganda / True-False
        function selectOption(selectedKey) {
            answers[currentIndex] = selectedKey;
            localStorage.setItem(storageKeyAnswers, JSON.stringify(answers));

            // Perbarui gaya opsi terpilih (Pilihan Ganda)
            const options = document.querySelectorAll(".opt-card");
            options.forEach(function(el) {
                el.classList.remove("selected");
                const checkIcon = el.querySelector(".opt-check");
                if (checkIcon) {
                    checkIcon.innerHTML = '<i class="bi bi-circle text-muted opacity-50 fs-5"></i>';
                }
            });

            // Perbarui gaya kartu True/False
            const tfCards = document.querySelectorAll(".tf-card");
            tfCards.forEach(function(el) {
                el.classList.remove("selected");
                const checkIcon = el.querySelector(".opt-check");
                if (checkIcon) {
                    checkIcon.innerHTML = '<i class="bi bi-circle text-muted fs-3 opacity-50"></i>';
                }
            });

            const selectedCard = document.getElementById(`opt-${selectedKey}`);
            if (selectedCard) {
                selectedCard.classList.add("selected");
                const checkIcon = selectedCard.querySelector(".opt-check");
                if (checkIcon) {
                    if (selectedCard.classList.contains("tf-card-true")) {
                        checkIcon.innerHTML = '<i class="bi bi-check-circle-fill text-success fs-3"></i>';
                    } else if (selectedCard.classList.contains("tf-card-false")) {
                        checkIcon.innerHTML = '<i class="bi bi-x-circle-fill text-danger fs-3"></i>';
                    } else {
                        checkIcon.innerHTML = '<i class="bi bi-check-circle-fill text-primary fs-5"></i>';
                    }
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