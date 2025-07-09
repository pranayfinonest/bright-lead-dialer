<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/permissions.php';

requirePermission('team.manage');

$pageTitle = 'Manager Operations Hub - FINONEST TeleCRM';

// Get team statistics and performance data
$teamStats = getTeamStats();
$teamMembers = getTeamMembers();
$teamPerformance = getTeamPerformance();
$activeCalls = getActiveCalls();
$teamCampaigns = getTeamCampaigns();

function getTeamStats() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE manager_id = ? AND active = 1) as team_size,
            (SELECT COUNT(*) FROM calls c JOIN users u ON c.user_id = u.id WHERE u.manager_id = ? AND DATE(c.created_at) = CURDATE()) as calls_today,
            (SELECT COUNT(*) FROM calls c JOIN users u ON c.user_id = u.id WHERE u.manager_id = ? AND c.disposition = 'converted' AND DATE(c.created_at) = CURDATE()) as conversions_today,
            (SELECT COUNT(*) FROM leads l JOIN users u ON l.assigned_to = u.id WHERE u.manager_id = ?) as team_leads,
            (SELECT COUNT(*) FROM campaigns c JOIN users u ON c.user_id = u.id WHERE u.manager_id = ? AND c.status = 'active') as active_campaigns,
            (SELECT SUM(conv.revenue) FROM conversions conv JOIN calls c ON conv.call_id = c.id JOIN users u ON c.user_id = u.id WHERE u.manager_id = ? AND DATE(conv.created_at) = CURDATE()) as revenue_today
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTeamMembers() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.phone,
            u.last_login,
            COUNT(c.id) as calls_today,
            COUNT(CASE WHEN c.disposition = 'converted' THEN 1 END) as conversions_today,
            AVG(c.duration) as avg_duration,
            (SELECT COUNT(*) FROM leads WHERE assigned_to = u.id) as assigned_leads
        FROM users u
        LEFT JOIN calls c ON u.id = c.user_id AND DATE(c.created_at) = CURDATE()
        WHERE u.manager_id = ? AND u.active = 1
        GROUP BY u.id, u.name, u.email, u.phone, u.last_login
        ORDER BY calls_today DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeamPerformance() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            DATE(c.created_at) as date,
            COUNT(c.id) as calls,
            COUNT(CASE WHEN c.disposition = 'converted' THEN 1 END) as conversions,
            AVG(c.duration) as avg_duration,
            COUNT(DISTINCT c.user_id) as active_agents
        FROM calls c
        JOIN users u ON c.user_id = u.id
        WHERE u.manager_id = ? AND c.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(c.created_at)
        ORDER BY date DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getActiveCalls() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            u.name as agent_name,
            l.name as lead_name,
            l.phone as lead_phone,
            c.created_at as call_start,
            c.duration,
            'active' as status
        FROM calls c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN leads l ON c.lead_id = l.id
        WHERE u.manager_id = ? 
        AND c.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
        AND c.disposition IS NULL
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTeamCampaigns() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            c.id,
            c.name,
            c.status,
            c.type,
            COUNT(cl.id) as total_leads,
            COUNT(CASE WHEN cl.status = 'called' THEN 1 END) as called_leads,
            COUNT(CASE WHEN cl.status = 'converted' THEN 1 END) as converted_leads
        FROM campaigns c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN campaign_leads cl ON c.id = cl.campaign_id
        WHERE u.manager_id = ?
        GROUP BY c.id, c.name, c.status, c.type
        ORDER BY c.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
?>

<div class="manager-dashboard">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Manager Operations Hub</h1>
                <p>Team performance monitoring and management dashboard</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-outline" onclick="generateTeamReport()">
                    <i class="icon-download"></i>
                    Team Report
                </button>
                <button class="btn btn-primary" onclick="showLeadDistribution()">
                    <i class="icon-users"></i>
                    Distribute Leads
                </button>
            </div>
        </div>
    </div>

    <!-- Team Overview Stats -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Team Size</span>
                    <span class="stat-value"><?php echo $teamStats['team_size']; ?></span>
                    <span class="stat-desc">active team members</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-users"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Today's Calls</span>
                    <span class="stat-value"><?php echo $teamStats['calls_today']; ?></span>
                    <span class="stat-desc"><?php echo $teamStats['conversions_today']; ?> conversions</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Team Leads</span>
                    <span class="stat-value"><?php echo number_format($teamStats['team_leads']); ?></span>
                    <span class="stat-desc">assigned to team</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-target"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card secondary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Revenue Today</span>
                    <span class="stat-value">₹<?php echo formatCurrency($teamStats['revenue_today'] ?: 0); ?></span>
                    <span class="stat-desc">team performance</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-trending-up"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Team Monitoring -->
    <div class="monitoring-grid">
        <!-- Team Members Performance -->
        <div class="team-members-card">
            <div class="card-header">
                <h3>Team Performance Today</h3>
                <div class="header-actions">
                    <button class="btn btn-outline btn-sm" onclick="refreshTeamData()">
                        <i class="icon-refresh"></i>
                        Refresh
                    </button>
                </div>
            </div>
            <div class="card-content">
                <div class="team-members-list">
                    <?php foreach ($teamMembers as $member): ?>
                        <?php
                        $conversionRate = $member['calls_today'] > 0 ? 
                            round(($member['conversions_today'] / $member['calls_today']) * 100, 1) : 0;
                        $avgDuration = gmdate('i:s', $member['avg_duration'] ?: 0);
                        $isOnline = $member['last_login'] && strtotime($member['last_login']) > strtotime('-30 minutes');
                        ?>
                        <div class="team-member-item">
                            <div class="member-info">
                                <div class="member-avatar">
                                    <?php echo strtoupper(substr($member['name'], 0, 2)); ?>
                                    <div class="status-indicator <?php echo $isOnline ? 'online' : 'offline'; ?>"></div>
                                </div>
                                <div class="member-details">
                                    <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                                    <p><?php echo htmlspecialchars($member['email']); ?></p>
                                    <p class="member-phone"><?php echo htmlspecialchars($member['phone']); ?></p>
                                </div>
                            </div>
                            
                            <div class="member-stats">
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $member['calls_today']; ?></span>
                                    <span class="stat-label">Calls</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value success"><?php echo $member['conversions_today']; ?></span>
                                    <span class="stat-label">Conversions</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $conversionRate; ?>%</span>
                                    <span class="stat-label">Rate</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?php echo $avgDuration; ?></span>
                                    <span class="stat-label">Avg Duration</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value primary"><?php echo $member['assigned_leads']; ?></span>
                                    <span class="stat-label">Leads</span>
                                </div>
                            </div>
                            
                            <div class="member-actions">
                                <button class="btn-icon" onclick="monitorAgent(<?php echo $member['id']; ?>)" title="Monitor">
                                    <i class="icon-monitor"></i>
                                </button>
                                <button class="btn-icon" onclick="listenToCall(<?php echo $member['id']; ?>)" title="Listen">
                                    <i class="icon-headphones"></i>
                                </button>
                                <button class="btn-icon" onclick="whisperToAgent(<?php echo $member['id']; ?>)" title="Whisper">
                                    <i class="icon-message-circle"></i>
                                </button>
                                <button class="btn-icon" onclick="assignLeads(<?php echo $member['id']; ?>)" title="Assign Leads">
                                    <i class="icon-user-plus"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Active Calls Monitor -->
        <div class="active-calls-card">
            <div class="card-header">
                <h3>Active Calls Monitor</h3>
                <div class="live-indicator">
                    <div class="status-dot"></div>
                    <span>Live</span>
                </div>
            </div>
            <div class="card-content">
                <div class="active-calls-list">
                    <?php if (empty($activeCalls)): ?>
                        <div class="no-active-calls">
                            <i class="icon-phone-off"></i>
                            <p>No active calls at the moment</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activeCalls as $call): ?>
                            <div class="active-call-item">
                                <div class="call-info">
                                    <div class="call-agent">
                                        <strong><?php echo htmlspecialchars($call['agent_name']); ?></strong>
                                    </div>
                                    <div class="call-lead">
                                        <span><?php echo htmlspecialchars($call['lead_name'] ?: 'Unknown'); ?></span>
                                        <span class="call-phone"><?php echo htmlspecialchars($call['lead_phone']); ?></span>
                                    </div>
                                    <div class="call-duration">
                                        <i class="icon-clock"></i>
                                        <span><?php echo gmdate('i:s', time() - strtotime($call['call_start'])); ?></span>
                                    </div>
                                </div>
                                <div class="call-actions">
                                    <button class="btn-icon success" onclick="listenToCall('<?php echo $call['agent_name']; ?>')" title="Listen">
                                        <i class="icon-headphones"></i>
                                    </button>
                                    <button class="btn-icon warning" onclick="whisperToAgent('<?php echo $call['agent_name']; ?>')" title="Whisper">
                                        <i class="icon-message-circle"></i>
                                    </button>
                                    <button class="btn-icon error" onclick="bargeIntoCall('<?php echo $call['agent_name']; ?>')" title="Barge">
                                        <i class="icon-phone-call"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Campaigns and Lead Distribution -->
    <div class="campaigns-leads-grid">
        <!-- Team Campaigns -->
        <div class="team-campaigns-card">
            <div class="card-header">
                <h3>Team Campaigns</h3>
                <button class="btn btn-primary btn-sm" onclick="createTeamCampaign()">
                    <i class="icon-plus"></i>
                    New Campaign
                </button>
            </div>
            <div class="card-content">
                <div class="campaigns-list">
                    <?php foreach ($teamCampaigns as $campaign): ?>
                        <?php
                        $progress = $campaign['total_leads'] > 0 ? 
                            round(($campaign['called_leads'] / $campaign['total_leads']) * 100) : 0;
                        $conversionRate = $campaign['called_leads'] > 0 ? 
                            round(($campaign['converted_leads'] / $campaign['called_leads']) * 100, 1) : 0;
                        ?>
                        <div class="campaign-item">
                            <div class="campaign-header">
                                <h4><?php echo htmlspecialchars($campaign['name']); ?></h4>
                                <div class="campaign-badges">
                                    <span class="badge <?php echo strtolower($campaign['status']); ?>">
                                        <?php echo ucfirst($campaign['status']); ?>
                                    </span>
                                    <span class="badge outline">
                                        <?php echo ucfirst($campaign['type']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="campaign-stats">
                                <div class="progress-section">
                                    <div class="progress-header">
                                        <span>Progress</span>
                                        <span><?php echo $progress; ?>%</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                </div>
                                <div class="campaign-metrics">
                                    <span><?php echo $campaign['called_leads']; ?>/<?php echo $campaign['total_leads']; ?> called</span>
                                    <span><?php echo $campaign['converted_leads']; ?> conversions (<?php echo $conversionRate; ?>%)</span>
                                </div>
                            </div>
                            <div class="campaign-actions">
                                <button class="btn btn-outline btn-sm" onclick="manageCampaign(<?php echo $campaign['id']; ?>)">
                                    Manage
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Lead Distribution -->
        <div class="lead-distribution-card">
            <div class="card-header">
                <h3>Lead Distribution</h3>
                <button class="btn btn-primary btn-sm" onclick="showBulkAssignment()">
                    <i class="icon-shuffle"></i>
                    Bulk Assign
                </button>
            </div>
            <div class="card-content">
                <div class="distribution-controls">
                    <div class="form-group">
                        <label>Distribution Method</label>
                        <select id="distributionMethod" onchange="updateDistributionPreview()">
                            <option value="round_robin">Round Robin</option>
                            <option value="performance_based">Performance Based</option>
                            <option value="workload_balanced">Workload Balanced</option>
                            <option value="manual">Manual Assignment</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Lead Source</label>
                        <select id="leadSource">
                            <option value="unassigned">Unassigned Leads</option>
                            <option value="new_import">New Import</option>
                            <option value="campaign">From Campaign</option>
                        </select>
                    </div>
                </div>
                
                <div class="distribution-preview">
                    <h4>Distribution Preview</h4>
                    <div class="preview-list">
                        <?php foreach (array_slice($teamMembers, 0, 3) as $member): ?>
                            <div class="preview-item">
                                <span class="member-name"><?php echo htmlspecialchars($member['name']); ?></span>
                                <span class="current-leads"><?php echo $member['assigned_leads']; ?> current</span>
                                <span class="new-leads">+5 new</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="distribution-actions">
                    <button class="btn btn-outline" onclick="previewDistribution()">
                        Preview
                    </button>
                    <button class="btn btn-primary" onclick="executeDistribution()">
                        Distribute Leads
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Analytics -->
    <div class="team-analytics-card">
        <div class="card-header">
            <h3>Team Performance Analytics</h3>
            <div class="analytics-controls">
                <select id="analyticsTimeframe" onchange="updateAnalytics()">
                    <option value="today">Today</option>
                    <option value="week" selected>This Week</option>
                    <option value="month">This Month</option>
                </select>
                <button class="btn btn-outline btn-sm" onclick="exportAnalytics()">
                    <i class="icon-download"></i>
                    Export
                </button>
            </div>
        </div>
        <div class="card-content">
            <div class="analytics-grid">
                <div class="analytics-chart">
                    <canvas id="teamPerformanceChart" width="600" height="300"></canvas>
                </div>
                <div class="analytics-summary">
                    <div class="summary-item">
                        <h4>Top Performer</h4>
                        <div class="performer-info">
                            <?php if (!empty($teamMembers)): ?>
                                <div class="performer-avatar">
                                    <?php echo strtoupper(substr($teamMembers[0]['name'], 0, 2)); ?>
                                </div>
                                <div class="performer-details">
                                    <span class="performer-name"><?php echo htmlspecialchars($teamMembers[0]['name']); ?></span>
                                    <span class="performer-stats"><?php echo $teamMembers[0]['calls_today']; ?> calls today</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="summary-item">
                        <h4>Team Average</h4>
                        <div class="average-stats">
                            <?php
                            $avgCalls = !empty($teamMembers) ? round(array_sum(array_column($teamMembers, 'calls_today')) / count($teamMembers), 1) : 0;
                            $avgConversions = !empty($teamMembers) ? round(array_sum(array_column($teamMembers, 'conversions_today')) / count($teamMembers), 1) : 0;
                            ?>
                            <span><?php echo $avgCalls; ?> calls/agent</span>
                            <span><?php echo $avgConversions; ?> conversions/agent</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateTeamReport() {
    window.location.href = '../api/export_team_report.php';
}

function showLeadDistribution() {
    document.querySelector('.lead-distribution-card').scrollIntoView({ behavior: 'smooth' });
}

function refreshTeamData() {
    location.reload();
}

function monitorAgent(agentId) {
    window.open(`agent-monitor.php?id=${agentId}`, '_blank', 'width=1200,height=800');
}

function listenToCall(agentId) {
    alert(`Listening to ${agentId}'s call...`);
    // Implement call listening functionality
}

function whisperToAgent(agentId) {
    const message = prompt(`Whisper message to ${agentId}:`);
    if (message) {
        // Implement whisper functionality
        alert(`Whisper sent to ${agentId}: ${message}`);
    }
}

function bargeIntoCall(agentId) {
    if (confirm(`Barge into ${agentId}'s call?`)) {
        // Implement barge functionality
        alert(`Barging into ${agentId}'s call...`);
    }
}

function assignLeads(agentId) {
    window.location.href = `assign-leads.php?agent=${agentId}`;
}

function createTeamCampaign() {
    window.location.href = 'create-campaign.php';
}

function manageCampaign(campaignId) {
    window.location.href = `campaign-details.php?id=${campaignId}`;
}

function showBulkAssignment() {
    document.querySelector('.distribution-controls').style.display = 'block';
}

function updateDistributionPreview() {
    // Update preview based on selected method
    console.log('Updating distribution preview...');
}

function previewDistribution() {
    alert('Distribution preview would be shown here');
}

function executeDistribution() {
    if (confirm('Execute lead distribution?')) {
        alert('Leads distributed successfully!');
    }
}

function updateAnalytics() {
    // Update analytics based on timeframe
    console.log('Updating analytics...');
}

function exportAnalytics() {
    window.location.href = '../api/export_team_analytics.php';
}

// Initialize real-time updates
document.addEventListener('DOMContentLoaded', function() {
    // Set up real-time updates for active calls
    setInterval(function() {
        // Update active calls display
        console.log('Updating active calls...');
    }, 10000); // Update every 10 seconds
    
    // Initialize performance chart
    console.log('Initializing team performance chart...');
});
</script>

<?php include '../includes/footer.php'; ?>