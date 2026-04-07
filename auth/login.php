<?php
/**
 * WMSU ARL Hub: Masterpiece Login Page (Institutional v3)
 * Pure CSS (No Frameworks)
 * Standardized Fonts: Inter (Heading & Body)
 * Typography: h1 (64px), h2 (12px), body (16px)
 * Spacing: Base Unit (4), Border Radius (8px)
 */
require_once '../config/auth.php';

$error_msg = "";
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'invalid')
        $error_msg = "Invalid email or password.";
    elseif ($_GET['error'] === 'required')
        $error_msg = "Please log in first.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_msg = "Please enter both your email and password.";
    } elseif (loginUser($pdo, $email, $password)) {
        $role = $_SESSION['user_role'];
        header("Location: " . BASE_URL . $role . "/" . $role . "-dashboard.php");
        exit();
    } else {
        $error_msg = "Incorrect credentials. Please try again.";
    }
}

$page_title = "Login | WMSU ARL Hub";
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

    .auth-card {
        width: 100%;
        max-width: 480px;
        background: white;
        border-radius: 12px;
        /* Slight deviation for large component, but strictly 8px on internal elements */
        padding: 48px;
        box-shadow: 0 32px 64px -16px rgba(26, 26, 40, 0.15);
        border: 1px solid var(--border-light);
        position: relative;
        overflow: hidden;
    }

    .auth-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--wmsu-red);
    }

    .auth-header {
        text-align: center;
        margin-bottom: 40px;
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

    .auth-title {
        font-family: 'Inter', sans-serif;
        font-size: 28px;
        font-weight: 800;
        color: var(--wmsu-black);
        letter-spacing: -0.04em;
        margin-bottom: 8px;
    }

    .auth-subtitle {
        font-size: 14px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .label-box {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .required-star {
        color: var(--wmsu-red);
        margin-left: 2px;
    }

    .field-label {
        font-size: 13px;
        font-weight: 700;
        color: var(--wmsu-black);
    }

    .forgot-link {
        font-size: 13px;
        font-weight: 700;
        color: var(--wmsu-red);
        text-decoration: none;
    }

    .input-wrapper {
        position: relative;
    }

    .auth-input {
        width: 100%;
        height: 48px;
        padding: 0 16px;
        background: white;
        border: 1.5px solid #D1D1D9;
        border-radius: var(--radius-8);
        font-size: 14px;
        font-weight: 500;
        color: var(--wmsu-black);
        outline: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .auth-input:focus {
        border-color: var(--wmsu-red);
        box-shadow: 0 0 0 4px var(--wmsu-red-light);
    }

    .auth-input::placeholder {
        color: #9A9AAF;
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
        align-items: center;
        gap: 12px;
        animation: slideDown 0.4s ease;
    }

    @keyframes slideDown {
        from {
            transform: translateY(-10px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .auth-footer {
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid var(--bg-base);
        text-align: center;
    }

    .footer-note {
        font-size: 11px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .register-prompt {
        font-size: 14px;
        color: var(--text-secondary);
        font-weight: 500;
        margin-bottom: 12px;
    }
</style>

<div class="auth-page">
    <div class="auth-card">
        <header class="auth-header">
            <img src="<?php echo BASE_URL; ?>images/wmsulogo.jpg" alt="Official WMSU Seal" class="institutional-seal">
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to access your library resources.</p>
        </header>

        <?php if ($error_msg): ?>
            <div class="error-toast">
                <span class="material-symbols-outlined" style="font-size: 18px;">error</span>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <div class="label-box">
                    <label class="field-label" for="email">Institutional Email <span class="required-star">*</span></label>
                </div>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" class="auth-input"
                        placeholder="firstname.lastname@wmsu.edu.ph">
                </div>
            </div>

            <div class="form-group">
                <div class="label-box">
                    <label class="field-label" for="password">Password <span class="required-star">*</span></label>
                    <a href="forgot-password.php" class="forgot-link">Forgot?</a>
                </div>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" class="auth-input" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-primary"
                style="width: 100%; height: 48px; font-size: 15px;">
                Secure Sign In
            </button>
        </form>

        <div class="auth-footer">
            <p class="register-prompt" style="font-size: 14px; margin-bottom: 24px;">
                Don't have an account? <a href="register.php" style="color: var(--wmsu-red); font-weight: 700; text-decoration: none;">Sign up here</a>
            </p>
            <div style="width: 100%; height: 1px; background: var(--bg-base); margin-bottom: 24px;"></div>
            <p class="footer-note" style="max-width: 320px; margin: 0 auto; color: var(--text-muted);">
                By signing in, you agree to the WMSU ARL Hub <br>
                <a href="<?php echo BASE_URL; ?>core/terms.php" style="color: var(--text-primary); font-weight: 600; text-decoration: none;">Terms of Use</a> and <a href="<?php echo BASE_URL; ?>core/terms.php" style="color: var(--text-primary); font-weight: 600; text-decoration: none;">Privacy Policy</a>.
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>