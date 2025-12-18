<?php
require_once '../koneksi.php';
require_once 'functions.php';

 $page_title = 'Kelola Pengguna';

// Proses hapus user
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    if ($delete_id == $_SESSION['id']) {
        showToast("Anda tidak bisa menghapus akun Anda sendiri.", "error");
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        showToast("Pengguna berhasil dihapus.", "success");
    }
    header("Location: manage_users.php");
    exit;
}

// Proses reset password
if (isset($_GET['reset_id'])) {
    $reset_id = $_GET['reset_id'];
    $new_password = '12345678'; // Password default baru
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $reset_id);
    $stmt->execute();
    
    showToast("Password berhasil direset. Password baru adalah: <strong>{$new_password}</strong>", "warning");
    header("Location: manage_users.php");
    exit;
}

// Logika Pencarian
 $search = $_GET['search'] ?? '';
 $query = "SELECT id, username, email, created_at, role FROM users";
if (!empty($search)) {
    $query .= " WHERE username LIKE ? OR email LIKE ?";
}
 $query .= " ORDER BY created_at DESC";

 $stmt = $conn->prepare($query);
if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt->bind_param("ss", $search_param, $search_param);
}
 $stmt->execute();
 $users = $stmt->get_result();

 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Pengguna</h6>
        <a href="edit_user.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Tambah Pengguna</a>
    </div>
    <div class="card-body">
        <!-- Form Pencarian -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan username atau email..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
                <a href="manage_users.php" class="btn btn-outline-danger">Reset</a>
            </div>
        </form>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users->num_rows > 0): ?>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                <a href="manage_users.php?reset_id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm" title="Reset Password" onclick="return confirm('Yakin ingin mereset password pengguna ini?')"><i class="bi bi-key"></i></a>
                                <a href="manage_users.php?delete_id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin ingin menghapus pengguna ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data pengguna.</td>
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