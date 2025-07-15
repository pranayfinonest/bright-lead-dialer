<?php
session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$templateId = $_GET['id'] ?? null;

if (!$templateId) {
    http_response_code(400);
    echo json_encode(['error' => 'Template ID required']);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT id, name, type, category, subject, message, variables
        FROM message_templates 
        WHERE id = ? AND (user_id = ? OR user_id IS NULL)
    ");
    $stmt->execute([$templateId, $_SESSION['user_id']]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($template) {
        // Update usage count
        $updateStmt = $db->prepare("UPDATE message_templates SET usage_count = usage_count + 1 WHERE id = ?");
        $updateStmt->execute([$templateId]);
        
        echo json_encode($template);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Template not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>