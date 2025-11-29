document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerForm");
    const username = document.getElementById("username");
    const email = document.getElementById("email");
    const password = document.getElementById("password");
    const confirmPassword = document.getElementById("confirmPassword");

    const usernameError = document.getElementById("usernameError");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");
    const confirmPasswordError = document.getElementById("confirmPasswordError");

    form.addEventListener("submit", function (event) {
        let valid = true;

        // Reset error messages
        usernameError.style.display = "none";
        emailError.style.display = "none";
        passwordError.style.display = "none";
        confirmPasswordError.style.display = "none";

        // Validasi Username
        if (username.value.trim() === "") {
            usernameError.style.display = "block";
            valid = false;
        }

        // Validasi Email
        if (email.value.trim() === "") {
            emailError.style.display = "block";
            valid = false;
        }

        // Validasi Password
        if (password.value.trim() === "") {
            passwordError.style.display = "block";
            valid = false;
        }

        // Validasi Konfirmasi Password
        if (confirmPassword.value.trim() === "" || confirmPassword.value !== password.value) {
            confirmPasswordError.style.display = "block";
            valid = false;
        }

        // Jika tidak valid, cegah form dari submit
        if (!valid) {
            event.preventDefault();
        }
    });
});