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
    // Insert call record
    $stmt = $db->prepare("
        INSERT INTO calls (user_id, lead_id, phone_number, disposition, duration, notes, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $input['lead_id'] ?? null,
        $input['phone_number'],
        $input['disposition'],
        $input['duration'] ?? 0,
        $input['notes'] ?? ''
    ]);
    
    $call_id = $db->lastInsertId();
    
    // Update lead's last contact time if lead_id is provided
    if (!empty($input['lead_id'])) {
        $update_stmt = $db->prepare("UPDATE leads SET last_contact = NOW() WHERE id = ?");
        $update_stmt->execute([$input['lead_id']]);
    }
    
    // Log activity
    $activity_stmt = $db->prepare("
        INSERT INTO activities (user_id, lead_id, type, title, description, status, created_at) 
        VALUES (?, ?, 'call', ?, ?, ?, NOW())
    ");
    
    $activity_title = "Call to " . ($input['phone_number'] ?? 'Unknown');
    $activity_description = "Call disposition: " . ucfirst(str_replace('_', ' ', $input['disposition']));
    $activity_status = in_array($input['disposition'], ['connected', 'converted']) ? 'success' : 'failed';
    
    $activity_stmt->execute([
        $_SESSION['user_id'],
        $input['lead_id'] ?? null,
        $activity_title,
        $activity_description,
        $activity_status
    ]);
    
    echo json_encode([
        'success' => true, 
        'call_id' => $call_id,
        'message' => 'Call record saved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>