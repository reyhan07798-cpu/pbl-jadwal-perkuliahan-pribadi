document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("loginForm");
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    const usernameError = document.getElementById("usernameError");
    const passwordError = document.getElementById("passwordError");

    form.addEventListener("submit", function (event) {
        let valid = true;

        // Reset error messages
        usernameError.style.display = "none";
        passwordError.style.display = "none";

        // Validasi Username
        if (username.value.trim() === "") {
            usernameError.style.display = "block";
            valid = false;
        }

        // Validasi Password
        if (password.value.trim() === "") {
            passwordError.style.display = "block";
            valid = false;
        }

        // Jika tidak valid, cegah form dari submit
        if (!valid) {
            event.preventDefault();
        }
    });
});