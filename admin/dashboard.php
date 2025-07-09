<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/permissions.php';

requirePermission('system.manage');

$pageTitle = 'Admin Control Center - FINONEST TeleCRM';

// Get comprehensive system statistics
$systemStats = getSystemStats();
$userStats = getUserStats();
$performanceStats = getPerformanceStats();
$systemHealth = getSystemHealth();

function getSystemStats() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE active = 1) as total_users,
            (SELECT COUNT(*) FROM users WHERE role = 'caller' AND active = 1) as total_callers,
            (SELECT COUNT(*) FROM users WHERE role = 'manager' AND active = 1) as total_managers,
            (SELECT COUNT(*) FROM leads) as total_leads,
            (SELECT COUNT(*) FROM campaigns WHERE status = 'active') as active_campaigns,
            (SELECT COUNT(*) FROM calls WHERE DATE(created_at) = CURDATE()) as calls_today,
            (SELECT COUNT(*) FROM calls WHERE disposition = 'converted' AND DATE(created_at) = CURDATE()) as conversions_today,
            (SELECT SUM(revenue) FROM conversions WHERE DATE(created_at) = CURDATE()) as revenue_today,
            (SELECT COUNT(*) FROM messages WHERE DATE(created_at) = CURDATE()) as messages_today
    ");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserStats() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.name,
            u.role,
            u.last_login,
            COUNT(c.id) as total_calls,
            COUNT(CASE WHEN c.disposition = 'converted' THEN 1 END) as conversions,
            AVG(c.duration) as avg_duration,
            SUM(CASE WHEN c.disposition = 'converted' THEN conv.revenue ELSE 0 END) as revenue
        FROM users u
        LEFT JOIN calls c ON u.id = c.user_id AND DATE(c.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        LEFT JOIN conversions conv ON c.id = conv.call_id
        WHERE u.active = 1 AND u.role IN ('caller', 'manager')
        GROUP BY u.id, u.name, u.role, u.last_login
        ORDER BY total_calls DESC
        LIMIT 10
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPerformanceStats() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as calls,
            COUNT(CASE WHEN disposition = 'converted' THEN 1 END) as conversions,
            AVG(duration) as avg_duration,
            COUNT(DISTINCT user_id) as active_agents
        FROM calls 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSystemHealth() {
    global $db;
    
    // Check database connection
    $dbHealth = 'healthy';
    try {
        $db->query('SELECT 1');
    } catch (Exception $e) {
        $dbHealth = 'error';
    }
    
    // Check recent activity
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM activities WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute();
    $recentActivity = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    return [
        'database' => $dbHealth,
        'api_services' => 'operational',
        'email_service' => 'connected',
        'sms_gateway' => 'limited',
        'recent_activity' => $recentActivity
    ];
}

include '../includes/header.php';
?>

<div class="admin-dashboard">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Admin Control Center</h1>
                <p>System-wide overview and management dashboard</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="exportSystemReport()">
                    <i class="icon-download"></i>
                    Export Report
                </button>
                <button class="btn btn-primary" onclick="window.location.href='system-settings.php'">
                    <i class="icon-settings"></i>
                    System Settings
                </button>
            </div>
        </div>
    </div>

    <!-- System Overview Stats -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Total Users</span>
                    <span class="stat-value"><?php echo $systemStats['total_users']; ?></span>
                    <span class="stat-desc"><?php echo $systemStats['total_callers']; ?> callers, <?php echo $systemStats['total_managers']; ?> managers</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-users"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Total Leads</span>
                    <span class="stat-value"><?php echo number_format($systemStats['total_leads']); ?></span>
                    <span class="stat-desc">across all campaigns</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-target"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Active Campaigns</span>
                    <span class="stat-value"><?php echo $systemStats['active_campaigns']; ?></span>
                    <span class="stat-desc">running campaigns</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-play"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card secondary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Today's Performance</span>
                    <span class="stat-value"><?php echo $systemStats['calls_today']; ?></span>
                    <span class="stat-desc"><?php echo $systemStats['conversions_today']; ?> conversions, ₹<?php echo formatCurrency($systemStats['revenue_today'] ?: 0); ?></span>
                </div>
                <div class="stat-icon">
                    <i class="icon-trending-up"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Management Actions -->
    <div class="admin-actions-grid">
        <div class="action-card" onclick="window.location.href='users.php'">
            <div class="action-icon primary">
                <i class="icon-users"></i>
            </div>
            <h3>User Management</h3>
            <p>Manage users, roles, and permissions</p>
            <div class="action-stats">
                <span><?php echo $systemStats['total_users']; ?> total users</span>
            </div>
        </div>
        
        <div class="action-card" onclick="window.location.href='organization.php'">
            <div class="action-icon secondary">
                <i class="icon-building"></i>
            </div>
            <h3>Organization</h3>
            <p>Configure hierarchy and departments</p>
            <div class="action-stats">
                <span>Multi-level structure</span>
            </div>
        </div>
        
        <div class="action-card" onclick="window.location.href='integrations.php'">
            <div class="action-icon success">
                <i class="icon-link"></i>
            </div>
            <h3>Integrations</h3>
            <p>Manage external service integrations</p>
            <div class="action-stats">
                <span>WhatsApp, CRM, API</span>
            </div>
        </div>
        
        <div class="action-card" onclick="window.location.href='system-analytics.php'">
            <div class="action-icon warning">
                <i class="icon-bar-chart"></i>
            </div>
            <h3>System Analytics</h3>
            <p>Comprehensive system reports</p>
            <div class="action-stats">
                <span>Real-time insights</span>
            </div>
        </div>
        
        <div class="action-card" onclick="window.location.href='call-settings.php'">
            <div class="action-icon error">
                <i class="icon-phone"></i>
            </div>
            <h3>Call Settings</h3>
            <p>Configure dialer and IVR settings</p>
            <div class="action-stats">
                <span>Auto-dialer, IVR</span>
            </div>
        </div>
        
        <div class="action-card" onclick="window.location.href='notifications.php'">
            <div class="action-icon primary">
                <i class="icon-bell"></i>
            </div>
            <h3>Notifications</h3>
            <p>Manage system notification rules</p>
            <div class="action-stats">
                <span>Email, SMS, Push</span>
            </div>
        </div>
    </div>

    <!-- User Performance Table -->
    <div class="performance-table-card">
        <div class="card-header">
            <h3>User Performance (Last 7 Days)</h3>
            <div class="header-actions">
                <button class="btn btn-outline btn-sm" onclick="refreshPerformanceData()">
                    <i class="icon-refresh"></i>
                    Refresh
                </button>
                <button class="btn btn-outline btn-sm" onclick="exportUserReport()">
                    <i class="icon-download"></i>
                    Export
                </button>
            </div>
        </div>
        <div class="card-content">
            <div class="table-container">
                <table class="performance-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Total Calls</th>
                            <th>Conversions</th>
                            <th>Conversion Rate</th>
                            <th>Avg Duration</th>
                            <th>Revenue</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userStats as $user): ?>
                            <?php
                            $conversionRate = $user['total_calls'] > 0 ? 
                                round(($user['conversions'] / $user['total_calls']) * 100, 1) : 0;
                            $avgDuration = gmdate('i:s', $user['avg_duration'] ?: 0);
                            ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['total_calls']; ?></td>
                                <td class="success"><?php echo $user['conversions']; ?></td>
                                <td><?php echo $conversionRate; ?>%</td>
                                <td><?php echo $avgDuration; ?></td>
                                <td class="primary">₹<?php echo formatCurrency($user['revenue'] ?: 0); ?></td>
                                <td><?php echo $user['last_login'] ? date('M j, g:i A', strtotime($user['last_login'])) : 'Never'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon" onclick="viewUserDetails(<?php echo $user['id']; ?>)" title="View Details">
                                            <i class="icon-eye"></i>
                                        </button>
                                        <button class="btn-icon" onclick="editUser(<?php echo $user['id']; ?>)" title="Edit User">
                                            <i class="icon-edit"></i>
                                        </button>
                                        <button class="btn-icon" onclick="monitorUser(<?php echo $user['id']; ?>)" title="Monitor Activity">
                                            <i class="icon-monitor"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Health and Recent Activities -->
    <div class="system-health-grid">
        <div class="health-card">
            <div class="card-header">
                <h3>System Health</h3>
                <div class="health-status <?php echo $systemHealth['database'] === 'healthy' ? 'online' : 'offline'; ?>">
                    <div class="status-dot"></div>
                    <span><?php echo $systemHealth['database'] === 'healthy' ? 'All Systems Operational' : 'System Issues Detected'; ?></span>
                </div>
            </div>
            <div class="card-content">
                <div class="health-metrics">
                    <div class="metric">
                        <span class="metric-label">Database</span>
                        <span class="metric-status <?php echo $systemHealth['database'] === 'healthy' ? 'success' : 'error'; ?>">
                            <?php echo ucfirst($systemHealth['database']); ?>
                        </span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">API Services</span>
                        <span class="metric-status success">Operational</span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Email Service</span>
                        <span class="metric-status success">Connected</span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">SMS Gateway</span>
                        <span class="metric-status warning">Limited</span>
                    </div>
                    <div class="metric">
                        <span class="metric-label">Recent Activity</span>
                        <span class="metric-status success"><?php echo $systemHealth['recent_activity']; ?> events/hour</span>
                    </div>
                </div>
                
                <div class="health-actions">
                    <button class="btn btn-outline btn-sm" onclick="runSystemDiagnostics()">
                        <i class="icon-activity"></i>
                        Run Diagnostics
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="viewSystemLogs()">
                        <i class="icon-file-text"></i>
                        View Logs
                    </button>
                </div>
            </div>
        </div>
        
        <div class="recent-activities-card">
            <div class="card-header">
                <h3>Recent System Activities</h3>
                <button class="btn btn-outline btn-sm" onclick="viewAllActivities()">
                    View All
                </button>
            </div>
            <div class="card-content">
                <div class="activity-list">
                    <?php
                    $stmt = $db->prepare("
                        SELECT a.*, u.name as user_name 
                        FROM activities a 
                        LEFT JOIN users u ON a.user_id = u.id 
                        ORDER BY a.created_at DESC 
                        LIMIT 5
                    ");
                    $stmt->execute();
                    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($activities as $activity):
                    ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $activity['status']; ?>">
                                <i class="icon-<?php echo $activity['type'] === 'login' ? 'log-in' : ($activity['type'] === 'call' ? 'phone' : 'activity'); ?>"></i>
                            </div>
                            <div class="activity-content">
                                <p><?php echo htmlspecialchars($activity['title']); ?></p>
                                <div class="activity-meta">
                                    <span class="activity-user"><?php echo htmlspecialchars($activity['user_name'] ?: 'System'); ?></span>
                                    <span class="activity-time"><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="performance-chart-card">
        <div class="card-header">
            <h3>7-Day Performance Trend</h3>
            <div class="chart-controls">
                <select id="chartMetric" onchange="updateChart()">
                    <option value="calls">Total Calls</option>
                    <option value="conversions">Conversions</option>
                    <option value="agents">Active Agents</option>
                </select>
            </div>
        </div>
        <div class="card-content">
            <div class="chart-container">
                <canvas id="performanceChart" width="800" height="300"></canvas>
            </div>
            <div class="chart-legend">
                <?php foreach ($performanceStats as $stat): ?>
                    <div class="legend-item">
                        <span class="legend-date"><?php echo date('M j', strtotime($stat['date'])); ?></span>
                        <span class="legend-calls"><?php echo $stat['calls']; ?> calls</span>
                        <span class="legend-conversions"><?php echo $stat['conversions']; ?> conversions</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportSystemReport() {
    window.location.href = '../api/export_system_report.php';
}

function refreshPerformanceData() {
    location.reload();
}

function exportUserReport() {
    window.location.href = '../api/export_user_report.php';
}

function viewUserDetails(userId) {
    window.location.href = `user-details.php?id=${userId}`;
}

function editUser(userId) {
    window.location.href = `users.php?edit=${userId}`;
}

function monitorUser(userId) {
    window.location.href = `user-monitor.php?id=${userId}`;
}

function runSystemDiagnostics() {
    alert('Running system diagnostics...');
    // Implement system diagnostics
}

function viewSystemLogs() {
    window.location.href = 'system-logs.php';
}

function viewAllActivities() {
    window.location.href = 'activities.php';
}

function updateChart() {
    // Implement chart update logic
    console.log('Updating chart...');
}

// Initialize performance chart
document.addEventListener('DOMContentLoaded', function() {
    // Chart initialization would go here
    console.log('Initializing performance chart...');
});
</script>

<?php include '../includes/footer.php'; ?>