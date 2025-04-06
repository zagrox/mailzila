<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables if not already loaded
if (!defined('APP_URL')) {
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    } else {
        die('Environment file not found. Please create a .env file.');
    }

    // Define application constants
    define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8888/mailzila');
    define('APP_NAME', $_ENV['APP_NAME'] ?? 'Mailzila');
    define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
    define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? false);
}

// Initialize database connection if not already initialized
if (!isset($db)) {
    require_once __DIR__ . '/../includes/Database.php';
    $db = Database::getInstance(
        $_ENV['DB_HOST'],
        $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
}

// Initialize authentication if not already initialized
if (!isset($auth)) {
    require_once __DIR__ . '/../includes/Auth.php';
    $auth = new Auth();
}

// Set error reporting based on environment
if (APP_ENV === 'development' || APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('UTC');

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize ElasticEmail API if needed
if (!isset($api) && isset($_ENV['ELASTICEMAIL_API_KEY'])) {
    require_once __DIR__ . '/../includes/ElasticEmailAPI.php';
    $api = new ElasticEmailAPI($_ENV['ELASTICEMAIL_API_KEY']);
}

// Load helper functions
require_once __DIR__ . '/../includes/helpers.php'; 