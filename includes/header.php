<?php
/**
 * ============================================================
 * Header Include
 * ============================================================
 * This file renders the top navigation bar.
 * Include at the top of every dashboard page.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Fr. CRCE Library Management System - Digital Library Portal">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | ' : ''; ?>Fr. CRCE Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="topbar" id="topbar">
        <div class="topbar-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="brand">
                <i class="fas fa-book-open brand-icon"></i>
                <span class="brand-text">Fr. CRCE Library</span>
            </div>
        </div>
        <div class="topbar-right">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                    <span class="user-role"><?php echo ucfirst(htmlspecialchars($_SESSION['role'] ?? 'guest')); ?></span>
                </div>
            </div>
            <a href="<?php echo BASE_URL; ?>auth/logout.php" class="logout-btn" id="logoutBtn" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
