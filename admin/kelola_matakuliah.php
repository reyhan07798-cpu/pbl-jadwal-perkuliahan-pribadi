<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

// PERUBAHAN: Judul halaman menjadi "Daftar Mata Kuliah"
 $page_title = 'Daftar Mata Kuliah';

// Query untuk menampilkan semua mata kuliah
 $stmt = $conn->prepare("SELECT id, course_name, dosen, sks, room FROM courses ORDER BY course_name ASC");
 $stmt->execute();
 $courses = $stmt->get_result();

 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <!-- PERUBAHAN: Judul card dan hapus tombol "Tambah" -->
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Mata Kuliah yang Tersedia</h6>
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
                        <th>Ruangan</th>
                        <!-- PERUBAHAN: Hapus kolom "Aksi" -->
                    </tr>
                </thead>
                <tbody>
                    <?php if ($courses->num_rows > 0): ?>
                        <?php while($course = $courses->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $course['id']; ?></td>
                            <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                            <td><?php echo htmlspecialchars($course['dosen']); ?></td>
                            <td><?php echo htmlspecialchars($course['sks']); ?></td>
                            <td><?php echo htmlspecialchars($course['room']); ?></td>
                            <!-- PERUBAHAN: Hapus tombol Edit dan Hapus -->
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <!-- PERUBAHAN: Sesuaikan colspan -->
                            <td colspan="5" class="text-center">Belum ada mata kuliah yang tersedia.</td>
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