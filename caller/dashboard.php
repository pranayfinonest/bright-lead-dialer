<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/permissions.php';

requirePermission('calls.make');

$pageTitle = 'Caller Workspace - FINONEST TeleCRM';

// Get caller-specific data
$callerStats = getCallerStats();
$assignedLeads = getAssignedLeads();
$todaySchedule = getTodaySchedule();
$recentCalls = getRecentCalls();
$followUps = getFollowUps();

function getCallerStats() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT 
            (SELECT COUNT(*) FROM calls WHERE user_id = ? AND DATE(created_at) = CURDATE()) as calls_today,
            (SELECT COUNT(*) FROM calls WHERE user_id = ? AND disposition = 'converted' AND DATE(created_at) = CURDATE()) as conversions_today,
            (SELECT COUNT(*) FROM leads WHERE assigned_to = ?) as assigned_leads,
            (SELECT COUNT(*) FROM leads WHERE assigned_to = ? AND status = 'hot') as hot_leads,
            (SELECT COUNT(*) FROM schedule WHERE user_id = ? AND DATE(scheduled_at) = CURDATE() AND status = 'scheduled') as scheduled_calls,
            (SELECT AVG(duration) FROM calls WHERE user_id = ? AND DATE(created_at) = CURDATE()) as avg_duration,
            (SELECT SUM(revenue) FROM conversions conv JOIN calls c ON conv.call_id = c.id WHERE c.user_id = ? AND DATE(conv.created_at) = CURDATE()) as revenue_today
    ");
    $stmt->execute([
        $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], 
        $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAssignedLeads() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT l.*, 
               COALESCE(c.last_call_time, 'Never') as last_contact,
               COALESCE(c.call_count, 0) as call_attempts
        FROM leads l
        LEFT JOIN (
            SELECT lead_id, 
                   MAX(created_at) as last_call_time,
                   COUNT(*) as call_count
            FROM calls 
            GROUP BY lead_id
        ) c ON l.id = c.lead_id
        WHERE l.assigned_to = ?
        ORDER BY 
            CASE l.status 
                WHEN 'hot' THEN 1 
                WHEN 'warm' THEN 2 
                WHEN 'cold' THEN 3 
            END,
            l.created_at ASC
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodaySchedule() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT s.*, l.name as lead_name, l.phone as lead_phone
        FROM schedule s
        LEFT JOIN leads l ON s.lead_id = l.id
        WHERE s.user_id = ? AND DATE(s.scheduled_at) = CURDATE()
        ORDER BY s.scheduled_at ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentCalls() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT c.*, l.name as lead_name
        FROM calls c
        LEFT JOIN leads l ON c.lead_id = l.id
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getFollowUps() {
    global $db;
    
    $stmt = $db->prepare("
        SELECT l.*, s.scheduled_at as followup_time
        FROM leads l
        JOIN schedule s ON l.id = s.lead_id
        WHERE l.assigned_to = ? 
        AND s.type = 'followup' 
        AND s.status = 'scheduled'
        AND DATE(s.scheduled_at) <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        ORDER BY s.scheduled_at ASC
        LIMIT 10
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include '../includes/header.php';
?>

<div class="caller-dashboard">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Caller Workspace</h1>
                <p>Your personalized calling dashboard and lead management center</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-success" onclick="window.location.href='../dialer.php'">
                    <i class="icon-phone"></i>
                    Start Calling
                </button>
                <button class="btn btn-outline" onclick="window.location.href='my-performance.php'">
                    <i class="icon-bar-chart"></i>
                    My Performance
                </button>
            </div>
        </div>
    </div>

    <!-- Performance Stats -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Calls Today</span>
                    <span class="stat-value"><?php echo $callerStats['calls_today']; ?></span>
                    <span class="stat-desc"><?php echo $callerStats['conversions_today']; ?> conversions</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Assigned Leads</span>
                    <span class="stat-value"><?php echo $callerStats['assigned_leads']; ?></span>
                    <span class="stat-desc"><?php echo $callerStats['hot_leads']; ?> hot leads</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-users"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Scheduled Calls</span>
                    <span class="stat-value"><?php echo $callerStats['scheduled_calls']; ?></span>
                    <span class="stat-desc">for today</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-calendar"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card secondary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Revenue Today</span>
                    <span class="stat-value">₹<?php echo formatCurrency($callerStats['revenue_today'] ?: 0); ?></span>
                    <span class="stat-desc">Avg: <?php echo gmdate('i:s', $callerStats['avg_duration'] ?: 0); ?> per call</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-trending-up"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions-section">
        <div class="card-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="quick-actions-grid">
            <div class="action-card primary" onclick="window.location.href='../dialer.php'">
                <div class="action-icon">
                    <i class="icon-phone"></i>
                </div>
                <h4>Start Dialing</h4>
                <p>Begin your calling session</p>
                <div class="action-stats">
                    <span><?php echo count($assignedLeads); ?> leads in queue</span>
                </div>
            </div>
            
            <div class="action-card success" onclick="window.location.href='my-leads.php'">
                <div class="action-icon">
                    <i class="icon-users"></i>
                </div>
                <h4>My Leads</h4>
                <p>View and manage assigned leads</p>
                <div class="action-stats">
                    <span><?php echo $callerStats['hot_leads']; ?> hot leads</span>
                </div>
            </div>
            
            <div class="action-card warning" onclick="window.location.href='follow-ups.php'">
                <div class="action-icon">
                    <i class="icon-clock"></i>
                </div>
                <h4>Follow-ups</h4>
                <p>Pending follow-up calls</p>
                <div class="action-stats">
                    <span><?php echo count($followUps); ?> pending</span>
                </div>
            </div>
            
            <div class="action-card secondary" onclick="window.location.href='../messages.php'">
                <div class="action-icon">
                    <i class="icon-message-square"></i>
                </div>
                <h4>Send Messages</h4>
                <p>WhatsApp, SMS, Email</p>
                <div class="action-stats">
                    <span>Quick templates</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="caller-content-grid">
        <!-- Today's Schedule -->
        <div class="schedule-card">
            <div class="card-header">
                <h3>Today's Schedule</h3>
                <button class="btn btn-outline btn-sm" onclick="window.location.href='../schedule.php'">
                    View All
                </button>
            </div>
            <div class="card-content">
                <div class="schedule-list">
                    <?php if (empty($todaySchedule)): ?>
                        <div class="empty-schedule">
                            <i class="icon-calendar"></i>
                            <p>No scheduled calls for today</p>
                            <button class="btn btn-outline btn-sm" onclick="scheduleNewCall()">
                                Schedule Call
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($todaySchedule as $item): ?>
                            <div class="schedule-item">
                                <div class="schedule-time">
                                    <span class="time"><?php echo date('g:i A', strtotime($item['scheduled_at'])); ?></span>
                                    <span class="type"><?php echo ucfirst($item['type']); ?></span>
                                </div>
                                <div class="schedule-content">
                                    <h4><?php echo htmlspecialchars($item['lead_name'] ?: $item['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($item['purpose']); ?></p>
                                    <?php if ($item['lead_phone']): ?>
                                        <p class="schedule-phone"><?php echo htmlspecialchars($item['lead_phone']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="schedule-actions">
                                    <?php if ($item['status'] === 'scheduled'): ?>
                                        <button class="btn btn-primary btn-sm" onclick="startCall('<?php echo $item['lead_phone']; ?>')">
                                            <i class="icon-phone"></i>
                                            Call
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Priority Leads -->
        <div class="priority-leads-card">
            <div class="card-header">
                <h3>Priority Leads</h3>
                <button class="btn btn-outline btn-sm" onclick="window.location.href='my-leads.php'">
                    View All
                </button>
            </div>
            <div class="card-content">
                <div class="leads-list">
                    <?php foreach (array_slice($assignedLeads, 0, 5) as $lead): ?>
                        <div class="lead-item">
                            <div class="lead-info">
                                <div class="lead-header">
                                    <h4><?php echo htmlspecialchars($lead['name']); ?></h4>
                                    <span class="status-badge <?php echo strtolower($lead['status']); ?>">
                                        <?php echo ucfirst($lead['status']); ?>
                                    </span>
                                </div>
                                <p class="lead-phone"><?php echo htmlspecialchars($lead['phone']); ?></p>
                                <p class="lead-source">Source: <?php echo htmlspecialchars($lead['source']); ?></p>
                                <p class="lead-attempts">Attempts: <?php echo $lead['call_attempts']; ?></p>
                            </div>
                            <div class="lead-actions">
                                <button class="btn btn-primary btn-sm" onclick="callLead('<?php echo $lead['phone']; ?>', <?php echo $lead['id']; ?>)">
                                    <i class="icon-phone"></i>
                                    Call
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="messageLead(<?php echo $lead['id']; ?>)">
                                    <i class="icon-message-square"></i>
                                    Message
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-ups and Recent Activity -->
    <div class="activity-grid">
        <!-- Follow-ups -->
        <div class="followups-card">
            <div class="card-header">
                <h3>Upcoming Follow-ups</h3>
                <button class="btn btn-outline btn-sm" onclick="window.location.href='follow-ups.php'">
                    View All
                </button>
            </div>
            <div class="card-content">
                <div class="followups-list">
                    <?php if (empty($followUps)): ?>
                        <div class="empty-followups">
                            <i class="icon-clock"></i>
                            <p>No follow-ups scheduled</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($followUps as $followup): ?>
                            <div class="followup-item">
                                <div class="followup-info">
                                    <h4><?php echo htmlspecialchars($followup['name']); ?></h4>
                                    <p class="followup-phone"><?php echo htmlspecialchars($followup['phone']); ?></p>
                                    <p class="followup-time">
                                        <i class="icon-clock"></i>
                                        <?php echo date('M j, g:i A', strtotime($followup['followup_time'])); ?>
                                    </p>
                                </div>
                                <div class="followup-actions">
                                    <button class="btn btn-primary btn-sm" onclick="callLead('<?php echo $followup['phone']; ?>', <?php echo $followup['id']; ?>)">
                                        <i class="icon-phone"></i>
                                        Call Now
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Calls -->
        <div class="recent-calls-card">
            <div class="card-header">
                <h3>Recent Calls</h3>
                <button class="btn btn-outline btn-sm" onclick="window.location.href='call-history.php'">
                    View All
                </button>
            </div>
            <div class="card-content">
                <div class="calls-list">
                    <?php foreach (array_slice($recentCalls, 0, 5) as $call): ?>
                        <div class="call-item">
                            <div class="call-info">
                                <div class="call-header">
                                    <h4><?php echo htmlspecialchars($call['lead_name'] ?: 'Unknown'); ?></h4>
                                    <span class="disposition-badge <?php echo strtolower($call['disposition']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $call['disposition'])); ?>
                                    </span>
                                </div>
                                <p class="call-phone"><?php echo htmlspecialchars($call['phone_number']); ?></p>
                                <p class="call-time">
                                    <i class="icon-clock"></i>
                                    <?php echo date('M j, g:i A', strtotime($call['created_at'])); ?>
                                    (<?php echo gmdate('i:s', $call['duration']); ?>)
                                </p>
                            </div>
                            <div class="call-actions">
                                <button class="btn btn-outline btn-sm" onclick="redialCall('<?php echo $call['phone_number']; ?>')">
                                    <i class="icon-phone"></i>
                                    Redial
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    <div class="performance-summary-card">
        <div class="card-header">
            <h3>Today's Performance Summary</h3>
            <div class="performance-time">
                <i class="icon-clock"></i>
                <span>Last updated: <?php echo date('g:i A'); ?></span>
            </div>
        </div>
        <div class="card-content">
            <div class="performance-metrics">
                <div class="metric-item">
                    <div class="metric-icon success">
                        <i class="icon-phone"></i>
                    </div>
                    <div class="metric-info">
                        <span class="metric-value"><?php echo $callerStats['calls_today']; ?></span>
                        <span class="metric-label">Total Calls</span>
                    </div>
                </div>
                
                <div class="metric-item">
                    <div class="metric-icon primary">
                        <i class="icon-target"></i>
                    </div>
                    <div class="metric-info">
                        <span class="metric-value"><?php echo $callerStats['conversions_today']; ?></span>
                        <span class="metric-label">Conversions</span>
                    </div>
                </div>
                
                <div class="metric-item">
                    <div class="metric-icon warning">
                        <i class="icon-clock"></i>
                    </div>
                    <div class="metric-info">
                        <span class="metric-value"><?php echo gmdate('i:s', $callerStats['avg_duration'] ?: 0); ?></span>
                        <span class="metric-label">Avg Duration</span>
                    </div>
                </div>
                
                <div class="metric-item">
                    <div class="metric-icon secondary">
                        <i class="icon-trending-up"></i>
                    </div>
                    <div class="metric-info">
                        <span class="metric-value">₹<?php echo formatCurrency($callerStats['revenue_today'] ?: 0); ?></span>
                        <span class="metric-label">Revenue</span>
                    </div>
                </div>
            </div>
            
            <div class="performance-actions">
                <button class="btn btn-outline" onclick="window.location.href='my-performance.php'">
                    View Detailed Report
                </button>
                <button class="btn btn-primary" onclick="window.location.href='../dialer.php'">
                    Continue Calling
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function callLead(phone, leadId = null) {
    const url = leadId ? `../dialer.php?number=${encodeURIComponent(phone)}&lead=${leadId}` : `../dialer.php?number=${encodeURIComponent(phone)}`;
    window.location.href = url;
}

function messageLead(leadId) {
    window.location.href = `../messages.php?lead=${leadId}`;
}

function startCall(phone) {
    window.location.href = `../dialer.php?number=${encodeURIComponent(phone)}`;
}

function redialCall(phone) {
    window.location.href = `../dialer.php?number=${encodeURIComponent(phone)}`;
}

function scheduleNewCall() {
    window.location.href = '../schedule.php?action=new';
}

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    location.reload();
}, 300000);

// Show notification for upcoming scheduled calls
document.addEventListener('DOMContentLoaded', function() {
    // Check for calls scheduled in next 15 minutes
    const now = new Date();
    const scheduleItems = document.querySelectorAll('.schedule-item');
    
    scheduleItems.forEach(item => {
        const timeElement = item.querySelector('.time');
        if (timeElement) {
            const callTime = new Date();
            // Parse time and check if it's within 15 minutes
            // Implementation would depend on exact time format
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>