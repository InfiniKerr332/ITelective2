<?php
/**
 * WMSU ARL Hub: Forgot Password
 */
require_once '../config/auth.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        // Always show success to prevent email enumeration
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?")
                ->execute([$token, $expiry, $user['id']]);
                
            require_once '../includes/mailer.php';
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $fullBaseUrl = $protocol . $host . BASE_URL;
            $resetLink = $fullBaseUrl . "auth/reset-password.php?token=" . $token;
            $subject = 'WMSU ARL Hub - Password Reset';
            $bodyHTML = "Hello,<br><br>We received a request to reset your password. Click the link below to set a new password:<br><br><a href='$resetLink'>$resetLink</a><br><br>This link will expire in 1 hour.<br><br>If you did not request this, please ignore this email.<br><br>Best regards,<br><b>WMSU ARL Hub</b>";
            $bodyText = "Hello,\n\nWe received a request to reset your password. Open the link below to set a new password:\n\n$resetLink\n\nThis link will expire in 1 hour.\n\nIf you did not request this, please ignore this email.\n\nBest regards,\nWMSU ARL Hub";
            
            sendEmail($email, 'User', $subject, $bodyHTML, $bodyText);
        }
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WMSU ARL Hub</title>
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
        <!-- Logo & Title Section -->
        <div class="flex flex-col items-center text-center mb-10">
            <div class="mb-10 hover:scale-105 transition-transform duration-500">
                <img src="../images/logo.png" alt="WMSU ARL Logo" class="h-20 w-auto object-contain">
            </div>
            
            <h1 class="text-3xl font-heading font-black text-[#1A1A2E] tracking-tight mb-3">Forgot Password</h1>
            <p class="text-[14px] font-medium text-[#848494] leading-relaxed max-w-[320px] mx-auto">
                No worries! Enter your institutional email and we'll send you a reset link.
            </p>
        </div>

        <?php if ($success): ?>
            <div class="text-center py-6">
                <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="material-symbols-outlined text-green-500 text-4xl">mark_email_read</span>
                </div>
                <h3 class="font-heading font-bold text-2xl text-text mb-4">Check your email</h3>
                <p class="text-text-secondary leading-relaxed mb-8">
                    If an account with that email exists, we've sent a password reset link. It expires in 1 hour.
                </p>
                <a href="login.php" class="inline-flex items-center justify-center gap-2 w-full bg-primary hover:bg-primary-dark text-white font-medium py-3.5 rounded-xl transition-colors shadow-md">
                    Back to Login
                </a>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-red-500 mt-0.5">error</span>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-text mb-2">Institutional Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-gray-400 text-[20px]">mail</span>
                        </div>
                        <input type="email" name="email" placeholder="you@wmsu.edu.ph" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="w-full pl-11 pr-4 py-3.5 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow bg-gray-50 focus:bg-white text-[15px]">
                    </div>
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-medium py-3.5 rounded-xl transition-colors shadow-md flex items-center justify-center gap-2 text-[16px]">
                    Send Reset Link
                    <span class="material-symbols-outlined text-[20px]">send</span>
                </button>
            </form>
        <?php endif; ?>

        <div class="mt-8 text-center">
            <a href="login.php" class="inline-flex items-center gap-2 text-primary hover:text-primary-dark font-semibold transition-colors">
                <span class="material-symbols-outlined text-[20px]">keyboard_backspace</span>
                Back to Login
            </a>
        </div>
    </div>
</body>
</html>
