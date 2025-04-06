<?php
require_once __DIR__ . '/../../config/init.php';

$auth = new Auth();
$auth->logout();

// Clear all session data
session_unset();
session_destroy();

// Redirect to login page with the correct path
header('Location: ' . APP_URL . '/auth/login');
exit; 