<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Schedule Manager - FINONEST TeleCRM';

// Get today's schedule
$stmt = $db->prepare("
    SELECT s.*, l.name as lead_name, l.phone as lead_phone
    FROM schedule s
    LEFT JOIN leads l ON s.lead_id = l.id
    WHERE s.user_id = ? AND DATE(s.scheduled_at) = CURDATE()
    ORDER BY s.scheduled_at ASC
");
$stmt->execute([$_SESSION['user_id']]);
$todaySchedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get upcoming week stats
$week_stmt = $db->prepare("
    SELECT 
        DATE(scheduled_at) as date,
        COUNT(*) as count,
        COUNT(CASE WHEN priority = 'high' THEN 1 END) as important
    FROM schedule 
    WHERE user_id = ? 
    AND DATE(scheduled_at) BETWEEN CURDATE() + INTERVAL 1 DAY AND CURDATE() + INTERVAL 7 DAY
    GROUP BY DATE(scheduled_at)
    ORDER BY date
");
$week_stmt->execute([$_SESSION['user_id']]);
$upcomingWeek = $week_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get schedule stats
$stats_stmt = $db->prepare("
    SELECT 
        COUNT(CASE WHEN DATE(scheduled_at) = CURDATE() THEN 1 END) as today_count,
        COUNT(CASE WHEN DATE(scheduled_at) = CURDATE() AND status = 'completed' THEN 1 END) as completed_count,
        COUNT(CASE WHEN DATE(scheduled_at) = CURDATE() AND status = 'scheduled' THEN 1 END) as upcoming_count,
        COUNT(CASE WHEN DATE(scheduled_at) BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY THEN 1 END) as week_count
    FROM schedule 
    WHERE user_id = ?
");
$stats_stmt->execute([$_SESSION['user_id']]);
$schedule_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="schedule-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Schedule Manager</h1>
                <p>Manage your calls, meetings, and follow-ups</p>
            </div>
            <button class="btn btn-primary" onclick="showScheduleModal()">
                <i class="icon-plus"></i>
                Schedule New
            </button>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Today's Schedule</span>
                    <span class="stat-value"><?php echo $schedule_stats['today_count']; ?></span>
                </div>
                <div class="stat-icon primary">
                    <i class="icon-calendar"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Completed</span>
                    <span class="stat-value"><?php echo $schedule_stats['completed_count']; ?></span>
                </div>
                <div class="stat-icon success">
                    <i class="icon-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Upcoming</span>
                    <span class="stat-value"><?php echo $schedule_stats['upcoming_count']; ?></span>
                </div>
                <div class="stat-icon warning">
                    <i class="icon-calendar"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">This Week</span>
                    <span class="stat-value"><?php echo $schedule_stats['week_count']; ?></span>
                </div>
                <div class="stat-icon secondary">
                    <i class="icon-calendar"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="schedule-layout">
        <!-- Today's Schedule -->
        <div class="today-schedule-card">
            <div class="card-header">
                <h3>Today's Schedule</h3>
            </div>
            <div class="card-content">
                <div class="schedule-list">
                    <?php foreach ($todaySchedule as $item): ?>
                        <div class="schedule-item">
                            <div class="schedule-icon">
                                <i class="icon-<?php echo $item['type'] === 'call' ? 'phone' : ($item['type'] === 'meeting' ? 'video' : 'message-square'); ?>"></i>
                            </div>
                            
                            <div class="schedule-content">
                                <div class="schedule-header">
                                    <h4><?php echo htmlspecialchars($item['lead_name'] ?: $item['title']); ?></h4>
                                    <span class="status-badge <?php echo $item['status']; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </div>
                                <p class="schedule-purpose"><?php echo htmlspecialchars($item['purpose']); ?></p>
                                <p class="schedule-phone"><?php echo htmlspecialchars($item['lead_phone']); ?></p>
                            </div>
                            
                            <div class="schedule-time">
                                <p class="time"><?php echo date('g:i A', strtotime($item['scheduled_at'])); ?></p>
                                <p class="type"><?php echo ucfirst($item['type']); ?></p>
                            </div>
                            
                            <div class="schedule-actions">
                                <?php if ($item['status'] === 'scheduled'): ?>
                                    <button class="btn btn-primary btn-sm" onclick="startCall('<?php echo $item['lead_phone']; ?>')">
                                        <i class="icon-phone"></i>
                                        Call
                                    </button>
                                    <button class="btn btn-outline btn-sm" onclick="reschedule(<?php echo $item['id']; ?>)">
                                        Reschedule
                                    </button>
                                <?php elseif ($item['status'] === 'completed'): ?>
                                    <button class="btn btn-outline btn-sm" onclick="viewNotes(<?php echo $item['id']; ?>)">
                                        View Notes
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($todaySchedule)): ?>
                        <div class="empty-schedule">
                            <p>No appointments scheduled for today</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Week -->
        <div class="upcoming-week-card">
            <div class="card-header">
                <h3>Upcoming Week</h3>
            </div>
            <div class="card-content">
                <div class="week-list">
                    <?php foreach ($upcomingWeek as $day): ?>
                        <div class="week-item">
                            <div class="week-info">
                                <p class="week-date"><?php echo date('l', strtotime($day['date'])); ?></p>
                                <p class="week-count"><?php echo $day['count']; ?> appointments</p>
                            </div>
                            <div class="week-stats">
                                <p class="total-count"><?php echo $day['count']; ?></p>
                                <p class="urgent-count"><?php echo $day['important']; ?> urgent</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($upcomingWeek)): ?>
                        <div class="empty-week">
                            <p>No appointments scheduled for this week</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <button class="btn btn-outline full-width" onclick="viewFullCalendar()">
                    View Full Calendar
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-schedule-card">
        <div class="card-header">
            <h3>Quick Schedule</h3>
        </div>
        <div class="card-content">
            <div class="quick-actions-grid">
                <div class="quick-action-card" onclick="scheduleCall()">
                    <div class="action-icon primary">
                        <i class="icon-phone"></i>
                    </div>
                    <h4>Schedule Call</h4>
                    <p>Book a follow-up call with a lead</p>
                </div>
                
                <div class="quick-action-card" onclick="bookMeeting()">
                    <div class="action-icon success">
                        <i class="icon-video"></i>
                    </div>
                    <h4>Book Meeting</h4>
                    <p>Schedule a video meeting</p>
                </div>
                
                <div class="quick-action-card" onclick="setReminder()">
                    <div class="action-icon warning">
                        <i class="icon-clock"></i>
                    </div>
                    <h4>Set Reminder</h4>
                    <p>Create a follow-up reminder</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showScheduleModal() {
    alert('Schedule modal would open here');
}

function startCall(phone) {
    window.location.href = `dialer.php?number=${encodeURIComponent(phone)}`;
}

function reschedule(id) {
    alert(`Reschedule appointment ${id}`);
}

function viewNotes(id) {
    alert(`View notes for appointment ${id}`);
}

function viewFullCalendar() {
    alert('Full calendar view would open here');
}

function scheduleCall() {
    alert('Schedule call modal would open here');
}

function bookMeeting() {
    alert('Book meeting modal would open here');
}

function setReminder() {
    alert('Set reminder modal would open here');
}
</script>

<?php include 'includes/footer.php'; ?>