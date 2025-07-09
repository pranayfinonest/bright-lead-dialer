<div class="quick-actions-card">
    <div class="card-header">
        <div class="card-title">
            <div class="title-icon"></div>
            <span>Quick Actions</span>
        </div>
    </div>
    <div class="card-content">
        <div class="actions-grid">
            <?php
            $actions = [
                [
                    'icon' => 'phone',
                    'label' => 'Start Dialing',
                    'description' => 'Begin your calling session',
                    'variant' => 'call',
                    'href' => 'dialer.php'
                ],
                [
                    'icon' => 'user-plus',
                    'label' => 'Add Lead',
                    'description' => 'Add new prospect',
                    'variant' => 'default',
                    'href' => 'leads.php?action=add'
                ],
                [
                    'icon' => 'message-square',
                    'label' => 'Send SMS',
                    'description' => 'Quick message blast',
                    'variant' => 'secondary',
                    'href' => 'messages.php?type=sms'
                ],
                [
                    'icon' => 'target',
                    'label' => 'New Campaign',
                    'description' => 'Create calling campaign',
                    'variant' => 'outline',
                    'href' => 'campaigns.php?action=create'
                ],
                [
                    'icon' => 'calendar',
                    'label' => 'Schedule',
                    'description' => 'View today\'s calls',
                    'variant' => 'ghost',
                    'href' => 'schedule.php'
                ],
                [
                    'icon' => 'import',
                    'label' => 'Import Leads',
                    'description' => 'Upload CSV file',
                    'variant' => 'outline',
                    'href' => 'leads.php?action=import'
                ]
            ];
            ?>
            
            <?php foreach ($actions as $action): ?>
                <a href="<?php echo $action['href']; ?>" class="action-btn <?php echo $action['variant']; ?>">
                    <i class="icon-<?php echo $action['icon']; ?>"></i>
                    <div class="action-content">
                        <div class="action-label"><?php echo $action['label']; ?></div>
                        <div class="action-description"><?php echo $action['description']; ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>