<?php
/**
 * ============================================================
 * Database Configuration
 * Fr. CRCE Library Management System
 * ============================================================
 * 
 * Modify these credentials to match your server setup.
 * For XAMPP: host=localhost, username=root, password=(empty)
 * For live hosting: use your cPanel database credentials
 */

// Database connection parameters
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Change for live server
define('DB_PASS', '');             // Change for live server
define('DB_NAME', 'library_db');

// Create database connection using MySQLi
$conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection - set Demo Mode instead of halting
if (!$conn) {
    define('DEMO_MODE', true);
} else {
    define('DEMO_MODE', false);
    // Set character set to UTF-8 for proper encoding
    mysqli_set_charset($conn, "utf8mb4");
}

// Base URL - change this for live server deployment
define('BASE_URL', '/library-system/');

// Upload directory for book cover images
define('UPLOAD_DIR', __DIR__ . '/../uploads/books/');

// Borrow limits per role
define('STUDENT_BORROW_LIMIT', 3);
define('PROFESSOR_BORROW_LIMIT', 5);

// Late return fine in INR per day
define('FINE_PER_DAY', 5);
?>
