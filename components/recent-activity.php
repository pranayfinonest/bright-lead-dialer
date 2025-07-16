<div class="recent-activity-card">
    <div class="card-header">
        <div class="card-title">
            <i class="icon-clock"></i>
            <span>Recent Activity</span>
        </div>
        <div class="live-badge">Live</div>
    </div>
    <div class="card-content">
        <div class="activity-list">
            <?php
            // Get recent activities from database
            $stmt = $db->prepare("
                SELECT 
                    a.*,
                    l.name as lead_name
                FROM activities a
                LEFT JOIN leads l ON a.lead_id = l.id
                WHERE a.user_id = ?
                ORDER BY a.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($activities)) {
                // Default activities for demo
                $activities = [
                    [
                        'type' => 'call',
                        'title' => 'Call with John Smith',
                        'description' => 'Discussed loan requirements - Follow up needed',
                        'time' => '2 min ago',
                        'status' => 'success'
                    ],
                    [
                        'type' => 'lead',
                        'title' => 'New lead added',
                        'description' => 'Sarah Johnson - Home loan inquiry',
                        'time' => '15 min ago',
                        'status' => 'pending'
                    ],
                    [
                        'type' => 'sms',
                        'title' => 'SMS sent to 25 contacts',
                        'description' => 'Monthly loan offer campaign',
                        'time' => '1 hour ago',
                        'status' => 'success'
                    ],
                    [
                        'type' => 'meeting',
                        'title' => 'Scheduled callback',
                        'description' => 'Mike Wilson - 3:00 PM today',
                        'time' => '2 hours ago',
                        'status' => 'pending'
                    ],
                    [
                        'type' => 'call',
                        'title' => 'Call attempt failed',
                        'description' => 'Lisa Brown - Number not reachable',
                        'time' => '3 hours ago',
                        'status' => 'failed'
                    ]
                ];
            }
            
            $activityIcons = [
                'call' => 'phone',
                'sms' => 'message-square',
                'lead' => 'user-check',
                'meeting' => 'calendar'
            ];
            
            $statusColors = [
                'success' => 'success',
                'failed' => 'error',
                'pending' => 'warning'
            ];
            ?>
            
            <?php foreach ($activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="icon-<?php echo $activityIcons[$activity['type']] ?? 'circle'; ?>"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-header">
                            <h4 class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></h4>
                            <?php if (isset($activity['status'])): ?>
                                <span class="activity-status <?php echo $statusColors[$activity['status']] ?? ''; ?>">
                                    <?php echo ucfirst($activity['status']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></p>
                        <p class="activity-time"><?php echo $activity['time']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>