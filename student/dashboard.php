<?php
/**
 * ============================================================
 * Student Dashboard
 * ============================================================
 * Shows student's borrowed books, request status,
 * and an option to request a new book.
 */

require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('student');

$page_title = 'Student Dashboard';
$user_id = $_SESSION['user_id'];

// Fetch stats
$res = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM requests WHERE user_id = ? AND status = 'issued'");
mysqli_stmt_bind_param($res, "i", $user_id);
mysqli_stmt_execute($res);
$active_borrows = mysqli_fetch_assoc(mysqli_stmt_get_result($res))['total'];
mysqli_stmt_close($res);

$res = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM requests WHERE user_id = ? AND status = 'requested'");
mysqli_stmt_bind_param($res, "i", $user_id);
mysqli_stmt_execute($res);
$pending_requests = mysqli_fetch_assoc(mysqli_stmt_get_result($res))['total'];
mysqli_stmt_close($res);

$res = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM requests WHERE user_id = ? AND status = 'approved'");
mysqli_stmt_bind_param($res, "i", $user_id);
mysqli_stmt_execute($res);
$approved = mysqli_fetch_assoc(mysqli_stmt_get_result($res))['total'];
mysqli_stmt_close($res);

$res = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM requests WHERE user_id = ? AND status = 'returned'");
mysqli_stmt_bind_param($res, "i", $user_id);
mysqli_stmt_execute($res);
$returned = mysqli_fetch_assoc(mysqli_stmt_get_result($res))['total'];
mysqli_stmt_close($res);

// Borrow limit
$borrow_limit = STUDENT_BORROW_LIMIT;
$limit_reached = isBorrowLimitReached($conn, $user_id, 'student');

// Active requests
$my_requests = mysqli_prepare($conn, 
    "SELECT r.*, b.title as book_title, b.author as book_author 
     FROM requests r 
     JOIN books b ON r.book_id = b.id 
     WHERE r.user_id = ? AND r.status IN ('requested', 'approved', 'issued') 
     ORDER BY r.request_date DESC"
);
mysqli_stmt_bind_param($my_requests, "i", $user_id);
mysqli_stmt_execute($my_requests);
$active_requests = mysqli_stmt_get_result($my_requests);

// History
$hist_stmt = mysqli_prepare($conn, 
    "SELECT bh.*, b.title as book_title 
     FROM borrow_history bh 
     JOIN books b ON bh.book_id = b.id 
     WHERE bh.user_id = ? 
     ORDER BY bh.issue_date DESC LIMIT 10"
);
mysqli_stmt_bind_param($hist_stmt, "i", $user_id);
mysqli_stmt_execute($hist_stmt);
$history = mysqli_stmt_get_result($hist_stmt);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
    <p>Student Dashboard — Borrow limit: <?php echo $active_borrows; ?>/<?php echo $borrow_limit; ?> books</p>
</div>

<?php if ($limit_reached): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> You have reached your borrow limit of <?php echo $borrow_limit; ?> books. Return a book to request more.
    </div>
<?php endif; ?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-book-reader"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $active_borrows; ?></h3>
            <p>Books Issued</p>
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
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $approved; ?></h3>
            <p>Approved</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal">
            <i class="fas fa-undo"></i>
        </div>
        <div class="stat-info">
            <h3><?php echo $returned; ?></h3>
            <p>Returned</p>
        </div>
    </div>
</div>

<!-- Quick Action -->
<div class="mb-3">
    <a href="request_book.php" class="btn btn-primary">
        <i class="fas fa-hand-holding"></i> Browse & Request Books
    </a>
</div>

<!-- Active Requests -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clipboard-list"></i> My Active Requests</h2>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($active_requests) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($req = mysqli_fetch_assoc($active_requests)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($req['book_title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($req['book_author']); ?></td>
                                <td><span class="badge badge-<?php echo $req['status']; ?>"><?php echo ucfirst($req['status']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($req['request_date'])); ?></td>
                                <td><?php echo $req['issued_date'] ? date('d M Y', strtotime($req['issued_date'])) : '-'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Active Requests</h3>
                <p>Browse books to make a request.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Borrow History -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-history"></i> My Borrow History</h2>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($history) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Issued</th>
                            <th>Returned</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($h = mysqli_fetch_assoc($history)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($h['book_title']); ?></td>
                                <td><?php echo date('d M Y', strtotime($h['issue_date'])); ?></td>
                                <td>
                                    <?php echo $h['return_date'] 
                                        ? date('d M Y', strtotime($h['return_date'])) 
                                        : '<span class="text-warning">Not returned</span>'; ?>
                                </td>
                                <td>
                                    <?php if ($h['fine'] > 0): ?>
                                        <span class="text-danger">₹<?php echo number_format($h['fine'], 2); ?></span>
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
                <h3>No History</h3>
                <p>Your borrowing history will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
