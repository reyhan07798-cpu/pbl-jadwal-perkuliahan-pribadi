<?php
require_once '../koneksi.php';
require_once 'functions.php';

 $page_title = 'Kelola Mata Kuliah';

// Proses hapus mata kuliah
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    showToast("Mata Kuliah berhasil dihapus.", "success");
    header("Location: manage_courses.php");
    exit;
}

// Ambil data mata kuliah
// PERUBAHAN: 'credits' diganti dengan 'sks'
 $stmt = $conn->prepare("SELECT id, course_name, dosen, sks FROM courses ORDER BY course_name ASC");
 $stmt->execute();
 $courses = $stmt->get_result();

 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Mata Kuliah</h6>
        <a href="edit_course.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Mata Kuliah</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Dosen Pengajar</th>
                        <th>SKS</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($courses->num_rows > 0): ?>
                        <?php while($course = $courses->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['dosen']); ?></td>
                            <!-- PERUBAHAN: 'credits' diganti dengan 'sks' -->
                            <td><?php echo htmlspecialchars($course['sks']); ?></td>
                            <td>
                                <a href="edit_course.php?id=<?php echo $course['id']; ?>" class="btn btn-info btn-sm"><i class="bi bi-pencil"></i></a>
                                <a href="manage_courses.php?delete_id=<?php echo $course['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus mata kuliah ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data mata kuliah.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'template.php';
?> 