/**
 * ============================================================
 * Demo UI Helpers
 * Fr. CRCE Library Management System
 * ============================================================
 * Shared UI rendering functions for demo HTML pages.
 * Generates the topbar, sidebar, and common UI components.
 */

const DemoUI = (function () {
    'use strict';

    function renderTopbar(session) {
        const base = DemoBackend.getBasePath();
        return `
        <nav class="topbar" id="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="brand">
                    <i class="fas fa-book-open brand-icon"></i>
                    <span class="brand-text">Fr. CRCE Library</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name">${DemoBackend.escapeHtml(session.name)}</span>
                        <span class="user-role">${session.role.charAt(0).toUpperCase() + session.role.slice(1)}</span>
                    </div>
                </div>
                <a href="#" onclick="DemoBackend.logout(); window.location.href="${base}auth/login.php?success=You have been logged out successfully."; return false;" class="logout-btn" id="logoutBtn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </nav>`;
    }

    function renderSidebar(session) {
        const base = DemoBackend.getBasePath();
        const role = session.role;
        const currentPage = window.location.pathname.split('/').pop();
        const active = (page) => currentPage === page ? 'active' : '';

        let navContent = '';

        if (role === 'admin') {
            navContent = `
                <div class="nav-section">
                    <span class="nav-section-title">Main</span>
                    <a href="${base}admin/dashboard.php" class="nav-link ${active('dashboard.php')}" id="nav-dashboard">
                        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-section">
                    <span class="nav-section-title">Books</span>
                    <a href="${base}admin/add_book.php" class="nav-link ${active('add_book.php')}" id="nav-add-book">
                        <i class="fas fa-plus-circle"></i><span>Add Book</span>
                    </a>
                    <a href="${base}admin/manage_books.php" class="nav-link ${active('manage_books.php')}" id="nav-manage-books">
                        <i class="fas fa-book"></i><span>Manage Books</span>
                    </a>
                </div>
                <div class="nav-section">
                    <span class="nav-section-title">Operations</span>
                    <a href="${base}admin/requests.php" class="nav-link ${active('requests.php')}" id="nav-requests">
                        <i class="fas fa-clipboard-list"></i><span>Book Requests</span>
                    </a>
                    <a href="${base}admin/users.php" class="nav-link ${active('users.php')}" id="nav-users">
                        <i class="fas fa-users"></i><span>Manage Users</span>
                    </a>
                </div>
                <div class="nav-section">
                    <span class="nav-section-title">Fine & Receipts</span>
                    <a href="${base}admin/fine_settings.php" class="nav-link ${active('fine_settings.php')}" id="nav-fine-settings">
                        <i class="fas fa-cog"></i><span>Fine Settings</span>
                    </a>
                    <a href="${base}admin/receipts.php" class="nav-link ${active('receipts.php')}" id="nav-receipts">
                        <i class="fas fa-receipt"></i><span>Receipts</span>
                    </a>
                    <a href="${base}admin/overdue.php" class="nav-link ${active('overdue.php')}" id="nav-overdue">
                        <i class="fas fa-exclamation-triangle"></i><span>Overdue Books</span>
                    </a>
                </div>`;
        } else if (role === 'professor') {
            navContent = `
                <div class="nav-section">
                    <span class="nav-section-title">Main</span>
                    <a href="${base}professor/dashboard.php" class="nav-link ${active('dashboard.php')}" id="nav-dashboard">
                        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-section">
                    <span class="nav-section-title">Books</span>
                    <a href="${base}professor/request_book.php" class="nav-link ${active('request_book.php')}" id="nav-request-book">
                        <i class="fas fa-hand-holding"></i><span>Request Book</span>
                    </a>
                    <a href="${base}professor/bulk_request.php" class="nav-link ${active('bulk_request.php')}" id="nav-bulk-request">
                        <i class="fas fa-layer-group"></i><span>Bulk Request</span>
                    </a>
                </div>`;
        } else {
            navContent = `
                <div class="nav-section">
                    <span class="nav-section-title">Main</span>
                    <a href="${base}student/dashboard.php" class="nav-link ${active('dashboard.php')}" id="nav-dashboard">
                        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-section">
                    <span class="nav-section-title">Books</span>
                    <a href="${base}student/request_book.php" class="nav-link ${active('request_book.php')}" id="nav-request-book">
                        <i class="fas fa-hand-holding"></i><span>Request Book</span>
                    </a>
                </div>`;
        }

        return `
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="college-badge"><i class="fas fa-university"></i></div>
                <h3>Fr. CRCE</h3>
                <p>Bandra, Mumbai</p>
            </div>
            <nav class="sidebar-nav">${navContent}</nav>
            <div class="sidebar-footer">
                <p>&copy; 2026 Fr. CRCE</p>
            </div>
        </aside>
        <main class="main-content" id="mainContent">`;
    }

    function renderFooter() {
        return `
        </main>
        <script src="${DemoBackend.getBasePath()}assets/js/main.js"><\/script>`;
    }

    function renderDemoBanner() {
        return ``;
    }

    function initLayout(requiredRole) {
        const session = DemoBackend.requireAuth(requiredRole);
        if (!session) return null;

        document.body.insertAdjacentHTML('afterbegin', renderDemoBanner() + renderTopbar(session) + renderSidebar(session));

        // Main content is already in the page body, 
        // we need to close main and add footer script
        // This is handled by each page individually

        return session;
    }

    function showAlert(containerId, msg, type) {
        const area = document.getElementById(containerId);
        if (!area) return;
        const icon = type === 'danger' ? 'exclamation-circle' : (type === 'success' ? 'check-circle' : 'exclamation-triangle');
        area.innerHTML = `<div class="alert alert-${type}"><i class="fas fa-${icon}"></i> ${DemoBackend.escapeHtml(msg)}</div>`;
    }

    function renderPagination(paginationData, urlBuilder) {
        if (paginationData.totalPages <= 1) return '';
        let html = '<div class="pagination">';
        if (paginationData.page > 1) {
            html += `<a href="#" onclick="${urlBuilder(paginationData.page - 1)}; return false;"><i class="fas fa-chevron-left"></i></a>`;
        }
        for (let i = 1; i <= paginationData.totalPages; i++) {
            if (i === paginationData.page) {
                html += `<span class="active">${i}</span>`;
            } else {
                html += `<a href="#" onclick="${urlBuilder(i)}; return false;">${i}</a>`;
            }
        }
        if (paginationData.page < paginationData.totalPages) {
            html += `<a href="#" onclick="${urlBuilder(paginationData.page + 1)}; return false;"><i class="fas fa-chevron-right"></i></a>`;
        }
        html += '</div>';
        return html;
    }

    return {
        renderTopbar, renderSidebar, renderFooter, renderDemoBanner,
        initLayout, showAlert, renderPagination
    };
})();
