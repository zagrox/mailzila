<?php

require_once __DIR__ . '/../includes/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// ElasticEmail Configuration
define('ELASTICEMAIL_API_KEY', $_ENV['ELASTICEMAIL_API_KEY']);

// Application Configuration
define('APP_NAME', $_ENV['APP_NAME']);
define('APP_URL', $_ENV['APP_URL']);
define('APP_ENV', $_ENV['APP_ENV']);
define('APP_DEBUG', $_ENV['APP_DEBUG']);

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Initialize ElasticEmail Client
use ElasticEmail\Api\CampaignsApi;
use ElasticEmail\Configuration;

$config = Configuration::getDefaultConfiguration()
    ->setApiKey('X-ElasticEmail-ApiKey', ELASTICEMAIL_API_KEY);

$apiInstance = new CampaignsApi(
    new GuzzleHttp\Client(),
    $config
); 