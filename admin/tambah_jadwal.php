<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

 $schedule = null;
 $is_edit = isset($_GET['id']);

if ($is_edit) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM schedules WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $schedule = $result->fetch_assoc();
    } else {
        showToast("Jadwal tidak ditemukan.", "error");
        header("Location: kelola_jadwal.php");
        exit;
    }
    $page_title = 'Ubah Jadwal';
} else {
    $page_title = 'Tambah Jadwal';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $course_id = $_POST['course_id'];
    $day_of_week = $_POST['day_of_week'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if (strtotime($start_time) >= strtotime($end_time)) {
        showToast("Waktu mulai harus lebih awal dari waktu selesai.", "error");
    } else {
        if ($is_edit) {
            $id = $_POST['id'];
            $stmt = $conn->prepare("UPDATE schedules SET user_id=?, course_id=?, day_of_week=?, start_time=?, end_time=? WHERE id = ?");
            $stmt->bind_param("iisssi", $user_id, $course_id, $day_of_week, $start_time, $end_time, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO schedules (user_id, course_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $user_id, $course_id, $day_of_week, $start_time, $end_time);
        }

        if ($stmt->execute()) {
            showToast($is_edit ? "Jadwal berhasil diperbarui." : "Jadwal berhasil ditambahkan.", "success");
            header("Location: kelola_jadwal.php");
            exit;
        } else {
            showToast("Terjadi kesalahan.", "error");
        }
    }
}

 $users = $conn->query("SELECT id, username FROM users ORDER BY username ASC");
 $courses = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");
 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo $is_edit ? 'Ubah' : 'Tambah'; ?> Jadwal</h6>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
            <?php endif; ?>
            
            <div class="mb-3">
                <label for="user_id" class="form-label">Pilih Pengguna</label>
                <select class="form-control" id="user_id" name="user_id" required>
                    <option value="">-- Pilih Pengguna --</option>
                    <?php while($user = $users->fetch_assoc()): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo (isset($schedule) && $schedule['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="course_id" class="form-label">Pilih Mata Kuliah</label>
                <select class="form-control" id="course_id" name="course_id" required>
                    <option value="">-- Pilih Mata Kuliah --</option>
                    <?php while($course = $courses->fetch_assoc()): ?>
                        <option value="<?php echo $course['id']; ?>" <?php echo (isset($schedule) && $schedule['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="day_of_week" class="form-label">Hari</label>
                <select class="form-control" id="day_of_week" name="day_of_week" required>
                    <option value="">-- Pilih Hari --</option>
                    <option value="Senin" <?php echo (isset($schedule) && $schedule['day_of_week'] == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                    <option value="Selasa" <?php echo (isset($schedule) && $schedule['day_of_week'] == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                    <option value="Rabu" <?php echo (isset($schedule) && $schedule['day_of_week'] == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                    <option value="Kamis" <?php echo (isset($schedule) && $schedule['day_of_week'] == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                    <option value="Jumat" <?php echo (isset($schedule) && $schedule['day_of_week'] == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="start_time" class="form-label">Waktu Mulai</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo htmlspecialchars($schedule['start_time'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="end_time" class="form-label">Waktu Selesai</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo htmlspecialchars($schedule['end_time'] ?? ''); ?>" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="kelola_jadwal.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'tema.php';
?>