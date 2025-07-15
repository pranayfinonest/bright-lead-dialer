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

$leadId = $_GET['id'] ?? null;

if (!$leadId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Lead ID required']);
    exit;
}

try {
    // Check if user has access to this lead
    $stmt = $db->prepare("SELECT notes FROM leads WHERE id = ? AND assigned_to = ?");
    $stmt->execute([$leadId, $_SESSION['user_id']]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lead) {
        echo json_encode([
            'success' => true,
            'notes' => $lead['notes']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Lead not found or access denied']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>