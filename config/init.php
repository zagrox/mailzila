<?php
session_start();

// Load environment variables
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

// Initialize database connection
require_once __DIR__ . '/../includes/Database.php';
$db = Database::getInstance(
    $_ENV['DB_HOST'],
    $_ENV['DB_NAME'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS']
);

// Initialize authentication
require_once __DIR__ . '/../includes/Auth.php';
$auth = new Auth();

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $auth;
    return $auth->getCurrentUser();
}

function redirect($path) {
    header('Location: ' . APP_URL . $path);
    exit();
}

// Load ElasticEmail API if needed
if (isset($_ENV['ELASTICEMAIL_API_KEY'])) {
    require_once __DIR__ . '/../includes/ElasticEmailAPI.php';
    $api = new ElasticEmailAPI($_ENV['ELASTICEMAIL_API_KEY']);
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

// Common functions
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

function getNotificationIcon($type) {
    switch ($type) {
        case 'campaign':
            return 'fas fa-paper-plane';
        case 'subscriber':
            return 'fas fa-user';
        case 'system':
            return 'fas fa-cog';
        default:
            return 'fas fa-bell';
    }
}

// CSRF Protection
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token() {
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
} 