<?php
/**
 * ============================================================
 * Authentication Check
 * ============================================================
 * Include this file at the top of every protected page.
 * It verifies the user is logged in and has the correct role.
 * 
 * Usage: 
 *   require_once '../includes/auth_check.php';
 *   checkAuth('admin');  // Only admin can access
 *   checkAuth('professor');
 *   checkAuth('student');
 *   checkAuth(['admin', 'professor']); // Multiple roles allowed
 */

session_start();

/**
 * Check if user is authenticated and has the required role
 * @param string|array $required_role - The role(s) allowed to access this page
 */
function checkAuth($required_role = null) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        // Destroy any partial session data
        session_unset();
        session_destroy();
        header("Location: " . getBaseUrl() . "auth/login.php?error=Please login to continue");
        exit();
    }

    // If a specific role is required, verify it
    if ($required_role !== null) {
        $allowed = is_array($required_role) ? $required_role : [$required_role];
        if (!in_array($_SESSION['role'], $allowed)) {
            // Redirect to their own dashboard if they try accessing wrong area
            $redirect = getBaseUrl() . $_SESSION['role'] . '/dashboard.php';
            header("Location: $redirect");
            exit();
        }
    }
}

/**
 * Get the base URL for redirections
 * @return string Base URL path
 */
function getBaseUrl() {
    return '/library-system/';
}

/**
 * Check if user has reached their borrow limit
 * @param mysqli $conn - Database connection
 * @param int $user_id - User ID
 * @param string $role - User role
 * @return bool True if limit reached
 */
function isBorrowLimitReached($conn, $user_id, $role) {
    // Count active borrows (approved or issued, not returned)
    $stmt = mysqli_prepare($conn, 
        "SELECT COUNT(*) as count FROM requests 
         WHERE user_id = ? AND status IN ('approved', 'issued')"
    );
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $current_borrows = $row['count'];
    mysqli_stmt_close($stmt);

    // Check against role-specific limits
    $limit = ($role === 'professor') ? PROFESSOR_BORROW_LIMIT : STUDENT_BORROW_LIMIT;
    return $current_borrows >= $limit;
}

/**
 * Calculate fine for late return
 * @param string $issued_date - Date the book was issued
 * @return float Fine amount in INR
 */
function calculateFine($issued_date) {
    $issue = new DateTime($issued_date);
    $now = new DateTime();
    $diff = $now->diff($issue);
    $days = $diff->days;
    
    // 14 days borrowing period
    if ($days > 14) {
        $late_days = $days - 14;
        return $late_days * FINE_PER_DAY;
    }
    return 0;
}
?>
