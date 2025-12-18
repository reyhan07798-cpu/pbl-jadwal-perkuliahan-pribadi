<?php
require_once '../koneksi.php';
require_once 'functions.php';

 $page_title = 'Edit Pengguna';
 $user = null;
 $is_edit = isset($_GET['id']);

if ($is_edit) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        showToast("Pengguna tidak ditemukan.", "error");
        header("Location: manage_users.php");
        exit;
    }
    $page_title = $is_edit ? 'Edit Pengguna: ' . htmlspecialchars($user['username']) : 'Tambah Pengguna';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    if ($is_edit) {
        $id = $_POST['id'];
        $sql = "UPDATE users SET username=?, email=?, role=?";
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password=?";
        }
        $sql .= " WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!empty($password)) {
            $stmt->bind_param("ssssi", $username, $email, $role, $hashed_password, $id);
        } else {
            $stmt->bind_param("sssi", $username, $email, $role, $id);
        }
    } else {
        if (empty($password)) {
            showToast("Password wajib diisi untuk pengguna baru.", "error");
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        }
    }

    if (isset($stmt) && $stmt->execute()) {
        showToast($is_edit ? "Pengguna berhasil diperbarui." : "Pengguna berhasil ditambahkan.", "success");
        header("Location: manage_users.php");
        exit;
    } else {
        showToast("Terjadi kesalahan. Mungkin username atau email sudah digunakan.", "error");
    }
}
 $conn->close();

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo $is_edit ? 'Edit' : 'Tambah'; ?> Pengguna</h6>
    </div>
    <div class="card-body">
        <form action="" method="post">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            <?php endif; ?>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="user" <?php echo (isset($user) && $user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo (isset($user) && $user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password <?php echo $is_edit ? '(Kosongkan jika tidak ingin diubah)' : ''; ?></label>
                <input type="password" class="form-control" id="password" name="password" <?php echo !$is_edit ? 'required' : ''; ?>>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="manage_users.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'template.php';
?>