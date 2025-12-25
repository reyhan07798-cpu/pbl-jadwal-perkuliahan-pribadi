<?php
require_once '../koneksi.php';
require_once 'fungsi.php'; 

// Cek apakah user sudah login dan role-nya admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    header('location: ../Mahasiswa/login_mahasiswa.php');
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

// --- 1. LOGIKA HALAMAN ---
 $page_title = 'Beranda';

// Query untuk mendapatkan statistik
 $stmt_users = $conn->prepare("SELECT COUNT(*) FROM users");
 $stmt_users->execute();
 $total_users = $stmt_users->get_result()->fetch_row()[0];

 $stmt_courses = $conn->prepare("SELECT COUNT(*) FROM courses");
 $stmt_courses->execute();
 $total_courses = $stmt_courses->get_result()->fetch_row()[0];

 $stmt_schedules = $conn->prepare("SELECT COUNT(*) FROM schedules");
 $stmt_schedules->execute();
 $total_schedules = $stmt_schedules->get_result()->fetch_row()[0];

 $stmt_notes = $conn->prepare("SELECT COUNT(*) FROM notes");
 $stmt_notes->execute();
 $total_notes = $stmt_notes->get_result()->fetch_row()[0];

// --- PERUBAHAN: Query untuk statistik pesan ---
 $stmt_messages = $conn->prepare("SELECT COUNT(*) FROM contact_messages");
 $stmt_messages->execute();
 $total_messages = $stmt_messages->get_result()->fetch_row()[0];

// --- PERUBAHAN: Query untuk pesan belum dibaca ---
 $stmt_unread = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'");
 $stmt_unread->execute();
 $total_unread = $stmt_unread->get_result()->fetch_row()[0];


// Query untuk aktivitas terkini (pengguna baru)
 $recent_users = $conn->prepare("SELECT username, created_at FROM users ORDER BY created_at DESC LIMIT 5");
 $recent_users->execute();
 $result_users = $recent_users->get_result();

// --- PERUBAHAN: Query untuk pesan terbaru ---
 $recent_messages = $conn->prepare("SELECT name, message, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5");
 $recent_messages->execute();
 $result_messages = $recent_messages->get_result();

// --- PERUBAHAN: Query data untuk grafik jadwal per hari ---
 $schedule_data = $conn->query("SELECT day_of_week, COUNT(*) as count FROM schedules GROUP BY day_of_week ORDER BY FIELD(day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')");

 $labels = [];
 $data = [];
while($row = $schedule_data->fetch_assoc()){
    $labels[] = $row['day_of_week']; 
    $data[] = $row['count'];
}

 $conn->close();

// --- 2. SIMPAN ISI HALAMAN KE VARIABEL ---
ob_start();
?>

<!-- Konten Dashboard -->
<div class="row">
    <!-- Kartu Statistik -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pengguna</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Mata Kuliah</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_courses; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-book fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Jadwal</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_schedules; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-calendar-week fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Catatan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_notes; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-sticky fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- --- PERUBAHAN: Kartu Statistik Pesan --- -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Pesan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_messages; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-envelope fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-secondary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Pesan Belum Dibaca</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_unread; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-envelope-open fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grafik dan Aktivitas Terkini -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Distribusi Jadwal per Hari</h6>
            </div>
            <div class="card-body">
                <canvas id="scheduleChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Pengguna Baru Terdaftar</h6>
            </div>
            <div class="card-body">
                <?php if ($result_users->num_rows > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php while($user = $result_users->fetch_assoc()): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?php echo htmlspecialchars($user['username']); ?></span>
                                <small><?php echo date('d M Y', strtotime($user['created_at'])); ?></small>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>Belum ada pengguna baru.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- --- PERUBAHAN: Aktivitas Terkini Pesan --- -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Pesan Terbaru dari Form Kontak</h6>
                <a href="pesan_masuk.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Lihat Semua Pesan</a>
            </div>
            <div class="card-body">
                <?php if ($result_messages->num_rows > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php while($message = $result_messages->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="me-3">
                                        <div class="fw-bold"><?php echo htmlspecialchars($message['name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($message['email']); ?></small>
                                    </div>
                                    <div>
                                        <?php 
                                            $preview = substr($message['message'], 0, 50);
                                            echo htmlspecialchars($preview);
                                            if (strlen($message['message']) > 50) {
                                                echo '...';
                                            }
                                        ?>
                                    </div>
                                    <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($message['created_at'])); ?></small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?php echo $message['status'] == 'unread' ? 'danger' : 'secondary'; ?> me-2">
                                        <?php echo $message['status'] == 'unread' ? 'Baru' : 'Dibaca'; ?>
                                    </span>
                                    <a href="pesan_masuk.php?mark_read=<?php echo $message['id']; ?>" class="btn btn-sm btn-info me-1">Tandai</a>
                                    <a href="pesan_masuk.php?delete=<?php echo $message['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus pesan ini?')">Hapus</a>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>Belum ada pesan masuk.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('scheduleChart').getContext('2d');
    const scheduleChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Jumlah Jadwal',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

<?php
// --- 3. TUTUP BUFFER DAN SIMPAN KE VARIABEL ---
 $page_content = ob_get_clean();

// --- 4. PANGGIL TEMPLATE ---
require_once 'tema.php';
?>