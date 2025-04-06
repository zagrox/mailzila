<?php
require_once '../../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];

// Update all notifications to read
$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$result = $db->query($sql, [$userId]);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update notifications']);
}
?> 