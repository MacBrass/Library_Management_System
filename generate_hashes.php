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
    'Admin123'    => 'admin@andrews.edu',
    'Prof1234'    => 'prof@andrews.edu',
    'Student123'  => 'student@andrews.edu',
];

foreach ($passwords as $pass => $email) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    echo "Email: $email\n";
    echo "Password: $pass\n";
    echo "Hash: $hash\n\n";
}

echo "Copy these hashes into your library_db.sql file.\n";
?>
