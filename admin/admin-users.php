<?php
/**
 * WMSU ARL Hub: Admin User Management — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('admin');

$search     = trim($_GET['search'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 15;
$offset     = ($page - 1) * $perPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_ban'])) {
        $userId = (int)$_POST['user_id'];
        $pdo->prepare("UPDATE users SET is_banned = NOT is_banned WHERE id = ?")->execute([$userId]);
        logAudit($pdo, $_SESSION['user_id'], 'toggle_ban', "Toggled ban for user ID $userId");
    }
    if (isset($_POST['change_role'])) {
        $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$_POST['new_role'], (int)$_POST['user_id']]);
        logAudit($pdo, $_SESSION['user_id'], 'role_change', "Changed role for user ID ".(int)$_POST['user_id']);
    }
    header("Location: admin-users.php?search=".urlencode($search)."&role=$roleFilter&page=$page"); exit();
}

$conditions = []; $params = [];
if ($search !== '') { $conditions[] = "(full_name LIKE ? OR email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($roleFilter !== '') { $conditions[] = "role = ?"; $params[] = $roleFilter; }
$where = $conditions ? "WHERE ".implode(' AND ', $conditions) : '';

try {
    $totalUsers = $pdo->prepare("SELECT COUNT(*) FROM users $where");
    $totalUsers->execute($params);
    $totalUsers = $totalUsers->fetchColumn();
    $totalPages = ceil($totalUsers / $perPage);
    $stmt = $pdo->prepare("SELECT * FROM users $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    $roleCounts = [
        'all'     => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'student' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
        'faculty' => $pdo->query("SELECT COUNT(*) FROM users WHERE role='faculty'")->fetchColumn(),
        'admin'   => $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn(),
    ];
} catch (PDOException $e) { $users=[]; $totalUsers=0; $totalPages=1; $roleCounts=['all'=>0,'student'=>0,'faculty'=>0,'admin'=>0]; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - WMSU ARL Hub</title>
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
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">User Management</h1>
                    <p class="text-[#4A4A5A] mt-1"><?php echo number_format($roleCounts['all']); ?> registered users in the system.</p>
                </div>
            </div>

            <!-- Role Tabs -->
            <div class="flex gap-2 mb-6">
                <?php foreach ([''=>'All', 'student'=>'Students', 'faculty'=>'Faculty', 'admin'=>'Admins'] as $role => $label):
                    $count = $role === '' ? $roleCounts['all'] : $roleCounts[$role];
                    $active = $roleFilter === $role;
                ?>
                <a href="?role=<?php echo $role; ?>&search=<?php echo urlencode($search); ?>"
                   class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold transition-colors <?php echo $active ? 'bg-[#1A1A2E] text-white' : 'bg-white text-[#4A4A5A] border border-[#E2E2E4] hover:border-[#1A1A2E]'; ?>">
                    <?php echo $label; ?>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full <?php echo $active ? 'bg-white/15 text-white' : 'bg-[#F4F4F6] text-[#4A4A5A]'; ?>"><?php echo $count; ?></span>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Search Bar -->
            <form method="GET" class="flex gap-3 mb-6">
                <div class="relative flex-1 max-w-xs">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF] text-[17px]">search</span>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search name or email..."
                           class="w-full pl-9 pr-4 h-10 bg-white border border-[#E2E2E4] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E]">
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($roleFilter); ?>">
                </div>
                <button type="submit" class="h-10 px-4 bg-[#1A1A2E] text-white text-sm font-semibold rounded-lg hover:bg-black transition-colors">Search</button>
                <?php if ($search): ?><a href="?role=<?php echo $roleFilter; ?>" class="h-10 px-4 flex items-center gap-1 bg-white text-[#4A4A5A] text-sm font-medium rounded-lg border border-[#E2E2E4] hover:border-[#B81C2E] transition-colors">
                    <span class="material-symbols-outlined text-[15px]">close</span> Clear
                </a><?php endif; ?>
            </form>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-black/[0.06] overflow-hidden">
                <?php if (empty($users)): ?>
                <div class="py-16 text-center">
                    <span class="material-symbols-outlined text-[48px] text-gray-200 block mb-3">people</span>
                    <p class="text-[#4A4A5A] text-sm font-medium">No users found.</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#F9F9FB] border-b border-[#F4F4F6]">
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">User</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Role</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Materials</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Status</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Joined</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F4F4F6]">
                            <?php foreach ($users as $u):
                                try {
                                    $mc = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE contributor_id = ?");
                                    $mc->execute([$u['id']]);
                                    $matCount = $mc->fetchColumn();
                                } catch (Exception $e) { $matCount = 0; }
                                $isBanned = $u['is_banned'] ?? 0;
                                $avatarColors = ['admin' => 'bg-[#B81C2E]', 'faculty' => 'bg-emerald-600', 'student' => 'bg-indigo-500'];
                                $rolePills = ['admin' => 'bg-[#F9E8EA] text-[#B81C2E]', 'faculty' => 'bg-emerald-50 text-emerald-700', 'student' => 'bg-indigo-50 text-indigo-700'];
                                $roleColor = $avatarColors[$u['role']] ?? 'bg-gray-400';
                                $pillColor = $rolePills[$u['role']] ?? 'bg-gray-100 text-gray-600';
                            ?>
                            <tr class="hover:bg-[#F9F9FB] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 <?php echo $roleColor; ?> rounded-xl flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                            <?php echo strtoupper(substr($u['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-[#1A1A2E]"><?php echo htmlspecialchars($u['full_name']); ?></div>
                                            <div class="text-xs text-[#9CA3AF]"><?php echo htmlspecialchars($u['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $pillColor; ?> text-[10px] font-bold px-2.5 py-1 rounded-full uppercase"><?php echo ucfirst($u['role']); ?></span>
                                </td>
                                <td class="px-6 py-4 font-bold text-[#1A1A2E]"><?php echo $matCount; ?></td>
                                <td class="px-6 py-4">
                                    <span class="flex items-center gap-1.5 text-xs font-semibold <?php echo $isBanned ? 'text-red-600' : 'text-green-600'; ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?php echo $isBanned ? 'bg-red-500' : 'bg-green-500'; ?>"></span>
                                        <?php echo $isBanned ? 'Banned' : 'Active'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-[#4A4A5A]"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="admin-user-detail.php?id=<?php echo $u['id']; ?>"
                                           class="flex items-center gap-1 bg-[#F4F4F6] text-[#4A4A5A] text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-[#E9E9EF] transition-colors">
                                            <span class="material-symbols-outlined text-[13px]">visibility</span> View
                                        </a>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" class="inline" onsubmit="return confirm('<?php echo $isBanned ? 'Are you sure you want to unban this user?' : 'Are you sure you want to ban this user?'; ?>');">
                                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" name="toggle_ban" value="1"
                                                    class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors border <?php echo $isBanned ? 'bg-green-50 text-green-700 border-green-100 hover:bg-green-100' : 'bg-red-50 text-red-700 border-red-100 hover:bg-red-100'; ?>">
                                                <span class="material-symbols-outlined text-[13px]"><?php echo $isBanned ? 'lock_open' : 'block'; ?></span>
                                                <?php echo $isBanned ? 'Unban' : 'Ban'; ?>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="flex justify-center gap-2 mt-6">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="?search=<?php echo urlencode($search); ?>&role=<?php echo $roleFilter; ?>&page=<?php echo $p; ?>"
                   class="w-9 h-9 flex items-center justify-center rounded-lg text-sm font-semibold transition-colors <?php echo $p === $page ? 'bg-[#1A1A2E] text-white' : 'bg-white border border-[#E2E2E4] text-[#4A4A5A] hover:border-[#1A1A2E]'; ?>">
                    <?php echo $p; ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

