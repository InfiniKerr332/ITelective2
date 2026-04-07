<?php
/**
 * WMSU ARL Hub: Admin Dashboard — Premium Stitch Design System
 */
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/paths.php';
}
require_once '../config/auth.php';
checkAuth('admin');

try {
    $totalUsers        = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalMaterials    = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
    $pendingModeration = $pdo->query("SELECT COUNT(*) FROM materials WHERE status = 'pending'")->fetchColumn();
    $totalDownloads    = $pdo->query("SELECT COALESCE(SUM(downloads_count),0) FROM materials")->fetchColumn();

    $studentsCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    $facultyCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='faculty'")->fetchColumn();

    // Fetch Recent Activity Logs
    $recentLogs = $pdo->query("
        SELECT a.*, u.full_name as user_name 
        FROM audit_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        ORDER BY a.timestamp DESC 
        LIMIT 5
    ")->fetchAll();

    // Fetch Top Downloaded
    $topMaterials = $pdo->query("
        SELECT m.title, m.downloads_count, u.full_name as author 
        FROM materials m 
        LEFT JOIN users u ON m.contributor_id = u.id 
        ORDER BY m.downloads_count DESC 
        LIMIT 5
    ")->fetchAll();

    // Calculate Weekly Growth for Chart (Cumulative Users per week for the last 4 weeks)
    $weeklyGrowth = [];
    $maxUsers = $totalUsers > 0 ? $totalUsers : 1; 
    for($i = 3; $i >= 0; $i--) {
        $dateLimit = date('Y-m-d 23:59:59', strtotime("-{$i} weeks"));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE created_at <= ?");
        $stmt->execute([$dateLimit]);
        $cnt = $stmt->fetchColumn();
        $weeklyGrowth[] = [
            'label' => $i === 0 ? 'Current' : 'Week ' . (4 - $i),
            'count' => $cnt,
            'percent' => max(10, min(100, ($cnt / $maxUsers) * 100))
        ];
    }
} catch (PDOException $e) {
    $totalUsers = $totalMaterials = $pendingModeration = $totalDownloads = $studentsCount = $facultyCount = 0;
    $recentLogs = $topMaterials = [];
    $weeklyGrowth = [
        ['label'=>'Week 1','count'=>0,'percent'=>10],
        ['label'=>'Week 2','count'=>0,'percent'=>10],
        ['label'=>'Week 3','count'=>0,'percent'=>10],
        ['label'=>'Current','count'=>0,'percent'=>10]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WMSU ARL Hub</title>
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
        h1, h2, h3, .font-headline { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
        .premium-card { background: white; border-radius: 2.5rem; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.02); }
    </style>
</head>
<body class="text-[#1A1A2E] bg-[#F4F4F6]">

<?php require_once '../includes/dashboard-nav.php'; ?>
<?php require_once '../includes/sidebar.php'; ?>

<div class="flex">
    <!-- Main Content Area -->
    <main class="flex-1 ml-[240px] pt-16 flex flex-col min-h-screen bg-[#F4F4F6]">
        <div class="max-w-[1280px] w-full mx-auto p-8 flex-1">
            <!-- Page Header -->
            <div class="mb-8 flex justify-between items-end">
                <div>
                    <h1 class="text-[28px] font-bold text-[#1A1A2E] leading-tight font-headline">System Dashboard</h1>
                    <p class="text-gray-500 text-sm mt-1"><?php echo date('l, F j, Y'); ?> • Welcome back, Administrator.</p>
                </div>
                <a href="admin-analytics.php" class="bg-primary hover:bg-[#8C1222] text-white px-5 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined text-lg" data-icon="analytics">analytics</span>
                    View Analytics
                </a>
            </div>
            
            <!-- Warning Banner -->
            <?php if ($pendingModeration > 0): ?>
            <div class="bg-[#FFF8E1] border-l-4 border-[#D4AF37] p-4 mb-8 rounded-r-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[#856404]" data-icon="warning">warning</span>
                    <span class="text-sm text-[#5A3E00] font-medium"><?php echo $pendingModeration; ?> materials are flagged and pending moderation review.</span>
                </div>
                <a href="<?php echo BASE_URL; ?>admin/admin-moderation.php" class="text-[#856404] hover:underline text-sm font-bold">Review Now</a>
            </div>
            <?php endif; ?>
            
            <!-- Stats Rows - Bento Style -->
            <div class="grid grid-cols-4 gap-6 mb-8">
                <!-- Row 1 -->
                <div class="bg-white p-6 rounded-xl border border-black/5 flex flex-col shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-gray-500 text-xs font-medium uppercase tracking-wider mb-2">Total Users</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-[#1A1A2E]"><?php echo number_format($totalUsers); ?></span>
                        <span class="text-[10px] text-[#2E7D32] bg-[#E8F5E9] px-1.5 py-0.5 rounded font-bold">Live</span>
                    </div>
                    <div class="mt-4 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-primary h-full w-full"></div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-black/5 flex flex-col shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-gray-500 text-xs font-medium uppercase tracking-wider mb-2">Total Materials</span>
                    <span class="text-2xl font-bold text-[#1A1A2E]"><?php echo number_format($totalMaterials); ?></span>
                    <p class="text-[11px] text-gray-400 mt-2">Active in repository</p>
                </div>
                <div class="bg-white p-6 rounded-xl border border-black/5 flex flex-col shadow-sm border-l-4 <?php echo $pendingModeration > 0 ? 'border-[#B71C1C]' : 'border-gray-200'; ?> hover:shadow-md transition-shadow">
                    <span class="text-gray-500 text-xs font-medium uppercase tracking-wider mb-2">Flagged Content</span>
                    <span class="text-2xl font-bold <?php echo $pendingModeration > 0 ? 'text-[#B71C1C]' : 'text-[#1A1A2E]'; ?>"><?php echo number_format($pendingModeration); ?></span>
                    <p class="text-[11px] <?php echo $pendingModeration > 0 ? 'text-[#B71C1C]' : 'text-gray-400'; ?> mt-2 font-medium"><?php echo $pendingModeration > 0 ? 'Requires immediate action' : 'All clear'; ?></p>
                </div>
                <div class="bg-white p-6 rounded-xl border border-black/5 flex flex-col shadow-sm hover:shadow-md transition-shadow">
                    <span class="text-gray-500 text-xs font-medium uppercase tracking-wider mb-2">Downloads Today</span>
                    <span class="text-2xl font-bold text-[#1A1A2E]"><?php echo number_format($totalDownloads); ?></span>
                    <p class="text-[11px] text-gray-400 mt-2">Across all courses</p>
                </div>
                
                <!-- Row 2 -->
                <div class="bg-white p-6 rounded-xl border border-black/5 shadow-sm hover:shadow-md transition-shadow col-span-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-primary text-xl" data-icon="school">school</span>
                        <span class="text-gray-500 text-xs font-medium uppercase tracking-wider">Active Students</span>
                    </div>
                    <span class="text-2xl font-bold text-[#1A1A2E]"><?php echo number_format($studentsCount); ?></span>
                </div>
                <div class="bg-white p-6 rounded-xl border border-black/5 shadow-sm hover:shadow-md transition-shadow col-span-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-primary text-xl" data-icon="workspace_premium">workspace_premium</span>
                        <span class="text-gray-500 text-xs font-medium uppercase tracking-wider">Faculty Members</span>
                    </div>
                    <span class="text-2xl font-bold text-[#1A1A2E]"><?php echo number_format($facultyCount); ?></span>
                </div>
                <div class="bg-white p-6 rounded-xl border border-black/5 shadow-sm hover:shadow-md transition-shadow col-span-2 flex items-center justify-between">
                    <div>
                        <span class="text-gray-500 text-xs font-medium uppercase tracking-wider mb-1 block">Compliance Rate</span>
                        <span class="text-2xl font-bold text-[#2E7D32]">98.2%</span>
                    </div>
                    <div class="w-16 h-16 rounded-full border-[6px] border-[#E8F5E9] border-t-[#2E7D32] flex items-center justify-center">
                        <span class="text-[10px] font-bold text-[#2E7D32]">High</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Table -->
            <section class="bg-white rounded-xl border border-black/5 shadow-sm overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-black/5 flex justify-between items-center bg-white">
                    <h2 class="text-lg font-bold text-[#1A1A2E]">Recent System Activity</h2>
                    <a href="admin-audit.php" class="text-primary text-xs font-bold hover:underline">View All Logs</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-[#1A1A2E] text-white">
                                <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider">Timestamp</th>
                                <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider">User</th>
                                <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider">Target</th>
                                <th class="px-6 py-3.5 text-[11px] font-semibold uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-black/5">
                            <?php if (empty($recentLogs)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No recent activity found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentLogs as $index => $log): 
                                    $bgClass = $index % 2 === 0 ? 'bg-white' : 'bg-[#F4F4F6]';
                                    
                                    // Determine status and style based on action
                                    $actionLower = strtolower($log['action']);
                                    $statusText = 'Completed';
                                    $statusClass = 'bg-[#E8F5E9] text-[#2E7D32]'; // Green
                                    
                                    if (str_contains($actionLower, 'flag') || str_contains($actionLower, 'report')) {
                                        $statusText = 'Pending';
                                        $statusClass = 'bg-[#FFF8E1] text-[#856404]'; // Yellow
                                    } elseif (str_contains($actionLower, 'remove') || str_contains($actionLower, 'delete') || str_contains($actionLower, 'reject')) {
                                        $statusText = 'Actioned';
                                        $statusClass = 'bg-[#FFEBEE] text-[#B71C1C]'; // Red
                                    }
                                ?>
                                <tr class="<?php echo $bgClass; ?> hover:bg-[#F9E8EA] transition-colors">
                                    <td class="px-6 py-4 text-[13px] text-gray-500"><?php echo date('M j, h:i A', strtotime($log['timestamp'])); ?></td>
                                    <td class="px-6 py-4 text-[13px] font-medium text-[#1A1A2E] capitalize"><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-500"><?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?></td>
                                    <td class="px-6 py-4 text-[13px] text-gray-500 text-sm max-w-[200px] truncate" title="<?php echo htmlspecialchars($log['details']); ?>"><?php echo htmlspecialchars($log['details']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="<?php echo $statusClass; ?> px-2.5 py-0.5 rounded-full text-[11px] font-semibold"><?php echo $statusText; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            
            <!-- Bottom Row: Growth & Top Materials -->
            <div class="grid grid-cols-2 gap-8 mb-12">
                <!-- User Growth Data -->
                <div class="bg-white p-6 rounded-xl border border-black/5 shadow-sm">
                    <h3 class="text-base font-bold text-[#1A1A2E] mb-6">User Growth This Month</h3>
                    <div class="h-48 w-full flex items-end gap-3 px-2">
                        <?php foreach($weeklyGrowth as $idx => $grow): 
                            $bgClass = $idx === 3 ? 'bg-primary' : 'bg-[#F9E8EA]';    
                        ?>
                        <div class="w-full <?php echo $bgClass; ?> rounded-t-md transition-all hover:opacity-80 flex flex-col justify-end" style="height: <?php echo $grow['percent']; ?>%;" title="<?php echo $grow['label']; ?>: <?php echo $grow['count']; ?> users">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between mt-4 text-[10px] text-gray-500 font-bold uppercase tracking-widest">
                        <?php foreach($weeklyGrowth as $grow): ?>
                            <span><?php echo $grow['label']; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Top Downloaded Materials -->
                <div class="bg-white p-6 rounded-xl border border-black/5 shadow-sm">
                    <h3 class="text-base font-bold text-[#1A1A2E] mb-6">Top Downloaded Materials</h3>
                    <div class="space-y-4">
                        <?php if (empty($topMaterials)): ?>
                            <p class="text-sm text-gray-500">No downloads recorded yet.</p>
                        <?php else: ?>
                            <?php foreach ($topMaterials as $i => $material): 
                                $numStr = str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                                // Highlight top 3
                                if ($i < 3) {
                                    $numClass = 'text-primary italic';
                                    $rowClass = 'border-l-2 border-primary pl-4 bg-[#F9E8EA]/30 -ml-4 py-1';
                                } else {
                                    $numClass = 'text-primary/20 italic';
                                    $rowClass = '';
                                }
                            ?>
                            <div class="flex items-center gap-4 <?php echo $rowClass; ?>">
                                <span class="text-xl font-bold <?php echo $numClass; ?>"><?php echo $numStr; ?></span>
                                <div class="flex-1 truncate">
                                    <p class="text-[13px] font-semibold text-[#1A1A2E] truncate" title="<?php echo htmlspecialchars($material['title']); ?>"><?php echo htmlspecialchars($material['title']); ?></p>
                                    <p class="text-[11px] text-gray-500"><?php echo number_format($material['downloads_count']); ?> downloads • <?php echo htmlspecialchars($material['author'] ?? 'Unknown'); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>

        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>
