<?php
/**
 * WMSU ARL Hub: Masterpiece Registration Page (Institutional v3)
 * Pure CSS (No Frameworks)
 */
require_once '../config/auth.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error_msg  = "";
$success_msg = "";
$f_first = $f_middle = $f_last = $f_email = $f_role = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $firstName      = trim($_POST['first_name']   ?? '');
    $middleName     = trim($_POST['middle_name']  ?? '');
    $lastName       = trim($_POST['last_name']    ?? '');
    $email          = trim($_POST['email']        ?? '');
    $password       = $_POST['password']          ?? '';
    $confirmPassword= $_POST['confirm_password']  ?? '';
    $role           = $_POST['role']              ?? 'student';

    $f_first  = $firstName;
    $f_middle = $middleName;
    $f_last   = $lastName;
    $f_email  = $email;
    $f_role   = $role;

    $fullName = $firstName;
    if ($middleName !== '') {
        $fullName .= ' ' . strtoupper(substr($middleName, 0, 1)) . '.';
    }
    $fullName .= ' ' . $lastName;

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($role)) {
        $error_msg = "All required fields marked with * must be filled out.";
    } elseif (!str_ends_with(strtolower($email), '@wmsu.edu.ph')) {
        $error_msg = "Verification failed: Please use your official @wmsu.edu.ph account.";
    } elseif ($password !== $confirmPassword) {
        $error_msg = "Passwords do not match. Please try again.";
    } elseif (strlen($password) < 8) {
        $error_msg = "Password must be at least 8 characters.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $error_msg = "An account with this email already exists.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $verificationCode = sprintf("%06d", mt_rand(1, 999999));
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_verified, verification_code) VALUES (?, ?, ?, ?, 0, ?)");
            if ($stmt->execute([$fullName, $email, $hashedPassword, $role, $verificationCode])) {
                require_once '../includes/mailer.php';
                $subject = "Your Verification Code";
                $bodyHTML = "Your verification code is: <b style='font-size: 24px; color: #B81C2E; letter-spacing: 2px;'>$verificationCode</b>";
                $bodyText = "Your verification code is: $verificationCode";
                if (sendEmail($email, $firstName, $subject, $bodyHTML, $bodyText)) {
                    header("Location: verify.php?email=" . urlencode($email));
                    exit();
                } else {
                    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
                    $error_msg = "Could not send verification email. Please contact support.";
                }
            } else { $error_msg = "System error. Please try again later."; }
        }
    }
}

$page_title = "Sign up | WMSU ARL Hub";
include '../includes/header.php';
?>

<style>
    .auth-page {
        min-height: calc(100vh - var(--header-height));
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 80px 24px;
        background: var(--bg-base);
    }

    .register-card {
        width: 100%;
        max-width: 560px;
        background: white;
        border-radius: 12px;
        padding: 48px;
        box-shadow: 0 32px 64px -16px rgba(26, 26, 40, 0.15);
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
    }

    .register-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--wmsu-red);
    }

    .institutional-seal {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        padding: 4px;
        border: 2px solid var(--wmsu-red-light);
        margin-bottom: 24px;
        background: white;
        object-fit: contain;
    }

    .auth-header {
        text-align: center;
        margin-bottom: 32px;
    }

    .auth-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--wmsu-black);
        letter-spacing: -0.04em;
        margin-bottom: 4px;
    }

    .auth-subtitle {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .field-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }

    .required-star {
        color: var(--wmsu-red);
        margin-left: 2px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .field-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--wmsu-black);
        margin-bottom: 8px;
        display: block;
    }

    .auth-input, .auth-select {
        width: 100%;
        height: 44px;
        padding: 0 16px;
        background: white;
        border: 1.5px solid #D1D1D9;
        border-radius: var(--radius-8);
        font-size: 14px;
        font-weight: 500;
        color: var(--wmsu-black);
        outline: none;
        transition: all 0.3s ease;
    }

    .auth-input:focus, .auth-select:focus {
        border-color: var(--wmsu-red);
        box-shadow: 0 0 0 4px var(--wmsu-red-light);
    }

    .auth-select {
        cursor: pointer;
        appearance: none;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' height='24' viewBox='0 -960 960 960' width='24' fill='%231A1A28'%3E%3Cpath d='M480-345 240-585l56-56 184 184 184-184 56 56-240 240Z'/%3E%3C/svg%3E") no-repeat right 12px center;
        background-size: 18px;
    }

    .error-toast {
        background: #FFF1F2;
        border: 1px solid #FFE4E6;
        color: #9F1239;
        padding: 12px 16px;
        border-radius: var(--radius-8);
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 24px;
        display: flex;
        gap: 12px;
        animation: slideDown 0.4s ease;
    }

    .validation-hint {
        font-size: 11px;
        color: #6B7280;
        margin-top: 6px;
        display: block;
    }

</style>

<div class="auth-page">
    <div class="register-card">
        <header class="auth-header">
            <img src="<?php echo BASE_URL; ?>images/wmsulogo.jpg" alt="Official WMSU Seal" class="institutional-seal">
            <h1 class="auth-title">Create Account</h1>
            <p class="auth-subtitle">Join the Academic Resource Library Hub.</p>
        </header>

        <?php if ($error_msg): ?>
            <div class="error-toast">
                <span class="material-symbols-outlined" style="font-size: 18px;">warning</span>
                <span><?php echo $error_msg; ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="registerForm">
            <div class="field-grid">
                <div class="form-group">
                    <label class="field-label">First Name <span class="required-star">*</span></label>
                    <input type="text" name="first_name" class="auth-input" value="<?php echo htmlspecialchars($f_first); ?>" placeholder="Juan">
                </div>
                <div class="form-group">
                    <label class="field-label">Middle Name <span style="font-size: 11px; font-weight: 500; color: var(--text-muted);">(Optional)</span></label>
                    <input type="text" name="middle_name" class="auth-input" value="<?php echo htmlspecialchars($f_middle); ?>" placeholder="Middle Initial or Name">
                </div>
                <div class="form-group">
                    <label class="field-label">Last Name <span class="required-star">*</span></label>
                    <input type="text" name="last_name" class="auth-input" value="<?php echo htmlspecialchars($f_last); ?>" placeholder="Dela Cruz">
                </div>
            </div>

            <div class="form-group">
                <label class="field-label">Institutional Email <span class="required-star">*</span></label>
                <input type="email" name="email" class="auth-input" value="<?php echo htmlspecialchars($f_email); ?>" placeholder="username@wmsu.edu.ph">
                <span class="validation-hint">Please use your official @wmsu.edu.ph account.</span>
            </div>

            <div class="form-group">
                <label class="field-label">Account Role <span class="required-star">*</span></label>
                <select name="role" class="auth-select">
                    <option value="student" <?php echo $f_role == 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="faculty" <?php echo $f_role == 'faculty' ? 'selected' : ''; ?>>Faculty</option>
                </select>
            </div>

            <div class="form-group">
                <label class="field-label" for="pw">Password <span class="required-star">*</span></label>
                <input type="password" id="pw" name="password" class="auth-input" placeholder="Minimum 8 characters">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="field-label" for="cpw">Confirm Password <span class="required-star">*</span></label>
                <input type="password" id="cpw" name="confirm_password" class="auth-input" placeholder="Minimum 8 characters">
            </div>

            <button type="submit" name="register" class="btn btn-primary" style="width: 100%; height: 48px; font-size: 15px;">
                Create Account
            </button>
        </form>

        <div class="auth-footer" style="margin-top: 32px; text-align: center;">
            <p class="register-prompt" style="font-size: 14px; margin-bottom: 24px; color: var(--text-secondary); font-weight: 500;">
                Already have an account? <a href="login.php" style="color: var(--wmsu-red); font-weight: 700; text-decoration: none;">Login here</a>
            </p>
            <div style="width: 100%; height: 1px; background: var(--bg-base); margin-bottom: 24px;"></div>
            <p class="footer-note" style="max-width: 320px; margin: 0 auto; font-size: 12px; color: var(--text-muted); line-height: 1.6;">
                By signing up, you agree to the WMSU ARL Hub <br>
                <a href="<?php echo BASE_URL; ?>core/terms.php" style="color: var(--text-primary); font-weight: 600; text-decoration: none;">Terms of Use</a> and <a href="<?php echo BASE_URL; ?>core/terms.php" style="color: var(--text-primary); font-weight: 600; text-decoration: none;">Privacy Policy</a>.
            </p>
        </div>
    </div>
</div>



<?php include '../includes/footer.php'; ?>
