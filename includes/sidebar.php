<?php
/**
 * ============================================================
 * Sidebar Navigation
 * ============================================================
 * Renders role-specific navigation links.
 * The sidebar adapts based on the logged-in user's role.
 */

$role = $_SESSION['role'] ?? 'student';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar Navigation -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="college-badge">
            <i class="fas fa-university"></i>
        </div>
        <h3>FrCRCE college</h3>
        <p>Bandra, Mumbai</p>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role === 'admin'): ?>
            <!-- Admin Navigation -->
            <div class="nav-section">
                <span class="nav-section-title">Main</span>
                <a href="<?php echo BASE_URL; ?>admin/dashboard.php" 
                   class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" id="nav-dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">Books</span>
                <a href="<?php echo BASE_URL; ?>admin/add_book.php" 
                   class="nav-link <?php echo $current_page === 'add_book.php' ? 'active' : ''; ?>" id="nav-add-book">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Book</span>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_books.php" 
                   class="nav-link <?php echo $current_page === 'manage_books.php' ? 'active' : ''; ?>" id="nav-manage-books">
                    <i class="fas fa-book"></i>
                    <span>Manage Books</span>
                </a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">Operations</span>
                <a href="<?php echo BASE_URL; ?>admin/requests.php" 
                   class="nav-link <?php echo $current_page === 'requests.php' ? 'active' : ''; ?>" id="nav-requests">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Book Requests</span>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/users.php" 
                   class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" id="nav-users">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">Fine & Receipts</span>
                <a href="<?php echo BASE_URL; ?>admin/fine_settings.php" 
                   class="nav-link <?php echo $current_page === 'fine_settings.php' ? 'active' : ''; ?>" id="nav-fine-settings">
                    <i class="fas fa-cog"></i>
                    <span>Fine Settings</span>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/receipts.php" 
                   class="nav-link <?php echo $current_page === 'receipts.php' ? 'active' : ''; ?>" id="nav-receipts">
                    <i class="fas fa-receipt"></i>
                    <span>Receipts</span>
                </a>
                <a href="<?php echo BASE_URL; ?>admin/overdue.php" 
                   class="nav-link <?php echo $current_page === 'overdue.php' ? 'active' : ''; ?>" id="nav-overdue">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Overdue Books</span>
                </a>
            </div>

        <?php elseif ($role === 'professor'): ?>
            <!-- Professor Navigation -->
            <div class="nav-section">
                <span class="nav-section-title">Main</span>
                <a href="<?php echo BASE_URL; ?>professor/dashboard.php" 
                   class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" id="nav-dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">Books</span>
                <a href="<?php echo BASE_URL; ?>professor/request_book.php" 
                   class="nav-link <?php echo $current_page === 'request_book.php' ? 'active' : ''; ?>" id="nav-request-book">
                    <i class="fas fa-hand-holding"></i>
                    <span>Request Book</span>
                </a>
                <a href="<?php echo BASE_URL; ?>professor/bulk_request.php" 
                   class="nav-link <?php echo $current_page === 'bulk_request.php' ? 'active' : ''; ?>" id="nav-bulk-request">
                    <i class="fas fa-layer-group"></i>
                    <span>Bulk Request</span>
                </a>
            </div>

        <?php else: ?>
            <!-- Student Navigation -->
            <div class="nav-section">
                <span class="nav-section-title">Main</span>
                <a href="<?php echo BASE_URL; ?>student/dashboard.php" 
                   class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" id="nav-dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-section">
                <span class="nav-section-title">Books</span>
                <a href="<?php echo BASE_URL; ?>student/request_book.php" 
                   class="nav-link <?php echo $current_page === 'request_book.php' ? 'active' : ''; ?>" id="nav-request-book">
                    <i class="fas fa-hand-holding"></i>
                    <span>Request Book</span>
                </a>
            </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <p>&copy; <?php echo date('Y'); ?> FrCRCE college</p>
    </div>
</aside>

<!-- Main Content Wrapper Start -->
<main class="main-content" id="mainContent">
