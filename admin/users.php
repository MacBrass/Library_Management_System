<?php
/**
 * ============================================================
 * Manage Users (Admin)
 * ============================================================
 * Admin can view all users, activate/deactivate accounts,
 * and see user details.
 */

require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('admin');

$page_title = 'Manage Users';
$success = '';
$error = '';

// ---- Handle Activate/Deactivate ----
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];

    // Prevent admin from deactivating themselves
    if ($user_id == $_SESSION['user_id']) {
        $error = 'You cannot modify your own account.';
    } else {
        if ($action === 'activate') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET status='active' WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = 'User activated successfully.';
            }
            mysqli_stmt_close($stmt);
        } elseif ($action === 'deactivate') {
            $stmt = mysqli_prepare($conn, "UPDATE users SET status='inactive' WHERE id=?");
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $success = 'User deactivated successfully.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// ---- Filter by Role ----
$role_filter = $_GET['role'] ?? 'all';
$valid_roles = ['all', 'admin', 'professor', 'student'];
if (!in_array($role_filter, $valid_roles)) $role_filter = 'all';

$where = '';
if ($role_filter !== 'all') {
    $where = "WHERE role = '" . mysqli_real_escape_string($conn, $role_filter) . "'";
}

// Search
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $like = '%' . mysqli_real_escape_string($conn, $search) . '%';
    $where = empty($where) 
        ? "WHERE (name LIKE '$like' OR email LIKE '$like' OR department LIKE '$like')" 
        : "$where AND (name LIKE '$like' OR email LIKE '$like' OR department LIKE '$like')";
}

// ---- Pagination ----
$per_page = 15;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

$count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users $where");
$total_users = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total_users / $per_page);

// Fetch users
$users = mysqli_query($conn, "SELECT * FROM users $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1>Manage Users</h1>
    <p>View and manage system users</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-users"></i> All Users (<?php echo $total_users; ?>)</h2>
        <div class="d-flex gap-1 align-center">
            <form method="GET" action="" class="search-box" style="margin: 0;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search users..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($role_filter); ?>">
            </form>
        </div>
    </div>
    <div class="card-body">
        <!-- Role Filter -->
        <div class="btn-group mb-2">
            <a href="?role=all" class="btn btn-sm <?php echo $role_filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">All</a>
            <a href="?role=admin" class="btn btn-sm <?php echo $role_filter === 'admin' ? 'btn-primary' : 'btn-outline'; ?>">Admins</a>
            <a href="?role=professor" class="btn btn-sm <?php echo $role_filter === 'professor' ? 'btn-primary' : 'btn-outline'; ?>">Professors</a>
            <a href="?role=student" class="btn btn-sm <?php echo $role_filter === 'student' ? 'btn-primary' : 'btn-outline'; ?>">Students</a>
        </div>

        <?php if (mysqli_num_rows($users) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = mysqli_fetch_assoc($users)): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $user['role'] === 'admin' ? 'issued' : 
                                            ($user['role'] === 'professor' ? 'approved' : 'requested'); 
                                    ?>"><?php echo ucfirst($user['role']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($user['department'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <?php if ($user['status'] === 'active'): ?>
                                            <a href="?action=deactivate&id=<?php echo $user['id']; ?>&role=<?php echo $role_filter; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Deactivate this user?')" 
                                               title="Deactivate">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?action=activate&id=<?php echo $user['id']; ?>&role=<?php echo $role_filter; ?>" 
                                               class="btn btn-sm btn-success" 
                                               title="Activate">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">You</span>
                                    <?php endif; ?>
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
                        <a href="?page=<?php echo $page - 1; ?>&role=<?php echo $role_filter; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&role=<?php echo $role_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&role=<?php echo $role_filter; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Users Found</h3>
                <p>No users match the current filter.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
