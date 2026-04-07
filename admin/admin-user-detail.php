<?php
/**
 * WMSU ARL Hub: Admin User Detail — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('admin');

$targetId = (int)($_GET['id'] ?? 0);
if (!$targetId) { header("Location: admin-users.php"); exit(); }

try {
    $uStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $uStmt->execute([$targetId]); $user = $uStmt->fetch();
    if (!$user) { header("Location: admin-users.php"); exit(); }

    $mStmt = $pdo->prepare("SELECT m.*, (SELECT COUNT(*) FROM reviews r WHERE r.material_id = m.id) AS review_count FROM materials m WHERE m.contributor_id = ? ORDER BY m.created_at DESC");
    $mStmt->execute([$targetId]); $materials = $mStmt->fetchAll();

    $dStmt = $pdo->prepare("SELECT COUNT(*) FROM downloads WHERE user_id = ?");
    $dStmt->execute([$targetId]); $downloadCount = $dStmt->fetchColumn();

    $aStmt = $pdo->prepare("SELECT * FROM audit_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT 10");
    $aStmt->execute([$targetId]); $auditLogs = $aStmt->fetchAll();
} catch (PDOException $e) { $user=[]; $materials=[]; $downloadCount=0; $auditLogs=[]; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_ban'])) {
        $pdo->prepare("UPDATE users SET is_banned = NOT is_banned WHERE id = ?")->execute([$targetId]);
        logAudit($pdo, $_SESSION['user_id'], 'toggle_ban', "Admin toggled ban for user: ".$user['email']);
    }
    if (isset($_POST['change_role'])) {
        $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$_POST['new_role'], $targetId]);
        logAudit($pdo, $_SESSION['user_id'], 'role_change', "Changed role of ".$user['email']." to ".$_POST['new_role']);
    }
    header("Location: admin-user-detail.php?id=$targetId"); exit();
}

$isBanned = $user['is_banned'] ?? 0;
$roleColors = ['admin'=>'bg-[#B81C2E] text-white','faculty'=>'bg-emerald-600 text-white','student'=>'bg-indigo-500 text-white'];
$avatarColor = $roleColors[$user['role']] ?? 'bg-gray-400 text-white';
$pillColors = ['approved'=>'bg-emerald-50 text-emerald-700','pending'=>'bg-amber-50 text-amber-700','rejected'=>'bg-red-50 text-red-700'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Detail - WMSU ARL Hub</title>
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

            <a href="admin-users.php" class="inline-flex items-center gap-2 text-sm font-semibold text-[#4A4A5A] hover:text-[#1A1A2E] mb-6 transition-colors">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to User Management
            </a>

            <!-- Profile Hero -->
            <div class="bg-white rounded-xl border border-black/[0.06] p-6 flex items-center gap-6 mb-6">
                <div class="w-16 h-16 <?php echo $avatarColor; ?> rounded-2xl flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-sm">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-[#1A1A2E]"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <p class="text-sm text-[#4A4A5A] mt-0.5"><?php echo htmlspecialchars($user['email']); ?> · Joined <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    <div class="flex gap-2 mt-2">
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full <?php echo ['admin'=>'bg-[#F9E8EA] text-[#B81C2E]','faculty'=>'bg-emerald-50 text-emerald-700','student'=>'bg-indigo-50 text-indigo-600'][$user['role']] ?? 'bg-gray-100 text-gray-600'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                        <span class="text-[10px] font-bold uppercase px-2.5 py-1 rounded-full <?php echo $isBanned ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'; ?>">
                            <?php echo $isBanned ? 'Banned' : 'Active'; ?>
                        </span>
                    </div>
                </div>
                <?php if ($targetId !== (int)$_SESSION['user_id']): ?>
                <form method="POST">
                    <button type="submit" name="toggle_ban" value="1"
                            class="flex items-center gap-2 <?php echo $isBanned ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-red-50 text-red-700 border-red-100'; ?> border text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-80 transition-opacity">
                        <span class="material-symbols-outlined text-[15px]"><?php echo $isBanned ? 'lock_open' : 'block'; ?></span>
                        <?php echo $isBanned ? 'Unban User' : 'Ban User'; ?>
                    </button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-4 gap-4 mb-6">
                <?php foreach ([
                    [count($materials),                              'Uploads'],
                    [$downloadCount,                                 'Downloads Made'],
                    [array_sum(array_column($materials, 'downloads_count') ?: [0]), 'Times Shared'],
                    [array_sum(array_column($materials, 'review_count') ?: [0]),    'Reviews Received'],
                ] as $s): ?>
                <div class="bg-white rounded-xl border border-black/[0.06] p-4">
                    <div class="text-2xl font-bold text-[#1A1A2E]"><?php echo number_format($s[0]); ?></div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-[#4A4A5A] mt-0.5"><?php echo $s[1]; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Change Role -->
            <?php if ($targetId !== (int)$_SESSION['user_id']): ?>
            <div class="bg-white rounded-xl border border-black/[0.06] p-6 mb-5">
                <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Change Role</h3>
                <form method="POST" class="flex gap-3 items-center">
                    <select name="new_role" class="h-10 px-3 bg-[#F9F9FB] border border-[#E2E2E4] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#B81C2E]">
                        <option value="student" <?php echo $user['role']==='student'?'selected':''; ?>>Student</option>
                        <option value="faculty" <?php echo $user['role']==='faculty'?'selected':''; ?>>Faculty</option>
                        <option value="admin"   <?php echo $user['role']==='admin'  ?'selected':''; ?>>Admin</option>
                    </select>
                    <button type="submit" name="change_role" value="1"
                            class="h-10 px-4 bg-[#1A1A2E] text-white text-sm font-semibold rounded-lg hover:bg-black transition-colors">
                        Update Role
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- Uploaded Materials -->
            <?php if (!empty($materials)): ?>
            <div class="bg-white rounded-xl border border-black/[0.06] p-6 mb-5">
                <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Uploaded Materials (<?php echo count($materials); ?>)</h3>
                <div class="space-y-2">
                    <?php foreach ($materials as $m):
                        $pill = $pillColors[$m['status']] ?? 'bg-gray-100 text-gray-600';
                    ?>
                    <div class="flex items-center gap-4 bg-[#F9F9FB] rounded-xl p-3.5">
                        <div class="w-9 h-9 bg-[#F9E8EA] text-[#B81C2E] rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-[17px]">description</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm text-[#1A1A2E] truncate"><?php echo htmlspecialchars($m['title']); ?></div>
                            <div class="text-xs text-[#4A4A5A]"><?php echo htmlspecialchars($m['category']); ?> · <?php echo date('M d, Y', strtotime($m['created_at'])); ?></div>
                        </div>
                        <span class="<?php echo $pill; ?> text-[9px] font-bold px-2 py-0.5 rounded-full uppercase flex-shrink-0"><?php echo $m['status']; ?></span>
                        <span class="text-xs font-bold text-[#4A4A5A] flex-shrink-0"><?php echo number_format($m['downloads_count']); ?> dl</span>
                        <a href="<?php echo BASE_URL; ?>core/material-details.php?id=<?php echo $m['id']; ?>"
                           class="flex-shrink-0 text-xs font-semibold text-[#4A4A5A] bg-white border border-[#E2E2E4] px-3 py-1.5 rounded-lg hover:border-[#1A1A2E] transition-colors">View</a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Activity Log -->
            <?php if (!empty($auditLogs)): ?>
            <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                <h3 class="text-base font-bold text-[#1A1A2E] mb-4">Recent Activity</h3>
                <div class="divide-y divide-[#F4F4F6]">
                    <?php foreach ($auditLogs as $log): ?>
                    <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="w-1.5 h-1.5 rounded-full bg-[#D1D1D9] mt-2 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <span class="font-semibold text-sm text-[#1A1A2E]"><?php echo htmlspecialchars($log['action']); ?></span>
                            <?php if ($log['details']): ?>
                            <span class="text-sm text-[#4A4A5A]"> — <?php echo htmlspecialchars($log['details']); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs text-[#9CA3AF] font-medium flex-shrink-0"><?php echo date('M d, H:i', strtotime($log['timestamp'])); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

