<?php
session_start();
require_once '../includes/auth.php';

header('Content-Type: application/json');

echo json_encode([
    'authenticated' => isLoggedIn(),
    'user_id' => $_SESSION['user_id'] ?? null
]);
?>