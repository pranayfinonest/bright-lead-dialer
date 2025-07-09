<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Analytics Dashboard - FINONEST TeleCRM';

// Get analytics data
$analytics_stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_calls,
        COUNT(CASE WHEN disposition = 'converted' THEN 1 END) as conversions,
        AVG(duration) as avg_duration,
        SUM(CASE WHEN disposition = 'converted' THEN revenue ELSE 0 END) as total_revenue
    FROM calls 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$analytics_stmt->execute([$_SESSION['user_id']]);
$analytics = $analytics_stmt->fetch(PDO::FETCH_ASSOC);

$conversion_rate = $analytics['total_calls'] > 0 ? 
    round(($analytics['conversions'] / $analytics['total_calls']) * 100, 1) : 0;

// Get call success breakdown
$success_stmt = $db->prepare("
    SELECT 
        disposition,
        COUNT(*) as count,
        (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM calls WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY))) as percentage
    FROM calls 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY disposition
");
$success_stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$call_breakdown = $success_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get agent performance
$agent_stmt = $db->prepare("
    SELECT 
        u.name,
        COUNT(c.id) as calls,
        COUNT(CASE WHEN c.disposition = 'converted' THEN 1 END) as conversions,
        (COUNT(CASE WHEN c.disposition = 'converted' THEN 1 END) * 100.0 / COUNT(c.id)) as rate,
        SUM(CASE WHEN c.disposition = 'converted' THEN c.revenue ELSE 0 END) as revenue
    FROM users u
    LEFT JOIN calls c ON u.id = c.user_id AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    WHERE u.role = 'agent'
    GROUP BY u.id, u.name
    HAVING calls > 0
    ORDER BY rate DESC
");
$agent_stmt->execute();
$agent_performance = $agent_stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="analytics-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Analytics Dashboard</h1>
                <p>Track performance and gain insights from your calling activities</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline">
                    <i class="icon-calendar"></i>
                    Last 30 Days
                </button>
                <button class="btn btn-primary">
                    <i class="icon-download"></i>
                    Export Report
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Total Calls</span>
                    <span class="stat-value"><?php echo number_format($analytics['total_calls']); ?></span>
                    <span class="stat-change positive">↗ +12.5%</span>
                </div>
                <div class="stat-icon primary">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Conversion Rate</span>
                    <span class="stat-value"><?php echo $conversion_rate; ?>%</span>
                    <span class="stat-change positive">↗ +2.1%</span>
                </div>
                <div class="stat-icon success">
                    <i class="icon-target"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Avg Call Duration</span>
                    <span class="stat-value"><?php echo gmdate('i:s', $analytics['avg_duration'] ?: 0); ?></span>
                    <span class="stat-change positive">↗ +0:45</span>
                </div>
                <div class="stat-icon warning">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Revenue Generated</span>
                    <span class="stat-value">₹<?php echo formatCurrency($analytics['total_revenue'] ?: 0); ?></span>
                    <span class="stat-change positive">↗ +18.7%</span>
                </div>
                <div class="stat-icon secondary">
                    <i class="icon-trending-up"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Tabs -->
    <div class="analytics-tabs-card">
        <div class="card-header">
            <h3>Detailed Analytics</h3>
        </div>
        <div class="card-content">
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showAnalyticsTab('performance')">Performance</button>
                <button class="tab-btn" onclick="showAnalyticsTab('agents')">Agents</button>
                <button class="tab-btn" onclick="showAnalyticsTab('campaigns')">Campaigns</button>
                <button class="tab-btn" onclick="showAnalyticsTab('trends')">Trends</button>
            </div>

            <!-- Performance Tab -->
            <div id="performance-tab" class="tab-content active">
                <div class="performance-grid">
                    <!-- Call Success Rate -->
                    <div class="performance-card">
                        <div class="card-header">
                            <h4>Call Success Rate</h4>
                        </div>
                        <div class="card-content">
                            <div class="success-breakdown">
                                <?php foreach ($call_breakdown as $item): ?>
                                    <?php
                                    $color = match($item['disposition']) {
                                        'converted', 'connected' => 'success',
                                        'no_answer', 'busy' => 'warning',
                                        'failed' => 'error',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <div class="breakdown-item">
                                        <div class="breakdown-header">
                                            <span><?php echo ucwords(str_replace('_', ' ', $item['disposition'])); ?></span>
                                            <span class="<?php echo $color; ?>"><?php echo round($item['percentage'], 1); ?>%</span>
                                        </div>
                                        <div class="progress-bar">
                                            <div class="progress-fill <?php echo $color; ?>" 
                                                 style="width: <?php echo $item['percentage']; ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Best Calling Hours -->
                    <div class="performance-card">
                        <div class="card-header">
                            <h4>Best Calling Hours</h4>
                        </div>
                        <div class="card-content">
                            <div class="calling-hours">
                                <div class="hour-item best">
                                    <span>10:00 AM - 12:00 PM</span>
                                    <span class="badge success">Best</span>
                                </div>
                                <div class="hour-item good">
                                    <span>2:00 PM - 4:00 PM</span>
                                    <span class="badge primary">Good</span>
                                </div>
                                <div class="hour-item average">
                                    <span>4:00 PM - 6:00 PM</span>
                                    <span class="badge warning">Average</span>
                                </div>
                                <div class="hour-item low">
                                    <span>6:00 PM - 8:00 PM</span>
                                    <span class="badge outline">Low</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agents Tab -->
            <div id="agents-tab" class="tab-content">
                <div class="agents-list">
                    <?php foreach ($agent_performance as $agent): ?>
                        <div class="agent-card">
                            <div class="agent-info">
                                <div class="agent-avatar">
                                    <?php echo strtoupper(substr($agent['name'], 0, 2)); ?>
                                </div>
                                <span class="agent-name"><?php echo htmlspecialchars($agent['name']); ?></span>
                            </div>
                            <div class="agent-stats">
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $agent['calls']; ?></span>
                                    <span class="stat-label">Calls</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value success"><?php echo $agent['conversions']; ?></span>
                                    <span class="stat-label">Conversions</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo round($agent['rate'], 1); ?>%</span>
                                    <span class="stat-label">Success Rate</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value primary">₹<?php echo formatCurrency($agent['revenue']); ?></span>
                                    <span class="stat-label">Revenue</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Other tabs would be implemented similarly -->
            <div id="campaigns-tab" class="tab-content">
                <p>Campaign analytics would be displayed here</p>
            </div>

            <div id="trends-tab" class="tab-content">
                <p>Trend analysis would be displayed here</p>
            </div>
        </div>
    </div>
</div>

<script>
function showAnalyticsTab(tabName) {
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
</script>

<?php include 'includes/footer.php'; ?>