<?php
/**
 * ============================================================
 * Landing Page
 * Fr. CRCE Library Management System
 * ============================================================
 * This is the public-facing homepage. It shows system features
 * and provides links to login and register.
 */

session_start();

// If user is already logged in, redirect to their dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: " . $_SESSION['role'] . "/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Fr. CRCE Digital Library Management System - Browse, request, and manage library books online.">
    <title>Fr. CRCE - Digital Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="landing-wrapper">
        <!-- Floating decorative shapes -->

        <!-- Navigation -->
        <nav class="landing-nav">
            <div class="brand">
                <i class="fas fa-book-open"></i>
                <span>Fr. CRCE Library</span>
            </div>
            <div class="btn-group">
                <a href="auth/login.php" class="btn btn-outline"  id="navLoginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
                <a href="auth/register.php" class="btn btn-primary" id="navRegisterBtn">
                    <i class="fas fa-user-plus"></i> Register
                </a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="landing-hero">
            <h1>Digital Library Management System</h1>
            <p>Fr. CRCE, Bandra, Mumbai — Browse our collection, request books online, and manage your reading with our modern library portal.</p>
            <div class="btn-group">
                <a href="auth/login.php" class="btn btn-primary btn-lg" id="heroLoginBtn">
                    <i class="fas fa-sign-in-alt"></i> Login to Portal
                </a>
                <a href="auth/register.php" class="btn btn-outline btn-lg"  id="heroRegisterBtn">
                    <i class="fas fa-user-plus"></i> Create Account
                </a>
            </div>
        </section>

        <!-- Features Section -->
        <section class="landing-features">
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Smart Search</h3>
                <p>Find books instantly with our live AJAX search. Search by title, author, category, or ISBN.</p>
            </div>
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-hand-holding"></i>
                </div>
                <h3>Online Requests</h3>
                <p>Request books directly from your dashboard. Track approvals and due dates in real time.</p>
            </div>
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Role-Based Access</h3>
                <p>Separate dashboards for Admin, Professors, and Students with appropriate permissions.</p>
            </div>
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3>Analytics Dashboard</h3>
                <p>Admins can view library statistics, borrowing trends, and manage the entire system.</p>
            </div>
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3>Bulk Requests</h3>
                <p>Professors can request books on behalf of their students in bulk with a single action.</p>
            </div>
            <div class="feature-card">
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure System</h3>
                <p>Built with prepared statements, session protection, and input validation for maximum security.</p>
            </div>
        </section>

        <!-- Footer -->
        <footer class="landing-footer">
            <p>&copy; <?php echo date('Y'); ?> Fr. CRCE, Bandra, Mumbai. Digital Library Management System.</p>
        </footer>
    </div>

    <script src="assets/js/demo-backend.js"></script>
    <script>
        const APP_CONFIG = {
            demoMode: <?php echo DEMO_MODE ? 'true' : 'false'; ?>
        };

        if (APP_CONFIG.demoMode) {
            // If already logged in via demo, redirect
            if (DemoBackend.isLoggedIn()) {
                const s = DemoBackend.getSession();
                window.location.href = s.role + '/dashboard.php';
            }
            
            // Fix links in demo mode to point to .php copies
            document.querySelectorAll('a[href$=".html"]').forEach(link => {
                link.href = link.href.replace('.html', '.php');
            });
        }
    </script>
</body>
</html>
