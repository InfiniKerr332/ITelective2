<?php
/**
 * WMSU ARL Hub: Secure Download Handler
 */
require_once '../config/auth.php';
checkAuth();

$id = (int)($_GET['id'] ?? 0);
$fileId = (int)($_GET['file_id'] ?? 0);
$userId = $_SESSION['user_id'];

try {
    if ($fileId > 0) {
        // Fetch specific file
        $stmt = $pdo->prepare("SELECT mf.*, m.status FROM material_files mf JOIN materials m ON mf.material_id = m.id WHERE mf.id = ?");
        $stmt->execute([$fileId]);
        $fileData = $stmt->fetch();
        
        if (!$fileData || $fileData['status'] === 'rejected') {
            header("Location: ../core/browse.php?error=notfound");
            exit();
        }
        $dbPath = $fileData['file_path'];
        $materialId = $fileData['material_id'];
    } else {
        // Fetch Material primary file
        $stmt = $pdo->prepare("SELECT * FROM materials WHERE id = ?");
        $stmt->execute([$id]);
        $material = $stmt->fetch();

        if (!$material || $material['status'] === 'rejected') {
            header("Location: ../core/browse.php?error=notfound");
            exit();
        }
        $dbPath = $material['file_path'];
        $materialId = $id;
    }

    $fullPath = '../' . $dbPath;
    if (!file_exists($fullPath)) {
        header("Location: ../core/browse.php?error=notfound");
        exit();
    }

    // Record Download
    $stmt = $pdo->prepare("INSERT INTO downloads (user_id, material_id) VALUES (?, ?)");
    $stmt->execute([$userId, $materialId]);

    // Update download count
    $pdo->prepare("UPDATE materials SET downloads_count = downloads_count + 1 WHERE id = ?")->execute([$materialId]);

    // Fetch material title for logging
    $stmtM = $pdo->prepare("SELECT title FROM materials WHERE id = ?");
    $stmtM->execute([$materialId]);
    $materialTitle = $stmtM->fetchColumn();

    // Log Audit
    logAudit($pdo, $userId, "DOWNLOAD", "Downloaded resource: " . $materialTitle . " (File: " . basename($dbPath) . ")");

    // Serve File
    $fileName = basename($fullPath);
    $fileSize = filesize($fullPath);
    $mimeType = mime_content_type($fullPath);

    header("Content-Type: " . $mimeType);
    header("Content-Disposition: attachment; filename=\"" . $fileName . "\"");
    header("Content-Length: " . $fileSize);
    header("Pragma: no-cache");
    header("Expires: 0");

    readfile($fullPath);
    exit();

} catch (PDOException $e) {
    header("Location: ../core/browse.php?error=db");
    exit();
}
?>
