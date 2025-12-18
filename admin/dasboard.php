<?php
require_once '../koneksi.php';
require_once 'functions.php';

// --- 1. LOGIKA HALAMAN ---
 $page_title = 'Dashboard';

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

// Query untuk aktivitas terkini (pengguna baru)
 $recent_users = $conn->prepare("SELECT username, created_at FROM users ORDER BY created_at DESC LIMIT 5");
 $recent_users->execute();
 $result_users = $recent_users->get_result();

// --- PERUBAHAN DIMULAI DI SINI ---
// Query data untuk grafik jadwal per hari
// Menggunakan kolom 'day_of_week' yang sesuai dengan struktur database Anda
 $schedule_data = $conn->query("SELECT day_of_week, COUNT(*) as count FROM schedules GROUP BY day_of_week ORDER BY FIELD(day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat')");

 $labels = [];
 $data = [];
while($row = $schedule_data->fetch_assoc()){
    // Mengambil data dari kolom 'day_of_week'
    $labels[] = $row['day_of_week']; 
    $data[] = $row['count'];
}
// --- PERUBAHAN SELESAI DI SINI ---

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
require_once 'template.php';
?>