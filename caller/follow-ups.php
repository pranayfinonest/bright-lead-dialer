<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/permissions.php';

requirePermission('followups.manage');

$pageTitle = 'Follow-ups - FINONEST TeleCRM';

// Get follow-ups for the caller
$stmt = $db->prepare("
    SELECT l.*, s.scheduled_at as followup_time, s.priority, s.notes as followup_notes
    FROM leads l
    JOIN schedule s ON l.id = s.lead_id
    WHERE l.assigned_to = ? 
    AND s.type = 'followup' 
    AND s.status = 'scheduled'
    ORDER BY s.scheduled_at ASC
");
$stmt->execute([$_SESSION['user_id']]);
$followups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get follow-up statistics
$stats_stmt = $db->prepare("
    SELECT 
        COUNT(CASE WHEN DATE(s.scheduled_at) = CURDATE() THEN 1 END) as due_today,
        COUNT(CASE WHEN DATE(s.scheduled_at) < CURDATE() THEN 1 END) as overdue,
        COUNT(CASE WHEN DATE(s.scheduled_at) BETWEEN CURDATE() + INTERVAL 1 DAY AND CURDATE() + INTERVAL 7 DAY THEN 1 END) as this_week,
        COUNT(CASE WHEN s.priority = 'high' THEN 1 END) as high_priority
    FROM leads l
    JOIN schedule s ON l.id = s.lead_id
    WHERE l.assigned_to = ? 
    AND s.type = 'followup' 
    AND s.status = 'scheduled'
");
$stats_stmt->execute([$_SESSION['user_id']]);
$followup_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="followups-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Follow-ups</h1>
                <p>Manage your scheduled follow-up calls and meetings</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-primary" onclick="scheduleNewFollowup()">
                    <i class="icon-plus"></i>
                    Schedule Follow-up
                </button>
                <button class="btn btn-outline" onclick="viewCalendar()">
                    <i class="icon-calendar"></i>
                    Calendar View
                </button>
            </div>
        </div>
    </div>

    <!-- Follow-up Statistics -->
    <div class="stats-grid">
        <div class="stat-card error">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Due Today</span>
                    <span class="stat-value"><?php echo $followup_stats['due_today']; ?></span>
                    <span class="stat-desc">needs immediate attention</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-clock"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Overdue</span>
                    <span class="stat-value"><?php echo $followup_stats['overdue']; ?></span>
                    <span class="stat-desc">past due date</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-alert-triangle"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card primary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">This Week</span>
                    <span class="stat-value"><?php echo $followup_stats['this_week']; ?></span>
                    <span class="stat-desc">upcoming follow-ups</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-calendar"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card secondary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">High Priority</span>
                    <span class="stat-value"><?php echo $followup_stats['high_priority']; ?></span>
                    <span class="stat-desc">important follow-ups</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-flag"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-ups List -->
    <div class="followups-list-card">
        <div class="card-header">
            <h3>Scheduled Follow-ups</h3>
            <div class="header-actions">
                <select id="priorityFilter" onchange="filterFollowups()">
                    <option value="">All Priorities</option>
                    <option value="high">High Priority</option>
                    <option value="medium">Medium Priority</option>
                    <option value="low">Low Priority</option>
                </select>
                <select id="timeFilter" onchange="filterFollowups()">
                    <option value="">All Time</option>
                    <option value="overdue">Overdue</option>
                    <option value="today">Due Today</option>
                    <option value="tomorrow">Due Tomorrow</option>
                    <option value="week">This Week</option>
                </select>
            </div>
        </div>
        <div class="card-content">
            <?php if (empty($followups)): ?>
                <div class="empty-followups">
                    <div class="empty-icon">
                        <i class="icon-clock"></i>
                    </div>
                    <h3>No Follow-ups Scheduled</h3>
                    <p>You don't have any follow-ups scheduled at the moment.</p>
                    <button class="btn btn-primary" onclick="scheduleNewFollowup()">
                        <i class="icon-plus"></i>
                        Schedule Your First Follow-up
                    </button>
                </div>
            <?php else: ?>
                <div class="followups-list">
                    <?php foreach ($followups as $followup): ?>
                        <?php
                        $followupDate = strtotime($followup['followup_time']);
                        $now = time();
                        $isOverdue = $followupDate < $now;
                        $isDueToday = date('Y-m-d', $followupDate) === date('Y-m-d');
                        $isDueTomorrow = date('Y-m-d', $followupDate) === date('Y-m-d', strtotime('+1 day'));
                        
                        $urgencyClass = '';
                        if ($isOverdue) $urgencyClass = 'overdue';
                        elseif ($isDueToday) $urgencyClass = 'due-today';
                        elseif ($isDueTomorrow) $urgencyClass = 'due-tomorrow';
                        ?>
                        
                        <div class="followup-item <?php echo $urgencyClass; ?>" data-priority="<?php echo $followup['priority']; ?>" data-time="<?php echo date('Y-m-d', $followupDate); ?>">
                            <div class="followup-header">
                                <div class="followup-lead-info">
                                    <h4 class="lead-name"><?php echo htmlspecialchars($followup['name']); ?></h4>
                                    <p class="lead-phone">
                                        <i class="icon-phone"></i>
                                        <?php echo htmlspecialchars($followup['phone']); ?>
                                    </p>
                                    <?php if ($followup['email']): ?>
                                        <p class="lead-email">
                                            <i class="icon-mail"></i>
                                            <?php echo htmlspecialchars($followup['email']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="followup-meta">
                                    <div class="followup-time">
                                        <div class="time-display">
                                            <span class="date"><?php echo date('M j, Y', $followupDate); ?></span>
                                            <span class="time"><?php echo date('g:i A', $followupDate); ?></span>
                                        </div>
                                        <?php if ($isOverdue): ?>
                                            <span class="urgency-badge overdue">
                                                <i class="icon-alert-triangle"></i>
                                                Overdue
                                            </span>
                                        <?php elseif ($isDueToday): ?>
                                            <span class="urgency-badge due-today">
                                                <i class="icon-clock"></i>
                                                Due Today
                                            </span>
                                        <?php elseif ($isDueTomorrow): ?>
                                            <span class="urgency-badge due-tomorrow">
                                                <i class="icon-calendar"></i>
                                                Due Tomorrow
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="priority-indicator">
                                        <span class="priority-badge <?php echo $followup['priority']; ?>">
                                            <?php echo ucfirst($followup['priority']); ?> Priority
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="followup-content">
                                <div class="lead-status">
                                    <span class="status-badge <?php echo strtolower($followup['status']); ?>">
                                        <?php echo ucfirst($followup['status']); ?>
                                    </span>
                                    <span class="source-info">Source: <?php echo htmlspecialchars($followup['source'] ?: 'Unknown'); ?></span>
                                </div>
                                
                                <?php if ($followup['followup_notes']): ?>
                                    <div class="followup-notes">
                                        <h5>Follow-up Notes:</h5>
                                        <p><?php echo htmlspecialchars($followup['followup_notes']); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($followup['notes']): ?>
                                    <div class="lead-notes">
                                        <h5>Lead Notes:</h5>
                                        <p><?php echo htmlspecialchars($followup['notes']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="followup-actions">
                                <button class="btn btn-primary" onclick="callLead('<?php echo $followup['phone']; ?>', <?php echo $followup['id']; ?>)">
                                    <i class="icon-phone"></i>
                                    Call Now
                                </button>
                                <button class="btn btn-outline" onclick="sendMessage(<?php echo $followup['id']; ?>)">
                                    <i class="icon-message-square"></i>
                                    Message
                                </button>
                                <button class="btn btn-outline" onclick="rescheduleFollowup(<?php echo $followup['id']; ?>)">
                                    <i class="icon-calendar"></i>
                                    Reschedule
                                </button>
                                <button class="btn btn-outline" onclick="markCompleted(<?php echo $followup['id']; ?>)">
                                    <i class="icon-check"></i>
                                    Mark Done
                                </button>
                                
                                <div class="dropdown">
                                    <button class="btn-icon dropdown-toggle">
                                        <i class="icon-more-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="#" onclick="editFollowup(<?php echo $followup['id']; ?>)">
                                            <i class="icon-edit"></i>
                                            Edit Follow-up
                                        </a>
                                        <a href="#" onclick="viewLeadHistory(<?php echo $followup['id']; ?>)">
                                            <i class="icon-history"></i>
                                            View History
                                        </a>
                                        <a href="#" onclick="addNotes(<?php echo $followup['id']; ?>)">
                                            <i class="icon-file-text"></i>
                                            Add Notes
                                        </a>
                                        <hr>
                                        <a href="#" onclick="cancelFollowup(<?php echo $followup['id']; ?>)" class="text-error">
                                            <i class="icon-x"></i>
                                            Cancel Follow-up
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Schedule Follow-up Modal -->
<div id="scheduleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Schedule Follow-up</h3>
            <button class="modal-close" onclick="closeScheduleModal()">&times;</button>
        </div>
        <form class="modal-body" onsubmit="saveFollowup(event)">
            <div class="form-group">
                <label for="leadSelect">Select Lead</label>
                <select id="leadSelect" required>
                    <option value="">Choose a lead...</option>
                    <!-- Populated via JavaScript -->
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="followupDate">Date</label>
                    <input type="date" id="followupDate" required>
                </div>
                <div class="form-group">
                    <label for="followupTime">Time</label>
                    <input type="time" id="followupTime" required>
                </div>
            </div>
            <div class="form-group">
                <label for="followupPriority">Priority</label>
                <select id="followupPriority" required>
                    <option value="low">Low Priority</option>
                    <option value="medium" selected>Medium Priority</option>
                    <option value="high">High Priority</option>
                </select>
            </div>
            <div class="form-group">
                <label for="followupNotes">Notes</label>
                <textarea id="followupNotes" rows="3" placeholder="Add notes about this follow-up..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeScheduleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Schedule Follow-up</button>
            </div>
        </form>
    </div>
</div>

<script>
function filterFollowups() {
    const priorityFilter = document.getElementById('priorityFilter').value;
    const timeFilter = document.getElementById('timeFilter').value;
    const followupItems = document.querySelectorAll('.followup-item');
    
    followupItems.forEach(item => {
        let showItem = true;
        
        // Priority filter
        if (priorityFilter && item.dataset.priority !== priorityFilter) {
            showItem = false;
        }
        
        // Time filter
        if (timeFilter && showItem) {
            const itemDate = item.dataset.time;
            const today = new Date().toISOString().split('T')[0];
            const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];
            
            switch (timeFilter) {
                case 'overdue':
                    showItem = itemDate < today;
                    break;
                case 'today':
                    showItem = itemDate === today;
                    break;
                case 'tomorrow':
                    showItem = itemDate === tomorrow;
                    break;
                case 'week':
                    const weekFromNow = new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0];
                    showItem = itemDate >= today && itemDate <= weekFromNow;
                    break;
            }
        }
        
        item.style.display = showItem ? 'block' : 'none';
    });
}

function callLead(phone, leadId) {
    window.location.href = `../dialer.php?number=${encodeURIComponent(phone)}&lead=${leadId}`;
}

function sendMessage(leadId) {
    window.location.href = `../messages.php?lead=${leadId}`;
}

function rescheduleFollowup(leadId) {
    const newDate = prompt('Enter new follow-up date (YYYY-MM-DD):');
    const newTime = prompt('Enter new follow-up time (HH:MM):');
    
    if (newDate && newTime) {
        fetch('../api/reschedule_followup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lead_id: leadId,
                new_date: newDate,
                new_time: newTime
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to reschedule follow-up');
            }
        });
    }
}

function markCompleted(leadId) {
    if (confirm('Mark this follow-up as completed?')) {
        fetch('../api/complete_followup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lead_id: leadId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to mark follow-up as completed');
            }
        });
    }
}

function scheduleNewFollowup() {
    document.getElementById('scheduleModal').style.display = 'block';
    loadLeadsForScheduling();
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
}

function loadLeadsForScheduling() {
    fetch('../api/get_my_leads.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('leadSelect');
            select.innerHTML = '<option value="">Choose a lead...</option>';
            
            data.leads.forEach(lead => {
                const option = document.createElement('option');
                option.value = lead.id;
                option.textContent = `${lead.name} - ${lead.phone}`;
                select.appendChild(option);
            });
        });
}

function saveFollowup(event) {
    event.preventDefault();
    
    const formData = {
        lead_id: document.getElementById('leadSelect').value,
        date: document.getElementById('followupDate').value,
        time: document.getElementById('followupTime').value,
        priority: document.getElementById('followupPriority').value,
        notes: document.getElementById('followupNotes').value
    };
    
    fetch('../api/schedule_followup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeScheduleModal();
            location.reload();
        } else {
            alert('Failed to schedule follow-up');
        }
    });
}

function viewCalendar() {
    window.location.href = '../schedule.php';
}

function editFollowup(leadId) {
    alert('Edit follow-up feature coming soon');
}

function viewLeadHistory(leadId) {
    window.location.href = `lead-history.php?id=${leadId}`;
}

function addNotes(leadId) {
    const notes = prompt('Add notes for this lead:');
    if (notes) {
        fetch('../api/add_lead_notes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lead_id: leadId,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to add notes');
            }
        });
    }
}

function cancelFollowup(leadId) {
    if (confirm('Are you sure you want to cancel this follow-up?')) {
        fetch('../api/cancel_followup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lead_id: leadId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to cancel follow-up');
            }
        });
    }
}

// Set default date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('followupDate').value = today;
    
    // Set default time to next hour
    const now = new Date();
    now.setHours(now.getHours() + 1);
    const timeString = now.toTimeString().slice(0, 5);
    document.getElementById('followupTime').value = timeString;
});
</script>

<?php include '../includes/footer.php'; ?>