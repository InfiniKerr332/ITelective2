<?php
/**
 * WMSU ARL Hub: Admin Audit Trail — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('admin');

$search       = trim($_GET['search'] ?? '');
$actionFilter = $_GET['action'] ?? '';
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 20;
$offset       = ($page - 1) * $perPage;

$conditions = []; $params = [];
if ($search !== '') { $conditions[] = "(a.action LIKE ? OR a.details LIKE ? OR u.full_name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($actionFilter !== '') { $conditions[] = "a.action = ?"; $params[] = $actionFilter; }
$where = $conditions ? "WHERE ".implode(' AND ', $conditions) : '';

try {
    $countQ = $pdo->prepare("SELECT COUNT(*) FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id $where");
    $countQ->execute($params); $total = $countQ->fetchColumn(); $totalPages = ceil($total / $perPage);
    $stmt = $pdo->prepare("SELECT a.*, u.full_name, u.role, u.email FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id $where ORDER BY a.timestamp DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params); $logs = $stmt->fetchAll();
    $actionTypes = $pdo->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) { $logs=[]; $total=0; $totalPages=1; $actionTypes=[]; }

$logStyles = [
    'login'       => ['bg-indigo-50',  'text-indigo-600',  'login'],
    'logout'      => ['bg-[#F4F4F6]',  'text-[#4A4A5A]',  'logout'],
    'upload'      => ['bg-emerald-50', 'text-emerald-600', 'upload'],
    'approve'     => ['bg-emerald-50', 'text-emerald-600', 'check_circle'],
    'reject'      => ['bg-red-50',     'text-red-600',     'cancel'],
    'toggle_ban'  => ['bg-amber-50',   'text-amber-600',   'block'],
    'role_change' => ['bg-indigo-50',  'text-indigo-600',  'manage_accounts'],
    'delete'      => ['bg-red-50',     'text-red-600',     'delete'],
];
function getLogStyle($action, $map) {
    foreach ($map as $k => $v) { if (stripos($action, $k) !== false) return $v; }
    return ['bg-[#F4F4F6]', 'text-[#4A4A5A]', 'info'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - WMSU ARL Hub</title>
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

            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">Audit Trail</h1>
                    <p class="text-[#4A4A5A] mt-1">System activity log for all user actions.</p>
                </div>
                <span class="flex items-center gap-2 bg-white border border-[#E2E2E4] px-4 py-2.5 rounded-lg text-sm font-semibold text-[#4A4A5A]">
                    <span class="material-symbols-outlined text-[#B81C2E] text-[16px]">list_alt</span>
                    <?php echo number_format($total); ?> entries
                </span>
            </div>

            <!-- Filters -->
            <form method="GET" class="flex flex-wrap gap-3 mb-6">
                <div class="relative flex-1 min-w-[200px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF] text-[17px]">search</span>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search action, detail, or user..."
                           class="w-full pl-9 pr-4 h-10 bg-white border border-[#E2E2E4] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E]">
                </div>
                <select name="action" class="h-10 px-3 bg-white border border-[#E2E2E4] rounded-lg text-sm text-[#1A1A2E] font-medium focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E]">
                    <option value="">All Actions</option>
                    <?php foreach ($actionTypes as $at): ?>
                    <option value="<?php echo htmlspecialchars($at); ?>" <?php echo $actionFilter === $at ? 'selected' : ''; ?>><?php echo htmlspecialchars($at); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="h-10 px-4 bg-[#1A1A2E] text-white text-sm font-semibold rounded-lg hover:bg-black transition-colors">Filter</button>
                <?php if ($search || $actionFilter): ?>
                <a href="admin-audit.php" class="h-10 px-4 flex items-center gap-1 bg-white border border-[#E2E2E4] rounded-lg text-sm text-[#4A4A5A] font-medium hover:border-[#B81C2E] transition-colors">
                    <span class="material-symbols-outlined text-[14px]">close</span> Clear
                </a>
                <?php endif; ?>
            </form>

            <!-- Log Feed -->
            <?php if (empty($logs)): ?>
            <div class="bg-white rounded-xl border border-black/5 py-20 text-center">
                <span class="material-symbols-outlined text-[56px] text-gray-200 block mb-4">history</span>
                <h3 class="text-lg font-bold text-[#1A1A2E] mb-2">No audit logs found</h3>
                <p class="text-[#4A4A5A] text-sm">System activity will appear here as users interact with the platform.</p>
            </div>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($logs as $log):
                    $style = getLogStyle($log['action'], $logStyles);
                    $avatarColors = ['admin'=>'bg-[#B81C2E]','faculty'=>'bg-emerald-600','student'=>'bg-indigo-500'];
                    $avatarColor = $avatarColors[$log['role'] ?? ''] ?? 'bg-gray-400';
                ?>
                <div class="bg-white rounded-xl border border-black/[0.06] p-4 flex items-start gap-4 hover:border-black/10 hover:shadow-sm transition-all">
                    <div class="w-9 h-9 <?php echo $style[0]; ?> <?php echo $style[1]; ?> rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[18px]"><?php echo $style[2]; ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-sm text-[#1A1A2E] mb-0.5"><?php echo htmlspecialchars($log['action']); ?></div>
                        <?php if ($log['details']): ?>
                        <div class="text-xs text-[#4A4A5A] mb-2 leading-relaxed"><?php echo htmlspecialchars($log['details']); ?></div>
                        <?php endif; ?>
                        <div class="flex items-center gap-4 text-xs text-[#4A4A5A]">
                            <span class="flex items-center gap-1.5">
                                <div class="w-4 h-4 <?php echo $avatarColor; ?> rounded flex items-center justify-center text-white text-[8px] font-bold flex-shrink-0">
                                    <?php echo $log['full_name'] ? strtoupper(substr($log['full_name'], 0, 1)) : '?'; ?>
                                </div>
                                <?php echo htmlspecialchars($log['full_name'] ?? 'System'); ?>
                                <?php if ($log['role']): ?><span class="bg-[#F4F4F6] px-1.5 py-0.5 rounded text-[9px] font-bold uppercase"><?php echo $log['role']; ?></span><?php endif; ?>
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">schedule</span>
                                <?php echo date('M d, Y · H:i:s', strtotime($log['timestamp'])); ?>
                            </span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-[#D1D1D9] flex-shrink-0">ID #<?php echo $log['id']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center gap-2 mt-6">
                <?php if ($page > 1): ?>
                <a href="?search=<?php echo urlencode($search); ?>&action=<?php echo urlencode($actionFilter); ?>&page=<?php echo $page-1; ?>"
                   class="w-9 h-9 flex items-center justify-center bg-white border border-[#E2E2E4] rounded-lg text-sm text-[#4A4A5A] hover:border-[#1A1A2E] transition-colors">‹</a>
                <?php endif; ?>
                <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
                <a href="?search=<?php echo urlencode($search); ?>&action=<?php echo urlencode($actionFilter); ?>&page=<?php echo $p; ?>"
                   class="w-9 h-9 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors <?php echo $p === $page ? 'bg-[#1A1A2E] text-white' : 'bg-white border border-[#E2E2E4] text-[#4A4A5A] hover:border-[#1A1A2E]'; ?>">
                    <?php echo $p; ?>
                </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                <a href="?search=<?php echo urlencode($search); ?>&action=<?php echo urlencode($actionFilter); ?>&page=<?php echo $page+1; ?>"
                   class="w-9 h-9 flex items-center justify-center bg-white border border-[#E2E2E4] rounded-lg text-sm text-[#4A4A5A] hover:border-[#1A1A2E] transition-colors">›</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

