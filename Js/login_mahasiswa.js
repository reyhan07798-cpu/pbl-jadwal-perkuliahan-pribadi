document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("loginForm");
  const username = document.getElementById("username");
  const password = document.getElementById("password");
  const usernameError = document.getElementById("usernameError");
  const passwordError = document.getElementById("passwordError");

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    usernameError.style.display = "none";
    passwordError.style.display = "none";

    let valid = true;

    if (username.value.trim() === "") {
      usernameError.style.display = "block";
      valid = false;
    }

    if (password.value.trim() === "") {
      passwordError.style.display = "block";
      valid = false;
    }

    if (!valid) return;

    // Akun default (contoh dari kamu)
    const akunDefault = [
      { username: "Naya Khairunnisa", password: "3312501019" },
      { username: "Puja Davi", password: "3312501020" },
      { username: "Jelita Aulia", password: "3312501021" },
      { username: "Reyhan", password: "3312501022" },
    ];

    const akunRegister = JSON.parse(localStorage.getItem("daftarAkun")) || [];


    const semuaAkun = [...akunDefault, ...akunRegister];

    const cocok = semuaAkun.find(
      (a) => a.username === username.value && a.password === password.value
    );

    if (cocok) {
      alert("✅ Login Berhasil! Selamat datang, " + cocok.username);
      localStorage.setItem("mahasiswa", cocok.username);
      window.location.href = "jadwal_mahasiswa.html";
    } else {
      alert("❌ Username atau Password salah!");
    }
  });
});
