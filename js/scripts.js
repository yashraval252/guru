// scripts.js - Handles login and registration form validations and UI interactions

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            clearErrors(loginForm);
            let valid = true;

            const email = loginForm.email.value.trim();
            const password = loginForm.password.value;

            if (!validateEmail(email)) {
                showError(loginForm.email, 'Please enter a valid email.');
                valid = false;
            }
            if (password.length === 0) {
                showError(loginForm.password, 'Please enter your password.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            clearErrors(registerForm);
            let valid = true;

            const name = registerForm.name.value.trim();
            const email = registerForm.email.value.trim();
            const password = registerForm.password.value;
            const confirmPassword = registerForm.confirm_password.value;

            if (name.length < 2 || name.length > 100) {
                showError(registerForm.name, 'Name must be between 2 and 100 characters.');
                valid = false;
            }
            if (!validateEmail(email)) {
                showError(registerForm.email, 'Please enter a valid email.');
                valid = false;
            }
            if (password.length < 6) {
                showError(registerForm.password, 'Password must be at least 6 characters.');
                valid = false;
            }
            if (password !== confirmPassword) {
                showError(registerForm.confirm_password, 'Passwords do not match.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    function clearErrors(form) {
        const errorElements = form.querySelectorAll('.input-error');
        errorElements.forEach(el => el.textContent = '');
    }

    function showError(inputElement, message) {
        const errorElement = inputElement.parentElement.querySelector('.input-error');
        if (errorElement) {
            errorElement.textContent = message;
        }
    }

    function validateEmail(email) {
        // Simple RFC 5322 email validation regex
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email.toLowerCase());
    }
});
