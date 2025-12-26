<?php
session_start();
require_once "../koneksi.php";

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if ($_SESSION['role'] == 'admin') {
        header("location: ../admin/beranda.php"); // PERBAIKAN: dasboard.php -> beranda.php
    } else {
        header("location: ../Mahasiswa/jadwal.php");
    }
    exit;
}

 $username = $password = "";
 $username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["username"]))){
        $username_err = "Masukkan username.";
    } else {
        $username = trim($_POST["username"]);
    }
    if(empty(trim($_POST["password"]))){
        $password_err = "Masukkan password.";
    } else {
        $password = trim($_POST["password"]);
    }
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_username);
            $param_username = $username;
            
            if($stmt->execute()){
                $stmt->store_result();
                
                if($stmt->num_rows == 1){
                    $stmt->bind_result($id, $username_db, $hashed_password, $role);
                    
                    if($stmt->fetch()){
                        if(!empty($hashed_password)){
                            if(password_verify($password, $hashed_password)){
                                session_regenerate_id();
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username_db;
                                $_SESSION["role"] = $role;
                    
                                // PERBAIKAN: Menambahkan kurung kurawal {
                                if ($_SESSION['role'] == 'admin') {
                                    header("location: ../admin/beranda.php");
                                } else {
                                    header("location: ../Mahasiswa/jadwal.php");
                                }
                                exit; // PERBAIKAN: Menambahkan kurung kurawal }
                            } else{
                                $login_err = "Username atau password tidak valid.";
                            }
                        } else {
                            $login_err = "Terjadi kesalahan dengan akun Anda. Silakan hubungi administrator.";
                        }
                    }
                } else{
                    $login_err = "Username atau password tidak valid.";
                }
            } else{
                echo "Oops! Ada yang salah. Silakan coba lagi nanti.";
            }
            $stmt->close();
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
    <title>Login Aplikasi Jadwal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Menggunakan Bootstrap Icons untuk ikon mata -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../Css/login_mahasiswa.css">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Login</h2>
                        <p class="text-center text-muted mb-4">Silakan masukkan username dan password.</p>
                        <?php if(!empty($login_err)){ echo '<div class="alert alert-danger" role="alert">' . $login_err . '</div>'; } ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" id="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>">
                                <?php if(!empty($username_err)){ echo '<div class="invalid-feedback">' . $username_err . '</div>'; } ?>
                            </div>    
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <!-- Menggunakan input-group untuk menggabungkan field input dan tombol mata -->
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                                    <span class="input-group-text" id="togglePassword">
                                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                                    </span>
                                </div>
                                <?php if(!empty($password_err)){ echo '<div class="invalid-feedback">' . $password_err . '</div>'; } ?>
                            </div>
                            
                            <div class="d-grid">
                                <input type="submit" class="btn btn-primary" value="Login">
                            </div>
                            <p class="text-center mt-3 mb-0"><a href="lupa_password.php">Lupa Password?</a></p>
                        </form>
                        <hr>
                        <p class="text-center mb-0">Belum punya akun? <a href="register.php">Daftar sekarang</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript untuk Toggle Show/Hide Password
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const toggleIcon = document.querySelector('#toggleIcon');

        togglePassword.addEventListener('click', function (e) {
            // Cek tipe saat ini
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            
            // Ubah tipe
            password.setAttribute('type', type);
            
            // Ubah ikon (bi-eye-slash vs bi-eye)
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>