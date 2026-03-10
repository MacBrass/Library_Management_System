<?php
/**
 * ============================================================
 * Request Book (Student)
 * ============================================================
 * Students can browse available books and request them.
 * Students cannot approve requests — only admin can.
 */

require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('student');

$page_title = 'Request Book';
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle book request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = intval($_POST['book_id']);

    // Check borrow limit
    if (isBorrowLimitReached($conn, $user_id, 'student')) {
        $error = 'You have reached your borrow limit of ' . STUDENT_BORROW_LIMIT . ' books.';
    } else {
        // Check if book exists and is available
        $book_check = mysqli_prepare($conn, "SELECT id, title, available FROM books WHERE id = ?");
        mysqli_stmt_bind_param($book_check, "i", $book_id);
        mysqli_stmt_execute($book_check);
        $book = mysqli_fetch_assoc(mysqli_stmt_get_result($book_check));
        mysqli_stmt_close($book_check);

        if (!$book) {
            $error = 'Book not found.';
        } elseif ($book['available'] <= 0) {
            $error = 'No copies of "' . htmlspecialchars($book['title']) . '" are currently available.';
        } else {
            // Check for duplicate pending request
            $dup_check = mysqli_prepare($conn, 
                "SELECT id FROM requests WHERE user_id = ? AND book_id = ? AND status IN ('requested', 'approved', 'issued')"
            );
            mysqli_stmt_bind_param($dup_check, "ii", $user_id, $book_id);
            mysqli_stmt_execute($dup_check);
            $dup_result = mysqli_stmt_get_result($dup_check);

            if (mysqli_num_rows($dup_result) > 0) {
                $error = 'You already have an active request for this book.';
            } else {
                // Create request
                $req = mysqli_prepare($conn, "INSERT INTO requests (user_id, book_id, status) VALUES (?, ?, 'requested')");
                mysqli_stmt_bind_param($req, "ii", $user_id, $book_id);

                if (mysqli_stmt_execute($req)) {
                    $success = 'Book "' . htmlspecialchars($book['title']) . '" requested successfully! Wait for admin approval.';
                } else {
                    $error = 'Failed to create request. Please try again.';
                }
                mysqli_stmt_close($req);
            }
            mysqli_stmt_close($dup_check);
        }
    }
}

// ---- Pagination ----
$per_page = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

$search = trim($_GET['search'] ?? '');
$where = '';
if (!empty($search)) {
    $like = '%' . mysqli_real_escape_string($conn, $search) . '%';
    $where = "WHERE (title LIKE '$like' OR author LIKE '$like' OR category LIKE '$like' OR isbn LIKE '$like')";
}

$count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM books $where");
$total = mysqli_fetch_assoc($count_res)['total'];
$total_pages = ceil($total / $per_page);

$books = mysqli_query($conn, "SELECT * FROM books $where ORDER BY title ASC LIMIT $per_page OFFSET $offset");

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1>Request a Book</h1>
    <p>Browse and request books from the library</p>
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

<!-- Search -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="" class="d-flex gap-1 align-center">
            <div class="search-box" style="max-width: 100%; flex: 1;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search by title, author, category, or ISBN..." 
                       value="<?php echo htmlspecialchars($search); ?>" id="liveSearchInput">
                <div class="search-results" id="searchResults"></div>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (!empty($search)): ?>
                <a href="request_book.php" class="btn btn-outline">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<meta name="base-url" content="<?php echo BASE_URL; ?>">

<!-- Books Grid -->
<div class="books-grid">
    <?php if (mysqli_num_rows($books) > 0): ?>
        <?php while ($book = mysqli_fetch_assoc($books)): ?>
            <div class="book-card">
                <div class="book-card-cover">
                    <?php if (!empty($book['cover_image']) && file_exists(__DIR__ . '/../uploads/books/' . $book['cover_image'])): ?>
                        <img src="<?php echo BASE_URL; ?>uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <?php else: ?>
                        <i class="fas fa-book no-cover"></i>
                    <?php endif; ?>
                </div>
                <div class="book-card-body">
                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p class="author"><?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="availability <?php echo $book['available'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $book['available'] > 0 
                            ? $book['available'] . ' copies available' 
                            : 'Currently unavailable'; ?>
                    </p>
                </div>
                <div class="book-card-footer">
                    <?php if ($book['available'] > 0): ?>
                        <form method="POST" action="" style="margin: 0;">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-hand-holding"></i> Request
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-outline btn-sm w-100" disabled>
                            <i class="fas fa-times-circle"></i> Unavailable
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state" style="grid-column: 1 / -1;">
            <i class="fas fa-search"></i>
            <h3>No Books Found</h3>
            <p><?php echo !empty($search) ? 'Try a different search term.' : 'No books in the library yet.'; ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-left"></i></a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>"><i class="fas fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
