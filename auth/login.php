<?php
/**
 * ============================================================
 * Login Page
 * ============================================================
 * Handles user authentication with email and password.
 * Uses password_verify() for secure password checking.
 * Validates email format and password requirements on server side.
 */

session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../" . $_SESSION['role'] . "/dashboard.php");
    exit();
}

require_once '../config/database.php';

$error = '';
$success = '';

// Get messages from URL params (from register redirect, etc.)
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Server-side validation
    $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!preg_match($emailRegex, $email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        // Look up user by email using prepared statement
        $stmt = mysqli_prepare($conn, "SELECT id, name, email, password, role, status FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Check if account is active
            if ($row['status'] !== 'active') {
                $error = 'Your account has been deactivated. Contact the administrator.';
            }
            // Verify password hash
            elseif (password_verify($password, $row['password'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['role'] = $row['role'];

                // Redirect to role-specific dashboard
                header("Location: ../" . $row['role'] . "/dashboard.php");
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to Fr. CRCE Library Management System">
    <title>Login | Fr. CRCE Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo">
                    <i class="fas fa-book-open"></i>
                </div>
                <h1>Welcome Back</h1>
                <p>Fr. CRCE Library Portal</p>
            </div>

            <div id="alertArea"></div>

            <?php if (DEMO_MODE): ?>
                <!-- Demo credentials info -->
                <div style="background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 12px; padding: 16px; margin-bottom: 24px; font-size: 0.85rem;">
                    <strong style="color: #6366f1; display: block; margin-bottom: 4px;"><i class="fas fa-info-circle"></i> Demo Credentials:</strong>
                    <div style="color: #475569; line-height: 1.5;">
                        Admin: <code>admin@frcrce.ac.in</code> / <code>Admin123</code><br>
                        Prof: <code>prof@frcrce.ac.in</code> / <code>Prof1234</code><br>
                        Student: <code>student@frcrce.ac.in</code> / <code>Student123</code>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST" action="" id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="you@frcrce.ac.in" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary" id="loginSubmitBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/demo-backend.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        const APP_CONFIG = {
            demoMode: <?php echo DEMO_MODE ? 'true' : 'false'; ?>
        };

        if (APP_CONFIG.demoMode) {
            // If already logged in via demo, redirect
            if (DemoBackend.isLoggedIn()) {
                const s = DemoBackend.getSession();
                window.location.href = '../' + s.role + '/dashboard.php';
            }

            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;

                function showAlert(msg, type) {
                    const area = document.getElementById('alertArea');
                    const icon = type === 'danger' ? 'exclamation-circle' : 'check-circle';
                    area.innerHTML = '<div class="alert alert-' + type + '"><i class="fas fa-' + icon + '"></i> ' + DemoBackend.escapeHtml(msg) + '</div>';
                }

                const result = DemoBackend.login(email, password);
                if (result.success) {
                    window.location.href = '../' + result.role + '/dashboard.php';
                } else {
                    showAlert(result.error, 'danger');
                }
            });
        }
    </script>
</body>
</html>
