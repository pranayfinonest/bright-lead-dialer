<header class="header-bar">
    <div class="header-content">
        <!-- Logo and Brand -->
        <div class="header-left">
            <div class="brand-section">
                <img src="assets/images/logo.png" alt="FINONEST Logo" class="header-logo">
                <div class="brand-text">
                    <h1>FINONEST</h1>
                    <p>trust comes first</p>
                </div>
            </div>
            
            <!-- Search -->
            <div class="search-section">
                <div class="search-input">
                    <i class="icon-search"></i>
                    <input type="text" placeholder="Search leads, campaigns, or contacts..." id="globalSearch">
                </div>
            </div>
        </div>

        <!-- Right side actions -->
        <div class="header-right">
            <!-- Live Status -->
            <div class="status-indicator online">
                <div class="status-dot"></div>
                <span>Online</span>
            </div>

            <!-- Notifications -->
            <div class="notification-dropdown">
                <button class="notification-btn" onclick="toggleNotifications()">
                    <i class="icon-bell"></i>
                    <span class="notification-badge">3</span>
                </button>
                <div class="notification-panel" id="notificationPanel">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                    </div>
                    <div class="notification-list">
                        <div class="notification-item">
                            <div class="notification-content">
                                <p class="notification-title">New lead assigned</p>
                                <p class="notification-desc">John Doe needs follow-up call</p>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-content">
                                <p class="notification-title">Campaign completed</p>
                                <p class="notification-desc">Home Loan Q1 campaign finished</p>
                            </div>
                        </div>
                        <div class="notification-item">
                            <div class="notification-content">
                                <p class="notification-title">Callback reminder</p>
                                <p class="notification-desc">Call Sarah at 3:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings -->
            <button class="icon-btn" onclick="window.location.href='settings.php'">
                <i class="icon-settings"></i>
            </button>

            <!-- User Menu -->
            <div class="user-dropdown">
                <button class="user-btn" onclick="toggleUserMenu()">
                    <i class="icon-user"></i>
                </button>
                <div class="user-menu" id="userMenu">
                    <a href="profile.php">Profile</a>
                    <a href="team-settings.php">Team Settings</a>
                    <a href="preferences.php">Preferences</a>
                    <hr>
                    <a href="logout.php" class="logout-link">Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>