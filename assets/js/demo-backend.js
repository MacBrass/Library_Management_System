/**
 * ============================================================
 * Demo Backend - localStorage-based Mock Database
 * St. Andrew's College Library Management System
 * ============================================================
 * This file simulates the MySQL database using localStorage
 * so the app can run on VS Code Live Server without XAMPP.
 *
 * All data is stored in the browser's localStorage.
 * On first load, sample data is seeded automatically.
 */

const DemoBackend = (function () {
    'use strict';

    // ---- Storage Keys ----
    const KEYS = {
        users: 'lib_users',
        books: 'lib_books',
        requests: 'lib_requests',
        borrowHistory: 'lib_borrow_history',
        session: 'lib_session',
        seeded: 'lib_data_seeded',
        idCounters: 'lib_id_counters',
        fineSettings: 'lib_fine_settings',
        receipts: 'lib_receipts'
    };

    // ---- Helpers ----
    function getStore(key) {
        try {
            return JSON.parse(localStorage.getItem(key)) || [];
        } catch { return []; }
    }

    function setStore(key, data) {
        localStorage.setItem(key, JSON.stringify(data));
    }

    function getCounters() {
        try {
            return JSON.parse(localStorage.getItem(KEYS.idCounters)) || { users: 10, books: 20, requests: 10, borrowHistory: 10 };
        } catch { return { users: 10, books: 20, requests: 10, borrowHistory: 10 }; }
    }

    function nextId(table) {
        const counters = getCounters();
        counters[table] = (counters[table] || 0) + 1;
        localStorage.setItem(KEYS.idCounters, JSON.stringify(counters));
        return counters[table];
    }

    function now() {
        return new Date().toISOString().slice(0, 19).replace('T', ' ');
    }

    function dateOnly(d) {
        if (!d) return null;
        return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    // ---- Seed Sample Data ----
    function seedData() {
        if (localStorage.getItem(KEYS.seeded) === 'true') return;

        const users = [
            { id: 1, name: 'Admin User', email: 'admin@andrews.edu', password: 'Admin123', role: 'admin', department: 'Administration', status: 'active', created_at: '2024-01-15 10:00:00' },
            { id: 2, name: 'Prof. Sharma', email: 'prof@andrews.edu', password: 'Prof1234', role: 'professor', department: 'Computer Science', status: 'active', created_at: '2024-01-20 10:00:00' },
            { id: 3, name: 'Rahul Verma', email: 'student@andrews.edu', password: 'Student123', role: 'student', department: 'Computer Science', status: 'active', created_at: '2024-02-01 10:00:00' }
        ];

        const books = [
            { id: 1, title: 'Introduction to Algorithms', author: 'Thomas H. Cormen', isbn: '9780262033848', category: 'Computer Science', publisher: 'MIT Press', year: 2009, quantity: 5, available: 5, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 2, title: 'Database System Concepts', author: 'Abraham Silberschatz', isbn: '9780078022159', category: 'Computer Science', publisher: 'McGraw Hill', year: 2019, quantity: 3, available: 3, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 3, title: 'Operating System Concepts', author: 'Abraham Silberschatz', isbn: '9781119800361', category: 'Computer Science', publisher: 'Wiley', year: 2021, quantity: 4, available: 4, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 4, title: 'Clean Code', author: 'Robert C. Martin', isbn: '9780132350884', category: 'Software Engineering', publisher: 'Pearson', year: 2008, quantity: 3, available: 3, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 5, title: 'The Pragmatic Programmer', author: 'Andrew Hunt', isbn: '9780135957059', category: 'Software Engineering', publisher: 'Addison-Wesley', year: 2019, quantity: 2, available: 2, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 6, title: 'Data Structures Using C', author: 'Reema Thareja', isbn: '9780198099307', category: 'Computer Science', publisher: 'Oxford', year: 2014, quantity: 6, available: 6, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 7, title: 'Computer Networks', author: 'Andrew Tanenbaum', isbn: '9780132126953', category: 'Networking', publisher: 'Pearson', year: 2010, quantity: 3, available: 3, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 8, title: 'Artificial Intelligence: A Modern Approach', author: 'Stuart Russell', isbn: '9780134610993', category: 'Artificial Intelligence', publisher: 'Pearson', year: 2020, quantity: 4, available: 4, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 9, title: 'Design Patterns', author: 'Erich Gamma', isbn: '9780201633610', category: 'Software Engineering', publisher: 'Addison-Wesley', year: 1994, quantity: 2, available: 2, cover_image: '', created_at: '2024-01-15 10:00:00' },
            { id: 10, title: 'Machine Learning', author: 'Tom Mitchell', isbn: '9780070428072', category: 'Artificial Intelligence', publisher: 'McGraw Hill', year: 1997, quantity: 3, available: 3, cover_image: '', created_at: '2024-01-15 10:00:00' }
        ];

        setStore(KEYS.users, users);
        setStore(KEYS.books, books);
        setStore(KEYS.requests, []);
        setStore(KEYS.borrowHistory, []);
        setStore(KEYS.receipts, []);
        localStorage.setItem(KEYS.idCounters, JSON.stringify({ users: 3, books: 10, requests: 0, borrowHistory: 0, receipts: 0 }));

        // Default fine settings
        setStore(KEYS.fineSettings, {
            fineMode: 'per_day',
            borrowPeriod: 14,
            studentFinePerDay: 5,
            professorFinePerDay: 3,
            studentFixedFine: 50,
            professorFixedFine: 30,
            noReturnDays: 60,
            noReturnFine: 500,
            currency: '₹'
        });

        localStorage.setItem(KEYS.seeded, 'true');
    }

    // ---- Auth Functions ----
    function login(email, password) {
        const users = getStore(KEYS.users);
        const user = users.find(u => u.email === email && u.password === password);
        if (!user) return { success: false, error: 'Invalid email or password.' };
        if (user.status !== 'active') return { success: false, error: 'Your account has been deactivated. Contact the administrator.' };
        setStore(KEYS.session, { user_id: user.id, name: user.name, email: user.email, role: user.role });
        return { success: true, role: user.role };
    }

    function logout() {
        localStorage.removeItem(KEYS.session);
    }

    function getSession() {
        try {
            return JSON.parse(localStorage.getItem(KEYS.session));
        } catch { return null; }
    }

    function isLoggedIn() {
        return getSession() !== null;
    }

    function requireAuth(requiredRole) {
        const session = getSession();
        if (!session) {
            window.location.href = getBasePath() + 'auth/login.html?error=Please login to continue';
            return null;
        }
        if (requiredRole) {
            const allowed = Array.isArray(requiredRole) ? requiredRole : [requiredRole];
            if (!allowed.includes(session.role)) {
                window.location.href = getBasePath() + session.role + '/dashboard.html';
                return null;
            }
        }
        return session;
    }

    // ---- User Functions ----
    function register(name, email, password, role, department) {
        const users = getStore(KEYS.users);
        if (users.find(u => u.email === email)) {
            return { success: false, error: 'An account with this email already exists.' };
        }
        const user = {
            id: nextId('users'),
            name, email, password,
            role: (['student', 'professor'].includes(role)) ? role : 'student',
            department: department || '',
            status: 'active',
            created_at: now()
        };
        users.push(user);
        setStore(KEYS.users, users);
        return { success: true };
    }

    function getUsers(filter) {
        let users = getStore(KEYS.users);
        if (filter && filter.role && filter.role !== 'all') {
            users = users.filter(u => u.role === filter.role);
        }
        if (filter && filter.search) {
            const s = filter.search.toLowerCase();
            users = users.filter(u =>
                u.name.toLowerCase().includes(s) ||
                u.email.toLowerCase().includes(s) ||
                (u.department || '').toLowerCase().includes(s)
            );
        }
        return users.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }

    function toggleUserStatus(userId) {
        const users = getStore(KEYS.users);
        const user = users.find(u => u.id === userId);
        if (user) {
            user.status = user.status === 'active' ? 'inactive' : 'active';
            setStore(KEYS.users, users);
            return { success: true, newStatus: user.status };
        }
        return { success: false };
    }

    // ---- Book Functions ----
    function getBooks(filter) {
        let books = getStore(KEYS.books);
        if (filter && filter.search) {
            const s = filter.search.toLowerCase();
            books = books.filter(b =>
                b.title.toLowerCase().includes(s) ||
                b.author.toLowerCase().includes(s) ||
                (b.category || '').toLowerCase().includes(s) ||
                (b.isbn || '').toLowerCase().includes(s)
            );
        }
        if (filter && filter.availableOnly) {
            books = books.filter(b => b.available > 0);
        }
        return books.sort((a, b) => {
            if (filter && filter.sortBy === 'title') return a.title.localeCompare(b.title);
            return new Date(b.created_at) - new Date(a.created_at);
        });
    }

    function getBookById(id) {
        return getStore(KEYS.books).find(b => b.id === id) || null;
    }

    function addBook(bookData) {
        const books = getStore(KEYS.books);
        const book = {
            id: nextId('books'),
            title: bookData.title,
            author: bookData.author,
            isbn: bookData.isbn || '',
            category: bookData.category || '',
            publisher: bookData.publisher || '',
            year: bookData.year || 0,
            quantity: bookData.quantity || 1,
            available: bookData.quantity || 1,
            cover_image: bookData.cover_image || '',
            created_at: now()
        };
        books.push(book);
        setStore(KEYS.books, books);
        return { success: true, id: book.id, title: book.title };
    }

    function updateBook(id, bookData) {
        const books = getStore(KEYS.books);
        const book = books.find(b => b.id === id);
        if (!book) return { success: false, error: 'Book not found.' };

        const qtyDiff = (bookData.quantity || book.quantity) - book.quantity;

        book.title = bookData.title || book.title;
        book.author = bookData.author || book.author;
        book.isbn = bookData.isbn !== undefined ? bookData.isbn : book.isbn;
        book.category = bookData.category !== undefined ? bookData.category : book.category;
        book.publisher = bookData.publisher !== undefined ? bookData.publisher : book.publisher;
        book.year = bookData.year !== undefined ? bookData.year : book.year;
        book.quantity = bookData.quantity || book.quantity;
        book.available = Math.max(0, book.available + qtyDiff);
        if (bookData.cover_image) book.cover_image = bookData.cover_image;

        setStore(KEYS.books, books);
        return { success: true };
    }

    function deleteBook(id) {
        const requests = getStore(KEYS.requests);
        const hasActive = requests.some(r => r.book_id === id && ['requested', 'approved', 'issued'].includes(r.status));
        if (hasActive) return { success: false, error: 'Cannot delete a book with active requests or issued copies.' };

        let books = getStore(KEYS.books);
        books = books.filter(b => b.id !== id);
        setStore(KEYS.books, books);
        return { success: true };
    }

    function searchBooks(query) {
        if (!query || query.length < 2) return [];
        const q = query.toLowerCase();
        return getStore(KEYS.books)
            .filter(b =>
                b.title.toLowerCase().includes(q) ||
                b.author.toLowerCase().includes(q) ||
                (b.category || '').toLowerCase().includes(q) ||
                (b.isbn || '').toLowerCase().includes(q)
            )
            .slice(0, 10);
    }

    // ---- Request Functions ----
    function createRequest(userId, bookId) {
        const books = getStore(KEYS.books);
        const book = books.find(b => b.id === bookId);
        if (!book) return { success: false, error: 'Book not found.' };
        if (book.available <= 0) return { success: false, error: 'No copies of "' + book.title + '" are currently available.' };

        const requests = getStore(KEYS.requests);
        const duplicate = requests.find(r => r.user_id === userId && r.book_id === bookId && ['requested', 'approved', 'issued'].includes(r.status));
        if (duplicate) return { success: false, error: 'You already have an active request for this book.' };

        // Check borrow limit
        const session = getSession();
        const role = session ? session.role : 'student';
        const limit = role === 'professor' ? 5 : 3;
        const activeCount = requests.filter(r => r.user_id === userId && ['approved', 'issued'].includes(r.status)).length;
        if (activeCount >= limit) return { success: false, error: 'You have reached your borrow limit of ' + limit + ' books.' };

        const request = {
            id: nextId('requests'),
            user_id: userId,
            book_id: bookId,
            status: 'requested',
            request_date: now(),
            approval_date: null,
            issued_date: null,
            return_date: null
        };
        requests.push(request);
        setStore(KEYS.requests, requests);
        return { success: true, title: book.title };
    }

    function createBulkRequest(bookId, studentIds) {
        const books = getStore(KEYS.books);
        const book = books.find(b => b.id === bookId);
        if (!book) return { success: false, error: 'Selected book not found.' };
        if (book.available < studentIds.length) return { success: false, error: 'Not enough copies available. Available: ' + book.available + ', Requested: ' + studentIds.length };

        let created = 0, skipped = 0;
        const requests = getStore(KEYS.requests);

        studentIds.forEach(sid => {
            const dup = requests.find(r => r.user_id === sid && r.book_id === bookId && ['requested', 'approved', 'issued'].includes(r.status));
            if (dup) { skipped++; return; }
            const activeCount = requests.filter(r => r.user_id === sid && ['approved', 'issued'].includes(r.status)).length;
            if (activeCount >= 3) { skipped++; return; }

            requests.push({
                id: nextId('requests'),
                user_id: sid,
                book_id: bookId,
                status: 'requested',
                request_date: now(),
                approval_date: null,
                issued_date: null,
                return_date: null
            });
            created++;
        });

        setStore(KEYS.requests, requests);
        let msg = 'Bulk request completed: ' + created + ' requests created';
        if (skipped > 0) msg += ', ' + skipped + ' skipped (duplicate or limit reached)';
        msg += ' for "' + book.title + '".';
        return { success: true, message: msg };
    }

    function getRequests(filter) {
        let requests = getStore(KEYS.requests);
        const users = getStore(KEYS.users);
        const books = getStore(KEYS.books);

        if (filter && filter.userId) {
            requests = requests.filter(r => r.user_id === filter.userId);
        }
        if (filter && filter.status && filter.status !== 'all') {
            requests = requests.filter(r => r.status === filter.status);
        }
        if (filter && filter.activeOnly) {
            requests = requests.filter(r => ['requested', 'approved', 'issued'].includes(r.status));
        }

        return requests
            .map(r => {
                const user = users.find(u => u.id === r.user_id) || { name: 'Unknown', email: '', role: 'student' };
                const book = books.find(b => b.id === r.book_id) || { title: 'Unknown', author: '' };
                return { ...r, user_name: user.name, user_email: user.email, user_role: user.role, book_title: book.title, book_author: book.author };
            })
            .sort((a, b) => new Date(b.request_date) - new Date(a.request_date));
    }

    function approveRequest(reqId) {
        const requests = getStore(KEYS.requests);
        const req = requests.find(r => r.id === reqId);
        if (!req || req.status !== 'requested') return { success: false, error: 'Invalid request.' };

        const books = getStore(KEYS.books);
        const book = books.find(b => b.id === req.book_id);
        if (!book || book.available <= 0) return { success: false, error: 'No copies available.' };

        req.status = 'approved';
        req.approval_date = now();
        book.available--;

        setStore(KEYS.requests, requests);
        setStore(KEYS.books, books);
        return { success: true };
    }

    function rejectRequest(reqId) {
        const requests = getStore(KEYS.requests);
        const req = requests.find(r => r.id === reqId);
        if (!req || req.status !== 'requested') return { success: false };
        req.status = 'rejected';
        setStore(KEYS.requests, requests);
        return { success: true };
    }

    function issueRequest(reqId) {
        const requests = getStore(KEYS.requests);
        const req = requests.find(r => r.id === reqId);
        if (!req || req.status !== 'approved') return { success: false };

        req.status = 'issued';
        req.issued_date = now();
        setStore(KEYS.requests, requests);

        // Create borrow history
        const history = getStore(KEYS.borrowHistory);
        history.push({
            id: nextId('borrowHistory'),
            user_id: req.user_id,
            book_id: req.book_id,
            issue_date: req.issued_date,
            return_date: null,
            fine: 0
        });
        setStore(KEYS.borrowHistory, history);

        // Auto-generate issue receipt
        const receiptData = generateReceipt('issue', { request: req });

        return { success: true, receiptId: receiptData.id };
    }

    function returnRequest(reqId) {
        const requests = getStore(KEYS.requests);
        const req = requests.find(r => r.id === reqId);
        if (!req || req.status !== 'issued') return { success: false };

        const returnDate = now();
        req.status = 'returned';
        req.return_date = returnDate;
        setStore(KEYS.requests, requests);

        // Update book availability
        const books = getStore(KEYS.books);
        const book = books.find(b => b.id === req.book_id);
        if (book) {
            book.available++;
            setStore(KEYS.books, books);
        }

        // Calculate fine using admin-configured settings
        let fine = 0;
        let lateDays = 0;
        const settings = getFineSettings();
        const users = getStore(KEYS.users);
        const userObj = users.find(u => u.id === req.user_id);
        const userRole = userObj ? userObj.role : 'student';

        if (req.issued_date) {
            const issueMs = new Date(req.issued_date).getTime();
            const returnMs = new Date(returnDate).getTime();
            const days = Math.floor((returnMs - issueMs) / (1000 * 60 * 60 * 24));
            lateDays = Math.max(0, days - settings.borrowPeriod);

            if (days >= settings.noReturnDays) {
                // No-return penalty (max fine)
                fine = settings.noReturnFine;
            } else if (lateDays > 0) {
                if (settings.fineMode === 'fixed') {
                    fine = userRole === 'professor' ? settings.professorFixedFine : settings.studentFixedFine;
                } else {
                    const ratePerDay = userRole === 'professor' ? settings.professorFinePerDay : settings.studentFinePerDay;
                    fine = lateDays * ratePerDay;
                }
            }
        }

        // Update borrow history
        const history = getStore(KEYS.borrowHistory);
        const histEntry = history.find(h => h.user_id === req.user_id && h.book_id === req.book_id && !h.return_date);
        if (histEntry) {
            histEntry.return_date = returnDate;
            histEntry.fine = fine;
            setStore(KEYS.borrowHistory, history);
        }

        // Auto-generate return receipt
        const receiptData = generateReceipt('return', {
            request: req,
            fine: fine,
            lateDays: lateDays,
            settings: settings
        });

        return { success: true, fine: fine, lateDays: lateDays, receiptId: receiptData.id };
    }

    // ---- Stats Functions ----
    function getAdminStats() {
        const books = getStore(KEYS.books);
        const requests = getStore(KEYS.requests);
        const users = getStore(KEYS.users);
        return {
            totalBooks: books.length,
            totalCopies: books.reduce((s, b) => s + b.quantity, 0),
            totalBorrowed: requests.filter(r => r.status === 'issued').length,
            pendingRequests: requests.filter(r => r.status === 'requested').length,
            totalStudents: users.filter(u => u.role === 'student').length,
            totalProfessors: users.filter(u => u.role === 'professor').length
        };
    }

    function getUserStats(userId) {
        const requests = getStore(KEYS.requests).filter(r => r.user_id === userId);
        return {
            issued: requests.filter(r => r.status === 'issued').length,
            pending: requests.filter(r => r.status === 'requested').length,
            approved: requests.filter(r => r.status === 'approved').length,
            returned: requests.filter(r => r.status === 'returned').length
        };
    }

    function getBorrowHistory(filter) {
        let history = getStore(KEYS.borrowHistory);
        const users = getStore(KEYS.users);
        const books = getStore(KEYS.books);

        if (filter && filter.userId) {
            history = history.filter(h => h.user_id === filter.userId);
        }

        return history
            .map(h => {
                const user = users.find(u => u.id === h.user_id) || { name: 'Unknown' };
                const book = books.find(b => b.id === h.book_id) || { title: 'Unknown' };
                return { ...h, user_name: user.name, book_title: book.title };
            })
            .sort((a, b) => new Date(b.issue_date) - new Date(a.issue_date))
            .slice(0, filter && filter.limit ? filter.limit : 100);
    }

    function isBorrowLimitReached(userId, role) {
        const limit = role === 'professor' ? 5 : 3;
        const requests = getStore(KEYS.requests);
        const active = requests.filter(r => r.user_id === userId && ['approved', 'issued'].includes(r.status)).length;
        return active >= limit;
    }

    // ---- Fine Settings Functions ----
    function getFineSettings() {
        try {
            const s = JSON.parse(localStorage.getItem(KEYS.fineSettings));
            return s || {
                fineMode: 'per_day', borrowPeriod: 14,
                studentFinePerDay: 5, professorFinePerDay: 3,
                studentFixedFine: 50, professorFixedFine: 30,
                noReturnDays: 60, noReturnFine: 500, currency: '₹'
            };
        } catch {
            return {
                fineMode: 'per_day', borrowPeriod: 14,
                studentFinePerDay: 5, professorFinePerDay: 3,
                studentFixedFine: 50, professorFixedFine: 30,
                noReturnDays: 60, noReturnFine: 500, currency: '₹'
            };
        }
    }

    function saveFineSettings(settings) {
        setStore(KEYS.fineSettings, settings);
        return { success: true };
    }

    function calculateCurrentFine(issuedDate, userRole) {
        const settings = getFineSettings();
        const issueMs = new Date(issuedDate).getTime();
        const nowMs = Date.now();
        const days = Math.floor((nowMs - issueMs) / (1000 * 60 * 60 * 24));
        const lateDays = Math.max(0, days - settings.borrowPeriod);
        const dueDate = new Date(issueMs + settings.borrowPeriod * 86400000);

        let fine = 0;
        if (days >= settings.noReturnDays) {
            fine = settings.noReturnFine;
        } else if (lateDays > 0) {
            if (settings.fineMode === 'fixed') {
                fine = userRole === 'professor' ? settings.professorFixedFine : settings.studentFixedFine;
            } else {
                const rate = userRole === 'professor' ? settings.professorFinePerDay : settings.studentFinePerDay;
                fine = lateDays * rate;
            }
        }

        return {
            totalDays: days,
            lateDays: lateDays,
            fine: fine,
            isOverdue: lateDays > 0,
            isNoReturn: days >= settings.noReturnDays,
            dueDate: dueDate.toISOString().slice(0, 19).replace('T', ' '),
            currency: settings.currency
        };
    }

    function getOverdueBooks() {
        const requests = getStore(KEYS.requests).filter(r => r.status === 'issued');
        const users = getStore(KEYS.users);
        const books = getStore(KEYS.books);
        const results = [];

        requests.forEach(r => {
            const user = users.find(u => u.id === r.user_id) || { name: 'Unknown', role: 'student' };
            const book = books.find(b => b.id === r.book_id) || { title: 'Unknown' };
            const fineInfo = calculateCurrentFine(r.issued_date, user.role);
            if (fineInfo.isOverdue) {
                results.push({
                    ...r,
                    user_name: user.name,
                    user_email: user.email || '',
                    user_role: user.role,
                    book_title: book.title,
                    book_author: book.author || '',
                    ...fineInfo
                });
            }
        });

        return results.sort((a, b) => b.lateDays - a.lateDays);
    }

    // ---- Receipt Functions ----
    function generateReceipt(type, data) {
        const users = getStore(KEYS.users);
        const books = getStore(KEYS.books);
        const settings = getFineSettings();
        const req = data.request;
        const user = users.find(u => u.id === req.user_id) || { name: 'Unknown', email: '', role: 'student', department: '' };
        const book = books.find(b => b.id === req.book_id) || { title: 'Unknown', author: '', isbn: '' };

        const dueDate = req.issued_date
            ? new Date(new Date(req.issued_date).getTime() + settings.borrowPeriod * 86400000)
            : null;

        const receipt = {
            id: nextId('receipts'),
            receipt_number: 'SACL-' + new Date().getFullYear() + '-' + String(nextId('receipts')).padStart(5, '0'),
            type: type,
            generated_at: now(),
            // User info
            user_name: user.name,
            user_email: user.email,
            user_role: user.role,
            user_department: user.department || '',
            // Book info
            book_title: book.title,
            book_author: book.author,
            book_isbn: book.isbn || '',
            // Dates
            issue_date: req.issued_date || null,
            due_date: dueDate ? dueDate.toISOString().slice(0, 19).replace('T', ' ') : null,
            return_date: req.return_date || null,
            // Fine info
            fine: data.fine || 0,
            late_days: data.lateDays || 0,
            fine_mode: settings.fineMode,
            currency: settings.currency,
            // Request reference
            request_id: req.id
        };

        const receipts = getStore(KEYS.receipts);
        receipts.push(receipt);
        setStore(KEYS.receipts, receipts);
        return receipt;
    }

    function getReceipts(filter) {
        let receipts = getStore(KEYS.receipts);
        if (filter && filter.userId) {
            // Need to match via request
            const requests = getStore(KEYS.requests);
            const userReqIds = requests.filter(r => r.user_id === filter.userId).map(r => r.id);
            receipts = receipts.filter(r => userReqIds.includes(r.request_id));
        }
        if (filter && filter.type) {
            receipts = receipts.filter(r => r.type === filter.type);
        }
        return receipts.sort((a, b) => new Date(b.generated_at) - new Date(a.generated_at));
    }

    function getReceiptById(id) {
        return getStore(KEYS.receipts).find(r => r.id === id) || null;
    }

    function generateReceiptHTML(receipt) {
        const s = getFineSettings();
        const c = s.currency;
        const isReturn = receipt.type === 'return';
        const hasFine = receipt.fine > 0;

        return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt ${receipt.receipt_number}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; padding: 20px; color: #1e293b; }
        .receipt { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden; }
        .receipt-header { background: linear-gradient(135deg, #1e3a5f, #2d5a8e); color: #fff; padding: 28px 32px; text-align: center; }
        .receipt-header .logo { font-size: 2rem; margin-bottom: 8px; }
        .receipt-header h1 { font-size: 1.2rem; font-weight: 600; letter-spacing: 0.5px; }
        .receipt-header p { font-size: 0.8rem; opacity: 0.8; margin-top: 4px; }
        .receipt-type { text-align: center; padding: 16px; }
        .receipt-type span { display: inline-block; padding: 6px 20px; border-radius: 20px; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
        .type-issue { background: #dbeafe; color: #1d4ed8; }
        .type-return { background: #dcfce7; color: #16a34a; }
        .type-fine { background: #fee2e2; color: #dc2626; }
        .receipt-body { padding: 0 32px 24px; }
        .receipt-number { text-align: center; font-size: 0.8rem; color: #64748b; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 2px dashed #e2e8f0; }
        .receipt-number strong { color: #1e293b; font-size: 0.9rem; }
        .info-section { margin-bottom: 20px; }
        .info-section h3 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 10px; font-weight: 600; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .info-item { padding: 8px 12px; background: #f8fafc; border-radius: 8px; }
        .info-item .label { font-size: 0.7rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-item .value { font-size: 0.9rem; font-weight: 500; margin-top: 2px; }
        .info-item.full { grid-column: 1 / -1; }
        .fine-box { background: ${hasFine ? 'linear-gradient(135deg, #fef2f2, #fee2e2)' : 'linear-gradient(135deg, #f0fdf4, #dcfce7)'}; border: 1px solid ${hasFine ? '#fca5a5' : '#86efac'}; border-radius: 12px; padding: 20px; text-align: center; margin: 20px 0; }
        .fine-box .amount { font-size: 2rem; font-weight: 700; color: ${hasFine ? '#dc2626' : '#16a34a'}; }
        .fine-box .fine-label { font-size: 0.8rem; color: ${hasFine ? '#991b1b' : '#166534'}; margin-top: 4px; }
        .fine-details { font-size: 0.75rem; color: #64748b; margin-top: 8px; }
        .receipt-footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center; }
        .receipt-footer p { font-size: 0.75rem; color: #94a3b8; line-height: 1.6; }
        .print-btn { display: inline-block; padding: 10px 28px; background: linear-gradient(135deg, #1e3a5f, #2d5a8e); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 500; margin: 16px 0; }
        .print-btn:hover { opacity: 0.9; }
        .download-btn { display: inline-block; padding: 10px 28px; background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 500; margin: 16px 4px; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt { box-shadow: none; }
            .print-btn, .download-btn, .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <div class="logo">📚</div>
            <h1>St. Andrew's College Library</h1>
            <p>Bandra, Mumbai</p>
        </div>

        <div class="receipt-type">
            <span class="type-${receipt.type}">
                ${receipt.type === 'issue' ? '📖 Book Issue Receipt' : receipt.type === 'return' ? '✅ Book Return Receipt' : '💰 Fine Receipt'}
            </span>
        </div>

        <div class="receipt-body">
            <div class="receipt-number">
                Receipt No: <strong>${receipt.receipt_number}</strong><br>
                Date: ${new Date(receipt.generated_at).toLocaleDateString('en-IN', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
            </div>

            <div class="info-section">
                <h3>👤 Borrower Details</h3>
                <div class="info-grid">
                    <div class="info-item"><div class="label">Name</div><div class="value">${receipt.user_name}</div></div>
                    <div class="info-item"><div class="label">Role</div><div class="value">${receipt.user_role.charAt(0).toUpperCase() + receipt.user_role.slice(1)}</div></div>
                    <div class="info-item"><div class="label">Email</div><div class="value">${receipt.user_email}</div></div>
                    <div class="info-item"><div class="label">Department</div><div class="value">${receipt.user_department || 'N/A'}</div></div>
                </div>
            </div>

            <div class="info-section">
                <h3>📚 Book Details</h3>
                <div class="info-grid">
                    <div class="info-item full"><div class="label">Title</div><div class="value">${receipt.book_title}</div></div>
                    <div class="info-item"><div class="label">Author</div><div class="value">${receipt.book_author}</div></div>
                    <div class="info-item"><div class="label">ISBN</div><div class="value">${receipt.book_isbn || 'N/A'}</div></div>
                </div>
            </div>

            <div class="info-section">
                <h3>📅 Dates</h3>
                <div class="info-grid">
                    <div class="info-item"><div class="label">Issue Date</div><div class="value">${receipt.issue_date ? new Date(receipt.issue_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A'}</div></div>
                    <div class="info-item"><div class="label">Due Date</div><div class="value">${receipt.due_date ? new Date(receipt.due_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) : 'N/A'}</div></div>
                    ${isReturn ? '<div class="info-item"><div class="label">Return Date</div><div class="value">' + new Date(receipt.return_date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }) + '</div></div>' : ''}
                    ${isReturn ? '<div class="info-item"><div class="label">Late By</div><div class="value">' + (receipt.late_days > 0 ? receipt.late_days + ' day(s)' : 'On Time') + '</div></div>' : ''}
                </div>
            </div>

            ${isReturn ? '<div class="fine-box"><div class="fine-label">' + (hasFine ? 'FINE AMOUNT' : 'NO FINE') + '</div><div class="amount">' + c + (receipt.fine > 0 ? receipt.fine.toFixed(2) : '0.00') + '</div>' + (hasFine ? '<div class="fine-details">Fine mode: ' + (receipt.fine_mode === 'fixed' ? 'Fixed' : 'Per day') + (receipt.late_days > 0 ? ' | Late: ' + receipt.late_days + ' day(s)' : '') + '</div>' : '') + '</div>' : '<div class="fine-box" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #93c5fd;"><div class="fine-label" style="color: #1e40af;">BORROW PERIOD</div><div class="amount" style="color: #1d4ed8; font-size: 1.2rem;">${receipt.due_date ? new Date(receipt.due_date).toLocaleDateString("en-IN", { day: "2-digit", month: "long", year: "numeric" }) : "N/A"}</div><div class="fine-details">Please return by due date to avoid fines</div></div>'}
        </div>

        <div class="receipt-footer">
            <p><strong>St. Andrew's College Library</strong><br>
            Bandra (W), Mumbai 400 050<br>
            This is a computer-generated receipt. No signature required.</p>
        </div>
    </div>

    <div style="text-align: center;" class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print Receipt</button>
        <button class="download-btn" onclick="downloadPDF()">📥 Save as PDF</button>
    </div>

    <script>
    function downloadPDF() {
        alert('To save as PDF: Click Print, then choose "Save as PDF" as destination.');
        window.print();
    }
    <\/script>
</body>
</html>`;
    }

    // ---- Utility ----
    function getBasePath() {
        // Determine the base path to the library-system root.
        const path = window.location.pathname;
        const idx = path.indexOf('/library-system/');
        if (idx !== -1) return path.substring(0, idx + '/library-system/'.length);
        // Fallback: walk up from current directory
        const parts = path.split('/');
        // Find 'library-system' in path
        for (let i = 0; i < parts.length; i++) {
            if (parts[i] === 'library-system') {
                return parts.slice(0, i + 1).join('/') + '/';
            }
        }
        // ultimate fallback
        return '../';
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function resetAllData() {
        Object.values(KEYS).forEach(k => localStorage.removeItem(k));
        seedData();
    }

    function paginate(arr, page, perPage) {
        const total = arr.length;
        const totalPages = Math.ceil(total / perPage);
        const offset = (page - 1) * perPage;
        return {
            data: arr.slice(offset, offset + perPage),
            total,
            totalPages,
            page,
            perPage
        };
    }

    // ---- Initialize ----
    seedData();

    // Public API
    return {
        login, logout, getSession, isLoggedIn, requireAuth,
        register, getUsers, toggleUserStatus,
        getBooks, getBookById, addBook, updateBook, deleteBook, searchBooks,
        createRequest, createBulkRequest, getRequests, approveRequest, rejectRequest, issueRequest, returnRequest,
        getAdminStats, getUserStats, getBorrowHistory, isBorrowLimitReached,
        getFineSettings, saveFineSettings, calculateCurrentFine, getOverdueBooks,
        generateReceipt, getReceipts, getReceiptById, generateReceiptHTML,
        getBasePath, escapeHtml, resetAllData, paginate, dateOnly, now
    };
})();
