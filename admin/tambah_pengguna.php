<?php
require_once '../koneksi.php';
require_once 'fungsi.php';

 $page_title = 'Tambah Pengguna';
 $edit_mode = false;
 $username = '';
 $email = '';

if (isset($_GET['id'])) {
    $edit_mode = true;
    $id = $_GET['id'];
    $page_title = 'Edit Pengguna';

    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $username = $data['username'];
        $email = $data['email'];
    }
}

if (isset($_POST['simpan'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    
    if (!$edit_mode) {
        $password = $_POST['password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }

    $role = 'user';

    if (!$edit_mode) {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
        $pesan = "Pengguna berhasil ditambahkan.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $username, $email, $id);
        $pesan = "Data pengguna berhasil diperbarui.";
    }

    if ($stmt->execute()) {
        showToast($pesan, "success");
        header("Location: kelola_pengguna.php");
        exit;
    } else {
        showToast("Gagal: " . $stmt->error, "error");
    }
}

ob_start();
?>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo $edit_mode ? 'Edit Pengguna' : 'Tambah Pengguna'; ?></h6>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <?php if (!$edit_mode): ?>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <?php endif; ?>

            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
            <a href="kelola_pengguna.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php
 $page_content = ob_get_clean();
require_once 'tema.php';
?>