<?php
/**
 * WMSU ARL Hub: Faculty Notifications — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('faculty');

$userId = $_SESSION['user_id'];

if (isset($_POST['mark_read'])) {
    try { $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?")->execute([$_POST['mark_read'], $userId]); } catch (PDOException $e) {}
    header("Location: faculty-notifications.php"); exit();
}

if (isset($_POST['mark_all_read'])) {
    try { $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]); } catch (PDOException $e) {}
    header("Location: faculty-notifications.php"); exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();
    $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $unreadStmt->execute([$userId]);
    $unreadCount = $unreadStmt->fetchColumn();
} catch (PDOException $e) { $notifications=[]; $unreadCount=0; }

$iconMap = [
    'upload_approved' => ['check_circle',    'text-emerald-600', 'bg-emerald-50'],
    'upload_rejected' => ['cancel',           'text-red-600',     'bg-red-50'],
    'new_review'      => ['star',             'text-amber-500',   'bg-amber-50'],
    'new_report'      => ['flag',             'text-red-600',     'bg-red-50'],
    'system'          => ['notifications',    'text-indigo-500',  'bg-indigo-50'],
];
function getIconData($type, $map) { return $map[$type] ?? $map['system']; }
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

            <div class="flex justify-between items-start mb-8">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-[28px] font-bold text-[#1A1A2E]">Notifications</h1>
                        <?php if ($unreadCount > 0): ?>
                        <span class="bg-[#B81C2E] text-white text-xs font-bold px-2.5 py-1 rounded-full"><?php echo $unreadCount; ?> new</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-[#4A4A5A] mt-1">Reviews, submission updates, and system alerts.</p>
                </div>
                <?php if ($unreadCount > 0): ?>
                <form method="POST">
                    <button type="submit" name="mark_all_read" value="1"
                            class="flex items-center gap-2 bg-white border border-[#E2E2E4] text-[#4A4A5A] text-sm font-semibold px-4 py-2.5 rounded-lg hover:border-[#1A1A2E] transition-colors">
                        <span class="material-symbols-outlined text-[16px]">done_all</span> Mark All as Read
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <?php if (empty($notifications)): ?>
            <div class="bg-white rounded-xl border border-black/5 py-20 text-center">
                <span class="material-symbols-outlined text-[56px] text-gray-200 block mb-4">notifications_none</span>
                <h3 class="text-lg font-bold text-[#1A1A2E] mb-2">All caught up!</h3>
                <p class="text-[#4A4A5A] text-sm">No notifications yet. We'll alert you when something needs your attention.</p>
            </div>
            <?php else: ?>
            <div class="space-y-2.5">
                <?php foreach ($notifications as $n):
                    $ic = getIconData($n['type'] ?? 'system', $iconMap);
                    $isUnread = !$n['is_read'];
                ?>
                <div class="relative bg-white rounded-xl border <?php echo $isUnread ? 'border-[#B81C2E]/20 bg-gradient-to-r from-white to-[#FFF9F9]' : 'border-black/[0.06]'; ?> p-4 flex items-start gap-4 hover:border-black/10 hover:shadow-sm transition-all">
                    <?php if ($isUnread): ?>
                    <div class="absolute top-4 right-4 w-2 h-2 bg-[#B81C2E] rounded-full"></div>
                    <?php endif; ?>
                    <div class="w-10 h-10 <?php echo $ic[2]; ?> <?php echo $ic[1]; ?> rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[20px]"><?php echo $ic[0]; ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm text-[#1A1A2E] mb-1"><?php echo htmlspecialchars($n['title'] ?? 'Notification'); ?></div>
                        <?php if ($n['message'] ?? ''): ?>
                        <div class="text-xs text-[#4A4A5A] leading-relaxed mb-2"><?php echo htmlspecialchars($n['message']); ?></div>
                        <?php endif; ?>
                        <div class="flex items-center gap-4 text-xs text-[#9CA3AF]">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">schedule</span>
                                <?php echo date('M d, Y · h:i A', strtotime($n['created_at'])); ?>
                            </span>
                            <?php if ($n['link'] ?? ''): ?>
                            <a href="<?php echo htmlspecialchars($n['link']); ?>" class="text-[#B81C2E] font-semibold hover:underline">View →</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($isUnread): ?>
                    <form method="POST" class="flex-shrink-0">
                        <input type="hidden" name="mark_read" value="<?php echo $n['id']; ?>">
                        <button type="submit" class="text-xs font-semibold text-[#4A4A5A] bg-[#F4F4F6] px-3 py-1.5 rounded-lg hover:bg-[#E9E9EF] transition-colors">
                            Mark read
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

