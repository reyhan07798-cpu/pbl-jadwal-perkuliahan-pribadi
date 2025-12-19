<?php
require_once 'config/koneksi.php';

// Query dengan JOIN untuk mendapatkan nama mata kuliah
 $stmt = $conn->prepare("SELECT s.id, s.day_of_week, c.course_name, s.start_time, s.end_time FROM schedules s JOIN courses c ON s.course_id = c.id ORDER BY FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), s.start_time");
 $stmt->execute();
 $result = $stmt->get_result();

 $conn->close();

 $page_title = 'Daftar Jadwal';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Jadwal</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-info">
                        <tr>
                            <th>Hari</th>
                            <th>Mata Kuliah</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['day_of_week']); ?></td>
                                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td><?php echo date('H:i', strtotime($row['start_time'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($row['end_time'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Tidak ada data jadwal.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

</div> <!-- Penutup .container-fluid -->

<script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>