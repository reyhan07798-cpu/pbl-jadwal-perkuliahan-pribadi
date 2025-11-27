// =========================
// HAPUS DATA MAHASISWA LAMA
// =========================
localStorage.removeItem("mahasiswa");

// =========================
// LOGIN HANDLING
// =========================
document.getElementById("loginForm").addEventListener("submit", function (e) {
    e.preventDefault();

    let username = document.getElementById("username").value.trim();
    let password = document.getElementById("password").value.trim();

    let usernameError = document.getElementById("usernameError");
    let passwordError = document.getElementById("passwordError");

    let valid = true;

    if (username === "") {
        usernameError.style.display = "block";
        valid = false;
    } else {
        usernameError.style.display = "none";
    }

    if (password === "") {
        passwordError.style.display = "block";
        valid = false;
    } else {
        passwordError.style.display = "none";
    }

    if (!valid) return;

    // Data login simple tanpa database
    let akunTerdaftar = JSON.parse(localStorage.getItem("akun_mahasiswa"));

    if (!akunTerdaftar) {
        alert("Akun tidak ditemukan! Silakan daftar terlebih dahulu.");
        return;
    }

    if (akunTerdaftar.username === username && akunTerdaftar.password === password) {
        alert("Login berhasil!");
        window.location.href = "jadwal.html"; 
    } else {
        alert("Username atau password salah!");
    }
});
