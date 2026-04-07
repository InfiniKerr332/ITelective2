<?php
/**
 * WMSU ARL Hub: Search Results — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth();

$query   = trim($_GET['q'] ?? '');
$cat     = $_GET['category'] ?? '';
$sort    = $_GET['sort'] ?? 'newest';
$results = [];
$total   = 0;

if ($query !== '') {
    $conditions = ["(m.title LIKE ? OR m.description LIKE ? OR u.full_name LIKE ?)", "m.status = 'approved'"];
    $params = ["%$query%", "%$query%", "%$query%"];
    if ($cat !== '') { $conditions[] = "m.category = ?"; $params[] = $cat; }
    $orderClause = match($sort) {
        'popular'   => "m.downloads_count DESC",
        'top_rated' => "avg_rating DESC",
        default     => "m.created_at DESC",
    };
    $where = implode(' AND ', $conditions);
    $sql = "SELECT m.*, u.full_name,
                ROUND(AVG(r.rating),1) AS avg_rating,
                COUNT(DISTINCT r.id)   AS review_count
            FROM materials m
            LEFT JOIN users u ON m.contributor_id = u.id
            LEFT JOIN ratings r ON r.material_id = m.id
            WHERE $where GROUP BY m.id ORDER BY $orderClause";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        $total   = count($results);
    } catch (PDOException $e) { $results = []; }
}

$categories = ['Modules', 'Handouts', 'Past Exams', 'Research'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results<?php echo $query ? " — \"$query\"" : ''; ?> - WMSU ARL Hub</title>
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

            <!-- Search Header -->
            <div class="mb-8">
                <h1 class="text-[28px] font-bold text-[#1A1A2E]">Search Materials</h1>
                <p class="text-[#4A4A5A] mt-1">Find academic resources from the WMSU community.</p>
            </div>

            <!-- Search Bar -->
            <form action="search-results.php" method="GET" class="flex gap-3 mb-6">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#9CA3AF] text-[18px]">search</span>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>"
                           placeholder="Search by title, subject, or author..."
                           class="w-full h-12 pl-10 pr-4 bg-white border border-[#E2E2E4] rounded-lg text-sm text-[#1A1A2E] placeholder-[#9CA3AF] focus:outline-none focus:ring-1 focus:ring-[#B81C2E] focus:border-[#B81C2E] transition-colors">
                </div>
                <?php if ($cat): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($cat); ?>"><?php endif; ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                <button type="submit" class="h-12 px-6 bg-[#B81C2E] text-white text-sm font-semibold rounded-lg hover:bg-[#8C1222] transition-colors flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">search</span> Search
                </button>
            </form>

            <?php if ($query !== ''): ?>
            <!-- Filters Row -->
            <div class="flex flex-wrap gap-6 items-center mb-6">
                <!-- Category filter -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-[#4A4A5A] uppercase tracking-wide">Category:</span>
                    <a href="search-results.php?q=<?php echo urlencode($query); ?>&sort=<?php echo $sort; ?>"
                       class="px-3 py-1.5 rounded-full text-xs font-semibold <?php echo $cat === '' ? 'bg-[#1A1A2E] text-white' : 'bg-white text-[#4A4A5A] border border-[#E2E2E4] hover:border-[#1A1A2E]'; ?> transition-colors">All</a>
                    <?php foreach ($categories as $c): ?>
                    <a href="search-results.php?q=<?php echo urlencode($query); ?>&category=<?php echo urlencode($c); ?>&sort=<?php echo $sort; ?>"
                       class="px-3 py-1.5 rounded-full text-xs font-semibold <?php echo $cat === $c ? 'bg-[#B81C2E] text-white' : 'bg-white text-[#4A4A5A] border border-[#E2E2E4] hover:border-[#B81C2E]'; ?> transition-colors"><?php echo $c; ?></a>
                    <?php endforeach; ?>
                </div>
                <!-- Sort filter -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-[#4A4A5A] uppercase tracking-wide">Sort:</span>
                    <?php foreach (['newest' => 'Newest', 'popular' => 'Most Downloaded', 'top_rated' => 'Top Rated'] as $key => $label): ?>
                    <a href="search-results.php?q=<?php echo urlencode($query); ?><?php echo $cat ? '&category='.urlencode($cat) : ''; ?>&sort=<?php echo $key; ?>"
                       class="px-3 py-1.5 rounded-full text-xs font-semibold <?php echo $sort === $key ? 'bg-[#1A1A2E] text-white' : 'bg-white text-[#4A4A5A] border border-[#E2E2E4] hover:border-[#1A1A2E]'; ?> transition-colors"><?php echo $label; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <p class="text-sm text-[#4A4A5A] mb-6">
                Found <span class="font-bold text-[#1A1A2E]"><?php echo $total; ?></span> result<?php echo $total !== 1 ? 's' : ''; ?> for
                "<span class="font-bold text-[#B81C2E]"><?php echo htmlspecialchars($query); ?></span>"
                <?php if ($cat): ?> in <span class="font-semibold"><?php echo htmlspecialchars($cat); ?></span><?php endif; ?>
            </p>

            <!-- Results -->
            <?php if (empty($results)): ?>
            <div class="bg-white rounded-xl border border-black/5 py-20 text-center">
                <span class="material-symbols-outlined text-[56px] text-gray-200 block mb-4">search_off</span>
                <h3 class="text-lg font-bold text-[#1A1A2E] mb-2">No results found</h3>
                <p class="text-[#4A4A5A] text-sm">Try different keywords or remove category filters.</p>
            </div>
            <?php else: ?>
            <div class="flex flex-col gap-4">
                <?php foreach ($results as $r):
                    $ext   = strtoupper(pathinfo($r['file_path'] ?? '', PATHINFO_EXTENSION)) ?: 'DOC';
                    $stars = (float)($r['avg_rating'] ?? 0);
                ?>
                <div class="bg-white rounded-xl border border-black/[0.08] hover:border-[#B81C2E]/30 hover:shadow-md transition-all p-5 flex items-center gap-5">
                    <div class="w-12 h-12 bg-[#F9E8EA] rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[#B81C2E] text-[24px]">description</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="bg-[#F4F4F6] text-[#4A4A5A] text-[10px] font-bold px-2 py-0.5 rounded-full uppercase"><?php echo htmlspecialchars($r['category']); ?></span>
                            <span class="bg-[#F4F4F6] text-[#4A4A5A] text-[10px] font-medium px-2 py-0.5 rounded-full uppercase"><?php echo $ext; ?></span>
                            <?php if (!empty($r['is_official'])): ?>
                            <span class="bg-amber-50 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Official</span>
                            <?php endif; ?>
                        </div>
                        <a href="material-details.php?id=<?php echo $r['id']; ?>">
                            <h3 class="text-base font-bold text-[#1A1A2E] hover:text-[#B81C2E] transition-colors leading-snug"><?php echo htmlspecialchars($r['title']); ?></h3>
                        </a>
                        <div class="flex items-center gap-4 mt-1 text-xs text-[#4A4A5A]">
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">person</span><?php echo htmlspecialchars($r['full_name'] ?? ''); ?></span>
                            <span class="flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">download</span><?php echo number_format($r['downloads_count'] ?? 0); ?></span>
                            <?php if ($stars > 0): ?>
                            <span class="flex items-center gap-1 text-amber-500"><span class="material-symbols-outlined text-[13px]" style="font-variation-settings:'FILL' 1;">star</span><?php echo $stars; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="material-details.php?id=<?php echo $r['id']; ?>"
                       class="flex-shrink-0 flex items-center gap-1.5 bg-[#1A1A2E] text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:bg-black transition-colors">
                        <span class="material-symbols-outlined text-[15px]">open_in_new</span> View
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- Empty search state -->
            <div class="bg-white rounded-xl border border-black/5 py-20 text-center">
                <span class="material-symbols-outlined text-[60px] text-gray-200 block mb-4">manage_search</span>
                <h3 class="text-lg font-bold text-[#1A1A2E] mb-2">Start your search</h3>
                <p class="text-[#4A4A5A] text-sm mb-6">Enter a keyword above to find academic materials.</p>
                <a href="browse.php" class="inline-flex items-center gap-2 bg-[#1A1A2E] text-white px-5 py-2.5 rounded-md text-sm font-semibold">
                    <span class="material-symbols-outlined text-[16px]">grid_view</span> Browse All Materials
                </a>
            </div>
            <?php endif; ?>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

