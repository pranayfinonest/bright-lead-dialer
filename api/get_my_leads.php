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

try {
    $stmt = $db->prepare("
        SELECT id, name, phone, email, status, source
        FROM leads 
        WHERE assigned_to = ? 
        ORDER BY 
            CASE status 
                WHEN 'hot' THEN 1 
                WHEN 'warm' THEN 2 
                WHEN 'cold' THEN 3 
            END,
            created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'leads' => $leads
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error'
    ]);
}
?>