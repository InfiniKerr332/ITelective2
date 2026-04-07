<?php
/**
 * WMSU ARL Hub: Change Password
 */
require_once '../config/auth.php';
;

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $userId = $_SESSION['user_id'];

    if ($new !== $confirm) {
        $error_msg = "New passwords do not match.";
    } elseif (strlen($new) < 8) {
        $error_msg = "New password must be at least 8 characters.";
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (password_verify($current, $user['password'])) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed, $userId])) {
                // Log Audit
                logAudit($pdo, $userId, "SECURITY", "Changed user password");
                $success_msg = "Password updated successfully!";
            } else {
                $error_msg = "System error. Could not update password.";
            }
        } else {
            $error_msg = "Current password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings - WMSU ARL Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #B81C2E;
            --primary-light: rgba(184, 28, 46, 0.08);
            --bg: #F8F9FA;
            --white: #FFFFFF;
            --rich-black: #1A1A2E;
            --text-muted: #6B7280;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            display: flex;
            min-height: 100vh;
        }

        .content {
            flex: 1;
            margin-left: 280px;
            padding: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .security-card {
            background-color: var(--white);
            width: 100%;
            max-width: 500px;
            padding: 50px;
            border-radius: 32px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
        }

        .card-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .card-header i {
            width: 60px;
            height: 60px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 24px;
        }

        h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--rich-black);
            margin-bottom: 8px;
        }

        p { color: var(--text-muted); font-size: 15px; }

        .form-group { margin-bottom: 24px; }

        label {
            display: block;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 700;
            color: var(--rich-black);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1.5px solid #F3F4F6;
            background-color: #F9FAFB;
            font-size: 15px;
            transition: all 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(184, 28, 46, 0.1);
        }

        .btn-update {
            width: 100%;
            padding: 16px;
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-update:hover {
            background-color: #8E1523;
            transform: translateY(-2px);
        }

        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 32px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-error {
            background-color: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .alert-success {
            background-color: #F0FDF4;
            color: #166534;
            border: 1px solid #DCFCE7;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="content">
        <div class="security-card">
            <div class="card-header">
                <i class="fas fa-shield-alt"></i>
                <h1>Update Security</h1>
                <p>Change your password to ensure account safety.</p>
            </div>

            <?php if ($error_msg): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_msg): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_msg; ?>
                </div>
            <?php endif; ?>

            <form action="change-password.php" method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="At least 8 characters" required>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Repeat new password" required>
                </div>

                <button type="submit" name="change_password" class="btn-update">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html>
