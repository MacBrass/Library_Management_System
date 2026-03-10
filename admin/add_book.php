<?php
/**
 * ============================================================
 * Add Book (Admin)
 * ============================================================
 * Allows admin to add new books to the library.
 * Handles book cover image upload with validation.
 */

require_once '../config/database.php';
require_once '../includes/auth_check.php';
checkAuth('admin');

$page_title = 'Add Book';
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $cover_image = '';

    // Validation
    if (empty($title) || empty($author)) {
        $error = 'Book title and author are required.';
    } elseif ($quantity < 1) {
        $error = 'Quantity must be at least 1.';
    } else {
        // Handle file upload
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['cover_image'];
            $file_name = $file['name'];
            $file_size = $file['size'];
            $file_tmp = $file['tmp_name'];

            // Validate file extension
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_ext)) {
                $error = 'Only JPG, JPEG, and PNG files are allowed.';
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error = 'File size must be less than 2MB.';
            } else {
                // Generate unique filename
                $new_filename = 'book_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

                // Ensure upload directory exists
                $upload_path = __DIR__ . '/../uploads/books/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }

                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path . $new_filename)) {
                    $cover_image = $new_filename;
                } else {
                    $error = 'Failed to upload cover image.';
                }
            }
        }

        // Insert book if no errors
        if (empty($error)) {
            $stmt = mysqli_prepare($conn, 
                "INSERT INTO books (title, author, isbn, category, publisher, year, quantity, available, cover_image) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $available = $quantity; // New book: all copies available
            mysqli_stmt_bind_param($stmt, "sssssiiss", 
                $title, $author, $isbn, $category, $publisher, $year, $quantity, $available, $cover_image
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = 'Book "' . htmlspecialchars($title) . '" added successfully!';
                // Reset form values
                $title = $author = $isbn = $category = $publisher = '';
                $year = $quantity = 0;
            } else {
                $error = 'Failed to add book. Please try again.';
            }

            mysqli_stmt_close($stmt);
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header">
    <h1>Add New Book</h1>
    <p>Add a new book to the library collection</p>
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
        <h2><i class="fas fa-plus-circle"></i> Book Details</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data" id="addBookForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Book Title <span class="required">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" 
                           placeholder="Enter book title" 
                           value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="author">Author <span class="required">*</span></label>
                    <input type="text" class="form-control" id="author" name="author" 
                           placeholder="Enter author name" 
                           value="<?php echo htmlspecialchars($author ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" class="form-control" id="isbn" name="isbn" 
                           placeholder="e.g., 9780262033848" 
                           value="<?php echo htmlspecialchars($isbn ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select class="form-control" id="category" name="category">
                        <option value="">Select Category</option>
                        <?php
                        $categories = ['Computer Science', 'Software Engineering', 'Networking', 
                                       'Artificial Intelligence', 'Mathematics', 'Physics', 
                                       'Chemistry', 'Biology', 'Literature', 'History', 
                                       'Economics', 'Management', 'Other'];
                        foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo (isset($category) && $category === $cat) ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="publisher">Publisher</label>
                    <input type="text" class="form-control" id="publisher" name="publisher" 
                           placeholder="Enter publisher name" 
                           value="<?php echo htmlspecialchars($publisher ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="year">Year of Publication</label>
                    <input type="number" class="form-control" id="year" name="year" 
                           placeholder="e.g., 2023" min="1900" max="<?php echo date('Y'); ?>" 
                           value="<?php echo ($year ?? 0) > 0 ? $year : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quantity">Quantity <span class="required">*</span></label>
                    <input type="number" class="form-control" id="quantity" name="quantity" 
                           placeholder="Number of copies" min="1" 
                           value="<?php echo ($quantity ?? 1) > 0 ? $quantity : 1; ?>" required>
                </div>
                <div class="form-group">
                    <label>Book Cover Image</label>
                    <div class="upload-area">
                        <input type="file" id="coverImage" name="cover_image" accept=".jpg,.jpeg,.png">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload cover image (JPG, PNG, max 2MB)</p>
                    </div>
                    <div class="upload-preview" id="uploadPreview"></div>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary btn-lg" id="submitBookBtn">
                    <i class="fas fa-plus-circle"></i> Add Book
                </button>
                <a href="manage_books.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Books
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
