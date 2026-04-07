<?php
/**
 * WMSU ARL Hub: Leaderboard — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth();

$period = $_GET['period'] ?? 'all';
$dateFilter = match($period) {
    'week'     => "AND m.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
    'month'    => "AND m.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
    'semester' => "AND m.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)",
    default    => "",
};

try {
    $leaderboard = $pdo->query("
        SELECT m.*, u.full_name,
            ROUND(AVG(r.rating),1) AS avg_rating,
            COUNT(DISTINCT r.id)   AS review_count
        FROM materials m
        LEFT JOIN users u ON m.contributor_id = u.id
        LEFT JOIN ratings r ON r.material_id = m.id
        WHERE m.status = 'approved' $dateFilter
        GROUP BY m.id
        ORDER BY m.downloads_count DESC, avg_rating DESC
        LIMIT 10
    ")->fetchAll();

    $categories = $pdo->query("
        SELECT category, COUNT(*) AS count, SUM(downloads_count) AS total_dl
        FROM materials WHERE status = 'approved'
        GROUP BY category ORDER BY total_dl DESC
    ")->fetchAll();
} catch (PDOException $e) { $leaderboard = []; $categories = []; }

$medals  = ['🥇', '🥈', '🥉'];
$periods = ['all' => 'All Time', 'week' => 'This Week', 'month' => 'This Month', 'semester' => 'This Semester'];
$maxDl   = !empty($categories) ? max(array_column($categories, 'total_dl') ?: [1]) : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Materials Leaderboard - WMSU ARL Hub</title>
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

            <!-- Page Header -->
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">Top Materials Leaderboard</h1>
                    <p class="text-[#4A4A5A] mt-1">The most accessed, rated, and celebrated academic resources.</p>
                </div>
                <!-- Period Filter -->
                <div class="flex items-center gap-2">
                    <?php foreach ($periods as $k => $v): ?>
                    <a href="?period=<?php echo $k; ?>"
                       class="px-3 py-2 rounded-lg text-xs font-semibold transition-colors <?php echo $period === $k ? 'bg-[#B81C2E] text-white' : 'bg-white text-[#4A4A5A] border border-[#E2E2E4] hover:border-[#B81C2E]'; ?>">
                        <?php echo $v; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-8 items-start">

                <!-- Leaderboard List -->
                <div>
                    <h2 class="text-[20px] font-bold text-[#1A1A2E] mb-5">Top 10 Resources</h2>
                    <?php if (empty($leaderboard)): ?>
                    <div class="bg-white rounded-xl border border-black/5 py-16 text-center">
                        <span class="material-symbols-outlined text-[48px] text-gray-200 block mb-3">emoji_events</span>
                        <p class="text-[#4A4A5A] text-sm font-medium">No data available for this period.</p>
                    </div>
                    <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($leaderboard as $i => $r): ?>
                        <a href="material-details.php?id=<?php echo $r['id']; ?>"
                           class="flex items-center gap-4 bg-white rounded-xl border <?php echo $i === 0 ? 'border-[#B81C2E]/30 shadow-sm shadow-[#B81C2E]/10' : 'border-black/[0.06]'; ?> p-4 hover:border-[#B81C2E]/40 hover:shadow-md transition-all">

                            <!-- Rank -->
                            <div class="w-10 text-center flex-shrink-0">
                                <?php if ($i < 3): ?>
                                <span class="text-2xl"><?php echo $medals[$i]; ?></span>
                                <?php else: ?>
                                <span class="text-base font-bold text-[#D1D1D9]">#<?php echo $i + 1; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Icon -->
                            <div class="w-11 h-11 <?php echo $i === 0 ? 'bg-[#F9E8EA]' : 'bg-[#F4F4F6]'; ?> rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-<?php echo $i === 0 ? '[#B81C2E]' : '[#4A4A5A]'; ?> text-[22px]">description</span>
                            </div>

                            <!-- Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap gap-1.5 mb-1">
                                    <?php if (!empty($r['is_official'])): ?>
                                    <span class="bg-amber-50 text-amber-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">Official</span>
                                    <?php endif; ?>
                                    <span class="bg-[#F4F4F6] text-[#4A4A5A] text-[9px] font-bold px-2 py-0.5 rounded-full uppercase"><?php echo htmlspecialchars($r['category']); ?></span>
                                </div>
                                <h3 class="font-bold text-sm text-[#1A1A2E] leading-snug truncate"><?php echo htmlspecialchars($r['title']); ?></h3>
                                <p class="text-xs text-[#4A4A5A] mt-0.5">by <?php echo htmlspecialchars($r['full_name'] ?? 'Unknown'); ?></p>
                            </div>

                            <!-- Stats -->
                            <div class="text-right flex-shrink-0">
                                <div class="text-base font-bold text-[#1A1A2E]"><?php echo number_format($r['downloads_count']); ?></div>
                                <div class="text-[10px] text-[#4A4A5A] uppercase tracking-wide font-medium">downloads</div>
                                <?php if ($r['avg_rating']): ?>
                                <div class="text-xs text-amber-500 font-bold mt-1 flex items-center justify-end gap-0.5">
                                    <span class="material-symbols-outlined text-[13px]" style="font-variation-settings:'FILL' 1;">star</span>
                                    <?php echo $r['avg_rating']; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Category Breakdown -->
                <div class="sticky top-[80px]">
                    <h2 class="text-[20px] font-bold text-[#1A1A2E] mb-5">By Category</h2>
                    <div class="bg-white rounded-xl border border-black/[0.06] overflow-hidden divide-y divide-[#F4F4F6]">
                        <?php foreach ($categories as $c):
                            $pct = $maxDl > 0 ? round(($c['total_dl'] / $maxDl) * 100) : 0;
                        ?>
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="font-semibold text-sm text-[#1A1A2E]"><?php echo htmlspecialchars($c['category']); ?></span>
                                <span class="text-xs font-semibold text-[#4A4A5A]"><?php echo number_format($c['total_dl']); ?> dl</span>
                            </div>
                            <div class="h-1.5 bg-[#F4F4F6] rounded-full overflow-hidden">
                                <div class="h-full bg-[#B81C2E] rounded-full transition-all" style="width: <?php echo $pct; ?>%"></div>
                            </div>
                            <div class="text-[11px] text-[#9CA3AF] mt-1"><?php echo $c['count']; ?> materials</div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                        <div class="p-8 text-center text-sm text-[#4A4A5A]">No category data available.</div>
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

