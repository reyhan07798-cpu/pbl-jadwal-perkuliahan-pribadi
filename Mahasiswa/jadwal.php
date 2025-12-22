<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login_mahasiswa.php");
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Perkuliahan Mahasiswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../Css/jadwal.css">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-content">
                <div class="header-left">
                    <h1><i class="bi bi-journal-bookmark-fill"></i> Jadwal Perkuliahan</h1>
                    <p>Selamat Datang Di Website Kami! <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b></p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span><i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                        <a href="logout.php" class="btn btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
                    </div>
                    <div class="datetime-widget">
                        <div class="datetime-day" id="datetime-day">Senin</div>
                        <div class="datetime-date" id="datetime-date">24 November 2025</div>
                        <div class="datetime-time" id="datetime-time">00:00:00</div>
                    </div>
                </div>
            </div>
        </header>

        <nav class="app-nav">
            <button class="nav-btn active" data-tab="schedule"><i class="bi bi-calendar-week"></i> Jadwal Mingguan</button>
            <button class="nav-btn" data-tab="calendar"><i class="bi bi-calendar3"></i> Kalender</button>
            <button class="nav-btn" data-tab="notes"><i class="bi bi-journal-text"></i> Catatan</button>
        </nav>

        <main class="app-main">
            <!-- Tab Jadwal Mingguan -->
            <section class="tab-content active" id="schedule-tab">
                <div class="schedule-layout">
                    <aside class="schedule-sidebar">
                        <div class="card">
                            <button id="add-course-btn" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah Mata Kuliah</button>
                        </div>
                        <div class="card">
                            <h5><i class="bi bi-list-ul"></i> Daftar Mata Kuliah</h5>
                            <input type="text" class="form-control" id="search-course" placeholder="Cari mata kuliah atau dosen...">
                            <div id="course-list"></div>
                        </div>
                    </aside>
                    <div class="schedule-content">
                      <div class="card">
                        <!-- PERUBAHAN: Header diubah untuk menampung tombol export -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="schedule-title">Jadwal Mingguan</h4>
                                <p id="current-week-date" class="schedule-date"></p>
                            </div>
                            <!-- PERUBAHAN: Tombol Export PDF ditambahkan di sini -->
                            <button id="export-pdf-btn" class="btn btn-success">
                                <i class="bi bi-file-earmark-pdf"></i> Export PDF
                            </button>
                        </div>
                        <div class="table-container">
                            <table class="schedule-table" id="schedule-table"></table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tab Kalender -->
            <section class="tab-content" id="calendar-tab">
                <div class="card">
                    <div class="calendar-header">
                        <button id="prev-month-btn" class="btn btn-secondary"><i class="bi bi-chevron-left"></i></button>
                        <h3 id="calendar-month-year">November 2025</h3>
                        <button id="next-month-btn" class="btn btn-secondary"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="calendar-grid" id="calendar-grid">
                        <!-- Kalender akan di-generate oleh JavaScript -->
                    </div>
                </div>
            </section>

            <!-- Tab Catatan -->
            <section class="tab-content" id="notes-tab">
                <div class="notes-layout">
                    <div class="notes-content">
                        <div class="card mb-3">
                            <button id="add-note-btn" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah Catatan Baru</button>
                        </div>
                        
                        <h4><i class="bi bi-journal-text"></i> Daftar Catatan</h4>
                        <div class="notes-grid" id="notes-grid">
                            <!-- Daftar catatan akan di-generate oleh JavaScript -->
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal untuk form tambah/edit -->
    <div id="modal-container" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h3 class="modal-title" id="modal-title">Modal Title</h3>
            <div class="modal-body" id="modal-body">
                Modal content goes here.
            </div>
        </div>
    </div>

    <div id="toast-container" class="toast-container"></div>

    <script src="../Js/jadwal.js"></script>
</body>
</html>