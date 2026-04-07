<?php
/**
 * WMSU ARL Hub: Report Material Handler
 */
require_once '../config/auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materialId = (int)$_POST['material_id'];
    $reason = trim($_POST['reason']);
    $userId = $_SESSION['user_id'];

    if (empty($reason)) {
        header("Location: ../core/material-details.php?id=$materialId&error=Reason is required");
        exit();
    }

    try {
        // Insert into reports table
        $stmt = $pdo->prepare("INSERT INTO reports (material_id, reporter_id, reason) VALUES (?, ?, ?)");
        $stmt->execute([$materialId, $userId, $reason]);

        // Get material title for notification
        $stmt = $pdo->prepare("SELECT title FROM materials WHERE id = ?");
        $stmt->execute([$materialId]);
        $materialTitle = $stmt->fetchColumn();

        // Notify Admins
        $adminStmt = $pdo->query("SELECT id FROM users WHERE role = 'admin'");
        $admins = $adminStmt->fetchAll();
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'new_report', ?)");
        foreach ($admins as $admin) {
            $notifStmt->execute([
                $admin['id'],
                "New Material Report",
                "The material '$materialTitle' has been reported for: $reason",
                "admin/admin-flagged-detail.php?id=$materialId"
            ]);
        }

        logAudit($pdo, $userId, "REPORT", "Reported material ID: $materialId");
        header("Location: ../core/material-details.php?id=$materialId&success=Report submitted successfully. Administrators will review it.");
        exit();
    } catch (PDOException $e) {
        header("Location: ../core/material-details.php?id=$materialId&error=Database error: " . $e->getMessage());
        exit();
    }
} else {
    header("Location: ../core/browse.php");
    exit();
}
