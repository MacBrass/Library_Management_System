<?php
/**
 * ============================================================
 * Admin Dashboard
 * ============================================================
 * Shows library statistics and recent activity.
 * Only accessible by admin role.
 */

require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('admin');

$page_title = 'Admin Dashboard';

// Fetch statistics using prepared statements
// Total books
$res = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(quantity) as copies FROM books");
$books_stats = mysqli_fetch_assoc($res);
$total_books = $books_stats['total'];
$total_copies = $books_stats['copies'] ?? 0;

// Total borrowed (currently issued)
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM requests WHERE status = 'issued'");
$total_borrowed = mysqli_fetch_assoc($res)['total'];

// Pending requests
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM requests WHERE status = 'requested'");
$pending_requests = mysqli_fetch_assoc($res)['total'];

// Total students
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'student'");
$total_students = mysqli_fetch_assoc($res)['total'];

// Total professors
$res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'professor'");
$total_professors = mysqli_fetch_assoc($res)['total'];

// Recent requests (latest 10)
$recent_requests = mysqli_query($conn, 
    "SELECT r.*, u.name as user_name, u.role as user_role, b.title as book_title 
     FROM requests r 
     JOIN users u ON r.user_id = u.id 
     JOIN books b ON r.book_id = b.id 
     ORDER BY r.request_date DESC 
     LIMIT 10"
);

// Recent borrow history
$recent_history = mysqli_query($conn, 
    "SELECT bh.*, u.name as user_name, b.title as book_title 
     FROM borrow_history bh 
     JOIN users u ON bh.user_id = u.id 
     JOIN books b ON bh.book_id = b.id 
     ORDER BY bh.issue_date DESC 
     LIMIT 10"
);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
    <p>Library overview and statistics</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_books; ?></h3>
            <p>Total Books</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-book-reader"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_borrowed; ?></h3>
            <p>Currently Issued</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $pending_requests; ?></h3>
            <p>Pending Requests</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_students; ?></h3>
            <p>Students</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $total_professors; ?></h3>
            <p>Professors</p>
        </div>
    </div>
</div>

<!-- Recent Requests -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clipboard-list"></i> Recent Book Requests</h2>
        <a href="requests.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($recent_requests) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Book</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($req = mysqli_fetch_assoc($recent_requests)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($req['user_name']); ?></td>
                                <td><span class="badge badge-<?php echo $req['user_role'] === 'professor' ? 'approved' : 'requested'; ?>"><?php echo ucfirst($req['user_role']); ?></span></td>
                                <td><?php echo htmlspecialchars($req['book_title']); ?></td>
                                <td><span class="badge badge-<?php echo $req['status']; ?>"><?php echo ucfirst($req['status']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($req['request_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Requests Yet</h3>
                <p>Book requests will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Borrow History -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> Recent Borrow History</h2>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($recent_history) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Book</th>
                            <th>Issued</th>
                            <th>Returned</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($hist = mysqli_fetch_assoc($recent_history)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hist['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($hist['book_title']); ?></td>
                                <td><?php echo date('d M Y', strtotime($hist['issue_date'])); ?></td>
                                <td>
                                    <?php echo $hist['return_date'] 
                                        ? date('d M Y', strtotime($hist['return_date'])) 
                                        : '<span class="text-warning">Not Returned</span>'; ?>
                                </td>
                                <td>
                                    <?php if ($hist['fine'] > 0): ?>
                                        <span class="text-danger">₹<?php echo number_format($hist['fine'], 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-success">₹0.00</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h3>No History Yet</h3>
                <p>Borrowing history will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
