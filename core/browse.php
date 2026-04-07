<?php
/**
 * WMSU ARL Hub: Masterpiece Browse Resources
 * Institutional Excellence — Pure CSS (No Frameworks)
 */
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/paths.php';
}
require_once '../config/auth.php';
// Remove checkAuth() to allow guest browsing

$isLoggedIn = isset($_SESSION['user_id']);
$search     = trim($_GET['q'] ?? '');
$category   = $_GET['category'] ?? 'All';

if (!$isLoggedIn) {
    require_once '../includes/header.php';
} else {
    $page_title = "Resources | WMSU ARL Hub";
}

$categories = [
    ['All', 'school'],
    ['Modules', 'auto_stories'],
    ['Handouts', 'description'],
    ['Past Exams', 'history_edu'],
    ['Research', 'science'],
    ['Thesis', 'account_balance'],
    ['Reviewer', 'quiz'],
    ['Textbook', 'menu_book'],
    ['Lecture Notes', 'edit_note']
];

try {
    $sql    = "SELECT m.*, u.full_name, COALESCE(AVG(r.rating),0) as avg_rating 
               FROM materials m 
               JOIN users u ON m.contributor_id = u.id 
               LEFT JOIN reviews r ON r.material_id = m.id 
               WHERE m.status = 'approved'";
    $params = [];
    
    if ($search) {
        $sql .= " AND (m.title LIKE ? OR m.description LIKE ? OR u.full_name LIKE ?)";
        $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
    }
    
    if ($category !== 'All') { 
        $sql .= " AND m.category = ?"; 
        $params[] = $category; 
    }
    
    $sql .= " GROUP BY m.id ORDER BY m.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $materials = $stmt->fetchAll();
} catch (PDOException $e) { 
    $materials = []; 
}

function getFileIcon($path) {
    $ext = strtolower(pathinfo($path ?? '', PATHINFO_EXTENSION));
    switch($ext) {
        case 'pdf': return 'description';
        case 'doc': case 'docx': return 'article';
        case 'ppt': case 'pptx': return 'slideshow';
        default: return 'insert_drive_file';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knowledge Repository - WMSU ARL Hub</title>
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f4f4f6; }
        h1, h2, h3, .brand-logo { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .filter-strip::-webkit-scrollbar { display: none; }
        .filter-strip { scrollbar-width: none; }
        .custom-scroll::-webkit-scrollbar { width: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</head>
<body class="text-rich-black overflow-x-hidden">

<?php if ($isLoggedIn): ?>
    <?php require_once '../includes/dashboard-nav.php'; ?>
    <div class="flex min-h-[calc(100vh-64px)]">
        <?php require_once '../includes/sidebar.php'; ?>
        <main class="ml-[240px] flex-1 bg-background flex flex-col pt-16">
<?php else: ?>
    <main class="w-full max-w-[1280px] mx-auto px-8 flex-col pt-8">
<?php endif; ?>

        <div class="p-8 flex-1">
            <header class="mb-10">
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-primary mb-2">Library</p>
                <h1 class="text-3xl font-black text-[#1A1A2E] tracking-tight">Browse Resources</h1>
            </header>

            <!-- ── Filter Interface ── -->
            <div class="mb-8 flex flex-wrap items-center gap-3 relative z-40">
                <div class="relative group">
                    <button type="button" class="flex items-center gap-3 bg-white border border-gray-200 text-[#1A1A2E] px-6 py-3 rounded-xl shadow-sm hover:border-primary hover:text-primary transition-all font-bold text-sm uppercase tracking-wider" onclick="document.getElementById('filterDropdown').classList.toggle('hidden')">
                        <span class="material-symbols-outlined text-[20px]">tune</span>
                        Filter Category
                        <span class="material-symbols-outlined text-[20px] ml-2 group-hover:translate-y-0.5 transition-transform">expand_more</span>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="filterDropdown" class="hidden absolute top-full left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl shadow-black/10 border border-gray-100 py-3 z-50">
                        <div class="px-4 pb-2 mb-2 border-b border-gray-50 text-[10px] font-black uppercase tracking-widest text-gray-400">Categories</div>
                        <div class="max-h-72 overflow-y-auto custom-scroll w-full">
                            <?php foreach ($categories as $cat): ?>
                                <a href="?category=<?php echo urlencode($cat[0]); ?>&q=<?php echo urlencode($search); ?>" 
                                   class="flex items-center justify-between px-4 py-2 hover:bg-gray-50 transition-colors <?php echo $category === $cat[0] ? 'text-primary bg-red-50/50' : 'text-[#4A4A5A]'; ?>">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-[18px] <?php echo $category === $cat[0] ? 'text-primary' : 'text-gray-400'; ?>"><?php echo $cat[1]; ?></span>
                                        <span class="text-sm font-semibold"><?php echo ($cat[0] === 'All' ? 'All Resources' : $cat[0]); ?></span>
                                    </div>
                                    <?php if ($category === $cat[0]): ?>
                                        <span class="material-symbols-outlined text-[16px] text-primary">check</span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Active Filter Chip Content -->
                <?php if ($category !== 'All'): ?>
                    <div class="flex items-center gap-3 bg-[#1A1A2E] text-white px-5 py-3 rounded-xl text-sm font-bold uppercase tracking-wider shadow-md shadow-gray-200">
                        <?php 
                            $catIcon = 'category';
                            foreach($categories as $c) if($c[0] === $category) $catIcon = $c[1];
                        ?>
                        <span class="material-symbols-outlined text-[18px] text-primary"><?php echo $catIcon; ?></span>
                        <?php echo htmlspecialchars($category); ?>
                        <a href="?category=All&q=<?php echo urlencode($search); ?>" class="ml-2 flex items-center justify-center hover:bg-white/20 rounded-full transition-colors p-0.5">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <script>
                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    const dropdown = document.getElementById('filterDropdown');
                    if (dropdown) {
                        const button = dropdown.previousElementSibling;
                        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                            dropdown.classList.add('hidden');
                        }
                    }
                });
            </script>

            <!-- ── Search Status ── -->
            <?php if ($search): ?>
                <div class="mb-8 flex items-center gap-4">
                    <span class="text-sm font-semibold text-[#4A4A5A]">Showing results for "<?php echo htmlspecialchars($search); ?>"</span>
                    <a href="browse.php" class="text-xs font-bold uppercase text-primary hover:underline">Clear Search</a>
                </div>
            <?php endif; ?>

            <!-- ── Resource Grid ── -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (count($materials) > 0): ?>
                    <?php foreach ($materials as $item): ?>
                        <div class="bg-white rounded-2xl border border-black/5 p-6 flex flex-col gap-4 transform transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl">
                            <div class="flex justify-between items-start">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-primary bg-red-50 px-2.5 py-1 rounded-md"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="material-symbols-outlined text-gray-300">description</span>
                            </div>

                            <h3 class="text-base font-bold text-[#1A1A2E] leading-snug line-clamp-2 min-h-[44px]"><?php echo htmlspecialchars($item['title']); ?></h3>
                            
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-primary">
                                    <?php echo strtoupper(substr($item['full_name'], 0, 1)); ?>
                                </div>
                                <div class="text-xs text-[#4A4A5A]">
                                    By <span class="font-bold text-[#1A1A2E]"><?php echo htmlspecialchars($item['full_name']); ?></span>
                                </div>
                            </div>

                            <div class="mt-auto pt-4 border-t border-gray-100 flex justify-between items-center">
                                <div class="flex items-center gap-1.5 text-gray-400">
                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                    <span class="text-xs font-bold"><?php echo number_format($item['downloads_count']); ?></span>
                                </div>
                                <a href="material-details.php?id=<?php echo $item['id']; ?>" class="bg-primary hover:bg-[#8C1222] text-white text-[11px] font-bold uppercase tracking-wide px-4 py-2 rounded-lg transition-colors">
                                    Inspect
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full flex flex-col items-center justify-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
                        <span class="material-symbols-outlined text-6xl text-gray-300 mb-4">find_in_page</span>
                        <h2 class="text-xl font-bold text-[#4A4A5A] mb-2">No Resources Found</h2>
                        <p class="text-sm text-gray-400 font-medium">Try adjusting your filters or search terms.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isLoggedIn): ?>
            <?php require_once '../includes/dashboard-footer.php'; ?>
        <?php endif; ?>

    </main>
<?php if ($isLoggedIn): ?>
    </div>
<?php else: ?>
    <?php include '../includes/footer.php'; ?>
<?php endif; ?>

</body>
</html>
