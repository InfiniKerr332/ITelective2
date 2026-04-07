<?php
/**
 * WMSU ARL Hub: Role-Aware Sidebar v4
 * Navigation links adapt based on user role
 */
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/paths.php';
}

$current_role  = $_SESSION['user_role'] ?? 'student';
$current_page  = basename($_SERVER['PHP_SELF']);

// Role-based URL mapping
$dashboardUrl = BASE_URL . $current_role . '/' . $current_role . '-dashboard.php';
$uploadsUrl   = match($current_role) {
    'faculty' => BASE_URL . 'faculty/faculty-uploads.php',
    default   => BASE_URL . 'student/my-uploads.php',
};
$profileUrl = BASE_URL . 'student/profile.php';
$historyUrl = BASE_URL . 'student/download-history.php';
$notifUrl   = match($current_role) {
    'faculty' => BASE_URL . 'faculty/faculty-notifications.php',
    default   => BASE_URL . 'core/notifications.php',
};
?>

<style>
    .institutional-sidebar {
        width: var(--sidebar-width, 240px);
        background: var(--wmsu-black, #1A1A2E);
        border-right: 1px solid rgba(255, 255, 255, 0.05);
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        padding-top: 40px;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow-y: auto;
        box-shadow: 12px 0 32px rgba(0,0,0,0.1);
    }

    .sidebar-section { margin-bottom: 8px; }

    .sidebar-label {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        color: rgba(255, 255, 255, 0.35);
        margin-bottom: 12px;
        padding: 0 24px;
        display: block;
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 11px 24px;
        color: rgba(255, 255, 255, 0.6);
        font-size: 13px;
        font-weight: 600;
        transition: all 0.25s ease;
        text-decoration: none;
        border-left: 3px solid transparent;
    }

    .sidebar-link:hover {
        background: rgba(255, 255, 255, 0.05);
        color: white;
    }

    .sidebar-link.active {
        background: rgba(184, 28, 46, 0.1);
        color: #B81C2E;
        border-left-color: #B81C2E;
    }

    .sidebar-link.active .material-symbols-outlined {
        color: #B81C2E;
    }

    .sidebar-link .material-symbols-outlined {
        font-size: 20px;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 24px 28px;
        text-decoration: none;
    }

    .sidebar-brand-icon {
        width: 32px;
        height: 32px;
        background: #B81C2E;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(184, 28, 46, 0.3);
    }

    .sidebar-brand-text {
        color: white;
        font-size: 16px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
</style>

<aside class="institutional-sidebar">
    
    <!-- Branding -->
    <a href="<?php echo BASE_URL; ?>" class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <span class="material-symbols-outlined" style="color: white; font-size: 18px;">auto_stories</span>
        </div>
        <span class="sidebar-brand-text">WMSU ARL Hub</span>
    </a>

    <!-- ── Academic Hub Section ── -->
    <div class="sidebar-section mb-6">
        <span class="sidebar-label">Academic Hub</span>
        <div class="sidebar-links">
            <div class="px-6 py-2 text-white font-bold text-sm tracking-wide bg-white/5 mx-4 rounded-lg border border-white/10">
                WMSU Zamboanga
            </div>
        </div>
    </div>

    <!-- ── Main Menu ── -->
    <div class="sidebar-section">
        <span class="sidebar-label">Main Menu</span>
        <div class="sidebar-links">
            <a href="<?php echo $dashboardUrl; ?>" 
               class="sidebar-link <?php echo str_contains($current_page, 'dashboard') ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">dashboard</span> Dashboard
            </a>
            
            <a href="<?php echo BASE_URL; ?>core/browse.php" 
               class="sidebar-link <?php echo $current_page == 'browse.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">library_books</span> Browse Materials
            </a>
            
            <?php if ($current_role === 'student' || $current_role === 'faculty'): ?>
                <a href="<?php echo $uploadsUrl; ?>" 
                   class="sidebar-link <?php echo in_array($current_page, ['my-uploads.php','faculty-uploads.php','upload.php']) ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">cloud_upload</span> My Uploads
                </a>
            <?php endif; ?>
            
            <?php if ($current_role === 'student'): ?>
                <a href="<?php echo $historyUrl; ?>" 
                   class="sidebar-link <?php echo $current_page == 'download-history.php' ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">history</span> Download History
                </a>
            <?php endif; ?>
            
            <?php if ($current_role === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>admin/admin-users.php" 
                   class="sidebar-link <?php echo str_contains($current_page, 'user') ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">group</span> User Management
                </a>
                <a href="<?php echo BASE_URL; ?>admin/admin-moderation.php" 
                   class="sidebar-link <?php echo str_contains($current_page, 'moderation') ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">shield</span> Content Moderation
                </a>
                <a href="<?php echo BASE_URL; ?>admin/admin-analytics.php" 
                   class="sidebar-link <?php echo str_contains($current_page, 'analytic') ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">analytics</span> System Analytics
                </a>
                <a href="<?php echo BASE_URL; ?>admin/admin-audit.php" 
                   class="sidebar-link <?php echo str_contains($current_page, 'audit') ? 'active' : ''; ?>">
                    <span class="material-symbols-outlined">receipt_long</span> System Logs
                </a>
            <?php endif; ?>
            
            <a href="<?php echo $profileUrl; ?>" class="sidebar-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">person</span> My Profile
            </a>
        </div>
    </div>

    <!-- ── Support ── -->
    <div class="sidebar-section" style="margin-top: auto; margin-bottom: 32px;">
        <span class="sidebar-label">Support</span>
        <div class="sidebar-links">
            <a href="<?php echo BASE_URL; ?>admin/admin-help.php" 
               class="sidebar-link <?php echo $current_page == 'admin-help.php' ? 'active' : ''; ?>">
                <span class="material-symbols-outlined">help</span> Help Center
            </a>
            <a href="<?php echo BASE_URL; ?>auth/logout.php" class="sidebar-link" style="color: rgba(255, 77, 77, 0.8);">
                <span class="material-symbols-outlined">logout</span> Logout
            </a>
        </div>
    </div>

</aside>
