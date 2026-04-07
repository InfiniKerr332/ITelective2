<?php
require_once '../config/auth.php';
require_once '../includes/mailer.php';

$error_msg = "";
$success_msg = "";

$email = $_GET['email'] ?? '';

if (!$email) {
    header("Location: login.php");
    exit();
}

// Handle Verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify'])) {
    $code = trim($_POST['verification_code'] ?? '');

    if (empty($code)) {
        $error_msg = "Please enter the 6-digit verification code.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ? AND is_verified = 0");
        $stmt->execute([$email, $code]);
        $user = $stmt->fetch();

        if ($user) {
            // Valid code!
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
            $update->execute([$user['id']]);

            // Auto login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            logAudit($pdo, $user['id'], 'Login', 'User verified email and logged in.');

            header("Location: " . BASE_URL . $user['role'] . "/" . $user['role'] . "-dashboard.php");
            exit();
        } else {
            $error_msg = "Invalid authentication code. Please check your email and try again.";
        }
    }
}

// Handle Resend Code
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['resend'])) {
    $currentTime = time();
    $cooldown = 60; // 1 minute
    
    if (isset($_SESSION['last_resend'][$email]) && ($currentTime - $_SESSION['last_resend'][$email] < $cooldown)) {
        $remaining = $cooldown - ($currentTime - $_SESSION['last_resend'][$email]);
        $error_msg = "Please wait {$remaining} seconds before requesting a new code.";
    } else {
        $newCode = sprintf("%06d", mt_rand(100000, 999999));
        
        $stmt = $pdo->prepare("UPDATE users SET verification_code = ? WHERE email = ? AND is_verified = 0");
        if ($stmt->execute([$newCode, $email])) {
            // Fetch name for email
            $stmtUser = $pdo->prepare("SELECT full_name FROM users WHERE email = ?");
            $stmtUser->execute([$email]);
            $u = $stmtUser->fetch();
            $firstName = $u ? explode(' ', $u['full_name'])[0] : 'User';
            
            $subject = 'Your Verification Code';
            $bodyHTML = "Your verification code is: <b style='font-size: 24px; color: #B81C2E; letter-spacing: 2px;'>$newCode</b>";
            $bodyText = "Your verification code is: $newCode";
            
            if (sendEmail($email, $firstName, $subject, $bodyHTML, $bodyText)) {
                $_SESSION['last_resend'][$email] = $currentTime;
                $success_msg = "A new code has been sent to your institutional email.";
            } else {
                $error_msg = "Failed to send email. Please try again later.";
            }
        } else {
            $error_msg = "System error. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - WMSU ARL Hub</title>
    <meta name="description" content="Verify your WMSU ARL Hub account.">
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
                        stitch: {
                            rich: '#1A1A2E',
                            surface: '#252542'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .otp-input {
            width: 100%;
            aspect-ratio: 1;
            max-height: 64px;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            background: #F9FAFB;
            transition: all 0.2s ease;
            color: #111827;
        }
        .otp-input:focus {
            border-color: #B81C2E;
            background: white;
            box-shadow: 0 4px 16px rgba(184,28,46,0.12);
            outline: none;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen text-text antialiased font-sans">
    <div class="fixed top-8 left-8">
        <a href="<?php echo BASE_URL; ?>index.php" class="flex items-center gap-2 text-text-secondary hover:text-primary transition-all font-medium group">
            <span class="material-symbols-outlined text-[20px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
            Back to Home
        </a>
    </div>

    <!-- Centered Verification Card -->
    <div class="max-w-[500px] w-full mx-4 bg-surface rounded-3xl shadow-2xl flex flex-col p-10 sm:p-14 relative overflow-hidden">
        <!-- Accent line -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-primary"></div>
        
        <div class="text-center mb-10">
            <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-red-100">
                <span class="material-symbols-outlined text-primary text-[32px]">mark_email_unread</span>
            </div>
            <h1 class="text-3xl font-heading font-extrabold text-text mb-3">Verify Your Email</h1>
            <p class="text-text-secondary leading-relaxed px-2">
                We've sent a 6-digit authentication code to<br>
                <span class="text-text font-bold bg-red-50 text-primary px-2 py-0.5 rounded-md mt-2 inline-block"><?php echo htmlspecialchars($email); ?></span>
            </p>
        </div>

        <?php if ($success_msg): ?>
            <div class="mb-8 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl flex items-start gap-3 animate-in fade-in slide-in-from-top-4 duration-500">
                <span class="material-symbols-outlined text-green-500">check_circle</span>
                <div class="text-[14px] font-medium"><?php echo $success_msg; ?></div>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl flex items-start gap-3 animate-in fade-in slide-in-from-top-4 duration-500">
                <span class="material-symbols-outlined text-red-500">error</span>
                <div class="text-[14px] font-medium"><?php echo $error_msg; ?></div>
            </div>
        <?php endif; ?>

        <form action="verify.php?email=<?php echo urlencode($email); ?>" method="POST" id="verifyForm">
            <div class="mb-10">
                <label class="block text-sm font-bold text-text mb-4 text-center uppercase tracking-wider opacity-60">Verification Code</label>
                <div class="flex gap-2 sm:gap-3 justify-between">
                    <input type="text" class="otp-input" maxlength="1" autofocus autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" autocomplete="off">
                </div>
                <input type="hidden" name="verification_code" id="verification_code">
            </div>

            <button type="submit" name="verify" class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-4 rounded-xl transition-all flex items-center justify-center gap-3 shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 active:translate-y-0" onclick="submitOTP()">
                <span class="material-symbols-outlined text-[22px]">verified_user</span>
                Verify Account
            </button>
        </form>

        <div class="mt-12 space-y-6 text-center">
            <div class="text-sm">
                <span class="text-text-secondary">Didn't receive the email?</span>
                <form action="verify.php?email=<?php echo urlencode($email); ?>" method="POST" id="resendForm" class="inline">
                    <button type="submit" name="resend" id="resendBtn" class="text-primary hover:text-primary-dark font-bold transition-colors disabled:opacity-50 disabled:cursor-not-allowed ml-1">
                        Resend Code
                    </button>
                    <span id="countdownText" class="hidden text-text-secondary ml-1 font-medium">(Wait <span id="timer">60</span>s)</span>
                </form>
            </div>
            
            <div style="width: 100%; height: 1px; background: linear-gradient(to right, transparent, #E5E7EB, transparent);"></div>
            
            <a href="login.php" class="inline-flex items-center gap-2 text-text-secondary hover:text-text font-semibold transition-all group">
                <span class="material-symbols-outlined text-[20px] group-hover:-translate-x-1 transition-transform">keyboard_backspace</span> 
                Back to Login
            </a>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.otp-input');
        const hiddenInput = document.getElementById('verification_code');
        const resendBtn = document.getElementById('resendBtn');
        const countdownText = document.getElementById('countdownText');
        const timerDisplay = document.getElementById('timer');

        // OTP Input Logic
        inputs.forEach((input, index) => {
            input.addEventListener('focus', () => input.select());

            input.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    inputs[index - 1].focus();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    submitOTP();
                    document.getElementById('verifyForm').submit();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const data = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                if (data) {
                    data.split('').forEach((char, i) => { if(inputs[i]) inputs[i].value = char; });
                    const nextIndex = Math.min(data.length, inputs.length - 1);
                    inputs[nextIndex].focus();
                }
            });
        });

        function submitOTP() {
            let code = '';
            inputs.forEach(input => code += input.value);
            hiddenInput.value = code;
        }

        // Resend Cooldown Logic
        let cooldown = <?php 
            $timeSince = time() - ($_SESSION['last_resend'][$email] ?? 0);
            echo max(0, 60 - $timeSince);
        ?>;

        if (cooldown > 0) {
            startTimer(cooldown);
        }

        function startTimer(duration) {
            resendBtn.disabled = true;
            countdownText.classList.remove('hidden');
            let timer = duration;
            
            const interval = setInterval(() => {
                timerDisplay.textContent = timer;
                if (--timer < 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    countdownText.classList.add('hidden');
                }
            }, 1000);
        }

        // Success message auto-hide
        setTimeout(() => {
            const msgs = document.querySelectorAll('.animate-in');
            msgs.forEach(m => {
                m.classList.add('fade-out');
                setTimeout(() => m.style.display = 'none', 500);
            });
        }, 8000);
    </script>
</body>
</html>
