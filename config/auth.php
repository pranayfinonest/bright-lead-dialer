<?php
// Enhanced Authentication System with OTP and Role-based Access

class AuthManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function login($email, $password, $otp = null) {
        $stmt = $this->db->prepare("
            SELECT id, name, email, password, role, phone, otp_secret, otp_enabled, 
                   failed_attempts, locked_until, last_login
            FROM users 
            WHERE email = ? AND active = 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Check if account is locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            return ['success' => false, 'error' => 'Account temporarily locked'];
        }
        
        if (!password_verify($password, $user['password'])) {
            $this->incrementFailedAttempts($user['id']);
            return ['success' => false, 'error' => 'Invalid credentials'];
        }
        
        // Check 2FA if enabled
        if ($user['otp_enabled'] && $user['otp_secret']) {
            if (!$otp) {
                return ['success' => false, 'require_otp' => true];
            }
            
            if (!$this->verifyOTP($user['otp_secret'], $otp)) {
                return ['success' => false, 'error' => 'Invalid OTP code'];
            }
        }
        
        // Successful login
        $this->resetFailedAttempts($user['id']);
        $this->updateLastLogin($user['id']);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['login_time'] = time();
        
        // Log successful login
        $this->logActivity($user['id'], 'login', 'User logged in successfully');
        
        return ['success' => true, 'role' => $user['role'], 'user' => $user];
    }
    
    public function sendPasswordResetOTP($email) {
        $stmt = $this->db->prepare("SELECT id, name FROM users WHERE email = ? AND active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }
        
        $otp = sprintf('%06d', mt_rand(0, 999999));
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $stmt = $this->db->prepare("
            INSERT INTO password_resets (user_id, otp, expires_at, created_at) 
            VALUES (?, ?, ?, NOW()) 
            ON DUPLICATE KEY UPDATE otp = ?, expires_at = ?, created_at = NOW()
        ");
        $stmt->execute([$user['id'], $otp, $expires, $otp, $expires]);
        
        // Send OTP via email/SMS
        $this->sendOTPNotification($email, $user['name'], $otp);
        
        return true;
    }
    
    public function verifyPasswordResetOTP($email, $otp, $newPassword) {
        $stmt = $this->db->prepare("
            SELECT pr.user_id 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE u.email = ? AND pr.otp = ? AND pr.expires_at > NOW()
        ");
        $stmt->execute([$email, $otp]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            return false;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$hashedPassword, $reset['user_id']]);
        
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE user_id = ?");
        $stmt->execute([$reset['user_id']]);
        
        $this->logActivity($reset['user_id'], 'password_reset', 'Password reset successfully');
        
        return true;
    }
    
    private function incrementFailedAttempts($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET failed_attempts = failed_attempts + 1,
                locked_until = CASE 
                    WHEN failed_attempts >= 4 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                    ELSE locked_until 
                END
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    private function resetFailedAttempts($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET failed_attempts = 0, locked_until = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }
    
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    private function verifyOTP($secret, $otp) {
        // Implement TOTP verification (Google Authenticator compatible)
        // This is a simplified version - use a proper TOTP library in production
        return strlen($otp) === 6 && is_numeric($otp);
    }
    
    private function sendOTPNotification($email, $name, $otp) {
        // Implement email/SMS sending logic
        // This would integrate with your preferred service
        error_log("OTP for $email: $otp");
    }
    
    private function logActivity($userId, $type, $description) {
        $stmt = $this->db->prepare("
            INSERT INTO activities (user_id, type, title, description, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $type, ucfirst(str_replace('_', ' ', $type)), $description]);
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'User logged out');
        }
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role'],
                'phone' => $_SESSION['user_phone']
            ];
        }
        return null;
    }
}

// Global authentication functions
function isLoggedIn() {
    global $authManager;
    return $authManager->isLoggedIn();
}

function requireLogin() {
    global $authManager;
    $authManager->requireLogin();
}

function getCurrentUser() {
    global $authManager;
    return $authManager->getCurrentUser();
}

function logout() {
    global $authManager;
    $authManager->logout();
}

// Initialize auth manager
$authManager = new AuthManager($db);
?>