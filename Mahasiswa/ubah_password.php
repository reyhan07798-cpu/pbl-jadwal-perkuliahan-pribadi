<?php
session_start();
require_once "../koneksi.php";

// Cek apakah user sudah login, jika belum redirect ke halaman login
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login_mahasiswa.php");
    exit;
}

 $current_password = $new_password = $confirm_password = "";
 $current_password_err = $new_password_err = $confirm_password_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validasi password lama
    if (empty(trim($_POST["current_password"]))) {
        $current_password_err = "Masukkan password lama Anda.";
    } else {
        $current_password = trim($_POST["current_password"]);
    }

    // Validasi password baru
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Masukkan password baru.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password baru harus memiliki minimal 6 karakter.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validasi konfirmasi password baru
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Konfirmasi password baru.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password baru tidak cocok.";
        }
    }

    // Jika tidak ada error, lanjutkan proses
    if (empty($current_password_err) && empty($new_password_err) && empty($confirm_password_err)) {
        
        // Ambil password lama dari database
        $sql = "SELECT password FROM users WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $param_id);
            $param_id = $_SESSION["id"];
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($hashed_password);
                    if ($stmt->fetch()) {
                        // Verifikasi password lama yang dimasukkan user dengan password di database
                        if (password_verify($current_password, $hashed_password)) {
                            
                            // Jika password lama benar, update password baru
                            $sql_update = "UPDATE users SET password = ? WHERE id = ?";
                            if ($stmt_update = $conn->prepare($sql_update)) {
                                $stmt_update->bind_param("si", $param_new_password, $param_id);
                                $param_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $param_id = $_SESSION["id"];
                                
                                if ($stmt_update->execute()) {
                                    $success_msg = "Password berhasil diubah!";
                                } else {
                                    echo "Oops! Terjadi kesalahan. Silakan coba lagi.";
                                }
                                $stmt_update->close();
                            }
                        } else {
                            $current_password_err = "Password lama yang Anda masukkan salah.";
                        }
                    }
                }
                $stmt->close();
            }
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Ubah Password</h2>
                        <p class="text-center text-muted mb-4">Masukkan password lama dan baru Anda.</p>

                        <?php if(!empty($success_msg)){ echo '<div class="alert alert-success" role="alert">' . $success_msg . '</div>'; } ?>
                        
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Lama</label>
                                <input type="password" name="current_password" class="form-control <?php echo (!empty($current_password_err)) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $current_password_err; ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $new_password_err; ?></div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                            </div>
                            <div class="d-grid">
                                <input type="submit" class="btn btn-warning" value="Ubah Password">
                            </div>
                        </form>
                        <hr>
                        <p class="text-center mb-0"><a href="jadwal.php">Kembali ke Dashboard</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>