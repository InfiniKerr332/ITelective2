<?php
/**
 * WMSU ARL Hub: Authentication & Role Management
 */
require_once __DIR__ . '/paths.php';
session_start();
require_once __DIR__ . '/db.php';

/**
 * Handle Login Function
 */
function loginUser($pdo, $email, $password) {
    global $error_msg;
    
    if (!str_ends_with(strtolower($email), '@wmsu.edu.ph')) {
        $error_msg = "Please use your @wmsu.edu.ph institutional email.";
        return false;
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Check verification
        if (isset($user['is_verified']) && $user['is_verified'] == 0) {
            header("Location: " . BASE_URL . "auth/verify.php?email=" . urlencode($email));
            exit();
        }

        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = !empty($user['first_name']) ? ($user['first_name'] . ' ' . $user['last_name']) : $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        
        // Log the action
        logAudit($pdo, $user['id'], 'Login', 'User successfully logged in.');
        return true;
    } 
    return false;
}

/**
 * Check Authentication & Authorization
 */
function checkAuth($role = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "auth/login.php?error=required");
        exit();
    }
    
    if ($role && $_SESSION['user_role'] !== $role) {
        die("Unauthorized access.");
    }
}

/**
 * Handle Sign Out
 */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . BASE_URL . "auth/login.php");
    exit();
}
?>
