<?php
// 1. START SESSION (AMAN)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. KONEKSI DATABASE
require_once '../koneksi.php';
require_once 'fungsi.php'; 

// 3. CEK LOGIN ADMIN
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../Mahasiswa/login_mahasiswa.php');
    exit;
}

// 4. NOTIFIKASI TOAST
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
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Chart.js (Untuk Dashboard) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* --- CSS KHUSUS AGAR TIDAK BUG & RAPI --- */
        
        body {
            background-color: #f4f6f9;
            overflow-x: hidden; /* Mencegah scroll horizontal aneh */
        }

        /* 1. NAVBAR ATAS (KOTAK BIRU) */
        .navbar-custom {
            height: 60px; /* Paksa tinggi tetap agar tidak gede/berubah-ubah */
            background: linear-gradient(135deg, #1a4d80, #2c7be0);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1030; /* Di atas segalanya */
            position: fixed; /* Fixed agar tidak ikut scroll */
            top: 0; left: 0; right: 0;
        }

        /* 2. WRAPPER UTAMA (AGAR SIDEBAR & KONTEN RAPI) */
        .d-flex-wrapper {
            display: flex;
            width: 100%;
            margin-top: 60px; /* Jarak ke bawah navbar fixed */
            min-height: calc(100vh - 60px);
        }

        /* 3. SIDEBAR */
        .sidebar {
            background: white;
            width: 250px;
            min-width: 250px;
            transition: all 0.3s ease-in-out;
            border-right: 1px solid #dee2e6;
            display: flex;
            flex-direction: column;
            overflow-y: auto; /* Scroll jika menu terlalu panjang */
        }

        /* Styling Menu Sidebar */
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
            padding-left: 25px; /* Efek geser sedikit saat hover */
        }

        /* 4. LOGIKA RESPONSIVE (HP vs LAPTOP) */

        /* --- MOBILE (HP) --- */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 60px;
                left: 0;
                bottom: 0;
                z-index: 1020;
                transform: translateX(-100%); /* Default: Tersembunyi di kiri */
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }

            /* Ketika Sidebar Aktif (3 garis diklik di HP) */
            .sidebar.active {
                transform: translateX(0); /* Meluncur keluar */
            }

            /* Overlay Gelap saat menu buka di HP */
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
            
            /* Di HP, Sidebar sembunyi default */
            .sidebar { display: none; } 
            .sidebar.active { display: flex; }
        }

        /* --- DESKTOP (LAPTOP) --- */
        @media (min-width: 768px) {
            .sidebar {
                display: block !important; /* Paksa muncul di laptop */
                width: 250px;
            }

            /* Logika Toggle di Desktop: Menyembunyikan (width 0) */
            .sidebar.collapsed {
                width: 0;
                min-width: 0;
                overflow: hidden;
                opacity: 0;
            }

            /* Content melebar saat sidebar tutup */
            .main-content {
                width: 100%;
                transition: width 0.3s ease-in-out;
            }
            
            #sidebarOverlay { display: none !important; } /* Overlay tidak dipakai di desktop */
        }
    </style>
</head>
<body>

    <!-- NAVBAR ATAS (Fixed) -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom px-3">
        <div class="container-fluid d-flex align-items-center">
            
            <!-- TOMBOL 3 GARIS (HAMBURGER) -->
            <!-- Selalu muncul (di HP & Laptop) -->
            <button class="btn btn-outline-light btn-sm me-3 border-0" type="button" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>

            <!-- BRAND / JUDUL -->
            <a class="navbar-brand fw-bold fs-5" href="#">
                <i class="bi bi-grid-fill me-2"></i> Admin Panel
            </a>

            <!-- TOMBOL LOGOUT & USERNAME (Kanan) -->
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
        
        <!-- OVERLAY (Khusus HP) -->
        <div id="sidebarOverlay"></div>

        <!-- SIDEBAR -->
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

                <!-- --- ISI HALAMAN DARI FILE LAIN (contoh: beranda.php) --- -->
                <?php echo $page_content ?? ''; ?>

            </main>
        </div>
    </div>

    <!-- CONTAINER TOAST NOTIFIKASI -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 2000">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notifikasi</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body"></div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            const content = document.querySelector('.main-content');

            // --- FUNGSI TOGGLE SIDEBAR (KLIK TOMBOL 3 TITIK) ---
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function () {
                    const isMobile = window.innerWidth < 768;

                    if (isMobile) {
                        // LOGIKA MOBILE: Tampilkan/Sembunyikan + Overlay
                        sidebar.classList.toggle('active');
                        overlay.classList.toggle('active');
                    } else {
                        // LOGIKA LAPTOP: Tampilkan/Sembunyikan (Collapse)
                        sidebar.classList.toggle('collapsed');
                    }
                });
            }

            // --- FUNGSI TUTUP SAAT OVERLAY DIKLIK (KLIK LUAR SIDEBAR) ---
            if (overlay) {
                overlay.addEventListener('click', function () {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            }

            // --- FUNGSI RESET SAAT RESIZE WINDOW ---
            // Agar layout tidak rusak saat HP di-rotate atau window di-resize
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 768) {
                    // Jika pindah ke Desktop, buka sidebar default, tutup overlay
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    sidebar.classList.remove('collapsed'); // Pastikan terbuka
                } else {
                    // Jika pindah ke Mobile, tutup sidebar & overlay
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