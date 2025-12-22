<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    showToast("ID Pengguna tidak valid.", "error");
    header("Location: kelola_pengguna.php");
    exit;
}

 $user_id = $_GET['id'];

 $stmt_user = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
 $stmt_user->bind_param("i", $user_id);
 $stmt_user->execute();
 $user_result = $stmt_user->get_result();

if ($user_result->num_rows === 0) {
    showToast("Pengguna tidak ditemukan.", "error");
    header("Location: kelola_pengguna.php");
    exit;
}
 $user = $user_result->fetch_assoc();
 $page_title = 'Jadwal: ' . htmlspecialchars($user['username']);

 $stmt_schedule = $conn->prepare("
    SELECT c.course_name, c.dosen, c.room, s.day_of_week, s.start_time, s.end_time 
    FROM schedules s
    JOIN courses c ON s.course_id = c.id
    WHERE s.user_id = ?
    ORDER BY FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), s.start_time ASC
");
 $stmt_schedule->bind_param("i", $user_id);
 $stmt_schedule->execute();
 $schedules = $stmt_schedule->get_result();

 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="bi bi-calendar-check"></i> Jadwal untuk: <strong><?php echo htmlspecialchars($user['username']); ?></strong> (<?php echo htmlspecialchars($user['email']); ?>)
        </h6>
        <a href="kelola_pengguna.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <?php if ($schedules->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Hari</th>
                            <th>Mata Kuliah</th>
                            <th>Dosen Pengajar</th>
                            <th>Ruangan</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($schedule = $schedules->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['dosen']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['room']); ?></td>
                            <td>
                                <?php echo date('H:i', strtotime($schedule['start_time'])); ?> - 
                                <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle"></i> Pengguna ini belum memiliki jadwal mata kuliah.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'tema.php';
?>