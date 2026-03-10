<?php
/**
 * ============================================================
 * Book Requests Management (Admin)
 * ============================================================
 * Admin can approve, reject, issue, and process returned books.
 * Manages the full request lifecycle.
 */

require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('admin');

$page_title = 'Book Requests';
$success = '';
$error = '';

// ---- Handle Actions ----
if (isset($_GET['action']) && isset($_GET['id'])) {
    $req_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Fetch request details
    $req_stmt = mysqli_prepare($conn, "SELECT r.*, b.available, b.title as book_title FROM requests r JOIN books b ON r.book_id = b.id WHERE r.id = ?");
    mysqli_stmt_bind_param($req_stmt, "i", $req_id);
    mysqli_stmt_execute($req_stmt);
    $req_result = mysqli_stmt_get_result($req_stmt);
    $request = mysqli_fetch_assoc($req_result);
    mysqli_stmt_close($req_stmt);

    if ($request) {
        switch ($action) {
            case 'approve':
                if ($request['status'] === 'requested') {
                    if ($request['available'] <= 0) {
                        $error = 'No copies available for "' . htmlspecialchars($request['book_title']) . '".';
                    } else {
                        $now = date('Y-m-d H:i:s');
                        // Update request status
                        $upd = mysqli_prepare($conn, "UPDATE requests SET status='approved', approval_date=? WHERE id=?");
                        mysqli_stmt_bind_param($upd, "si", $now, $req_id);
                        mysqli_stmt_execute($upd);
                        mysqli_stmt_close($upd);

                        // Decrease available copies
                        $dec = mysqli_prepare($conn, "UPDATE books SET available = available - 1 WHERE id = ? AND available > 0");
                        mysqli_stmt_bind_param($dec, "i", $request['book_id']);
                        mysqli_stmt_execute($dec);
                        mysqli_stmt_close($dec);

                        $success = 'Request approved successfully.';
                    }
                }
                break;

            case 'reject':
                if ($request['status'] === 'requested') {
                    $upd = mysqli_prepare($conn, "UPDATE requests SET status='rejected' WHERE id=?");
                    mysqli_stmt_bind_param($upd, "i", $req_id);
                    mysqli_stmt_execute($upd);
                    mysqli_stmt_close($upd);
                    $success = 'Request rejected.';
                }
                break;

            case 'issue':
                if ($request['status'] === 'approved') {
                    $now = date('Y-m-d H:i:s');
                    // Update request status
                    $upd = mysqli_prepare($conn, "UPDATE requests SET status='issued', issued_date=? WHERE id=?");
                    mysqli_stmt_bind_param($upd, "si", $now, $req_id);
                    mysqli_stmt_execute($upd);
                    mysqli_stmt_close($upd);

                    // Create borrow history record
                    $hist = mysqli_prepare($conn, "INSERT INTO borrow_history (user_id, book_id, issue_date) VALUES (?, ?, ?)");
                    mysqli_stmt_bind_param($hist, "iis", $request['user_id'], $request['book_id'], $now);
                    mysqli_stmt_execute($hist);
                    mysqli_stmt_close($hist);

                    $success = 'Book issued successfully.';
                }
                break;

            case 'return':
                if ($request['status'] === 'issued') {
                    $now = date('Y-m-d H:i:s');
                    
                    // Calculate fine
                    $fine = 0;
                    if ($request['issued_date']) {
                        $fine = calculateFine($request['issued_date']);
                    }

                    // Update request status
                    $upd = mysqli_prepare($conn, "UPDATE requests SET status='returned', return_date=? WHERE id=?");
                    mysqli_stmt_bind_param($upd, "si", $now, $req_id);
                    mysqli_stmt_execute($upd);
                    mysqli_stmt_close($upd);

                    // Increase available copies
                    $inc = mysqli_prepare($conn, "UPDATE books SET available = available + 1 WHERE id = ?");
                    mysqli_stmt_bind_param($inc, "i", $request['book_id']);
                    mysqli_stmt_execute($inc);
                    mysqli_stmt_close($inc);

                    // Update borrow history
                    $hist_upd = mysqli_prepare($conn, 
                        "UPDATE borrow_history SET return_date=?, fine=? 
                         WHERE user_id=? AND book_id=? AND return_date IS NULL 
                         ORDER BY issue_date DESC LIMIT 1"
                    );
                    mysqli_stmt_bind_param($hist_upd, "sdii", $now, $fine, $request['user_id'], $request['book_id']);
                    mysqli_stmt_execute($hist_upd);
                    mysqli_stmt_close($hist_upd);

                    $fine_msg = $fine > 0 ? " Late return fine: ₹" . number_format($fine, 2) : "";
                    $success = 'Book returned successfully.' . $fine_msg;
                }
                break;
        }
    }
}

// ---- Filter by Status ----
$filter = $_GET['filter'] ?? 'all';
$valid_filters = ['all', 'requested', 'approved', 'rejected', 'issued', 'returned'];
if (!in_array($filter, $valid_filters)) $filter = 'all';

$where = '';
if ($filter !== 'all') {
    $where = "WHERE r.status = '$filter'";
}

// ---- Pagination ----
$per_page = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

$count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM requests r $where");
$total = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total / $per_page);

// Fetch requests
$requests = mysqli_query($conn, 
    "SELECT r.*, u.name as user_name, u.email as user_email, u.role as user_role, 
            b.title as book_title, b.author as book_author
     FROM requests r 
     JOIN users u ON r.user_id = u.id 
     JOIN books b ON r.book_id = b.id 
     $where 
     ORDER BY r.request_date DESC 
     LIMIT $per_page OFFSET $offset"
);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1>Book Requests</h1>
    <p>Manage book requests, approvals, and returns</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<!-- Filter Tabs -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clipboard-list"></i> Requests (<?php echo $total; ?>)</h2>
        <div class="btn-group">
            <a href="?filter=all" class="btn btn-sm <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
            <a href="?filter=requested" class="btn btn-sm <?php echo $filter === 'requested' ? 'btn-primary' : 'btn-outline'; ?>">Pending</a>
            <a href="?filter=approved" class="btn btn-sm <?php echo $filter === 'approved' ? 'btn-primary' : 'btn-outline'; ?>">Approved</a>
            <a href="?filter=issued" class="btn btn-sm <?php echo $filter === 'issued' ? 'btn-primary' : 'btn-outline'; ?>">Issued</a>
            <a href="?filter=returned" class="btn btn-sm <?php echo $filter === 'returned' ? 'btn-primary' : 'btn-outline'; ?>">Returned</a>
            <a href="?filter=rejected" class="btn btn-sm <?php echo $filter === 'rejected' ? 'btn-primary' : 'btn-outline'; ?>">Rejected</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($requests) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Book</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Issued</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($req = mysqli_fetch_assoc($requests)): ?>
                            <tr>
                                <td><?php echo $req['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($req['user_name']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($req['user_email']); ?></small>
                                </td>
                                <td><span class="badge badge-<?php echo $req['user_role'] === 'professor' ? 'approved' : 'requested'; ?>"><?php echo ucfirst($req['user_role']); ?></span></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($req['book_title']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($req['book_author']); ?></small>
                                </td>
                                <td><span class="badge badge-<?php echo $req['status']; ?>"><?php echo ucfirst($req['status']); ?></span></td>
                                <td><?php echo date('d M Y', strtotime($req['request_date'])); ?></td>
                                <td>
                                    <?php echo $req['issued_date'] ? date('d M Y', strtotime($req['issued_date'])) : '-'; ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <?php if ($req['status'] === 'requested'): ?>
                                            <a href="?action=approve&id=<?php echo $req['id']; ?>&filter=<?php echo $filter; ?>" 
                                               class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="?action=reject&id=<?php echo $req['id']; ?>&filter=<?php echo $filter; ?>" 
                                               class="btn btn-sm btn-danger" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php elseif ($req['status'] === 'approved'): ?>
                                            <a href="?action=issue&id=<?php echo $req['id']; ?>&filter=<?php echo $filter; ?>" 
                                               class="btn btn-sm btn-primary" title="Issue Book">
                                                <i class="fas fa-hand-holding"></i> Issue
                                            </a>
                                        <?php elseif ($req['status'] === 'issued'): ?>
                                            <a href="?action=return&id=<?php echo $req['id']; ?>&filter=<?php echo $filter; ?>" 
                                               class="btn btn-sm btn-warning" title="Return Book">
                                                <i class="fas fa-undo"></i> Return
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>"><i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Requests</h3>
                <p>No requests found for the selected filter.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
