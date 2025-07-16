<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Message Center - FINONEST TeleCRM';

// Get message templates
$stmt = $db->prepare("
    SELECT * FROM message_templates 
    WHERE user_id = ? OR user_id IS NULL 
    ORDER BY type, usage_count DESC
");
$stmt->execute([$_SESSION['user_id']]);
$templates = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group templates by type
$whatsapp_templates = array_filter($templates, fn($t) => $t['type'] === 'whatsapp');
$sms_templates = array_filter($templates, fn($t) => $t['type'] === 'sms');
$email_templates = array_filter($templates, fn($t) => $t['type'] === 'email');

// Get message stats
$stats_stmt = $db->prepare("
    SELECT 
        COUNT(CASE WHEN type = 'whatsapp' THEN 1 END) as whatsapp_sent,
        COUNT(CASE WHEN type = 'sms' THEN 1 END) as sms_sent,
        COUNT(CASE WHEN type = 'email' THEN 1 END) as emails_sent,
        AVG(CASE WHEN response_received = 1 THEN 1 ELSE 0 END) * 100 as response_rate
    FROM messages 
    WHERE user_id = ? AND DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$stats_stmt->execute([$_SESSION['user_id']]);
$message_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="messages-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Message Center</h1>
                <p>Manage WhatsApp, SMS, and Email templates</p>
            </div>
            <button class="btn btn-primary" onclick="showCreateTemplateModal()">
                <i class="icon-plus"></i>
                Create Template
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">WhatsApp Sent</span>
                    <span class="stat-value"><?php echo number_format($message_stats['whatsapp_sent'] ?: 0); ?></span>
                </div>
                <div class="stat-icon success">
                    <i class="icon-message-square"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">SMS Sent</span>
                    <span class="stat-value"><?php echo number_format($message_stats['sms_sent'] ?: 0); ?></span>
                </div>
                <div class="stat-icon primary">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Emails Sent</span>
                    <span class="stat-value"><?php echo number_format($message_stats['emails_sent'] ?: 0); ?></span>
                </div>
                <div class="stat-icon secondary">
                    <i class="icon-mail"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Response Rate</span>
                    <span class="stat-value"><?php echo number_format($message_stats['response_rate'] ?: 0, 1); ?>%</span>
                </div>
                <div class="stat-icon warning">
                    <i class="icon-message-square"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates -->
    <div class="templates-card">
        <div class="card-header">
            <h3>Message Templates</h3>
        </div>
        <div class="card-content">
            <div class="template-tabs">
                <button class="tab-btn active" onclick="showTab('whatsapp')">WhatsApp</button>
                <button class="tab-btn" onclick="showTab('sms')">SMS</button>
                <button class="tab-btn" onclick="showTab('email')">Email</button>
            </div>

            <!-- Search -->
            <div class="search-section">
                <div class="search-input">
                    <i class="icon-search"></i>
                    <input type="text" placeholder="Search templates..." id="templateSearch">
                </div>
            </div>

            <!-- WhatsApp Templates -->
            <div id="whatsapp-tab" class="tab-content active">
                <div class="templates-list">
                    <?php foreach ($whatsapp_templates as $template): ?>
                        <div class="template-item">
                            <div class="template-content">
                                <div class="template-header">
                                    <h4><?php echo htmlspecialchars($template['name']); ?></h4>
                                    <div class="template-meta">
                                        <span class="badge outline"><?php echo ucfirst($template['category']); ?></span>
                                        <span class="usage-count">Used <?php echo $template['usage_count']; ?> times</span>
                                    </div>
                                </div>
                                <p class="template-message"><?php echo htmlspecialchars($template['message']); ?></p>
                            </div>
                            <div class="template-actions">
                                <button class="btn btn-outline btn-sm" onclick="editTemplate(<?php echo $template['id']; ?>)">Edit</button>
                                <button class="btn btn-primary btn-sm" onclick="useTemplate(<?php echo $template['id']; ?>)">Use Template</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SMS Templates -->
            <div id="sms-tab" class="tab-content">
                <div class="templates-list">
                    <?php foreach ($sms_templates as $template): ?>
                        <div class="template-item">
                            <div class="template-content">
                                <div class="template-header">
                                    <h4><?php echo htmlspecialchars($template['name']); ?></h4>
                                    <div class="template-meta">
                                        <span class="badge outline"><?php echo ucfirst($template['category']); ?></span>
                                        <span class="usage-count">Used <?php echo $template['usage_count']; ?> times</span>
                                    </div>
                                </div>
                                <p class="template-message"><?php echo htmlspecialchars($template['message']); ?></p>
                            </div>
                            <div class="template-actions">
                                <button class="btn btn-outline btn-sm" onclick="editTemplate(<?php echo $template['id']; ?>)">Edit</button>
                                <button class="btn btn-primary btn-sm" onclick="useTemplate(<?php echo $template['id']; ?>)">Use Template</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Email Templates -->
            <div id="email-tab" class="tab-content">
                <div class="templates-list">
                    <?php foreach ($email_templates as $template): ?>
                        <div class="template-item">
                            <div class="template-content">
                                <div class="template-header">
                                    <h4><?php echo htmlspecialchars($template['name']); ?></h4>
                                    <div class="template-meta">
                                        <span class="badge outline"><?php echo ucfirst($template['category']); ?></span>
                                        <span class="usage-count">Used <?php echo $template['usage_count']; ?> times</span>
                                    </div>
                                </div>
                                <p class="template-subject"><strong>Subject:</strong> <?php echo htmlspecialchars($template['subject']); ?></p>
                                <p class="template-message"><?php echo htmlspecialchars($template['message']); ?></p>
                            </div>
                            <div class="template-actions">
                                <button class="btn btn-outline btn-sm" onclick="editTemplate(<?php echo $template['id']; ?>)">Edit</button>
                                <button class="btn btn-primary btn-sm" onclick="useTemplate(<?php echo $template['id']; ?>)">Use Template</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Send -->
    <div class="quick-send-card">
        <div class="card-header">
            <h3>Quick Send Message</h3>
        </div>
        <div class="card-content">
            <form id="quickSendForm" onsubmit="sendQuickMessage(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipient">To (Phone/Email)</label>
                        <input type="text" id="recipient" name="recipient" placeholder="+91 98765 43210" required>
                    </div>
                    <div class="form-group">
                        <label for="messageType">Message Type</label>
                        <select id="messageType" name="messageType" required>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="sms">SMS</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="quickMessage">Message</label>
                    <textarea id="quickMessage" name="message" placeholder="Type your message here..." rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-send"></i>
                    Send Message
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
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

function showCreateTemplateModal() {
    alert('Create template modal would open here');
}

function editTemplate(id) {
    window.location.href = `edit-template.php?id=${id}`;
}

function useTemplate(id) {
    // Load template into quick send form
    fetch(`api/get_template.php?id=${id}`)
        .then(response => response.json())
        .then(template => {
            document.getElementById('messageType').value = template.type;
            document.getElementById('quickMessage').value = template.message;
        });
}

function sendQuickMessage(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData);
    
    fetch('api/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Message sent successfully!');
            event.target.reset();
        } else {
            alert('Failed to send message: ' + result.error);
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>