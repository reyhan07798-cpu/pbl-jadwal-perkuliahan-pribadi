<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

$page_title = 'Daftar Mata Kuliah';

$stmt = $conn->prepare(
    "SELECT id, course_name, dosen, sks, room 
     FROM courses 
     ORDER BY course_name ASC"
);
$stmt->execute();
$courses = $stmt->get_result();

$conn->close();
ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            Daftar Mata Kuliah yang Tersedia
        </h6>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Mata Kuliah</th>
                        <th>Dosen Pengajar</th>
                        <th>SKS</th>
                        <th>Ruangan</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($courses->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($course = $courses->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($course['course_name']); ?></td>
                            <td><?= htmlspecialchars($course['dosen']); ?></td>
                            <td><?= htmlspecialchars($course['sks']); ?></td>
                            <td><?= htmlspecialchars($course['room']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">
                            Belum ada mata kuliah yang tersedia.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$page_content = ob_get_clean();
require_once 'tema.php';
?>
