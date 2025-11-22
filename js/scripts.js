// scripts.js - Handles form validations and UI interactions

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const entryForm = document.getElementById('entryForm');

    // Login form validation
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

    // Registration form validation
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

    // Entry form handling
    if (entryForm) {
        // Set today's date as default
        const dateInput = document.getElementById('date');
        if (dateInput) {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            dateInput.value = `${year}-${month}-${day}`;
        }

        entryForm.addEventListener('submit', async(e) => {
            e.preventDefault();
            clearErrors(entryForm);
            let valid = true;

            const title = entryForm.title.value.trim();
            const date = entryForm.date.value;

            if (title === '') {
                showError(entryForm.title, 'Title is required.');
                valid = false;
            } else if (title.length > 255) {
                showError(entryForm.title, 'Title must be 255 characters or fewer.');
                valid = false;
            }

            if (date === '') {
                showError(entryForm.date, 'Date is required.');
                valid = false;
            }

            if (!valid) {
                return;
            }

            try {
                const response = await fetch('api/entries.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        title: title,
                        date: date
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.error && Array.isArray(data.error)) {
                        data.error.forEach(error => console.error(error));
                    }
                    throw new Error(data.error || 'Failed to add entry');
                }

                // Reset form
                entryForm.reset();

                // Set today's date again
                dateInput.value = `${year}-${month}-${day}`;

                // Add success message
                showSuccessMessage('Entry added successfully! Refresh to see it in the calendar.');

                // Refresh calendar if available
                if (window.location.pathname.includes('dashboard')) {
                    // Reload the page to show new entry
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }

            } catch (error) {
                console.error('Error:', error);
                showErrorMessage('Failed to add entry. Please try again.');
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
            inputElement.classList.add('is-invalid');
        }
    }

    function validateEmail(email) {
        // Simple RFC 5322 email validation regex
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email.toLowerCase());
    }

    function showSuccessMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <strong>Success!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        const entryCard = entryForm.closest('.card');
        if (entryCard) {
            entryCard.parentElement.insertBefore(alertDiv, entryCard);
        }

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            <strong>Error!</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        const entryCard = entryForm.closest('.card');
        if (entryCard) {
            entryCard.parentElement.insertBefore(alertDiv, entryCard);
        }

        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
});