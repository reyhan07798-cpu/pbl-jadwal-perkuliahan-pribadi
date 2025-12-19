<?php
require_once 'config/koneksi.php';

// --- LOGIKA: MENGAMBIL DATA DARI DATABASE ---
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

 $stmt_activity = $conn->prepare("SELECT a.activity, a.created_at FROM activity_log a ORDER BY a.created_at DESC LIMIT 5");
 $stmt_activity->execute();
 $result_activity = $stmt_activity->get_result();

 $schedule_data = $conn->query("SELECT day_of_week, COUNT(*) as count FROM schedules GROUP BY day_of_week ORDER BY FIELD(day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')");

 $labels = [];
 $data = [];
while($row = $schedule_data->fetch_assoc()){
    $labels[] = $row['day_of_week']; 
    $data[] = $row['count'];
}

 $conn->close();

// Set page title dan panggil header/sidebar
 $page_title = 'Dashboard';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!-- Konten Utama -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>

    <!-- Kartu Statistik -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Pengguna</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-people-fill fa-2x text-gray-300"></i></div>
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
                        <div class="col-auto"><i class="bi bi-book-fill fa-2x text-gray-300"></i></div>
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
                        <div class="col-auto"><i class="bi bi-calendar-week-fill fa-2x text-gray-300"></i></div>
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
                        <div class="col-auto"><i class="bi bi-sticky-fill fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik dan Aktivitas Terkini -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Distribusi Jadwal per Hari</h6></div>
                <div class="card-body"><canvas id="scheduleChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header"><h6 class="m-0 font-weight-bold text-primary">Aktivitas Terkini</h6></div>
                <div class="card-body">
                    <?php if ($result_activity->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while($activity = $result_activity->fetch_assoc()): ?>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <p class="mb-1"><?php echo $activity['activity']; ?></p>
                                        <small class="text-muted"><?php echo date('d M', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>Belum ada aktivitas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

</div> <!-- Penutup .container-fluid dari header -->

<!-- Bootstrap 5 JS Bundle (Lokal) -->
<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('scheduleChart').getContext('2d');
    const scheduleChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Jumlah Jadwal',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: 'rgba(90, 135, 135, 0.5)',
                borderColor: 'rgba(90, 135, 135, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
</script>
</body>
</html>