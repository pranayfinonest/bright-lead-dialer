<?php
session_start();
require_once '../config/database.php';
require_once '../config/auth.php';
require_once '../config/permissions.php';

requirePermission('leads.read.assigned');

$pageTitle = 'My Leads - FINONEST TeleCRM';

// Get caller's assigned leads
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where_conditions = ["assigned_to = ?"];
$params = [$_SESSION['user_id']];

if ($search) {
    $where_conditions[] = "(name LIKE ? OR phone LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status_filter) {
    $where_conditions[] = "status = ?";
    $params[] = $status_filter;
}

$where_clause = implode(" AND ", $where_conditions);

$stmt = $db->prepare("
    SELECT l.*, 
           COALESCE(c.last_call_time, 'Never') as last_contact,
           COALESCE(c.call_count, 0) as call_attempts,
           COALESCE(c.last_disposition, 'none') as last_disposition
    FROM leads l
    LEFT JOIN (
        SELECT lead_id, 
               MAX(created_at) as last_call_time,
               COUNT(*) as call_count,
               (SELECT disposition FROM calls WHERE lead_id = l.id ORDER BY created_at DESC LIMIT 1) as last_disposition
        FROM calls 
        GROUP BY lead_id
    ) c ON l.id = c.lead_id
    WHERE $where_clause 
    ORDER BY 
        CASE l.status 
            WHEN 'hot' THEN 1 
            WHEN 'warm' THEN 2 
            WHEN 'cold' THEN 3 
        END,
        l.created_at DESC
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count
$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM leads WHERE $where_clause");
$count_stmt->execute($params);
$total_leads = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_leads / $limit);

// Get lead statistics
$stats_stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_leads,
        COUNT(CASE WHEN status = 'hot' THEN 1 END) as hot_leads,
        COUNT(CASE WHEN status = 'warm' THEN 1 END) as warm_leads,
        COUNT(CASE WHEN status = 'cold' THEN 1 END) as cold_leads,
        COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted_leads,
        COUNT(CASE WHEN next_followup = CURDATE() THEN 1 END) as followups_due
    FROM leads 
    WHERE assigned_to = ?
");
$stats_stmt->execute([$_SESSION['user_id']]);
$lead_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<div class="my-leads-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>My Leads</h1>
                <p>Manage your assigned leads and track progress</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-success" onclick="window.location.href='../dialer.php'">
                    <i class="icon-phone"></i>
                    Start Calling
                </button>
                <button class="btn btn-outline" onclick="exportMyLeads()">
                    <i class="icon-download"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Lead Statistics -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Total Leads</span>
                    <span class="stat-value"><?php echo $lead_stats['total_leads']; ?></span>
                    <span class="stat-desc">assigned to you</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-users"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card error">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Hot Leads</span>
                    <span class="stat-value"><?php echo $lead_stats['hot_leads']; ?></span>
                    <span class="stat-desc">high priority</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-trending-up"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Warm Leads</span>
                    <span class="stat-value"><?php echo $lead_stats['warm_leads']; ?></span>
                    <span class="stat-desc">medium priority</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-target"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card success">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Converted</span>
                    <span class="stat-value"><?php echo $lead_stats['converted_leads']; ?></span>
                    <span class="stat-desc">successful conversions</span>
                </div>
                <div class="stat-icon">
                    <i class="icon-check-circle"></i>
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
            <div class="action-card primary" onclick="callNextLead()">
                <div class="action-icon">
                    <i class="icon-phone"></i>
                </div>
                <h4>Call Next Lead</h4>
                <p>Start with highest priority</p>
            </div>
            
            <div class="action-card success" onclick="showFollowUps()">
                <div class="action-icon">
                    <i class="icon-clock"></i>
                </div>
                <h4>Follow-ups Due</h4>
                <p><?php echo $lead_stats['followups_due']; ?> leads need follow-up</p>
            </div>
            
            <div class="action-card warning" onclick="sendBulkMessage()">
                <div class="action-icon">
                    <i class="icon-message-square"></i>
                </div>
                <h4>Send Messages</h4>
                <p>Bulk WhatsApp/SMS</p>
            </div>
            
            <div class="action-card secondary" onclick="updateLeadStatus()">
                <div class="action-icon">
                    <i class="icon-edit"></i>
                </div>
                <h4>Update Status</h4>
                <p>Bulk status update</p>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="leads-table-card">
        <div class="card-header">
            <h3>My Lead Database</h3>
        </div>
        <div class="card-content">
            <!-- Filters -->
            <div class="table-filters">
                <div class="search-input">
                    <i class="icon-search"></i>
                    <input type="text" placeholder="Search my leads..." 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           onchange="filterLeads()" id="leadSearch">
                </div>
                <select id="statusFilter" onchange="filterLeads()">
                    <option value="">All Status</option>
                    <option value="hot" <?php echo $status_filter === 'hot' ? 'selected' : ''; ?>>Hot</option>
                    <option value="warm" <?php echo $status_filter === 'warm' ? 'selected' : ''; ?>>Warm</option>
                    <option value="cold" <?php echo $status_filter === 'cold' ? 'selected' : ''; ?>>Cold</option>
                    <option value="converted" <?php echo $status_filter === 'converted' ? 'selected' : ''; ?>>Converted</option>
                </select>
                <button class="btn btn-outline" onclick="showAdvancedFilters()">
                    <i class="icon-filter"></i>
                    Advanced
                </button>
            </div>

            <div class="table-container">
                <table class="leads-table">
                    <thead>
                        <tr>
                            <th>Lead Details</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Last Contact</th>
                            <th>Call Attempts</th>
                            <th>Next Follow-up</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr class="lead-row" data-lead-id="<?php echo $lead['id']; ?>">
                                <td>
                                    <div class="lead-details">
                                        <div class="lead-header">
                                            <p class="lead-name"><?php echo htmlspecialchars($lead['name']); ?></p>
                                            <span class="priority-indicator <?php echo strtolower($lead['status']); ?>"></span>
                                        </div>
                                        <p class="lead-phone">
                                            <i class="icon-phone"></i>
                                            <?php echo htmlspecialchars($lead['phone']); ?>
                                        </p>
                                        <?php if ($lead['email']): ?>
                                            <p class="lead-email">
                                                <i class="icon-mail"></i>
                                                <?php echo htmlspecialchars($lead['email']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($lead['status']); ?>">
                                        <?php echo ucfirst($lead['status']); ?>
                                    </span>
                                    <?php if ($lead['last_disposition'] !== 'none'): ?>
                                        <div class="last-disposition">
                                            <small><?php echo ucfirst(str_replace('_', ' ', $lead['last_disposition'])); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="source-badge">
                                        <?php echo htmlspecialchars($lead['source'] ?: 'Unknown'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($lead['last_contact'] !== 'Never'): ?>
                                        <div class="contact-info">
                                            <span class="contact-date"><?php echo date('M j, Y', strtotime($lead['last_contact'])); ?></span>
                                            <span class="contact-time"><?php echo date('g:i A', strtotime($lead['last_contact'])); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-contact">Never contacted</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="call-attempts">
                                        <span class="attempts-count"><?php echo $lead['call_attempts']; ?></span>
                                        <span class="attempts-label">attempts</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($lead['next_followup']): ?>
                                        <div class="followup-info">
                                            <span class="followup-date <?php echo (strtotime($lead['next_followup']) < time()) ? 'overdue' : ''; ?>">
                                                <?php echo date('M j, Y', strtotime($lead['next_followup'])); ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <button class="btn btn-outline btn-sm" onclick="scheduleFollowup(<?php echo $lead['id']; ?>)">
                                            Schedule
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary btn-sm" onclick="callLead('<?php echo $lead['phone']; ?>', <?php echo $lead['id']; ?>)">
                                            <i class="icon-phone"></i>
                                            Call
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn-icon dropdown-toggle">
                                                <i class="icon-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="#" onclick="sendWhatsApp(<?php echo $lead['id']; ?>)">
                                                    <i class="icon-message-square"></i>
                                                    WhatsApp
                                                </a>
                                                <a href="#" onclick="sendSMS(<?php echo $lead['id']; ?>)">
                                                    <i class="icon-phone"></i>
                                                    SMS
                                                </a>
                                                <a href="#" onclick="sendEmail(<?php echo $lead['id']; ?>)">
                                                    <i class="icon-mail"></i>
                                                    Email
                                                </a>
                                                <hr>
                                                <a href="#" onclick="editLead(<?php echo $lead['id']; ?>)">
                                                    <i class="icon-edit"></i>
                                                    Edit Lead
                                                </a>
                                                <a href="#" onclick="viewHistory(<?php echo $lead['id']; ?>)">
                                                    <i class="icon-clock"></i>
                                                    View History
                                                </a>
                                                <a href="#" onclick="addNotes(<?php echo $lead['id']; ?>)">
                                                    <i class="icon-file-text"></i>
                                                    Add Notes
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>" 
                           class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Lead Notes Modal -->
<div id="notesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Lead Notes</h3>
            <button class="modal-close" onclick="closeNotesModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="leadNotes">Notes</label>
                <textarea id="leadNotes" rows="5" placeholder="Add your notes here..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeNotesModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveNotes()">Save Notes</button>
        </div>
    </div>
</div>

<script>
function filterLeads() {
    const search = document.getElementById('leadSearch').value;
    const status = document.getElementById('statusFilter').value;
    window.location.href = `my-leads.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`;
}

function callLead(phone, leadId) {
    window.location.href = `../dialer.php?number=${encodeURIComponent(phone)}&lead=${leadId}`;
}

function callNextLead() {
    // Find the first hot lead, then warm, then cold
    const hotLeads = document.querySelectorAll('.status-badge.hot');
    if (hotLeads.length > 0) {
        const leadRow = hotLeads[0].closest('.lead-row');
        const phone = leadRow.querySelector('.lead-phone').textContent.trim();
        const leadId = leadRow.dataset.leadId;
        callLead(phone, leadId);
        return;
    }
    
    // If no hot leads, try warm
    const warmLeads = document.querySelectorAll('.status-badge.warm');
    if (warmLeads.length > 0) {
        const leadRow = warmLeads[0].closest('.lead-row');
        const phone = leadRow.querySelector('.lead-phone').textContent.trim();
        const leadId = leadRow.dataset.leadId;
        callLead(phone, leadId);
        return;
    }
    
    // Otherwise, call first available lead
    const firstLead = document.querySelector('.lead-row');
    if (firstLead) {
        const phone = firstLead.querySelector('.lead-phone').textContent.trim();
        const leadId = firstLead.dataset.leadId;
        callLead(phone, leadId);
    } else {
        alert('No leads available to call');
    }
}

function sendWhatsApp(leadId) {
    window.location.href = `../messages.php?type=whatsapp&lead=${leadId}`;
}

function sendSMS(leadId) {
    window.location.href = `../messages.php?type=sms&lead=${leadId}`;
}

function sendEmail(leadId) {
    window.location.href = `../messages.php?type=email&lead=${leadId}`;
}

function editLead(leadId) {
    window.location.href = `edit-lead.php?id=${leadId}`;
}

function viewHistory(leadId) {
    window.location.href = `lead-history.php?id=${leadId}`;
}

function addNotes(leadId) {
    document.getElementById('notesModal').style.display = 'block';
    document.getElementById('notesModal').dataset.leadId = leadId;
    
    // Load existing notes
    fetch(`../api/get_lead_notes.php?id=${leadId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('leadNotes').value = data.notes || '';
            }
        });
}

function closeNotesModal() {
    document.getElementById('notesModal').style.display = 'none';
}

function saveNotes() {
    const modal = document.getElementById('notesModal');
    const leadId = modal.dataset.leadId;
    const notes = document.getElementById('leadNotes').value;
    
    fetch('../api/save_lead_notes.php', {
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
            closeNotesModal();
            showToast('Notes saved successfully');
        } else {
            alert('Failed to save notes');
        }
    });
}

function scheduleFollowup(leadId) {
    const date = prompt('Enter follow-up date (YYYY-MM-DD):');
    if (date) {
        fetch('../api/schedule_followup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                lead_id: leadId,
                followup_date: date
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to schedule follow-up');
            }
        });
    }
}

function showFollowUps() {
    window.location.href = 'follow-ups.php';
}

function sendBulkMessage() {
    window.location.href = '../messages.php?action=bulk';
}

function updateLeadStatus() {
    alert('Bulk status update feature coming soon');
}

function exportMyLeads() {
    window.location.href = '../api/export_my_leads.php';
}

function showAdvancedFilters() {
    alert('Advanced filters feature coming soon');
}

function showToast(message) {
    if (window.TeleCRM && window.TeleCRM.showToast) {
        window.TeleCRM.showToast(message);
    } else {
        alert(message);
    }
}
</script>

<?php include '../includes/footer.php'; ?>