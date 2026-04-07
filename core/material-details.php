<?php
/**
 * WMSU ARL Hub: Material Details — Premium Stitch Design System
 */
if (!defined('BASE_URL')) {
    require_once dirname(__DIR__) . '/config/paths.php';
}
require_once '../config/auth.php';

$isLoggedIn = isset($_SESSION['user_id']);
$id = (int)($_GET['id'] ?? 0);

try {
    // Correctly using 'reviews' table for both aggregate and individual reviews
    $stmt = $pdo->prepare("SELECT m.*, u.full_name, u.role as contributor_role,
        ROUND(COALESCE(AVG(r.rating), 0), 1) AS avg_rating, COUNT(DISTINCT r.id) AS review_count
        FROM materials m
        JOIN users u ON m.contributor_id = u.id
        LEFT JOIN reviews r ON r.material_id = m.id
        WHERE m.id = ? GROUP BY m.id");
    $stmt->execute([$id]);
    $material = $stmt->fetch();
    
    if (!$material) { 
        header("Location: browse.php"); 
        exit(); 
    }

    $stmt = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.material_id = ? ORDER BY r.created_at DESC");
    $stmt->execute([$id]);
    $reviews = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM material_files WHERE material_id = ?");
    $stmt->execute([$id]);
    $materialFiles = $stmt->fetchAll();

    // Fallback if no records in material_files (legacy)
    if (empty($materialFiles) && !empty($material['file_path'])) {
        $materialFiles[] = [
            'id' => 0, // indicates primary file
            'file_path' => $material['file_path'],
            'file_name' => basename($material['file_path']),
            'file_type' => strtolower(pathinfo($material['file_path'], PATHINFO_EXTENSION))
        ];
    }

    $ext = strtoupper(pathinfo($material['file_path'] ?? '', PATHINFO_EXTENSION)) ?: 'DOC';
    $stars = (float)($material['avg_rating'] ?? 0);
} catch (PDOException $e) { 
    die("Database error: " . $e->getMessage()); 
}

function getFileIconLarge($path) {
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
    <title><?php echo htmlspecialchars($material['title']); ?> - WMSU ARL Hub</title>
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
        h1, h2, h3, .font-headline { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
        .premium-card { background: white; border-radius: 2.5rem; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.02); }
        .viewer-frame { width: 100%; min-height: 600px; border: none; border-radius: 1.5rem; background: #F9F9FB; }
        .viewer-tabs button { transition: all 0.25s ease; }
        .viewer-tabs button.active { background: #B81C2E; color: white; }
    </style>
</head>
<body class="text-[#1A1A2E]">

<?php require_once '../includes/dashboard-nav.php'; ?>

<div class="flex min-h-[calc(100vh-64px)]">
    <?php require_once '../includes/sidebar.php'; ?>

    <main class="ml-[240px] flex-1 bg-[#F4F4F6] flex flex-col">
        <div class="p-10 flex-1 space-y-12">

            <!-- ── Navigation & Header ── -->
            <div class="flex flex-col gap-6">
                <nav class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-[#9CA3AF]">
                    <a href="<?php echo BASE_URL; ?>index.php" class="hover:text-primary transition-colors">Home</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <a href="browse.php" class="hover:text-primary transition-colors">Digital Library</a>
                    <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    <span class="text-primary truncate max-w-[300px]"><?php echo htmlspecialchars($material['title']); ?></span>
                </nav>
                
                <div class="flex items-start justify-between gap-12">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="bg-primary/5 text-primary text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest border border-primary/10"><?php echo htmlspecialchars($material['category']); ?></span>
                            <?php if (!empty($material['contributor_role']) && $material['contributor_role'] === 'faculty'): ?>
                            <span class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-3 py-1.5 rounded-full uppercase tracking-widest border border-indigo-100 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;">verified</span> Professional Resource
                            </span>
                            <?php endif; ?>
                        </div>
                        <h1 class="text-[42px] font-black text-[#1A1A2E] leading-[1.1] tracking-tight uppercase mb-4"><?php echo htmlspecialchars($material['title']); ?></h1>
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-2 text-amber-500 font-black text-[14px]">
                                <div class="flex">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="material-symbols-outlined text-[18px]" style="<?php echo $i <= round($stars) ? "font-variation-settings:'FILL' 1;" : "color:#D1D1D9;"; ?>">star</span>
                                    <?php endfor; ?>
                                </div>
                                <span><?php echo $stars; ?></span>
                                <span class="text-[#9CA3AF] opacity-50 font-bold ml-1">• <?php echo $material['review_count']; ?> Global Ratings</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_380px] gap-10 items-start">

                <!-- ── Primary Details View ── -->
                <div class="space-y-10">
                    
                    <!-- Overview Card -->
                    <div class="premium-card p-10">
                        <div class="flex items-center gap-10 mb-10 pb-10 border-b border-[#F4F4F6]">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-[#F4F4F6] rounded-2xl flex items-center justify-center text-[#1A1A2E]">
                                    <span class="material-symbols-outlined text-[24px]">account_circle</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black text-[#9CA3AF] uppercase tracking-widest">Shared By</span>
                                    <span class="text-[14px] font-black text-[#1A1A2E]"><?php echo htmlspecialchars($material['full_name']); ?></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-[#F4F4F6] rounded-2xl flex items-center justify-center text-[#1A1A2E]">
                                    <span class="material-symbols-outlined text-[24px]">calendar_month</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black text-[#9CA3AF] uppercase tracking-widest">Date Published</span>
                                    <span class="text-[14px] font-black text-[#1A1A2E]"><?php echo date('F j, Y', strtotime($material['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-[#F4F4F6] rounded-2xl flex items-center justify-center text-[#1A1A2E]">
                                    <span class="material-symbols-outlined text-[24px]">analytics</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black text-[#9CA3AF] uppercase tracking-widest">Total Usage</span>
                                    <span class="text-[14px] font-black text-[#1A1A2E]"><?php echo number_format($material['downloads_count'] ?? 0); ?> Downloads</span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <h2 class="text-[20px] font-black text-[#1A1A2E] uppercase tracking-tight">Executive Summary</h2>
                            <p class="text-[#4A4A5A] leading-[1.8] text-[16px] font-medium opacity-80"><?php echo nl2br(htmlspecialchars($material['description'] ?? 'No formal description provided for this resource.')); ?></p>
                        </div>
                    </div>

                    <!-- ── Inline Document Viewer ── -->
                    <?php 
                        $primaryFile = $materialFiles[0] ?? null;
                        $primaryUrl = $primaryFile ? BASE_URL . $primaryFile['file_path'] : '';
                        $primaryExt = $primaryFile ? strtolower($primaryFile['file_type'] ?? pathinfo($primaryFile['file_path'], PATHINFO_EXTENSION)) : '';
                        $isPdf = $primaryExt === 'pdf';
                        $isImageFile = in_array($primaryExt, ['jpg','jpeg','png','webp']);
                    ?>
                    <div class="premium-card p-10">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-[20px] font-black text-[#1A1A2E] uppercase tracking-tight">Document Preview</h2>
                                <p class="text-[12px] font-bold text-[#9CA3AF] uppercase tracking-widest mt-1">Click to view · No download required</p>
                            </div>
                            <?php if (count($materialFiles) > 1): ?>
                            <div class="viewer-tabs flex gap-2">
                                <?php foreach ($materialFiles as $idx => $vf): ?>
                                <button onclick="switchViewer(<?php echo $idx; ?>)" 
                                        id="vtab-<?php echo $idx; ?>"
                                        class="px-4 py-2 rounded-xl text-[11px] font-bold uppercase tracking-wider <?php echo $idx === 0 ? 'active' : 'bg-[#F4F4F6] text-[#4A4A5A] hover:bg-[#E9E9EF]'; ?>">
                                    <?php echo strtoupper($vf['file_type'] ?? pathinfo($vf['file_path'], PATHINFO_EXTENSION)); ?> <?php echo $idx + 1; ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Viewer Container -->
                        <?php foreach ($materialFiles as $idx => $vf): ?>
                        <?php 
                            $vUrl = BASE_URL . $vf['file_path'];
                            $vExt = strtolower($vf['file_type'] ?? pathinfo($vf['file_path'], PATHINFO_EXTENSION));
                            $vIsPdf = $vExt === 'pdf';
                            $vIsImage = in_array($vExt, ['jpg','jpeg','png','webp']);
                        ?>
                        <div class="viewer-panel <?php echo $idx > 0 ? 'hidden' : ''; ?>" id="viewer-<?php echo $idx; ?>">
                            <?php if ($vIsPdf): ?>
                                <iframe src="<?php echo $vUrl; ?>#toolbar=1&navpanes=0" class="viewer-frame" title="PDF Viewer"></iframe>
                            <?php elseif ($vIsImage): ?>
                                <div class="bg-[#F9F9FB] rounded-[1.5rem] p-4 flex items-center justify-center" style="min-height:400px;">
                                    <img src="<?php echo $vUrl; ?>" alt="<?php echo htmlspecialchars($vf['file_name']); ?>" class="max-w-full max-h-[560px] rounded-2xl object-contain shadow-lg">
                                </div>
                            <?php else: ?>
                                <div class="bg-[#F9F9FB] rounded-[1.5rem] p-16 text-center" style="min-height:300px;">
                                    <div class="w-20 h-20 bg-primary/5 rounded-3xl flex items-center justify-center mx-auto mb-6">
                                        <span class="material-symbols-outlined text-primary text-[42px]"><?php echo getFileIconLarge($vf['file_path']); ?></span>
                                    </div>
                                    <h3 class="text-lg font-black text-[#1A1A2E] mb-2 uppercase"><?php echo strtoupper($vExt); ?> Document</h3>
                                    <p class="text-sm text-[#4A4A5A] mb-6">This file type cannot be previewed in the browser.</p>
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="<?php echo $vUrl; ?>" target="_blank" 
                                           class="inline-flex items-center gap-2 bg-[#1A1A2E] text-white px-6 py-3 rounded-xl text-[12px] font-bold uppercase tracking-wider hover:bg-black transition-all">
                                            <span class="material-symbols-outlined text-[18px]">open_in_new</span> Open in New Tab
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>actions/download-handler.php?<?php echo ($vf['id'] > 0 ? 'file_id='.$vf['id'] : 'id='.$id); ?>" 
                                           class="inline-flex items-center gap-2 bg-primary text-white px-6 py-3 rounded-xl text-[12px] font-bold uppercase tracking-wider hover:bg-[#8C1222] transition-all">
                                            <span class="material-symbols-outlined text-[18px]">download</span> Download
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Resource Files Gallery -->
                    <div class="premium-card p-10">
                        <div class="flex justify-between items-center mb-10">
                            <div>
                                <h2 class="text-[20px] font-black text-[#1A1A2E] uppercase tracking-tight">Resource Attachments</h2>
                                <p class="text-[12px] font-bold text-[#9CA3AF] uppercase tracking-widest mt-1"><?php echo count($materialFiles); ?> File<?php echo count($materialFiles) !== 1 ? 's' : ''; ?> Included</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <?php foreach ($materialFiles as $file): ?>
                                <?php 
                                    $isImage = in_array(strtolower($file['file_type']), ['jpg', 'jpeg', 'png', 'webp']);
                                    $fileUrl = BASE_URL . $file['file_path'];
                                    $downloadUrl = BASE_URL . "actions/download-handler.php?" . ($file['id'] > 0 ? "file_id=" . $file['id'] : "id=" . $id);
                                ?>
                                <div class="group relative bg-[#F9F9FB] rounded-[2rem] border border-black/[0.04] overflow-hidden hover:border-primary/20 transition-all">
                                    <?php if ($isImage): ?>
                                        <div class="aspect-video relative overflow-hidden bg-white">
                                            <img src="<?php echo $fileUrl; ?>" alt="<?php echo htmlspecialchars($file['file_name']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                                                <a href="<?php echo $fileUrl; ?>" target="_blank" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#1A1A2E] hover:bg-primary hover:text-white transition-all">
                                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                                </a>
                                                <a href="<?php echo $downloadUrl; ?>" class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#1A1A2E] hover:bg-primary hover:text-white transition-all">
                                                    <span class="material-symbols-outlined text-[20px]">download</span>
                                                </a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="aspect-video flex flex-col items-center justify-center gap-3 bg-white p-6">
                                            <div class="w-14 h-14 bg-primary/5 rounded-2xl flex items-center justify-center">
                                                <span class="material-symbols-outlined text-primary text-[32px]"><?php echo getFileIconLarge($file['file_path']); ?></span>
                                            </div>
                                            <span class="text-[10px] font-black uppercase text-primary tracking-widest"><?php echo strtoupper($file['file_type']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="p-6 flex items-center justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <span class="block text-[13px] font-bold text-[#1A1A2E] truncate" title="<?php echo htmlspecialchars($file['file_name']); ?>">
                                                <?php echo htmlspecialchars($file['file_name']); ?>
                                            </span>
                                            <?php if (isset($file['file_size']) && $file['id'] > 0): ?>
                                                <span class="block text-[10px] font-bold text-[#9CA3AF] uppercase tracking-widest mt-1">
                                                    <?php echo round($file['file_size'] / (1024 * 1024), 2) ?: round($file['file_size'] / 1024, 1); ?> 
                                                    <?php echo ($file['file_size'] > 1024 * 1024) ? 'MB' : 'KB'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!$isImage): ?>
                                            <a href="<?php echo $downloadUrl; ?>" class="w-10 h-10 bg-white border border-black/5 rounded-xl flex items-center justify-center text-[#1A1A2E] hover:bg-primary hover:text-white hover:border-primary transition-all shrink-0">
                                                <span class="material-symbols-outlined text-[20px]">download</span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Community Discourse -->
                    <div class="premium-card p-10">
                        <div class="flex justify-between items-center mb-10">
                            <div>
                                <h2 class="text-[20px] font-black text-[#1A1A2E] uppercase tracking-tight">Community Discussion</h2>
                                <p class="text-[12px] font-bold text-[#9CA3AF] uppercase tracking-widest mt-1"><?php echo count($reviews); ?> Professional Insight<?php echo count($reviews) !== 1 ? 's' : ''; ?></p>
                            </div>
                        </div>

                        <?php if ($isLoggedIn): ?>
                        <div class="mb-12 bg-[#F9F9FB] p-8 rounded-3xl border border-black/[0.04]">
                            <form action="<?php echo BASE_URL; ?>actions/review-handler.php" method="POST" class="space-y-6">
                                <input type="hidden" name="material_id" value="<?php echo $id; ?>">
                                <div class="flex items-center gap-4 mb-4">
                                    <span class="text-[11px] font-black text-[#1A1A2E] uppercase tracking-widest">Assign Quality Rating:</span>
                                    <div class="flex gap-1">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                        <button type="button" onclick="setRating(<?php echo $i; ?>)" class="rating-star group" id="star-<?php echo $i; ?>">
                                            <span class="material-symbols-outlined text-[24px] text-gray-200 group-hover:text-amber-400 transition-colors">star</span>
                                        </button>
                                        <?php endfor; ?>
                                        <input type="hidden" name="rating" id="rating-input" value="5">
                                    </div>
                                </div>
                                <div class="relative">
                                    <textarea name="comment" rows="4" required
                                              placeholder="Provide your professional feedback or ask a question about this resource..."
                                              class="w-full px-6 py-5 bg-white border border-black/[0.06] rounded-2xl text-[14px] font-medium text-[#1A1A2E] placeholder-[#9CA3AF] focus:ring-2 focus:ring-primary/10 transition-all resize-none shadow-sm"></textarea>
                                </div>
                                <button type="submit" class="w-full h-14 bg-[#1A1A2E] text-white text-[13px] font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all shadow-xl shadow-black/10 flex items-center justify-center gap-3">
                                    Submit Feedback <span class="material-symbols-outlined text-[18px]">rocket_launch</span>
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>

                        <div class="space-y-8">
                            <?php if (empty($reviews)): ?>
                            <div class="text-center py-20 bg-[#F9F9FB] rounded-[2.5rem] border border-dashed border-black/[0.06]">
                                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                                    <span class="material-symbols-outlined text-[32px] text-gray-200">chat_bubble</span>
                                </div>
                                <p class="text-[#9CA3AF] text-[13px] font-bold uppercase tracking-widest">No discourse recorded yet.</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($reviews as $rev): ?>
                            <div class="flex gap-6 group">
                                <div class="w-14 h-14 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 font-black text-lg shrink-0">
                                    <?php echo strtoupper(substr($rev['full_name'], 0, 1)); ?>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="text-[14px] font-black text-[#1A1A2E] uppercase tracking-tight"><?php echo htmlspecialchars($rev['full_name']); ?></span>
                                            <span class="text-[10px] font-bold text-[#9CA3AF] uppercase tracking-widest opacity-60"><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></span>
                                        </div>
                                        <div class="flex items-center gap-0.5">
                                            <?php for($i=1; $i<=5; $i++): ?>
                                            <span class="material-symbols-outlined text-[14px] <?php echo $i <= ($rev['rating'] ?? 5) ? 'text-amber-500' : 'text-gray-200'; ?>" style="font-variation-settings:'FILL' 1;">star</span>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <p class="text-[15px] text-[#4A4A5A] leading-relaxed font-medium opacity-70"><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ── Actionable Sidebar ── -->
                <div class="space-y-8 sticky top-[100px]">
                    
                    <!-- Premium Access Card -->
                    <div class="bg-[#1A1A2E] rounded-[2.5rem] p-10 text-white shadow-2xl shadow-primary/10 relative overflow-hidden border border-white/5">
                        <div class="absolute -top-12 -right-12 w-48 h-48 bg-primary/20 rounded-full blur-[60px]"></div>
                        
                        <div class="relative z-10">
                            <div class="w-20 h-20 bg-primary/20 rounded-3xl flex items-center justify-center mb-8 border border-white/10">
                                <span class="material-symbols-outlined text-[#B81C2E] text-[42px]" style="font-variation-settings: 'FILL' 1;"><?php echo getFileIconLarge($material['file_path'] ?? ''); ?></span>
                            </div>
                            
                            <h3 class="text-2xl font-black mb-4 uppercase tracking-tight">Institutional Access</h3>
                            <p class="text-white/40 text-[14px] font-medium leading-relaxed mb-10">Please ensure this material align with your WMSU curriculum standards before proceeding.</p>

                            <?php if ($isLoggedIn): ?>
                            <a href="<?php echo BASE_URL; ?>actions/download-handler.php?id=<?php echo $id; ?>"
                               class="group flex items-center justify-center gap-4 w-full bg-primary text-white h-16 rounded-2xl text-[13px] font-black uppercase tracking-widest hover:bg-[#8C1222] transition-all shadow-xl shadow-primary/20 active:scale-95">
                                Download Resource <span class="material-symbols-outlined text-[20px] transition-transform group-hover:rotate-45">download</span>
                            </a>
                            <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>auth/login.php"
                               class="flex items-center justify-center gap-4 w-full bg-white text-[#1A1A2E] h-16 rounded-2xl text-[13px] font-black uppercase tracking-widest hover:bg-white/90 transition-all shadow-xl shadow-black/20">
                                Login to Unlock <span class="material-symbols-outlined text-[20px]">lock_open</span>
                            </a>
                            <?php endif; ?>

                            <div class="mt-12 space-y-5 pt-10 border-t border-white/5">
                                <div class="flex justify-between items-center text-[11px] font-black uppercase tracking-widest">
                                    <span class="text-white/30">Document Integrity</span>
                                    <span class="text-emerald-400 flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings:'FILL' 1;">verified</span> 100% Safe
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-[11px] font-black uppercase tracking-widest">
                                    <span class="text-white/30">Technical Format</span>
                                    <span class="bg-white/5 py-1 px-3 rounded-lg border border-white/5"><?php echo $ext; ?></span>
                                </div>
                                <div class="flex justify-between items-center text-[11px] font-black uppercase tracking-widest">
                                    <span class="text-white/30">Resource Level</span>
                                    <span class="text-indigo-300">Public Domain</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Policy Card -->
                    <div class="premium-card p-8 flex gap-6 bg-amber-50/30 border-amber-500/10">
                        <div class="w-12 h-12 bg-amber-500/10 rounded-2xl flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-amber-600 text-[24px]">gavel</span>
                        </div>
                        <div>
                            <h4 class="font-black text-[13px] text-[#1A1A2E] mb-2 uppercase tracking-tight tracking-wider leading-none">Ethical Standard</h4>
                            <p class="text-[12px] text-[#1A1A2E] font-medium opacity-60 leading-relaxed">By accessing this resource, you agree to respect academic integrity and the original contributor's work.</p>
                        </div>
                    </div>
                    <!-- Report Card -->
                    <div class="premium-card p-8 flex flex-col gap-6 bg-red-50/10 border-red-500/10">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-red-500/10 rounded-2xl flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-red-600 text-[20px]">report</span>
                            </div>
                            <div>
                                <h4 class="font-black text-[12px] text-[#1A1A2E] uppercase tracking-tight tracking-wider leading-none">Content Concern?</h4>
                                <p class="text-[10px] text-[#1A1A2E] font-medium opacity-60 mt-1">Found something incorrect?</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('reportModal').classList.remove('hidden')" class="w-full h-12 bg-white border border-red-500/20 text-red-600 text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-red-50 transition-all flex items-center justify-center gap-2">
                            Flag Concern
                        </button>
                    </div>
                </div>
            </div>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>

<!-- Report Modal -->
<div id="reportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-[2.5rem] w-full max-w-md p-10 relative shadow-2xl">
        <button onclick="document.getElementById('reportModal').classList.add('hidden')" class="absolute top-8 right-8 text-[#9CA3AF] hover:text-[#1A1A2E]">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="w-16 h-16 bg-red-50 rounded-3xl flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-red-600 text-[32px]">report</span>
        </div>
        <h3 class="text-2xl font-black text-[#1A1A2E] mb-2 uppercase tracking-tight">Flag Resource</h3>
        <p class="text-sm text-[#4A4A5A] font-medium opacity-70 mb-8 leading-relaxed">Help us maintain high academic standards. Why are you reporting this resource?</p>
        
        <form action="<?php echo BASE_URL; ?>actions/report-handler.php" method="POST" class="space-y-6">
            <input type="hidden" name="material_id" value="<?php echo $id; ?>">
            <div>
                <label class="block text-[10px] font-black text-[#9CA3AF] uppercase tracking-widest mb-2">Reason for Report</label>
                <textarea name="reason" rows="4" required
                          placeholder="e.g. Inaccurate information, copyright violation, low quality..."
                          class="w-full px-5 py-4 bg-[#F9F9FB] border border-[#E2E2E4] rounded-2xl text-sm text-[#1A1A2E] focus:ring-2 focus:ring-primary/10 transition-all resize-none"></textarea>
            </div>
            <button type="submit" class="w-full h-14 bg-red-600 text-white text-[12px] font-black uppercase tracking-widest rounded-xl hover:bg-red-700 transition-all shadow-xl shadow-red-600/20">
                Submit Formal Report
            </button>
        </form>
    </div>
</div>

<script>
    function setRating(val) {
        document.getElementById('rating-input').value = val;
        for(let i=1; i<=5; i++) {
            const star = document.querySelector(`#star-${i} span`);
            if(i <= val) {
                star.classList.add('text-amber-400');
                star.classList.remove('text-gray-200');
                star.style.fontVariationSettings = "'FILL' 1";
            } else {
                star.classList.add('text-gray-200');
                star.classList.remove('text-amber-400');
                star.style.fontVariationSettings = "'FILL' 0";
            }
        }
    }
    // Initialize rating
    setRating(5);

    // Viewer tab switching for multi-file support
    function switchViewer(idx) {
        document.querySelectorAll('.viewer-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.viewer-tabs button').forEach(b => {
            b.classList.remove('active');
            b.classList.add('bg-[#F4F4F6]', 'text-[#4A4A5A]');
        });
        const panel = document.getElementById('viewer-' + idx);
        const tab = document.getElementById('vtab-' + idx);
        if (panel) panel.classList.remove('hidden');
        if (tab) {
            tab.classList.add('active');
            tab.classList.remove('bg-[#F4F4F6]', 'text-[#4A4A5A]');
        }
    }
</script>

</body>
</html>

