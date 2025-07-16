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
        $navItems = [
            ['name' => 'Dashboard', 'href' => 'index.php', 'icon' => 'layout-dashboard', 'page' => 'index'],
            ['name' => 'Leads', 'href' => 'leads.php', 'icon' => 'users', 'page' => 'leads'],
            ['name' => 'Dialer', 'href' => 'dialer.php', 'icon' => 'phone', 'page' => 'dialer'],
            ['name' => 'Campaigns', 'href' => 'campaigns.php', 'icon' => 'target', 'page' => 'campaigns'],
            ['name' => 'Messages', 'href' => 'messages.php', 'icon' => 'message-square', 'page' => 'messages'],
            ['name' => 'Schedule', 'href' => 'schedule.php', 'icon' => 'calendar', 'page' => 'schedule'],
            ['name' => 'Analytics', 'href' => 'analytics.php', 'icon' => 'bar-chart-3', 'page' => 'analytics'],
            ['name' => 'Settings', 'href' => 'settings.php', 'icon' => 'settings', 'page' => 'settings'],
        ];
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
            </div>
        </div>
    </div>
</div>