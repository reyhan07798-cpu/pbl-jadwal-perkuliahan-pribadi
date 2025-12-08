<?php
session_start();
require_once "../koneksi.php";
 $username = $email = $new_password = "";
 $username_err = $email_err = $password_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify'])) {
    if (empty(trim($_POST["username"]))) $username_err = "Username tidak boleh kosong."; else $username = trim($_POST["username"]);
    if (empty(trim($_POST["email"]))) $email_err = "Email tidak boleh kosong."; else $email = trim($_POST["email"]);
    if (empty($username_err) && empty($email_err)) {
        $sql = "SELECT id FROM users WHERE username = ? AND email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_username, $param_email);
            $param_username = $username; $param_email = $email;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) { $stmt->bind_result($user_id); $stmt->fetch(); $_SESSION['verified_for_reset'] = $user_id; }
                else { $username_err = "Username atau Email tidak cocok dengan akun manapun."; }
            }
            $stmt->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
    if (isset($_SESSION['verified_for_reset'])) {
        if (empty(trim($_POST["new_password"]))) $password_err = "Password baru tidak boleh kosong.";
        elseif (strlen(trim($_POST["new_password"])) < 6) $password_err = "Password harus memiliki minimal 6 karakter.";
        else $new_password = trim($_POST["new_password"]);
        if (empty($password_err)) {
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("si", $param_password, $param_id);
                $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                $param_id = $_SESSION['verified_for_reset'];
                if ($stmt->execute()) {
                    unset($_SESSION['verified_for_reset']);
                    $success_msg = "Password berhasil diubah! Anda akan dialihkan ke halaman login dalam 3 detik.";
                    header("refresh:3;url=login_mahasiswa.php");
                }
                $stmt->close();
            }
        }
    } else { header("location: lupa_password.php"); exit(); }
}
 $conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Css/lupa_password.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Lupa Password</h2>
                        <?php if(!empty($success_msg)){ echo '<div class="alert alert-success" role="alert">' . $success_msg . '</div>'; } ?>
                        <?php if (!isset($_SESSION['verified_for_reset'])): ?>
                            <p class="text-center text-muted mb-4">Masukkan username dan email Anda untuk memverifikasi akun.</p>
                            <?php if(!empty($username_err) || !empty($email_err)){ echo '<div class="alert alert-danger" role="alert">Harap perbaiki kesalahan di bawah.</div>'; } ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="mb-3"><label for="username" class="form-label">Username</label><input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>"><div class="invalid-feedback"><?php echo $username_err; ?></div></div>
                                <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>"><div class="invalid-feedback"><?php echo $email_err; ?></div></div>
                                <div class="d-grid"><button type="submit" name="verify" class="btn btn-primary">Verifikasi Akun</button></div>
                            </form>
                        <?php else: ?>
                            <p class="text-center text-muted mb-4">Akun terverifikasi. Silakan masukkan password baru Anda.</p>
                            <?php if(!empty($password_err)){ echo '<div class="alert alert-danger" role="alert">' . $password_err . '</div>'; } ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <div class="mb-3"><label for="new_password" class="form-label">Password Baru</label><input type="password" name="new_password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"><div class="invalid-feedback"><?php echo $password_err; ?></div></div>
                                <div class="d-grid"><button type="submit" name="reset" class="btn btn-success">Ubah Password</button></div>
                            </form>
                        <?php endif; ?>
                        <hr><p class="text-center mb-0">Sudah ingat akun Anda? <a href="login_mahasiswa.php">Login di sini</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>