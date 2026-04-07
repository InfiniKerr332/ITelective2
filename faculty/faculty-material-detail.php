<?php
/**
 * WMSU ARL Hub: Faculty Material Detail (Review Workflow) — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('faculty');

$materialId = (int)($_GET['id'] ?? 0);
if (!$materialId) { header("Location: faculty-dashboard.php"); exit(); }

try {
    $mat = $pdo->prepare("SELECT m.*, u.full_name AS contributor, u.email AS contributor_email, u.role AS contributor_role FROM materials m LEFT JOIN users u ON m.contributor_id = u.id WHERE m.id = ?");
    $mat->execute([$materialId]); $material = $mat->fetch();
    if (!$material) { header("Location: faculty-dashboard.php"); exit(); }

    $revStmt = $pdo->prepare("SELECT rv.*, u.full_name FROM reviews rv JOIN users u ON rv.user_id = u.id WHERE rv.material_id = ? ORDER BY rv.created_at DESC");
    $revStmt->execute([$materialId]); $reviews = $revStmt->fetchAll();

    $ratingStmt = $pdo->prepare("SELECT ROUND(AVG(rating),1) FROM reviews WHERE material_id = ?");
    $ratingStmt->execute([$materialId]); $avgRating = $ratingStmt->fetchColumn();

    $repStmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE material_id = ?");
    $repStmt->execute([$materialId]); $reportCount = $repStmt->fetchColumn();
} catch (PDOException $e) { header("Location: faculty-dashboard.php"); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $comment = trim($_POST['comment'] ?? '');
    if ($action === 'approve') {
        $pdo->prepare("UPDATE materials SET status='approved' WHERE id=?")->execute([$materialId]);
        logAudit($pdo, $_SESSION['user_id'], 'approve', "Approved material: ".$material['title']);
        try { $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?,?,?,?,?)")->execute([$material['contributor_id'], 'Material Approved!', 'Your material "'.$material['title'].'" has been approved and is now live.', 'upload_approved', 'material-details.php?id='.$materialId]); } catch (Exception $e) {}
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE materials SET status='rejected' WHERE id=?")->execute([$materialId]);
        logAudit($pdo, $_SESSION['user_id'], 'reject', "Rejected material: ".$material['title'].($comment?" | Reason: $comment":""));
        try { $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, link) VALUES (?,?,?,?,?)")->execute([$material['contributor_id'], 'Material Not Approved', 'Your material "'.$material['title'].'" was not approved.'.($comment?" Reason: $comment":''), 'upload_rejected', 'my-uploads.php']); } catch (Exception $e) {}
    }
    header("Location: faculty-material-detail.php?id=$materialId"); exit();
}

$pillColors = ['approved'=>'bg-emerald-50 text-emerald-700','pending'=>'bg-amber-50 text-amber-700','rejected'=>'bg-red-50 text-red-700'];
$pill = $pillColors[$material['status']] ?? 'bg-gray-100 text-gray-600';
$avatarColors = ['admin'=>'bg-[#B81C2E]','faculty'=>'bg-emerald-600','student'=>'bg-indigo-500'];
$avColor = $avatarColors[$material['contributor_role']] ?? 'bg-gray-400';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Material - WMSU ARL Hub</title>
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
        :root { --sidebar-width: 240px; --header-height: 64px; --wmsu-black: #1A1A2E; --wmsu-red: #B81C2E; --wmsu-red-dark: #8C1222; --bg-base: #F4F4F6; --border-light: rgba(0,0,0,0.05); --text-primary: #1A1A28; --text-secondary: #4A4A5A; --text-muted: #848494; --radius-8: 8px; --shadow-sm: 0 2px 8px rgba(0,0,0,0.05); --shadow-md: 0 12px 24px -8px rgba(0,0,0,0.1); --shadow-red: 0 12px 24px -8px rgba(184,28,46,0.4); }
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

            <a href="faculty-dashboard.php" class="inline-flex items-center gap-2 text-sm font-semibold text-[#4A4A5A] hover:text-[#1A1A2E] mb-6 transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to Dashboard
            </a>

            <div class="grid grid-cols-[1.5fr_1fr] gap-6 items-start">
                <!-- Left: Material Details -->
                <div class="space-y-5">

                    <!-- Main Info -->
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                        <div class="flex items-start gap-5 pb-5 mb-5 border-b border-[#F4F4F6]">
                            <div class="w-14 h-14 bg-[#F9E8EA] text-[#B81C2E] rounded-2xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-[26px]">description</span>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[#1A1A2E] leading-snug mb-2"><?php echo htmlspecialchars($material['title']); ?></h2>
                                <div class="flex flex-wrap gap-2 items-center">
                                    <span class="text-[10px] font-bold uppercase bg-[#F4F4F6] text-[#4A4A5A] px-2.5 py-1 rounded-full"><?php echo htmlspecialchars($material['category']); ?></span>
                                    <span class="<?php echo $pill; ?> text-[9px] font-bold px-2 py-0.5 rounded-full uppercase"><?php echo $material['status']; ?></span>
                                    <?php if ($material['is_official']): ?>
                                    <span class="bg-[#F9E8EA] text-[#B81C2E] text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Official</span>
                                    <?php endif; ?>
                                    <?php if ($reportCount > 0): ?>
                                    <span class="bg-amber-50 text-amber-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase"><?php echo $reportCount; ?> Report<?php echo $reportCount !== 1 ? 's' : ''; ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-xs text-[#9CA3AF] mt-2">Submitted <?php echo date('F d, Y', strtotime($material['created_at'])); ?></p>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-3 mb-5">
                            <?php foreach ([
                                [number_format($material['downloads_count']), 'Downloads'],
                                [number_format($material['views_count'] ?? 0), 'Views'],
                                [$avgRating ? '★ '.$avgRating : '—', 'Avg Rating'],
                            ] as $s): ?>
                            <div class="bg-[#F9F9FB] rounded-xl p-3.5 text-center">
                                <div class="text-lg font-bold text-[#1A1A2E]"><?php echo $s[0]; ?></div>
                                <div class="text-[9px] font-bold uppercase tracking-wide text-[#9CA3AF]"><?php echo $s[1]; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($material['description']): ?>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wide text-[#4A4A5A] mb-2">Description</div>
                            <p class="text-sm text-[#4A4A5A] leading-relaxed"><?php echo nl2br(htmlspecialchars($material['description'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Reviews -->
                    <?php if (!empty($reviews)): ?>
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                        <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Student Reviews (<?php echo count($reviews); ?>)</h3>
                        <div class="space-y-3">
                            <?php foreach ($reviews as $r): ?>
                            <div class="bg-[#F9F9FB] rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="text-amber-400 text-sm">
                                        <?php echo str_repeat('★', (int)$r['rating']); ?><?php echo str_repeat('☆', 5 - (int)$r['rating']); ?>
                                    </div>
                                    <span class="text-xs font-semibold text-[#4A4A5A]"><?php echo htmlspecialchars($r['full_name']); ?></span>
                                </div>
                                <?php if ($r['comment']): ?>
                                <p class="text-xs text-[#4A4A5A] leading-relaxed"><?php echo htmlspecialchars($r['comment']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Right: Actions -->
                <div class="sticky top-6 space-y-4">

                    <!-- Contributor -->
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                        <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Contributor</h3>
                        <div class="flex items-center gap-3 bg-[#F9F9FB] rounded-xl p-4">
                            <div class="w-10 h-10 <?php echo $avColor; ?> rounded-xl flex items-center justify-center text-white font-bold flex-shrink-0">
                                <?php echo strtoupper(substr($material['contributor'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-semibold text-sm text-[#1A1A2E]"><?php echo htmlspecialchars($material['contributor'] ?? 'Unknown'); ?></div>
                                <div class="text-xs text-[#4A4A5A]"><?php echo htmlspecialchars($material['contributor_email'] ?? ''); ?></div>
                                <div class="text-[9px] font-bold uppercase text-[#9CA3AF] mt-0.5"><?php echo $material['contributor_role']; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Decision Panel -->
                    <?php if ($material['status'] === 'pending'): ?>
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                        <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Review Decision</h3>
                        <form method="POST" class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-[#4A4A5A] uppercase tracking-wide mb-1.5">Feedback Comment <span class="text-[#9CA3AF] font-normal normal-case">(optional)</span></label>
                                <textarea name="comment" placeholder="Add feedback for the contributor…" rows="3"
                                          class="w-full px-4 py-3 bg-[#F9F9FB] border border-[#E2E2E4] rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E] resize-none"></textarea>
                            </div>
                            <button type="submit" name="action" value="approve"
                                    class="w-full flex items-center justify-center gap-2 bg-emerald-600 text-white py-3 rounded-xl font-semibold text-sm hover:bg-emerald-700 transition-colors">
                                <span class="material-symbols-outlined text-[17px]">check_circle</span> Approve & Publish
                            </button>
                            <button type="submit" name="action" value="reject"
                                    class="w-full flex items-center justify-center gap-2 bg-red-50 text-red-700 py-3 rounded-xl font-semibold text-sm hover:bg-red-100 transition-colors border border-red-100">
                                <span class="material-symbols-outlined text-[17px]">cancel</span> Reject Submission
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="bg-white rounded-xl border border-black/[0.06] p-6 text-center">
                        <span class="<?php echo $pill; ?> text-[10px] font-bold px-3 py-1.5 rounded-full uppercase inline-block mb-2"><?php echo $material['status']; ?></span>
                        <p class="text-xs text-[#9CA3AF]">No further review action required.</p>
                    </div>
                    <?php endif; ?>

                    <a href="<?php echo BASE_URL; ?>core/material-details.php?id=<?php echo $materialId; ?>" target="_blank"
                       class="flex items-center justify-center gap-2 w-full bg-white border border-[#E2E2E4] text-[#4A4A5A] py-3 rounded-xl font-semibold text-sm hover:border-[#1A1A2E] transition-colors">
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

