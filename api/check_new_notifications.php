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
    // Check for new notifications (unread)
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM notifications 
        WHERE user_id = ? AND read_at IS NULL
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $unreadCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get latest notification
    $stmt = $db->prepare("
        SELECT title, message, type, created_at
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'hasNew' => $unreadCount > 0,
        'count' => $unreadCount,
        'latest' => $latest ? [
            'title' => $latest['title'],
            'description' => $latest['message'],
            'type' => $latest['type']
        ] : null
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>