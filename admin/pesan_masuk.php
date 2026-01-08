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

 $page_title = "Pesan Masuk";

// --- LOGIKA AKSI (Tandai Baca, Hapus, Tandai Semua) ---

if (isset($_GET['action']) && $_GET['action'] === 'mark_read' && isset($_GET['id'])) {
    $stmt = $conn->prepare("UPDATE contact_messages SET status='read' WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $_SESSION['toast'] = ['tipe' => 'success', 'pesan' => 'Pesan ditandai sudah dibaca.'];
    header("Location: pesan_masuk.php"); 
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $_SESSION['toast'] = ['tipe' => 'success', 'pesan' => 'Pesan berhasil dihapus.'];
    header("Location: pesan_masuk.php"); 
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    $conn->query("UPDATE contact_messages SET status='read'");
    $_SESSION['toast'] = ['tipe' => 'success', 'pesan' => 'Semua pesan ditandai sudah dibaca.'];
    header("Location: pesan_masuk.php");
    exit;
}

 $messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
 $total_msg = $messages->num_rows; 
 $stmt_unread = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE status='unread'");
 $stmt_unread->execute();
 $unread_count = $stmt_unread->get_result()->fetch_row()[0];
 $read_count = $total_msg - $unread_count;
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
        /* --- MOBILE (HP) --- */
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

        /* --- DESKTOP (LAPTOP) --- */
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
    <div class="d-flex-wrapper">
        <div id="sidebarOverlay"></div>
        <nav class="sidebar" id="sidebar">
            <div class="p-3 d-flex flex-column h-100">
                <h5 class="text-primary fw-bold mb-4">Menu Utama</h5>
                
                <ul class="nav flex-column flex-grow-1">
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="beranda.php">
                            <i class="bi bi-speedometer2 me-2"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="kelola_pengguna.php">
                            <i class="bi bi-people me-2"></i> Kelola Pengguna
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="kelola_matakuliah.php">
                            <i class="bi bi-book me-2"></i> Daftar Mata Kuliah
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="kelola_jadwal.php">
                            <i class="bi bi-calendar-week me-2"></i> Lihat Jadwal
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link" href="kelola_catatan.php">
                            <i class="bi bi-sticky me-2"></i> Kelola Catatan
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link active" href="pesan_masuk.php">
                            <i class="bi bi-envelope me-2"></i> Pesan Masuk
                        </a>
                    </li>
                </ul>
                
                <hr class="mt-auto mb-3">
                
            </div>
        </nav>

        <div class="main-content p-4">
            <main>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?php echo htmlspecialchars($page_title); ?></h1>
                    <a href="?action=mark_all_read" class="btn btn-sm btn-outline-primary" onclick="return confirm('Tandai semua pesan sudah dibaca?')">
                        <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                    </a>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Total Pesan</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $total_msg; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-danger mb-3 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Belum Dibaca</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $unread_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Sudah Dibaca</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $read_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <?php if ($messages->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while($msg = $messages->fetch_assoc()): ?>
                                    <div class="list-group-item message-card <?php echo $msg['status'] == 'unread' ? 'unread' : 'read'; ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <div class="me-3">
                                                <div class="fw-bold">
                                                    <?php echo htmlspecialchars($msg['name']); ?>
                                                    <small class="text-muted fw-normal ms-2"><?php echo htmlspecialchars($msg['email']); ?></small>
                                                </div>
                                                <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($msg['created_at'])); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php echo $msg['status'] == 'unread' ? 'danger' : 'secondary'; ?> mb-2 d-inline-block">
                                                    <?php echo $msg['status'] == 'unread' ? 'Baru' : 'Dibaca'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <p class="mb-2 mt-2 text-break"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                                        
                                        <div class="d-flex gap-2 mt-2">
                                            <?php if ($msg['status'] == 'unread'): ?>
                                                <a href="?action=mark_read&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="bi bi-envelope-open"></i> Tandai Dibaca
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary mt-1">Sudah Dibaca</span>
                                            <?php endif; ?>
                                            
                                            <a href="?action=delete&id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus pesan ini?')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </a>
                                        </div>
                                    </div>
                                    <style>
                                        .message-card.unread { background-color: #f0f7ff; border-left: 4px solid #0d6efd; }
                                        .message-card.read { background-color: #fff; border-left: 4px solid transparent; }
                                        .message-card:hover { background-color: #f8f9fa; }
                                    </style>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center p-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="mt-3 text-muted">Belum ada pesan masuk.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

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