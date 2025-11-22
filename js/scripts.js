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

                // Set today's date again (recompute to avoid scope issues)
                if (dateInput) {
                    const t = new Date();
                    const y = t.getFullYear();
                    const m = String(t.getMonth() + 1).padStart(2, '0');
                    const d = String(t.getDate()).padStart(2, '0');
                    dateInput.value = `${y}-${m}-${d}`;
                }

                // Add success message
                showSuccessMessage('Entry added successfully!');

                // If calendar is available, add event dynamically
                try {
                    if (window.APP_CALENDAR && typeof window.APP_CALENDAR.addEvent === 'function') {
                        window.APP_CALENDAR.addEvent({
                            id: String(data.entry.id),
                            title: data.entry.title,
                            start: data.entry.date,
                            allDay: true,
                            backgroundColor: '#6366f1',
                            borderColor: '#4f46e5'
                        });
                    }
                } catch (err) {
                    console.warn('Could not add event to calendar dynamically', err);
                }

                // Prepend to recent entries list if present
                const entriesList = document.getElementById('entriesList');
                if (entriesList) {
                    const li = renderEntryItem(data.entry);
                    // Prepend inside UL
                    entriesList.insertBefore(li, entriesList.firstChild);
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

    // Render a single entry list item (used when adding dynamically)
    function renderEntryItem(entry) {
        const li = document.createElement('li');
        li.className = 'entry-item';
        li.setAttribute('data-entry-id', String(entry.id));
        li.setAttribute('data-entry-date', entry.date);

        const left = document.createElement('div');
        const title = document.createElement('p');
        title.className = 'entry-title';
        title.textContent = entry.title;
        const time = document.createElement('time');
        time.className = 'entry-date';
        time.setAttribute('datetime', entry.date);
        const dt = new Date(entry.date + 'T00:00:00');
        time.textContent = dt.toLocaleDateString(undefined, { month: 'short', day: '2-digit', year: 'numeric' });
        left.appendChild(title);
        left.appendChild(time);

        const right = document.createElement('div');
        const del = document.createElement('button');
        del.className = 'btn btn-sm btn-outline-danger delete-entry';
        del.setAttribute('data-entry-id', String(entry.id));
        del.setAttribute('aria-label', 'Delete entry');
        del.textContent = 'Delete';
        right.appendChild(del);

        li.appendChild(left);
        li.appendChild(right);

        return li;
    }

    // Global event delegation for delete buttons (works even if list is added later)
    document.addEventListener('click', async(ev) => {
        const btn = ev.target.closest && ev.target.closest('.delete-entry');
        if (!btn) return;
        const id = btn.getAttribute('data-entry-id');
        if (!id) return;

        if (!confirm('Delete this entry?')) return;

        try {
            const res = await fetch('api/entries.php?id=' + encodeURIComponent(id), {
                method: 'DELETE'
            });
            const json = await res.json();
            if (!res.ok) {
                throw new Error(json.error || 'Failed to delete');
            }

            // Remove list item
            const li = document.querySelector('li[data-entry-id="' + id + '"]');
            if (li) li.remove();

            // Remove from calendar if present
            try {
                if (window.APP_CALENDAR && typeof window.APP_CALENDAR.getEventById === 'function') {
                    const evObj = window.APP_CALENDAR.getEventById(String(id));
                    if (evObj) evObj.remove();
                }
            } catch (err) {
                console.warn('Could not remove event from calendar', err);
            }

        } catch (err) {
            console.error('Delete failed', err);
            showErrorMessage('Failed to delete entry.');
        }
    });

    // Date filter handling
    const filterDateInput = document.getElementById('filterDate');
    const clearFilterBtn = document.getElementById('clearFilter');
    if (filterDateInput) {
        filterDateInput.addEventListener('change', () => {
            const val = filterDateInput.value;
            filterEntriesByDate(val);
        });
    }
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (filterDateInput) {
                filterDateInput.value = '';
            }
            filterEntriesByDate('');
        });
    }

    function filterEntriesByDate(dateStr) {
        const entriesListEl = document.getElementById('entriesList');
        if (!entriesListEl) return;
        const items = Array.from(entriesListEl.querySelectorAll('li.entry-item'));
        if (!dateStr) {
            items.forEach(i => i.style.display = 'flex');
            return;
        }
        items.forEach(i => {
            const d = i.getAttribute('data-entry-date');
            if (d === dateStr) {
                i.style.display = 'flex';
            } else {
                i.style.display = 'none';
            }
        });
    }
});