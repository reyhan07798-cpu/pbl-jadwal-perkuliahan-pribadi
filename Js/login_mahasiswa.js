document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("loginForm");
    const username = document.getElementById("username");
    const password = document.getElementById("password");
    const usernameError = document.getElementById("usernameError");
    const passwordError = document.getElementById("passwordError");

    form.addEventListener("submit", function (event) {
        let valid = true;
        usernameError.style.display = "none";
        passwordError.style.display = "none";

        if (username.value.trim() === "") {
            usernameError.style.display = "block";
            valid = false;
        }

        if (password.value.trim() === "") {
            passwordError.style.display = "block";
            valid = false;
        }

        if (!valid) {
            event.preventDefault();
        }
    });
});