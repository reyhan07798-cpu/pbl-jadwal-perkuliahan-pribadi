<?php
require_once '../koneksi.php';
require_once 'functions.php';

 $page_title = 'Edit Mata Kuliah';
 $course = null;
 $is_edit = isset($_GET['id']);

if ($is_edit) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
    } else {
        showToast("Mata Kuliah tidak ditemukan.", "error");
        header("Location: manage_courses.php");
        exit;
    }
    $page_title = $is_edit ? 'Edit Mata Kuliah: ' . htmlspecialchars($course['course_name']) : 'Tambah Mata Kuliah';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_name = $_POST['course_name'];
    $dosen = $_POST['dosen'];
    // PERUBAHAN: 'credits' diganti dengan 'sks'
    $sks = $_POST['sks'];

    if ($is_edit) {
        $id = $_POST['id'];
        // PERUBAHAN: 'credits' diganti dengan 'sks'
        $stmt = $conn->prepare("UPDATE courses SET course_name=?, dosen=?, sks=? WHERE id = ?");
        $stmt->bind_param("ssii", $course_name, $dosen, $sks, $id);
    } else {
        // PERUBAHAN: 'credits' diganti dengan 'sks'
        $stmt = $conn->prepare("INSERT INTO courses (course_name, dosen, sks) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $course_name, $dosen, $sks);
    }

    if ($stmt->execute()) {
        showToast($is_edit ? "Mata Kuliah berhasil diperbarui." : "Mata Kuliah berhasil ditambahkan.", "success");
        header("Location: manage_courses.php");
        exit;
    } else {
        showToast("Terjadi kesalahan.", "error");
    }
}
 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo $is_edit ? 'Edit' : 'Tambah'; ?> Mata Kuliah</h6>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $course['id']; ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="course_name" class="form-label">Nama Mata Kuliah</label>
                <input type="text" class="form-control" id="course_name" name="course_name" value="<?php echo htmlspecialchars($course['course_name'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="dosen" class="form-label">Dosen Pengajar</label>
                <input type="text" class="form-control" id="dosen" name="dosen" value="<?php echo htmlspecialchars($course['dosen'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <!-- PERUBAHAN: id, name, dan value 'credits' diganti dengan 'sks' -->
                <label for="sks" class="form-label">SKS</label>
                <input type="number" class="form-control" id="sks" name="sks" value="<?php echo htmlspecialchars($course['sks'] ?? ''); ?>" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="manage_courses.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'template.php';
?>