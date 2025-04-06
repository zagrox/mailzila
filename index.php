<?php
require_once __DIR__ . '/config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . APP_URL . '/auth/login');
    exit;
}

// If logged in, include the dashboard
require_once __DIR__ . '/pages/dashboard.php'; 