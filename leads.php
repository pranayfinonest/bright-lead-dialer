<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Lead Management - FINONEST TeleCRM';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_lead':
                $stmt = $db->prepare("
                    INSERT INTO leads (name, phone, email, status, source, assigned_to, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['phone'],
                    $_POST['email'],
                    $_POST['status'],
                    $_POST['source'],
                    $_SESSION['user_id']
                ]);
                $success_message = "Lead added successfully!";
                break;
        }
    }
}

// Get leads with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where_conditions = ["1=1"];
$params = [];

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
    SELECT l.*, u.name as agent_name 
    FROM leads l 
    LEFT JOIN users u ON l.assigned_to = u.id 
    WHERE $where_clause 
    ORDER BY l.created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total count for pagination
$count_stmt = $db->prepare("SELECT COUNT(*) as total FROM leads WHERE $where_clause");
$count_stmt->execute($params);
$total_leads = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_leads / $limit);

include 'includes/header.php';
?>

<div class="leads-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Lead Management</h1>
                <p>Manage and track your sales leads effectively</p>
            </div>
            <button class="btn btn-primary" onclick="showAddLeadModal()">
                <i class="icon-plus"></i>
                Add New Lead
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <?php
        $stats_stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_leads,
                SUM(CASE WHEN status = 'hot' THEN 1 ELSE 0 END) as hot_leads,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_leads,
                SUM(CASE WHEN next_followup = CURDATE() THEN 1 ELSE 0 END) as followups_due
            FROM leads
        ");
        $stats_stmt->execute();
        $lead_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Total Leads</span>
                    <span class="stat-value"><?php echo number_format($lead_stats['total_leads']); ?></span>
                </div>
                <div class="stat-icon primary">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Hot Leads</span>
                    <span class="stat-value"><?php echo $lead_stats['hot_leads']; ?></span>
                </div>
                <div class="stat-icon error">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Added Today</span>
                    <span class="stat-value"><?php echo $lead_stats['today_leads']; ?></span>
                </div>
                <div class="stat-icon success">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Follow-ups Due</span>
                    <span class="stat-value"><?php echo $lead_stats['followups_due']; ?></span>
                </div>
                <div class="stat-icon warning">
                    <i class="icon-calendar"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="leads-table-card">
        <div class="card-header">
            <h2>Lead Database</h2>
        </div>
        <div class="card-content">
            <div class="table-filters">
                <div class="search-input">
                    <i class="icon-search"></i>
                    <input type="text" placeholder="Search leads by name, phone, or email..." 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           onchange="filterLeads()" id="leadSearch">
                </div>
                <select id="statusFilter" onchange="filterLeads()">
                    <option value="">All Status</option>
                    <option value="hot" <?php echo $status_filter === 'hot' ? 'selected' : ''; ?>>Hot</option>
                    <option value="warm" <?php echo $status_filter === 'warm' ? 'selected' : ''; ?>>Warm</option>
                    <option value="cold" <?php echo $status_filter === 'cold' ? 'selected' : ''; ?>>Cold</option>
                </select>
                <button class="btn btn-outline">
                    <i class="icon-filter"></i>
                    Filters
                </button>
            </div>

            <!-- Leads Table -->
            <div class="table-container">
                <table class="leads-table">
                    <thead>
                        <tr>
                            <th>Lead Details</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Assigned To</th>
                            <th>Last Contact</th>
                            <th>Next Follow-up</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <div class="lead-details">
                                        <p class="lead-name"><?php echo htmlspecialchars($lead['name']); ?></p>
                                        <p class="lead-phone"><?php echo htmlspecialchars($lead['phone']); ?></p>
                                        <p class="lead-email"><?php echo htmlspecialchars($lead['email']); ?></p>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($lead['status']); ?>">
                                        <?php echo ucfirst($lead['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($lead['source']); ?></td>
                                <td><?php echo htmlspecialchars($lead['agent_name'] ?: 'Unassigned'); ?></td>
                                <td><?php echo $lead['last_contact'] ? date('M j, Y', strtotime($lead['last_contact'])) : 'Never'; ?></td>
                                <td><?php echo $lead['next_followup'] ? date('M j, Y', strtotime($lead['next_followup'])) : 'Not scheduled'; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-icon" onclick="callLead('<?php echo $lead['phone']; ?>')">
                                            <i class="icon-phone"></i>
                                        </button>
                                        <button class="btn-icon" onclick="emailLead('<?php echo $lead['email']; ?>')">
                                            <i class="icon-mail"></i>
                                        </button>
                                        <button class="btn-icon" onclick="messageLead('<?php echo $lead['phone']; ?>')">
                                            <i class="icon-message-square"></i>
                                        </button>
                                        <div class="dropdown">
                                            <button class="btn-icon dropdown-toggle">
                                                <i class="icon-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="#" onclick="editLead(<?php echo $lead['id']; ?>)">Edit Lead</a>
                                                <a href="#" onclick="viewHistory(<?php echo $lead['id']; ?>)">View History</a>
                                                <a href="#" onclick="scheduleCall(<?php echo $lead['id']; ?>)">Schedule Call</a>
                                                <a href="#" onclick="deleteLead(<?php echo $lead['id']; ?>)" class="text-error">Delete Lead</a>
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

<!-- Add Lead Modal -->
<div id="addLeadModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Lead</h3>
            <button class="modal-close" onclick="closeAddLeadModal()">&times;</button>
        </div>
        <form method="POST" class="modal-body">
            <input type="hidden" name="action" value="add_lead">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="cold">Cold</option>
                    <option value="warm">Warm</option>
                    <option value="hot">Hot</option>
                </select>
            </div>
            <div class="form-group">
                <label for="source">Source</label>
                <select id="source" name="source" required>
                    <option value="website">Website</option>
                    <option value="referral">Referral</option>
                    <option value="social_media">Social Media</option>
                    <option value="advertisement">Advertisement</option>
                    <option value="cold_call">Cold Call</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddLeadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Lead</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddLeadModal() {
    document.getElementById('addLeadModal').style.display = 'block';
}

function closeAddLeadModal() {
    document.getElementById('addLeadModal').style.display = 'none';
}

function filterLeads() {
    const search = document.getElementById('leadSearch').value;
    const status = document.getElementById('statusFilter').value;
    window.location.href = `leads.php?search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`;
}

function callLead(phone) {
    window.location.href = `dialer.php?number=${encodeURIComponent(phone)}`;
}

function emailLead(email) {
    window.location.href = `messages.php?type=email&to=${encodeURIComponent(email)}`;
}

function messageLead(phone) {
    window.location.href = `messages.php?type=sms&to=${encodeURIComponent(phone)}`;
}
</script>

<?php include 'includes/footer.php'; ?>