<?php
/**
 * ============================================================
 * Register Page
 * ============================================================
 * Handles new user registration with server-side validation.
 * New users register as 'student' by default (admin can change).
 * Uses password_hash() with PASSWORD_DEFAULT for secure storage.
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../" . $_SESSION['role'] . "/dashboard.php");
    exit();
}

require_once '../config/database.php';

$error = '';
$name = $email = $department = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $department = trim($_POST['department'] ?? '');

    // Validate role (only student and professor can self-register)
    $allowed_roles = ['student', 'professor'];
    if (!in_array($role, $allowed_roles)) {
        $role = 'student';
    }

    // Server-side validation
    $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif (strlen($name) < 2) {
        $error = 'Name must be at least 2 characters.';
    } elseif (!preg_match($emailRegex, $email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one number.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {
            $error = 'An account with this email already exists.';
        } else {
            // Hash password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = mysqli_prepare($conn, 
                "INSERT INTO users (name, email, password, role, department, status) VALUES (?, ?, ?, ?, ?, 'active')"
            );
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $role, $department);

            if (mysqli_stmt_execute($stmt)) {
                // Registration successful - redirect to login
                header("Location: login.php?success=Registration successful! Please login.");
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }

            mysqli_stmt_close($stmt);
        }

        mysqli_stmt_close($check);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Register for FrCRCE college Library Management System">
    <title>Register | FrCRCE Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/Library_Management_System/assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>Create Account</h1>
                <p>Join FrCRCE college Library Portal</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" 
                           placeholder="Enter your full name" 
                           value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="you@crce.edu.in" 
                           value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="role">I am a <span class="required">*</span></label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="professor" <?php echo (isset($_POST['role']) && $_POST['role'] === 'professor') ? 'selected' : ''; ?>>Professor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="department">Department</label>
                    <input type="text" class="form-control" id="department" name="department" 
                           placeholder="e.g., Computer Science" 
                           value="<?php echo htmlspecialchars($department); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Minimum 8 chars, 1 uppercase, 1 number" required>
                    <span class="form-help">Min 8 characters, at least one uppercase letter and one number</span>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" 
                           placeholder="Re-enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary" id="registerSubmitBtn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
