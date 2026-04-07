<?php
/**
 * WMSU ARL Hub: Reset Password
 */
require_once '../config/auth.php';

$token = trim($_GET['token'] ?? '');
$error = '';
$success = false;
$validToken = false;
$userId = null;

if ($token) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if ($user) { $validToken = true; $userId = $user['id']; }
        else { $error = 'This reset link is invalid or has expired. Please request a new one.'; }
    } catch (PDOException $e) { $error = 'Database error. Please try again.'; }
} else {
    $error = 'No reset token provided.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?")
            ->execute([$hash, $userId]);
        logAudit($pdo, $userId, 'password_reset', 'User reset their password.');
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - WMSU ARL Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#B81C2E',
                        'primary-dark': '#8A1523',
                        background: '#FAFAFA',
                        surface: '#FFFFFF',
                        text: '#111827',
                        'text-secondary': '#6B7280',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen text-text antialiased py-12">
    <div class="fixed top-8 left-8 z-50">
        <a href="<?php echo BASE_URL; ?>index.php" class="flex items-center gap-2 text-text-secondary hover:text-primary transition-colors font-medium bg-white/80 backdrop-blur px-4 py-2 rounded-full shadow-sm">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            Home
        </a>
    </div>

    <div class="max-w-[500px] w-full mx-4 bg-surface rounded-2xl shadow-xl overflow-hidden p-8 sm:p-12 relative">
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shrink-0 shadow-lg shadow-black/5 overflow-hidden">
                    <img src="../images/logo.png" alt="WMSU Logo" class="w-full h-full object-cover">
                </div>
                <span class="font-heading font-bold text-2xl text-text">ARL Hub</span>
            </div>
            <h1 class="text-3xl font-heading font-bold text-text mb-3">Set New Password</h1>
            <p class="text-text-secondary">Choose a strong password for your account.</p>
        </div>

        <?php if ($success): ?>
            <div class="text-center py-6">
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-green-500 text-4xl">check_circle</span>
                </div>
                <h3 class="font-heading font-bold text-2xl text-text mb-4">Password Updated!</h3>
                <p class="text-text-secondary leading-relaxed mb-8">
                    Your password has been changed successfully. You can now log in with your new credentials.
                </p>
                <a href="login.php" class="inline-flex items-center justify-center gap-2 w-full bg-primary hover:bg-primary-dark text-white font-medium py-3.5 rounded-xl transition-colors shadow-md">
                    Go to Login
                </a>
            </div>
        <?php elseif (!$validToken): ?>
            <div class="text-center py-6">
                <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-red-500 text-4xl">error</span>
                </div>
                <h3 class="font-heading font-bold text-2xl text-text mb-4">Invalid Link</h3>
                <p class="text-text-secondary leading-relaxed mb-8">
                    <?php echo htmlspecialchars($error); ?>
                </p>
                <a href="forgot-password.php" class="inline-flex items-center justify-center gap-2 w-full bg-primary hover:bg-primary-dark text-white font-medium py-3.5 rounded-xl transition-colors shadow-md">
                    Request New Link
                </a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-500 mt-0.5">error</span>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="?token=<?php echo urlencode($token); ?>" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-text mb-2">New Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-gray-400 text-[20px]">lock</span>
                        </div>
                        <input type="password" name="password" required minlength="8" placeholder="Min. 8 characters"
                               class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow bg-gray-50 focus:bg-white text-[15px]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-text mb-2">Confirm Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-gray-400 text-[20px]">lock_reset</span>
                        </div>
                        <input type="password" name="confirm" required placeholder="Repeat your password"
                               class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow bg-gray-50 focus:bg-white text-[15px]">
                    </div>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-3.5 rounded-xl transition-colors shadow-md flex items-center justify-center gap-2 text-[16px] mt-2">
                    Reset Password
                    <span class="material-symbols-outlined text-[20px]">key</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
