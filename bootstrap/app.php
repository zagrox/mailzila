<?php

// Enable error reporting in development
if ($_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
$config = require __DIR__ . '/../config/app.php';

// Define constants
define('APP_NAME', $config['name']);
define('APP_URL', $config['url']);
define('APP_ENV', $config['env']);
define('APP_DEBUG', $config['debug']);

// Set timezone
date_default_timezone_set($config['timezone']);

// Set locale
setlocale(LC_ALL, $config['locale']);

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize services
$container = new \App\Container\Container();

// Register core services
$container->bind(\App\Services\DatabaseService::class, function() {
    return \App\Services\DatabaseService::getInstance();
});

$container->bind(\App\Services\AuthService::class, function() use ($container) {
    return new \App\Services\AuthService();
});

$container->bind(\App\Services\ViewService::class, function() {
    return new \App\Services\ViewService();
});

// Register models
$container->bind(\App\Models\User::class, function() use ($container) {
    return new \App\Models\User($container->make(\App\Services\DatabaseService::class));
});

// Register controllers
$container->bind(\App\Controllers\Auth\LoginController::class, function() use ($container) {
    return new \App\Controllers\Auth\LoginController();
});

// Initialize router
$router = new \App\Routing\Router($container);

// Load routes
require_once __DIR__ . '/../routes/web.php';

// Handle the request
try {
    $router->dispatch();
} catch (\Exception $e) {
    if (APP_DEBUG) {
        throw $e;
    }
    
    // Log error
    error_log($e->getMessage());
    
    // Show error page
    http_response_code(500);
    include __DIR__ . '/../resources/views/errors/500.php';
} 