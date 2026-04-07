<?php
/**
 * WMSU ARL Hub: Admin System Analytics — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('admin');

try {
    $totalUsers     = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $newUsersMonth  = $pdo->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetchColumn();
    $totalMaterials = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
    $approvedMats   = $pdo->query("SELECT COUNT(*) FROM materials WHERE status='approved'")->fetchColumn();
    $totalDownloads = $pdo->query("SELECT SUM(downloads_count) FROM materials")->fetchColumn() ?? 0;
    $totalReviews   = $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    $pendingMats    = $pdo->query("SELECT COUNT(*) FROM materials WHERE status='pending'")->fetchColumn();

    $monthlyDl = $pdo->query("
        SELECT DATE_FORMAT(downloaded_at,'%b %Y') AS month, COUNT(*) AS count
        FROM downloads WHERE downloaded_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(downloaded_at,'%Y-%m') ORDER BY downloaded_at ASC
    ")->fetchAll();

    $catStats = $pdo->query("
        SELECT category, COUNT(*) AS count, SUM(downloads_count) AS total_dl
        FROM materials WHERE status='approved' GROUP BY category ORDER BY count DESC
    ")->fetchAll();

    $topContributors = $pdo->query("
        SELECT u.full_name, u.role, COUNT(m.id) AS mat_count, SUM(m.downloads_count) AS total_dl
        FROM users u JOIN materials m ON m.contributor_id = u.id
        WHERE m.status = 'approved' GROUP BY u.id ORDER BY total_dl DESC LIMIT 5
    ")->fetchAll();

    $roleDist = $pdo->query("SELECT role, COUNT(*) AS count FROM users GROUP BY role")->fetchAll();
} catch (PDOException $e) {
    $totalUsers=$totalMaterials=$approvedMats=$totalDownloads=$totalReviews=$pendingMats=$newUsersMonth=0;
    $monthlyDl=$catStats=$topContributors=$roleDist=[];
}

$roleColors = ['student'=>'#6366F1','faculty'=>'#059669','admin'=>'#B81C2E'];
$totalR = array_sum(array_column($roleDist, 'count') ?: [0]);
$dl_counts = array_column($monthlyDl, 'count');
$maxDl = !empty($dl_counts) ? max(max($dl_counts), 1) : 1;
$cat_counts = array_column($catStats, 'count');
$maxCat = !empty($cat_counts) ? max(max($cat_counts), 1) : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Analytics - WMSU ARL Hub</title>
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
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">System Analytics</h1>
                    <p class="text-[#4A4A5A] mt-1">Platform performance and usage insights.</p>
                </div>
                <span class="flex items-center gap-2 bg-white border border-[#E2E2E4] px-4 py-2.5 rounded-lg text-sm font-semibold text-[#4A4A5A]">
                    <span class="material-symbols-outlined text-[#B81C2E] text-[16px]">calendar_today</span>
                    <?php echo date('F Y'); ?>
                </span>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <?php $kpis = [
                    ['Total Users',      'group',       number_format($totalUsers),    "$newUsersMonth new this month",   'bg-indigo-50 text-indigo-500'],
                    ['Total Materials',  'description', number_format($totalMaterials),"$approvedMats approved",         'bg-[#F9E8EA] text-[#B81C2E]'],
                    ['Total Downloads',  'download',    number_format($totalDownloads),'across all materials',            'bg-emerald-50 text-emerald-600'],
                    ['Pending Review',   'pending',     number_format($pendingMats),   'awaiting moderation',            'bg-amber-50 text-amber-500'],
                ]; foreach ($kpis as $k): ?>
                <div class="bg-white rounded-xl border border-black/[0.06] p-5 hover:-translate-y-0.5 transition-transform">
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]"><?php echo $k[0]; ?></span>
                        <div class="w-9 h-9 <?php echo $k[4]; ?> rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-[18px]"><?php echo $k[1]; ?></span>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-[#1A1A2E] mb-1"><?php echo $k[2]; ?></div>
                    <div class="text-xs text-[#4A4A5A] font-medium"><?php echo $k[3]; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <!-- Downloads Bar Chart -->
                <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                    <h3 class="text-base font-bold text-[#1A1A2E] mb-5">Downloads — Last 6 Months</h3>
                    <?php if (empty($monthlyDl)): ?>
                    <div class="h-40 flex items-center justify-center text-sm text-[#9CA3AF]">No data available yet</div>
                    <?php else: ?>
                    <div class="flex items-end gap-3 h-44">
                        <?php foreach ($monthlyDl as $m):
                            $h = max(8, round(($m['count'] / $maxDl) * 160));
                        ?>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <span class="text-[10px] font-bold text-[#1A1A2E]"><?php echo $m['count']; ?></span>
                            <div class="w-full bg-[#B81C2E] rounded-t-md transition-all" style="height:<?php echo $h; ?>px"></div>
                            <span class="text-[9px] font-medium text-[#9CA3AF] text-center leading-tight"><?php echo $m['month']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Materials by Category -->
                <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                    <h3 class="text-base font-bold text-[#1A1A2E] mb-5">Materials by Category</h3>
                    <?php if (empty($catStats)): ?>
                    <div class="text-center text-sm text-[#9CA3AF] py-10">No category data yet</div>
                    <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($catStats as $c):
                            $pct = round(($c['count'] / $maxCat) * 100);
                        ?>
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-sm font-semibold text-[#1A1A2E]"><?php echo htmlspecialchars($c['category']); ?></span>
                                <span class="text-xs font-bold text-[#4A4A5A]"><?php echo $c['count']; ?></span>
                            </div>
                            <div class="h-2 bg-[#F4F4F6] rounded-full overflow-hidden">
                                <div class="h-full bg-[#B81C2E] rounded-full" style="width:<?php echo $pct; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Top Contributors -->
                <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                    <h3 class="text-base font-bold text-[#1A1A2E] mb-5">Top Contributors</h3>
                    <?php if (empty($topContributors)): ?>
                    <div class="text-center text-sm text-[#9CA3AF] py-10">No contributor data yet</div>
                    <?php else: ?>
                    <div class="divide-y divide-[#F4F4F6]">
                        <?php foreach ($topContributors as $i => $c):
                            $cColor = ['admin'=>'bg-[#B81C2E]','faculty'=>'bg-emerald-600','student'=>'bg-indigo-500'][$c['role']] ?? 'bg-gray-400';
                        ?>
                        <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                            <span class="text-sm font-bold text-[#D1D1D9] w-5 text-center">#<?php echo $i+1; ?></span>
                            <div class="w-8 h-8 <?php echo $cColor; ?> rounded-xl flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                <?php echo strtoupper(substr($c['full_name'], 0, 1)); ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-sm text-[#1A1A2E] truncate"><?php echo htmlspecialchars($c['full_name']); ?></div>
                                <div class="text-xs text-[#4A4A5A]"><?php echo $c['mat_count']; ?> materials · <?php echo number_format($c['total_dl']); ?> downloads</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- User Distribution -->
                <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                    <h3 class="text-base font-bold text-[#1A1A2E] mb-5">User Distribution</h3>
                    <div class="space-y-4">
                        <?php foreach ($roleDist as $r):
                            $pct = $totalR > 0 ? round(($r['count'] / $totalR) * 100) : 0;
                            $color = $roleColors[$r['role']] ?? '#9CA3AF';
                        ?>
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-sm font-semibold text-[#1A1A2E]"><?php echo ucfirst($r['role']); ?>s</span>
                                <span class="text-xs font-bold text-[#4A4A5A]"><?php echo $r['count']; ?> <span class="text-[#9CA3AF] font-medium">(<?php echo $pct; ?>%)</span></span>
                            </div>
                            <div class="h-2.5 bg-[#F4F4F6] rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width:<?php echo $pct; ?>%;background:<?php echo $color; ?>"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between items-center pt-5 mt-5 border-t border-[#F4F4F6]">
                        <span class="text-sm text-[#4A4A5A] font-medium">Total Reviews</span>
                        <span class="text-xl font-bold text-[#1A1A2E]"><?php echo number_format($totalReviews); ?></span>
                    </div>
                </div>
            </div>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

