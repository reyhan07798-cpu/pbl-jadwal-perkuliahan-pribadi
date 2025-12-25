<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

// Cek login admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: ../Mahasiswa/login_mahasiswa.php');
    exit;
}

 $page_title = 'Pesan Masuk';

// Proses tandai dibaca
if (isset($_GET['action']) && $_GET['action'] == 'mark_read' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: pesan_masuk.php");
    exit;
}

// Proses hapus pesan
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: pesan_masuk.php");
    exit;
}

// Ambil semua pesan
 $stmt = $conn->prepare("
    SELECT id, name, email, message, status, created_at 
    FROM contact_messages 
    ORDER BY created_at DESC
");
 $stmt->execute();
 $messages = $stmt->get_result();

 $conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .message-card {
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
        }
        
        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .message-card.unread {
            border-left-color: #0d6efd;
            background-color: #f8f9fa;
        }
        
        .message-content {
            max-height: 100px;
            overflow-y: auto;
        }
        
        .btn-action {
            transition: all 0.2s ease;
        }
        
        .btn-action:hover {
            transform: scale(1.05);
        }
        
        .status-badge {
            font-size: 0.75rem;
        }
        
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
        }
        
        .stats-card {
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h5 class="sidebar-heading">Admin Panel</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="pesan_masuk.php">
                                <i class="bi bi-envelope"></i> Pesan Masuk
                                <?php
                                // Hitung pesan belum dibaca
                                $unread_count = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
                                $unread_count->execute();
                                $unread = $unread_count->get_result()->fetch_row()[0];
                                if ($unread > 0) {
                                    echo '<span class="badge bg-danger ms-2">' . $unread . '</span>';
                                }
                                ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kelola_pengguna.php">
                                <i class="bi bi-people"></i> Kelola Pengguna
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kelola_matakuliah.php">
                                <i class="bi bi-book"></i> Kelola Mata Kuliah
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kelola_jadwal.php">
                                <i class="bi bi-calendar-week"></i> Kelola Jadwal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kelola_catatan.php">
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
                    <h1 class="h2"><?php echo $page_title; ?></h1>
                    <div class="btn-group mb-2 mb-md-0">
                        <a href="?action=mark_all_read" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-check-all"></i> Tandai Semua Dibaca
                        </a>
                        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pesan</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $messages->num_rows; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-envelope fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sudah Dibaca</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $read_count = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE status = 'read'");
                                            $read_count->execute();
                                            echo $read_count->get_result()->fetch_row()[0];
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-envelope-open fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card stats-card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Belum Dibaca</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $unread_count = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
                                            $unread_count->execute();
                                            echo $unread_count->get_result()->fetch_row()[0];
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-envelope fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages List -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Daftar Pesan Masuk</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($messages->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Pesan</th>
                                            <th>Waktu</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($message = $messages->fetch_assoc()): ?>
                                            <tr class="message-card <?php echo $message['status'] == 'unread' ? 'unread' : ''; ?>">
                                                <td>
                                                    <span class="badge status-badge bg-<?php echo $message['status'] == 'unread' ? 'danger' : 'secondary'; ?>">
                                                        <?php echo $message['status'] == 'unread' ? 'Baru' : 'Dibaca'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                                <td>
                                                    <div class="message-content">
                                                        <?php echo nl2br(htmlspecialchars(substr($message['message'], 0, 100))); ?>
                                                        <?php echo strlen($message['message']) > 100 ? '...' : ''; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo date('d M Y, H:i', strtotime($message['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($message['status'] == 'unread'): ?>
                                                        <a href="?action=mark_read&id=<?php echo $message['id']; ?>" class="btn btn-sm btn-success btn-action">
                                                            <i class="bi bi-envelope-open"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="?action=delete&id=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Yakin ingin menghapus pesan ini?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox" style="font-size: 4rem;"></i>
                                <h5>Belum ada pesan masuk</h5>
                                <p>Pesan dari formulir kontak akan muncul di sini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Tandai semua pesan sebagai dibaca
        <?php if (isset($_GET['action']) && $_GET['action'] == 'mark_all_read'): ?>
            <?php
            $stmt = $conn->prepare("UPDATE contact_messages SET status = 'read'");
            $stmt->execute();
            ?>
            window.location.href = 'pesan_masuk.php';
        <?php endif; ?>
    </script>
</body>
</html>