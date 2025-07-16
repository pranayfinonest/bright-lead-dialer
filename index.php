<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Dashboard - FINONEST TeleCRM';
include 'includes/header.php';
?>

<div class="dashboard-container">
    <!-- Welcome Header -->
    <div class="welcome-header">
        <div class="brand-section">
            <img src="assets/images/logo.png" alt="FINONEST Logo" class="logo">
            <div class="brand-text">
                <h1>FINONEST</h1>
                <p>trust comes first</p>
            </div>
        </div>
        <p class="description">
            Professional telecalling platform designed for financial services, loan agents, and sales teams
        </p>
        <div class="session-status">
            <i class="icon-clock"></i>
            <span>Active Session: <?php echo getSessionDuration(); ?></span>
        </div>
    </div>

    <!-- Performance Header -->
    <div class="performance-header">
        <h2>Good morning, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h2>
        <p>Ready to make today productive? You have <?php echo getPendingLeadsCount(); ?> leads to call.</p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <?php
        $stats = getDashboardStats();
        ?>
        <div class="stat-card primary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Total Calls Today</span>
                    <span class="stat-value"><?php echo $stats['calls_today']; ?></span>
                    <span class="stat-change positive">+12%</span>
                    <span class="stat-desc">vs yesterday</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Leads Generated</span>
                    <span class="stat-value"><?php echo $stats['leads_generated']; ?></span>
                    <span class="stat-change positive">+8%</span>
                    <span class="stat-desc">this week</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-users"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Conversion Rate</span>
                    <span class="stat-value"><?php echo $stats['conversion_rate']; ?>%</span>
                    <span class="stat-change positive">+2.3%</span>
                    <span class="stat-desc">monthly average</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-target"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Revenue Impact</span>
                    <span class="stat-value">₹<?php echo formatCurrency($stats['revenue']); ?></span>
                    <span class="stat-change positive">+15%</span>
                    <span class="stat-desc">this month</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-trending-up"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Call Performance Today -->
    <div class="performance-grid">
        <div class="stat-card success">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Successful Calls</span>
                    <span class="stat-value"><?php echo $stats['successful_calls']; ?></span>
                    <span class="stat-desc"><?php echo $stats['success_rate']; ?>% success rate</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Failed Attempts</span>
                    <span class="stat-value"><?php echo $stats['failed_calls']; ?></span>
                    <span class="stat-desc"><?php echo $stats['failed_rate']; ?>% failed rate</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-x-circle"></i>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Scheduled Callbacks</span>
                    <span class="stat-value"><?php echo $stats['callbacks']; ?></span>
                    <span class="stat-desc">pending today</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-calendar"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="main-content-grid">
        <!-- Quick Actions -->
        <div class="quick-actions-section">
            <?php include 'components/quick-actions.php'; ?>
        </div>
        
        <!-- Recent Activity -->
        <div class="recent-activity-section">
            <?php include 'components/recent-activity.php'; ?>
        </div>
    </div>

    <!-- Today's Priority Section -->
    <div class="priority-section">
        <div class="section-header">
            <h2>Today's Priorities</h2>
            <span class="update-time">Updated <?php echo getLastUpdateTime(); ?></span>
        </div>
        <div class="priority-grid">
            <div class="priority-card warning">
                <h3>High Priority Callbacks</h3>
                <p><?php echo getHighPriorityCallbacks(); ?> leads waiting for follow-up</p>
                <span class="due-time">Due before 2:00 PM</span>
            </div>
            <div class="priority-card primary">
                <h3>New Lead Assignment</h3>
                <p><?php echo getNewLeadsCount(); ?> fresh leads from website</p>
                <span class="due-time">Call within 1 hour</span>
            </div>
            <div class="priority-card success">
                <h3>Campaign Performance</h3>
                <p>Home Loan Q1 exceeding targets</p>
                <span class="due-time">125% of goal achieved</span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>