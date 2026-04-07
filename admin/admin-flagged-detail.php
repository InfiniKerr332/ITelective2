<?php
/**
 * WMSU ARL Hub: Admin Flagged Material Detail — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('admin');

$materialId = (int)($_GET['id'] ?? 0);
if (!$materialId) { header("Location: admin-moderation.php"); exit(); }

try {
    $mat = $pdo->prepare("SELECT m.*, u.full_name AS contributor, u.email AS contributor_email, u.role AS contributor_role FROM materials m LEFT JOIN users u ON m.contributor_id = u.id WHERE m.id = ?");
    $mat->execute([$materialId]); $material = $mat->fetch();
    if (!$material) { header("Location: admin-moderation.php"); exit(); }

    $repStmt = $pdo->prepare("SELECT rp.*, u.full_name AS reporter_name, u.email AS reporter_email FROM reports rp JOIN users u ON rp.reporter_id = u.id WHERE rp.material_id = ? ORDER BY rp.created_at DESC");
    $repStmt->execute([$materialId]); $reports = $repStmt->fetchAll();

    $revStmt = $pdo->prepare("SELECT rv.*, u.full_name FROM reviews rv JOIN users u ON rv.user_id = u.id WHERE rv.material_id = ? ORDER BY rv.created_at DESC LIMIT 5");
    $revStmt->execute([$materialId]); $reviews = $revStmt->fetchAll();

    $fileStmt = $pdo->prepare("SELECT * FROM material_files WHERE material_id = ?");
    $fileStmt->execute([$materialId]); $materialFiles = $fileStmt->fetchAll();

    // Legacy fallback
    if (empty($materialFiles) && !empty($material['file_path'])) {
        $materialFiles[] = [
            'id' => 0,
            'file_path' => $material['file_path'],
            'file_name' => basename($material['file_path']),
            'file_type' => strtolower(pathinfo($material['file_path'], PATHINFO_EXTENSION))
        ];
    }
} catch (PDOException $e) { $material=[]; $reports=[]; $reviews=[]; $materialFiles=[]; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    // Identify contributor and title for notifications
    $infoStmt = $pdo->prepare("SELECT contributor_id, title FROM materials WHERE id = ?");
    $infoStmt->execute([$materialId]);
    $matData = $infoStmt->fetch();
    $contributorId = $matData['contributor_id'] ?? 0;
    $matTitle = $matData['title'] ?? 'your material';

    if ($action === 'approve') { 
        $pdo->prepare("UPDATE materials SET status='approved' WHERE id=?")->execute([$materialId]); 
        
        // Notify
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'upload_approved', ?)");
        $notifStmt->execute([
            $contributorId,
            "Flag Review: Approved",
            "Your material '$matTitle' was reviewed by admins following reports; it has been approved and kept live.",
            "core/material-details.php?id=$materialId"
        ]);

        logAudit($pdo, $_SESSION['user_id'], 'approve', "Approved material ID $materialId after report review."); 
    }
    elseif ($action === 'reject') { 
        $pdo->prepare("UPDATE materials SET status='rejected' WHERE id=?")->execute([$materialId]); 

        // Notify
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'upload_rejected', ?)");
        $notifStmt->execute([
            $contributorId,
            "Flag Review: Rejected",
            "Your material '$matTitle' was reviewed and rejected based on user reports.",
            "core/material-details.php?id=$materialId"
        ]);

        logAudit($pdo, $_SESSION['user_id'], 'reject', "Rejected material ID $materialId."); 
    }
    elseif ($action === 'remove') { 
        // Before removal notify
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, 'upload_rejected', ?)");
        $notifStmt->execute([
            $contributorId,
            "Material Removed",
            "Your material '$matTitle' has been permanently removed due to policy violations.",
            "student/dashboard.php"
        ]);

        $pdo->prepare("DELETE FROM materials WHERE id=?")->execute([$materialId]); 
        logAudit($pdo, $_SESSION['user_id'], 'delete', "Removed flagged material ID $materialId."); header("Location: admin-moderation.php"); exit(); 
    }
    if (isset($_POST['dismiss_report'])) { $pdo->prepare("DELETE FROM reports WHERE id=?")->execute([(int)$_POST['dismiss_report']]); }
    header("Location: admin-flagged-detail.php?id=$materialId"); exit();
}

$reasonCounts = array_count_values(array_column($reports, 'reason'));
arsort($reasonCounts);
$pillColors = ['approved'=>'bg-emerald-50 text-emerald-700','pending'=>'bg-amber-50 text-amber-700','rejected'=>'bg-red-50 text-red-700'];
$pill = $pillColors[$material['status']] ?? 'bg-gray-100 text-gray-600';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flagged Material Review - WMSU ARL Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#B81C2E',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F4F4F6; }
        h1,h2,h3 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; vertical-align: middle; }
    </style>
</head>
<body class="text-[#1A1A2E]">
<?php require_once '../includes/dashboard-nav.php'; ?>
<div class="flex min-h-[calc(100vh-64px)]">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="ml-[240px] flex-1 bg-[#F4F4F6] flex flex-col">
        <div class="p-8 flex-1">

            <a href="admin-moderation.php" class="inline-flex items-center gap-2 text-sm font-semibold text-[#4A4A5A] hover:text-[#1A1A2E] mb-6 transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Moderation
            </a>

            <?php if (count($reports) > 0): ?>
            <div class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-6 text-sm text-red-700 font-medium">
                <span class="material-symbols-outlined text-[18px]">warning</span>
                This material has been flagged by <strong class="font-bold"><?php echo count($reports); ?> user<?php echo count($reports) !== 1 ? 's' : ''; ?></strong>. Review the reports below before taking action.
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-[1.4fr_1fr] gap-6 items-start">
                <!-- Left Column -->
                <div class="space-y-5">

                    <!-- Material Info -->
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                        <div class="flex items-start gap-5 pb-5 mb-5 border-b border-[#F4F4F6]">
                            <div class="w-14 h-14 bg-[#F9E8EA] text-[#B81C2E] rounded-2xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[26px]">description</span>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[#1A1A2E] leading-snug mb-2"><?php echo htmlspecialchars($material['title']); ?></h2>
                                <div class="flex flex-wrap gap-2 items-center">
                                    <span class="text-xs text-[#4A4A5A] font-medium"><?php echo htmlspecialchars($material['category']); ?></span>
                                    <span class="text-[#D1D1D9]">·</span>
                                    <span class="text-xs text-[#4A4A5A] font-medium"><?php echo htmlspecialchars($material['contributor'] ?? 'Unknown'); ?></span>
                                    <span class="text-[#D1D1D9]">·</span>
                                    <span class="text-xs text-[#4A4A5A]"><?php echo date('M d, Y', strtotime($material['created_at'])); ?></span>
                                    <span class="<?php echo $pill; ?> text-[9px] font-bold px-2 py-0.5 rounded-full uppercase"><?php echo $material['status']; ?></span>
                                    <?php if ($material['is_official']): ?>
                                    <span class="bg-[#F9E8EA] text-[#B81C2E] text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Official</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if ($material['description']): ?>
                        <p class="text-sm text-[#4A4A5A] leading-relaxed mb-4"><?php echo htmlspecialchars($material['description']); ?></p>
                        <?php endif; ?>
                        <div class="flex gap-5 text-sm text-[#4A4A5A] font-medium mb-6">
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[#B81C2E] text-[14px]">download</span><?php echo number_format($material['downloads_count']); ?> downloads</span>
                            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[#B81C2E] text-[14px]">visibility</span><?php echo number_format($material['views_count'] ?? 0); ?> views</span>
                        </div>

                        <!-- Associated Files List -->
                        <div class="mt-6 pt-6 border-t border-[#F4F4F6]">
                            <h3 class="text-xs font-bold text-[#1A1A2E] uppercase tracking-widest mb-4">Content for Review (<?php echo count($materialFiles); ?>)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($materialFiles as $file): ?>
                                <?php 
                                    $isImage = in_array(strtolower($file['file_type']), ['jpg', 'jpeg', 'png', 'webp']);
                                    $fileUrl = BASE_URL . $file['file_path'];
                                    $downloadUrl = BASE_URL . "actions/download-handler.php?" . ($file['id'] > 0 ? "file_id=" . $file['id'] : "id=" . $materialId);
                                ?>
                                <div class="bg-[#F9F9FB] rounded-xl border border-black/[0.04] p-3 flex items-center gap-3 group">
                                    <div class="w-10 h-10 rounded-lg bg-white border border-black/5 flex items-center justify-center shrink-0 overflow-hidden">
                                        <?php if ($isImage): ?>
                                            <img src="<?php echo $fileUrl; ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="material-symbols-outlined text-primary text-[18px]">description</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[11px] font-bold text-[#1A1A2E] truncate" title="<?php echo htmlspecialchars($file['file_name']); ?>"><?php echo htmlspecialchars($file['file_name']); ?></div>
                                        <div class="text-[9px] text-[#9CA3AF] font-bold uppercase tracking-widest"><?php echo strtoupper($file['file_type']); ?></div>
                                    </div>
                                    <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="<?php echo $fileUrl; ?>" target="_blank" class="w-7 h-7 bg-white rounded flex items-center justify-center text-[#4A4A5A] hover:text-primary transition-colors border border-black/5 shadow-sm">
                                            <span class="material-symbols-outlined text-[14px]">visibility</span>
                                        </a>
                                        <a href="<?php echo $downloadUrl; ?>" class="w-7 h-7 bg-white rounded flex items-center justify-center text-[#4A4A5A] hover:text-primary transition-colors border border-black/5 shadow-sm">
                                            <span class="material-symbols-outlined text-[14px]">download</span>
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Report Summary -->
                    <?php if (!empty($reasonCounts)): ?>
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                        <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Report Summary</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($reasonCounts as $reason => $count): ?>
                            <span class="flex items-center gap-1.5 bg-red-50 text-red-700 text-xs font-bold px-3 py-1.5 rounded-full">
                                <span class="material-symbols-outlined text-[12px]">flag</span>
                                <?php echo htmlspecialchars($reason); ?> (<?php echo $count; ?>)
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Individual Reports -->
                    <?php if (!empty($reports)): ?>
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                        <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Individual Reports (<?php echo count($reports); ?>)</h3>
                        <div class="space-y-3">
                            <?php foreach ($reports as $r): ?>
                            <div class="bg-[#F9F9FB] rounded-xl p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-[10px] font-bold uppercase bg-red-50 text-red-700 px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($r['reason'] ?? 'Other'); ?></span>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to dismiss this report?');">
                                        <input type="hidden" name="dismiss_report" value="<?php echo $r['id']; ?>">
                                        <button type="submit" class="text-xs text-[#4A4A5A] font-medium hover:text-[#1A1A2E] transition-colors">Dismiss</button>
                                    </form>
                                </div>
                                <?php if ($r['description'] ?? ''): ?>
                                <p class="text-xs text-[#4A4A5A] leading-relaxed mb-2"><?php echo htmlspecialchars($r['description']); ?></p>
                                <?php endif; ?>
                                <div class="flex items-center gap-2 text-xs text-[#9CA3AF]">
                                    <div class="w-4 h-4 bg-[#B81C2E] rounded flex items-center justify-center text-white text-[8px] font-bold">
                                        <?php echo strtoupper(substr($r['reporter_name'] ?? '?', 0, 1)); ?>
                                    </div>
                                    <span class="font-medium"><?php echo htmlspecialchars($r['reporter_name']); ?></span>
                                    <span>·</span>
                                    <span><?php echo date('M d, Y', strtotime($r['created_at'])); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Admin Action Panel -->
                <div class="sticky top-6 space-y-4">
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                        <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Admin Decision</h3>
                        <form method="POST" class="space-y-3">
                            <button type="submit" name="action" value="approve"
                                    class="w-full flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 py-3 rounded-xl font-semibold text-sm hover:bg-emerald-100 transition-colors border border-emerald-100"
                                    onclick="return confirm('Are you sure you want to approve this flagged material?');">
                                <span class="material-symbols-outlined text-[17px]">check_circle</span> Approve & Keep Live
                            </button>
                            <button type="submit" name="action" value="reject"
                                    class="w-full flex items-center justify-center gap-2 bg-amber-50 text-amber-700 py-3 rounded-xl font-semibold text-sm hover:bg-amber-100 transition-colors border border-amber-100"
                                    onclick="return confirm('Are you sure you want to reject and hide this material?');">
                                <span class="material-symbols-outlined text-[17px]">pause_circle</span> Reject (Hide from Public)
                            </button>
                            <button type="submit" name="action" value="remove"
                                    class="w-full flex items-center justify-center gap-2 bg-red-50 text-red-700 py-3 rounded-xl font-semibold text-sm hover:bg-red-100 transition-colors border border-red-100"
                                    onclick="return confirm('Permanently delete this material? This cannot be undone.')">
                                <span class="material-symbols-outlined text-[17px]">delete</span> Remove Permanently
                            </button>
                        </form>
                        <p class="text-center text-xs text-[#9CA3AF] mt-4">All actions are logged and attributed to your account.</p>
                    </div>
                    <a href="<?php echo BASE_URL; ?>core/material-details.php?id=<?php echo $materialId; ?>" target="_blank"
                       class="flex items-center justify-center gap-2 bg-white border border-[#E2E2E4] text-[#4A4A5A] py-3 rounded-xl font-semibold text-sm hover:border-[#1A1A2E] transition-colors">
                        <span class="material-symbols-outlined text-[16px]">open_in_new</span> View Public Page
                    </a>
                </div>
            </div>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

