<?php
/**
 * WMSU ARL Hub: Review Submission Handler
 */
require_once '../config/auth.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $materialId = $_POST['material_id'] ?? 0;
    $rating = $_POST['rating'] ?? 5;
    $comment = $_POST['comment'] ?? '';
    $userId = $_SESSION['user_id'];

    if ($materialId > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (material_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$materialId, $userId, $rating, $comment]);

            // Get material info for notification
            $infoStmt = $pdo->prepare("SELECT contributor_id, title FROM materials WHERE id = ?");
            $infoStmt->execute([$materialId]);
            $material = $infoStmt->fetch();

            if ($material && $material['contributor_id'] != $userId) {
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'new_review', ?)");
                $notifStmt->execute([
                    $material['contributor_id'],
                    "New Review Received",
                    "Someone shared their professional insight on '{$material['title']}'.",
                    "core/material-details.php?id=$materialId"
                ]);
            }

            // Log Audit
            logAudit($pdo, $userId, "REVIEW", "Added a review for material ID: " . $materialId);

            header("Location: ../core/material-details.php?id=" . $materialId . "&success=review_added");
            exit();
        } catch (PDOException $e) {
            header("Location: ../core/material-details.php?id=" . $materialId . "&error=review_failed");
            exit();
        }
    }
}

header("Location: ../core/browse.php");
exit();
?>
