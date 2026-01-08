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

        usernameError.style.display = "none";
        emailError.style.display = "none";
        passwordError.style.display = "none";
        confirmPasswordError.style.display = "none";

        //VALIDASI
        if (username.value.trim() === "") {
            usernameError.style.display = "block";
            valid = false;
        }
        if (email.value.trim() === "") {
            emailError.style.display = "block";
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

        if (!valid) {
            event.preventDefault();
        }
    });
});