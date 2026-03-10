<?php
/**
 * ============================================================
 * Bulk Request (Professor)
 * ============================================================
 * Professors can request books on behalf of their students.
 * Select a book + multiple students = multiple requests created.
 */

require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('professor');

$page_title = 'Bulk Request';
$success = '';
$error = '';

// Process bulk request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = intval($_POST['book_id'] ?? 0);
    $student_ids = $_POST['students'] ?? [];

    if ($book_id <= 0) {
        $error = 'Please select a book.';
    } elseif (empty($student_ids)) {
        $error = 'Please select at least one student.';
    } else {
        // Verify book exists and has enough copies
        $book_stmt = mysqli_prepare($conn, "SELECT id, title, available FROM books WHERE id = ?");
        mysqli_stmt_bind_param($book_stmt, "i", $book_id);
        mysqli_stmt_execute($book_stmt);
        $book = mysqli_fetch_assoc(mysqli_stmt_get_result($book_stmt));
        mysqli_stmt_close($book_stmt);

        if (!$book) {
            $error = 'Selected book not found.';
        } elseif ($book['available'] < count($student_ids)) {
            $error = 'Not enough copies available. Available: ' . $book['available'] . ', Requested: ' . count($student_ids);
        } else {
            $created = 0;
            $skipped = 0;

            foreach ($student_ids as $sid) {
                $sid = intval($sid);

                // Check if student already has an active request for this book
                $dup = mysqli_prepare($conn, 
                    "SELECT id FROM requests WHERE user_id = ? AND book_id = ? AND status IN ('requested', 'approved', 'issued')"
                );
                mysqli_stmt_bind_param($dup, "ii", $sid, $book_id);
                mysqli_stmt_execute($dup);
                $dup_res = mysqli_stmt_get_result($dup);

                if (mysqli_num_rows($dup_res) > 0) {
                    $skipped++;
                } else {
                    // Check student borrow limit
                    if (!isBorrowLimitReached($conn, $sid, 'student')) {
                        $req = mysqli_prepare($conn, "INSERT INTO requests (user_id, book_id, status) VALUES (?, ?, 'requested')");
                        mysqli_stmt_bind_param($req, "ii", $sid, $book_id);
                        if (mysqli_stmt_execute($req)) {
                            $created++;
                        }
                        mysqli_stmt_close($req);
                    } else {
                        $skipped++;
                    }
                }
                mysqli_stmt_close($dup);
            }

            $success = "Bulk request completed: $created requests created";
            if ($skipped > 0) {
                $success .= ", $skipped skipped (duplicate or limit reached)";
            }
            $success .= " for \"" . htmlspecialchars($book['title']) . "\".";
        }
    }
}

// Fetch available books
$books = mysqli_query($conn, "SELECT id, title, author, available FROM books WHERE available > 0 ORDER BY title ASC");

// Fetch active students
$students = mysqli_query($conn, "SELECT id, name, email, department FROM users WHERE role = 'student' AND status = 'active' ORDER BY name ASC");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1>Bulk Book Request</h1>
    <p>Request books on behalf of your students</p>
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
        <h2><i class="fas fa-layer-group"></i> Create Bulk Request</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" id="bulkRequestForm">
            <!-- Step 1: Select Book -->
            <div class="form-group">
                <label for="book_id">Select Book <span class="required">*</span></label>
                <select class="form-control" id="book_id" name="book_id" required>
                    <option value="">-- Choose a Book --</option>
                    <?php while ($book = mysqli_fetch_assoc($books)): ?>
                        <option value="<?php echo $book['id']; ?>">
                            <?php echo htmlspecialchars($book['title']); ?> 
                            by <?php echo htmlspecialchars($book['author']); ?> 
                            (<?php echo $book['available']; ?> available)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Step 2: Select Students -->
            <div class="form-group">
                <label>Select Students <span class="required">*</span></label>
                <div class="mb-1">
                    <label class="checkbox-item" style="border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <input type="checkbox" id="selectAll">
                        <label for="selectAll" style="font-weight: 600;">Select All Students</label>
                    </label>
                </div>
                <div class="checkbox-group">
                    <?php if (mysqli_num_rows($students) > 0): ?>
                        <?php while ($stu = mysqli_fetch_assoc($students)): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" class="student-checkbox" name="students[]" 
                                       value="<?php echo $stu['id']; ?>" 
                                       id="student_<?php echo $stu['id']; ?>">
                                <label for="student_<?php echo $stu['id']; ?>">
                                    <?php echo htmlspecialchars($stu['name']); ?> 
                                    <span class="text-muted">(<?php echo htmlspecialchars($stu['email']); ?> — <?php echo htmlspecialchars($stu['department'] ?? 'N/A'); ?>)</span>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted">No active students found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-success btn-lg" id="bulkSubmitBtn">
                    <i class="fas fa-paper-plane"></i> Submit Bulk Request
                </button>
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
