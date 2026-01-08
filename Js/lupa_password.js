document.getElementById("lupaPasswordForm").addEventListener("submit", function (e) {
    e.preventDefault();

    let username = document.getElementById("username").value.trim();
    let email = document.getElementById("email").value.trim();
    let newPassword = document.getElementById("newPassword").value.trim();

    let usernameError = document.getElementById("usernameError");
    let emailError = document.getElementById("emailError");
    let newPasswordError = document.getElementById("newPasswordError");

    let valid = true;

    if (username === "") {
        usernameError.style.display = "block";
        valid = false;
    } else {
        usernameError.style.display = "none";
    }

    if (email === "") {
        emailError.textContent = "Email tidak boleh kosong!";
        emailError.style.display = "block";
        valid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { // Validasi format email sederhana
        emailError.textContent = "Format email tidak valid!";
        emailError.style.display = "block";
        valid = false;
    } else {
        emailError.style.display = "none";
    }

    if (newPassword === "") {
        newPasswordError.style.display = "block";
        valid = false;
    } else {
        newPasswordError.style.display = "none";
    }

    if (!valid) return;
    let akunTerdaftar = JSON.parse(localStorage.getItem("akun_mahasiswa"));
    
    if (!akunTerdaftar) {
        alert("Tidak ada akun terdaftar! Silakan daftar terlebih dahulu.");
        return;
    }

    if (akunTerdaftar.username === username && akunTerdaftar.email === email) {
        akunTerdaftar.password = newPassword;
        localStorage.setItem("akun_mahasiswa", JSON.stringify(akunTerdaftar));
        
        alert("Password berhasil diubah! Silakan login dengan password baru Anda.");
        window.location.href = "login_mahasiswa.php"; 
    } else {
        alert("Username atau Email tidak cocok dengan data akun!");
    }
});