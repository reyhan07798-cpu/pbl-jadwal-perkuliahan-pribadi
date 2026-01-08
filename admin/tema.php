<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';
require_once 'fungsi.php'; 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../Mahasiswa/login_mahasiswa.php');
    exit;
}

 $toast_script = '';
if (isset($_SESSION['toast'])) {
    $tipe  = $_SESSION['toast']['tipe'];
    $pesan = $_SESSION['toast']['pesan'];
    $safe_pesan = addslashes($pesan);
    $toast_script = "<script>document.addEventListener('DOMContentLoaded', function() { showToast('{$safe_pesan}', '{$tipe}'); });</script>";
    unset($_SESSION['toast']);
}

 $page_title = $page_title ?? 'Panel Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        
        body {
            background-color: #f4f6f9;
            overflow-x: hidden; 
        }
        .navbar-custom {
            height: 60px; 
            background: linear-gradient(135deg, #1a4d80, #2c7be0);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1030; 
            position: fixed; 
            top: 0; left: 0; right: 0;
        }
        .d-flex-wrapper {
            display: flex;
            width: 100%;
            margin-top: 60px; 
            min-height: calc(100vh - 60px);
        }
        .sidebar {
            background: white;
            width: 250px;
            min-width: 250px;
            transition: all 0.3s ease-in-out;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            overflow-y: auto; 
        }
        .nav-link {
            color: #333;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 5px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #e9ecef;
            color: #0d6efd;
            padding-left: 25px; 
        }
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 60px;
                left: 0;
                bottom: 0;
                z-index: 1020;
                transform: translateX(-100%); 
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }
            .sidebar.active {
                transform: translateX(0); 
            }
            #sidebarOverlay {
                display: none;
                position: fixed;
                top: 60px; left: 0; right: 0; bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1015;
                opacity: 0;
                transition: opacity 0.3s;
            }
            #sidebarOverlay.active {
                display: block;
                opacity: 1;
            }
            .sidebar { display: none; } 
            .sidebar.active { display: flex; }
        }
        @media (min-width: 768px) {
            .sidebar {
                display: block !important; 
                width: 250px;
            }
            .sidebar.collapsed {
                width: 0;
                min-width: 0;
                overflow: hidden;
                opacity: 0;
            }
            .main-content {
                width: 100%;
                transition: width 0.3s ease-in-out;
            }
            
            #sidebarOverlay { display: none !important; } 
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-3">
        <div class="container-fluid d-flex align-items-center">
            <button class="btn btn-outline-light btn-sm me-3 border-0" type="button" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand fw-bold fs-5" href="#">
                <i class="bi bi-grid-fill me-2"></i> Admin Panel
            </a>

            <!-- TOMBOL LOGOUT & USERNAME -->
            <div class="ms-auto d-flex align-items-center gap-2">
                <span class="text-white d-none d-md-block small">
                    <i class="bi bi-person-circle me-1"></i> 
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a class="btn btn-sm btn-danger rounded-pill px-3" href="keluar.php" role="button">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </a>
            </div>
        </div>
    </nav>

    <!-- WRAPPER UTAMA -->
    <div class="d-flex-wrapper">
        <div id="sidebarOverlay"></div>
        <nav class="sidebar" id="sidebar">
            <div class="p-3 d-flex flex-column h-100">
                <h5 class="text-primary fw-bold mb-4">Menu Utama</h5>
                
                <ul class="nav flex-column flex-grow-1">
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'beranda.php' ? 'active' : ''; ?>" href="beranda.php">
                            <i class="bi bi-speedometer2 me-2"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['kelola_pengguna.php', 'tambah_pengguna.php']) ? 'active' : ''; ?>" href="kelola_pengguna.php">
                            <i class="bi bi-people me-2"></i> Kelola Pengguna
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola_matakuliah.php' ? 'active' : ''; ?>" href="kelola_matakuliah.php">
                            <i class="bi bi-book me-2"></i> Daftar Mata Kuliah
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola_jadwal.php' ? 'active' : ''; ?>" href="kelola_jadwal.php">
                            <i class="bi bi-calendar-week me-2"></i> Lihat Jadwal
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'kelola_catatan.php' ? 'active' : ''; ?>" href="kelola_catatan.php">
                            <i class="bi bi-sticky me-2"></i> Kelola Catatan
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'pesan_masuk.php' ? 'active' : ''; ?>" href="pesan_masuk.php">
                            <i class="bi bi-envelope me-2"></i> Pesan Masuk
                        </a>
                    </li>
                </ul>
                
                <hr class="mt-auto mb-3">
                
            </div>
        </nav>

        <!-- MAIN CONTENT (KONTEN) -->
        <div class="main-content p-4">
            <main>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
                </div>
                <?php echo $page_content ?? ''; ?>

            </main>
        </div>
    </div>
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notifikasi</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            const content = document.querySelector('.main-content');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function () {
                    const isMobile = window.innerWidth < 768;

                    if (isMobile) {
                        sidebar.classList.toggle('active');
                        overlay.classList.toggle('active');
                    } else {
                        sidebar.classList.toggle('collapsed');
                    }
                });
            }
            if (overlay) {
                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }

            // --- FUNGSI RESET SAAT RESIZE WINDOW ---
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    sidebar.classList.remove('collapsed');
                } else {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    sidebar.classList.remove('collapsed');
                }
            });
        });

        // --- FUNGSI TOAST ---
        function showToast(pesan, tipe = 'success') {
            const toastElement = document.getElementById('liveToast');
            const toastBody = document.getElementById('toast-body');
            toastBody.innerHTML = pesan;

            const toastHeader = toastElement.querySelector('.toast-header');
            toastHeader.className = 'toast-header text-white bg-' + (tipe === 'error' ? 'danger' : tipe);

            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    </script>
    <?php echo $toast_script; ?>
</body>
</html>