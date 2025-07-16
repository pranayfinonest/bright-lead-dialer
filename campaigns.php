<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

requireLogin();

$pageTitle = 'Campaign Manager - FINONEST TeleCRM';

// Get campaigns
$stmt = $db->prepare("
    SELECT c.*, 
           COUNT(cl.id) as total_leads,
           COUNT(CASE WHEN cl.status = 'called' THEN 1 END) as called_leads,
           COUNT(CASE WHEN cl.status = 'converted' THEN 1 END) as converted_leads
    FROM campaigns c
    LEFT JOIN campaign_leads cl ON c.id = cl.campaign_id
    WHERE c.user_id = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get campaign stats
$stats_stmt = $db->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_campaigns,
        SUM(total_leads) as total_leads,
        SUM(called_leads) as calls_made,
        AVG(CASE WHEN total_leads > 0 THEN (converted_leads * 100.0 / total_leads) ELSE 0 END) as avg_conversion
    FROM (
        SELECT c.status,
               COUNT(cl.id) as total_leads,
               COUNT(CASE WHEN cl.status = 'called' THEN 1 END) as called_leads,
               COUNT(CASE WHEN cl.status = 'converted' THEN 1 END) as converted_leads
        FROM campaigns c
        LEFT JOIN campaign_leads cl ON c.id = cl.campaign_id
        WHERE c.user_id = ?
        GROUP BY c.id
    ) campaign_stats
");
$stats_stmt->execute([$_SESSION['user_id']]);
$campaign_stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="campaigns-container">
    <!-- Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1>Campaign Manager</h1>
                <p>Create and manage your telecalling campaigns</p>
            </div>
            <button class="btn btn-primary" onclick="showCreateCampaignModal()">
                <i class="icon-plus"></i>
                New Campaign
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Active Campaigns</span>
                    <span class="stat-value"><?php echo $campaign_stats['active_campaigns'] ?: 0; ?></span>
                </div>
                <div class="stat-icon primary">
                    <i class="icon-target"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Total Leads</span>
                    <span class="stat-value"><?php echo number_format($campaign_stats['total_leads'] ?: 0); ?></span>
                </div>
                <div class="stat-icon secondary">
                    <i class="icon-users"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Calls Made</span>
                    <span class="stat-value"><?php echo number_format($campaign_stats['calls_made'] ?: 0); ?></span>
                </div>
                <div class="stat-icon success">
                    <i class="icon-phone"></i>
                </div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-info">
                    <span class="stat-title">Avg. Conversion</span>
                    <span class="stat-value"><?php echo number_format($campaign_stats['avg_conversion'] ?: 0, 1); ?>%</span>
                </div>
                <div class="stat-icon warning">
                    <i class="icon-trending-up"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns List -->
    <div class="campaigns-list">
        <?php foreach ($campaigns as $campaign): ?>
            <?php
            $progress = $campaign['total_leads'] > 0 ? 
                round(($campaign['called_leads'] / $campaign['total_leads']) * 100) : 0;
            $conversion_rate = $campaign['called_leads'] > 0 ? 
                round(($campaign['converted_leads'] / $campaign['called_leads']) * 100, 1) : 0;
            ?>
            <div class="campaign-card">
                <div class="campaign-header">
                    <div class="campaign-info">
                        <h3><?php echo htmlspecialchars($campaign['name']); ?></h3>
                        <div class="campaign-badges">
                            <span class="badge <?php echo strtolower($campaign['type']); ?>">
                                <?php echo ucfirst($campaign['type']); ?>
                            </span>
                            <span class="badge <?php echo strtolower($campaign['status']); ?>">
                                <?php echo ucfirst($campaign['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="campaign-actions">
                        <?php if ($campaign['status'] === 'active'): ?>
                            <button class="btn btn-outline btn-sm" onclick="pauseCampaign(<?php echo $campaign['id']; ?>)">
                                <i class="icon-pause"></i>
                                Pause
                            </button>
                        <?php elseif ($campaign['status'] === 'paused'): ?>
                            <button class="btn btn-primary btn-sm" onclick="resumeCampaign(<?php echo $campaign['id']; ?>)">
                                <i class="icon-play"></i>
                                Resume
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline btn-sm" onclick="viewCampaign(<?php echo $campaign['id']; ?>)">
                            View Details
                        </button>
                    </div>
                </div>
                
                <div class="campaign-content">
                    <div class="campaign-stats">
                        <!-- Progress -->
                        <div class="stat-section">
                            <div class="stat-header">
                                <span>Progress</span>
                                <span><?php echo $progress; ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <p class="stat-desc">
                                <?php echo $campaign['called_leads']; ?> of <?php echo $campaign['total_leads']; ?> leads called
                            </p>
                        </div>

                        <!-- Conversions -->
                        <div class="stat-section">
                            <p class="stat-label">Conversions</p>
                            <p class="stat-value success"><?php echo $campaign['converted_leads']; ?></p>
                            <p class="stat-desc"><?php echo $conversion_rate; ?>% conversion rate</p>
                        </div>

                        <!-- Timeline -->
                        <div class="stat-section">
                            <p class="stat-label">Timeline</p>
                            <p class="stat-desc">
                                Start: <?php echo date('M j, Y', strtotime($campaign['start_date'])); ?>
                            </p>
                            <p class="stat-desc">
                                End: <?php echo date('M j, Y', strtotime($campaign['end_date'])); ?>
                            </p>
                        </div>

                        <!-- Quick Actions -->
                        <div class="stat-section">
                            <p class="stat-label">Quick Actions</p>
                            <div class="quick-actions">
                                <button class="btn btn-outline btn-sm full-width">View Reports</button>
                                <button class="btn btn-outline btn-sm full-width">Export Data</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Quick Campaign Templates -->
    <div class="templates-card">
        <div class="card-header">
            <h3>Quick Campaign Templates</h3>
        </div>
        <div class="card-content">
            <div class="templates-grid">
                <div class="template-card" onclick="createFromTemplate('cold_calling')">
                    <div class="template-icon primary">
                        <i class="icon-phone"></i>
                    </div>
                    <h4>Cold Calling</h4>
                    <p>Start calling new leads from your database</p>
                </div>
                
                <div class="template-card" onclick="createFromTemplate('followup')">
                    <div class="template-icon success">
                        <i class="icon-target"></i>
                    </div>
                    <h4>Follow-up Campaign</h4>
                    <p>Re-engage with previous contacts</p>
                </div>
                
                <div class="template-card" onclick="createFromTemplate('warm_leads')">
                    <div class="template-icon warning">
                        <i class="icon-trending-up"></i>
                    </div>
                    <h4>Warm Leads</h4>
                    <p>Call interested prospects</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showCreateCampaignModal() {
    // Implementation for creating new campaign
    alert('Create campaign modal would open here');
}

function pauseCampaign(id) {
    if (confirm('Are you sure you want to pause this campaign?')) {
        // AJAX call to pause campaign
        fetch('api/campaign_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'pause',
                campaign_id: id
            })
        }).then(() => {
            location.reload();
        });
    }
}

function resumeCampaign(id) {
    // AJAX call to resume campaign
    fetch('api/campaign_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'resume',
            campaign_id: id
        })
    }).then(() => {
        location.reload();
    });
}

function viewCampaign(id) {
    window.location.href = `campaign-details.php?id=${id}`;
}

function createFromTemplate(template) {
    window.location.href = `create-campaign.php?template=${template}`;
}
</script>

<?php include 'includes/footer.php'; ?>