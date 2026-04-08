<?php
/**
 * ============================================================
 * Password Hash Generator
 * ============================================================
 * Run this file once to generate password hashes for sample users.
 * Then copy the hashes into library_db.sql
 * 
 * Usage: php generate_hashes.php
 */

echo "=== Password Hash Generator ===\n\n";

$passwords = [
    'Admin123'    => 'admin@crce.edu.in',
    'Prof1234'    => 'prof@crce.edu.in',
    'Student123'  => 'student@crce.edu.in',
];

foreach ($passwords as $pass => $email) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    echo "Email: $email\n";
    echo "Password: $pass\n";
    echo "Hash: $hash\n\n";
}

echo "Copy these hashes into your library_db.sql file.\n";
?>
