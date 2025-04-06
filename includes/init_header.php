<?php
// Get current path without base URL
$currentPath = str_replace('/mailzila', '', $_SERVER['REQUEST_URI']);
$currentPath = strtok($currentPath, '?'); // Remove query string

// Check if user is logged in
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $isLoggedIn ? $auth->getCurrentUser() : null;

// If not logged in and not on an auth page, redirect to login
$authPaths = ['/auth/login', '/auth/register', '/auth/google', '/auth/github'];
if (!$isLoggedIn && !in_array($currentPath, $authPaths)) {
    header('Location: ' . APP_URL . '/auth/login');
    exit;
}

// If logged in and on an auth page, redirect to home
if ($isLoggedIn && in_array($currentPath, $authPaths)) {
    header('Location: ' . APP_URL);
    exit;
}

// Initialize ElasticEmail API if needed
if (!isset($api) && isset($_ENV['ELASTICEMAIL_API_KEY'])) {
    require_once __DIR__ . '/ElasticEmailAPI.php';
    $api = new ElasticEmailAPI($_ENV['ELASTICEMAIL_API_KEY']);
} 