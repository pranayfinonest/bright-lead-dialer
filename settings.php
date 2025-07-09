<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Settings - FINONEST TeleCRM';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $stmt = $db->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, phone = ?, bio = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['first_name'] . ' ' . $_POST['last_name'],
                    $_POST['email'],
                    $_POST['phone'],
                    $_POST['bio'],
                    $_SESSION['user_id']
                ]);
                $success_message = "Profile updated successfully!";
                break;
                
            case 'change_password':
                if (password_verify($_POST['current_password'], getCurrentUserPassword())) {
                    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$new_password, $_SESSION['user_id']]);
                    $success_message = "Password changed successfully!";
                } else {
                    $error_message = "Current password is incorrect!";
                }
                break;
        }
    }
}

function getCurrentUserPassword() {
    global $db;
    $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['password'];
}

// Get current user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="settings-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Settings</h1>
                <p>Configure your TeleCRM preferences and system settings</p>
            </div>
            <button class="btn btn-primary" onclick="saveAllSettings()">
                <i class="icon-save"></i>
                Save Changes
            </button>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="settings-tabs-card">
        <div class="card-content">
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showSettingsTab('profile')">Profile</button>
                <button class="tab-btn" onclick="showSettingsTab('notifications')">Notifications</button>
                <button class="tab-btn" onclick="showSettingsTab('calling')">Calling</button>
                <button class="tab-btn" onclick="showSettingsTab('security')">Security</button>
                <button class="tab-btn" onclick="showSettingsTab('integrations')">Integrations</button>
                <button class="tab-btn" onclick="showSettingsTab('appearance')">Appearance</button>
            </div>

            <!-- Profile Tab -->
            <div id="profile-tab" class="tab-content active">
                <div class="settings-section">
                    <div class="section-header">
                        <h3><i class="icon-user"></i> Profile Information</h3>
                    </div>
                    <form method="POST" class="settings-form">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars(explode(' ', $user['name'], 2)[1] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" rows="3" 
                                      placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>

                <div class="settings-section">
                    <div class="section-header">
                        <h3>Agent Settings</h3>
                    </div>
                    <div class="settings-form">
                        <div class="form-group">
                            <label for="agent_id">Agent ID</label>
                            <input type="text" id="agent_id" value="<?php echo $user['agent_id'] ?? 'SA001'; ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label for="department">Department</label>
                            <select id="department" name="department">
                                <option value="sales">Sales</option>
                                <option value="support">Customer Support</option>
                                <option value="followup">Follow-up</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="shift">Work Shift</label>
                            <select id="shift" name="shift">
                                <option value="morning">Morning (9 AM - 6 PM)</option>
                                <option value="evening">Evening (2 PM - 11 PM)</option>
                                <option value="night">Night (6 PM - 3 AM)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div id="notifications-tab" class="tab-content">
                <div class="settings-section">
                    <div class="section-header">
                        <h3><i class="icon-bell"></i> Notification Preferences</h3>
                    </div>
                    <div class="settings-form">
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Lead Assignments</h4>
                                <p>Get notified when new leads are assigned</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Callback Reminders</h4>
                                <p>Reminders for scheduled callbacks</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Campaign Updates</h4>
                                <p>Updates about campaign performance</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>System Alerts</h4>
                                <p>Important system notifications</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="subsection">
                        <h4>Notification Methods</h4>
                        <div class="setting-item">
                            <span>Email Notifications</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <span>Push Notifications</span>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="setting-item">
                            <span>SMS Notifications</span>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="security-tab" class="tab-content">
                <div class="settings-section">
                    <div class="section-header">
                        <h3><i class="icon-shield"></i> Security Settings</h3>
                    </div>
                    <div class="subsection">
                        <h4>Change Password</h4>
                        <form method="POST" class="settings-form">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>

                    <div class="subsection">
                        <h4>Two-Factor Authentication</h4>
                        <div class="setting-item">
                            <div class="setting-info">
                                <h4>Enable 2FA</h4>
                                <p>Add an extra layer of security</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other tabs would be implemented similarly -->
            <div id="calling-tab" class="tab-content">
                <p>Calling preferences would be configured here</p>
            </div>

            <div id="integrations-tab" class="tab-content">
                <p>Third-party integrations would be managed here</p>
            </div>

            <div id="appearance-tab" class="tab-content">
                <p>Appearance settings would be configured here</p>
            </div>
        </div>
    </div>
</div>

<script>
function showSettingsTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    event.target.classList.add('active');
}

function saveAllSettings() {
    alert('All settings would be saved here');
}
</script>

<?php include 'includes/footer.php'; ?>