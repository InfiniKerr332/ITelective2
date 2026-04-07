<?php
/**
 * WMSU ARL Hub: Notifications — Unified View
 */
require_once '../config/auth.php';
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

// Fetch all notifications for this user
try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $all_notifications = $stmt->fetchAll();
} catch (PDOException $e) { $all_notifications = []; }

// Helper for icons
function getNotifIcon($type) {
    switch($type) {
        case 'upload_approved': return 'check_circle';
        case 'upload_rejected': return 'cancel';
        case 'new_review': return 'star';
        case 'new_report': return 'flag';
        default: return 'notifications';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - WMSU ARL Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#B81C2E' } } }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F4F4F6; }
        h1,h2 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; vertical-align: middle; }
    </style>
</head>
<body class="text-[#1A1A2E]">
<?php require_once '../includes/dashboard-nav.php'; ?>
<div class="flex min-h-[calc(100vh-64px)]">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="ml-[240px] flex-1 flex flex-col">
        <div class="p-8 flex-1">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">Your Notifications</h1>
                    <p class="text-[#4A4A5A] mt-1">Stay updated with your activities and materials.</p>
                </div>
                <button onclick="markAllRead()" class="text-xs font-bold text-primary uppercase tracking-widest hover:underline">Mark all as read</button>
            </div>

            <div class="bg-white rounded-2xl border border-black/[0.06] overflow-hidden">
                <?php if (empty($all_notifications)): ?>
                    <div class="py-24 text-center">
                        <div class="w-16 h-16 bg-[#F9F9FB] rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-[#D1D1D9] text-[32px]">notifications_off</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#1A1A2E]">No notifications</h3>
                        <p class="text-sm text-[#9CA3AF]">When you receive updates, they will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="divide-y divide-[#F4F4F6]">
                        <?php foreach ($all_notifications as $n): ?>
                            <a href="<?php echo BASE_URL; ?>actions/notification-handler.php?action=read&id=<?php echo $n['id']; ?>&link=<?php echo urlencode($n['link'] ?? '#'); ?>" 
                               class="flex items-center gap-6 p-6 hover:bg-[#F9F9FB] transition-colors <?php echo $n['is_read'] ? 'opacity-50' : ''; ?>">
                                <div class="w-12 h-12 bg-[#F9E8EA] text-primary rounded-2xl flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[24px]"><?php echo getNotifIcon($n['type']); ?></span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1">
                                        <h4 class="font-bold text-[#1A1A2E] leading-tight"><?php echo htmlspecialchars($n['title']); ?></h4>
                                        <span class="text-[11px] font-medium text-[#9CA3AF]"><?php echo date('M d, Y • g:i A', strtotime($n['created_at'])); ?></span>
                                    </div>
                                    <p class="text-sm text-[#4A4A5A] leading-relaxed"><?php echo htmlspecialchars($n['message']); ?></p>
                                </div>
                                <?php if (!$n['is_read']): ?>
                                    <div class="w-2 h-2 bg-primary rounded-full shrink-0"></div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>

<script>
    function markAllRead() {
        window.location.href = '<?php echo BASE_URL; ?>actions/notification-handler.php?action=mark_all_read';
    }
</script>
</body>
</html>
