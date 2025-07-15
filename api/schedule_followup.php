<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

try {
    // Validate required fields
    if (empty($input['lead_id']) || empty($input['date']) || empty($input['time'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Combine date and time
    $scheduledAt = $input['date'] . ' ' . $input['time'] . ':00';
    
    // Insert follow-up schedule
    $stmt = $db->prepare("
        INSERT INTO schedule (user_id, lead_id, title, type, purpose, scheduled_at, priority, notes, created_at) 
        VALUES (?, ?, ?, 'followup', ?, ?, ?, ?, NOW())
    ");
    
    $title = "Follow-up call";
    $purpose = "Scheduled follow-up call";
    $priority = $input['priority'] ?? 'medium';
    $notes = $input['notes'] ?? '';
    
    $stmt->execute([
        $_SESSION['user_id'],
        $input['lead_id'],
        $title,
        $purpose,
        $scheduledAt,
        $priority,
        $notes
    ]);
    
    // Update lead's next_followup field
    $updateStmt = $db->prepare("UPDATE leads SET next_followup = ? WHERE id = ?");
    $updateStmt->execute([$input['date'], $input['lead_id']]);
    
    // Log activity
    $activityStmt = $db->prepare("
        INSERT INTO activities (user_id, lead_id, type, title, description, created_at) 
        VALUES (?, ?, 'meeting', ?, ?, NOW())
    ");
    
    $activityTitle = "Follow-up scheduled";
    $activityDescription = "Follow-up call scheduled for " . date('M j, Y g:i A', strtotime($scheduledAt));
    
    $activityStmt->execute([
        $_SESSION['user_id'],
        $input['lead_id'],
        $activityTitle,
        $activityDescription
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Follow-up scheduled successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>