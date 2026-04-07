<?php
/**
 * WMSU ARL Hub: Upload Success — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth();

$materialTitle = htmlspecialchars($_GET['title'] ?? 'Your Material');
$role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'student';
$dashboardLink = match($role) {
    'admin'   => BASE_URL.'admin/admin-dashboard.php',
    'faculty' => BASE_URL.'faculty/faculty-dashboard.php',
    default   => BASE_URL.'student/student-dashboard.php',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Successful - WMSU ARL Hub</title>
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
        @keyframes pop { 0%{transform:scale(0);opacity:0} 100%{transform:scale(1);opacity:1} }
        .pop-in { animation: pop 0.5s cubic-bezier(0.175,0.885,0.32,1.275) forwards; }
    </style>
</head>
<body class="text-[#1A1A2E]">
<?php require_once '../includes/dashboard-nav.php'; ?>
<div class="flex min-h-[calc(100vh-64px)]">
    <?php require_once '../includes/sidebar.php'; ?>
    <main class="ml-[240px] flex-1 bg-[#F4F4F6] flex items-center justify-center p-8">
        <div class="bg-white rounded-2xl border border-black/[0.06] p-12 max-w-md w-full text-center shadow-sm">

            <!-- Success Icon -->
            <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-7 pop-in">
                <span class="material-symbols-outlined text-emerald-600 text-[42px]" style="font-variation-settings:'FILL' 1;">check_circle</span>
            </div>

            <h1 class="text-2xl font-bold text-[#1A1A2E] mb-3">Upload Successful!</h1>
            <p class="text-sm text-[#4A4A5A] leading-relaxed mb-5">
                Your material has been submitted and is now pending faculty review before it becomes visible in the repository.
            </p>

            <!-- Material Tag -->
            <div class="inline-flex items-center gap-2 bg-[#F9E8EA] text-[#B81C2E] text-sm font-semibold px-4 py-2 rounded-full mb-6">
                <span class="material-symbols-outlined text-[15px]">description</span>
                <?php echo $materialTitle; ?>
            </div>

            <!-- What's Next -->
            <div class="bg-[#F9F9FB] border border-[#E2E2E4] rounded-xl p-4 text-left mb-7">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-indigo-500 text-[18px] mt-0.5">info</span>
                    <div>
                        <div class="font-semibold text-sm text-[#1A1A2E] mb-1">What happens next?</div>
                        <p class="text-xs text-[#4A4A5A] leading-relaxed">
                            Our faculty team will review your submission within 1–3 business days. Once approved, it will be visible to the entire WMSU community. You'll be notified of the decision.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-2.5">
                <a href="<?php echo BASE_URL; ?>core/upload.php"
                   class="flex items-center justify-center gap-2 w-full bg-[#B81C2E] text-white py-3 rounded-xl font-semibold text-sm hover:bg-[#8C1222] transition-colors">
                    <span class="material-symbols-outlined text-[16px]">add</span> Upload Another Material
                </a>
                <a href="my-uploads.php"
                   class="flex items-center justify-center gap-2 w-full bg-white border border-[#E2E2E4] text-[#4A4A5A] py-3 rounded-xl font-semibold text-sm hover:border-[#1A1A2E] transition-colors">
                    <span class="material-symbols-outlined text-[16px]">folder_open</span> View My Contributions
                </a>
                <a href="<?php echo BASE_URL; ?>core/browse.php"
                   class="flex items-center justify-center gap-2 w-full bg-white border border-[#E2E2E4] text-[#4A4A5A] py-3 rounded-xl font-semibold text-sm hover:border-[#1A1A2E] transition-colors">
                    <span class="material-symbols-outlined text-[16px]">grid_view</span> Browse Repository
                </a>
            </div>

        </div>
    </main>
</div>
</body>
</html>

