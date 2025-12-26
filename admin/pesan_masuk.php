<?php
// File: admin/pesan_masuk.php

// 1. Start Session (Aman untuk dipanggil berkali-kali)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Koneksi Database
require_once '../koneksi.php';

// 3. Cek Keamanan (Harus Admin)
if (
    !isset($_SESSION['loggedin']) ||
    $_SESSION['loggedin'] !== true ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: ../Mahasiswa/login_mahasiswa.php');
    exit;
}

 $page_title = "Pesan Masuk";

// --- LOGIKA AKSI (Tandai Baca, Hapus, Tandai Semua) ---

// Tandai 1 pesan sudah dibaca
if (isset($_GET['action']) && $_GET['action'] === 'mark_read' && isset($_GET['id'])) {
    $stmt = $conn->prepare("UPDATE contact_messages SET status='read' WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    header("Location: pesan_masuk.php"); // Refresh halaman
    exit;
}

// Hapus 1 pesan
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    header("Location: pesan_masuk.php"); // Refresh halaman
    exit;
}

// Tandai semua sudah dibaca (Dijalankan jika URL ada ?action=mark_all_read)
if (isset($_GET['action']) && $_GET['action'] === 'mark_all_read') {
    $conn->query("UPDATE contact_messages SET status='read'");
    header("Location: pesan_masuk.php");
    exit;
}

// --- AMBIL DATA ---

// Ambil semua pesan
 $messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");

// Ambil Statistik
 $total_msg = $messages->num_rows; // Total baris saat ini
// Hitung unread
 $stmt_unread = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE status='unread'");
 $stmt_unread->execute();
 $unread_count = $stmt_unread->get_result()->fetch_row()[0];
// Hitung read
 $read_count = $total_msg - $unread_count;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); }
        .message-card { transition: all 0.2s; cursor: pointer; }
        .message-card.unread { background-color: #f0f7ff; border-left: 4px solid #0d6efd; font-weight: 500; }
        .message-card.read { background-color: #fff; border-left: 4px solid transparent; }
        .message-card:hover { background-color: #e9ecef; }
        .avatar { width: 40px; height: 40px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse p-3">
                <h5 class="text-primary mb-4">Admin Panel</h5>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2"><a class="nav-link" href="beranda.php"><i class="bi bi-speedometer2"></i> Beranda</a></li>
                    <li class="nav-item mb-2"><a class="nav-link active bg-light text-primary rounded" href="pesan_masuk.php">
                        <i class="bi bi-envelope"></i> Pesan Masuk 
                        <?php if($unread_count > 0) echo '<span class="badge bg-danger ms-1">'.$unread_count.'</span>'; ?>
                    </a></li>
                    <li class="nav-item mb-2"><a class="nav-link" href="kelola_pengguna.php"><i class="bi bi-people"></i> Pengguna</a></li>
                    <li class="nav-item mb-2"><a class="nav-link text-danger" href="keluar.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2"><?php echo $page_title; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="?action=mark_all_read" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Tandai semua pesan sudah dibaca?')">
                                <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Total Pesan</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $total_msg; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-danger mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Belum Dibaca</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $unread_count; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Sudah Dibaca</h5>
                                <p class="card-text fs-2 fw-bold"><?php echo $read_count; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List Pesan -->
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <?php if ($messages->num_rows > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php while($msg = $messages->fetch_assoc()): ?>
                                    <div class="list-group-item message-card <?php echo $msg['status'] == 'unread' ? 'unread' : 'read'; ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">
                                                <?php echo htmlspecialchars($msg['name']); ?>
                                                <small class="text-muted fw-normal ms-2"><?php echo htmlspecialchars($msg['email']); ?></small>
                                            </h5>
                                            <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($msg['created_at'])); ?></small>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>