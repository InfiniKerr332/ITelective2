<?php
/**
 * WMSU ARL Hub: Notification Actions Handler
 */
require_once '../config/auth.php';

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    header("Location: ../auth/login.php");
    exit();
}

if ($action === 'read') {
    $notif_id = (int)($_GET['id'] ?? 0);
    $link = $_GET['link'] ?? '#';

    if ($notif_id > 0) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notif_id, $user_id]);
    }
    
    // Fix: Prepend BASE_URL if the link is relative and doesn't already have it
    if (!empty($link) && $link !== '#' && !str_starts_with($link, 'http') && !str_starts_with($link, '/')) {
        $link = BASE_URL . $link;
    }
    
    header("Location: " . $link);
    exit();
}

if ($action === 'mark_all_read') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Redirect back to referring page
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}

header("Location: ../index.php");
exit();
