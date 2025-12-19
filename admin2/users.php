<?php
require_once 'config/koneksi.php';

// Query untuk mendapatkan semua data pengguna
 $stmt = $conn->prepare("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
 $stmt->execute();
 $result = $stmt->get_result();

 $conn->close();

 $page_title = 'Daftar Pengguna';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<!-- Konten Utama -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Daftar Pengguna</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Tanggal Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['role']); ?></span></td>
                                    <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">Tidak ada data pengguna.</td>
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