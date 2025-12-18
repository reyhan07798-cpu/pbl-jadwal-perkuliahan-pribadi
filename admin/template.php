<?php
session_start();
require_once 'functions.php'; // Memuat fungsi bantu

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: ../login.php');
    exit;
}

// Jika ada notifikasi toast, tampilkan lalu hapus dari session
 $toast_script = '';
if (isset($_SESSION['toast'])) {
    $tipe = $_SESSION['toast']['tipe'];
    $pesan = $_SESSION['toast']['pesan'];
    $toast_script = "<script>document.addEventListener('DOMContentLoaded', function() { showToast('{$pesan}', '{$tipe}'); });</script>";
    unset($_SESSION['toast']);
}

// Jika judul halaman tidak diset, gunakan default
 $page_title = $page_title ?? 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Aplikasi Jadwal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <!-- Tambahkan Chart.js untuk visualisasi data di Dashboard -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="position-sticky pt-3">
                <h5 class="sidebar-heading">Admin Panel</h5>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <!-- PERUBAHAN DI SINI: href dan basename sekarang merujuk ke dashboard.php -->
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['manage_users.php', 'edit_user.php']) ? 'active' : ''; ?>" href="manage_users.php">
                            <i class="bi bi-people"></i> Kelola Pengguna
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['manage_courses.php', 'edit_course.php']) ? 'active' : ''; ?>" href="manage_courses.php">
                            <i class="bi bi-book"></i> Kelola Mata Kuliah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['manage_schedules.php', 'edit_schedule.php']) ? 'active' : ''; ?>" href="manage_schedules.php">
                            <i class="bi bi-calendar-week"></i> Kelola Jadwal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_notes.php' ? 'active' : ''; ?>" href="manage_notes.php">
                            <i class="bi bi-sticky"></i> Kelola Catatan
                        </a>
                    </li>
                </ul>
                <hr>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                        <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
            </div>

            <!-- --- ISI HALAMAN AKAN DITAMPILKAN DI SINI --- -->
            <?php echo $page_content ?? ''; ?>

        </main>
    </div>

    <!-- Container untuk Toast Notifikasi -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notifikasi</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body">
                <!-- Pesan akan dimasukkan di sini oleh JS -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk menampilkan Toast dari JavaScript
        function showToast(pesan, tipe = 'success') {
            const toastElement = document.getElementById('liveToast');
            const toastBody = document.getElementById('toast-body');
            toastBody.textContent = pesan;

            // Ganti warna header berdasarkan tipe
            const toastHeader = toastElement.querySelector('.toast-header');
            toastHeader.className = 'toast-header text-white bg-' + (tipe === 'error' ? 'danger' : tipe);

            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    </script>
    <?php echo $toast_script; // Cetak script untuk toast dari PHP jika ada ?>
</body>
</html>