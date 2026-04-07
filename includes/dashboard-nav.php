<?php
/**
 * WMSU ARL Hub: Dashboard Top Navbar v4
 * Working search form, notification link, profile dropdown
 */
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/paths.php';
}

$current_role  = $_SESSION['user_role'] ?? 'student';
$user_name     = $_SESSION['full_name'] ?? $_SESSION['user_name'] ?? 'Scholar';
$notifUrl = match($current_role) {
    'faculty' => BASE_URL . 'faculty/faculty-notifications.php',
    default   => BASE_URL . 'core/notifications.php',
};
?>

<style>
    .dashboard-top-nav {
        height: var(--header-height, 64px);
        width: calc(100% - var(--sidebar-width, 240px));
        background: #1A1A2E; 
        border-bottom: 1px solid rgba(255,255,255,0.05);
        position: fixed;
        right: 0;
        top: 0;
        z-index: 800;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 40px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .search-interface {
        flex: 1;
        max-width: 480px;
        position: relative;
        transition: transform 0.3s ease;
    }

    .search-interface:focus-within {
        transform: translateX(8px);
    }

    .search-interface input {
        width: 100%;
        height: 40px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        padding: 0 16px 0 40px;
        font-size: 14px;
        font-weight: 500;
        color: white;
        outline: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Inter', sans-serif;
    }
    
    .search-interface input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
        color: rgba(255, 255, 255, 0.5);
        pointer-events: none;
        transition: color 0.3s ease;
    }

    .search-interface input:focus {
        border-color: #B81C2E;
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 24px -10px rgba(184, 28, 46, 0.2);
    }

    .search-interface input:focus + .search-icon {
        color: #B81C2E;
    }

    .user-profile-box {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .notification-btn {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        text-decoration: none;
    }

    .notification-btn:hover {
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transform: translateY(-2px) rotate(8deg);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .notification-dot {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 8px;
        height: 8px;
        background: #B81C2E;
        border-radius: 50%;
        border: 2px solid #1A1A2E;
    }

    .profile-pill {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 5px 16px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid transparent;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }

    .profile-pill:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 12px 24px -8px rgba(0,0,0,0.2);
        transform: translateY(-1px);
    }

    .user-info-text {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .user-info-text .name {
        font-size: 13px;
        font-weight: 800;
        color: white;
        letter-spacing: -0.02em;
    }

    .user-info-text .role {
        font-size: 10px;
        font-weight: 700;
        color: #B81C2E;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<nav class="dashboard-top-nav">
    <form action="<?php echo BASE_URL; ?>core/browse.php" method="GET" class="search-interface">
        <span class="material-symbols-outlined search-icon">search</span>
        <input type="text" name="q" placeholder="Search materials, topics, authors..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
    </form>

    <div class="user-profile-box">
        <a href="<?php echo $notifUrl; ?>" class="notification-btn">
            <span class="material-symbols-outlined">notifications</span>
            <?php
            try {
                $unreadNavStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                $unreadNavStmt->execute([$_SESSION['user_id']]);
                if ($unreadNavStmt->fetchColumn() > 0):
            ?>
            <div class="notification-dot"></div>
            <?php endif; } catch (Exception $e) {} ?>
        </a>

        <a href="<?php echo BASE_URL; ?>student/profile.php" class="profile-pill">
            <div style="width: 32px; height: 32px; background: #B81C2E; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 800; box-shadow: 0 4px 12px rgba(184, 28, 46, 0.2);">
                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
            </div>
            <div class="user-info-text">
                <span class="name"><?php echo htmlspecialchars($user_name); ?></span>
                <span class="role">@<?php echo strtoupper($current_role); ?></span>
            </div>
            <span class="material-symbols-outlined" style="font-size: 18px; color: #848494; margin-left: 4px;">expand_more</span>
        </a>
    </div>
</nav>
