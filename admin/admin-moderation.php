<?php
/**
 * WMSU ARL Hub: Admin Content Moderation — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth('admin');

try {
    $pending  = $pdo->query("SELECT m.*, u.full_name, u.role as contributor_role FROM materials m LEFT JOIN users u ON m.contributor_id = u.id WHERE m.status = 'pending' ORDER BY m.created_at ASC")->fetchAll();
    $approved = $pdo->query("SELECT COUNT(*) FROM materials WHERE status='approved'")->fetchColumn();
    $rejected = $pdo->query("SELECT COUNT(*) FROM materials WHERE status='rejected'")->fetchColumn();
} catch (PDOException $e) { $pending=[]; $approved=0; $rejected=0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Moderation - WMSU ARL Hub</title>
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

            <!-- Feedback Messages -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="mb-6 p-4 rounded-xl <?php echo $_GET['msg'] === 'approved' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-amber-50 text-amber-700 border border-amber-100'; ?> flex items-center gap-3 animate-in fade-in slide-in-from-top-4">
                    <span class="material-symbols-outlined"><?php echo $_GET['msg'] === 'approved' ? 'check_circle' : 'info'; ?></span>
                    <span class="text-sm font-bold">Successfully <?php echo htmlspecialchars($_GET['msg']); ?>.</span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 border border-red-100 flex items-center gap-3">
                    <span class="material-symbols-outlined">error</span>
                    <span class="text-sm font-bold">Error: <?php echo htmlspecialchars($_GET['error'] === 'notfound' ? 'Material not found.' : $_GET['error']); ?></span>

                </div>
            <?php endif; ?>
            
            <!-- Page Header -->
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-[28px] font-bold text-[#1A1A2E]">Content Moderation</h1>
                    <p class="text-[#4A4A5A] mt-1">Review and verify materials submitted by the WMSU community.</p>
                </div>
                <div class="flex gap-4">
                    <div class="bg-amber-50 border border-amber-100 rounded-xl px-5 py-3 text-center">
                        <div class="text-xl font-bold text-amber-600"><?php echo count($pending); ?></div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-amber-500">Pending</div>
                    </div>
                    <div class="bg-green-50 border border-green-100 rounded-xl px-5 py-3 text-center">
                        <div class="text-xl font-bold text-green-600"><?php echo $approved; ?></div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-green-500">Approved</div>
                    </div>
                    <div class="bg-red-50 border border-red-100 rounded-xl px-5 py-3 text-center">
                        <div class="text-xl font-bold text-red-600"><?php echo $rejected; ?></div>
                        <div class="text-[10px] font-semibold uppercase tracking-wide text-red-500">Rejected</div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl border border-black/[0.06] overflow-hidden">
                <?php if (empty($pending)): ?>
                <div class="py-20 text-center">
                    <span class="material-symbols-outlined text-[56px] text-green-200 block mb-4">shield</span>
                    <h3 class="text-lg font-bold text-[#1A1A2E] mb-2">Queue is Clear!</h3>
                    <p class="text-[#4A4A5A] text-sm">All submitted materials have been processed.</p>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#F4F4F6] bg-[#F9F9FB]">
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Resource Title</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Contributor</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Category</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Submitted</th>
                                <th class="text-left px-6 py-4 text-[10px] font-bold uppercase tracking-wider text-[#4A4A5A]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F4F4F6]">
                            <?php foreach ($pending as $item): ?>
                            <tr class="hover:bg-[#F9F9FB] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-[#1A1A2E] leading-snug"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div class="text-xs text-[#9CA3AF] mt-0.5"><?php echo strtoupper(pathinfo($item['file_path'], PATHINFO_EXTENSION)); ?> file</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 bg-[#F9E8EA] rounded-lg flex items-center justify-center text-[#B81C2E] font-bold text-xs flex-shrink-0">
                                            <?php echo strtoupper(substr($item['full_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-[#1A1A2E] text-xs"><?php echo htmlspecialchars($item['full_name']); ?></div>
                                            <div class="text-[10px] text-[#9CA3AF] capitalize"><?php echo $item['contributor_role']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="bg-[#EEF2FF] text-[#4338CA] text-[10px] font-bold px-2.5 py-1 rounded-full uppercase">
                                        <?php echo htmlspecialchars($item['category']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-[#4A4A5A] font-medium">
                                    <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="<?php echo BASE_URL; ?>core/material-details.php?id=<?php echo $item['id']; ?>"
                                           class="flex items-center gap-1 bg-[#F4F4F6] text-[#4A4A5A] text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-[#E9E9EF] transition-colors">
                                            <span class="material-symbols-outlined text-[13px]">visibility</span> View
                                        </a>
                                        <form action="<?php echo BASE_URL; ?>actions/moderate-handler.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to approve this material?');">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="flex items-center gap-1 bg-green-50 text-green-700 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-green-100 transition-colors border border-green-100">
                                                <span class="material-symbols-outlined text-[13px]">check_circle</span> Approve
                                            </button>
                                        </form>
                                        <form action="<?php echo BASE_URL; ?>actions/moderate-handler.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to reject this material?');">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="flex items-center gap-1 bg-red-50 text-red-700 text-xs font-semibold px-3 py-1.5 rounded-lg hover:bg-red-100 transition-colors border border-red-100">
                                                <span class="material-symbols-outlined text-[13px]">cancel</span> Reject
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

