<?php
/**
 * WMSU ARL Hub: Administrative Help Center
 * Interactive version with search and detailed guides
 */
require_once '../config/auth.php';
checkAuth('admin');

// Help topics data
$topics = [
    [
        'id' => 'moderation',
        'title' => 'Content Moderation',
        'icon' => 'shield',
        'description' => 'Verify and approve research materials submitted by scholars.',
        'items' => [
            ['q' => 'How to approve or reject submissions?', 'a' => 'Navigate to the Moderation queue from the sidebar. You can review the full details of a material before clicking the Approve or Reject buttons at the end of the row.'],
            ['q' => 'What happen when a material is rejected?', 'a' => 'Rejection notifies the contributor via their local notification center. The material will remain in their "My Uploads" folder but with a "Rejected" status.'],
            ['q' => 'Managing flagged content.', 'a' => 'Materials reported by users appear in the System Logs. Currently, you must manually check these and use the moderation tool to take action.']
        ]
    ],
    [
        'id' => 'users',
        'title' => 'User Management',
        'icon' => 'group',
        'description' => 'Manage the status and roles of WMSU institutional accounts.',
        'items' => [
            ['q' => 'How to ban or unban accounts?', 'a' => 'In User Management, locate the user and click "Ban". This prevents them from logging in but preserves their upload history. Re-click to unban.'],
            ['q' => 'Resetting user passwords.', 'a' => 'Admins cannot see passwords. Users must use the "Forgot Password" link on the login page to receive a reset code at their @wmsu.edu.ph email.'],
            ['q' => 'Changing user roles.', 'a' => 'Currently roles are assigned during registration. Role promotion (e.g. Student to Faculty) requires a database manual update or contacting technical support.']
        ]
    ],
    [
        'id' => 'analytics',
        'title' => 'System Analytics',
        'icon' => 'analytics',
        'description' => 'Insights into resource usage and user engagement.',
        'items' => [
            ['q' => 'Understanding the dashboard stats.', 'a' => 'The dashboard shows real-time counts for active users, pending materials, and total downloads across the entire hub.'],
            ['q' => 'Generating monthly reports.', 'a' => 'The "Generate Report" button on the dashboard compiles top downloads and active users into a summary view for administrative review.'],
            ['q' => 'Tracking top downloads.', 'a' => 'System Analytics tracks "downloads_count" for each material. The leaderboard shows the most impactful researchers in the community.']
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Center - WMSU ARL Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#B81C2E' } } }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F4F4F6; }
        h1, h2, h3 { font-family: 'Plus Jakarta Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
        .guide-expandable { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .guide-expandable.active { max-height: 200px; }
    </style>
</head>
<body class="text-[#1A1A2E]">

<?php require_once '../includes/dashboard-nav.php'; ?>
<div class="flex">
    <?php require_once '../includes/sidebar.php'; ?>
    
    <main class="flex-1 ml-[240px] pt-16 min-h-screen">
        <div class="p-8 max-w-[1200px] mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
                <div>
                    <h1 class="text-[32px] font-bold text-[#1A1A2E]">Help Center</h1>
                    <p class="text-[#4A4A5A] mt-2">Find guides and support for managing the hub.</p>
                </div>
                <div class="relative w-full max-w-sm">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input type="text" id="helpSearch" placeholder="Search for guides..." 
                           class="w-full pl-12 pr-4 py-3 bg-white border border-black/[0.06] rounded-2xl focus:ring-2 focus:ring-primary focus:outline-none transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="topicsGrid">
                <?php foreach($topics as $t): ?>
                <div class="topic-card bg-white p-8 rounded-3xl border border-black/[0.06] hover:shadow-xl transition-all h-fit" data-title="<?php echo strtolower($t['title']); ?>">
                    <div class="w-14 h-14 bg-[#F9E8EA] text-primary rounded-2xl flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-[28px]"><?php echo $t['icon']; ?></span>
                    </div>
                    <h3 class="text-xl font-bold text-[#1A1A2E] mb-2"><?php echo $t['title']; ?></h3>
                    <p class="text-xs text-[#9CA3AF] mb-6"><?php echo $t['description']; ?></p>
                    
                    <div class="space-y-3">
                        <?php foreach($t['items'] as $idx => $item): ?>
                        <div class="border-b border-[#F4F4F6] last:border-0 pb-3 last:pb-0">
                            <button onclick="toggleAnswer('<?php echo $t['id'].$idx; ?>')" 
                                    class="w-full flex items-center justify-between text-left group">
                                <span class="text-[13px] font-bold text-[#4A4A5A] group-hover:text-primary transition-colors pr-4"><?php echo $item['q']; ?></span>
                                <span id="icon-<?php echo $t['id'].$idx; ?>" class="material-symbols-outlined text-gray-300 text-[18px] transition-transform">add</span>
                            </button>
                            <div id="ans-<?php echo $t['id'].$idx; ?>" class="guide-expandable text-xs text-[#848494] mt-2 leading-relaxed">
                                <?php echo $item['a']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-12 bg-[#1A1A2E] p-12 rounded-[2.5rem] text-white flex flex-col md:flex-row items-center justify-between overflow-hidden relative">
                <div class="relative z-10">
                    <h3 class="text-2xl font-bold mb-2">Still need help?</h3>
                    <p class="text-white/60 max-w-[400px] text-sm">Contact the WMSU IT Services helpdesk at itsupport@wmsu.edu.ph for critical system issues.</p>
                </div>
                <div class="mt-8 md:mt-0 relative z-10">
                    <a href="mailto:itsupport@wmsu.edu.ph" class="bg-primary text-white px-8 py-3 rounded-xl font-bold hover:bg-primary/90 transition-colors inline-block shadow-lg shadow-primary/20">Send Email Support</a>
                </div>
                <span class="material-symbols-outlined text-[180px] text-white/5 absolute -right-4 -bottom-10">support_agent</span>
            </div>
        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>

<script>
    function toggleAnswer(id) {
        const ans = document.getElementById('ans-' + id);
        const icon = document.getElementById('icon-' + id);
        
        ans.classList.toggle('active');
        if(ans.classList.contains('active')) {
            icon.textContent = 'remove';
            icon.style.transform = 'rotate(180deg)';
        } else {
            icon.textContent = 'add';
            icon.style.transform = 'rotate(0)';
        }
    }

    document.getElementById('helpSearch').addEventListener('input', function(e) {
        const q = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.topic-card');
        
        cards.forEach(card => {
            const title = card.getAttribute('data-title');
            if(title.includes(q)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>
