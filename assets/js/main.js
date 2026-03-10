/**
 * ============================================================
 * Main JavaScript
 * St. Andrew's College Library Management System
 * ============================================================
 */

document.addEventListener('DOMContentLoaded', function() {

    // ---- Sidebar Toggle (Mobile) ----
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    }

    // ---- AJAX Live Search ----
    const searchInput = document.getElementById('liveSearchInput');
    const searchResults = document.getElementById('searchResults');

    if (searchInput && searchResults) {
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            // Clear previous timeout to debounce requests
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                searchResults.classList.remove('show');
                searchResults.innerHTML = '';
                return;
            }

            // Debounce: wait 300ms after user stops typing
            searchTimeout = setTimeout(function() {
                fetchSearchResults(query);
            }, 300);
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('show');
            }
        });

        /**
         * Fetch search results via AJAX
         * @param {string} query - Search term
         */
        function fetchSearchResults(query) {
            // Determine base URL from the page
            const baseUrl = document.querySelector('meta[name="base-url"]');
            const base = baseUrl ? baseUrl.content : '/library-system/';

            fetch(base + 'search/search_books.php?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        searchResults.innerHTML = '<div class="search-result-item"><span class="text-muted">No books found</span></div>';
                    } else {
                        searchResults.innerHTML = data.map(book => `
                            <div class="search-result-item" onclick="window.location.href='${base}${book.link}'">
                                <div class="title">${escapeHtml(book.title)}</div>
                                <div class="meta">${escapeHtml(book.author)} | ${escapeHtml(book.category)} | Available: ${book.available}</div>
                            </div>
                        `).join('');
                    }
                    searchResults.classList.add('show');
                })
                .catch(error => {
                    console.error('Search error:', error);
                    searchResults.innerHTML = '<div class="search-result-item text-danger">Search failed</div>';
                    searchResults.classList.add('show');
                });
        }
    }

    // ---- File Upload Preview ----
    const fileInput = document.getElementById('coverImage');
    const uploadPreview = document.getElementById('uploadPreview');

    if (fileInput && uploadPreview) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, JPEG, and PNG files are allowed.');
                    this.value = '';
                    uploadPreview.innerHTML = '';
                    return;
                }
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB.');
                    this.value = '';
                    uploadPreview.innerHTML = '';
                    return;
                }
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    uploadPreview.innerHTML = `<img src="${e.target.result}" alt="Cover Preview">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ---- Confirm Delete ----
    window.confirmDelete = function(url, itemName) {
        if (confirm('Are you sure you want to delete ' + itemName + '? This action cannot be undone.')) {
            window.location.href = url;
        }
    };

    // ---- Auto-hide alerts after 5 seconds ----
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // ---- Form Validation ----
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showFormError('Please enter a valid email address.');
                return;
            }

            // Password length check
            if (password.length < 8) {
                e.preventDefault();
                showFormError('Password must be at least 8 characters.');
                return;
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            // Name validation
            if (name.length < 2) {
                e.preventDefault();
                showFormError('Please enter your full name.');
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showFormError('Please enter a valid email address.');
                return;
            }

            // Password strength validation
            if (password.length < 8) {
                e.preventDefault();
                showFormError('Password must be at least 8 characters.');
                return;
            }

            if (!/[A-Z]/.test(password)) {
                e.preventDefault();
                showFormError('Password must contain at least one uppercase letter.');
                return;
            }

            if (!/[0-9]/.test(password)) {
                e.preventDefault();
                showFormError('Password must contain at least one number.');
                return;
            }

            // Confirm password
            if (password !== confirmPassword) {
                e.preventDefault();
                showFormError('Passwords do not match.');
                return;
            }
        });
    }

    /**
     * Display form validation error
     * @param {string} message - Error message to display
     */
    function showFormError(message) {
        // Remove existing error alerts
        const existing = document.querySelector('.alert-danger.js-error');
        if (existing) existing.remove();

        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger js-error';
        alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + escapeHtml(message);

        const form = document.querySelector('.auth-form') || document.querySelector('form');
        if (form) {
            form.insertBefore(alertDiv, form.firstChild);
        }

        // Auto-remove after 4 seconds
        setTimeout(function() {
            alertDiv.style.opacity = '0';
            setTimeout(() => alertDiv.remove(), 300);
        }, 4000);
    }

    /**
     * Escape HTML to prevent XSS in dynamic content
     * @param {string} text - Raw text to escape
     * @returns {string} Escaped HTML string
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Make escapeHtml available globally
    window.escapeHtml = escapeHtml;

    // ---- Select All Checkbox ----
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
    }
});
