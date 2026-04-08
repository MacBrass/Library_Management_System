<?php
/**
 * ============================================================
 * Database Setup Script
 * ============================================================
 * Run this file ONCE after importing library_db.sql to generate
 * correct password hashes for sample users.
 * 
 * Access: http://localhost/library-system/setup.php
 * 
 * This will:
 * 1. Update sample user passwords with proper bcrypt hashes
 * 2. Verify the database connection
 * 3. Display setup status
 */

require_once 'config/database.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Setup</title>";
echo "<style>body{font-family:'Segoe UI',sans-serif;max-width:700px;margin:50px auto;padding:20px;background:#f5f6fa;}";
echo ".box{background:#fff;padding:20px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);margin-bottom:20px;}";
echo ".success{color:#27ae60;}.error{color:#e74c3c;}.info{color:#3498db;}";
echo "h1{color:#2c3e50;}h2{color:#34495e;}pre{background:#f0f2f5;padding:10px;border-radius:6px;overflow-x:auto;}</style></head><body>";

echo "<h1>🏛️ FrCRCE Library - Setup</h1>";

// Test database connection
echo "<div class='box'>";
echo "<h2>Database Connection</h2>";
if ($conn) {
    echo "<p class='success'>✅ Successfully connected to database: " . DB_NAME . "</p>";
} else {
    echo "<p class='error'>❌ Database connection failed. Check config/database.php</p>";
    echo "</div></body></html>";
    exit();
}
echo "</div>";

// Check tables
echo "<div class='box'>";
echo "<h2>Table Check</h2>";
$tables = ['users', 'books', 'requests', 'borrow_history'];
$all_ok = true;
foreach ($tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p class='success'>✅ Table '$table' exists</p>";
    } else {
        echo "<p class='error'>❌ Table '$table' not found. Import library_db.sql first!</p>";
        $all_ok = false;
    }
}
echo "</div>";

if (!$all_ok) {
    echo "<div class='box'><p class='error'>Please import library_db.sql into phpMyAdmin first, then run this setup again.</p></div>";
    echo "</body></html>";
    exit();
}

// Update sample user passwords with correct hashes
echo "<div class='box'>";
echo "<h2>Setting Up Sample Users</h2>";

$users = [
    ['email' => 'admin@crce.edu.in',   'password' => 'Admin123',    'name' => 'Admin User',    'role' => 'admin',     'department' => 'Administration'],
    ['email' => 'prof@crce.edu.in',    'password' => 'Prof1234',    'name' => 'Prof. Sharma',  'role' => 'professor', 'department' => 'Computer Science'],
    ['email' => 'student@crce.edu.in', 'password' => 'Student123',  'name' => 'Rahul Verma',   'role' => 'student',   'department' => 'Computer Science'],
];

foreach ($users as $user) {
    // Check if user exists
    $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($check, "s", $user['email']);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);

    $hash = password_hash($user['password'], PASSWORD_DEFAULT);

    if (mysqli_num_rows($result) > 0) {
        // Update existing user's password
        $upd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
        mysqli_stmt_bind_param($upd, "ss", $hash, $user['email']);
        mysqli_stmt_execute($upd);
        echo "<p class='info'>🔄 Updated: {$user['email']} ({$user['role']})</p>";
        mysqli_stmt_close($upd);
    } else {
        // Insert new user
        $ins = mysqli_prepare($conn, 
            "INSERT INTO users (name, email, password, role, department, status) VALUES (?, ?, ?, ?, ?, 'active')"
        );
        mysqli_stmt_bind_param($ins, "sssss", $user['name'], $user['email'], $hash, $user['role'], $user['department']);
        mysqli_stmt_execute($ins);
        echo "<p class='success'>✅ Created: {$user['email']} ({$user['role']})</p>";
        mysqli_stmt_close($ins);
    }

    mysqli_stmt_close($check);
}

echo "</div>";

// Show login credentials
echo "<div class='box'>";
echo "<h2>📋 Login Credentials</h2>";
echo "<pre>";
echo "Admin Login:\n";
echo "  Email:    admin@crce.edu.in\n";
echo "  Password: Admin123\n\n";
echo "Professor Login:\n";
echo "  Email:    prof@crce.edu.in\n";
echo "  Password: Prof1234\n\n";
echo "Student Login:\n";
echo "  Email:    student@crce.edu.in\n";
echo "  Password: Student123\n";
echo "</pre>";
echo "</div>";

echo "<div class='box'>";
echo "<h2>🚀 Setup Complete!</h2>";
echo "<p class='success'>The library system is ready to use.</p>";
echo "<p><a href='auth/login.php' style='color:#3498db;font-weight:600;'>→ Go to Login Page</a></p>";
echo "<p class='error'><strong>IMPORTANT:</strong> Delete or rename this file (setup.php) after setup for security.</p>";
echo "</div>";

echo "</body></html>";
?>
