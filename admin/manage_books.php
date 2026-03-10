<?php
/**
 * ============================================================
 * Manage Books (Admin)
 * ============================================================
 * View all books with search and pagination.
 * Admin can edit and delete books from here.
 */

require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('admin');

$page_title = 'Manage Books';
$success = '';
$error = '';

// ---- Handle Delete ----
if (isset($_GET['delete'])) {
    $book_id = intval($_GET['delete']);

    // Check if book has active requests
    $check = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM requests WHERE book_id = ? AND status IN ('requested','approved','issued')");
    mysqli_stmt_bind_param($check, "i", $book_id);
    mysqli_stmt_execute($check);
    $check_res = mysqli_stmt_get_result($check);
    $active = mysqli_fetch_assoc($check_res)['cnt'];
    mysqli_stmt_close($check);

    if ($active > 0) {
        $error = 'Cannot delete a book with active requests or issued copies.';
    } else {
        // Delete cover image file if exists
        $img_stmt = mysqli_prepare($conn, "SELECT cover_image FROM books WHERE id = ?");
        mysqli_stmt_bind_param($img_stmt, "i", $book_id);
        mysqli_stmt_execute($img_stmt);
        $img_res = mysqli_stmt_get_result($img_stmt);
        $book_img = mysqli_fetch_assoc($img_res);
        if ($book_img && !empty($book_img['cover_image'])) {
            $img_path = __DIR__ . '/../uploads/books/' . $book_img['cover_image'];
            if (file_exists($img_path)) {
                unlink($img_path);
            }
        }
        mysqli_stmt_close($img_stmt);

        $del = mysqli_prepare($conn, "DELETE FROM books WHERE id = ?");
        mysqli_stmt_bind_param($del, "i", $book_id);
        if (mysqli_stmt_execute($del)) {
            $success = 'Book deleted successfully.';
        } else {
            $error = 'Failed to delete book.';
        }
        mysqli_stmt_close($del);
    }
}

// ---- Handle Edit (POST) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_book_id'])) {
    $edit_id = intval($_POST['edit_book_id']);
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);

    // Get current book data for available copies calculation
    $cur = mysqli_prepare($conn, "SELECT quantity, available FROM books WHERE id = ?");
    mysqli_stmt_bind_param($cur, "i", $edit_id);
    mysqli_stmt_execute($cur);
    $cur_res = mysqli_stmt_get_result($cur);
    $current = mysqli_fetch_assoc($cur_res);
    mysqli_stmt_close($cur);

    if ($current) {
        // Calculate new available: available + (new_quantity - old_quantity)
        $diff = $quantity - $current['quantity'];
        $new_available = max(0, $current['available'] + $diff);

        // Handle new cover image upload
        $cover_sql = '';
        $cover_image = '';
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cover_image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png'];

            if (in_array($ext, $allowed_ext) && $file['size'] <= 2 * 1024 * 1024) {
                $new_filename = 'book_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
                $upload_path = __DIR__ . '/../uploads/books/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
                if (move_uploaded_file($file['tmp_name'], $upload_path . $new_filename)) {
                    $cover_image = $new_filename;
                    $cover_sql = ', cover_image = ?';
                }
            }
        }

        // Update book
        if ($cover_sql) {
            $stmt = mysqli_prepare($conn, 
                "UPDATE books SET title=?, author=?, isbn=?, category=?, publisher=?, year=?, quantity=?, available=? $cover_sql WHERE id=?"
            );
            mysqli_stmt_bind_param($stmt, "sssssiiisi", 
                $title, $author, $isbn, $category, $publisher, $year, $quantity, $new_available, $cover_image, $edit_id
            );
        } else {
            $stmt = mysqli_prepare($conn, 
                "UPDATE books SET title=?, author=?, isbn=?, category=?, publisher=?, year=?, quantity=?, available=? WHERE id=?"
            );
            mysqli_stmt_bind_param($stmt, "sssssiiii", 
                $title, $author, $isbn, $category, $publisher, $year, $quantity, $new_available, $edit_id
            );
        }

        if (mysqli_stmt_execute($stmt)) {
            $success = 'Book updated successfully.';
        } else {
            $error = 'Failed to update book.';
        }
        mysqli_stmt_close($stmt);
    }
}

// ---- Pagination ----
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Search filter
$search = trim($_GET['search'] ?? '');
$where = '';
$params = [];
$types = '';

if (!empty($search)) {
    $where = "WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? OR category LIKE ?";
    $like = "%$search%";
    $params = [$like, $like, $like, $like];
    $types = 'ssss';
}

// Count total for pagination
$count_sql = "SELECT COUNT(*) as total FROM books $where";
if (!empty($search)) {
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
    mysqli_stmt_execute($count_stmt);
    $count_res = mysqli_stmt_get_result($count_stmt);
    $total_books = mysqli_fetch_assoc($count_res)['total'];
    mysqli_stmt_close($count_stmt);
} else {
    $count_res = mysqli_query($conn, $count_sql);
    $total_books = mysqli_fetch_assoc($count_res)['total'];
}

$total_pages = ceil($total_books / $per_page);

// Fetch books
$sql = "SELECT * FROM books $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
if (!empty($search)) {
    $stmt = mysqli_prepare($conn, $sql);
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';
    mysqli_stmt_bind_param($stmt, $types, ...$params);
} else {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $per_page, $offset);
}
mysqli_stmt_execute($stmt);
$books = mysqli_stmt_get_result($stmt);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1>Manage Books</h1>
    <p>View, edit, and manage library books</p>
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
        <h2><i class="fas fa-book"></i> All Books (<?php echo $total_books; ?>)</h2>
        <div class="d-flex gap-1 align-center">
            <form method="GET" action="" class="search-box" style="margin: 0;">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Search books..." 
                       value="<?php echo htmlspecialchars($search); ?>" id="bookSearchInput">
            </form>
            <a href="add_book.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Book
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($books) > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cover</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($book = mysqli_fetch_assoc($books)): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($book['cover_image']) && file_exists(__DIR__ . '/../uploads/books/' . $book['cover_image'])): ?>
                                        <img src="<?php echo BASE_URL; ?>uploads/books/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                                             alt="Cover" class="book-cover-thumb">
                                    <?php else: ?>
                                        <div class="book-cover-thumb" style="background: var(--bg-body); display:flex; align-items:center; justify-content:center; color: var(--text-muted);">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['category']); ?></td>
                                <td><?php echo $book['quantity']; ?></td>
                                <td>
                                    <span class="<?php echo $book['available'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $book['available']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-primary" 
                                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($book)); ?>)" 
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="confirmDelete('manage_books.php?delete=<?php echo $book['id']; ?>&page=<?php echo $page; ?>', '<?php echo htmlspecialchars(addslashes($book['title'])); ?>')" 
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h3>No Books Found</h3>
                <p><?php echo !empty($search) ? 'Try a different search term.' : 'Start by adding some books.'; ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal-box" style="max-width: 600px;">
        <h3><i class="fas fa-edit"></i> Edit Book</h3>
        <form method="POST" action="" enctype="multipart/form-data" id="editBookForm">
            <input type="hidden" name="edit_book_id" id="editBookId">
            <div class="form-row">
                <div class="form-group">
                    <label>Title <span class="required">*</span></label>
                    <input type="text" class="form-control" name="title" id="editTitle" required>
                </div>
                <div class="form-group">
                    <label>Author <span class="required">*</span></label>
                    <input type="text" class="form-control" name="author" id="editAuthor" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>ISBN</label>
                    <input type="text" class="form-control" name="isbn" id="editIsbn">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" class="form-control" name="category" id="editCategory">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Publisher</label>
                    <input type="text" class="form-control" name="publisher" id="editPublisher">
                </div>
                <div class="form-group">
                    <label>Year</label>
                    <input type="number" class="form-control" name="year" id="editYear">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Quantity <span class="required">*</span></label>
                    <input type="number" class="form-control" name="quantity" id="editQuantity" min="1" required>
                </div>
                <div class="form-group">
                    <label>New Cover Image</label>
                    <input type="file" class="form-control" name="cover_image" accept=".jpg,.jpeg,.png">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    /**
     * Open the edit modal with pre-filled book data
     * @param {Object} book - Book data object
     */
    function openEditModal(book) {
        document.getElementById('editBookId').value = book.id;
        document.getElementById('editTitle').value = book.title;
        document.getElementById('editAuthor').value = book.author;
        document.getElementById('editIsbn').value = book.isbn || '';
        document.getElementById('editCategory').value = book.category || '';
        document.getElementById('editPublisher').value = book.publisher || '';
        document.getElementById('editYear').value = book.year || '';
        document.getElementById('editQuantity').value = book.quantity;
        document.getElementById('editModal').classList.add('show');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('show');
    }

    // Close modal when clicking overlay
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>

<?php include '../includes/footer.php'; ?>
