<div class="mobile-navigation">
    <div class="mobile-nav-container">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF'], '.php');
        $userRole = $_SESSION['user_role'] ?? 'caller';
        
        // Role-based mobile navigation
        $mobileNavItems = [];
        
        if ($userRole === 'admin') {
            $mobileNavItems = [
                ['name' => 'Dashboard', 'href' => 'admin/dashboard.php', 'icon' => 'layout-dashboard', 'page' => 'dashboard'],
                ['name' => 'Users', 'href' => 'admin/users.php', 'icon' => 'users', 'page' => 'users'],
                ['name' => 'Analytics', 'href' => 'admin/analytics.php', 'icon' => 'bar-chart-3', 'page' => 'analytics'],
                ['name' => 'Settings', 'href' => 'admin/settings.php', 'icon' => 'settings', 'page' => 'settings'],
            ];
        } elseif ($userRole === 'manager') {
            $mobileNavItems = [
                ['name' => 'Dashboard', 'href' => 'manager/dashboard.php', 'icon' => 'layout-dashboard', 'page' => 'dashboard'],
                ['name' => 'Team', 'href' => 'manager/team.php', 'icon' => 'users', 'page' => 'team'],
                ['name' => 'Leads', 'href' => 'leads.php', 'icon' => 'target', 'page' => 'leads'],
                ['name' => 'Reports', 'href' => 'manager/reports.php', 'icon' => 'bar-chart-3', 'page' => 'reports'],
            ];
        } else { // caller
            $mobileNavItems = [
                ['name' => 'Dashboard', 'href' => 'caller/dashboard.php', 'icon' => 'layout-dashboard', 'page' => 'dashboard'],
                ['name' => 'Dialer', 'href' => 'dialer.php', 'icon' => 'phone', 'page' => 'dialer'],
                ['name' => 'Leads', 'href' => 'caller/my-leads.php', 'icon' => 'users', 'page' => 'my-leads'],
                ['name' => 'Messages', 'href' => 'messages.php', 'icon' => 'message-square', 'page' => 'messages'],
                ['name' => 'Schedule', 'href' => 'schedule.php', 'icon' => 'calendar', 'page' => 'schedule'],
            ];
        }
        ?>
        
        <?php foreach ($mobileNavItems as $item): ?>
            <a href="<?php echo $item['href']; ?>" 
               class="mobile-nav-item <?php echo ($currentPage === $item['page']) ? 'active' : ''; ?>">
                <div class="mobile-nav-icon">
                    <i class="icon-<?php echo $item['icon']; ?>"></i>
                </div>
                <span class="mobile-nav-label"><?php echo $item['name']; ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Mobile Sidebar Overlay -->
<div class="mobile-sidebar-overlay" onclick="closeMobileSidebar()"></div>

<!-- Mobile Sidebar -->
<div class="mobile-sidebar">
    <div class="mobile-sidebar-header">
        <div class="mobile-user-profile">
            <div class="mobile-user-avatar">
                <span><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></span>
            </div>
            <div class="mobile-user-info">
                <p class="mobile-user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <p class="mobile-user-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <p class="mobile-user-role"><?php echo ucfirst($_SESSION['user_role']); ?></p>
            </div>
        </div>
        <button class="mobile-sidebar-close" onclick="closeMobileSidebar()">
            <i class="icon-x"></i>
        </button>
    </div>
    
    <div class="mobile-sidebar-content">
        <div class="mobile-sidebar-section">
            <h4>Quick Actions</h4>
            <div class="mobile-quick-actions">
                <a href="dialer.php" class="mobile-quick-action">
                    <i class="icon-phone"></i>
                    <span>Start Calling</span>
                </a>
                <a href="leads.php?action=add" class="mobile-quick-action">
                    <i class="icon-user-plus"></i>
                    <span>Add Lead</span>
                </a>
                <a href="messages.php" class="mobile-quick-action">
                    <i class="icon-message-square"></i>
                    <span>Send Message</span>
                </a>
            </div>
        </div>
        
        <div class="mobile-sidebar-section">
            <h4>Account</h4>
            <div class="mobile-account-links">
                <a href="settings.php" class="mobile-account-link">
                    <i class="icon-settings"></i>
                    <span>Settings</span>
                </a>
                <a href="profile.php" class="mobile-account-link">
                    <i class="icon-user"></i>
                    <span>Profile</span>
                </a>
                <a href="logout.php" class="mobile-account-link logout">
                    <i class="icon-log-out"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function closeMobileSidebar() {
    const sidebar = document.querySelector('.mobile-sidebar');
    const overlay = document.querySelector('.mobile-sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.remove('show');
    }
    if (overlay) {
        overlay.classList.remove('show');
    }
}

function toggleMobileMenu() {
    const sidebar = document.querySelector('.mobile-sidebar');
    const overlay = document.querySelector('.mobile-sidebar-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
}
</script>