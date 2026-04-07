<?php
/**
 * WMSU ARL Hub: Global Institutional Header v3
 * Standardized Fonts: Inter (Heading & Body)
 * Typography: h1 (64px), h2 (12px), body (16px)
 * Spacing: Base Unit (4), Border Radius (8px)
 * Corrected Nav Links for Institutional Assets
 */
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/paths.php';
}
require_once dirname(__DIR__) . '/config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$user_name  = $_SESSION['user_name'] ?? '';
$user_role  = $_SESSION['user_role'] ?? '';
$current_page = basename($_SERVER['PHP_SELF']);

function isActive($page) {
    global $current_page;
    return $current_page === $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'WMSU ARL Hub - Academic Resource Library'; ?></title>
    
    <!-- Unified Institutional Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Material Symbols for Consistency -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <!-- Centralized Institutional CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">

    <style>
        .institutional-header {
            height: var(--header-height); /* 64px */
            background: var(--wmsu-black);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
        }

        .header-content {
            width: 100%;
            max-width: 1280px;
            padding: 0 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .branding {
            display: flex;
            align-items: center;
            gap: 12px;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .branding:hover {
            transform: translateX(4px);
        }

        .logo-box {
            width: 32px;
            height: 32px;
            background: var(--wmsu-red);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-8);
            box-shadow: 0 4px 12px rgba(184, 28, 46, 0.3);
        }

    .login-btn {
        padding: 10px 24px;
        font-size: 14px;
        font-weight: 700;
        color: #FFFFFF !important;
        background: rgba(255, 255, 255, 0.08);
        border: 1.5px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--radius-8);
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .login-btn:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.4);
        color: white !important;
    }

    .signup-btn {
        padding: 8px 20px;
        font-size: 14px;
        font-weight: 700;
        color: #FFFFFF;
        background: var(--wmsu-red);
        border-radius: var(--radius-8);
        box-shadow: var(--shadow-red);
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .signup-btn:hover {
        background: #9a1827;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(184, 28, 46, 0.3);
    }

        .brand-text {
            color: white;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.04em;
        }

        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .nav-link {
            font-size: 14px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
            padding: 8px 12px;
            border-radius: var(--radius-8);
            position: relative;
            text-decoration: none; /* Removed underlines */
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.08);
            text-decoration: none;
        }

        body {
            padding-top: var(--header-height);
        }
    </style>
</head>
<body>

<nav class="institutional-header">
    <div class="header-content">
        <a href="<?php echo BASE_URL; ?>" class="branding">
            <div class="logo-box">
                <img src="<?php echo BASE_URL; ?>images/logo.png" alt="WMSU logo" style="width: 100%; height: auto; border-radius: inherit;">
            </div>
            <span class="brand-text">ARL Hub</span>
        </a>

        <div class="nav-links">
            <a href="<?php echo BASE_URL; ?>index.php" class="nav-link <?php echo isActive('index.php'); ?>">Home</a>
            <a href="<?php echo BASE_URL; ?>core/browse.php" class="nav-link <?php echo isActive('browse.php'); ?>">Resources</a>
            <a href="<?php echo BASE_URL; ?>core/faculty.php" class="nav-link <?php echo isActive('faculty.php'); ?>">Faculty</a>
            <a href="<?php echo BASE_URL; ?>core/about.php" class="nav-link <?php echo isActive('about.php'); ?>">About</a>
        </div>

        <div class="auth-actions" style="display: flex; gap: 12px; align-items: center;">
            <?php if ($isLoggedIn): ?>
                <a href="<?php echo BASE_URL . $_SESSION['user_role'] . '/' . $_SESSION['user_role']; ?>-dashboard.php" class="signup-btn" style="padding: 10px 24px;">Dashboard</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>auth/login.php" class="login-btn">Log In</a>
                <a href="<?php echo BASE_URL; ?>auth/register.php" class="signup-btn" style="padding: 10px 24px;">Sign up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
