<?php
/**
 * WMSU ARL Hub: Faculty Official Uploads — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('faculty');

$userId = $_SESSION['user_id'];
$filterStatus = $_GET['status'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM materials WHERE id = ? AND contributor_id = ? AND is_official = 1")->execute([$_POST['delete_id'], $userId]);
    header("Location: faculty-uploads.php"); exit();
}

try {
    $conditions = ["m.contributor_id = ?", "m.is_official = 1"];
    $params = [$userId];
    if ($filterStatus !== '') { $conditions[] = "m.status = ?"; $params[] = $filterStatus; }
    $stmt = $pdo->prepare("SELECT m.*,
        (SELECT ROUND(AVG(r.rating),1) FROM reviews r WHERE r.material_id = m.id) AS avg_rating,
        (SELECT COUNT(*) FROM reviews r WHERE r.material_id = m.id) AS review_count
        FROM materials m WHERE ".implode(' AND ', $conditions)." ORDER BY m.created_at DESC");
    $stmt->execute($params);
    $uploads = $stmt->fetchAll();

    $offStmt = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE contributor_id = ? AND is_official = 1"); $offStmt->execute([$userId]); $totalOfficial = $offStmt->fetchColumn();
    $dlStmt  = $pdo->prepare("SELECT SUM(downloads_count) FROM materials WHERE contributor_id = ? AND is_official = 1"); $dlStmt->execute([$userId]); $totalDl = $dlStmt->fetchColumn() ?? 0;
    $apStmt  = $pdo->prepare("SELECT COUNT(*) FROM materials WHERE contributor_id = ? AND is_official = 1 AND status='approved'"); $apStmt->execute([$userId]); $approved = $apStmt->fetchColumn();
} catch (PDOException $e) { $uploads=[]; $totalOfficial=0; $totalDl=0; $approved=0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Official Uploads - WMSU ARL Hub</title>
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
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">My Official Uploads</h1>
                    <p class="text-[#4A4A5A] mt-1">Faculty-verified academic resources you have contributed.</p>
                </div>
                <a href="<?php echo BASE_URL; ?>core/upload.php?official=1"
                   class="flex items-center gap-2 bg-[#B81C2E] text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#8C1222] transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[17px]">add</span> Upload Official Material
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-5 mb-8">
                <?php foreach ([
                    ['verified',     'Official Materials', number_format($totalOfficial), 'bg-[#F9E8EA] text-[#B81C2E]'],
                    ['download',     'Total Downloads',    number_format($totalDl),       'bg-indigo-50 text-indigo-500'],
                    ['check_circle', 'Live & Approved',    number_format($approved),      'bg-emerald-50 text-emerald-600'],
                ] as $s): ?>
                <div class="bg-white rounded-xl border border-black/[0.06] p-5 flex items-center gap-4 hover:-translate-y-0.5 transition-transform">
                    <div class="w-11 h-11 <?php echo $s[3]; ?> rounded-xl flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]"><?php echo $s[0]; ?></span>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-[#1A1A2E]"><?php echo $s[2]; ?></div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-[#4A4A5A]"><?php echo $s[1]; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Status Filter -->
            <div class="flex gap-2 mb-5">
                <?php foreach (['' => 'All', 'approved' => 'Approved', 'pending' => 'Pending', 'rejected' => 'Rejected'] as $val => $label):
                    $active = $filterStatus === $val; ?>
                <a href="?status=<?php echo $val; ?>"
                   class="px-4 py-2 rounded-full text-xs font-semibold transition-colors <?php echo $active ? 'bg-[#1A1A2E] text-white' : 'bg-white text-[#4A4A5A] border border-[#E2E2E4] hover:border-[#1A1A2E]'; ?>">
                    <?php echo $label; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-black/[0.06] overflow-hidden">
                <?php if (empty($uploads)): ?>
                <div class="py-16 text-center">
                    <span class="material-symbols-outlined text-[52px] text-gray-200 block mb-4">verified</span>
                    <h3 class="text-base font-bold text-[#1A1A2E] mb-2">No official uploads yet</h3>
                    <p class="text-[#4A4A5A] text-sm mb-5">Upload faculty-verified materials to make them appear here.</p>
                    <a href="<?php echo BASE_URL; ?>core/upload.php?official=1" class="inline-flex items-center gap-2 bg-[#B81C2E] text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-[#8C1222] transition-colors">
                        <span class="material-symbols-outlined text-[16px]">add</span> Upload Material
                    </a>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[#F9F9FB] border-b border-[#F4F4F6]">
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Material</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Status</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Downloads</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Rating</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F4F4F6]">
                            <?php foreach ($uploads as $u):
                                $pillColors = ['approved'=>'bg-emerald-50 text-emerald-700','pending'=>'bg-amber-50 text-amber-700','rejected'=>'bg-red-50 text-red-700'];
                                $pill = $pillColors[$u['status']] ?? 'bg-gray-100 text-gray-600';
                            ?>
                            <tr class="hover:bg-[#F9F9FB] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0">
                                            <span class="material-symbols-outlined text-emerald-600 text-[18px]">verified</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-[#1A1A2E] flex items-center gap-1.5">
                                                <?php echo htmlspecialchars($u['title']); ?>
                                                <span class="bg-[#F9E8EA] text-[#B81C2E] text-[9px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">Official</span>
                                            </div>
                                            <div class="text-xs text-[#4A4A5A] mt-0.5"><?php echo htmlspecialchars($u['category']); ?> · <?php echo date('M d, Y', strtotime($u['created_at'])); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="<?php echo $pill; ?> text-[10px] font-bold px-2.5 py-1 rounded-full uppercase"><?php echo $u['status']; ?></span>
                                </td>
                                <td class="px-6 py-4 font-bold text-[#1A1A2E]"><?php echo number_format($u['downloads_count']); ?></td>
                                <td class="px-6 py-4">
                                    <?php if ($u['avg_rating']): ?>
                                    <span class="flex items-center gap-1 text-amber-500 font-semibold text-xs">
                                        <span class="material-symbols-outlined text-[14px]" style="font-variation-settings:'FILL' 1;">star</span>
                                        <?php echo $u['avg_rating']; ?> <span class="text-[#9CA3AF]">(<?php echo $u['review_count']; ?>)</span>
                                    </span>
                                    <?php else: ?><span class="text-[#D1D1D9] text-xs">—</span><?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="<?php echo BASE_URL; ?>core/material-details.php?id=<?php echo $u['id']; ?>"
                                           class="flex items-center gap-1 bg-[#F4F4F6] text-[#4A4A5A] text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-[#E9E9EF] transition-colors">
                                            <span class="material-symbols-outlined text-[13px]">visibility</span> View
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this official material?')">
                                            <input type="hidden" name="delete_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="flex items-center gap-1 bg-red-50 text-red-700 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-red-100 border border-red-100 transition-colors">
                                                <span class="material-symbols-outlined text-[13px]">delete</span> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

