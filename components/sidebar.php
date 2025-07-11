<div class="sidebar">
    <div class="sidebar-header">
        <div class="brand-info">
            <div class="brand-icon">
                <i class="icon-phone"></i>
            </div>
            <div class="brand-text">
                <h1>TeleCRM</h1>
                <p>Free & Open Source</p>
            </div>
        </div>
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="icon-chevron-left"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF'], '.php');
        $userRole = $_SESSION['user_role'] ?? 'caller';
        
        // Role-based navigation items
        $navItems = [];
        
        if ($userRole === 'admin') {
            $navItems = [
                ['name' => 'Dashboard', 'href' => 'admin/dashboard.php', 'icon' => 'layout-dashboard', 'page' => 'dashboard'],
                ['name' => 'User Management', 'href' => 'admin/users.php', 'icon' => 'users', 'page' => 'users'],
                ['name' => 'Organization', 'href' => 'admin/organization.php', 'icon' => 'building', 'page' => 'organization'],
                ['name' => 'Integrations', 'href' => 'admin/integrations.php', 'icon' => 'link', 'page' => 'integrations'],
                ['name' => 'System Settings', 'href' => 'admin/settings.php', 'icon' => 'settings', 'page' => 'settings'],
                ['name' => 'Analytics', 'href' => 'admin/analytics.php', 'icon' => 'bar-chart-3', 'page' => 'analytics'],
            ];
        } elseif ($userRole === 'manager') {
            $navItems = [
                ['name' => 'Dashboard', 'href' => 'manager/dashboard.php', 'icon' => 'layout-dashboard', 'page' => 'dashboard'],
                ['name' => 'Team Management', 'href' => 'manager/team.php', 'icon' => 'users', 'page' => 'team'],
                ['name' => 'Lead Management', 'href' => 'leads.php', 'icon' => 'target', 'page' => 'leads'],
                ['name' => 'Campaigns', 'href' => 'campaigns.php', 'icon' => 'megaphone', 'page' => 'campaigns'],
                ['name' => 'Call Monitoring', 'href' => 'manager/monitoring.php', 'icon' => 'headphones', 'page' => 'monitoring'],
                ['name' => 'Reports', 'href' => 'manager/reports.php', 'icon' => 'bar-chart-3', 'page' => 'reports'],
                ['name' => 'Schedule', 'href' => 'schedule.php', 'icon' => 'calendar', 'page' => 'schedule'],
            ];
        } else { // caller
            $navItems = [
                ['name' => 'Dashboard', 'href' => 'caller/dashboard.php', 'icon' => 'layout-dashboard', 'page' => 'dashboard'],
                ['name' => 'Dialer', 'href' => 'dialer.php', 'icon' => 'phone', 'page' => 'dialer'],
                ['name' => 'My Leads', 'href' => 'caller/my-leads.php', 'icon' => 'users', 'page' => 'my-leads'],
                ['name' => 'Messages', 'href' => 'messages.php', 'icon' => 'message-square', 'page' => 'messages'],
                ['name' => 'Follow-ups', 'href' => 'caller/follow-ups.php', 'icon' => 'clock', 'page' => 'follow-ups'],
                ['name' => 'Schedule', 'href' => 'schedule.php', 'icon' => 'calendar', 'page' => 'schedule'],
                ['name' => 'Performance', 'href' => 'caller/performance.php', 'icon' => 'trending-up', 'page' => 'performance'],
            ];
        }
        ?>
        
        <?php foreach ($navItems as $item): ?>
            <a href="<?php echo $item['href']; ?>" 
               class="nav-item <?php echo ($currentPage === $item['page']) ? 'active' : ''; ?>">
                <i class="icon-<?php echo $item['icon']; ?>"></i>
                <span><?php echo $item['name']; ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="user-profile">
            <div class="user-avatar">
                <span><?php echo strtoupper(substr($_SESSION['user_name'], 0, 2)); ?></span>
            </div>
            <div class="user-info">
                <p class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <p class="user-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <p class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></p>
            </div>
        </div>
    </div>
</div>