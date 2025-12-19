<?php
require_once 'config/koneksi.php';

 $stmt = $conn->prepare("SELECT id, course_name, created_at FROM courses ORDER BY created_at DESC");
 $stmt->execute();
 $result = $stmt->get_result();

 $conn->close();

 $page_title = 'Daftar Mata Kuliah';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Mata Kuliah</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-success">
                        <tr>
                            <th>ID</th>
                            <th>Nama Mata Kuliah</th>
                            <th>Tanggal Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data mata kuliah.</td>
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