// Main JavaScript for FINONEST TeleCRM

// Global variables
let sidebarCollapsed = false;
let currentUser = null;

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
    setupEventListeners();
    loadUserData();
});

function initializeApp() {
    // Check if user is logged in
    checkAuthStatus();
    
    // Initialize mobile responsiveness
    handleMobileLayout();
    
    // Setup global search
    setupGlobalSearch();
    
    // Initialize notifications
    initializeNotifications();
}

function setupEventListeners() {
    // Sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Mobile navigation
    setupMobileNavigation();
    
    // Global search
    const globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('input', handleGlobalSearch);
    }
    
    // Notification dropdown
    setupNotificationDropdown();
    
    // User menu dropdown
    setupUserMenuDropdown();
    
    // Window resize handler
    window.addEventListener('resize', handleWindowResize);
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebarCollapsed = !sidebarCollapsed;
    
    if (sidebarCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
    } else {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('sidebar-collapsed');
    }
    
    // Save preference
    localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
}

function handleMobileLayout() {
    const isMobile = window.innerWidth <= 768;
    const desktopLayout = document.querySelector('.desktop-layout');
    const mobileLayout = document.querySelector('.mobile-layout');
    
    if (isMobile) {
        if (desktopLayout) desktopLayout.style.display = 'none';
        if (mobileLayout) mobileLayout.style.display = 'block';
    } else {
        if (desktopLayout) desktopLayout.style.display = 'flex';
        if (mobileLayout) mobileLayout.style.display = 'none';
    }
}

function setupMobileNavigation() {
    const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
    mobileNavItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            mobileNavItems.forEach(nav => nav.classList.remove('active'));
            // Add active class to clicked item
            this.classList.add('active');
        });
    });
}

function setupGlobalSearch() {
    const searchInput = document.getElementById('globalSearch');
    if (!searchInput) return;
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                performGlobalSearch(query);
            }, 300);
        }
    });
}

function performGlobalSearch(query) {
    // Show loading state
    showSearchLoading();
    
    // Perform search
    fetch(`api/global_search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(results => {
            displaySearchResults(results);
        })
        .catch(error => {
            console.error('Search error:', error);
            hideSearchResults();
        });
}

function displaySearchResults(results) {
    // Implementation for displaying search results
    console.log('Search results:', results);
}

function showSearchLoading() {
    // Show loading indicator
}

function hideSearchResults() {
    // Hide search results
}

function setupNotificationDropdown() {
    const notificationBtn = document.querySelector('.notification-btn');
    const notificationPanel = document.getElementById('notificationPanel');
    
    if (notificationBtn && notificationPanel) {
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleNotifications();
        });
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationPanel.contains(e.target)) {
                notificationPanel.classList.remove('show');
            }
        });
    }
}

function toggleNotifications() {
    const panel = document.getElementById('notificationPanel');
    if (panel) {
        panel.classList.toggle('show');
        
        // Load notifications if opening
        if (panel.classList.contains('show')) {
            loadNotifications();
        }
    }
}

function loadNotifications() {
    fetch('api/get_notifications.php')
        .then(response => response.json())
        .then(notifications => {
            displayNotifications(notifications);
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function displayNotifications(notifications) {
    const notificationList = document.querySelector('.notification-list');
    if (!notificationList) return;
    
    notificationList.innerHTML = '';
    
    notifications.forEach(notification => {
        const notificationItem = document.createElement('div');
        notificationItem.className = 'notification-item';
        notificationItem.innerHTML = `
            <div class="notification-content">
                <p class="notification-title">${notification.title}</p>
                <p class="notification-desc">${notification.description}</p>
                <span class="notification-time">${notification.time}</span>
            </div>
        `;
        notificationList.appendChild(notificationItem);
    });
}

function setupUserMenuDropdown() {
    const userBtn = document.querySelector('.user-btn');
    const userMenu = document.getElementById('userMenu');
    
    if (userBtn && userMenu) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleUserMenu();
        });
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                userMenu.classList.remove('show');
            }
        });
    }
}

function toggleUserMenu() {
    const menu = document.getElementById('userMenu');
    if (menu) {
        menu.classList.toggle('show');
    }
}

function handleWindowResize() {
    handleMobileLayout();
}

function checkAuthStatus() {
    // Check if user is authenticated
    fetch('api/check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (!data.authenticated && !window.location.pathname.includes('login.php')) {
                window.location.href = 'login.php';
            }
        })
        .catch(error => {
            console.error('Auth check error:', error);
        });
}

function loadUserData() {
    fetch('api/get_user_data.php')
        .then(response => response.json())
        .then(user => {
            currentUser = user;
            updateUserInterface(user);
        })
        .catch(error => {
            console.error('Error loading user data:', error);
        });
}

function updateUserInterface(user) {
    // Update user name in sidebar
    const userName = document.querySelector('.user-name');
    if (userName) {
        userName.textContent = user.name;
    }
    
    // Update user email in sidebar
    const userEmail = document.querySelector('.user-email');
    if (userEmail) {
        userEmail.textContent = user.email;
    }
    
    // Update user avatar
    const userAvatar = document.querySelector('.user-avatar span');
    if (userAvatar) {
        userAvatar.textContent = user.name.substring(0, 2).toUpperCase();
    }
}

function initializeNotifications() {
    // Check for new notifications periodically
    setInterval(checkForNewNotifications, 30000); // Every 30 seconds
}

function checkForNewNotifications() {
    fetch('api/check_new_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.hasNew) {
                updateNotificationBadge(data.count);
                showNotificationToast(data.latest);
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'block' : 'none';
    }
}

function showNotificationToast(notification) {
    // Create and show toast notification
    const toast = document.createElement('div');
    toast.className = 'notification-toast';
    toast.innerHTML = `
        <div class="toast-content">
            <h4>${notification.title}</h4>
            <p>${notification.description}</p>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Utility functions
function formatCurrency(amount) {
    if (amount >= 100000) {
        return (amount / 100000).toFixed(1) + 'L';
    } else if (amount >= 1000) {
        return (amount / 1000).toFixed(1) + 'K';
    }
    return amount.toLocaleString();
}

function formatPhoneNumber(phone) {
    // Format phone number for display
    return phone.replace(/(\d{2})(\d{5})(\d{5})/, '+$1 $2 $3');
}

function showLoading() {
    const loader = document.createElement('div');
    loader.id = 'globalLoader';
    loader.className = 'global-loader';
    loader.innerHTML = '<div class="loader-spinner"></div>';
    document.body.appendChild(loader);
}

function hideLoading() {
    const loader = document.getElementById('globalLoader');
    if (loader) {
        loader.remove();
    }
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Hide and remove toast
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Export functions for global use
window.TeleCRM = {
    toggleSidebar,
    toggleNotifications,
    toggleUserMenu,
    showToast,
    showLoading,
    hideLoading,
    formatCurrency,
    formatPhoneNumber
};