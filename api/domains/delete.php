<?php
require_once __DIR__ . '/../../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Verify CSRF token
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !verify_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Check if domain is provided
if (!isset($data['domain'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Domain parameter is required']);
    exit;
}

$domainName = $data['domain'];

// Check if user owns this domain
try {
    $sql = "SELECT * FROM domains WHERE domain_name = ? AND user_id = ?";
    $domain = $db->select($sql, [$domainName, $_SESSION['user_id']]);

    if (empty($domain)) {
        http_response_code(404);
        echo json_encode(['error' => 'Domain not found']);
        exit;
    }

    // Delete domain from ElasticEmail
    $api->deleteDomain($domainName);

    // Delete domain from database
    $sql = "DELETE FROM domains WHERE domain_name = ? AND user_id = ?";
    $db->query($sql, [$domainName, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 