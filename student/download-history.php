<?php
/**
 * WMSU ARL Hub: Download History — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth();

$userId = $_SESSION['user_id'];
$search = trim($_GET['search'] ?? '');

try {
    $sql = "SELECT d.*, m.title, m.category, m.file_path, m.id AS material_id, u.full_name AS contributor
            FROM downloads d
            JOIN materials m ON d.material_id = m.id
            LEFT JOIN users u ON m.contributor_id = u.id
            WHERE d.user_id = ?";
    $params = [$userId];
    if ($search !== '') { $sql .= " AND m.title LIKE ?"; $params[] = "%$search%"; }
    $sql .= " ORDER BY d.downloaded_at DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    $history = $stmt->fetchAll();

    $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM downloads WHERE user_id = ?"); $totalStmt->execute([$userId]); $totalAccessed = $totalStmt->fetchColumn();
    $monthStmt = $pdo->prepare("SELECT COUNT(*) FROM downloads WHERE user_id = ? AND MONTH(downloaded_at) = MONTH(NOW())"); $monthStmt->execute([$userId]); $thisMonth = $monthStmt->fetchColumn();
} catch (PDOException $e) { $history=[]; $totalAccessed=0; $thisMonth=0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download History - WMSU ARL Hub</title>
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
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">Access History</h1>
                    <p class="text-[#4A4A5A] mt-1">All materials you've downloaded from the repository.</p>
                </div>
                <span class="text-sm text-[#4A4A5A] font-medium"><?php echo date('l, F d, Y'); ?></span>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 gap-5 mb-8">
                <div class="bg-white rounded-xl border border-black/[0.06] p-5 flex items-center gap-4">
                    <div class="w-11 h-11 bg-[#F9E8EA] text-[#B81C2E] rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">history</span>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-[#1A1A2E]"><?php echo number_format($totalAccessed); ?></div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-[#4A4A5A]">Total Accesses</div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-black/[0.06] p-5 flex items-center gap-4">
                    <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">calendar_month</span>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-[#1A1A2E]"><?php echo number_format($thisMonth); ?></div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-[#4A4A5A]">This Month</div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <form method="GET" class="flex gap-3 mb-6">
                <div class="relative flex-1 max-w-sm">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF] text-[17px]">search</span>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search your downloads..."
                           class="w-full pl-9 pr-4 h-10 bg-white border border-[#E2E2E4] rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E]">
                </div>
                <button type="submit" class="h-10 px-4 bg-[#1A1A2E] text-white text-sm font-semibold rounded-lg hover:bg-black transition-colors">Search</button>
                <?php if ($search): ?><a href="download-history.php" class="h-10 px-4 flex items-center gap-1 bg-white border border-[#E2E2E4] rounded-lg text-sm text-[#4A4A5A] hover:border-[#B81C2E] transition-colors">
                    <span class="material-symbols-outlined text-[14px]">close</span> Clear
                </a><?php endif; ?>
            </form>

            <!-- History -->
            <?php if (empty($history)): ?>
            <div class="bg-white rounded-xl border border-black/5 py-20 text-center">
                <span class="material-symbols-outlined text-[56px] text-gray-200 block mb-4">download_done</span>
                <h3 class="text-lg font-bold text-[#1A1A2E] mb-2"><?php echo $search ? 'No matching results' : 'No download history yet'; ?></h3>
                <p class="text-[#4A4A5A] text-sm mb-6"><?php echo $search ? 'Try a different search term.' : 'Start exploring the repository to build your history.'; ?></p>
                <a href="<?php echo BASE_URL; ?>core/browse.php" class="inline-flex items-center gap-2 bg-[#B81C2E] text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#8C1222] transition-colors">
                    <span class="material-symbols-outlined text-[16px]">grid_view</span> Browse Repository
                </a>
            </div>
            <?php else:
                $grouped = [];
                foreach ($history as $h) {
                    $date = date('F d, Y', strtotime($h['downloaded_at']));
                    $grouped[$date][] = $h;
                }
            ?>
            <?php foreach ($grouped as $date => $items): ?>
            <div class="mb-7">
                <div class="text-[10px] font-bold uppercase tracking-widest text-[#9CA3AF] mb-3 pl-1"><?php echo $date; ?></div>
                <div class="space-y-2">
                    <?php foreach ($items as $h): ?>
                    <div class="bg-white rounded-xl border border-black/[0.06] p-4 flex items-center gap-4 hover:border-black/10 hover:shadow-sm transition-all">
                        <div class="w-10 h-10 bg-[#F9E8EA] rounded-xl flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-outlined text-[#B81C2E] text-[20px]">description</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-sm text-[#1A1A2E] truncate"><?php echo htmlspecialchars($h['title']); ?></div>
                            <div class="flex items-center gap-2 mt-0.5 text-xs text-[#4A4A5A]">
                                <span class="bg-[#F4F4F6] px-1.5 py-0.5 rounded text-[9px] font-bold uppercase"><?php echo htmlspecialchars($h['category']); ?></span>
                                <?php if ($h['contributor']): ?>· by <?php echo htmlspecialchars($h['contributor']); ?><?php endif; ?>
                            </div>
                        </div>
                        <span class="text-xs text-[#9CA3AF] font-medium flex-shrink-0"><?php echo date('h:i A', strtotime($h['downloaded_at'])); ?></span>
                        <a href="<?php echo BASE_URL; ?>core/material-details.php?id=<?php echo $h['material_id']; ?>"
                           class="flex-shrink-0 flex items-center gap-1 bg-[#F4F4F6] text-[#4A4A5A] text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-[#B81C2E] hover:text-white transition-colors">
                            <span class="material-symbols-outlined text-[13px]">open_in_new</span> View
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

