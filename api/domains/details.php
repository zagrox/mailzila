<?php
require_once __DIR__ . '/../../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if domain parameter is provided
if (!isset($_GET['domain'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Domain parameter is required']);
    exit;
}

$domainName = $_GET['domain'];

// Check if user owns this domain
try {
    $sql = "SELECT * FROM domains WHERE domain_name = ? AND user_id = ?";
    $domain = $db->select($sql, [$domainName, $_SESSION['user_id']]);

    if (empty($domain)) {
        http_response_code(404);
        echo json_encode(['error' => 'Domain not found']);
        exit;
    }

    // Get domain details from ElasticEmail
    $spfRecord = $api->getDomainSpfRecord($domainName);
    $dkimRecord = $api->getDomainDkimRecord($domainName);
    $verificationStatus = $api->getDomainVerificationStatus($domainName);
    $trackingStatus = $api->getDomainTrackingStatus($domainName);

    // Prepare response
    $response = [
        'spf_record' => $spfRecord['record'] ?? 'Not available',
        'dkim_record' => $dkimRecord['record'] ?? 'Not available',
        'verification' => [
            'spf' => $verificationStatus['spfVerified'] ?? false,
            'dkim' => $verificationStatus['dkimVerified'] ?? false,
            'tracking' => $trackingStatus['verified'] ?? false
        ]
    ];

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 