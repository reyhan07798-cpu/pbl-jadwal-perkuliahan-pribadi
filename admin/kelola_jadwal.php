<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

 $page_title = 'Lihat Jadwal';

// Query untuk menampilkan semua jadwal dengan info lengkap
 $stmt = $conn->prepare("
    SELECT 
        u.id as user_id, u.username,
        s.id as schedule_id, s.day_of_week, s.start_time, s.end_time,
        c.course_name, c.dosen, c.room
    FROM schedules s
    JOIN users u ON s.user_id = u.id
    JOIN courses c ON s.course_id = c.id
    ORDER BY u.username ASC, FIELD(s.day_of_week, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'), s.start_time ASC
");
 $stmt->execute();
 $result = $stmt->get_result();

// Kelompokkan hasil berdasarkan user_id
 $grouped_schedules = [];
while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    if (!isset($grouped_schedules[$user_id])) {
        $grouped_schedules[$user_id] = [
            'username' => $row['username'],
            'schedules' => []
        ];
    }
    $grouped_schedules[$user_id]['schedules'][] = $row;
}

 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Jadwal per Pengguna</h6>
        <!-- PERUBAHAN: Hapus tombol "Tambah Jadwal Baru" -->
    </div>
    <div class="card-body">
        <?php if (!empty($grouped_schedules)): ?>
            <?php foreach ($grouped_schedules as $user_id => $data): ?>
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 text-primary">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($data['username']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($data['schedules'])): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Hari</th>
                                            <th>Mata Kuliah</th>
                                            <th>Dosen</th>
                                            <th>Ruangan</th>
                                            <th>Waktu</th>
                                            <!-- PERUBAHAN: Hapus kolom "Aksi" -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['schedules'] as $schedule): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($schedule['day_of_week']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['course_name']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['dosen']); ?></td>
                                                <td><?php echo htmlspecialchars($schedule['room']); ?></td>
                                                <td>
                                                    <?php echo date('H:i', strtotime($schedule['start_time'])); ?> - 
                                                    <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
                                                </td>
                                                <!-- PERUBAHAN: Hapus tombol Edit dan Hapus -->
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Pengguna ini belum memiliki jadwal.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle"></i> Belum ada jadwal yang dibuat oleh pengguna mana pun.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'tema.php';
?>