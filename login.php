<?php
session_start();
require_once 'config/database.php';
require_once 'config/auth.php';
require_once 'config/permissions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $defaultRoute = $permissionManager->getDefaultRoute($_SESSION['user_role']);
    header("Location: $defaultRoute");
    exit;
}

$error = '';
$requireOTP = false;
$email = '';
$showResetForm = false;

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $otp = $_POST['otp'] ?? '';
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'forgot_password':
                if ($authManager->sendPasswordResetOTP($email)) {
                    $success_message = 'OTP sent to your email address';
                    $showResetForm = true;
                } else {
                    $error = 'Email not found';
                }
                break;
                
            case 'reset_password':
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                $resetOTP = $_POST['reset_otp'] ?? '';
                
                if ($newPassword !== $confirmPassword) {
                    $error = 'Passwords do not match';
                } elseif ($authManager->verifyPasswordResetOTP($email, $resetOTP, $newPassword)) {
                    $success_message = 'Password reset successfully. Please login.';
                    $showResetForm = false;
                } else {
                    $error = 'Invalid or expired OTP';
                    $showResetForm = true;
                }
                break;
                
            default:
                $result = $authManager->login($email, $password, $otp);
                
                if ($result['success']) {
                    $defaultRoute = $permissionManager->getDefaultRoute($result['role']);
                    header("Location: $defaultRoute");
                    exit;
                } elseif (isset($result['require_otp'])) {
                    $requireOTP = true;
                } else {
                    $error = $result['error'] ?? 'Login failed';
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FINONEST TeleCRM</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="assets/images/logo.png" alt="FINONEST Logo" class="auth-logo">
                <h1>FINONEST</h1>
                <p>trust comes first</p>
            </div>
            
            <div class="auth-tabs">
                <button class="tab-btn <?php echo !$showResetForm ? 'active' : ''; ?>" onclick="showTab('login')">Login</button>
                <button class="tab-btn <?php echo $showResetForm ? 'active' : ''; ?>" onclick="showTab('forgot')">Reset Password</button>
            </div>
            
            <!-- Login Form -->
            <div id="login-tab" class="tab-content <?php echo !$showResetForm ? 'active' : ''; ?>">
                <form method="POST" class="auth-form">
                    <h2>Welcome Back</h2>
                    <p class="auth-subtitle">Sign in to your TeleCRM account</p>
                    
                    <?php if ($error && !$showResetForm): ?>
                        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($success_message) && !$showResetForm): ?>
                        <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($email); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <?php if ($requireOTP): ?>
                        <div class="form-group">
                            <label for="otp">Enter OTP</label>
                            <input type="text" id="otp" name="otp" placeholder="6-digit code" maxlength="6" required>
                            <small>Check your email or authenticator app for the OTP code</small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" onclick="showTab('forgot')" class="forgot-link">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary full-width">
                        <?php echo $requireOTP ? 'Verify & Sign In' : 'Sign In'; ?>
                    </button>
                    
                    <div class="demo-accounts">
                        <p><strong>Demo Accounts:</strong></p>
                        <div class="demo-grid">
                            <div class="demo-account">
                                <strong>Admin:</strong> admin@finonest.com / password
                            </div>
                            <div class="demo-account">
                                <strong>Manager:</strong> manager@finonest.com / password
                            </div>
                            <div class="demo-account">
                                <strong>Caller:</strong> caller@finonest.com / password
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Password Reset Form -->
            <div id="forgot-tab" class="tab-content <?php echo $showResetForm ? 'active' : ''; ?>">
                <?php if (!$showResetForm): ?>
                    <form method="POST" class="auth-form">
                        <input type="hidden" name="action" value="forgot_password">
                        <h2>Reset Password</h2>
                        <p class="auth-subtitle">Enter your email to receive OTP</p>
                        
                        <div class="form-group">
                            <label for="forgot_email">Email Address</label>
                            <input type="email" id="forgot_email" name="email" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary full-width">Send OTP</button>
                        
                        <div class="auth-footer">
                            <p><a href="#" onclick="showTab('login')">Back to Login</a></p>
                        </div>
                    </form>
                <?php else: ?>
                    <form method="POST" class="auth-form">
                        <input type="hidden" name="action" value="reset_password">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <h2>Enter New Password</h2>
                        <p class="auth-subtitle">Enter the OTP sent to your email and your new password</p>
                        
                        <?php if ($error && $showResetForm): ?>
                            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($success_message) && $showResetForm): ?>
                            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="reset_otp">OTP Code</label>
                            <input type="text" id="reset_otp" name="reset_otp" placeholder="6-digit code" maxlength="6" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary full-width">Reset Password</button>
                        
                        <div class="auth-footer">
                            <p><a href="#" onclick="showTab('login')">Back to Login</a></p>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }
        
        // Auto-focus on OTP input if visible
        document.addEventListener('DOMContentLoaded', function() {
            const otpInput = document.getElementById('otp');
            if (otpInput) {
                otpInput.focus();
            }
        });
    </script>
</body>
</html>