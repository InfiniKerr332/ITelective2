<?php
/**
 * WMSU ARL Hub: Student Dashboard
 * Institutional Masterpiece — High Fidelity Modern Design
 */
require_once '../config/auth.php';
checkAuth('student');

$userId    = $_SESSION['user_id'];
$fullName  = $_SESSION['full_name'] ?? 'Crimson Scholar';
$firstName = explode(' ', trim($fullName))[0];
$userInitials = strtoupper(substr($firstName, 0, 1) . (str_contains($fullName, ' ') ? substr(explode(' ', $fullName)[1], 0, 1) : ''));

$avgRating = "4.8"; // Default placeholder if not implemented yet

try {
    // Statistics for the user
    $myUploadsCount = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE contributor_id = ?");
    $myUploadsCount->execute([$userId]);
    $myUploadsCount = $myUploadsCount->fetchColumn();

    $myDownloadsCount = $pdo->prepare("SELECT COUNT(*) FROM downloads WHERE user_id = ?");
    $myDownloadsCount->execute([$userId]);
    $myDownloadsCount = $myDownloadsCount->fetchColumn();

    $totalSubjectsCount = $pdo->query("SELECT COUNT(DISTINCT category) FROM materials WHERE status='approved'")->fetchColumn();
    
    // Recent Uploads (Site-wide Feed)
    $stmt = $pdo->prepare("
        SELECT m.*, m.category as category_name 
        FROM materials m 
        WHERE m.status = 'approved' 
        ORDER BY m.created_at DESC LIMIT 3
    ");
    $stmt->execute();
    $recentUploads = $stmt->fetchAll();

    // Recent Downloads (User Specific)
    $stmt = $pdo->prepare("
        SELECT m.*, d.downloaded_at, m.category as category_name 
        FROM downloads d
        JOIN materials m ON d.material_id = m.id
        WHERE d.user_id = ?
        ORDER BY d.downloaded_at DESC LIMIT 3
    ");
    $stmt->execute([$userId]);
    $recentDownloads = $stmt->fetchAll();

} catch (PDOException $e) {
    // Graceful fallback
    $myUploadsCount = $myDownloadsCount = $totalSubjectsCount = 0;
    $recentUploads = $recentDownloads = [];
}

/**
 * Utility: Extract File Extension for UI Labels
 */
function getLabel($filePath) {
    if (empty($filePath)) return 'PDF';
    $ext = strtoupper(pathinfo($filePath, PATHINFO_EXTENSION));
    return !empty($ext) ? $ext : 'PDF';
}

function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;
    if ($diff < 60) return "Just now";
    if ($diff < 3600) return round($diff/60) . "m ago";
    if ($diff < 86400) return round($diff/3600) . "h ago";
    return date('M d, Y', $time);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Dashboard - WMSU ARL Hub</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
          tailwind.config = {
            darkMode: "class",
            theme: {
              extend: {
                colors: {
                  "primary": "#B81C2E",       // WMSU Red
                  "primary-dark": "#8C1222",
                  "rich-black": "#1A1A2E",    // Background/Sidebar Rich Black
                  "surface": "#ffffff",
                  "background": "#F4F4F6",
                  "text-muted": "#4A4A5A"
                },
                fontFamily: {
                  "headline": ["Plus Jakarta Sans"],
                  "body": ["Inter"]
                },
                borderRadius: {"DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem"},
              },
            },
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
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="text-rich-black overflow-x-hidden">

<?php require_once '../includes/dashboard-nav.php'; ?>

<div class="flex min-h-[calc(100vh-64px)]">
    <?php require_once '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="ml-[240px] flex-1 bg-background flex flex-col pt-16">
        <div class="p-8 flex-1 min-h-[calc(100vh-64px)]">
            
            <!-- Header & Actions -->
            <div class="flex justify-between items-start mb-10">
                <div>
                    <h1 class="text-[28px] font-bold text-rich-black">Dashboard</h1>
                    <p class="text-text-muted mt-1">Welcome back, <?php echo htmlspecialchars($fullName); ?>!</p>
                </div>
                <div class="flex gap-4">
                    <a href="../core/browse.php" class="bg-white border border-primary/20 text-primary font-medium py-2.5 px-5 rounded-md text-sm hover:bg-primary/5 transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">search</span>
                        Browse All Materials
                    </a>
                    <a href="../core/upload.php" class="bg-primary text-white font-medium py-2.5 px-5 rounded-md text-sm hover:bg-primary-dark transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Upload New Material
                    </a>
                </div>
            </div>

            <!-- Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="bg-white p-5 rounded-xl border border-black/5 flex flex-col">
                    <span class="text-text-muted text-xs font-medium uppercase tracking-wide mb-1">My Uploads</span>
                    <span class="text-2xl font-bold text-rich-black"><?php echo number_format($myUploadsCount); ?></span>
                    <div class="mt-4 flex items-center text-[11px] text-[#2E7D32] bg-[#E8F5E9] w-fit px-2 py-0.5 rounded-full">
                        <span class="material-symbols-outlined text-[14px] mr-1">trending_up</span>
                        Active Contributor
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-black/5 flex flex-col">
                    <span class="text-text-muted text-xs font-medium uppercase tracking-wide mb-1">Downloads</span>
                    <span class="text-2xl font-bold text-rich-black"><?php echo number_format($myDownloadsCount); ?></span>
                    <div class="mt-4 flex items-center text-[11px] text-[#2E7D32] bg-[#E8F5E9] w-fit px-2 py-0.5 rounded-full">
                        <span class="material-symbols-outlined text-[14px] mr-1">visibility</span>
                        Expanding Knowledge
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-black/5 flex flex-col">
                    <span class="text-text-muted text-xs font-medium uppercase tracking-wide mb-1">Avg. Rating</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-rich-black"><?php echo $avgRating; ?></span>
                        <span class="text-xs text-text-muted">/ 5.0</span>
                    </div>
                    <div class="mt-4 flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">star</span>
                        <span class="material-symbols-outlined text-[16px] text-[#D1D1D9]">star</span>
                    </div>
                </div>
                <div class="bg-white p-5 rounded-xl border border-black/5 flex flex-col">
                    <span class="text-text-muted text-xs font-medium uppercase tracking-wide mb-1">Categories</span>
                    <span class="text-2xl font-bold text-rich-black"><?php echo number_format($totalSubjectsCount); ?></span>
                    <div class="mt-4 flex items-center text-[11px] text-text-muted bg-background w-fit px-2 py-0.5 rounded-full">
                        Active Semester
                    </div>
                </div>
            </div>

            <!-- Main Bento Sections -->
            <div class="grid grid-cols-1 gap-10">
                <!-- Recent Uploads Section -->
                <section>
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-[22px] font-bold text-rich-black">Recent Publications</h2>
                        <a class="text-primary text-sm font-semibold hover:underline" href="../core/browse.php">View All</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (empty($recentUploads)): ?>
                            <div class="col-span-full py-12 text-center bg-white rounded-xl border border-black/5">
                                <span class="material-symbols-outlined text-[48px] text-text-muted/20">find_in_page</span>
                                <p class="text-text-muted/60 mt-2 text-sm">No recent materials found.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($recentUploads as $item): ?>
                            <div class="bg-white p-5 rounded-[10px] border border-black/[0.08] hover:border-primary/25 hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)] transition-all relative">
                                <div class="flex justify-between items-start mb-4">
                                    <span class="bg-primary/10 text-primary text-[11px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider"><?php echo getLabel($item['file_path'] ?? 'PDF'); ?></span>
                                    <span class="text-[12px] text-text-muted"><?php echo date('M d, Y', strtotime($item['created_at'])); ?></span>
                                </div>
                                <h3 class="text-base font-semibold text-rich-black leading-snug mb-2 line-clamp-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="bg-background text-text-muted text-[11px] font-medium px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-background">
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">star</span>
                                        <span class="text-xs font-medium text-rich-black">4.8</span>
                                    </div>
                                    <span class="text-[12px] text-text-muted"><?php echo $item['downloads_count'] ?? 0; ?> downloads</span>
                                </div>
                                <a href="../core/material-details.php?id=<?php echo $item['id']; ?>" class="absolute inset-0 z-10"></a>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Recent Downloads Section -->
                <section>
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-[22px] font-bold text-rich-black">Recent Downloads</h2>
                        <a class="text-primary text-sm font-semibold hover:underline" href="download-history.php">View History</a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (empty($recentDownloads)): ?>
                            <div class="col-span-full py-12 text-center bg-white rounded-xl border border-black/5">
                                <span class="material-symbols-outlined text-[48px] text-text-muted/20">download</span>
                                <p class="text-text-muted/60 mt-2 text-sm">You haven't downloaded anything yet.</p>
                                <a href="../core/browse.php" class="text-primary text-xs font-bold hover:underline mt-2 inline-block italic uppercase tracking-wider">Start Browsing</a>
                            </div>
                        <?php else: ?>
                            <?php foreach($recentDownloads as $item): ?>
                            <div class="bg-white p-5 rounded-[10px] border border-black/[0.08] hover:border-primary/25 hover:shadow-[0_2px_8px_rgba(0,0,0,0.08)] transition-all">
                                <div class="flex justify-between items-start mb-4">
                                    <span class="bg-primary/10 text-primary text-[11px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider"><?php echo getLabel($item['file_path'] ?? 'PDF'); ?></span>
                                    <span class="text-[12px] text-text-muted">Downloaded <?php echo timeAgo($item['downloaded_at']); ?></span>
                                </div>
                                <h3 class="text-base font-semibold text-rich-black leading-snug mb-2 line-clamp-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="bg-background text-text-muted text-[11px] font-medium px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                </div>
                                <div class="flex items-center justify-between pt-4 border-t border-background">
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-primary" style="font-variation-settings: 'FILL' 1;">star</span>
                                        <span class="text-xs font-medium text-rich-black">4.9</span>
                                    </div>
                                    <a href="../actions/download-handler.php?id=<?php echo $item['id']; ?>" class="text-primary text-xs font-bold flex items-center gap-1 hover:underline">
                                        <span class="material-symbols-outlined text-[14px]">download</span> Re-download
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>

        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>

</body>
</html>
