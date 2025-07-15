<div class="mobile-header">
    <div class="mobile-header-content">
        <div class="mobile-brand">
            <img src="assets/images/logo.png" alt="FINONEST Logo" class="mobile-logo">
            <div class="mobile-brand-text">
                <h1>FINONEST</h1>
                <p>trust comes first</p>
            </div>
        </div>
        
        <div class="mobile-header-actions">
            <!-- Search Toggle -->
            <button class="mobile-btn" onclick="toggleMobileSearch()">
                <i class="icon-search"></i>
            </button>
            
            <!-- Notifications -->
            <button class="mobile-btn" onclick="toggleMobileNotifications()">
                <i class="icon-bell"></i>
                <span class="notification-badge">3</span>
            </button>
            
            <!-- Menu Toggle -->
            <button class="mobile-btn" onclick="toggleMobileMenu()">
                <i class="icon-menu"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile Search -->
    <div class="mobile-search" id="mobileSearch" style="display: none;">
        <div class="mobile-search-input">
            <i class="icon-search"></i>
            <input type="text" placeholder="Search leads, campaigns..." id="mobileSearchInput">
            <button class="mobile-search-close" onclick="toggleMobileSearch()">
                <i class="icon-x"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile Notifications Panel -->
    <div class="mobile-notifications" id="mobileNotifications" style="display: none;">
        <div class="mobile-notifications-header">
            <h3>Notifications</h3>
            <button class="mobile-close-btn" onclick="toggleMobileNotifications()">
                <i class="icon-x"></i>
            </button>
        </div>
        <div class="mobile-notifications-list">
            <div class="mobile-notification-item">
                <div class="notification-icon primary">
                    <i class="icon-phone"></i>
                </div>
                <div class="notification-content">
                    <p class="notification-title">New lead assigned</p>
                    <p class="notification-desc">John Doe needs follow-up call</p>
                    <span class="notification-time">2 min ago</span>
                </div>
            </div>
            <div class="mobile-notification-item">
                <div class="notification-icon success">
                    <i class="icon-check-circle"></i>
                </div>
                <div class="notification-content">
                    <p class="notification-title">Campaign completed</p>
                    <p class="notification-desc">Home Loan Q1 campaign finished</p>
                    <span class="notification-time">1 hour ago</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMobileSearch() {
    const search = document.getElementById('mobileSearch');
    const input = document.getElementById('mobileSearchInput');
    
    if (search.style.display === 'none') {
        search.style.display = 'block';
        input.focus();
    } else {
        search.style.display = 'none';
    }
}

function toggleMobileNotifications() {
    const notifications = document.getElementById('mobileNotifications');
    
    if (notifications.style.display === 'none') {
        notifications.style.display = 'block';
    } else {
        notifications.style.display = 'none';
    }
}

function toggleMobileMenu() {
    const sidebar = document.querySelector('.mobile-sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}
</script>