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
        SELECT id, title, message, type, action_url, created_at, read_at
        FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format notifications for display
    $formattedNotifications = array_map(function($notification) {
        return [
            'id' => $notification['id'],
            'title' => $notification['title'],
            'description' => $notification['message'],
            'type' => $notification['type'],
            'action_url' => $notification['action_url'],
            'time' => timeAgo($notification['created_at']),
            'read' => !is_null($notification['read_at'])
        ];
    }, $notifications);
    
    echo json_encode($formattedNotifications);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' min ago';
    if ($time < 86400) return floor($time/3600) . ' hour ago';
    if ($time < 2592000) return floor($time/86400) . ' day ago';
    if ($time < 31536000) return floor($time/2592000) . ' month ago';
    return floor($time/31536000) . ' year ago';
}
?>