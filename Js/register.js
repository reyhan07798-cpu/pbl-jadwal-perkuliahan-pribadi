document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("registerForm");
  const username = document.getElementById("username");
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirmPassword");

  const usernameError = document.getElementById("usernameError");
  const passwordError = document.getElementById("passwordError");
  const confirmPasswordError = document.getElementById("confirmPasswordError");

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    usernameError.style.display = "none";
    passwordError.style.display = "none";
    confirmPasswordError.style.display = "none";

    let valid = true;

    if (username.value.trim() === "") {
      usernameError.style.display = "block";
      valid = false;
    }

    if (password.value.trim() === "") {
      passwordError.style.display = "block";
      valid = false;
    }

    if (confirmPassword.value.trim() === "" || confirmPassword.value !== password.value) {
      confirmPasswordError.style.display = "block";
      valid = false;
    }

    if (!valid) return;

    let daftarAkun = JSON.parse(localStorage.getItem("daftarAkun")) || [];

    const sudahAda = daftarAkun.find(a => a.username === username.value);
    if (sudahAda) {
      alert("⚠️ Username sudah terdaftar! Silakan gunakan username lain.");
      return;
    }

    daftarAkun.push({ username: username.value, password: password.value });
    localStorage.setItem("daftarAkun", JSON.stringify(daftarAkun));

    alert("✅ Registrasi berhasil! Silakan login menggunakan akun Anda.");
    window.location.href = "login.html";
  });
});
