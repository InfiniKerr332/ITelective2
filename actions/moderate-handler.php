<?php
/**
 * WMSU ARL Hub: Admin Moderation Handler
 */
require_once '../config/auth.php';
checkAuth('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $adminId = $_SESSION['user_id'];

    try {
        // Get contributor and title info first
        $infoStmt = $pdo->prepare("SELECT contributor_id, title FROM materials WHERE id = ?");
        $infoStmt->execute([$id]);
        $material = $infoStmt->fetch();

        if (!$material) {
            header("Location: ../admin/admin-moderation.php?error=notfound");
            exit();
        }

        $contributorId = $material['contributor_id'];
        $title = $material['title'];

        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE materials SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            
            // Create notification only if we have a contributor
            if ($contributorId) {
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'upload_approved', ?)");
                $notifStmt->execute([
                    $contributorId,
                    "Material Approved!",
                    "Your material '$title' has been approved and is now live.",
                    "core/material-details.php?id=$id"
                ]);
            }

            logAudit($pdo, $adminId, "MODERATE", "Approved material ID: $id");
            header("Location: ../admin/admin-moderation.php?msg=approved");
        } else if ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE materials SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$id]);

            // Create notification only if we have a contributor
            if ($contributorId) {
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'upload_rejected', ?)");
                $notifStmt->execute([
                    $contributorId,
                    "Material Rejected",
                    "Your material '$title' was reviewed and rejected by the moderator.",
                    "core/material-details.php?id=$id"
                ]);
            }

            logAudit($pdo, $adminId, "MODERATE", "Rejected material ID: $id");
            header("Location: ../admin/admin-moderation.php?msg=rejected");
        }
        exit();
    } catch (Exception $e) {
        $error = "Moderation Error: " . $e->getMessage();
        header("Location: ../admin/admin-moderation.php?error=" . urlencode($error));
        exit();
    }

}
header("Location: ../admin/admin-moderation.php");
exit();
?>
