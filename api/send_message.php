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
    if (empty($input['recipient']) || empty($input['message']) || empty($input['messageType'])) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Insert message record
    $stmt = $db->prepare("
        INSERT INTO messages (user_id, type, recipient, subject, message, status, created_at) 
        VALUES (?, ?, ?, ?, ?, 'sent', NOW())
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $input['messageType'],
        $input['recipient'],
        $input['subject'] ?? null,
        $input['message']
    ]);
    
    $messageId = $db->lastInsertId();
    
    // Log activity
    $activityStmt = $db->prepare("
        INSERT INTO activities (user_id, type, title, description, status, created_at) 
        VALUES (?, ?, ?, ?, 'success', NOW())
    ");
    
    $activityTitle = ucfirst($input['messageType']) . " sent";
    $activityDescription = "Message sent to " . $input['recipient'];
    
    $activityStmt->execute([
        $_SESSION['user_id'],
        $input['messageType'],
        $activityTitle,
        $activityDescription
    ]);
    
    // Here you would integrate with actual messaging services
    // For now, we'll simulate successful sending
    
    echo json_encode([
        'success' => true,
        'message_id' => $messageId,
        'message' => 'Message sent successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>