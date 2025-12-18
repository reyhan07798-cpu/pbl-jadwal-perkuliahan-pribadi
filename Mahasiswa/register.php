<?php
require_once "../koneksi.php";
 $username = $email = $password = $confirm_password = "";
 $username_err = $email_err = $password_err = $confirm_password_err = $register_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["username"]))){
        $username_err = "Username tidak boleh kosong.";
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = trim($_POST["username"]);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $username_err = "Username ini sudah digunakan.";
                } else{
                    $username = trim($_POST["username"]);
                }
            }
            $stmt->close();
        }
    }
    if(empty(trim($_POST["email"]))){
        $email_err = "Email tidak boleh kosong.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $email_err = "Email ini sudah digunakan.";
                } else{
                    $email = trim($_POST["email"]);
                }
            }
            $stmt->close();
        }
    }
    if(empty(trim($_POST["password"]))){
        $password_err = "Password tidak boleh kosong.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password harus memiliki minimal 6 karakter.";
    } else{
        $password = trim($_POST["password"]);
    }
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Harap konfirmasi password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password tidak cocok.";
        }
    }
    if(empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("sss", $param_username, $param_email, $param_password);
            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            if($stmt->execute()){
                header("location: login_mahasiswa.php");
                exit();
            } else{
                $register_err = "Oops! Ada yang salah. Silakan coba lagi nanti.";
            }
            $stmt->close();
        }
    }}
    $conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/resgister.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Daftar Mahasiswa</h2>
                        <p class="text-center text-muted mb-4">Silakan isi form untuk membuat akun.</p>
                        <?php if(!empty($register_err)){ echo '<div class="alert alert-danger" role="alert">' . $register_err . '</div>'; } ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
                                <div class="invalid-feedback"><?php echo $username_err; ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
                                <div class="invalid-feedback"><?php echo $email_err; ?></div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $password_err; ?></div>
                            </div>
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                                <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                            </div>
                            <div class="d-grid">
                                <input type="submit" class="btn btn-primary" value="Daftar">
                            </div>
                        </form>
                        <hr>
                        <p class="text-center mb-0">Sudah punya akun? <a href="login_mahasiswa.php">Login di sini</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>