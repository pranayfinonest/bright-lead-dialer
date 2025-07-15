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

if (!$input || empty($input['lead_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

try {
    // Update lead notes (only if user has access)
    $stmt = $db->prepare("
        UPDATE leads 
        SET notes = ?, updated_at = NOW() 
        WHERE id = ? AND assigned_to = ?
    ");
    
    $result = $stmt->execute([
        $input['notes'] ?? '',
        $input['lead_id'],
        $_SESSION['user_id']
    ]);
    
    if ($stmt->rowCount() > 0) {
        // Log activity
        $activityStmt = $db->prepare("
            INSERT INTO activities (user_id, lead_id, type, title, description, created_at) 
            VALUES (?, ?, 'note', 'Notes updated', 'Lead notes were updated', NOW())
        ");
        $activityStmt->execute([$_SESSION['user_id'], $input['lead_id']]);
        
        echo json_encode(['success' => true, 'message' => 'Notes saved successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Lead not found or access denied']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>