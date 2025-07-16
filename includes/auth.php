<?php
require_once 'config/roles.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function login($email, $password, $otp = null) {
    global $db;
    
    $stmt = $db->prepare("SELECT id, name, email, password, role, otp_secret, otp_verified FROM users WHERE email = ? AND active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Check if 2FA is enabled
        if ($user['otp_secret'] && !$user['otp_verified']) {
            if (!$otp || !verifyOTP($user['otp_secret'], $otp)) {
                return ['success' => false, 'require_otp' => true];
            }
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        // Log login activity
        logActivity($user['id'], 'login', 'User logged in', 'success');
        
        return ['success' => true, 'role' => $user['role']];
    }
    
    return ['success' => false, 'error' => 'Invalid credentials'];
}

function logout() {
    if (isLoggedIn()) {
        logActivity($_SESSION['user_id'], 'logout', 'User logged out', 'success');
    }
    session_destroy();
    header('Location: login.php');
    exit;
}

function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }
    return null;
}

function sendPasswordResetOTP($email) {
    global $db;
    
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $otp = sprintf('%06d', mt_rand(0, 999999));
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $stmt = $db->prepare("
            INSERT INTO password_resets (user_id, otp, expires_at) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE otp = ?, expires_at = ?
        ");
        $stmt->execute([$user['id'], $otp, $expires, $otp, $expires]);
        
        // Send OTP via email/SMS (implement your preferred method)
        sendOTPEmail($email, $otp);
        
        return true;
    }
    
    return false;
}

function verifyPasswordResetOTP($email, $otp, $newPassword) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT pr.user_id 
        FROM password_resets pr 
        JOIN users u ON pr.user_id = u.id 
        WHERE u.email = ? AND pr.otp = ? AND pr.expires_at > NOW()
    ");
    $stmt->execute([$email, $otp]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reset) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $reset['user_id']]);
        
        $stmt = $db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$reset['user_id']]);
        
        return true;
    }
    
    return false;
}

function verifyOTP($secret, $otp) {
    // Implement TOTP verification (Google Authenticator compatible)
    // This is a simplified version - use a proper TOTP library in production
    return true; // Placeholder
}

function sendOTPEmail($email, $otp) {
    // Implement email sending logic
    // This is a placeholder - integrate with your email service
    return true;
}

function logActivity($userId, $type, $description, $status = 'success') {
    global $db;
    
    $stmt = $db->prepare("
        INSERT INTO activities (user_id, type, title, description, status, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $type, ucfirst($type), $description, $status]);
}
?>