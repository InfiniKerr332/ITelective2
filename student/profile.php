<?php
/**
 * WMSU ARL Hub: User Profile Management — Stitch Design System
 */
require_once '../config/auth.php';
checkAuth();

$userId = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = trim($_POST['full_name']);
    if (!empty($fullName)) {
        try {
            $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?")->execute([$fullName, $userId]);
            $_SESSION['user_name'] = $fullName;
            logAudit($pdo, $userId, "UPDATE", "Updated profile name");
            $success_msg = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Could not update profile. Please try again.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$roleColors = ['admin'=>'bg-[#B81C2E]','faculty'=>'bg-emerald-600','student'=>'bg-indigo-500'];
$avatarColor = $roleColors[$user['role']] ?? 'bg-gray-400';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - WMSU ARL Hub</title>
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

            <div class="mb-8">
                <h1 class="text-[28px] font-bold text-[#1A1A2E]">Profile Settings</h1>
                <p class="text-[#4A4A5A] mt-1">Manage your personal information and account security.</p>
            </div>

            <div class="max-w-2xl space-y-5">

                <!-- Profile Header Card -->
                <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 <?php echo $avatarColor; ?> rounded-2xl flex items-center justify-center text-white font-bold text-2xl flex-shrink-0 shadow-sm">
                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <div class="font-bold text-lg text-[#1A1A2E]"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div class="text-sm text-[#4A4A5A] mt-0.5"><?php echo htmlspecialchars($user['email']); ?></div>
                            <span class="mt-2 inline-flex items-center gap-1.5 bg-[#F4F4F6] text-[#4A4A5A] text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full">
                                <span class="material-symbols-outlined text-[11px]"><?php echo $user['role'] === 'admin' ? 'shield' : ($user['role'] === 'faculty' ? 'school' : 'person'); ?></span>
                                <?php echo ucfirst($user['role']); ?> · Institutional Account
                            </span>
                        </div>
                    </div>
                </div>

                <?php if ($success_msg): ?>
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm text-emerald-700 font-medium">
                    <span class="material-symbols-outlined text-emerald-600 text-[18px]">check_circle</span>
                    <?php echo $success_msg; ?>
                </div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-4 py-3 text-sm text-red-700 font-medium">
                    <span class="material-symbols-outlined text-red-600 text-[18px]">error</span>
                    <?php echo $error_msg; ?>
                </div>
                <?php endif; ?>

                <!-- Edit Form -->
                <div class="bg-white rounded-xl border border-black/[0.06] p-6">
                    <h3 class="text-base font-bold text-[#1A1A2E] mb-5">Personal Information</h3>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label for="full_name" class="block text-xs font-bold text-[#1A1A2E] uppercase tracking-wide mb-2">Full Legal Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required
                                   class="w-full h-11 px-4 bg-[#F9F9FB] border border-[#E2E2E4] rounded-xl text-sm text-[#1A1A2E] focus:outline-none focus:ring-2 focus:ring-[#B81C2E] focus:border-transparent transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#1A1A2E] uppercase tracking-wide mb-2">Institutional Email <span class="text-[#9CA3AF] font-normal normal-case">(cannot be changed)</span></label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                                   class="w-full h-11 px-4 bg-[#F0F0F2] border border-[#E2E2E4] rounded-xl text-sm text-[#9CA3AF] cursor-not-allowed">
                        </div>
                        <button type="submit" name="update_profile" value="1"
                                class="w-full h-11 bg-[#B81C2E] text-white font-semibold text-sm rounded-xl hover:bg-[#8C1222] transition-colors shadow-sm">
                            Save Profile Changes
                        </button>
                    </form>
                </div>

                <!-- Security -->
                <div class="bg-white rounded-xl border border-black/[0.06] p-6 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-sm text-[#1A1A2E] mb-0.5">Account Security</div>
                        <div class="text-xs text-[#4A4A5A]">Update your password to keep your account secure.</div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>auth/forgot-password.php"
                       class="flex items-center gap-2 border border-[#B81C2E] text-[#B81C2E] text-sm font-semibold px-4 py-2 rounded-lg hover:bg-[#F9E8EA] transition-colors">
                        <span class="material-symbols-outlined text-[16px]">lock</span> Change Password
                    </a>
                </div>

            </div>
        </div>
        <?php require_once '../includes/dashboard-footer.php'; ?>
    </main>
</div>
</body>
</html>

