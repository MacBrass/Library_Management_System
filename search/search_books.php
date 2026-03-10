<?php
/**
 * ============================================================
 * AJAX Book Search API
 * ============================================================
 * Returns JSON results for live search.
 * Searches by title, author, category, and ISBN.
 * Called via AJAX from the frontend JavaScript.
 */

// Start session for auth check (optional - search can be public)
session_start();

require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Get search query
$query = trim($_GET['q'] ?? '');

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

// Search using prepared statements to prevent SQL injection
$like = '%' . $query . '%';
$stmt = mysqli_prepare($conn, 
    "SELECT id, title, author, category, isbn, available 
     FROM books 
     WHERE title LIKE ? OR author LIKE ? OR category LIKE ? OR isbn LIKE ? 
     ORDER BY title ASC 
     LIMIT 10"
);
mysqli_stmt_bind_param($stmt, "ssss", $like, $like, $like, $like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$books = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Determine the link based on user role
    $role = $_SESSION['role'] ?? 'student';
    $link = $role . '/request_book.php';

    $books[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'author' => $row['author'],
        'category' => $row['category'] ?? 'Uncategorized',
        'isbn' => $row['isbn'] ?? '',
        'available' => $row['available'],
        'link' => $link
    ];
}

mysqli_stmt_close($stmt);

echo json_encode($books);
?>
